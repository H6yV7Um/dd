<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @todo 负载均衡的基类
 * @package Global/Balance 
 * @file Base.php
 * @author lihongliang01@baidu.com
 * @date 2015年9月28日 下午5:22:04
 * 
 *  
 **/
abstract class Global_Balance_Base {
	
	/**
	 * 不可用配置文件路径
	 * @var string
	 */
	protected $disableDir = 'disable';
	
	/**
	 * 重试时间间隔s
	 * @var int
	 */
	protected $retryInterval = 5; 
	
	/**
	 * 被认为下线的次数
	 * @var int
	 */
	protected $disableCount = 50;
	
	/**
	 * 获取连接
	 * @param string $configFileName
	 * @param mixed $dbObject
	 */
	abstract public function getConnect($configFileName, $dbObject);
	
	/**
	 * 获取可用配置列表
	 * @param string $configFileName 配置文件名称
	 * @return mixed
	 */
	public function getConfig($configFileName, $configKey=null) {
		$allConfig = $config = include CONFIG_PATH . $configFileName;
		if (empty($config)) {
			Bingo_Log::warning('No config of '. $configFileName, 'dal');
			return false;
		}
		if ($configKey && empty($config[$configKey])) {
			Bingo_Log::warning('No config of key  '. $configKey, 'dal');
			return false;
		}
		if ($configKey) {
			$config = $config[$configKey];
			$allConfig = $allConfig[$configKey];
		}
		$disableConfig = $this->getDisableConfig($configFileName);
		if (empty($disableConfig)) {
			return $config;
		}
		//遍历判断不可用列表是否到了下线次数，是否到了重试间隔，去掉不可用服务器
		$now = time();
		foreach ($config as $k=>$conf) {
			foreach ($disableConfig as $j=>$disConf) {
				if (($now-$disConf['timeStamp']) >= $this->retryInterval) {
					//到达了重试间隔
					unset($disableConfig[$j]);
					continue;
				}
				//没有到达下线次数,不是真正的不可用
				if ($disConf['count'] < $this->disableCount) {
					unset($disableConfig[$j]);
					continue;
				} 
				//真正的不可用
				if ($conf['host'] == $disConf['host']) {
					unset($config[$k]);
				}
			}
		}
		//var_dump($config);exit();
		return empty($config) ? $allConfig : $config;
	}
	
	/**
	 * 获取不可用的配置列表
	 * @param sting $configFileName 配置文件名称
	 * @return array
	 */
	public function getDisableConfig($configFileName) {
		$fp = @fopen(CONFIG_PATH . $this->disableDir . DIRECTORY_SEPARATOR . $configFileName, 'r');
		if ($fp) {
			if (flock($fp, LOCK_EX)) {
				$config = fread($fp, 1024);
				flock($fp, LOCK_UN);
			}
			fclose($fp);
			return json_decode($config, true);
		}
		return null;
	}
	
	/**
	 * 保存配置到不可用文件
	 * @param array $config				配置数组
	 * @param string $configFileName	配置文件名称
	 * @param array $replace			替换的配置
	 * @return mixed
	 */
	public function saveDisableConfig($config, $configFileName, $replace=null) {
		$disableConfig = $this->getDisableConfig($configFileName);
		if (empty($disableConfig)) {
			if (!empty($config)) {
				$disableConfig[] = $config;
			} else {
				return null;
			}
		} else {
			//遍历已经有的配置如果到了重试时间或者到了次数去掉配置，如果等于当前的配置区分以上两种和正常的
			$now = time();
			$in = false;
			foreach ($disableConfig as $k=>&$conf) {
				if (isset($replace['host']) && ($replace['host']==$conf['host'])) {
					unset($disableConfig[$k]);
					continue;
				}
					//判断下线时间是否到了重试时间间隔 
				if (($now-$conf['timeStamp']) > $this->retryInterval) {
					unset($disableConfig[$k]);
				} elseif (isset($config['host']) && ($config['host'] == $conf['host'])) {
					$conf['count'] += 1;
					$conf['timeStamp'] = $now;
					$in = true;
				}
			}
			if (!$in && !empty($config)) {
				$disableConfig[] = $config;
			}
		}
		$content = json_encode($disableConfig);
		Bingo_Log::warning('Save disable config '.$content);
		$fp = fopen(CONFIG_PATH . $this->disableDir . DIRECTORY_SEPARATOR . $configFileName, 'w+');
		if (flock($fp, LOCK_EX)) {
			fwrite($fp, $content);
			flock($fp, LOCK_UN);
		}
		return fclose($fp);
		//return file_put_contents(CONFIG_PATH . $this->disableDir . DIRECTORY_SEPARATOR . $configFileName, $content, LOCK_EX); 
	}
	
	/**
	 * 随机配置键
	 * @param array $config
	 * @return mixed
	 */
	public function randKey(array $config) {
		return array_rand($config);
	}
	
	
	/**
	 * 
	 * 
	 * 
	 * 
	 * array(
	 * 	'ip' => '',
	 * 	'port' => '',
	 * 	'count' => '',
	 *  'timeStamp' => '',
	 * )
	 */
	
	
}