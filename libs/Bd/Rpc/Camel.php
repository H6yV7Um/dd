<?php
/**
 * 对中间件Camel的支持。关于Camel的相关资料请访问
 * http://com.baidu.com/twiki/bin/view/Ns/TiebaPHPCamel
 * 
 * @author xuliqiang <xuliqiang@baidu.com>
 * @since 2011-03-21
 * 使用demo
require_once 'Bd/Rpc/Camel.php';
$objRpc = new Bd_Rpc_Camel('zycontrol');
$arrOutput = $objRpc->call(array(
        'cmd_no' => 0x201002,
        'forum_name' => 'test',
));
print_r($arrOutput);
 */
require_once 'Bd/Rpc/Abstract.php';
require_once 'Bingo/Log.php';
require_once 'Bingo/Timer.php';
class Bd_Rpc_Camel extends Bd_Rpc_Abstract
{
    protected $_strMethod = '';
    
    protected $_arrExtra  = array();
    /*    
     * 'bk'	=> 0,
     * 'retry' => 
     * 'ctimeout'
     * 'wtimeout'
     * 'rtimeout'
    */
    
    public function __construct($strServerName)
    {
        $this->_strServerName = (string) $strServerName;
        if (! extension_loaded('camel')) {
            throw new Exception('camel extension must be loaded for using Bd_Rpc_Camel!');
        }
        camel_set_logid(Bingo_Log::getLogId());
    }
    
    public function setOptions($arrConfig)
    {
        if (isset($arrConfig['bk'])) {
            $this->_arrExtra['bk'] = intval($arrConfig['bk']);
        }
        if (isset($arrConfig['retry'])) {
            $this->_arrExtra['retry'] = intval($arrConfig['retry']);
        }
        if (isset($arrConfig['ctimeout'])) {
            $this->_arrExtra['ctimeout'] = intval($arrConfig['ctimeout']);
        }
        if (isset($arrConfig['wtimeout'])) {
            $this->_arrExtra['wtimeout'] = intval($arrConfig['wtimeout']);
        }
        if (isset($arrConfig['rtimeout'])) {
            $this->_arrExtra['rtimeout'] = intval($arrConfig['rtimeout']);
        }
        if (isset($arrConfig['method'])) {
            $this->_strMethod = (string) $arrConfig['method'];
        }
        return $this;
    }
    
    public function setBalance($intBalance)
    {
        $this->_arrExtra['bk'] = intval($intBalance);
        return $this;
    }
    
    public function setMethod($strMethod)
    {
        $this->_strMethod = (string)$strMethod;
        return $this;
    }
    
    public function call($arrInput, $intRetry = NULL) 
    {
        if (!is_null($intRetry)) $this->_arrExtra['retry'] = intval($intRetry);
        return camel($this->_strServerName, $this->_strMethod, $arrInput, $this->_arrExtra);
    }
}