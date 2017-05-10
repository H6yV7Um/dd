<?php
/***************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/



/**
 * @file UserAgent.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2014/11/25 13:39:31
 * @brief
 *
 **/



class Global_Util_UserAgent
{
	/**
	 * 取得阅读器名称和版本
	 *
	 * @access public
	 * @return string
	 */
	public static function getBrowser($byArray=true)
	{
		$agent           = $_SERVER['HTTP_USER_AGENT'];
		$browser       = '';
		$browser_ver     = '';

		if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs))
		{
			$browser       = 'OmniWeb';
			$browser_ver     = $regs[2];
		}

		if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs))
		{
			$browser       = 'Netscape';
			$browser_ver     = $regs[2];
		}

		if (preg_match('/safari\/([^\s]+)/i', $agent, $regs))
		{
			$browser       = 'Safari';
			$browser_ver     = $regs[1];
		}

		if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs))
		{
			$browser       = 'Internet Explorer';
			$browser_ver     = $regs[1];
		}

		if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs))
		{
			$browser       = 'Opera';
			$browser_ver     = $regs[1];
		}

		if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs))
		{
			$browser       = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
			$browser_ver     = $regs[1];
		}

		if (preg_match('/Maxthon/i', $agent, $regs))
		{
			$browser       = '(Internet Explorer ' .$browser_ver. ') Maxthon';
			$browser_ver     = '';
		}

		if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs))
		{
			$browser       = 'FireFox';
			$browser_ver     = $regs[1];
		}

		if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs))
		{
			$browser       = 'Lynx';
			$browser_ver     = $regs[1];
		}

		if ($browser != '')
		{
			if($byArray){
				return array("browser"=>$browser,"ver"=>$browser_ver);
			}
			else{
				return $browser.' '.$browser_ver;
			}
		}
		else
		{
			return false;
		}
	}
	/**
	 * 取得客户真个操作体系
	 *
	 * @access private
	 * @return void
	 */
	public static function getOS()
	{
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$os = false;

		if (eregi('win', $agent) && strpos($agent, '95'))
		{
			$os = 'Windows 95';
		}
		else if (eregi('win 9x', $agent) && strpos($agent, '4.90'))
		{
			$os = 'Windows ME';
		}
		else if (eregi('win', $agent) && ereg('98', $agent))
		{
			$os = 'Windows 98';
		}
		else if (eregi('win', $agent) && eregi('nt 6.0', $agent))
		{
			$os = 'Windows Vista';
		}
		else if (eregi('win', $agent) && eregi('nt 6.1', $agent))
		{
			$os = 'Windows 7';
		}
		else if (eregi('win', $agent) && eregi('nt 5.1', $agent))
		{
			$os = 'Windows XP';
		}
		else if (eregi('win', $agent) && eregi('nt 5', $agent))
		{
			$os = 'Windows 2000';
		}
		else if (eregi('win', $agent) && eregi('nt', $agent))
		{
			$os = 'Windows NT';
		}
		else if (eregi('win', $agent) && ereg('32', $agent))
		{
			$os = 'Windows 32';
		}
		else if (eregi('linux', $agent))
		{
			$os = 'Linux';
		}
		else if (eregi('unix', $agent))
		{
			$os = 'Unix';
		}
		else if (eregi('sun', $agent) && eregi('os', $agent))
		{
			$os = 'SunOS';
		}
		else if (eregi('ibm', $agent) && eregi('os', $agent))
		{
			$os = 'IBM OS/2';
		}
		else if (eregi('Mac', $agent) && eregi('PC', $agent))
		{
			$os = 'Macintosh';
		}
		else if (eregi('PowerPC', $agent))
		{
			$os = 'PowerPC';
		}
		else if (eregi('AIX', $agent))
		{
			$os = 'AIX';
		}
		else if (eregi('HPUX', $agent))
		{
			$os = 'HPUX';
		}
		else if (eregi('NetBSD', $agent))
		{
			$os = 'NetBSD';
		}
		else if (eregi('BSD', $agent))
		{
			$os = 'BSD';
		}
		else if (ereg('OSF1', $agent))
		{
			$os = 'OSF1';
		}
		else if (ereg('IRIX', $agent))
		{
			$os = 'IRIX';
		}
		else if (eregi('FreeBSD', $agent))
		{
			$os = 'FreeBSD';
		}
		else if (eregi('teleport', $agent))
		{
			$os = 'teleport';
		}
		else if (eregi('flashget', $agent))
		{
			$os = 'flashget';
		}
		else if (eregi('webzip', $agent))
		{
			$os = 'webzip';
		}
		else if (eregi('offline', $agent))
		{
			$os = 'offline';
		}
		else
		{
			$os = 'Unknown';
		}
		return $os;
	}

	/**
	 *
	 *@param
	 *@return
	 */
	public static function isMobile(){ 
	    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
	    {
	        return true;
	    } 
	    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	    if (isset ($_SERVER['HTTP_VIA']))
	    { 
	        // 找不到为flase,否则为true
	        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	    } 
	    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
	    if (isset ($_SERVER['HTTP_USER_AGENT']))
	    {
	        $clientkeywords = array (
	        	'nokia',
	            'sony',
	            'ericsson',
	            'mot',
	            'samsung',
	            'htc',
	            'sgh',
	            'lg',
	            'sharp',
	            'sie-',
	            'philips',
	            'panasonic',
	            'alcatel',
	            'lenovo',
	            'iphone',
	            'ipod',
	            'blackberry',
	            'meizu',
	            'android',
	            'netfront',
	            'symbian',
	            'ucweb',
	            'windowsce',
	            'palm',
	            'operamini',
	            'operamobi',
	            'openwave',
	            'nexusone',
	            'cldc',
	            'midp',
	            'wap',
	            'mobile',
	            'ipad',
	            'windows phone',
	        ); 
	        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
	        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
	        {
	            return true;
	        } 
	    } 
	    // 协议法，因为有可能不准确，放到最后判断
	    if (isset ($_SERVER['HTTP_ACCEPT']))
	    { 
	        // 如果只支持wml并且不支持html那一定是移动设备
	        // 如果支持wml和html但是wml在html之前则是移动设备
	        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
	        {
	            return true;
	        } 
	    } 
	    return false;
	} 

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
