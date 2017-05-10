<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Token.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2014/12/15 21:17:04
 * @brief 
 * create & check token which contains infomation of content and expiretime
 *  
 **/





class Global_Util_Token
{
	CONST CONTENT_SEPEARTOR="__@@__@@";
	
	CONST TOKEN_ERRNO_NONEERR = 9000;
	CONST TOKEN_ERRNO_EXPIRED = 9001;
	CONST TOKEN_ERRNO_ERROR	  = 9002;
	CONST TOKEN_ERRNO_EMPYTKEY= 9003;
	
	
	public  static $errno = 9000;
	private static $errorMap = array(
			9000 => 'success',
			9001 => 'token expired',
			9002 => 'token error',
			9003 => 'empty key',
	);
	
    public static function create($msg,$key,$expireTime=3600)
    {
    	if(!$key){
    		self::$errno = self::TOKEN_ERRNO_EMPYTKEY;
    		return FALSE;
    	}
    	$endTime 	= time() + $expireTime;
    	$content 	= $msg . self::CONTENT_SEPEARTOR . $endTime;
    	$md5 		= md5(md5($content));
    	
    	$content 	= $content . self::CONTENT_SEPEARTOR . $md5;
    	$content 	= self::mcrypt($content,$key);
    	$token 		= base64_encode($content);
    	return $token;
    }

    public static function validate($token,$key,$endTime=NULL)
    {
		if(!$key){
			self::$errno = self::TOKEN_ERRNO_EMPYTKEY;
			return FALSE;
		}
		if($endTime == NULL){
			$endTime = time();
		}
		
    	//1.decode base64
    	$content = base64_decode($token);
    	//2.decode crypt
    	$content = self::mdecrypt($content, $key);

    	//3.check md5 of msg & endTime
    	list($msg,$met,$md5) = explode(self::CONTENT_SEPEARTOR,$content);
    	if($met < $endTime){
    		self::$errno =  self::TOKEN_ERRNO_EXPIRED;
    		return FAlSE;
    	}
    	if($md5 != md5(md5($msg.self::CONTENT_SEPEARTOR.$met))){
    		self::$errno = self::TOKEN_ERRNO_ERROR;
    		return FALSE;
    	}
    	
    	//4.return $msg,$endTime;
    	return $msg;
    	
    }
    
    private static function mcrypt($msg,$key)
    {
    	$key = md5($key);
    	$cipherText = '';
    	$mLen	= strlen($msg);
    	$kLen	= strlen($key);
    	$k = 0; 
    	for($i=0; $i<$mLen; $i++){
    		$m = substr($msg, $i, 1);
    		$s = substr($key,$k,1);
    		$c = $m ^ $s;
    		$cipherText  .= $c;
    		$k = ($k+1) % $kLen;
    	}
    	
    	return $cipherText;
    }
    
    private static function mdecrypt($cipherText,$key)
    {
    	$key=md5($key);
    	$msg='';
    	$ctLen	=strlen($cipherText);
    	$kLen	=strlen($key);
    	$k=0;
    	for($i=0;$i<$ctLen; $i++){
    		$c = substr($cipherText,$i,1);
    		$s = substr($key,$k,1);
    		$m = $c ^ $s;
    		$msg .= $m;
    		$k = ($k+1) % $kLen;
    	}
    	
    	return $msg;
    }
    
    public static function getError($errno=NULL)
    {
    	if(!$errno){
    		$errno = self::$errno;
    	}
    	
    	if(isset(self::$errorMap[$errno])){
    		return self::$errorMap[$errno];
    	}
    	else{
    		return 'Unkown error';
    	}
    }

}



/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
