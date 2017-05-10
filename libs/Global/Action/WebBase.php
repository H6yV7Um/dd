<?php
/**
 *
 *
 * @author mozhuoying <mozhuoying@baidu.com>
 * @package bingo2.0
 * @since 2014-04-29
 *
 * @modify gedejin <gedejin@baidu.com>
 * @time 2015-04-20
 * @todo controller 基类
 */

class Global_Action_WebBase extends Global_Action_Base
{

    protected $smarty = '';
    protected $cmsData = array();
    protected $sysInfo = array();
    protected $tplData = array();


	public function _before(){

		//初始化移动端参数
        $this->_initMobileEnv();
        //读取cms数据并且合并
        $this->_setCmsData();
        //传递给smarty的系统变量
        $this->_setSysInfo();
	}

    private function _setCmsData(){
        //读取base数据
        $arrBaseData = Global_Cms_Base::get( Global_Env::$baseDataPath );
        Bingo_Log::debug("baseDataPath=".Global_Env::$baseDataPath,"dal");
        //读取private数据
        $arrprivateData = Global_Cms_Base::get( Global_Env::$privateDataPath );

        Bingo_Log::debug("arrprivateDataPath=".Global_Env::$privateDataPath,"dal");
        //是否配置第二层base数据的地址
        $privateBaseDataPath = @$arrprivateData["head"]["privateBaseData"];
        $arrprivateBaseData = array();
        if( !empty( $privateBaseDataPath ) ){
            $arrprivateBaseData = Global_Cms_Base::get(Global_Env::transPath($privateBaseDataPath) );
        }
        //数据合并
        $this->cmsData = Global_Cms_Base::array_merge($arrBaseData, $arrprivateBaseData, $arrprivateData);

    }

    protected function _initSmarty()
    {
        if($this->smarty){
            return ;
        }
        $this->smarty = new Global_Template();
        //设置数据至smarty变量中
        $this->_setSmartyVar();
    }
    //给前端提供
    private function _setSysInfo(){
        if(defined("ONLINE")&& ONLINE===true){
            $isline='online';
        }elseif(defined("DEBUG") && DEBUG===true){
            $isline='offline';
        }else{
            $isline='offline';
        }

        $this->sysInfo = array(
            "country" => Global_Env::$country,
            "host"    => Global_Env::$host,
            "serverTime" => time(),
            "env" =>$isline,
            "get" => $_GET,
        );
        Bingo_Log::debug("sysinfo :".json_encode($this->sysInfo),'dal');
    }


    private function _setSmartyVar(){
        $this->smarty->assign( "sysInfo", $this->sysInfo );
        $this->smarty->assign( "root", $this->cmsData );
    }


    /**
     * @param
     * @return
     */
    protected function display($tpl='',$force_complie=false,$flag=false)
    {
        $this->_initSmarty();
        $this->smarty->force_compile = $force_complie;

        $this->smarty->assign( "tplData", $this->tplData );
        if($tpl){
            $this->smarty->display($tpl);
        }else{
            $this->smarty->display($this->cmsData['head']["tplDir"]);
        }

    }

}
