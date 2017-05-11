<?php

/**
 * Class Travel_Model
 */
class Travel_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'travel';

    /**
     * @return Travel_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public function createTravel($params) {
        $fields = [
            'infoId' => $params['infoId'],
            'price' => $params['price'],
            'hotelType' => $params['hotelType'],
            'startDate' => $params['startDate'],
            'endDate' => $params['endDate'],
            'startPlace' => $params['startPlace'],
            'endPlace' => $params['endPlace'],
            'goVehicle' => $params['goVehicle'],
            'backVehicle' => $params['backVehicle'],
            'createdTime' => $params['createdTime'],
        ];
        $res = $this->insert($this->table, $fields);

        return $res ? true : false;
    }

    public function getTravelInfo($infoId) {
        $fields = [
            'infoId',
            'price',
            'hotelType',
            'startDate',
            'endDate',
            'startPlace',
            'endPlace',
            'goVehicle',
            'backVehicle',
            'createdTime',
        ];
        $cond = [
            "infoId = " => $infoId,
        ];
        $res = $this->selectOne($this->table, $fields, $cond);

        return $res ?: [];
    }

    public function delTravel($infoId) {
        $cond = [
            "infoId = " => $infoId,
        ];
        $res = $this->delete($this->table, $cond);

        return $res ? true : false;
    }
}