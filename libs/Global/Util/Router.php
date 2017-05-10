<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Router.php
 * @author nixiaofei(com@baidu.com)
 * @date 2016/03/09 12:12:20
 * @brief 
 *  
 **/

class Global_Util_Router{

	public static $instance = null;
	public static $isCheckMobile = true;
	 /**
	 *
	 *@param
	 *@return
	 */
	public static function getInstance(){

		if(!$instance instanceof self){
			self::$instance = new self();
		}
		return self::$instance;
	}

	 /**
	 *
	 *@param
	 *@return
	 */
	private function __construct(){
		return false;
	}

	 /**
	 *
	 *@param
	 *@return
	 */
	public function __clone(){
		return false;
	}

	 /**
	 *
	 *@param
	 *@return
	 */
	public static function checkUrl($url){

		$isMobile = self::checkMobile();
		if(!$isMobile){
			return false;
		}
		$url = self::getUrl($url);
		if(empty($url)){
			return false;
		}
		self::redirect($url,false,301);
	}

	 /**
	 *
	 *@param
	 *@return
	 */
	public static function getUrl($url){

		$urlConfig =  Global_Util_Conf::get('UrlMap',array());
		if(empty($urlConfig)){
			return false;
		}
		foreach($urlConfig as $key=>$val){
			$res=preg_match($key,$url,$matchs);
			if($res){
				preg_match_all("/{:([a-zA-Z]*)}/",$val['url'],$result);
				$rurl = self::replaceUrl($val['url'],$result);
				return $rurl;
			}else{
				continue;
			}
		}
		return null;
	}

	 /**
	 *
	 *@param
	 *@return
	 */
	public static function checkMobile(){
		if(!self::$isCheckMobile){
			return true;
		}
		$isMobile = Global_Util_UserAgent::isMobile();
		if(!$isMobile){
			return false;
		}
		return true;
	}

	 /**
	 *
	 *@param
	 *@return
	 */
	public static function replaceUrl($url,$matchs){
		if(count($matchs)>1){
			$params = $_GET;
			foreach($matchs[1] as $key=>$val){
				$url=str_replace('{:'.$val.'}', $params[$val], $url);
				unset($params[$val]);
			}
			if(count($params)>0){
				$subfix = http_build_query($params);
				$url.='?'.$subfix;
			}

		}
		return $url;
	}
		
	 /**
	 *
	 *@param
	 *@return
	 */
	public static function redirect($url,$replace=false,$httpCode=301){
		header("Location:$url",$replace,$httpCode);
		die();
	}	
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
