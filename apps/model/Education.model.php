<?php

/**
 * Class Education_Model
 */
class Education_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'education';

    /**
     * @return Education_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public function createEdu($params) {
        $fields = [
            'infoId' => $params['infoId'],
            'price' => $params['price'],
            'startDate' => $params['startDate'],
            'endDate' => $params['endDate'],
            'startTime' => $params['startTime'],
            'endTime' => $params['endTime'],
            'createdTime' => $params['createdTime'],
        ];
        $res = $this->insert($this->table, $fields);

        return $res ? true : false;
    }

    public function getEduInfo($infoId) {
        $fields = [
            'infoId',
            'price',
            'startDate',
            'endDate',
            'startTime',
            'endTime',
            'createdTime',
        ];

        $cond = [
            "infoId = " => $infoId,
        ];
        $res = $this->selectOne($this->table, $fields, $cond);

        return $res ?: [];
    }

    public function delEdu($infoId) {
        $cond = [
            "infoId = " => $infoId,
        ];
        $res = $this->delete($this->table, $cond);

        return $res ? true : false;
    }
}