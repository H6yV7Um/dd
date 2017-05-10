<?php

/**
 *  国际化smarty模板封装
 * @author mozhuoying <mozhuoying@baidu.com>
 * @package global
 * @since 2014-04-28
 */
require_once "Third/Smarty/Smarty.class.php";
require_once "Global/Cms/Base.php";
class Global_Template extends Smarty
{   
    private static $__instance;

    public static function getInstance(){
        if(empty($__instance)){
            $__instance = new Global_Template();
        }
        return $__instance;
    }

    public function __construct(){
		parent::__construct();
		$this->setTemplateDir(TEMPLATE_PATH)
            ->setCompileDir(TEMPLATE_COMPLITE_PATH)
            ->setPluginsDir(SMARTY_PLUGINS_DIR)
            ->setCacheDir(TEMPLATE_CACHE_PATH)
            ->setConfigDir(array(
                                    "fis" => TEMPLATE_PATH."config"
                                )
                          );
        $this->compile_check = true;
        $this->left_delimiter = '<%';
        $this->right_delimiter = '%>';
        $this->cache_modified_check = true;
        $this->caching = false;
        if(defined("DEBUG") && DEBUG===true)
        {
            $this->debugging_ctrl='URL';
        }
        //$this->error_reporting=error_reporting();
    }
    public function show($tpl_name,$arrData = array(), $cache_id = null, $compile_id = null, $parent = null){
        $this->assign('root',$arrData);
        $this->display($tpl_name, $cache_id, $compile_id , $parent );
    }
	public function cacheShow($tpl_name,$arrData = array(), $cache_id = null, $compile_id = null, $parent = null, $cache_lifetime = -1){
		$this->caching = true;
		$this->cache_lifetime =  $cache_lifetime;
		$this->show($tpl_name,$arrData, $cache_id , $compile_id , $parent);
	}
}
?>
