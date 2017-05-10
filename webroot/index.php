<?php
//错误定义
if (!defined('DEBUG') || DEBUG != true) {
    define('ONLINE', true);
}
if (!defined('ERROR_LEVEL')) {
    define('ERROR_LEVEL', 0);
}
error_reporting(ERROR_LEVEL);

//定义产品线
define("PRODUCT_LINE", "diandi");

//定义根目录
define('ROOT_PATH', realpath(dirname(__FILE__) . "/../") . "/");
if (!defined('ACTION_TYPE')) {
    define('ACTION_TYPE', "api");
}

$strBingoLibPath = ROOT_PATH . '/' . 'libs';
set_include_path(get_include_path() . PATH_SEPARATOR . $strBingoLibPath);
require_once "Global/Env.php";

//初始化环境变量
Global_Env::init(ACTION_TYPE);
try {
    //加载框架总入口FrontController
    require_once 'Bingo/Controller/Front.php';
    //国际化特殊路由规则，通过总路由作为分发
    require_once 'Global/Router/Trans.php';
    $objGlobalHttpRouter = new Global_Router_Trans();
    //分发入口
    $objFrontController = Bingo_Controller_Front::getInstance(array(
        'actionDir'             => ROOT_PATH . 'apps' . DIRECTORY_SEPARATOR,
        'usePathinfo'           => false,
        'actionSuffix'          => '',
        'actionClassNameSuffix' => '',
        'ActionNameWithPath'    => false,
        'httpRouter'            => $objGlobalHttpRouter,
    ));
    $objFrontController->dispatch();
} catch (Exception $e) {
    Bingo_Log::warning("[system error] errNo: {$e->getCode()} errMsg: {$e->getMessage()}");
    echo "system error";
    exit();
}