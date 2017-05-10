<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Tn.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2014/12/01 15:01:31
 * @brief 
 *  用户渠道相关操作
 **/




class Global_Util_Tn
{
	/**
     * @param
     * @return
	 * 从请求或者cookie 里面获取用户渠道
	 */
	public static function getUserTn()
	{
		$tn='';
		if(isset($_REQUEST['CLIENT']) && $_REQUEST['CLIENT'] == 'mobile'){
			$tn = $_REQUEST['CLIENT'];
		}
		elseif(isset($_COOKIE['hiclub_tn'])){
			$tn = $_COOKIE['hiclub_tn'];
		}
		return $tn;
	}
	
	/**
     * @param
     * @return
	 * 用户注册信息里面获取用户渠道信息
	 */
	public static function getUserTnByUid($uid)
	{
		$tn = '';
		$ui = CmHiUser_Interface::getFullUserInfoTryCache($uid,'Tn');
		if($ui){
			return $ui[0]['Tn'];
		}
		return $tn;
	}
}



/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
