<?php

/**
 * Class Edu_Index_Action
 */
class Edu_Index_Action extends Global_Action_WebBase {
    /**
     * 展示发布页
     */
    public function publish() {
        $catList = Category_Index_Service::getInstance()->getCatList(Category_Index_Service::CAT_EDU);
        $this->tplData['catList'] = $catList;

        $this->display('Edu/publish.tpl');
    }
}