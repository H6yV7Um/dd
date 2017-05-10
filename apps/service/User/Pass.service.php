<?php

class User_Pass_Service extends Global_Service_Base {
    // token相关
    const TOKEN_CONTENT_SEPARATOR = "__@@__@@";
    const TOKEN_SECRET_KEY = "thisichussesonghuicretkeyhuifortoken";

    // 用户会话token的超时时间
    const USER_SESSION_EXPIRED_TIME = 86400;

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

    /**
     * 登录
     * @param $phoneNum
     * @param $password
     * @return bool
     * @throws Exception
     */
    public function login($phoneNum, $password) {
        $userInfo = User_Model::getInstance()->authUser($phoneNum, $password);
        if(!$userInfo) {
            throw new \Exception('phone or password error', Global_ErrorCode_User::USER_LOGIN_FAILED);
        }

        // 登录成功, 以uid为基础生成会话token
        $token = $this->createToken($userInfo['userId'], self::USER_SESSION_EXPIRED_TIME);
        setcookie("dd_token", $token);

        return true;
    }


    /**
     * 重置密码
     * @param $phoneNum
     * @param $password
     * @return bool
     * @throws Exception
     */
    public function resetPwd($phoneNum, $password) {
        // 用户是否存在
        if(!User_Model::getInstance()->isPhoneExists($phoneNum)) {
            throw new \Exception('phone is not register', Global_ErrorCode_Common::USER_NOT_EXISTS);
        }

        // 随机密码字符串
        $str = "abcdefghijklmnopqrestuvwxyz1234567890ABCDEFGHIJKLMNOPQRESTUVWXYZ";
        $pwdStr = substr(str_shuffle($str), 0, 6);
        $password = md5($password . $pwdStr);
        if(!User_Model::getInstance()->changePwd($phoneNum, $password, $pwdStr)) {
            throw new \Exception('change password failed', Global_ErrorCode_Common::USER_CHANGE_PWD_FAILED);
        }

        return true;
    }


    /**
     * 加密数据
     * @param $msg
     * @param string $key
     * @param int $expireTime
     * @return string
     * @throws Exception
     */
    public static function createToken($msg, $expireTime = 0, $key = self::TOKEN_SECRET_KEY) {
        if(!$msg || !$key) {
            throw new \Exception('params error', Global_ErrorCode_Common::COMMON_PARAMS_ERROR);
        }

        $endTime 	= $expireTime === 0 ? 0 : time() + $expireTime;
        $content 	= $msg . self::TOKEN_CONTENT_SEPARATOR . $endTime;
        $md5 		= md5(md5($content));
        $content 	= $content . self::TOKEN_CONTENT_SEPARATOR . $md5;
        $content 	= self::mcrypt($content,$key);
        $token 		= base64_encode($content);

        return $token;
    }

    /**
     * 解密数据
     * @param $token
     * @param string $key
     * @param null $endTime
     * @return string
     * @throws Exception
     */
    public static function validateToken($token, $endTime = null, $key = self::TOKEN_SECRET_KEY)
    {
        if(!$token || !$key) {
            throw new \Exception('params error', Global_ErrorCode_Common::COMMON_PARAMS_ERROR);
        }

        if(is_null($endTime)){
            $endTime = time();
        }

        // 1.decode base64
        $content = base64_decode($token);
        // 2.decode crypt
        $content = self::demcrypt($content, $key);
        // 3.check endTime
        list($msg, $met, $md5) = explode(self::TOKEN_CONTENT_SEPARATOR,$content);
        if($met != 0 && $met < $endTime){
            Bingo_Log::notice("token is expired");
            return false;
        }
        // 4. check md5 of msg
        if($md5 != md5(md5($msg.self::TOKEN_CONTENT_SEPARATOR.$met))){
            Bingo_Log::notice("token is invalid");
            return false;
        }

        return $msg;
    }

    /**
     * 数据按位异或
     * @param $msg
     * @param $key
     * @return string
     */
    private static function mcrypt($msg, $key)
    {
        $key = md5($key);
        $cipherText = '';
        $mLen	= strlen($msg);
        $kLen	= strlen($key);
        $k = 0;
        for($i=0; $i<$mLen; $i++){
            $m = substr($msg, $i, 1);
            $s = substr($key,$k,1);
            $c = $m ^ $s;
            $cipherText  .= $c;
            $k = ($k+1) % $kLen;
        }

        return $cipherText;
    }

    /**
     * 将异或后的数据再次异或, 还原数据内容
     * @param $cipherText
     * @param $key
     * @return string
     */
    private static function demcrypt($cipherText,$key)
    {
        $key=md5($key);
        $msg='';
        $ctLen	=strlen($cipherText);
        $kLen	=strlen($key);
        $k=0;
        for($i=0;$i<$ctLen; $i++){
            $c = substr($cipherText,$i,1);
            $s = substr($key,$k,1);
            $m = $c ^ $s;
            $msg .= $m;
            $k = ($k+1) % $kLen;
        }

        return $msg;
    }
}