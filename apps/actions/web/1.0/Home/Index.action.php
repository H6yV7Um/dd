<?php

class Home_Index_Action extends Global_Action_WebBase {
    public function index() {
        $this->tplData = [
            'name' => "lovyhui",
            'age'  => 23,
        ];
        $this->display("index/index.tpl");
    }
}