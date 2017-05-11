<?php

/**
 * Class Category_Index_Service
 */
class Category_Index_Service extends Global_Service_Base {
    /**
     * @return Category_Index_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * Category_Index_Service constructor.
     */
    protected function __construct() {
    }

    /**
     * @param $parentId
     * @return array|bool
     */
    public function getCatList($parentId) {
        if(!$parentId) {
            return false;
        }

        return Category_Model::getInstance()->getCatList($parentId);
    }
}