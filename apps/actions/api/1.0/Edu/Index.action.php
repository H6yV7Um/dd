<?php

/**
 * Class Edu_Index_Action
 */
class Edu_Index_Action extends Global_Action_Base {
    /**
     * @var array
     */
    protected static $publishParamsRule = [
        [
            'key'     => 'catId',
            'func'    => 'intval',
            'regex'   => '/^\d+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'title',
            'func'    => 'strval',
            'regex'   => '/^.+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'content',
            'func'    => 'strval',
            'regex'   => '/^.+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'demand',
            'func'    => 'strval',
            'regex'   => '/^.+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'extra',
            'default' => '',
            'func'    => 'strval',
            'regex'   => '/^.*$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'contact',
            'func'    => 'strval',
            'regex'   => '/^.+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'phoneNum',
            'func'    => 'strval',
            'regex'   => '/^1\d{10}$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'address',
            'method'  => 'post',
        ],
        [
            'key'     => 'detailAddress',
            'method'  => 'post',
        ],
        [
            'key'     => 'price',
            'method'  => 'post',
        ],
        [
            'key'     => 'startDate',
            'method'  => 'post',
        ],
        [
            'key'     => 'endDate',
            'method'  => 'post',
        ],
        [
            'key'     => 'startTime',
            'method'  => 'post',
        ],
        [
            'key'     => 'endTime',
            'method'  => 'post',
        ],
    ];

    protected static $getEduListParamsRule = [
        [
            'key'     => 'catId',
            'func'    => 'intval',
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

    protected static $getEduInfoParamsRule = [
        [
            'key'     => 'infoId',
            'func'    => 'strval',
            'regex'   => '/^\d+$/',
            'method'  => 'get',
        ],
    ];

    protected static $deleteParamsRule = [
        [
            'key'     => 'infoId',
            'func'    => 'strval',
            'regex'   => '/^\d+$/',
            'method'  => 'post',
        ],
    ];

    /**
     * 处理发布行为
     */
    public function publish() {
        try {
            $this->_checkParamsV2(self::$publishParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();
            // 上传图片
            $image = File_Upload_Service::getInstance()->uploadImg($_FILES['image']);
            $this->post['image'] = $image;

            $res = Education_Index_Service::getInstance()->createEdu($loginUserId, $this->post);
            if(!$res) {
                throw new \Exception('create edu failed', Global_ErrorCode_Common::RECRUIT_CREATE_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 列表
     */
    public function getEduList() {
        try {
            $this->_checkParamsV2(self::$getEduListParamsRule);

            $catId = $this->get['catId'];
            $page  = $this->get['page'];
            $eduList = Education_Index_Service::getInstance()->getEduList($catId, $page);

            $this->endWithResponseJson($eduList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 详情
     */
    public function getEduInfo() {
        try {
            $this->_checkParamsV2(self::$getEduInfoParamsRule);

            $eduInfo = Education_Index_Service::getInstance()->getEduInfo($this->get['infoId']);

            $this->endWithResponseJson($eduInfo);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 删除
     */
    public function delete() {
        try {
            $this->_checkParamsV2(self::$deleteParamsRule);

            $loginUserId = User_Pass_Service::getLoginUserId();

            $res = Education_Index_Service::getInstance()->delEdu($loginUserId, $this->post['infoId']);
            if(!$res) {
                throw new \Exception('del edu failed', Global_ErrorCode_Common::RECRUIT_DELETE_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}