<?php

/**
 * Class Travel_Index_Action
 */
class Travel_Index_Action extends Global_Action_Base {
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
            'method'  => 'post',
        ],
        [
            'key'     => 'demand',
            'func'    => 'strval',
            'method'  => 'post',
        ],
        [
            'key'     => 'extra',
            'default' => '',
            'func'    => 'strval',
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
            'key'     => 'price',
            'method'  => 'post',
        ],
        [
            'key'     => 'hotelType',
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
            'key'     => 'startPlace',
            'method'  => 'post',
        ],
        [
            'key'     => 'endPlace',
            'method'  => 'post',
        ],
        [
            'key'     => 'goVehicle',
            'method'  => 'post',
        ],
        [
            'key'     => 'backVehicle',
            'method'  => 'post',
        ],
    ];

    protected static $getTravelListParamsRule = [
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

    protected static $getTravelInfoParamsRule = [
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

            $infoId = Travel_Index_Service::getInstance()->createTravel($loginUserId, $this->post);
            if(!$infoId) {
                throw new \Exception('create travel failed', Global_ErrorCode_Common::RECRUIT_CREATE_FAILED);
            }

            $this->endWithResponseJson([
                'infoId' => $infoId,
            ]);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    public function getRecList() {
        try {
            $catList = Category_Model::getInstance()->getCatList(2);
            $catIds  = array_column($catList, 'catId');
            $baseInfoList = Information_Model::getInstance()->getBaseInfoListByCatIds($catIds);
            $baseInfoList = array_slice($baseInfoList, 0, 5);

            $this->endWithResponseJson($baseInfoList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 列表
     */
    public function getTravelList() {
        try {
            $this->_checkParamsV2(self::$getTravelListParamsRule);

            $catId = $this->get['catId'];
            $page  = $this->get['page'];
            $travelList = Travel_Index_Service::getInstance()->getTravelList($catId, $page);

            $this->endWithResponseJson($travelList);
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }

    /**
     * 详情
     */
    public function getTravelInfo() {
        try {
            $this->_checkParamsV2(self::$getTravelInfoParamsRule);

            $travelInfo = Travel_Index_Service::getInstance()->getTravelInfo($this->get['infoId']);

            // 更新访问数量
            Information_Model::getInstance()->incrViewCnt($this->get['infoId']);

            $this->endWithResponseJson($travelInfo);
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

            $res = Travel_Index_Service::getInstance()->delTravel($loginUserId, $this->post['infoId']);
            if(!$res) {
                throw new \Exception('del travel failed', Global_ErrorCode_Common::RECRUIT_DELETE_FAILED);
            }

            $this->endWithResponseJson();
        } catch(Exception $exception) {
            $this->exception = $exception;
            Bingo_Log::warning("internal exception: code: {$this->exception->getCode()} msg: {$this->exception->getMessage()}");

            $this->endWithResponseJson();
        }
    }
}