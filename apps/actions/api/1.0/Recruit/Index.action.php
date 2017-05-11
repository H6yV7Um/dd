<?php

/**
 * Class Recruit_Index_Action
 */
class Recruit_Index_Action extends Global_Action_Base {
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
            'key'     => 'salary',
            'default' => '0',
            'func'    => 'doubleval',
            'regex'   => '/^\d+$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'salaryType',
            'default' => '0',
            'func'    => 'intval',
            'regex'   => '/^[0-5]{1}$/',
            'method'  => 'post',
        ],
        [
            'key'     => 'number',
            'func'    => 'intval',
            'regex'   => '/^\d+$/',
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
        [
            'key'     => 'address',
            'method'  => 'post',
        ],
        [
            'key'     => 'detailAddress',
            'method'  => 'post',
        ],
    ];

    protected static $getRecruitListParamsRule = [
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

    protected static $getRecruitInfoParamsRule = [
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

            $res = Recruitment_Index_Service::getInstance()->createRecruit($loginUserId, $this->post);
            if(!$res) {
                throw new \Exception('create recruit failed', Global_ErrorCode_Common::RECRUIT_CREATE_FAILED);
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
    public function getRecruitList() {
        try {
            $this->_checkParamsV2(self::$getRecruitListParamsRule);

            $catId = $this->get['catId'];
            $page  = $this->get['page'];
            $recruitList = Recruitment_Index_Service::getInstance()->getRecruitList($catId, $page);

            $this->endWithResponseJson($recruitList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 详情
     */
    public function getRecruitInfo() {
        try {
            $this->_checkParamsV2(self::$getRecruitInfoParamsRule);

            $recruitInfo = Recruitment_Index_Service::getInstance()->getRecruitInfo($this->get['infoId']);

            $this->endWithResponseJson($recruitInfo);
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

            $res = Recruitment_Index_Service::getInstance()->delRecruit($loginUserId, $this->post['infoId']);
            if(!$res) {
                throw new \Exception('del recruit failed', Global_ErrorCode_Common::RECRUIT_DELETE_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}