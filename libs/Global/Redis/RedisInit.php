<?php

/*
* @descripton: redis操作类
*
* @filename: RedisInit.class.php  
* @author  : wangjialong@baidu.com
* @date    : 2013-05-21
* @package : web-ui/lib/redis/
*
* @Copyright (c) 2013 BAIDU-GPM
*
*/

class Global_Redis_RedisInit extends Global_Balance_Base {
    //redis对象 
    private static $_instanceList = null;
    private static $_lastConfig = null;
    private $redis = null;
    private $_conf = null;
    private static $redisInsList = array();

    CONST STORAGE_DATA = 'storage';
    CONST CACHE_DATA = 'cache';
    CONST MEDIA_DATA = 'mediaServer';
    CONST MSG_DATA = 'message';

    /**
     * 老的Redis实例化方法，不推荐使用
     *
     * @param
     *
     * @return
     */
    public static function getInstance($dbName = self::STORAGE_DATA) {
        if (!self::$_instanceList[$dbName]) {
            self::$_instanceList[$dbName] = new Global_Redis_RedisInit($dbName);
        }
        return self::$_instanceList[$dbName];
    }

    /**
     * 工厂函数，制造redis封装类
     *
     * @param
     *
     * @return
     */
    public static function factory($dbName = 'cache') {
        return new Global_Redis_RedisInit($dbName);
        if (empty(self::$redisInsList[$dbName])) {
            self::$_instanceList[$dbName] = new Global_Redis_RedisInit($dbName);
        }
        return self::$_instanceList[$dbName];
    }

    /**
     * Redis连接方法
     *
     * @param
     *
     * @return
     */
    protected function __construct($dbName) {
        if (!extension_loaded('redis')) { //Redis模块不存在
            //throw new Exception('Redis module not exists');
            Bingo_Log::warning("Redis module not exists", "dal");
            $this->exceptionProcess();
        }
        $configFileName = 'RedisCluster.Conf.php';
        $ret            = $this->getConnect($configFileName, $dbName);
        if (empty($ret)) {
            $this->exceptionProcess();
        }
        return $ret;
    }

    /**
     * 负载均衡选择Redis 连接
     *
     * @param
     *
     * @return
     */
    public function getConnect($configFileName, $configKey = null) {
        $config = $this->getConfig($configFileName, $configKey);
        //如果是Redis集群
        if (array_key_exists('isCluster', $config) && !empty($config['isCluster'])) {
            //初始化集群配置
            $ret = $this->runRedisCluster($config['seeds'], $config['timeout']);
        } else {
            $ret = $this->runTwProxyRedis($config);
        }
        return $ret;
    }

    /**
     * 初始化Redis集群
     *
     * @param
     *
     * @return
     */
    public function runRedisCluster($seeds, $timeout) {
        try {
            $OPT_FAILOVER = 5;
            shuffle($seeds);
            $this->redis = new RedisCluster(null, $seeds, $timeout);
            $this->redis->setOption($OPT_FAILOVER, RedisCluster::FAILOVER_DISTRIBUTE);
            return true;
        } catch (Exception $e) {
            Bingo_Log::warning($e->getMessage(), 'dal');
            Bingo_Log::warning('Redis cluster init failed', 'dal');
        }
        return false;
    }

    /**
     * 初始化tw-proxy式Redis集群
     *
     * @param
     *
     * @return
     */
    public function runTwProxyRedis($config) {
        if (empty($config[0])) {
            $configtemp = $config;
            $config     = array();
            $config[]   = $configtemp;
        }
        $ret = $conf = false;
        for ($i = 0; $i < 2; $i++) {
            $key  = $this->randKey($config);
            $conf = $config[$key];
            try {
                $this->redis = new Redis();
                $ret         = $this->redis->connect($conf["host"], $conf["port"], $conf["timeout"]);
                break;
            } catch (Exception $e) {
                Bingo_Log::warning($e->getMessage(), 'dal');
                $disableConfig = array(
                    'host'      => $conf['host'],
                    'port'      => $conf['port'],
                    'count'     => 1,
                    'timeStamp' => time(),
                );
                $this->saveDisableConfig($disableConfig, $configFileName);
                unset($config[$key]);
                continue;
            }
        }
        return $ret;
    }

    /**
     * 魔术方法
     *
     * @param
     *
     * @return
     */
    public function __call($func, $params) {
        try {
            if ($func == 'delete') {
                $func = 'del';
            } else if ($func == 'zDelete' || $func == 'zdelete') {
                //$response = $this->redis->zRem($params);var_dump($params);
                $func = 'zRem';
            }
            $rObj       = $this->redis();
            $reflection = new ReflectionMethod($rObj, $func);
            $response   = $reflection->invokeArgs($rObj, $params);
        } catch (Exception $e) {
            Bingo_Log::warning($e->getMessage(), 'dal');
            $response = false;
        }
        return $response;
    }

    /**
     * redis 方法使用封装入口
     *
     * @param
     *
     * @return
     */
    public function call($func, $args) {
        if (is_callable($this, $func)) {
            $reflection = new ReflectionMethod($this, $func);
            return $reflection->invokeArgs($this, $args);
        }
        $rObj       = $this->redis();
        $reflection = new ReflectionMethod($rObj, $func);
        return $reflection->invokeArgs($rObj, $args);
    }

    /**
     * Redis连接异常处理函数
     *
     * @param
     *
     * @return
     */
    public function exceptionProcess() {
        $info = debug_backtrace();
        exit();
        //echo json_encode($info);exit();
        Bingo_Log::warning("Redis init failed", "dal");
        Header("Location:" . "http://" . $_SERVER['HTTP_HOST'] . "/pages?name=Error500");
        exit();
    }

    /**
     * @param
     *
     * @return
     */
    public function exists($key) {
        if (!$key) {
            return false;
        }
        if ($this->redis) {
            return $this->redis->exists($key);
        } else {
            return false;
        }
    }

    /**
     * reconnect redis connection
     *
     * @param
     *
     * @return
     */
    public static function reconnect($dbName = 'db') {
        self::$_instanceList[$dbName] = null;
        return self::getInstance($dbName);
    }

    /**
     * @param
     *
     * @return
     */
    public function redis() {
        return $this->redis;
    }
}

?>