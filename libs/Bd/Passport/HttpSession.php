<?php
/***************************************************************************
 * 
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
/**
 * @file Bd/Passport/Session.php
 * @author fanmengzhe(com@baidu.com)
 * @date 2011/05/23 21:43:49
 * @brief 
 *  
 **/

/**
 * @brief	Bd_Passport Session服务交互实现
 */
class Bd_Passport_HttpSession {
	/* session command list */
	const SSN_GET_SESSION_ID 			= 0x00101;
	const SSN_GET_SESSION_DATA_BY_SID 	= 0x00102; #十进制258
	const SSN_SET_LOGINED_SESSION 		= 0x00103;
	const SSN_SET_OFFLINE_SESSION 		= 0x00104;
	const SSN_MOD_SESSION_DATA 			= 0x00201;
	const SSN_ADMIN_SET_OFFLINE_SESSION = 0x00301;
	const SSN_ADMIN_MOD_SESSION_DATA 	= 0x00302;
	const SSN_ADMIN_GET_SESSION_DATA 	= 0x00303;
	const SSN_SYS_STATUS_TRACE 			= 0x00400;
	const SSN_SYS_STATUS_DEBUG 			= 0x00002;

	const PUBLIC_DATA_LEN			= 32;
	
	public static $errmsgMap = array ( 
		0   => '[Session]OK',
		1   => '[Session]Unauthorized Ip and Apid',
		2   => '[Session]Invalid SID',
		3   => '[Session]Invalid Params',
		4   => '[Session]Login Exceed',
		16  => '[Session]Server Busy',
		18  => '[Session]Internal Error',
		32  => '[Session]Offline',
		33  => '[Session]Online',
		34  => '[Session]Multi Online',
		48  => '[Session]Unauthorized TPL',
		49  => '[Session]Auth Login Exceed',
		50  => '[Session]Check Token Failed',
	); 

	protected static $_instance = null;
	
	protected static $_apid		= null;
	protected static $_tpl		= null;
	protected static $_pass		= null;		/** use $_pass @ bae-env */
    protected static $_is_orp	= false;

	protected static $_errno	= 0;
	protected static $_errmsg	= '';


	protected static $_http_rpc	=null;
	protected static $_passConf =array();


	/** implemention of IError*/
	public function isError() {
		return self::$_errno === 0 ? false : true;
	}
	
	public function getCode() {
		return self::$_errno;
	}

	public function getMessage() {
		if (isset(self::$errmsgMap[self::$_errno])) {
			self::$_errmsg = self::$errmsgMap[self::$_errno];
		} else {
			self::$_errmsg = 'Unknown Error';
		}
		return self::$_errmsg;
	}

	
	protected function __construct() {
		self::$_passConf 	= include_once(ROOT_PATH.'config/Pass.Conf.php');
		self::$_apid		= self::$_passConf['tpl']['apid'];
		self::$_tpl			= self::$_passConf['tpl']['tpl'];
		self::$_pass		= self::$_passConf['tpl']['passwd'];
		if(!self::$_passConf)
			return;
		self::$_http_rpc 	= new Bd_Rpc_Http('pass',array(self::$_passConf['session']));
	}
	
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public static function getData(){


	}


	protected static $__httpGet;

    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  parseGData 
        *
        * @Param $gdata
        *
        * @Returns   
        *
        * @author fubendong(com@baidu.com)
        *
        * @date 2013-4-7 15:29:03
     */
    /* ----------------------------------------------------------------------------*/

	public function parseGData($gdata) {
		$arrData = unpack("C*" , $gdata);
            
        if (count($arrData) != self::PUBLIC_DATA_LEN) {
            return false;
        }   
        $arrTest = array();
        foreach($arrData as $key => $val) {
            $arrTest[] = $val;
        }   
        $arrData = $arrTest;

        $arrGData = array(
            'verifyQuestion'    => ($arrData[0] & 0x01) >> 0,
	        'verifyMobile'      => ($arrData[0] & 0x02) >> 1,
	        'verifyEmail'       => ($arrData[0] & 0x04) >> 2,
	        'passwordWeak'      => ($arrData[0] & 0x08) >> 3,
	        'passwordRemember'  => ($arrData[0] & 0x10) >> 4,
	        'openSpace'         => ($arrData[0] & 0x20) >> 5,
	        'openApp'           => ($arrData[3] & 0x01) >> 0,
	        'passwordExweak'    => ($arrData[3] & 0x02) >> 1,
	        'openFavo'          => ($arrData[3] & 0x04) >> 2,
	        'openSuperPC'       => ($arrData[3] & 0x08) >> 3,
            'pwd_protected'     => ($arrData[0] & 0x01) >> 0,
            'verified_mobil'    => ($arrData[0] & 0x02) >> 1,
            'verified_email'    => ($arrData[0] & 0x04) >> 2,
            'weak_pwd'          => ($arrData[0] & 0x08) >> 3,
            'rem_pwd'           => ($arrData[0] & 0x10) >> 4,
            'space'             => ($arrData[0] & 0x20) >> 5,
            'sex'               => ($arrData[0] & 0xc0) >> 6,
            'appstore'          => ($arrData[3] & 0x01) >> 0,
            'weakest_pwd'       => ($arrData[3] & 0x02) >> 1,
            'search'            => ($arrData[3] & 0x04) >> 2, // favo 
            'super'             => ($arrData[3] & 0x08) >> 3, 
            'search2'           => ($arrData[3] & 0x10) >> 4,
            'new_super'         => ($arrData[3] & 0x20) >> 5,
            'old_user'          => ($arrData[3] & 0x40) >> 6,
            'usersource'        => ($arrData[4] & 0x3f),
			'account'           => ($arrData[4] & 0xff),
            'device'            => ($arrData[5] & 0xff),
			'incomplete_user'   => ($arrData[6] & 0x01) >> 0,

        );   

        return $arrGData;
	}
}



/* vim: set expandtab ts=4 sw=4 sts=4 tw=100 */
?>
