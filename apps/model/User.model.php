<?php
class User_Model extends Base {
    /**
     * @var
     */
    private static $_instance;

    /**
     * @var string
     */
    protected $table = "t_User";

    /**
     * 对UserInfo_$uid的缓存特殊处理
     * @var array
     */
    protected $_map = [
        // t_User
        'userId'            => 'UserID',
        'categoryId'        => 'CategoryId',
        'passType'          => 'PassType',
        'userName'          => 'UserName',
        'userIcon'          => 'UserIcon',
        'nickName'          => 'NickName',
        'gender'            => 'Gender',
        'birthday'          => 'Birthday',
        'address'           => 'Address',
        'email'             => 'Email',
        'phone'             => 'Phone',
        'tn'                => 'Tn',
        'country'           => 'Country',
        'city'              => 'City',
        'news'              => 'News',
        'isSign'            => 'IsSign',
        'isOperationsGroup' => 'IsOperationsGroup',
        'videoName'         => 'VideoName',
        'videoUrl'          => 'VideoUrl',
        'videoDescription'  => 'VideoDescription',
        // t_UserAccount
        'balance'           => 'Balance',
        'balanceFree'       => 'Balance_free',
        'balanceIdr'        => 'Balance_idr',
        'balanceMyr'        => 'Balance_myr',
        'balanceUsd'        => 'Balance_usd',
        'revenue'           => 'Revenue',
        'totalRevenue'      => 'TotalRevenue',
        'virtualRevenue'    => 'VirtualRevenue',
        'totalConsumption'  => 'TotalConsumption',
        'wealthLevelId'     => 'AppWealthLevelId',
        //        'incomeLevelId'     => 'AppIncomeLevelId',// 改为计算,不往db中存了
        'anchorScore'       => 'AppAnchorScore',
        'userScore'         => 'AppUserScore',
        'appWealthLevelId'  => 'AppWealthLevelId',
        //        'appIncomeLevelId'  => 'AppIncomeLevelId', // 改为计算, 不往db中存了
        // t_Room
        'roomId'            => 'RoomID',
        'roomOwnerId'       => 'RoomOwnerID',
        'roomName'          => 'RoomName',
        'announcement'      => 'Announcement',
        'description'       => 'Description',
        'roomIcon'          => 'RoomIcon',
        'roomImg'           => 'RoomImg',
        'visits'            => 'Visits',
        'isBanned'          => 'IsBanned',
        'createdTime'       => 'CreateTime',
        'maxUser'           => 'MaxUser',
        'maxUserFinal'      => 'MaxUserFinal',
        'multiRoomImg'      => 'MultiRoomImg',
        'roomType'          => 'RoomType',
        // t_RecommendAnchors
        'recommendType'     => 'RecommendType',
        'recommendSorts'    => 'RecommendSorts',
        'anchorImg'         => 'AnchorImage',
        'likes'             => 'Likes',
        'liveTime'          => 'LiveTime',
        'isShow'            => 'IsNew',
        'isHot'             => 'IsHot',
        'isTop'             => 'IsTop',
        'weight'            => 'Weight',
    ];

    /**
     * @var array
     */
    protected static $userAttr = [
        'userId'            => 'UserID',
        'categoryId'        => 'CategoryId',
        'passType'          => 'PassType',
        'userName'          => 'UserName',
        'userIcon'          => 'UserIcon',
        'nickName'          => 'NickName',
        'gender'            => 'Gender',
        'birthday'          => 'Birthday',
        'address'           => 'Address',
        'email'             => 'Email',
        'phone'             => 'Phone',
        'tn'                => 'Tn',
        'country'           => 'Country',
        'city'              => 'City',
        'news'              => 'News',
        'isSign'            => 'IsSign',
        'isOperationsGroup' => 'IsOperationsGroup',
        'videoName'         => 'VideoName',
        'videoUrl'          => 'VideoUrl',
        'videoDescription'  => 'VideoDescription',
    ];

    /**
     * 用户
     * 虽然是全民ugc, 但是数据库中很多字段还是区分用户和主播的, 例如: 等级, 此处声明用户类型
     */
    const USER_TYPE_USER = 1;
    /**
     * 主播
     */
    const USER_TYPE_ANCHOR = 2;

    /**
     * 积分来源: 金币
     */
    const USER_SCORE_TYPE_GOLD = 1;

    /**
     *  积分来源: 送心
     */
    const USER_SCORE_TYPE_HEART = 2;

    /**
     *  积分来源: 观看事件
     */
    const USER_SCORE_TYPE_TIME = 3;

    /**
     * 签约用户(其实和分成一样)
     */
    const SIGN_USER = 1;

    /**
     * 分成用户
     */
    const BONUS_USER = 2;

    /**
     * ugc用户
     * 在端上, ugc和common没有区别
     */
    const UGC_USER = 3;

    /**
     * 普通用户
     */
    const COMMON_USER = 0;

    /**
     * 签约/分成用户的收入比例
     */
    const SIGN_REVENUE_RATE = 1;

    /**
     * 普通用户的收入比例
     */
    const COMMON_REVENUE_RATE = 0.5;

    /**
     * User_Model constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @return User_Model
     */
    public static function getInstance() {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }
        self::$_instance = new self();
        return self::$_instance;

    }

    /**
     * @param $userId
     *
     * @return bool|array
     */
    public function getUserInfo($userId) {
        $field     = User_Model::$userAttr;
        $condition = "UserID=$userId";
        $userInfo  = $this->selectOne($this->table, $field, $condition);
        if ($userInfo === false) {
            Bingo_Log::warning("[sql_log][error]:SQL:" . $this->dbInstance->getLastSQL() . " ERROR:" . $this->dbInstance->error(), 'sql_error');
            return false;
        }
        if (!empty($userInfo)) {
            $userInfo['NickName'] = urldecode($userInfo['NickName']);
            $userInfo['News']     = urldecode($userInfo['News']);
        }
        return $userInfo;

    }

    /**
     * @param $uInfo
     *
     * @return bool
     */
    public function createUser(array $uInfo) {
        if (!empty($uInfo['NickName'])) {
            $uInfo['NickName'] = urlencode($uInfo['NickName']);
            $uInfo['News']     = urlencode($uInfo['News']);
        }

        $res = $this->insertRow($this->table, $uInfo);
        if (!$res) {
            Bingo_Log::warning("[sql_log][error]:SQL:" . $this->dbInstance->getLastSQL() . " ERROR:" . $this->dbInstance->error(), 'sql_error');
            return false;
        }

        return true;
    }

    /**
     * update user specified attributes
     *
     * @param $userId
     * @param $attributes
     *
     * @return bool
     */
    public function setUserInfo($userId, $attributes) {
        $condition   = "UserID=$userId";
        $given_field = array_keys($attributes);
        $allow_field = array_intersect($given_field, User_Model::$userAttr);
        $attributes2 = array();
        foreach ($allow_field as $key) {
            $attributes2[$key] = $attributes[$key];
        }
        if (!empty($attributes2['NickName'])) {
            $attributes2['NickName'] = urlencode($attributes2['NickName']);
            $attributes2['News']     = urlencode($attributes2['News']);
        }
        $res = $this->update($this->table, $attributes2, $condition);
        if ($res === false) {
            Bingo_Log::warning("update user info failed. table: {$this->table} fields: " . json_encode($attributes2) . " cond: " . $condition, 'dal');
            return false;
        }

        return true;
    }
}