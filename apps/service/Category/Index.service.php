<?php

/**
 * Class Category_Index_Service
 */
class Category_Index_Service extends Global_Service_Base {
    const CAT_RECRUIT = 'recruit';
    const CAT_TRAVEL  = 'travel';
    const CAT_EDU     = 'edu';
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
     * @param $type
     * @return array|bool
     */
    public function getCatList($type) {
        if(!$type) {
            return false;
        }
        $catMap = [
            'recruit' => 1,
            'travel'  => 2,
            'edu'     => 3,
        ];
        $parentId = $catMap[$type];

        return Category_Model::getInstance()->getCatList($parentId);
    }
}