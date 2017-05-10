<?php

class Home_Index_Action extends Global_Action_WebBase {
    public function index() {
        $this->display("index/index.tpl");
    }
}