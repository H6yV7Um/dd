<?php
class User_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = "user";

    /**
     * @return User_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * 创建用户
     * @param $params
     * @return bool
     */
    public function createUser($params) {
        Bingo_Log::notice("create user start. params: " . json_encode($params));
        $fields = [
            'phoneNum' => $params['phoneNum'],
            'username' => $params['username'],
            'password' => $params['password'],
            'pwdStr'   => $params['pwdStr'],
            'createdTime' => time(),
        ];

        $res = $this->insert($this->table, $fields);
        if(!$res) {
            Bingo_Log::warning("insert failed. params: " . json_encode($params));
            return false;
        }

        Bingo_Log::notice("create user end. params: " . json_encode($params));

        return true;
    }

    public function isPhoneExists($phoneNum) {
        $cond = [
            "phoneNum = " => $phoneNum,
        ];
        $res  = $this->selectCount($this->table, $cond);

        return $res ? true : false;
    }

    public function isUsernameExists($username) {
        $cond = [
            "username = " => $username,
        ];
        $res  = $this->selectCount($this->table, $cond);

        return $res ? true : false;
    }

    /**
     * 验证用户登录
     * @param $username
     * @param $password
     * @return bool|array
     */
    public function authUser($username, $password) {
        $fields = [
            "userId",
            "password",
            "username",
            "pwdStr",
            "userIcon",
            "gender",
            "birthDay",
            "phoneNum",
            "email",
            "address",
            "detailAddress",
        ];
        $cond = "phoneNum = '$username' OR username = '$username'";
        $res = $this->selectOne($this->table, $fields, $cond);
        if(!$res) {
            return false;
        }

        if(md5($password . $res['pwdStr']) != $res['password']) {
            return false;
        }

        unset($res['password']);
        unset($res['pwdStr']);
        return $res;
    }

    /**
     * 修改密码
     * @param $phoneNum
     * @param $password
     * @param $pwdStr
     * @return bool
     */
    public function changePwd($phoneNum, $password, $pwdStr) {
        $fields = [
            'phoneNum' => $phoneNum,
            'password' => $password,
            'pwdStr'   => $pwdStr,
        ];
        $cond = [
            "phoneNum = " => $phoneNum,
        ];
        $res = $this->update($this->table, $fields, $cond);

        return $res ? true : false;
    }

    /**
     * @param $userId
     * @return array
     */
    public function getUserInfo($userId) {
        $fields = [
            "userId",
            "username",
            "userIcon",
            "gender",
            "birthDay",
            "phoneNum",
            "email",
            "address",
            "detailAddress",
        ];
        $cond = [
            "userId = " => $userId,
        ];
        $res = $this->selectOne($this->table, $fields, $cond);

        return $res ?: [];
    }

    public function isRegister($type, $value) {
        $cond = [
            "$type = " => $value,
        ];
        $res = $this->selectCount($this->table, $cond);

        return $res ? true : false;
    }
}