<?php

class Global_Action_Base {
    /**
     * @var array
     */
    public $post;
    /**
     * @var array
     */
    public $get;
    /**
     * @var Exception
     */
    public $exception = null;
    /**
     * @var int
     */
    protected $errno  = 0;
    /**
     * @var string
     */
    protected $errMsg = "";

    public function _before() {
    }

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

    public function getLoginUserId($bduss = null) {

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

    public function endWithResponseJson($result = []) {
        header("Access-Control-Allow-Credentials: true");
        if(defined(DEBUG) && DEBUG === true) {
            header("Access-Control-Allow-Origin: http://10.99.23.23:8599");
        } else {
            header("Access-Control-Allow-Origin: http://www.facestream.tv");
        }
        if (is_null($this->exception)) {
            $res = array(
                "errNo"  => 0,
                "errMsg" => "",
                "result" => $result,
            );
        } else {
            $this->errno  = $this->exception->getCode();
            $this->errMsg = $this->exception->getMessage();
            $res          = array(
                "errNo"  => $this->exception->getCode() ?: 500,
                "errMsg" => $this->exception->getMessage() ?: "Internal error",
                "result" => $result,
            );
        }
        $this->renderJSON($res);
    }

    /**
     * @param $val
     */
    public function renderJSON($val) {
        header("Content-type:application/json; charset=utf-8");
        echo json_encode($val);
        exit;
    }
}
