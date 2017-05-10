<?php

class Global_Action_WebBase extends Global_Action_Base
{
    /**
     * @var Global_Template
     */
    protected $smarty  = null;
    protected $sysInfo = array();
    protected $tplData = array();

	public function _before(){
	}

    protected function display($tpl, $forceCompile = false) {
        $this->_initSmarty();
        $this->smarty->force_compile = $forceCompile;

        $this->smarty->display($tpl);
    }

    protected function _initSmarty() {
        if($this->smarty) {
            return true;
        }
        $this->smarty = new Global_Template();

        //设置数据至smarty变量中
        $this->_setSmartyVar();

        return true;
    }

    protected function _setSmartyVar(){
        $this->_setSysInfo();

        $this->smarty->assign("sysInfo", $this->sysInfo);
        $this->smarty->assign("tplData", $this->tplData);
    }

    private function _setSysInfo() {
        if(defined("ONLINE") && ONLINE===true) {
            $env = 'online';
        } elseif(defined("DEBUG") && DEBUG===true) {
            $env = 'debug';
        } else {
            $env = 'debug';
        }

        $this->sysInfo = array(
            "serverTime" => time(),
            "env"        => $env,
            "get"        => $_GET,
        );
    }
}
