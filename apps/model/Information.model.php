<?php

/**
 * Class Information_Model
 */
class Information_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'information';

    const INFO_PER_PAGE_SIZE = '5';

    /**
     * @return Information_Model
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * @param $params
     */
    public function createBase($params) {
        $fields = [
            'catId'       => $params['catId'],
            'userId'      => $params['userId'],
            'title'       => $params['title'],
            'image'       => $params['image'],
            'content'     => $params['content'],
            'demand'      => $params['demand'],
            'extra'       => $params['extra'],
            'address'     => $params['address'],
            'detailAddress' => $params['detailAddress'],
            'contact'     => $params['contact'],
            'phoneNum'    => $params['phoneNum'],
            'createdTime' => time(),
        ];
        $res = $this->insert($this->table, $fields);
        if(!$res) {
            Bingo_Log::warning("insert failed. table: {$this->table} fields: " . json_encode($fields));
            return false;
        }

        return $this->getLastInsertId();
    }

    /**
     * 获取兼职列表
     * @param $catId
     * @param $page
     */
    public function getBaseInfoList($catId, $page) {
        $fields = [
            'infoId',
            'catId',
            'userId',
            'title',
            'image',
            'content',
            'demand',
            'extra',
            'address',
            'detailAddress',
            'contact',
            'phoneNum',
            'viewCnt',
            'commCnt',
            'createdTime',
        ];
        $cond = [
            "catId = " => $catId,
        ];
        $perPage = self::INFO_PER_PAGE_SIZE;
        $offset = ($page - 1) * $perPage;
        $append = "ORDER BY createdTime DESC LIMIT $offset, $perPage";
        $res = $this->select($this->table, $fields, $cond, $append);

        if(!$res) {
            return [];
        }

        foreach($res as &$value) {
            $catInfo = Category_Model::getInstance()->getCatInfo($value['catId']);
            if(!$catInfo) {
                continue;
            }

            switch($catInfo['parentId']) {
                case 1:
                    // recruit
                    $salaryType = [
                        '0' => '面议',
                        '1' => '小时',
                        '2' => '日',
                        '3' => '周',
                        '4' => '月',
                        '5' => '年',
                    ];
                    $addInfo = Recruitment_Model::getInstance()->getRecruitInfo($value['infoId']);
                    if($addInfo['salaryType']) {
                        $addInfo['price'] = $addInfo['salary'] / 10 . "/" . $salaryType[$addInfo['salaryType']];
                    } else {
                        $addInfo['price'] = "面议";
                    }
                    break;
                case 2:
                    // travel
                    $addInfo = Travel_Model::getInstance()->getTravelInfo($value['infoId']);
                    break;
                case 3:
                    // edu
                    $addInfo    = Education_Model::getInstance()->getEduInfo($value['infoId']);
                    break;
                default:
                    $addInfo['price'] = "--";
            }

            $value['price'] = $addInfo['price'];
        }
        unset($value);

        return $res ?: [];
    }

    public function getBaseInfoListByCatIds($catIds) {
        $fields = [
            'infoId',
            'catId',
            'userId',
            'title',
            'image',
            'content',
            'demand',
            'extra',
            'address',
            'detailAddress',
            'contact',
            'phoneNum',
            'viewCnt',
            'commCnt',
            'createdTime',
        ];

        $strIds = implode(',', $catIds);
        $cond = [
            "catId IN ($strIds)",
        ];
        $append = "ORDER BY createdTime DESC";
        $res = $this->select($this->table, $fields, $cond, $append);
        if(!$res) {
            return [];
        }

        foreach($res as &$value) {
            $catInfo = Category_Model::getInstance()->getCatInfo($value['catId']);
            if(!$catInfo) {
                continue;
            }

            switch($catInfo['parentId']) {
                case 1:
                    // recruit
                    $salaryType = [
                        '0' => '面议',
                        '1' => '小时',
                        '2' => '日',
                        '3' => '周',
                        '4' => '月',
                        '5' => '年',
                    ];
                    $addInfo = Recruitment_Model::getInstance()->getRecruitInfo($value['infoId']);
                    if($addInfo['salaryType']) {
                        $addInfo['price'] = $addInfo['salary'] / 10 . "/" . $salaryType[$addInfo['salaryType']];
                    } else {
                        $addInfo['price'] = "面议";
                    }
                    break;
                case 2:
                    // travel
                    $addInfo = Travel_Model::getInstance()->getTravelInfo($value['infoId']);
                    break;
                case 3:
                    // edu
                    $addInfo    = Education_Model::getInstance()->getEduInfo($value['infoId']);
                    break;
                default:
                    $addInfo['price'] = "--";
            }

            $value['price'] = $addInfo['price'];
        }
        unset($value);

        return $res ?: [];
    }


    public function getBaseInfoListByIds($infoIds) {
        $fields = [
            'infoId',
            'catId',
            'userId',
            'title',
            'image',
            'content',
            'demand',
            'extra',
            'address',
            'detailAddress',
            'contact',
            'phoneNum',
            'viewCnt',
            'commCnt',
            'createdTime',
        ];
        
        $strIds = implode(',', $infoIds);
        $cond = [
            "infoId IN ($strIds)",
        ];

        // todo 应该按照收藏时间排序, 这里先按发布时间排序了
        $append = "ORDER BY createdTime DESC";
        $baseInfoList = $this->select($this->table, $fields, $cond, $append);

        return $baseInfoList ?: [];
    }

    /**
     * 获取兼职/旅游/教育基础信息
     * @param $infoId
     * @return array
     */
    public function getBaseInfo($infoId) {
        $fields = [
            'infoId',
            'catId',
            'userId',
            'title',
            'image',
            'content',
            'demand',
            'extra',
            'address',
            'detailAddress',
            'contact',
            'phoneNum',
            'viewCnt',
            'commCnt',
            'createdTime',
        ];
        $cond = [
            "infoId = " => $infoId,
        ];
        $res = $this->selectOne($this->table, $fields, $cond);

        return $res ?: [];
    }

    /**
     * 删除base
     * @param $userId
     * @param $infoId
     * @return bool
     */
    public function delBase($userId, $infoId) {
        $cond = [
            "userId = " => $userId,
            "infoId = " => $infoId,
        ];
        $res = $this->delete($this->table, $cond);

        return $res ? true : false;
    }

    /**
     * 根据地区码获取省-市-区
     * @param $code
     * @return string
     */
    public function getAddressByCode($code) {
        if(!is_numeric($code)) {
            // 如果address不是地区码, 那么直接返回
            return $code;
        }

        $sql = "select p.pname, c.cname, a.aname from province as p left join city as c on p.pcode = c.provinceCode left join areaCounty as a on c.ccode = a.cityCode where acode = $code";
        $res = $this->queryBySql($sql);

        if(!$res) {
            return "";
        }

        $p = $res[0]['pname'];
        $c = $res[0]['cname'];
        $a = $res[0]['aname'];
        return "$p-$c-$a";
    }

    public function incrViewCnt($infoId) {
        $sql = "update {$this->table} set viewCnt = viewCnt + 1 where infoId = $infoId";
        $res = $this->queryBySql($sql);

        return $res ? true : false;
    }

    public function incrCommCnt($infoId) {
        $sql = "update {$this->table} set commCnt = commCnt + 1 where infoId = $infoId";
        $res = $this->queryBySql($sql);

        return $res ? true : false;
    }

    public function getPubList($userId, $catIds, $page) {
        $fields = [
            'infoId',
            'catId',
            'userId',
            'title',
            'image',
            'content',
            'demand',
            'extra',
            'address',
            'detailAddress',
            'contact',
            'phoneNum',
            'createdTime',
        ];
        $strIds = implode(',', $catIds);
        $cond = [
            "userId = " => $userId,
            "catId IN ($strIds)",
        ];

        $perPage = self::INFO_PER_PAGE_SIZE;
        $offset = ($page - 1) * $perPage;
        $append = "ORDER BY createdTime DESC LIMIT $offset, $perPage";

        $pubList = $this->select($this->table, $fields, $cond, $append);

        return $pubList ?: [];
    }

    public function getRecList($page = 1) {
        $fields = [
            'infoId',
            'catId',
            'userId',
            'title',
            'image',
            'content',
            'demand',
            'extra',
            'address',
            'detailAddress',
            'contact',
            'phoneNum',
            'viewCnt',
            'commCnt',
            'createdTime',
        ];
        $cond = [
            "isRec = " => 1,
        ];

        $perPage = self::INFO_PER_PAGE_SIZE;
        $offset = ($page - 1) * $perPage;
        $append = "ORDER BY updatedTime DESC LIMIT $offset, $perPage";
        $res = $this->select($this->table, $fields, $cond, $append);
        if(!$res) {
            return [];
        }

        foreach($res as &$value) {
            $catInfo = Category_Model::getInstance()->getCatInfo($value['catId']);
            if(!$catInfo) {
                continue;
            }

            switch($catInfo['parentId']) {
                case 1:
                    // recruit
                    $salaryType = [
                        '0' => '面议',
                        '1' => '小时',
                        '2' => '日',
                        '3' => '周',
                        '4' => '月',
                        '5' => '年',
                    ];
                    $addInfo = Recruitment_Model::getInstance()->getRecruitInfo($value['infoId']);
                    if($addInfo['salaryType']) {
                        $addInfo['price'] = $addInfo['salary'] / 10 . "/" . $salaryType[$addInfo['salaryType']];
                    } else {
                        $addInfo['price'] = "面议";
                    }
                    break;
                case 2:
                    // travel
                    $addInfo = Travel_Model::getInstance()->getTravelInfo($value['infoId']);
                    break;
                case 3:
                    // edu
                    $addInfo    = Education_Model::getInstance()->getEduInfo($value['infoId']);
                    break;
                default:
                    $addInfo['price'] = "--";
            }

            $value['price'] = $addInfo['price'];
        }
        unset($value);

        return $res ?: [];
    }
}