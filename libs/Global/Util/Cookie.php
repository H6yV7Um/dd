<?php
/***************************************************************************
 * i
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Cookie.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2014/06/13 19:41:01
 * @brief 
 *  
 **/


class Global_Util_Cookie
{

	private static $_COOKIE_TRASH_PATH="/__cookie_trash/";

	public static function delete($name)
	{
		return setcookie($name,false,0,"/");
		//return setcookie($name,NULL,0);
	}

	/**
	 * [getRootPath 返回cookie的根目录]
	 * @return [string] [description]
	 */
	public static function getRootPath()
	{
		if(!isset($_SERVER['HTTP_HOST']))
			return '/';
		preg_match('#.*(\.[^.]+\.[^.:]+)#',$_SERVER['HTTP_HOST'],$matchs);
		return $matchs[1];
	}
}







/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
