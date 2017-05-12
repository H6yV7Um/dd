<?php

class Cat_Index_Action extends Global_Action_Base {
    protected static $getCateListParamsRule = [
        [
            'key'     => 'type',
            'default' => 'recruit',
            'func'    => 'strval',
            'regex'   => '/^(recruit|travel|edu)$/',
            'method'  => 'get',
        ],
    ];
    public function getCatList() {
        try {
            $this->_checkParamsV2(self::$getCateListParamsRule);

            $catList = Category_Index_Service::getInstance()->getCatList($this->get['type']);
            if(!$catList) {
                throw new \Exception('get cat list failed', Global_ErrorCode_Common::CATEGORY_GET_CAT_LIST_FAILED);
            }

            $this->endWithResponseJson($catList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}