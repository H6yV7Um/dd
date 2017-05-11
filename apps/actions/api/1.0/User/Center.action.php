<?php

class User_Center_Action extends Global_Action_Base {
    protected static $getPubListParamsRule = [
        [
            'key'     => 'type',
            'default' => 'recruit',
            'func'    => 'strval',
            'regex'   => '/^(recruit|travel|edu)$/',
            'method'  => 'get',
        ],
        [
            'key'     => 'page',
            'default' => '1',
            'func'    => 'intval',
            'regex'   => '/^\d+$/',
            'method'  => 'get',
        ],
    ];
    public function getPubList() {
        try {
            $this->_checkParamsV2(self::$getPubListParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();

            $pubList = User_Center_Service::getInstance()->getPubList($loginUserId, $this->get['type'], $this->get['page']);

            $this->endWithResponseJson($pubList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}