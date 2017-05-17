<?php

/**
 * Class Education_Index_Service
 */
class Education_Index_Service extends Global_Service_Base {
    /**
     * @return Education_Index_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param $params
     */
    public function createEdu($userId, $params) {
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
            'address'     => $params['address'],
            'detailAddress' => $params['detailAddress'],
            'contact'     => $params['contact'],
            'phoneNum'    => $params['phoneNum'],
            'createdTime' => $currTime,
        ];
        $lastInsertId = Information_Model::getInstance()->createBase($baseData);
        if(!$lastInsertId) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('create info failed', Global_ErrorCode_Common::RECRUIT_CREATE_BASE_DATA_FAILED);
        }

        // create education
        $eduData = [
            'infoId' => $lastInsertId,
            'price' => $params['price'],
            'startDate' => $params['startDate'],
            'endDate' => $params['endDate'],
            'startTime' => $params['startTime'],
            'endTime' => $params['endTime'],
            'createdTime' => $currTime,
        ];

        $res = Education_Model::getInstance()->createEdu($eduData);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('create edu data failed', Global_ErrorCode_Common::RECRUIT_CREATE_RECRUIT_DATA_FAILED);
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
    public function getEduList($catId, $page) {
        $eduList = Information_Model::getInstance()->getBaseInfoList($catId, $page);
        if(!$eduList) {
            return [];
        }

        foreach($eduList as &$value) {
            $userInfo = User_Model::getInstance()->getUserInfo($value['userId']);
            $value['username'] = $userInfo ? $userInfo['username'] : "";
            $catInfo  = Category_Model::getInstance()->getCatInfo($value['catId']);
            $value['catName'] = $catInfo['catName'];
            $value['address'] = Information_Model::getInstance()->getAddressByCode($value['address']);
        }

        return $eduList;
    }

    /**
     * 获取兼职详情
     * @param $infoId
     * @return array
     */
    public function getEduInfo($infoId) {
        $baseInfo = Information_Model::getInstance()->getBaseInfo($infoId);
        $baseInfo['address'] = Information_Model::getInstance()->getAddressByCode($baseInfo['address']);

        $eduInfo = Education_Model::getInstance()->getEduInfo($infoId);
        if(!$baseInfo || !$eduInfo) {
            return [];
        }

        $catInfo  = Category_Model::getInstance()->getCatInfo($baseInfo['catId']);
        $eduInfo['catName'] = $catInfo['catName'];

        return array_merge($baseInfo, $eduInfo);
    }

    public function delEdu($userId, $infoId) {
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

        $res = Education_Model::getInstance()->delEdu($infoId);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('del edu failed', Global_ErrorCode_Common::RECRUIT_DELETE_RECRUIT_FAILED);
        }

        Information_Model::getInstance()->commit();

        return true;
    }
}