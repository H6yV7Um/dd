<?php
/**
 * Created by PhpStorm.
 * User: gedejin
 * Date: 15-1-27
 * Time: 上午11:04
 *
 * @desc    缓存存取类
 */

class Global_Cache_Base {

    private static function getCacheByKey($key){
        $redis   = Global_Redis_RedisInit::getInstance(Global_Redis_RedisInit::CACHE_DATA);
        return $redis->get($key);
    }

    private static function setCache($key,$data,$expire=3600){
        $redis   = Global_Redis_RedisInit::getInstance(Global_Redis_RedisInit::CACHE_DATA);
        $resSet = $redis->set($key,$data);
        if(!empty($expire)){
            $resExp = $redis->expire($key,$expire);
            return $resSet && $resExp;
        }
        return $resSet;
    }

    public static function remember($key=null,$expire=10,$callback=null,$readCache=true,$writeCache=true){
        if($readCache){
            $ret = self::getCacheByKey($key);
            if(!empty($ret)){
               return is_numeric($ret)?$ret:unserialize($ret);
            } 
        }

        if(empty($callback)){
            return false;
        }

        if($callback instanceof Closure){
            $data = call_user_func($callback);
            if(!empty($data)){
                if($writeCache){
                    $value = is_numeric($data)?$data:serialize($data);
                    self::setCache($key,$value,$expire);
                }
                return $data;
            }else{
                return false;
            }
        }
        return $callback;
    }

    public static function del($key){

        if(empty($key)){
            return false;
        }
        $redis   = Global_Redis_RedisInit::getInstance(Global_Redis_RedisInit::CACHE_DATA);
        return $redis->del($key);
    }
}