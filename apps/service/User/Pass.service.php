<?php

class User_Pass_Service extends Global_Service_Base {
    /**
     * @return User_Pass_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * 用户注册
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function register($params) {
        $needFields = [
            'phoneNum',
            'password',
        ];
        foreach($needFields as $field) {
            if(!$params[$field]) {
                Bingo_Log::warning("params error. params: " . json_encode($params));
                throw new \Exception('params error', Global_ErrorCode_User::USER_PARAMS_ERROR);
            }
        }

        // 手机号是否已注册
        if(User_Model::getInstance()->isPhoneExists($params['phoneNum'])) {
            throw new \Exception('phone already use', Global_ErrorCode_User::USER_ALREADY_REGISTER);
        }

        // 随机密码字符串
        $str = "abcdefghijklmnopqrestuvwxyz1234567890ABCDEFGHIJKLMNOPQRESTUVWXYZ";
        $pwdStr = substr(str_shuffle($str), 0, 6);
        $params['pwdStr']   = $pwdStr;
        $params['password'] = md5($params['password'] . $pwdStr);
        if(!User_Model::getInstance()->createUser($params)) {
            throw new \Exception('create user failed');
        }

        return true;
    }
}