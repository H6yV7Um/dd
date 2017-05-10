<?php

/**
 *
 *
 * @author  mozhuoying <mozhuoying@baidu.com>
 * @package bingo2.0
 * @since   2014-04-29
 *
 * @modify  gedejin <gedejin@baidu.com>
 * @time    2015-04-20
 * @todo    controller 基类
 */
class Global_Action_Base {

    protected $baseUserInfo = array();

    public $post;
    public $get;
    public $request;
    //会话信息，适配移动端
    private $_sessionParams = array();
    private $_sessionParamsKeys = array('CLIENT', 'DEVICEID', 'BDUSS', 'OS', 'OSVERSION', 'SID', 'DEVICE', 'TN', 'COUNTRY');
    private $_sessionCookieKeys = array('BDUSS', 'SID', 'DEVICEID', 'TN', 'OS', 'CLIENT', 'OSVERSION', 'COUNTRY');
    protected $errno = 0;
    protected $errMsg = "";

    /**
     * @var Exception
     */
    public $exception = null;

    public function _before() {
        $this->errno = Global_ErrorCode_Common::$COMMON_SUCCESS;
        //初始化移动端参数
        $this->_initMobileEnv();
    }

    /**
     * @todo 正则方式过滤参数
     *
     * @param array $paramsRule
     *
     * @throws Exception
     */
    public function _checkParams($paramsRule = array()) {
        if (DEBUG) {
            $this->_checkParamsRequest($paramsRule);
            return;
        }

        if (empty($paramsRule) || !is_array($paramsRule)) {
            $this->post = $_POST;
            $this->get  = $_GET;
        } else {
            $get      = array();
            $post     = array();
            $security = new Global_Security_Base();
            foreach ($paramsRule as $paramRule) {
                $func = $paramRule['type'] == 'string' ? 'strval' : 'intval';
                $temp = $func($_REQUEST[$paramRule["key"]]);
                $temp = isset($temp) ? $paramRule["default"] : $temp;
                $temp = ((isset($paramRule['min'])) ? ($temp > $paramRule['min'] ? $temp : $paramRule['default']) : $temp);
                if (strtolower($paramRule["method"]) == "get") {
                    if (preg_match($paramRule["regex"], $temp)) {
                        $get[$paramRule["key"]] = $temp;
                        //                        $get[$paramRule["key"]] = $security->xss_clean($temp);
                    } else {
                        if ($paramRule['isStrict']) {
                            throw new Exception("Params error.", Global_ErrorCode_Common::$COMMON_PARAMS_ERROR);
                        } else {
                            $get[$paramRule["key"]] = $paramRule["default"];
                        }
                    }
                } else {
                    if (preg_match($paramRule["regex"], $temp)) {
                        $post[$paramRule["key"]] = $temp;
                        //                        $post[$paramRule["key"]] = $security->xss_clean($temp);
                    } else {
                        if ($paramRule['isStrict']) {
                            throw new Exception("Params error.", Global_ErrorCode_Common::$COMMON_PARAMS_ERROR);
                        } else {
                            $get[$paramRule["key"]] = $paramRule["default"];
                        }
                    }

                }
            }

            $this->get  = $get;
            $this->post = $post;
        }
    }

    /**
     * @param array $paramsRules
     *
     * @return bool
     * @throws Exception
     */
    private function _checkParamsRequest($paramsRules = []) {
        if (empty($paramsRules) || !is_array($paramsRules)) {
            $this->post = $_REQUEST;
            $this->get  = $_REQUEST;
            return true;
        }

        $params   = [];
        $security = new Global_Security_Base();
        foreach ($paramsRules as $paramRule) {
            $func    = $paramRule['type'] == 'string' ? 'strval' : 'intval';
            $default = isset($paramRule['default']) ? $func($paramRule['default']) : null;
            $value   = isset($_REQUEST[$paramRule['key']]) ? $func($_REQUEST[$paramRule['key']]) : $default;
            if ($paramRule['regex'] && !preg_match($paramRule['regex'], $value) && $paramRule['isStrict']) {
                Bingo_Log::warning("regex: {$paramRule['regex']} key: {$paramRule['key']} value: $value", 'dal');
                throw new Exception("Params error. param: $value", Global_ErrorCode_Common::$COMMON_PARAMS_ERROR);
            }
            //            $params[$paramRule['key']] = $security->xss_clean($value);
            // app端屏蔽掉xss检测, web前端也做了兼容
            $params[$paramRule['key']] = $value;
        }
        $this->post = $params;
        $this->get  = $params;

        return true;
    }

    /**
     * 参数校验
     * @param $paramRuleList
     * @return bool
     * @throws Exception
     */
    public function _checkParamsV2($paramRuleList) {
        if(!$paramRuleList || !is_array($paramRuleList)) {
            $this->get  = $_GET;
            $this->post = $_POST;
            return true;
        }

        $getParams  = [];
        $postParams = [];
        foreach($paramRuleList as $paramRule) {
            if(!isset($paramRule['key']) || !$paramRule['key']) {
                continue;
            }

            $isGet = $isPost = 0;
            if(isset($paramRule['method']) && strtolower($paramRule['method']) == 'post') {
                $isPost = 1;
                $requestParams = $_POST;
            } else {
                $isGet  = 1;
                $requestParams = $_GET;
            }

            // get value
            $key = $paramRule['key'];
            if(!isset($requestParams[$key]) && !isset($requestParams['default'])) {
                Bingo_Log::warning("Params error. param '$key' is needed.", 'dal');
                throw new Exception("Params error. param '$key' is needed.", Global_ErrorCode_Common::COMMON_PARAMS_ERROR);
            }
            if(isset($requestParams[$key])) {
                $value = $requestParams[$key];
            } else {
                $value = $requestParams['default'];
            }

            // function handler
            if(isset($paramRule['func']) && is_callable($paramRule['func'])) {
                $value = $paramRule['func']($value);
            }

            // regex filter
            if (isset($paramRule['regex']) && $paramRule['regex'] && !preg_match($paramRule['regex'], $value)) {
                Bingo_Log::warning("regex: {$paramRule['regex']} key: {$paramRule['key']} value: $value", 'dal');
                throw new Exception("Params error. param: $value don't match the regex.", Global_ErrorCode_Common::COMMON_PARAMS_ERROR);
            }

            if($isGet) {
                $getParams[$key]  = $value;
            } else {
                $postParams[$key] = $value;
            }
        }

        $this->get  = $getParams;
        $this->post = $postParams;

        return true;
    }

    /**
     *方法的默认
     *
     * @param
     *
     * @return
     */
    public function _default() {
        $this->errno  = $this->exception->getCode();
        $this->errMsg = $this->exception->getMessage();
        $result = array(
            "message" => array(
                "code"        => $this->errno,
                "messageInfo" => $this->errMsg,
            ),
            'result'  => array(),
        );
        $this->renderJSON($result);
    }

    /**
     * @param $val
     */
    public function renderJSON($val) {
        if(isset($val['result'])) {
            $this->tranceReturnInt2Str($val['result']);
        }
        $this->responseJSON($val);
    }

    /**
     * @param $val
     */
    private function tranceReturnInt2Str(&$val) {
        foreach ($val as &$unit) {
            if (is_array($unit)) {
                $this->tranceReturnInt2Str($unit);
            } else {
                if (is_numeric($unit)) {
                    $unit = strval($unit);
                } else {
                    if (!is_numeric($unit)) {
                        $unit = urldecode($unit);
                    }
                }
            }
        }
    }

    /**
     * @param $val
     */
    public function responseJSON($val) {
        header("Content-type:application/json; charset=utf-8");
        if ($_REQUEST['CLIENT'] == 'mobile') {
            $val['sessionData'] = $this->getSessionParamsAll();
        }
        echo json_encode($val);
        $this->saveCostTime("", "api_cost", $val);
        die;
    }

    /**
     * @param $val
     */
    public function responseJSONMobile($val) {
        header("Content-type:application/json; charset=utf-8");
        $val['sessionData'] = $this->getSessionParamsAll();
        echo json_encode($val);
        $this->saveCostTime("", "api_cost");
        die;
    }

    /**
     * @param $val
     */
    public function responseJSONP($val) {
        header("Content-type:application/json; charset=utf-8");

        if ($_REQUEST['CLIENT'] == 'mobile') {
            $val['sessionData'] = $this->getSessionParamsAll();
        }
        $jsonp = $_REQUEST['jsonp'];
        if (!empty($jsonp)) {
            echo $jsonp . "(" . json_encode($val) . ")";
        } else {
            echo json_encode(array("message" => "params jsonp error!"));
        }
        $this->saveCostTime();
        die;
    }

    /**
     * @param string $subFunctionName
     * @param string $logFile
     */
    public function saveCostTime($subFunctionName = "main", $logFile = "api_cost") {
        global $g_startTime;

        $endTime = microtime(true);
        $api     = $_SERVER["REQUEST_URI"];
        $logInfo = "api:$api -- [$subFunctionName],costTime:" . ($endTime - $g_startTime);
        Bingo_Log::notice($logInfo, $logFile);
    }


    /**
     * @param     $error
     * @param     $msg
     * @param int $code
     */
    protected function _errorWithJSON($error, $msg, $code = 1) {
        if (!$error) {
            return;
        }
        Bingo_Log::warning($msg, 'dal');
        $retVal          = array('status' => $code);
        $retVal['error'] = $msg;
        $this->renderJSON($retVal);
        die;
    }

    /**
     * @return null|bool
     */
    protected function _initMobileEnv() {
        if ($_REQUEST['CLIENT'] != 'mobile') {
            return true;
        }

        foreach ($this->_sessionParamsKeys as $k) {
            $this->_sessionParams[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : '';
        }

        foreach ($this->_sessionCookieKeys as $k) {
            if (!empty($this->_sessionParams[$k])) {
                $_COOKIE[$k] = $this->_sessionParams[$k];
            }
        }
    }

    /**
     * @param $k
     * @param $v
     */
    protected function setSessionParams($k, $v) {
        if (in_array($k, $this->_sessionParamsKeys)) {
            $this->_sessionParams[$k] = $v;
        }

        if (in_array($k, $this->_sessionCookieKeys)) {
            $_COOKIE[$k] = $v;
        }
    }

    /**
     * @return array
     */
    protected function getSessionParamsAll() {
        return $this->_sessionParams;
    }

    /**
     * @param $code
     *
     * @return array
     */
    protected function getReturnMessage($code, $msg = null) {
        return array(
            "code"        => $code,
            "messageInfo" => Global_ErrorLang_Common::getMessage($code, $msg),
        );
    }

    /**
     * @param null $bduss
     *
     * @return int|null
     * @throws Exception
     */
    public function getLoginUserId($bduss = null) {
        if ($_GET['muchaocheat'] && DEBUG) {
            return $_GET['muchaocheat'];
        }

        if ($_GET['hhCheatId'] && DEBUG) {
            return $_GET['hhCheatId'];
        }

        $passService = User_Passport_Service::getInstance();

        if(is_null($bduss) && isset($_REQUEST['BDUSS'])) {
            $bduss = $_REQUEST['BDUSS'];
        }
        if(is_null($bduss) && isset($_COOKIE['BDUSS'])) {
            $bduss = $_COOKIE['BDUSS'];
        }

        Bingo_Log::notice("get login user userId. bduss: $bduss", 'dal');
        if (!$bduss || !$passService->isLogin($bduss)) {
            throw new Exception('need login', Global_ErrorCode_Common::COMMON_NEED_LOGIN);
        }

        $userId = $passService->getUidFromBduss($bduss);
        if (!$userId) {
            throw new Exception('need login', Global_ErrorCode_Common::COMMON_NEED_LOGIN);
        }

        return $userId;
    }

    /**
     * @param $result
     */
    public function endWithResponseJson($result = []) {
        header("Access-Control-Allow-Credentials: true");
        if(defined(DEBUG) && DEBUG === true) {
            header("Access-Control-Allow-Origin: http://10.99.23.23:8599");
        } else {
            header("Access-Control-Allow-Origin: http://www.facestream.tv");
        }
        if (is_null($this->exception)) {
            $res = array(
                "message" => $this->getReturnMessage(Global_ErrorCode_Common::$COMMON_SUCCESS),
                "result"  => $result,
            );
        } else {
            $this->errno  = $this->exception->getCode();
            $this->errMsg = $this->exception->getMessage();
            $res          = array(
                "message" => $this->getReturnMessage($this->errno, $this->errMsg),
            );
        }
        $this->renderJSON($res);
    }
}
