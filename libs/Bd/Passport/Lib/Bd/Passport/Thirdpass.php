<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Third.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2014/05/13 13:13:01
 * @brief 
 *  
 **/

/**
 * @brief	Bd_Passport 第三方账号接入实现
 */
class Bd_Passport_Thirdpass implements Bd_Passport_IError {
	
	public static $errmsgMap = array ( 
		0   	=> '[Thirdpass] OK',
		12009   => '[Thirdpass]Invalid Params',
		13012   => '[Thirdpass]Unauthorized Ip',
		11013   => '[Thirdpass]Email exists',
		11012  	=> '[Thirdpass]Email error',
		11016 	=> '[Thirdpass]Acount was banned',
		14012  	=> '[Thirdpass]Dbgate error',
		14013  	=> '[Thirdpass]Session error',
		14019  	=> '[Thirdpass]Phoenix error',
		'default' => '[Thirdpass]unknown error',
	); 

	protected static $_instance = null;
	
	protected static $_apid		= null;
	protected static $_tpl		= null;

	protected static $_errno	= 0;
	protected static $_errmsg	= '';

	protected static $_conf  =null;
	protected static $_http =null;

	protected $config=null;

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
		$this->config = include(CONFIG_PATH."Ha.Conf.php");
		$this->_fixHaBaseUrl($this->config);

		self::$_apid		= Bd_Passport_Conf::getConf('apid');
		self::$_tpl			= Bd_Passport_Conf::getConf('tpl');
		self::$_conf 		= Bd_Passport_Conf::getConf('server');
		self::$_conf['servers']=self::$_conf['thirdpass'][self::$_conf['cur_idc']];
		if(!isset(self::$_conf['servers'])){
			Bd_Passport_Log::warning("No server for thirdpass service" , -1);
		}
		if (is_null(self::$_tpl)) {
			Bd_Passport_Log::warning("tpl for thirdpass Not Found!" , -1);
		}
		
		if (!defined('LOG_ID')) {
			define('LOG_ID' , Bd_Passport_Util::getLogId());
		}
		
		if (!defined('CLIENT_IP')) {
			define('CLIENT_IP' , Bd_Passport_Util::getClientIp());
		}

		self::$_http=new Bd_Rpc_Http('thridpass',self::$_conf['servers']);
	}
	
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		} 
		return self::$_instance;
	}
	
	public  function oatuhlogin($arrParams)
	{
		$arrParams['cluster']=self::$_conf['thirdpass']['cluster'];
		$arrParams['tpl']=self::$_tpl;
		$arrInput['url']='/global/gapi/?oauthlogin';
		$arrInput['method']='post';
		$arrInput['post_vars']=http_build_query($arrParams);
		$arrOutput=self::$_http->call($arrInput);
		if($error=self::$_http->getError())
		{
			self::$_errno='curl';
			self::$errmsgMap['curl']=$error;
			return false;
		}
		$arrOutput=json_decode($arrOutput,true);
		if($arrOutput['errno'] !=0)
		{
			self::$_errno=$arrOutput['errno'];
			return false;
		}
		return $arrOutput;
	}


	public function authenticate($provider)
	{
		$hybridauth = new Hybrid_Auth( $this->config );
		$adapter = $hybridauth->authenticate( $provider );
		return $adapter;
	}
	
	public function logout($provider)
	{
		$hybridauth = new Hybrid_Auth( $this->config );
		$adapter = $hybridauth->getAdapter( $provider ); 
		$adapter->logout();
	}

	private function _fixHaBaseUrl(&$config)
	{
		$config['base_url']="http://{$_SERVER['HTTP_HOST']}/{$config['base_url']}";
		return $config;
	}
	
}







/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
