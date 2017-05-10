<?php

function smarty_compiler_require($arrParams,  $smarty){
    $strName = $arrParams['name'];
    //async，组件进行异步加载，针对JS
    $strAsync = $arrParams['async'];

    if ($strAsync) {
        $strAsync = trim($strAsync, '\'"');
        if ($strAsync == 'true') {
            $strAsync = 'true';
        } else {
            $strAsync = 'false';
        }
    } else {
        $strAsync = 'false';
    }

    $strCode = '';
    if($strName) {
        $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/lib/FISPagelet.class.php');
        $strCode .= '<?php if(!class_exists(\'FISPagelet\', false)){require_once(\'' . $strResourceApiPath . '\');}';
        
         /********autopack collect require resource************/
        $strAutoPackPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/lib/FISAutoPack.class.php');
        //注意加php头，否则解析出错
        $strCode = '<?php if(!class_exists(\'FISAutoPack\')){require_once(\'' . $strAutoPackPath . '\');}';
        $strCode .= 'FISAutoPack::addHashTable(' . $strName . ',$_smarty_tpl->smarty' . ');';
        /*****************autopack end**********************/

        $strCode .= 'FISPagelet::load(' . $strName . ',$_smarty_tpl->smarty,'.$strAsync.');';
        $strCode .= '?>';
    }
    return $strCode;
}
