<?php

/**
 * Class User_Center_Service
 */
class User_Center_Service extends Global_Service_Base {
    /**
     * @return User_Center_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public function getPubList($userId, $type, $page) {
        $catMap = [
            'recruit' => 1,
            'travel'  => 2,
            'edu'     => 3,
        ];
        $parentId = $catMap[$type];
        $catList  = Category_Model::getInstance()->getCatList($parentId);
        $catIds   = $this->array_column($catList, 'catId');

        $pubList  = Information_Model::getInstance()->getPubList($userId, $catIds, $page);

        return $pubList ?: [];
    }

    public function getCollList($userId, $type, $page) {
        $catMap = [
            'recruit' => 1,
            'travel'  => 2,
            'edu'     => 3,
        ];
        $parentId = $catMap[$type];
        $catList  = Category_Model::getInstance()->getCatList($parentId);
        $catIds   = $this->array_column($catList, 'catId');

        $collList = Collection_Model::getInstance()->getCollListByCatIds($userId, $catIds, $page);
        if(!$collList) {
            return [];
        }

        $infoIds = $this->array_column($collList, 'infoId');
        $collInfoList = Information_Model::getInstance()->getBaseInfoListByIds($infoIds);
        
        return $collInfoList ?: [];
    }
}