<?php

/**
 * Class Recruit_Index_Action
 */
class Recruit_Index_Action extends Global_Action_WebBase {
    /**
     * 展示发布页
     */
    public function publish() {
        $catList = Category_Index_Service::getInstance()->getCatList(Category_Index_Service::CAT_RECRUIT);
        $this->tplData['catList'] = $catList;

        $this->display('Recruit/publish.tpl');
    }
}