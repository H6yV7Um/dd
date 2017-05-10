<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Helper.php
 * @author nixiaofei(com@baidu.com)
 * @date 2016/05/05 19:15:50
 * @brief 
 *  
 **/

class Global_Util_Helper{

	/**
	 *@param int $code
	 *@param string $msg
	 *@return  object
	 */
	public static function showException($code,$msg=''){
		throw new Exception($msg,$code);
	}

	/**
	 *
	 *@param string $json
	 *@return mixed
	 */
	public static function json2array($json){
		$result = json_decode($json,true);
		if(json_last_error()!=JSON_ERROR_NONE){
			return false;
		}
		return $result;
	}

	 /**
     * @param
     * @return
     */
    public static function arrayHasFalse($item){
        if(!is_array($item)){
            $item = array($item);
        }
        if(empty($item)){
            return false;
        }
        foreach($item as $key=>$val){
            if($val === false){
                return true;
            }
        }
        return false;
    }

    /** 
     * url decode for array
     * @param
     * @return
     */
    public static function processUrlDecode($data){
    	if(empty($data)){
    		return $data;
    	}
    	if(!is_array($data)){
    		$data = array($data);
    	}
    	foreach($data as $key=>$item){
    		if(is_array($item)){
    			$data[$key] = self::processUrlDecode($item);
    		}else if(!is_numeric($item)){
    			$data[$key] = urldecode($item);
    		}
    	}
    	return $data;
    }

}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
