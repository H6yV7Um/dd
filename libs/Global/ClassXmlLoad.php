<?php

/**
 * 动态扩展类载入
 * 提供加载观察者注册事件方法，供消息通知者通知使用
 */

class Global_ClassXmlLoad{

	public $classMap = array();
	public $errorno;

	/**
	 * 加载xml配置
	 * @param
	 * @return
	 */
	public function __construct($configPath){
		try{
			$xml = simplexml_load_file($configPath);
			foreach($xml->children() as $child){
				$id = $child->getName();
				$class = $child->class->__tostring();
                $func  = $child->func->__tostring();
                $this->classMap[$id] = array(
                	'class' => $class,
                	'func'  => $func,
                );
			}
        }
        catch(exception $e){
        	$this->errno = Global_ErrorCode_Common::COMMON_CONFIG_ERROR;
        	Bingo_Log::warning('xml config error:'.$this->errno.' 
				 	error_msg:'.$e->getMessage());
        	return flase;
        }
        return true;
    }

}