<?php

/**
 * Class Recruitment_Index_Service
 */
class Recruitment_Index_Service extends Global_Service_Base {
    /**
     * @return Recruitment_Index_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param $params
     */
    public function createRecruit($userId, $params) {
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

        // create recruitment
        $recruitData = [
            'infoId' => $lastInsertId,
            'salary' => number_format($params['salary'], 1) * 10,
            'salaryType'  => $params['salaryType'],
            'number'      => $params['number'],
            'startDate'   => $params['startDate'],
            'endDate'     => $params['endDate'],
            'startTime'   => $params['startTime'],
            'endTime'     => $params['endTime'],
            'createdTime' => $currTime,
        ];
        $res = Recruitment_Model::getInstance()->createRecruit($recruitData);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('create recruit data failed', Global_ErrorCode_Common::RECRUIT_CREATE_RECRUIT_DATA_FAILED);
        }

        Information_Model::getInstance()->commit();

        return true;
    }

    /**
     * 获取兼职列表
     * @param $catId
     * @param $page
     * @return array
     */
    public function getRecruitList($catId, $page) {
        $recruitList = Information_Model::getInstance()->getBaseInfoList($catId, $page);
        if(!$recruitList) {
            return [];
        }

        foreach($recruitList as &$value) {
            $userInfo = User_Model::getInstance()->getUserInfo($value['userId']);
            $value['username'] = $userInfo ? $userInfo['username'] : "";
            $catInfo  = Category_Model::getInstance()->getCatInfo($value['catId']);
            $value['catName'] = $catInfo['catName'];
            $value['address'] = Information_Model::getInstance()->getAddressByCode($value['address']);
        }

        return $recruitList;
    }

    /**
     * 获取兼职详情
     * @param $infoId
     * @return array
     */
    public function getRecruitInfo($infoId) {
        $baseInfo = Information_Model::getInstance()->getBaseInfo($infoId);
        $recruitInfo = Recruitment_Model::getInstance()->getRecruitInfo($infoId);
        if(!$baseInfo || !$recruitInfo) {
            return [];
        }

        $salaryType = [
            '0' => '面议',
            '1' => '小时',
            '2' => '日',
            '3' => '周',
            '4' => '月',
            '5' => '年',
        ];
        if($recruitInfo['salaryType']) {
            $recruitInfo['price'] = $recruitInfo['salary'] / 10 . "元/" . $salaryType[$recruitInfo['salaryType']];
        } else {
            $recruitInfo['price'] = "面议";
        }

        $catInfo  = Category_Model::getInstance()->getCatInfo($baseInfo['catId']);
        $recruitInfo['catName'] = $catInfo['catName'];

        return array_merge($baseInfo, $recruitInfo);
    }

    public function delRecruit($userId, $infoId) {
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

        $res = Recruitment_Model::getInstance()->delRecruit($infoId);
        if(!$res) {
            Information_Model::getInstance()->rollBack();
            throw new \Exception('del recruit failed', Global_ErrorCode_Common::RECRUIT_DELETE_RECRUIT_FAILED);
        }

        Information_Model::getInstance()->commit();

        return true;
    }
}