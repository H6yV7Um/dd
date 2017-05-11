<?php

class Recruit_Index_Action extends Global_Action_WebBase {
    public function publish() {
        // 兼职分类
        $catList = Category_Index_Service::getInstance()->getCatList(Category_Index_Service::CAT_RECRUIT);
        $this->tplData['catList'] = $catList;

        $this->display('Recruit/publish.tpl');
    }
}