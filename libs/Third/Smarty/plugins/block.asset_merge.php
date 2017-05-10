<?php
define("SOURCE_ROOT",WEBROOT_PATH);
function smarty_block_asset_merge($params, $content, &$smarty, &$repeat){
    if (!$repeat){
        $tag_pattern = '#\s*<\s*T(?:\s+(.*))?\s*(?:/>|>(.*)</T>)\s*#Uims';
        $record_merge = array();
        $tags = array();

        $aindex = 0;
        //return $content;

        //cdn 补全,随机取备选cdn
        $root = $smarty->getTemplateVars('root');
        if (!empty($root['head']) && !empty($root['head']['cdn'])) {
            $cdns = split(';', rtrim($root['head']['cdn'], ';'));
            CacheRenderer::$cdn = $cdns[rand(0, count($cdns) - 1)];
        }

        foreach(array('link', 'script') as $tag){
            $p = str_replace('T', preg_quote($tag), $tag_pattern);
            if(preg_match_all($p, $content, $ms, PREG_OFFSET_CAPTURE)){
                foreach($ms[1] as $i => $m){
                    $tags[] = array(
                        'tag' => $tag,
                        'attrdata' => $m[0],
                        'index' => $ms[0][$i][1],
                        'tagdata' => $ms[0][$i][0],
                        'content' => $ms[2][$i],
                        'page_order' => $aindex++
                    );
                }
            }
        }
        $sortFun = create_function('$a,$b','if ($a["index"] == $b["index"]) return 0;return ($a["index"] < $b["index"]) ? 1 : -1;');
        $category = create_function('$work_unit','return implode("", array($work_unit["group"], $work_unit["tag"]));');
        usort($tags,$sortFun);
        $ex = new WorkUnitExtractor();
        $work_units = $ex->getAcceptedWorkUnits($tags);
        if ( count($work_units) === 0 ) {
            return $content;
        }
        $renderer = new CacheRenderer();
        $patched_content = $content;
        $render = array();

        $curr_cat = $category($work_units[0]);

        $entry = null;
        foreach($work_units as $i => $entry){
            $cg = $category($entry);

            // the moment the category changes, render all we have so far
            // this makes it IMPERATIVE to keep links of the same category
            // together.
            if ($curr_cat != $cg ){
                if (  $record_merge[$curr_cat] || file_exists(SOURCE_ROOT.ltrim($work_units[$i-1]['group'], "/")) ) {
                   $record_merge[$curr_cat] = true;
                    $render_order = array_reverse($render);
                    $res = $renderer->renderWorkUnits($work_units[$i-1]['tag'], $work_units[$i-1]['group'], $render_order);
                    // add rendered stuff to patched content
                    $m = null;
                    foreach($render as $r){
                        if ($m == null) $m = $r['position'];
                        if ($r['position'] < $m) $m = $r['position'];
                        // remove tag
                        $patched_content = substr_replace($patched_content, '', $r['position'], $r['length']);
                    }
                    // splice in replacement
                    $patched_content = substr_replace($patched_content, $res, $m, 0);
                }
                $curr_cat = $cg;
                $render = array($entry);
            }else{
                $render[] = $entry;
            }
        }
        $render_order = array_reverse($render);
        if ($work_units &&  (isset($record_merge[$cg]) || file_exists(SOURCE_ROOT.ltrim($work_units[count($work_units)-1]['file'],"/"))) ){
            $res = $renderer->renderWorkUnits($entry['tag'], $entry['group'], $render_order);
            if ($res === false){
                // see last comment
                return $content;
            }
            $m = null;
            foreach($render as $r){
                if ($m == null) $m = $r['position'];
                if ($r['position'] < $m) $m = $r['position'];
                // remove tag
                $patched_content = substr_replace($patched_content, '', $r['position'], $r['length']);
            }
            $patched_content = substr_replace($patched_content, $res, $m, 0);
        }
        return $patched_content;
    }
}
class WorkUnitExtractor{
    function getAcceptedWorkUnits($tags){
        $work_units = array();
        foreach($tags as $tag){
            $r = $this->workUnitFromTag($tag['tag'], $tag['attrdata'], $tag['content']);
            if ($r === false) continue; // handler has declined
            $r = array_merge($r, array(
                'page_order' => $tag['page_order'],
                'position' => $tag['index'],
                'length' => strlen($tag['tagdata']),
                'tag' => $tag['tag']
            ));
            $work_units[] = $r;
        }
        return $work_units;
    }

    function workUnitFromTag($tag, $attrdata, $content){
        switch($tag){
            case 'link':
                $fn = 'extract_link_unit';
                break;
            case 'script':
                $fn = 'extract_script_unit';
                break;
            default: throw new Exception("Cannot handle tag: ($tag)");
        }
        return $this->$fn($tag, $attrdata, $content);
    }

    private function extract_attrs($attstr){
        // The attribute name regex is too relaxed, but let's
        // compromise and keep it simple.
        $attextract = '#([a-z\-]+)\s*=\s*(["\'])\s*(.*?)\s*\2#';
        if (!preg_match_all($attextract, $attstr, $m)) return false;
        $res = array();
        foreach($m[1] as $idx => $name){
            $res[strtolower($name)] = $m[3][$idx];
        }
        return $res;
    }


    private function extract_link_unit($tag, $attrdata, $content){
        $attrs = $this->extract_attrs($attrdata);
        $attrs['type'] = strtolower($attrs['type']);

        // invalid markup
        if (empty($attrs['href']) || empty($attrs['des']) ) return false;
      /*  $path = null;
        $path = $this->urlToFile($attrs['href']);
        if ($path === false) return false;*/

        return array(
            'group' => $attrs['des'],
            'file' => $attrs['des'],
            'content' => $content,
            'type' => $attrs['type']
        );
    }

    private function extract_script_unit($tag, $attrdata, $content){
        $attrs = $this->extract_attrs($attrdata);
        if ($content[0] || empty($attrs['des'])){
            return false;           
         }

        return array(
                    'group' => $attrs['des'],
                    'content' => $content,
                    'file' => $attrs['des'],
                    'type' => $attrs['type']
        );
    }


}
class CacheRenderer {
    public static $cdn = '';
    function renderWorkUnits($tag, $cat, $work_units){
        switch($tag){
            case 'link':
                $fn = 'render_style_units';
                break;
            case 'script':
                $fn = 'render_script_units';
                break;
            default: throw new Exception("Cannot handle tag: $tag");
        }
        return $this->$fn($work_units, $cat);
    }
    private function render_style_units($work_units, $cat){
        // we can do this because tags are grouped by the presence of a file or not
        $href = $work_units[0]['group']."?m=".filemtime( SOURCE_ROOT.ltrim($work_units[0]['group'],"/") );
        $res = sprintf('<link rel="stylesheet" type="text/css" href="%s" />'."\n", htmlspecialchars(CacheRenderer::$cdn.$href, ENT_QUOTES));
        return $res;
    }

    private function render_script_units($work_units, $cat){
        $src = $work_units[0]['group']."?m=".filemtime( SOURCE_ROOT.ltrim($work_units[0]['group'],"/") );
        $res = sprintf('<script type="text/javascript" src="%s"></script>'."\n", htmlspecialchars(CacheRenderer::$cdn.$src, ENT_QUOTES));    
        return $res;
    }
}
?>
