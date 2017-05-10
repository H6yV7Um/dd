<?php

class DispatchEntrance extends Bingo_Action_Abstract {
    //不允许用户直接访问的url,_post为了提交数据的函数，后面两个是继承原始ACTION而来，主要继承的controller可以不继承
    protected $protected = array('_post', 'execute', 'initial', '_before', '_after', '_default');
    //页面所属类型,缺省是web类型（后续有需求再升级为其它产品线）
    protected $appType = '';
    // 控制器根目录
    protected $actionRootPath = '';
    // 具体控制器的绝对路径
    protected $actionPath = '';
    // 控制器
    protected $actionName = '';
    // 控制器方法
    protected $action = "_default";
    //缺省controller路径
    protected $defaultAction = array("default");
    protected $maxDeep = 3;

    /**
     * 执行入口
     */
    public function execute() {
        $this->appType = Global_Env::$productLine;
        $this->_initControllerRootPath();
        $this->_setController();
        try {
            if (class_exists($this->actionName)) {
                $objController = new $this->actionName();
            } else {
                throw new Exception($this->actionName . " file not exists");
            }
            //方法之前调用函数
            $this->_callmethod($objController, '_before');

            if (is_callable(array($objController, $this->action))) {
                if (!in_array($this->action, $this->protected)) {
                    $this->_callLog();
                    $this->_callmethod($objController, $this->action, Global_Env::$cli_param);
                }
            } else {
                throw new BadMethodCallException("action is not exists", 500);
            }

            //方法之后调用函数
            $this->_callmethod($objController, '_after');
        } catch (Exception $e) {
            $ActionBase = new Global_Action_Base();
            $ActionBase->exception = $e;
            $this->_callmethod($ActionBase, '_default');
        }
    }

    /**
     * 根据url设置controller的路径
     */
    private function _setController() {
        $country = Global_Env::$country;
        $arrUri  = Global_Env::$arrUri;
        $this->_pathToAbsolute($country, $arrUri);

    }

    /**
     * 初始化路径
     */
    private function _initControllerRootPath() {
        $request_version = $_REQUEST["VERSION"];
        $actionRootPath  = ACTION_PATH . $request_version . DIRECTORY_SEPARATOR;
        if (is_dir($actionRootPath)) {
            $this->actionRootPath = $actionRootPath;
        } else {
            $this->actionRootPath = ACTION_PATH . DEFAULT_VERSION . DIRECTORY_SEPARATOR;
        }
    }

    //转换相对路径为绝对路径
    private function _pathToAbsolute( $country, $arrUri ){
        $actionRootPath = $this->actionRootPath;

        // URL路径深度最大为3层, 最外层为方法名
        $actionCnt = $this->maxDeep - 1;
        $cnt       = count($arrUri);
        for($i = 0; $i < $actionCnt - $cnt; $i++) {
            $arrUri[] = DEFAULT_ACTION;
        }
        if($cnt == $this->maxDeep) {
            $this->action = array_pop($arrUri);
        } else {
            $this->action = 'index';
        }

        $this->actionPath = $actionRootPath.implode("/",$arrUri).".action.php";
        $this->actionName = implode("_",$arrUri)."_Action";
        if(!class_exists($this->actionName)){
            $this->actionPath = $actionRootPath.DEFAULT_ACTION_DIR."/".DEFAULT_ACTION.".action.php";
            $this->actionName = DEFAULT_ACTION_DIR."_".DEFAULT_ACTION."_Action";
        }
    }

    /**
     * 执行方法
     *
     * @param       $controller
     * @param       $method
     * @param array $args
     *
     * @return bool
     */
    private function _callmethod($controller, $method, $args = array()) {

        if (is_callable(array($controller, $method))) {
            $reflection = new ReflectionMethod($controller, $method);
            //公共方法才允许被调用
            $reflection->invokeArgs($controller, array($args));
            return true;
        }
        return false;
    }

    /**
     * 记录入口日志 用户名 ip 入参
     */
    private function _callLog() {
        $separator  = ' | ';
        $ip         = Global_Env::getRealip();
        $sessionId  = session_id();
        $input      = '';
        if (!empty($_GET)) {
            $input .= 'GET' . json_encode($_GET);
        }
        if (!empty($_POST)) {
            $input .= 'POST' . json_encode($_POST);
        }

        if (strlen($input) > 150) {
            $input = substr($input, 0, 150) . '...';
        }
        $callSource = $this->actionPath . '->' . $this->action;
        $message    = array($sessionId, $ip, $callSource, $input);
        $message    = $separator . join($separator, $message);
        Bingo_Log::notice("dispatch_entrance_log:" . $message, 'dal');
    }

}
