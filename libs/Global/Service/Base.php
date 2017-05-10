<?php

abstract class Global_Service_Base extends Global_BaseFactory {

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var Redis
     */
    protected $mediaRedis;

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

}