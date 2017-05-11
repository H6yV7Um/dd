<?php

/**
 * Class Home_Index_Action
 */
class Home_Index_Action extends Global_Action_Base {

    /**
     * 首页轮播图
     */
    public function getIndexList()  {
        try {
            
            
            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");
            
            $this->endWithResponseJson();   
        }
    }

    /**
     * 首页推荐列表
     */
    public function getRecList() {

    }
}