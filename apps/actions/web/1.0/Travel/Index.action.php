<?php

class Travel_Index_Action extends Global_Action_WebBase {
    /**
     * 展示发布页
     */
    public function publish() {
        $catList = Category_Index_Service::getInstance()->getCatList(Category_Index_Service::CAT_TRAVEL);
        $this->tplData['catList'] = $catList;

        $this->display('Travel/publish.tpl');
    }
}