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
        [
            'key'     => 'phoneCode',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^\d{6}$/',
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
        [
            'key'     => 'codeType',
            'default' => 'register',
            'func'    => 'strval',
            'regex'   => '/^(register|resetPwd)$/',
            'method'  => '',
        ],
    ];

    /**
     * 用户注册
     */
    public function register() {
        try {
            $this->_checkParamsV2(self::$registerParamsRule);

            // 验证码校验
            $res = Message_Phone_Service::getInstance()->validateCode($this->post['phoneNum'], $this->post['phoneCode']);
            if(!$res) {
                throw new \Exception('phone code is invalid', Global_ErrorCode_Common::USER_PHONE_CODE_ERROR);
            }

            // 注册
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

    /**
     * 登录
     */
    public function login() {
        try {
            $this->_checkParamsV2(self::$loginParamsRule);

            $res = User_Pass_Service::getInstance()->login($this->post['phoneNum'], $this->post['password']);
            if(!$res) {
                throw new \Exception('login failed', Global_ErrorCode_User::USER_LOGIN_FAILED);
            }

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
            $codeType = $this->post['codeType'];

            if(Message_Phone_Service::getInstance()->isFrequent($phoneNum, $codeType)) {
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