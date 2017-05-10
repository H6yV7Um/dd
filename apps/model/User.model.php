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
            "PhoneNum = " => $phoneNum,
        ];
        $res  = User_Model::getInstance()->selectCount($this->table, $cond);

        return $res ? true : false;
    }
}