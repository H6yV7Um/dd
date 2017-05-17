<?php

/**
 * Class Comment_Model
 */
class Comment_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'comment';

    /**
     *
     */
    const COMM_PAGE_SIZE = 15;

    /**
     * @return Comment_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param $userId
     * @param $infoId
     * @param $content
     * @return bool
     */
    public function createComm($userId, $infoId, $content) {
        $fields = [
            'userId' => $userId,
            'infoId' => $infoId,
            'content' => $content,
            'createdTime' => time(),
        ];
        $res = $this->insert($this->table, $fields);

        return $res ? true : false;
    }

    /**
     * @param $infoId
     * @param $page
     * @return array
     */
    public function getCommList($infoId, $page) {
        $fields = [
            'userId',
            'infoId',
            'content',
            'createdTime',
        ];
        $cond = [
            "infoId = " => $infoId,
        ];
        $perPage = self::COMM_PAGE_SIZE;
        $offset  = ($page - 1) * $perPage;
        $append  = "ORDER BY createdTime DESC LIMIT $offset, $perPage";
        $res     = $this->select($this->table, $fields, $cond, $append);

        return $res ?: [];
    }
}