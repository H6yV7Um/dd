<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @todo redis session handler
 * @file RedisSession.php
 * @author lihongliang01@baidu.com
 * @date 2014-7-7 
 * @time 下午7:30:41
 * 
 *  
 **/
class Global_Redis_RedisSession {
  
	static $store; 
	static $timeout;
	static $prefix;
	
	public static function init() {
		$sessionConf = require(CONFIG_PATH.'Redis.Session.Conf.php');
		$name = $sessionConf['cookie'];
		$level = $sessionConf['level'];
		self::$timeout = $sessionConf['timeout'];

		self::$prefix = $sessionConf['cookie'];

		if ($level == 'high') {
			$cookieLifeTime = 0;
		} else {
			$cookieLifeTime = self::$timeout;
		}

		if (empty($_SESSION) && function_exists('ini_set')) {
			ini_set('session.use_trans_sid', 0);
			ini_set('url_rewriter.tags', '');
			ini_set('session.save_handler', 'user');
			ini_set('session.serialize_handler', 'php');
			ini_set('session.use_cookies', 1);
			ini_set('session.use_only_cookies', 1);
			ini_set('session.name', $name);
			ini_set('session.cookie_lifetime', $cookieLifeTime);
			ini_set('session.cookie_path', '/');
			ini_set('session.cookie_httponly', 1);
			ini_set('session.auto_start', 0);
		}

		session_set_save_handler( 
			array('Global_Redis_RedisSession', 'open'), 
			array('Global_Redis_RedisSession', 'close'), 
			array('Global_Redis_RedisSession', 'read'), 
			array('Global_Redis_RedisSession', 'write'), 
			array('Global_Redis_RedisSession', 'destroy'), 
			array('Global_Redis_RedisSession', 'gc') 
		);

	}
  
	/**
	 * OPEN
	 * - Connect to Redis
	 * - Calculate and set timeout for SETEX
	 * - Set session_name as key prefix
	 */
	public static function open($path, $name) {
		$redis=Global_Redis_RedisInit::getInstance();
		self::$store = $redis;
	}
  
	/**
	 * CLOSE
	 * - Disconnect from Redis
	 */
	public static function close() {
		//self::$store->disconnect();
		self::$store->close();
		return true;
	}
  
	/**
	 * READ
	 * - Make key from session_id and prefix
	 * - Return whatever is stored in key
	 */	 
	public static function read($id) {
		$key = self::$prefix. '_' . $id;
		return self::$store->get($key);
	}
  
	/**
	 * WRITE
	 * - Make key from session_id and prefix
	 * - SETEX data with timeout calculated in open()
	 */
	public static function write($id, $data) {
		$key = self::$prefix. '_' . $id;
		self::$store->setex($key, self::$timeout, $data);
		return true;
	}
  
	/**
	 * DESTROY
	 * - Make key from session_id and prefix
	 * - DEL the key from store
	 */
	public static function destroy($id) {
		$key = self::$prefix. '_' . $id;
		self::$store->del($key);
		return true;
	}
  
	/**
	 * GARBAGE COLLECTION
	 * not needed as SETEX automatically removes itself after timeout
	 * ie. works like a cookie
	 */
	public static function gc() {
		return true;
	}
	
  }