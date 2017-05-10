<?php
/**
 * bd_socket，负载均衡采用bd_socket内置的。
 * @author xuliqiang <xuliqiang@baidu.com>
 * @TODO timer,log
 *
 */
require_once 'Bd/Rpc/Abstract.php';
require_once 'Bingo/Timer.php';
class Bd_Rpc_Bdsock extends Bd_Rpc_Abstract
{    
    protected $_resConn = false;
    protected $_intBalanceKey = false;
    
    protected $_strAlgorithm = 'Default';
    
    protected $_intConnectTimeout = 100;
    
    protected $_intReadTimeout = 200;
    
    protected $_intWriteTimeout = 200;
    
    public function __construct($strServerName, $arrServers=array())
    {
        if (! extension_loaded('bd_socket')) {
            throw new Exception('bd_socket extension must be loaded for using Bingo_Rpc_Bdsock!');
        }
        $this->_strServerName = $strServerName;
        $arrConf = bd_get_server_info ($this->_strServerName, false);
        if (false === $arrConf || !isset($arrConf['read_timeout_ms'])
            || !isset($arrConf['conn_timeout_ms'])
            || !isset($arrConf['write_timeout_ms'])
        ) {
            Bingo_Log::warning(sprintf("bd_get_server_info for server %s failed", 
                $this->_strServerName), $this->_strLogModule);
            return false;
        } else {
            $this->_intConnectTimeout = intval($arrConf['conn_timeout_ms']);
            $this->_intReadTimeout = intval($arrConf['read_timeout_ms']);
            $this->_intWriteTimeout = intval($arrConf['write_timeout_ms']);
        }
    }
    
    public function setOptions($arrConfig)
    {
        if (isset($arrConfig['balanceKey'])) $this->_intBalanceKey = $arrConfig['balanceKey'];
        if (isset($arrConfig['algorithm'])) $this->_strAlgorithm = $arrConfig['algorithm'];     
        return $this;
    }
    
    public function strCall($strInput, $intRetry = 1)
    {
        $strOutput = '';
        while ($intRetry -- ) {
            //connect
            $this->connect();
            if (false === $this->_resConn) {
                Bingo_Log::warning(sprintf('connect %s failure!', 
                    $this->_strServerName), $this->_strLogModule);
                continue;
            }
            //write
            $intSend = $this->write($strInput);
            if (false === $intSend) {
                Bingo_Log::warning(sprintf('write data to  %s failure!', 
                    $this->_strServerName), $this->_strLogModule);
                continue;
            }
            //receive
            $strData = $this->receive();
            if (false === $strData) {
                Bingo_Log::warning(sprintf('receive data from  %s failure!', 
                    $this->_strServerName), $this->_strLogModule);
                continue;
            }
            return $strData;
        }
        return false;
    }
    
    public function call($arrInput, $intRetry = 1)
    {
        $arrOutput = array();
        while ($intRetry -- ) {
            //connect
            $this->connect();
            if (false === $this->_resConn) {
                Bingo_Log::warning(sprintf('connect %s failure!', $this->_strServerName), 
                    $this->_strLogModule);
                continue;
            }
            //send
            $strData = $this->packInput($arrInput);
            if (false === $strData) {
                continue;
            }
            $intSend = $this->write($strData);
            if (false === $intSend) {
                Bingo_Log::warning(sprintf('write data to  %s failure!', 
                    $this->_strServerName), $this->_strLogModule);
                continue;
            }
            //receive
            $strData = $this->receive();
            if (false === $strData) {
                Bingo_Log::warning(sprintf('receive data from  %s failure!', 
                    $this->_strServerName), $this->_strLogModule);
                continue;
            }
            //unpack
            $arrOutput = array();
            if (strlen($strData) > 0) {
                $arrOutput = $this->unpackOutput($strData);
                if (false === $arrOutput) {
                    Bingo_Log::warning(sprintf('unpackOutput failure, server=%s!', 
                        $this->_strServerName), $this->_strLogModule);
                    continue;
                }
            }
            return $arrOutput;
        }
        return false;
    }
    
    public function connect()
    {
        $this->close();
        Bingo_Timer::start('bdsock_connect');
        $this->_resConn = bd_get_socket_ex($this->_strServerName, $this->_intBalanceKey, 
            $this->_strAlgorithm);
        Bingo_Timer::end('bdsock_connect');
        Bingo_Log::debug(sprintf('connect %s time[%d]', $this->_strServerName, 
            Bingo_Timer::calculate('bdsock_connect')), $this->_strLogModule);
    }
    
    public function write($strData)
    {
        Bingo_Timer::start('bdsock_send');
        $intSend = bd_socket_write ($this->_resConn, $strData, $this->_intWriteTimeout);
        Bingo_Timer::end('bdsock_send');
        Bingo_Log::debug(sprintf('send %s time[%d]', $this->_strServerName, 
            Bingo_Timer::calculate('bdsock_send')), $this->_strLogModule);
        return $intSend;
    }
    
    public function receive()
    {
        Bingo_Timer::start('bdsock_receive');
        $strData = $this->_receive();
        Bingo_Timer::end('bdsock_receive');
        Bingo_Log::debug(sprintf('receive %s time[%d]', $this->_strServerName, 
            Bingo_Timer::calculate('bdsock_receive')), $this->_strLogModule);
        return $strData;
    }
    
    public function close()
    {
        if ($this->_resConn) {
            bd_socket_close($this->_resConn);
        }
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    protected function _receive()
    {
        if ($this->_strHead == 'nshead') {
            $arrRet = bd_nshead_read ($this->_resConn, $this->_intReadTimeout);
            if (false === $arrRet || ! array_key_exists('body', $arrRet)) {
                Bingo_Log::warning('bd_nshead_read ' . $this->_strServerName . ' error!',
                    $this->_strLogModule);
                return false;
            }
            return $arrRet['body'];
        } elseif ($this->_strHead == 'shead') {
            require_once 'Bd/Shead.php';            
            $strData = bd_socket_read ($this->_resConn, Bd_Shead::LEN, $this->_intReadTimeout);
            if (false === $strData) {
                Bingo_Log::warning('bd_socket_read shead ' . $this->_strServerName . ' error!',
                    $this->_strLogModule);
                return false;
            }
            $arrShead = Bd_Shead::unpack($strData);
            if (false === $arrShead || !array_key_exists('detail_len', $arrShead)) {
                Bingo_Log::warning('unpack shead error, server:' . $this->_strServerName,
                    $this->_strLogModule);
                return false;
            }
            $intLen = intval($arrShead['detail_len']);
            if ($intLen == 0) {
                return true;
            }
            $strData = bd_socket_read ($this->_resConn, $intLen, $this->_intReadTimeout);
            if (false == $strData) {
                Bingo_Log::warning('bd_socket_read read body error!server:' . $this->_strServerName,
                    $this->_strLogModule);
                return false;
            }
            return $strData;
        }
        Bingo_Log::warning('receive error! head unsport '. $this->_strHead ,
            $this->_strLogModule);
        return false;
    }
    
}