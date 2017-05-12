<?php

/**
 * Class Home_Index_Action
 */
class Home_Index_Action extends Global_Action_Base {
    protected static $getRecListParamsRule = [
        [
            'key'     => 'page',
            'default' => '1',
            'func'    => 'intval',
            'regex'   => '/^\d+$/',
            'method'  => 'get',
        ],
    ];

    /**
     * 首页轮播图
     */
    public function getIndexList()  {
        try {
            $recList = Information_Model::getInstance()->getRecList();
            $recList = array_slice($recList, 0, 5);
            
            $this->endWithResponseJson($recList);
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
        try {
            $this->_checkParamsV2(self::$getRecListParamsRule);

            $recList = Information_Model::getInstance()->getRecList($this->get['page']);

            $this->endWithResponseJson($recList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}