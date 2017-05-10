<?php

/**
 * common config parser
 * @author: wangjialong@baidu.com
 */

class Global_ConfigParser{

	public $conf = array();
	protected $config_filename;

	/**
	 * factory to make configParser
	 * @param
	 * @return
	 */
	public static function configerFactory($config_id){
		return new Global_ConfigParser($config_id);
	}

	/**
	 * load config from xml
	 * @param
	 * @return
	 */
	public function __construct($config_id){
		try{
			$this->config_filename = CONFIG_PATH.$config_id.'.Conf.php';
			$this->conf = require $this->config_filename;
        }
        catch(exception $e){
        	Bingo_Log::warning('config error:'.' 
				 	error_msg:'.$e->getMessage(), 'dal');
        	return flase;
        }
        return true;
	}

	/**
	 * get the config value by key
	 * @param
	 * @return
	 */
	public function get($key){
		if(!array_key_exists($key, $this->conf)){
			throw new Exception("$this->config_filename load error key $key", 
				Global_ErrorCode_Common::$COMMON_CONFIG_ERROR);
		}
		return $this->conf[$key];
	}

	/**
	 * get conf value which is xml formate
	 * @param
	 * @return
	 */
	public function loadConf($key, $val){
		try{
			$subNodes = $this->conf->getElementsByTagName($key);
			$item = $subNodes->item(0);
			$names = $item->getElementsByTagName($val);
  			$value = $names->item(0)->nodeValue; 
		}
		catch(exception $e){
			Bingo_Log::warning($e->getMessage(), 'dal');
			return false;
		}
		return $value;
	}

	/**
	 * load the config which is array formate
	 * @param
	 * @return
	 */
}
