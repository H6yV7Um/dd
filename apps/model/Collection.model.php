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
        $fields = [
            'userId' => $userId,
            'infoId' => $infoId,
            'catId'  => $catId,
        ];
        $cond = [
            'userId = ' => $userId,
            'infoId = ' => $infoId,
            'catId  = '  => $catId,
        ];
        $res = $this->selectOne($this->table, $fields, $cond);

        return $res ?: false;
    }

    public function getCollListByCatIds($userId, $catIds, $page) {
        $fields = [
            'userId',
            'infoId',
            'catId',
            'createdTime',
        ];

        $strIds = implode(',', $catIds);
        $cond   = [
            "userId = " => $userId,
            "catId IN ($strIds)",
        ];

        $perPage = Information_Model::INFO_PER_PAGE_SIZE;
        $offset = ($page - 1) * $perPage;
        $append = "ORDER BY createdTime DESC LIMIT $offset, $perPage";

        $res = $this->select($this->table, $fields, $cond, $append);

        return $res ?: [];
    }

    public function delColl($userId, $infoId, $catId) {
        $cond = [
            'userId = ' => $userId,
            'infoId = ' => $infoId,
            'catId  = '  => $catId,
        ];
        $res = $this->delete($this->table, $cond);

        return $res ? true : false;
    }
}