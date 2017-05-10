<?php
/**
 * Created by PhpStorm.
 * User: gedejin(com@baidu.com)
 * Date: 15/12/1
 * Time: 上午11:30
 */

class Global_Mongo_MongoClient {

    private $_conf;
    private $_conn;    //Client
    private static $_mongo;

    /**
     * @return null||empty
     */
    private function  __construct(){
        if (!extension_loaded('mongo')) { //mongo模块不存在
            Bingo_Log::warning("mongo module not exists","dal");
            return null;
        }
        $this->_conf = Mongo_Config::$config;
        $mongo_config = $this->_conf;
        $host = join(',',$mongo_config['host']);
        $user = $mongo_config['uname'];
        $password = $mongo_config['auth'];
        $db = $mongo_config['db'];
        $mongoBase = new Global_Mongo_MongoBase($host,$user,$password,$db);
        self::$_mongo = $mongoBase;
    }

    /**
     * 析构函数 关闭mongo链接
     * @param
     * @return
     */
    public function  __destruct(){
        self::$_mongo->closeDatabase();
    }
    /**
     * @return MongoDB
     */
    public static function getMongoInstance() {
        if(empty(self::$_mongo)){
            new Global_Mongo_MongoClient();
        }
        return self::$_mongo;
    }

} 
