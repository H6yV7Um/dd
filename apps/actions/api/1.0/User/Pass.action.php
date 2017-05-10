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
            'regex'   => '/^\d{8,}$/',
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
            'regex'   => '/^\d{8,}$/',
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
}