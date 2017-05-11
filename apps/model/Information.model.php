<?php

/**
 * Class Information_Model
 */
class Information_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'information';

    /**
     * @return Information_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param $params
     */
    public function createInfo($params) {
        $fields = [
            'catId'       => $params['catId'],
            'userId'      => $params['userId'],
            'title'       => $params['title'],
            'image'       => $params['image'],
            'content'     => $params['content'],
            'demand'      => $params['demand'],
            'extra'       => $params['extra'],
            'address'     => $params['address'],
            'detailAddress' => $params['detailAddress'],
            'contact'     => $params['contact'],
            'phoneNum'    => $params['phoneNum'],
            'createdTime' => time(),
        ];
        $res = $this->insert($this->table, $fields);
        if(!$res) {
            Bingo_Log::warning("insert failed. table: {$this->table} fields: " . json_encode($fields));
            return false;
        }

        return $this->getLastInsertId();
    }
}