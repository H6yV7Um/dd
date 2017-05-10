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

    /**
     * 验证用户登录
     * @param $phoneNum
     * @param $password
     * @return bool|array
     */
    public function authUser($phoneNum, $password) {
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
        $cond = [
            "phoneNum = " => $phoneNum,
        ];
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

    public function getUserInfo() {

    }
}