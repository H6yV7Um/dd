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

    protected static $collParamsRule = [
        [
            'key'     => 'infoId',
            'func'    => 'intval',
            'regex'   => '/^\d+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'catId',
            'func'    => 'intval',
            'regex'   => '/^\d+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'type',
            'default' => '1',
            'func'    => 'intval',
            'regex'   => '/^(0|1)$/',
            'method'  => 'post',
        ],
    ];

    protected static $getCollListParamsRule = [
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

    public function profile() {
        try {
            $loginUserId = User_Pass_Service::getLoginUserId();
            $userInfo = User_Model::getInstance()->getUserInfo($loginUserId);

            $this->endWithResponseJson($userInfo);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    public function getPubList() {
        try {
            $this->_checkParamsV2(self::$getPubListParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();

            $pubList = User_Center_Service::getInstance()->getPubList($loginUserId, $this->get['type'], $this->get['page']);
            foreach($pubList as &$pubInfo) {
                $pubInfo['content'] = $pubInfo['title'];
            }
            unset($pubInfo);

            $this->endWithResponseJson($pubList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    public function coll() {
        try {
            $this->_checkParamsV2(self::$collParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();
            
            $infoId = $this->post['infoId'];
            $catId  = $this->post['catId'];
            $type   = $this->post['type'];
            $baseInfo = Information_Model::getInstance()->getBaseInfo($infoId);
            if(!$baseInfo || $baseInfo['catId'] != $catId) {
                throw new \Exception('info is not exist', Global_ErrorCode_Common::INFORMATION_INFO_NOT_EXISTS);
            }

            $collInfo = Collection_Model::getInstance()->getCollInfo($loginUserId, $infoId, $catId);

            if($type) {
                // 收藏
                if($collInfo) {
                    throw new \Exception('already collected', Global_ErrorCode_Common::USER_ALREADY_COLL);
                }

                $res = Collection_Model::getInstance()->createColl($loginUserId, $infoId, $catId);
                if(!$res) {
                    throw new \Exception('coll failed', Global_ErrorCode_Common::USER_COLL_FAILED);
                }
            } else {
                // 取消收藏
                if(!$collInfo) {
                    throw new \Exception('have not collected', Global_ErrorCode_Common::USER_NOT_COLL);
                }

                $res = Collection_Model::getInstance()->delColl($loginUserId, $infoId, $catId);
                if(!$res) {
                    throw new \Exception('del coll failed', Global_ErrorCode_Common::USER_DEL_COLL_FAILED);
                }
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");
            
            $this->endWithResponseJson();   
        }
    }

    public function getCollList() {
        try {
            $this->_checkParamsV2(self::$getCollListParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();

            $collList = User_Center_Service::getInstance()->getCollList($loginUserId, $this->get['type'], $this->get['page']);
            foreach($collList as &$collInfo) {
                $collInfo['content'] = $collInfo['title'];
            }
            unset($collInfo);

            $this->endWithResponseJson($collList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}