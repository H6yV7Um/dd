<?php
/*
* @descripton: 以php数组的方式读取cms数据类
*
* @filename: Data.php  
* @author  : mozhuoying@baidu.com
* @date    : 2014-04-08
* @package : global
*
* @Copyright (c) 2014 BAIDU-GPM
*
*/
class Global_Cms_Base{

    /**
     * 根据路径读取cms数据
     * @param  string  $dataPath 读取的数据路径
     * @return array 读取cms数据
     */
    static function get( $dataPath ){

        if( is_file(  $dataPath ) ){
            include($dataPath) ;
            return $root;
        } else {
            return array();
        }
    }

    /**
     * 递归合并数组把b合并到a
     * @param  array   合并的base 数组（a）
     * @param  array   需合并的数组(b)
     * @return array
     */
    static function array_merge() {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        foreach ($arrays as $array) {
            reset($base); //important
            while (list($key, $value) = @each($array)) {
                if (is_array($value) && @is_array($base[$key])) {
                    $base[$key] = self::array_merge($base[$key], $value);
                }
                else {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }
}


?>