<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @todo socket负载均衡
 * @package libs/Global/Balance 
 * @file Socket.php
 * @author lihongliang01@baidu.com
 * @date 2015年11月11日 下午6:58:41
 * 
 *  
 **/
class Global_Balance_Socket extends Global_Balance_Base {
	
	/**
	 * @param string $configFileName 
	 * @param string $configKey
	 * @param mixed $replace
	 * @return mixed
	 */
	public function getConnect($configFileName, $configKey=null, $replace=null) {
		if ($replace && is_array($replace)) {
			return fsockopen($replace['host'], $replace['port'], $errno, $errstr, 2);
		}
		$config = $this->getConfig($configFileName, $configKey);
		$result = $conf = false;
		for ($i = 0; $i < 2; $i++) {
			$key = $this->randKey($config);
			$conf = $config[$key];
			Bingo_Log::warning("cao ".json_encode($conf),'dal');
			$result = fsockopen($conf['host'], $conf['port'], $errno, $errstr, $conf['timeout']);
			if ($result==false) {
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
			$this->saveDisableConfig(null, $configFileName, $conf);
		
		}
		
		return $result;
	}
	
	/**
	 * 其他socket方式待扩展(socket_create ,stream等)
	 */
	public function getOtherConnect() {
		
	}
}
 
