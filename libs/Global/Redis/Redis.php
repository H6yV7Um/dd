<?php

/*
 * Redis Class
 * Todo:链接Redis设置一主多从的策略
 * Use:需要设置数组格式的主从结构的Redis
 * houhuiyang@baidu.com
 */

class Air_Redis {

    private $conf; //配置文件
    private $_isUseCluster = false; //是否使用M/S的读写集群方案
    private $_sn = 0; //Slave 句柄标记
    private $_linkHandle = array(//服务器连接句柄
        'master' => null, //只支持一台 Master
        'slave' => array(), //可以有多台 Slave
    );

    //构造函数 是否采用 M/S 方案
    public function __construct(array $config) {
        try {
            if (!extension_loaded('redis')) { //Redis模块不存在
                throw new Exception('Redis模块不存在');
            }
            if (empty($config)) { //配置数组
                throw new Exception('配置数组不能为空');
            }
            $this->conf = $config;
            $this->_isUseCluster = isset($this->conf['sc']) ? $this->conf['sc'] : false;
            $this->connect($this->conf['servers']);
        } catch (Exception $error) {
            throw new Exception('Redis初始化失败');
        }
    }

    //连接服务器
    public function connect(array $servers) {
        //设置Master连接
        $master = $servers['master'];
        $slave = $servers['slave'];
        $this->_linkHandle['master'] = new Redis(); if (!empty($master)) {
            $ret = $this->_linkHandle['master']->connect($master['host'], $master['port'], $master['timeout']); //一个主
            if ($ret && !empty($master['auth'])) {
                $this->_linkHandle['master']->auth($master['auth']);
            }
            else {
                //throw new Exception("redis connect failure");     
            }         
        }
        //设置Slave连接
        if (!empty($slave)) {
            foreach ($slave as $key => $val) {
                $this->_linkHandle['slave'][$this->_sn] = new Redis();
                $ret = $this->_linkHandle['slave'][$this->_sn]->connect($val['host'], $val['port'], $val['timeout']); //一个从
                if ($ret && !empty($val['auth'])) {
                    $this->_linkHandle['slave'][$this->_sn]->auth($val['auth']);
                }
                else {  
                    //throw new Exception("redis connect failure");  
                }
                $this->_sn++;
            }
        }
        return $ret;
    }

    //关闭连接
    public function close($flag = 2) {
        switch ($flag) {
            case 0:
                $this->getRedis()->close(); //关闭Master
                break;
            case 1:
                for ($i = 0; $i < $this->_sn; ++$i) {
                    $this->_linkHandle['slave'][$i]->close(); //关闭Slave
                }
                break;
            case 2:
                $this->getRedis()->close();
                for ($i = 0; $i < $this->_sn; ++$i) {
                    $this->_linkHandle['slave'][$i]->close(); //关闭所有
                }
                break;
        }
        return true;
    }

    //得到 Redis 原始对象可以有更多的操作,返回服务器的类型 true:返回Master false:返回Slave,返回的Slave选择 true:负载均衡随机返回一个Slave选择 false:返回所有的Slave选择
    public function getRedis($isMaster = true, $slaveOne = true) {
        if ($isMaster) { //只返回 Master
            return $this->_linkHandle['master'];
        } else {
            //var_dump($this->_linkHandle['slave']); //slave的句柄
            return $slaveOne ? $this->_getSlaveRedis() : $this->_linkHandle['slave'];
        }
    }

    //写缓存
    public function set($key, $value, $expire = 0) {
        if (!empty($expire)) {
            return $this->getRedis()->setex($key, $expire, $value);
        }
        return $ret = $this->getRedis()->set($key, $value);
    }

    //读缓存
    public function get($key) {
        $func = is_array($key) ? 'mGet' : 'get'; //是否一次取多个值  
        if (!$this->_isUseCluster) {
            return $this->getRedis()->{$func}($key); //没有使用M/S
        }
        return $this->_getSlaveRedis()->{$func}($key); //使用了 M/S
    }

    //条件形式设置缓存，如果 key 不存时就设置，存在时设置失败
    public function setnx($key, $value) {
        return $this->getRedis()->setnx($key, $value);
    }

    // 删除缓存
    public function remove($key) {
        return $this->getRedis()->delete($key);
    }

    //值加加操作,类似$i++ ,如果 key 不存在时自动设置为 0 后进行加加操作
    public function incr($key, $default = 1) {
        if ($default == 1) {
            return $this->getRedis()->incr($key);
        } else {
            return $this->getRedis()->incrBy($key, $default);
        }
    }

    //值减减操作,类似 --$i ,如果 key 不存在时自动设置为 0 后进行减减操作
    public function decr($key, $default = 1) {
        if ($default == 1) {
            return $this->getRedis()->decr($key);
        } else {
            return $this->getRedis()->decrBy($key, $default);
        }
    }

    //添空当前数据库
    public function clear() {
        return $this->getRedis()->flushDB();
    }

    //随机 HASH 得到 Redis Slave 服务器句柄
    private function _getSlaveRedis() {
        if ($this->_sn <= 1) {
            return $this->_linkHandle['slave'][0]; //就一台 Slave 机直接返回
        }
        $hash = $this->_hashId(mt_rand(), $this->_sn); //随机 Hash 得到 Slave 的句柄
        return $this->_linkHandle['slave'][$hash];
    }

    //根据ID得到 hash 后 0～m-1 之间的值
    private function _hashId($id, $m = 10) {
        $k = md5($id); //把字符串K转换为 0～m-1 之间的一个值作为对应记录的散列地址
        $l = strlen($k);
        $b = bin2hex($k);
        $h = 0;
        for ($i = 0; $i < $l; $i++) {
            $h += substr($b, $i * 2, 2); //相加模式HASH
        }
        $hash = ($h * 1) % $m;
        return $hash;
    }

    //析构函数
    public function __destruct() {
        //$this->close(2);  不要close,如果没连上，是会出问题的
    }

}