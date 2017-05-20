<?php

/**
 * Class Travel_Index_Service
 */
class Travel_Index_Service extends Global_Service_Base {
    /**
     * @return Travel_Index_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param $params
     */
    public function createTravel($userId, $params) {
        $currTime = time();
        Information_Model::getInstance()->startTransaction();

        // create info
        $baseData = [
            'catId'       => $params['catId'],
            'userId'      => $userId,
            'title'       => $params['title'],
            'image'       => $params['image'],
            'content'     => $params['content'],
            'demand'      => $params['demand'],
            'extra'       => $params['extra'],
            'address'     => $params['address'] ?: 0,
            'detailAddress' => $params['detailAddress'] ?: "",
            'contact'     => $params['contact'],
            'phoneNum'    => $params['phoneNum'],
            'createdTime' => $currTime,
        ];
        $lastInsertId = Information_Model::getInstance()->createBase($baseData);
        if(!$lastInsertId) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('create info failed', Global_ErrorCode_Common::RECRUIT_CREATE_BASE_DATA_FAILED);
        }

        // create travel
        $travelData = [
            'infoId' => $lastInsertId,
            'price' => $params['price'],
            'hotelType' => $params['hotelType'],
            'startDate' => $params['startDate'],
            'endDate' => $params['endDate'],
            'startPlace' => $params['startPlace'],
            'endPlace' => $params['endPlace'],
            'goVehicle' => $params['goVehicle'],
            'backVehicle' => $params['backVehicle'],
            'createdTime' => $currTime,
        ];
        $res = Travel_Model::getInstance()->createTravel($travelData);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('create travel data failed', Global_ErrorCode_Common::RECRUIT_CREATE_RECRUIT_DATA_FAILED);
        }

        Information_Model::getInstance()->commit();

        return $lastInsertId;
    }

    /**
     * 获取兼职列表
     * @param $catId
     * @param $page
     * @return array
     */
    public function getTravelList($catId, $page) {
        $travelList = Information_Model::getInstance()->getBaseInfoList($catId, $page);
        if(!$travelList) {
            return [];
        }

        foreach($travelList as &$value) {
            $userInfo = User_Model::getInstance()->getUserInfo($value['userId']);
            $value['username'] = $userInfo ? $userInfo['username'] : "";
            $catInfo  = Category_Model::getInstance()->getCatInfo($value['catId']);
            $value['catName'] = $catInfo['catName'];
            $value['address'] = Information_Model::getInstance()->getAddressByCode($value['address']);
        }

        return $travelList;
    }

    /**
     * 获取兼职详情
     * @param $infoId
     * @return array
     */
    public function getTravelInfo($infoId) {
        $baseInfo = Information_Model::getInstance()->getBaseInfo($infoId);
        // 收藏
        $loginUserId = User_Pass_Service::getLoginUserId();
        $baseInfo['isColl'] = Collection_Model::getInstance()->isColl($loginUserId, $infoId) ? "1" : "0";
        
        $baseInfo['address'] = Information_Model::getInstance()->getAddressByCode($baseInfo['address']);

        $travelInfo = Travel_Model::getInstance()->getTravelInfo($infoId);
        if(!$baseInfo || !$travelInfo) {
            return [];
        }

        $hotelType = [
            '1' => '露营',
            '2' => '农家乐',
            '3' => '旅社',
            '4' => '宾馆',
        ];
        $travelInfo['hotelType'] = $hotelType[$travelInfo['hotelType']];
        $vehicle = [
            '1' => '大巴',
            '2' => '火车',
            '3' => '飞机',
            '4' => '轮船',
        ];
        $travelInfo['goVehicle'] = $vehicle[$travelInfo['goVehicle']];
        $travelInfo['backVehicle'] = $vehicle[$travelInfo['backVehicle']];

        $catInfo  = Category_Model::getInstance()->getCatInfo($baseInfo['catId']);
        $travelInfo['catName'] = $catInfo['catName'];

        return array_merge($baseInfo, $travelInfo);
    }

    public function delTravel($userId, $infoId) {
        Information_Model::getInstance()->startTransaction();

        $res = Information_Model::getInstance()->getBaseInfo($infoId);
        if(!$res) {
            throw new \Exception('info is not exists', Global_ErrorCode_Common::INFORMATION_INFO_NOT_EXISTS);
        }

        $res = Information_Model::getInstance()->delBase($userId, $infoId);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('del base failed', Global_ErrorCode_Common::RECRUIT_DELETE_BASE_FAILED);
        }

        $res = Travel_Model::getInstance()->delTravel($infoId);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('del travel failed', Global_ErrorCode_Common::RECRUIT_DELETE_RECRUIT_FAILED);
        }

        Information_Model::getInstance()->commit();

        return true;
    }
}