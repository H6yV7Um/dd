<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Statistic.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2014/08/15 15:17:03
 * @brief 应用统计log
 *  
 **/


class Global_Util_Statistic
{
	private static $_prefix="HICLUB_LOG:";
	private static $_logType="dal";
	
	private static $_sessionCookieKeys=array('BDUSS','HICLUBSID','DEVICEID','TN', 'OS', 'CLIENT', 'OSVERSION', 'COUNTRY' );
	
	CONST HICLUB_STATISTIC_TYPE_TOPUP_SUC=1; //充值成功
	CONST HICLUB_STATISTIC_TYPE_FBLOGIN_SUC=2; //facebook 登录成功
	CONST HICLUB_STATISTIC_TYPE_REGISTRER_SUC=3; //注册成功
	CONST HICLUB_STATISTIC_TYPE_TWLOGIN_SUC=4;	//twitter 登陆成功
	CONST HICLUB_STATISTIC_TYPE_REGISTRER_SUC_FB=5;//facebook 注册成功
	CONST HICLUB_STATISTIC_TYPE_REGISTRER_SUC_TW=6;//twitter 注册成功
	
	/**
	 * 打印用户统计log，
	 * @param $msg array,里面是key=value
	 */
	public static function printLog($type,$msg)
	{
		$logStr="";
		if(!is_array($msg))
		{
			$msg=array($msg);
		}
		foreach($msg as $k=>$v)
		{
			$logStr .= "|$k=$v";
		}
        // @changed by zhangyanqing@baidu.com <2015-1-12>
        if (isset($_COOKIE) && !empty($_COOKIE['gl_ref'])) {
            $logStr .= '|gl_ref=' . $_COOKIE['gl_ref'];            
        } else {
            $logStr .= '|gl_ref=/';            
        }
        //移动端通用参数
        foreach (self::$_sessionCookieKeys as $cookieKey) {
        	if (isset($_COOKIE[$cookieKey])) {
        		$logStr .= '|' . $cookieKey . '=' . $_COOKIE[$cookieKey];
        	}
        }
        
		if(empty($logStr))
			return ;
			
		$bdUid=isset($_COOKIE['BAIDUID'])? $_COOKIE['BAIDUID']:"";
		$hiclubtn=isset($_COOKIE['hiclub_tn'])? $_COOKIE['hiclub_tn']:"";
		if(!$hiclubtn && (isset($msg['UserID'])|| isset($msg['uid']))){
			$uid = isset($msg['UserID'])? $msg['UserID'] : $msg['uid'];
			$hiclubtn = Global_Util_Tn::getUserTnByUid($uid);
		}
		$logStr = self::$_prefix.":type=$type"."[hiclubtn=$hiclubtn|BAIDUID={$bdUid}{$logStr}]";
		Bingo_Log::notice($logStr,self::$_logType);
	}
}







/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
