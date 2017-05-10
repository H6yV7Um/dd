<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @todo mysql负载均衡连接实现
 * @package libs/Global/Balance 
 * @file Mysql.php
 * @author lihongliang01@baidu.com
 * @date 2015年9月29日 下午6:35:44
 * 
 *  
 **/
class Global_Balance_Mysql extends Global_Balance_Base {
	
	/**
	 * 连接
	 * @param $configFileName 	配置文件名称
	 * @param $dbObject			数据库连接对象
	 * @param $configKey		
	 * @return bool
	 */
	public function getConnect($configFileName, $dbObject, $configKey=null) {
		$config = $this->getConfig($configFileName, $configKey);
		$result = $conf = false;
		for ($i = 0; $i < 2; $i++) {
			$key = $this->randKey($config);
			$conf = $config[$key];
			try {
				$result = $dbObject->connect($conf['host'], $conf['username'], $conf['password'], $conf['dbname'], $conf['port']);
			} catch (Exception $e) {
				Bingo_Log::warning($e->getMessage(), 'dal');
				$disableConfig = array(
					'host' => $conf['host'],
					'port' => $conf['port'],
					'count' => 1,
					'timeStamp' => time(),
				);
				$this->saveDisableConfig($disableConfig, $configFileName);
				unset($config[$key]);
				continue;
			}
			break;
		}
		//成功以后 删除掉disable里面的配置
		if ($result) {
			$dbObject->charset($conf['charset']);
			$this->saveDisableConfig(null, $configFileName, $conf);
		
		}

		return $result;
	}
}
 
