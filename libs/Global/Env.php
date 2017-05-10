<?php
define('WEBROOT_PATH', ROOT_PATH . 'webroot/');
define('DATA_PATH', ROOT_PATH . 'webroot/data/');
define('APPS_PATH', ROOT_PATH . 'apps/');
define('TEMPLATE_PATH', ROOT_PATH . 'templates/');
define('TEMPLATE_COMPLITE_PATH', ROOT_PATH . 'templates_c/');
define('TEMPLATE_CACHE_PATH', ROOT_PATH . 'templates_cache/');
//define('SMARTY_PLUGINS_DIR', ROOT_PATH . 'libs/Third/Smarty/plugins/');
define('SMARTY_PLUGINS_DIR', ROOT_PATH . 'plugin/');
define('LIB_PATH', ROOT_PATH . 'libs/');
define('LOG_PATH', ROOT_PATH . 'log/');
define('MODEL_PATH', ROOT_PATH . 'apps/model/');
define('SERVICE_PATH', ROOT_PATH . 'apps/service/');
define('SESSION_NAME', 'SID');
define('CONFIG_ROOT', ROOT_PATH . 'config/');
define('ACTION_PATH', APPS_PATH . '/actions/' . ACTION_TYPE . "/");
define('DEFAULT_ACTION_DIR', 'Index');
define('DEFAULT_ACTION', 'Index');
define('DEFAULT_VERSION', '1.0');
//定义module的路径
define('MODULE_PATH', APPS_PATH . PRODUCT_LINE . '/module/');
if (defined("ONLINE") && ONLINE === true) {
    define('CONFIG_PATH', CONFIG_ROOT . 'online/');
} elseif (defined("SANDBOX") && SANDBOX === true) {
    define('CONFIG_PATH', CONFIG_ROOT . 'sandbox/');
} else {
    define('CONFIG_PATH', CONFIG_ROOT . 'debug/');
}

// 冲缓存
if (isset($_GET['NOCACHE']) && $_GET['NOCACHE'] == 1) {
    define('NOCACHE', true);
} else {
    define('NOCACHE', false);
}
spl_autoload_register(array('Global_Env', 'LoadClass'));

/**
 * 国际化环境初始化类
 * @author  mozhuoying <mozhuoying@baidu.com>
 * @package global
 * @since   2014-04-28
 */
class Global_Env {
    //域名对应国家
    public static $country = '';
    //host
    public static $host = '';
    //产品线名称
    public static $productLine = '';
    //uri字符组
    public static $uri = '';
    //uri数组
    public static $arrUri = array();
    public static $arrTime;

    //页面对应数据路径
    public static $privateDataPath = '';
    //页面对应公有数据路径
    public static $baseDataPath = '';
    //数据文件后缀
    public static $dataName = "data.php";
    //命令行参数
    public static $cli_param = array();

    /**
     * 初始化api环境
     *
     * @param
     *
     * @return
     */
    public static function init($action_type = "api") {
        self::_initSession();
        self::$host = $_SERVER['HTTP_HOST'];
        $uri        = self::_getUriStr();
        self::_init_Bingo();
        //根据域名设置uri
        self::_setUri($uri);
        if ($action_type == "web") {
            self::_setCmsDataPath();
        }

    }

    /**
     * 自动类加载
     *
     * @param
     *
     * @return
     */
    public static function LoadClass($className = '') {
        //其他LIB_PATH
        $arrName       = explode("_", $className);
        $classType     = $arrName[count($arrName) - 1];
        $tempClassName = array_diff($arrName, array($classType));
        foreach ($tempClassName as &$u) {
            $u = ucfirst($u);
        }
        $filePath        = implode(DIRECTORY_SEPARATOR, $tempClassName);
        $request_version = empty($_REQUEST["VERSION"]) ? DEFAULT_VERSION : $_REQUEST["VERSION"];
        switch ($classType) {
            case "Action":
                $filePaths[] = ACTION_PATH . $request_version . "/" . $filePath . ".action.php";
                break;
            case "Service":
                $filePaths[] = SERVICE_PATH . $filePath . ".service.php";
                break;
            case "Model":
                $filePaths[] = MODEL_PATH . $filePath . ".model.php";
                break;
            case "Config":
                $filePaths[]  = CONFIG_PATH . $filePath . ".conf.php";
                $strClassPath = implode(DIRECTORY_SEPARATOR, $arrName) . ".php";
                $filePaths[]  = LIB_PATH . 'Bd/Passport/Lib/' . $strClassPath;
                break;
            case "Module":
                $filePaths[] = MODULE_PATH . $filePath . ".class.php";
                break;
            case "Interface":
                $filePaths[] = SERVICE_PATH . $filePath . ".interface.php";
                break;
            default:
                $strClassPath = implode(DIRECTORY_SEPARATOR, $arrName) . ".php";
                $filePaths[]  = LIB_PATH . $strClassPath;
                $filePaths[]  = LIB_PATH . 'Bd/Passport/Lib/' . $strClassPath;
                break;
        }

        foreach ($filePaths as $fp) {
            if (is_file($fp)) {
                include_once($fp);
                break;
            }
        }

    }

    /**
     * @param
     *
     * @return
     */
    public static function getRealip() {
        $ip = false;
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (null !== ($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ips = $_SERVER["HTTP_X_FORWARDED_FOR"];
            $ips = explode(',', $ips);
            $ip  = $ips[0];
        } else if (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }

    /**
     * @param
     *
     * @return
     */
    public static function timeAdjust($serverTime) {
        $cty        = self::$country;
        $arrTime    = self::$arrTime;
        $timeDiff   = $arrTime[$cty];
        $gdate      = gmdate("Y-m-d H:i:s", $serverTime);
        $servertime = strtotime($gdate);
        $newtime    = $servertime - $timeDiff * 60;
        return $newtime;
    }


    /**
     *目标国家时间转换为服务时间，前提：服务器位于东八区
     *
     * @param
     *
     * @return
     */
    public static function timeReverse($clientTime) {
        $cty      = self::$country;
        $arrTime  = self::$arrTime;
        $timeDiff = $arrTime[$cty];
        $timeDiff = 480 + $timeDiff;    //东八区，北京时间
        $newTime  = $clientTime + $timeDiff * 60;
        return $newTime;
    }


    /**
     * 转换相对数据路径为绝对路径
     *
     * @param
     *
     * @return
     */
    public static function transPath($relativeDataPath) {
        return DATA_PATH . '/' . ltrim($relativeDataPath, "/");
    }

    /**
     * @param
     *
     * @return
     */
    private static function _setCmsDataPath() {
        self::$privateDataPath = self::transPath(self::$uri . "/" . self::$dataName);
        if (!file_exists(self::$privateDataPath)) {
            self::$privateDataPath = self::transPath(self::$uri . "/index/" . self::$dataName);
        }
        self::$baseDataPath = self::transPath('base/' . self::$dataName);

    }


    /**
     * 初始化session
     *
     * @param
     *
     * @return
     */
    private static function _initSession() {
        $sessionRedisConfig = include_once(CONFIG_PATH . 'RedisCluster.Conf.php');
        $redisServers       = $sessionRedisConfig['session'];
        foreach ($redisServers as $server) {
            $success = self::startSession($server);
            if ($success) {
                break;
            }
        }
    }

    /**
     *
     * @param
     *
     * @return
     */
    private static function startSession($redisServer) {
        ini_set('session.save_handler', "redis");
        ini_set('session.save_path', "tcp://$redisServer");
        session_name(SESSION_NAME);
        if (isset($_REQUEST[SESSION_NAME]) && !empty($_REQUEST[SESSION_NAME])) {
            session_id($_REQUEST[SESSION_NAME]);
        }
        $success = session_start();
        if ($success) {
            $_REQUEST[SESSION_NAME] = session_id();
        }
        return $success;
    }

    /**
     * 设置uri
     *
     * @param
     *
     * @return
     */
    private static function _setUri($uri) {
        $uri          = trim($uri, '/');
        $uri          = empty($uri) ? "index" : $uri;
        $arrUri       = explode('/', $uri);
        self::$uri    = $uri;
        self::$arrUri = $arrUri;
    }

    /**
     *
     * @param
     *
     * @return
     */
    private static function _getUriStr() {
        if (!self::is_cli()) {
            $strQ = isset($_SERVER['SCRIPT_URL']) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REQUEST_URI'];
            if (ACTION_TYPE == "web") {
                if (preg_match('/^\/api\//', $strQ)) {
                    // api不处理, 返回json
                }
            }
            $strQ = str_replace('?', '&', $strQ);
            $arrQ = explode('&', $strQ);
            return isset($arrQ[0]) ? $arrQ[0] : '';
        } else {
            $argc            = $_SERVER["argc"];
            $argv            = $_SERVER["argv"];
            $strQ            = $argv[1] . DIRECTORY_SEPARATOR . $argv[2] . DIRECTORY_SEPARATOR . $argv[3];
            self::$cli_param = array_slice($argv, 4);
            return $strQ;
        }

    }

    /**
     *
     * @param
     *
     * @return
     */
    public static function is_cli() {
        return (PHP_SAPI === 'cli' || defined('STDIN'));
    }

    /**
     *
     * @param
     *
     * @return
     */
    public static function getPlatForm() {
        $os = $_REQUEST["OS"];
        if (empty($os)) {
            return "ios";
        }
        return $os;
    }

    /**
     *
     * @param
     *
     * @return
     */
    private static function _init_Bingo() {
        $logConfig = include_once(CONFIG_PATH . "Log.Conf.php");
        Bingo_Log::init($logConfig, 'ui');
    }


}
