<?php

/**
 * Class Information_Model
 */
class Information_Model extends Base_Model {
    /**
     * @var string
     */
    protected $table = 'information';

    const INFO_PER_PAGE_SIZE = '15';

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
            'createdTime',
        ];
        $cond = [
            "catId = " => $catId,
        ];
        $perPage = self::INFO_PER_PAGE_SIZE;
        $offset = ($page - 1) * $perPage;
        $append = "ORDER BY createdTime DESC LIMIT $offset, $perPage";
        $res = $this->select($this->table, $fields, $cond, $append);

        return $res ?: [];
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
}