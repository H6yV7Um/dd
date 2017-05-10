<?php
/**
 * Created by PhpStorm.
 * User: wangjialong
 * Date: 15-1-29
 * Time: 上午8:23
 * 通用工具类，加互斥锁，避免并发重入问题
 */

class Global_Util_Lock{

	//CONST mqLockTime = 2; //锁的最大等待时间，即最大持有锁时间 此处设为2s

	/**
	 * 获取消息队列互斥所
	 * 该锁的生命只有self::$mqLockTime(s),过了改时间，不保证锁还存在
	 * @param  $lockName : 锁名称，必需要的参数，对应不同业务名称，$waitTime：锁等待时间，默认2s
	 * $mqLockTime:最大持有锁时间，默认为2s
	 * @access 公用方法
	 * @return true or false,对应加锁状态
	 */
	public static function mqLock($lockName,$waitTime=2,$mqLockTime=2)
	{
		$noWait = 0;
		$redis  = Global_Redis_RedisInit::getInstance();
		$ret = $redis->setnx($lockName,time() + $mqLockTime);
		if($ret){
			Bingo_Log::notice("HiClub_Log:: $lockName get lock success  1",'dal');
			return true;
		}
		//锁已存在，两种情况，1、资源处于被锁状态；2、锁已失效
		if($waitTime <= 0){
			$noWait = 1; //由于锁可能已经失效，不等待任务只尝试获取锁1次，置非等待标志为1
		}
		//尝试等待锁，以防止死锁
		$startTime = time();
		while($ret = $redis->get($lockName)){
			if( !$ret || $ret < time() ){
				$endTime = time() + $mqLockTime;
				$ret2 = $redis->getset($lockName,$endTime);
				//锁已经被其其他竞争者得到
				if($ret2 != $ret){
					Bingo_Log::notice('HiClub_Log::get lock failed 2','dal');
					return false;
				}
				else{
					Bingo_Log::notice('HiClub_Log::get lock success  2','dal');
					return true;
				}
			}
			if($noWait==1){
				break;
			}
			usleep(10000);
			if(time() - $startTime > $waitTime){
				break;
			}
		}
		Bingo_Log::notice('HiClub_Log::get lock failed 3','dal');
		return false;
	}

	/**
	 * @param 锁名称，对应不同的业务名称
	 * @return ,返回被移除键的数量
	 * 释放消息队列互斥所
	 */
	public static function mqUnlock($lockName)
	{
		$redis = Global_Redis_RedisInit::getInstance();
		return $redis->del($lockName);
	}


}