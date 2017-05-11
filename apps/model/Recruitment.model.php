<?php

/**
 * Class Recruitment_Model
 */
class Recruitment_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'recruitment';

    /**
     * @return Recruitment_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public function createRecruit($params) {
        $fields = [
            'infoId' => $params['infoId'],
            'salary' => $params['salary'],
            'salaryType'  => $params['salaryType'],
            'number'      => $params['number'],
            'startDate'   => $params['startDate'],
            'endDate'     => $params['endDate'],
            'startTime'   => $params['startTime'],
            'endTime'     => $params['endTime'],
            'createdTime' => $params['createdTime'],
        ];
        $res = $this->insert($this->table, $fields);

        return $res ? true : false;
    }

    public function getRecruitInfo($infoId) {
        $fields = [
            'infoId',
            'salary',
            'salaryType',
            'number',
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

    public function delRecruit($infoId) {
        $cond = [
            "infoId = " => $infoId,
        ];
        $res = $this->delete($this->table, $cond);

        return $res ? true : false;
    }
}