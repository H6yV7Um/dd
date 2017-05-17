<?php

class Comm_Index_Action extends Global_Action_Base {
    protected static $publishParamsRule = [
        [
            'key'     => 'infoId',
            'regex'   => '/^\d+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'content',
            'func'    => 'strval',
            'regex'   => '/^.+$/',
            'method'  => 'post',
        ],
    ];

    protected static $getCommListParamsRule = [
        [
            'key'     => 'infoId',
            'regex'   => '/^\d+$/',
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


    public function publish() {
        try {
            $this->_checkParamsV2(self::$publishParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();

            $res = Comment_Model::getInstance()->createComm($loginUserId, $this->post['infoId'], $this->post['content']);
            if(!$res) {
                throw new \Exception('comment failed', Global_ErrorCode_Common::COMMENT_COMM_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    public function getCommList() {
        try {
            $this->_checkParamsV2(self::$getCommListParamsRule);

            $commList = Comment_Model::getInstance()->getCommList($this->get['infoId'], $this->get['page']);
            if($commList) {
                foreach($commList as &$commInfo) {
                    $userInfo = User_Model::getInstance()->getUserInfo($commInfo['userId']);
                    $commInfo['username'] = $userInfo['username'];
                    $commInfo['userIcon'] = $userInfo['userIcon'];
                }
                unset($commInfo);
            }

            $this->endWithResponseJson($commList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}