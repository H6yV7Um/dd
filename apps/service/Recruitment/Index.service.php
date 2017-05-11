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
        $lastInsertId = Information_Model::getInstance()->createInfo($baseData);
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
}