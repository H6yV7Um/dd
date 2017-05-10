<?php

/**
 * 国际化http路由接管类，国际化使用通用的路由规则
 * @author mozhuoying <mozhuoying@baidu.com>
 * @package global
 * @since 2014-04-28        
 */
require_once 'Bingo/Http/Router/Abstract.php'; 
class Global_Router_Trans extends Bingo_Http_Router_Abstract {
    private $transToUrl = "DispatchEntrance";
    //转换国际化路由 
    public function getHttpRouter() { 
        $strRouter = $this->transToUrl;
        Bingo_Http_Request::setHttpRouter($strRouter, array($strRouter)); 
        return $strRouter;
     } 
}