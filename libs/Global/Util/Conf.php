<?php
/**
 *获取配置文件
 *@author nixiaofei@baidu.com
 *@date [2015-12-18]
 */
class Global_Util_Conf
{
    
    private static $subfix = '.Conf.php';
    private static $item = array();

    /**
     *设置文件后缀
     *@param $subfix string
     *@return array
     */
    public static function setSubfix($subfix){
          self::$subfix = $subfix;
    }

    /**
     *获取文件后缀
     *@param  null
     *@return array
     */
    public static function getSubfix(){

        return self::$subfix;
    }

    /**
     *实例化数据 赞不支持config下添加二级目录读取
     *@param $key string
     *@return array 
     */
	private static function init($key){

        if(is_array($key)){
            $key = $key[0];
        }
        $arr = explode('.', $key);
        if(!empty(self::$item[$arr[0]])){
            return self::$item[$arr[0]];
        }
        try {
            $filePath = CONFIG_PATH.$arr[0].self::$subfix;
            if(!file_exists($filePath)){
                return null;
            }else{
                $array = include($filePath);
                self::$item[$arr[0]] = $array;
            }
        }catch(Exception $e){
            Bingo_Log::warning($e->getMessage(),'dal');
            return null;
        }
        return $array;
    }
    
    /**
     *设置值
     *@param $key string 键值 只是针对当次请求有效
     *@param $value string 
     *@return null 
     */ 
	public  static function set($key,$value){
        
        if (is_array($key)&&is_array($value)) {
            foreach ($key as $innerKey => $innerValue) {
                self::init($innerValue);
                self::setValue(self::$item, $innerValue, $value[$innerKey]);
            }
        } else {
            self::init($key);
            self::setValue(self::$item, $key, $value);
        }
	} 

    /**
     *设置值
     *@param $array  array 数组
     *@param $key  string 键值
     *@param $value  string 值
     *@return null
     */ 
    private static function setValue(&$array,$key,$value){
        
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     *获取某一项的值
     *@param $key string
     *@param $default  mix 可以已一个匿名函数
     *@return mix
     */ 
	public  static function get($key,$default=null){

        $array =  self::init($key);
        if (is_null($key)) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $k=>$segment) {
            if($k!=0){
                if (! is_array($array) || ! array_key_exists($segment, $array)) {
                    return self::value($default);
                }
                $array = $array[$segment];
            }
        }
        return $array;
    }

    /**
     *对值做处理
     *@param $value mix
     *@return  mix
     */
    private static function value($value){  

        return $value instanceof Closure ? $value() : $value;
    }

    /**
     *判断是否存在某一个键值
     *@param $key string 键值
     *@return  boolean
     */
	public  static function has($key){

        $array = self::init($key);
        if (empty($array) || is_null($key)) {
            return false;
        }
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if($k!=0){
                if (! is_array($array) || ! array_key_exists($segment, $array)) {
                    return self::value($default);
                }
                $array = $array[$segment];
            }
        }
        return true;
    }

}

?>