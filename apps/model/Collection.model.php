<?php

/**
 * Class Collection_Model
 */
class Collection_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'collection';

    /**
     * @return Collection_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public function createColl($userId, $infoId, $catId) {
        $fields = [
            'userId' => $userId,
            'infoId' => $infoId,
            'catId'  => $catId,
            'createdTime' => time(),
        ];
        $res = $this->insert($this->table, $fields);

        return $res ? true : false;
    }

    public function getCollInfo($userId, $infoId, $catId) {
        $cond = [
            'userId' => $userId,
            'infoId' => $infoId,
            'catId'  => $catId,
        ];
        $res = $this->selectOne($this->table, $cond);

        return $res ?: false;
    }
}