<?php
/***************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/


/**
 * @file   Base.php
 * @author gedejin(com@baidu.com)
 * @date   2015/04/20 10:01:58
 * @brief
 *
 **/
abstract class Global_Service_Base extends Global_BaseFactory {

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var Redis
     */
    protected $cacheRedis;

    /**
     * @var Redis
     */
    protected $mediaRedis;

    /**
     * @var Redis
     */
    protected $msgRedis;

    protected $defaultApiCacheTime = 60;

    protected $_errInfo = null;

    /**
     * redis key默认有效期
     */
    const REDIS_KEY_TTL = 3600;

    /**
     * 初始化storage redis
     */
    public function initRedis() {
        $this->redis = Global_Redis_RedisInit::getInstance();
    }

    /**
     * 初始化cache redis
     */
    public function initCacheRedis() {
        $this->cacheRedis = Global_Redis_RedisInit::getInstance(Global_Redis_RedisInit::CACHE_DATA);
    }

    /**
     * 初始化流媒体服务redis
     */
    public function initMediaRedis() {
        $this->mediaRedis = Global_Redis_RedisInit::getInstance(Global_Redis_RedisInit::MEDIA_DATA);
    }

    /**
     * 初始化消息服务的redis
     */
    public function initMessageRedis() {
        $this->msgRedis = Global_Redis_RedisInit::getInstance(Global_Redis_RedisInit::MSG_DATA);
    }

    /**
     * 重连redis
     *
     * @param
     *
     * @return
     */
    public function reconnectRedis() {
        $this->redis = Global_Redis_RedisInit::reconnect();
    }

    /**
     * 从缓存中获取数据, 如果数据不存在尝试刷新缓存
     * 支持json字符串类型
     *
     * @param       $key
     * @param null  $func   回调方法
     * @param array $params 回调参数
     * @param int   $ttl    redis key有效期
     *
     * @return mixed|null
     */
    public function getDataTryCache($key, $func = null, $params = [], $ttl = self::REDIS_KEY_TTL) {
        $data = $this->redis->get($key);
        if ($data) {
            return json_decode($data, true);
        }

        if (is_null($func)) {
            return null;
        }

        $data = call_user_func_array($func, $params);
        if (!$data) {
            return null;
        }

        if ($ttl > 0) {
            if (!$this->redis->setex($key, $ttl, json_encode($data))) {
                Bingo_Log::warning("redis error: setEx failed. key: $key ttl: $ttl value: " . json_encode($data), 'dal');
            }
        } else {
            if (!$this->redis->set($key, json_encode($data))) {
                Bingo_Log::warning("redis error: set failed. key: $key value: " . json_encode($data), 'dal');
            }
        }

        return $data;
    }

    /**
     * @param $serviceName
     * @param $params
     *
     * @return mixed|string
     */
    public function getCacheKeyByInterfaceNameAndParams($serviceName, $params) {
        if (isset($params["jsonp"])) {
            unset($params["jsonp"]);
        }
        sort($params);
        $key = $serviceName . "_" . implode("_", $params);
        $key = str_replace(" ", "_", $key);
        $key = str_replace(",", "_", $key);
        return $key;
    }

    /**
     * @param     $url
     * @param     $method
     * @param     $params
     * @param int $timeOut
     *
     * @return bool|mixed
     */
    public function getDataByCurl($url, $method, $params, $timeOut = 5) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method == "post") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $res = curl_exec($ch);
        if ($res == false && curl_errno($ch) != 0) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $res;
    }

    /**
     * @param $apiName
     * @param $params
     *
     * @return bool
     */
    public function getApiDataFromCache($apiName, $params) {
        $key  = $this->getCacheKeyByInterfaceNameAndParams($apiName, $params);
        $data = Cache_Module::getCacheByKey($key);
        if (!empty($data)) {
            $data = json_decode($data, true);
            if (!empty($data)) {
                $result["result"] = $data;
                return $result;
            }
        }
        return false;
    }

    /**
     * @param $apiName
     * @param $params
     * @param $result
     *
     * @return bool
     */
    public function setApiDataToCache($apiName, $params, $result) {
        $data = $result["result"];
        if (empty($data)) {
            return true;
        }
        $data = json_encode($result["result"]);
        $key  = $this->getCacheKeyByInterfaceNameAndParams($apiName, $params);
        return Cache_Module::setCache($key, $data, $this->defaultApiCacheTime);
    }

    /**
     * @param $subFunctionName
     */
    public function saveServiceCostTime($subFunctionName) {
        global $g_startTime;

        $endTime = microtime(true);
        $api     = $_SERVER["REQUEST_URI"];
        $logInfo = "api:$api -- [$subFunctionName],costTime:" . ($endTime - $g_startTime);
        Bingo_Log::notice($logInfo, "api_cost");
    }

    /**
     * @return null
     */
    public function getErrorInfo() {
        return $this->_errInfo;
    }

    /**
     * @param $errNum
     * @param $errMessage
     */
    protected function setErrInfo($errNum, $errMessage) {
        $this->_errInfo = array(
            'code'        => $errNum,
            'messageInfo' => $errMessage,
        );
    }

    /**
     * @return string
     */
    public function getPlatForm() {
        return Global_Env::getPlatForm();
    }

    /**
     *
     * @param
     *
     * @return
     */
    public function getOs() {
        $os = $_REQUEST["OS"];
        if (empty($os)) {
            return "ios";
        }
        return $os;
    }

    /**
     * change key to url
     *
     * @param $key
     *
     * @return string
     */
    public function key2url($key) {
        $url = sprintf("http://res.talebox.mobi/" . $key);
        return $url;
    }

    /**
     * 根据公共参数definition获取分辨率, 不传默认为高清
     * @return int
     */
    public function getDefinition() {
        list($width, $height) = explode("x", $_GET['definition']);
        if ($width < 480 || empty($width)) {
            return 100;
        } elseif ($width < 720) {
            return 300;
        } else {
            return 480;
        }

    }

    /**
     * 根据经纬度获取用户地理位置
     *
     * @param float $lng 经度
     * @param float $lat 纬度
     *
     * @return array
     */
    public function getLocation($lng, $lat) {
        if (!$lng || !$lat) {
            return [];
        }

//        $example = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=39.983424,116.322987&key=AIzaSyAaZaQzz6yzAFQRVNlYe2BIoQl36s53l3s&language=en';
        $url       = 'https://maps.googleapis.com/maps/api/geocode/json';
        $key       = 'AIzaSyAaZaQzz6yzAFQRVNlYe2BIoQl36s53l3s';
        $latlng    = "$lat,$lng";
        $language  = $_REQUEST['locale'] ? $_REQUEST['locale'] : 'en';
        $targetUrl = "{$url}?key={$key}&latlng={$latlng}&language=$language";

        $data = $this->getDataByCurl($targetUrl, 'get', []);
        if (!$data) {
            return [];
        }

        // 反地理编码会根据经纬度返回多个地理点, 按照准确度降序排列, 取第一个作为定位点
        $data     = json_decode($data, true);
        $address  = array_reverse($data['results'][0]['address_components']);

        return [
            'country' => $address[1]['long_name'],
            'city'    => $address[2]['long_name'],
        ];
    }

    /**
     * 获取等级与积分的map
     * t_Levels表获取等级配置
     * @return array
     */
    public function getLevelMap() {
        $key      = Global_CacheKey_KeyMap::USER_LEVEL_MAP_STRING;
        $levelMap = $this->getDataTryCache($key, function () {
            return LevelConf_Model::getInstance()->getLevelMap();
        }, [], LevelConf_Model::USER_LEVEL_MAP_TTL);

        return $levelMap ?: [];
    }

    /**
     * 获取用户等级
     *
     * @param int $levelType 枚举类型, 0|1
     * @param $score
     *
     * @return int
     */
    public function getLevel($levelType, $score) {
        $newLevel = 0;
        $levelMap = $this->getLevelMap();
        foreach ($levelMap as $levelInfo) {
            if ($levelInfo['LevelType'] == $levelType) {
                if ($score >= $levelInfo['Low'] && $score <= $levelInfo['High']) {
                    $newLevel = $levelInfo['LevelID'];
                    break;
                }
            }
        }
        if ($levelType == User_Model::USER_TYPE_USER) {
            $newLevel -= 41;
        }

        return $newLevel;
    }

    /**
     * PHP5.4不支持该函数, 手动实现
     * @param array $input
     * @param $columnKey
     * @param null $indexKey
     * @return array|bool
     */
    public function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach($input as $value) {
            if(!isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if(is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if(!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if(!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

    /**
     * 对房间列表重排序
     *
     * isTop > country > viewCnt > recommendSorts > anchorScore
     *
     * @param      $roomList
     * @param null $userId
     *
     * @return array
     */
    public function resort($roomList, $userId = null) {
        if (!$roomList) {
            return [];
        }

        // 观看者所属国家
        $country = '';
        if (!is_null($userId)) {
            $userInfo = User_Info_Service::getInstance()->getUserInfo($userId);
            if($userInfo['country'] != User_Info_Service::DEFAULT_COUNTRY) {
                $country = $userInfo['country'];
            }
        }

        // 排序
        usort($roomList, function ($a, $b) use ($country) {
            if($a['isTop'] != $b['isTop']) {
                return $b['isTop'] - $a['isTop'];
            }

            if($country) {
                $isASame = intval(strtolower($a['country']) == strtolower($country));
                $isBSame = intval(strtolower($b['country']) == strtolower($country));
                if ($isASame != $isBSame) {
                    return $isBSame - $isASame;
                }
            }
            if ($a['recommendSorts'] != $b['recommendSorts']) {
                return $b['recommendSorts'] - $a['recommendSorts'];
            }
            if ($a['viewCnt'] != $b['viewCnt']) {
                return $b['viewCnt'] - $a['viewCnt'];
            }
            if ($a['anchorScore'] != $b['anchorScore']) {
                return $b['anchorScore'] - $a['anchorScore'];
            }

            return 0;
        });

        return $roomList;
    }
}