<?php

/**
 * 单例工厂
 * Class Global_BaseFactory
 * author: chusonghui@baidu.com
 * time: 16/8/25 下午2:04
 */
abstract class Global_BaseFactory {
    /**
     * @var array
     */
    private static $_instances = [];

    /**
     * 限制子类的构造方法权限级别, 因为比protected更高的级别, 该工厂类无法访问(new).
     * Global_BaseFactory constructor.
     */
    protected function __construct() {

    }

    /**
     * @return mixed
     */
    public static function getInstance() {
        $className = self::getClassName();
        if(!(self::$_instances[$className] instanceof $className)) {
            self::$_instances[$className] = new $className();
        }

        return self::$_instances[$className];
    }

    /**
     * @return bool
     */
    public static function removeInstance() {
        $className = self::getClassName();
        if(array_key_exists($className, self::$_instances)) {
            unset(self::$_instances[$className]);
        }

        return true;
    }


    /**
     * @return string
     */
    final protected static function getClassName() {
        return get_called_class();
    }

    /**
     * 禁止对象克隆
     */
    final private function __clone() {}

}