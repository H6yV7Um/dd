<?php

/**
 * Class User_Pass_Action
 */
class User_Pass_Action extends Global_Action_Base {
    /**
     * @var array
     */
    protected static $registerParamsRule = [
        [
            'key'     => 'phoneNum',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^1\d{10}$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'password',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^\w{6,14}$/',
            'method'  => 'post',
        ],
    ];

    protected static $loginParamsRule = [
        [
            'key'     => 'phoneNum',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^1\d{10}$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'password',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^\w{6,14}$/',
            'method'  => 'post',
        ],
    ];

    protected static $sendPhoneCodeParamsRule = [
        [
            'key'     => 'phoneNum',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^1\d{10}$/',
            'method'  => 'post',
        ],
    ];

    /**
     * 用户注册
     */
    public function register() {
        try {
            $this->_checkParamsV2(self::$registerParamsRule);

            $res = User_Pass_Service::getInstance()->register($this->post);
            if(!$res) {
                throw new \Exception('register failed', Global_ErrorCode_User::USER_REG_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    public function login() {
        try {
            $this->_checkParamsV2(self::$loginParamsRule);

            $res = User_Pass_Service::getInstance()->login($this->post['phoneNum'], $this->post['password']);
            if(!$res) {
                throw new \Exception('login failed', Global_ErrorCode_User::USER_LOGIN_FAILED);
            }

            // TODO 登录成功之后redirect, 可以不写在api里, 放在web里更合适

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 发送验证码
     */
    public function sendPhoneCode() {
        try {
            $this->_checkParamsV2(self::$sendPhoneCodeParamsRule);

            $phoneNum = $this->post['phoneNum'];

            if(Message_Phone_Service::getInstance()->isFrequent($phoneNum, 'code')) {
                throw new \Exception('request so frequent', Global_ErrorCode_Common::MESSAGE_REQUEST_FREQUENT);
            }

            if(!Message_Phone_Service::getInstance()->sendPhoneCode($phoneNum)) {
                throw new \Exception('send phone code failed', Global_ErrorCode_Common::MESSAGE_SEND_PHONE_CODE_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}