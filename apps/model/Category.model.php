<?php

/**
 * Class Category_Model
 */
class Category_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'category';

    /**
     * @return Category_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param int $parentId
     * @return array
     */
    public function getCatList($parentId = 1) {
        $fields = [
            'catId',
            'catName',
            'parentId',
            'sort',
            'description',
        ];
        $cond = [
            "parentId = " => $parentId,
        ];
        $append = "ORDER BY sort DESC";
        $res = $this->select($this->table, $fields, $cond, $append);

        return $res ?: [];
    }
}