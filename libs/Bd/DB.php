<?php
/*
* class Bd_DB
*
* Author: zhangdongjin@baidu.com
* update: xuliqiang@baidu.com
*
**/


class Bd_DB
{
    // hook types
    const HK_BEFORE_QUERY = 0;
    const HK_AFTER_QUERY = 1;

    // query result types
    const FETCH_RAW = 0;    // return raw mysqli_result
    const FETCH_ROW = 1;    // return numeric array
    const FETCH_ASSOC = 2;  // return associate array
    const FETCH_OBJ = 3;    // return Bd_DB_Result object

    private $mysql = NULL;
    private $dbConf = NULL;
    private $isConnected = false;
    private $lastSQL = NULL;

    private $enableProfiling = false;
    private $arrCost = NULL;
    private $lastCost = 0;
    private $totalCost = 0;

    private $hkBeforeQ = array();
    private $hkAfterQ = array();
    private $onfail = NULL;

    private $sqlAssember = NULL;

    //////////////////////////// init ////////////////////////////

    public function __construct($enableProfiling = false)
    {
        $this->mysql = new mysqli();
        if($enableProfiling)
        {
            $this->enableProfiling(true);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function setOption($optName, $value)
    {
        return $this->mysql->options($optName, $value);
    }

    // use it before connect and reconnect
    public function setConnectTimeOut($seconds)
    {
        if($seconds <= 0)
        {
            return false;
        }
        return $this->setOption(MYSQLI_OPT_CONNECT_TIMEOUT, $seconds);
    }

    public function __get($name)
    {
        switch($name)
        {
            case 'error':
                return $this->mysql->error;
            case 'errno':
                return $this->mysql->errno;
            case 'insertID':
                return $this->mysql->insert_id;
            case 'affectedRows':
                return $this->mysql->affected_rows;
            case 'lastSQL':
                return $this->lastSQL;
            case 'lastCost':
                return $this->lastCost;
            case 'totalCost':
                return $this->totalCost;
            case 'isConnected':
                return $this->isConnected;
            case 'db':
                return $this->mysql;
            default:
                return NULL;
        }
    }

    //////////////////////////// connection ////////////////////////////

    public function connect($host, $uname = NULL, $passwd = NULL, 
        $dbname = NULL, $port = NULL, $strSocket = NULL, $flags = 0)
    {
        $port = intval($port);
        if(!$port)
        {
            $port = 3306;
        }

        $this->dbConf = array(
            'host' => $host,
            'port' => $port,
            'uname' => $uname,
            'passwd' => $passwd,
            'flags' => $flags,
            'dbname' => $dbname,
        );

        try {
           // $this->isConnected =new mysqli($host, $uname, $passwd, $dbname, $port, $strSocket, $flags);
            //$this->isConnected = $this->mysql->connect($host, $uname, $passwd, $dbname, $port, $strSocket, $flags);
        	$this->isConnected = $this->mysql->real_connect(
        			$host, $uname, $passwd, $dbname, $port, $strSocket, $flags
        	);
            //var_dump($this->isConnected);exit();
        } catch (mysqli_sql_exception $e) {
        	throw $e;
        }
        
        return $this->isConnected;
    }

    public function reconnect()
    {
        if($this->dbConf === NULL)
        {
            return false;
        }
        $conf = $this->dbConf;
        $this->isConnected = $this->mysql->real_connect(
            $conf['host'], $conf['uname'], $conf['passwd'],
            $conf['dbname'], $conf['port'], NULL, $conf['flags']
        );

        return $this->isConnected;
    }

    public function close()
    {
        if(!$this->isConnected)
        {
            return;
        }
        $this->isConnected = false;
        $this->mysql->close();
    }

    // note: mysqli.reconnect should be off
    public function isConnected($bolCheck = false)
    {
        if($this->isConnected && $bolCheck && !$this->mysql->ping())
        {
            $this->isConnected = false;
        }
        return $this->isConnected;
    }

    //////////////////////////// query ////////////////////////////

    public function query($sql, $fetchType = Bd_DB::FETCH_ASSOC, $bolUseResult = false)
    { 
        /*
        if(!$this->isConnected())
        {
            return false;
        }
        */

        if(!is_string($sql))
        {
            require_once('Bd/DB/ISQL.php');
            // get sql text
            if(!($sql instanceof BD_DB_ISQL) || !($sql = $sql->getSQL()))
            {
                return false;
            }
        }

        // execute hooks before query
        if (! empty($this->hkBeforeQ)) {
	        foreach($this->hkBeforeQ as $arrCallback)
	        {
	            $func = $arrCallback[0];
	            $extArgs = $arrCallback[1];
	            if(call_user_func_array($func, array($this, &$sql, $extArgs)) === false)
	            {
	                return false;
	            }
	        }
        }

        $this->lastSQL = $sql;

        $beg = intval(microtime(true)*1000000);
        //$this->mysql = new mysqli("10.94.172.73", "talebox", "MhxzKhl", "Talebox", "8667");
        $res = $this->mysql->query($sql, $bolUseResult?MYSQLI_USE_RESULT:MYSQLI_STORE_RESULT);
        // record cost
        $this->lastCost = intval(microtime(true)*1000000) - $beg;
        $this->totalCost += $this->lastCost;

        // do profiling
        if($this->enableProfiling)
        {
            $this->arrCost[] = array($sql, $this->lastCost);
        }

        $ret = false;

        // res is NULL if mysql is disconnected
        if(is_bool($res) || $res === NULL)
        {
            $ret = ($res == true);
            // call fail handler
            if(!$ret && $this->onfail !== NULL)
            {
                call_user_func_array($this->onfail, array($this, &$ret));
            }
        }
        // we have result
        else
        {
            switch($fetchType)
            {
                case Bd_DB::FETCH_OBJ:
                    require_once('Bd/DB/Result.php');
                    $ret = new Bd_DB_Result($res);
                    break;

                case Bd_DB::FETCH_ASSOC:
                    $ret = array();
                    while($row = $res->fetch_assoc())
                    {
                        $ret[] = $row;
                    }
                    $res->free();
                    break;

                case Bd_DB::FETCH_ROW:
                    $ret = array();
                    while($row = $res->fetch_row())
                    {
                        $ret[] = $row;
                    }
                    $res->free();
                    break;

                default:
                    $ret = $res;
                    break;
            }
        }

        // execute hooks after query
        if (! empty($this->hkAfterQ)) {
	        foreach($this->hkAfterQ as $arrCallback)
	        {
	            $func = $arrCallback[0];
	            $extArgs = $arrCallback[1];
	            call_user_func_array($func, array($this, &$ret, $extArgs));
	        }
        }
        return $ret;
    }

    private function __getSQLAssember()
    {
        if($this->sqlAssember == NULL)
        {
            require_once('Bd/DB/SQLAssember.php');
            $this->sqlAssember = new Bd_DB_SQLAssember($this);
        }
        return $this->sqlAssember;
    }

    public function select(
        $tables, $fields = array('*'), $conds = NULL, $options = NULL, $appends = NULL,
        $fetchType = Bd_DB::FETCH_ASSOC, $bolUseResult = false
    )
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getSelect($tables, $fields, $conds, $options, $appends);
        if(!$sql)
        {
            return false;
        }
        return $this->query($sql, $fetchType, $bolUseResult);
    }

    public function selectCount($tables, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $this->__getSQLAssember();
        $fields = 'COUNT(*)';
        $sql = $this->sqlAssember->getSelect($tables, $fields, $conds, $options, $appends);
        if(!$sql)
        {
            return false;
        }
        $res = $this->query($sql, Bd_DB::FETCH_ROW);
        if($res === false)
        {
            return false;
        }
        return intval($res[0][0]);
    }

    public function insert($table, $row, $options = NULL, $onDup = NULL)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getInsert($table, $row, $options, $onDup);
        if(!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    public function update($table, $row, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getUpdate($table, $row, $conds, $options, $appends);
        if(!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    public function delete($table, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getDelete($table, $conds, $options, $appends);
        if(!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    public function prepare($query, $getRaw = false)
    {
        $stmt = $this->mysql->prepare($query);
        if($stmt === false)
        {
            return false;
        }
        if($getRaw)
        {
            return $stmt;
        }
        else
        {
            require_once('Bd/DB/Stmt.php');
            return new Bd_DB_Stmt($stmt);
        }
    }

    public function getLastSQL()
    {
        return $this->lastSQL;
    }

    public function getInsertID()
    {
        return $this->mysql->insert_id;
    }

    public function getAffectedRows()
    {
        return $this->mysql->affected_rows;
    }
/*
    public function getLastQueryInfo()
    {
        return $this->mysql->info;
    }
*/
    //////////////////////////// hooks ////////////////////////////

    public function addHook($where, $id, $func, $extArgs = NULL)
    {
        switch($where)
        {
            case self::HK_BEFORE_QUERY:
                $dest = &$this->hkBeforeQ;
                break;
            case self::HK_AFTER_QUERY:
                $dest = &$this->hkAfterQ;
                break;
            default:
                return false;
        }
        if(!is_callable($func))
        {
            return false;
        }
        $dest[$id] = array($func, $extArgs);
        return true;
    }

    public function onFail($func = 0)
    {
        if($func === 0)
        {
            return $this->onfail;
        }
        if($func === NULL)
        {
            $this->onfail = NULL;
            return true;
        }
        if(!is_callable($func))
        {
            return false;
        }
        $this->onfail = $func;
        return true;
    }

    public function removeHook($where, $id)
    {
        switch($where)
        {
            case self::HK_BEFORE_QUERY:
                $dest = &$this->hkBeforeQ;
                break;
            case self::HK_AFTER_QUERY:
                $dest = &$this->hkAfterQ;
                break;
            default:
                return false;
        }
        if(!array_key_exists($id, $dest))
        {
            return false;
        }
        unset($dest[$id]);
        return true;
    }

    //////////////////////////// profiling ////////////////////////////

    public function getLastCost()
    {
        return $this->lastCost;
    }

    public function getTotalCost()
    {
        return $this->totalCost;
    }

    public function getProfilingData()
    {
        return $this->arrCost;
    }

    public function cleanProfilingData()
    {
        $this->arrCost = NULL;
    }

    public function enableProfiling($enable = NULL)
    {
        if($enable === NULL)
        {
            return $this->enableProfiling;
        }
        $this->enableProfiling = ($enable == true);
    }

    //////////////////////////// transaction ////////////////////////////

    public function autoCommit($bolAuto = NULL)
    {
        if($bolAuto === NULL)
        {
            $sql = 'SELECT @@autocommit';
            $res = $this->query($sql);
            if($res === false)
            {
                return NULL;
            }
            return $res[0]['@@autocommit'] == '1';
        }

        return $this->mysql->autocommit($bolAuto);
    }

    public function startTransaction()
    {
        $sql = 'START TRANSACTION';
        return $this->query($sql);
    }

    public function commit()
    {
        return $this->mysql->commit();
    }

    public function rollback()
    {
        return $this->mysql->rollback();
    }

    //////////////////////////// util ////////////////////////////

    public function escapeString($string)
    {
        return $this->mysql->real_escape_string($string);
    }

    public function selectDB($dbname)
    {
        if($this->mysql->select_db($dbname))
        {
            $this->dbConf['dbname'] = $dbname;
            return true;
        }
        return false;
    }

    public function getTables($pattern = NULL, $dbname = NULL)
    {
        $sql = 'SHOW TABLES';
        if($dbname !== NULL)
        {
            $sql .= ' FROM '.$this->escapeString($dbname);
        }
        if($pattern !== NULL)
        {
            $sql .= ' LIKE \''.$this->escapeString($pattern).'\'';
        }

        if(!($res = $this->query($sql, false)))
        {
            return false;
        }

        $ret = array();
        while($row = $res->fetch_row())
        {
            $ret[] = $row[0];
        }
        $res->free();
        return $ret;
    }

    public function isTableExists($name, $dbname = NULL)
    {
        $tables = $this->getTables($name, $dbname);
        if($tables === false)
        {
            return NULL;
        }
        return count($tables) > 0;
    }

/*
    public function changeUser($uname, $passwd, $dbname = NULL)
    {
        if(!$this->isConnected())
        {
            return false;
        }

        if($this->dbConf['uname'] == $name &&
            $this->dbConf['passwd'] == $passwd)
        {
            if($dbname !== NULL)
            {
                return $this->selectDB($dbname);
            }
            return true;
        }

        $ret = $this->mysql->change_user($uname, $passwd, $dbname);
        if($ret)
        {
            $this->dbConf['uname'] = $uname;
            $this->dbConf['passwd'] = $passwd;
            $this->dbConf['dbname'] = $dbname;
        }
        return $ret;
    }
*/

    public function charset($name = NULL)
    {
        if($name === NULL)
        {
            return $this->mysql->character_set_name();
        }
        return $this->mysql->set_charset($name);
    }

    public function getConnConf()
    {
        if($this->dbConf == NULL)
        {
            return NULL;
        }

        return array(
            'host' => $this->dbConf['host'],
            'port' => $this->dbConf['port'],
            'uname' => $this->dbConf['uname'],
            'dbname' => $this->dbConf['dbname']
            );
    }

    //////////////////////////// error ////////////////////////////

    public function errno()
    {
        return $this->mysql->errno;
    }

    public function error()
    {
        return $this->mysql->error;
    }
/*
    public function ansiError()
    {
        return $this->mysql->sqlstate;
    }
*/
    //////////////////////////// env ////////////////////////////
/*
    public static function getClientVersion($bolNum = true)
    {
        if($bolNum)
        {
            return mysqli_get_client_version();
        }
        else
        {
            return mysqli_get_client_info();
        }
    }

    public function getServerVersion($bolNum = true)
    {
        if($bolNum)
        {
            return $this->mysql->server_version;
        }
        else
        {
            return $this->mysql->server_info;
        }
    }

    public function getServerStat($detail = false)
    {
        if($detail === false || $detail === NULL)
        {
            return $this->mysql->stat();
        }

        $sql = 'SHOW STATUS';
        if(!($res = $this->query($sql, false)))
        {
            return false;
        }

        // get full detail
        if($detail === true)
        {
            $ret = array();
            while($row = $res->fetch_row())
            {
                $ret[$row[0]] = $row[1];
            }
        }
        // get one detail
        else
        {
            $ret = false;
            while($row = $res->fetch_row())
            {
                if($detail == $row[0])
                {
                    $ret = $row[1];
                }
            }
        }

        $res->free();
        return $ret;
    }

    public function getThreadID()
    {
        return $this->mysql->thread_id;
    }

    public function getHostInfo()
    {
        if($this->dbConf === NULL)
        {
            return NULL;
        }

        return array(
            'host' => $this->dbConf['host'],
            'port' => $this->dbConf['port']
            );
    }

    public function getProtocol()
    {
        return $this->mysql->protocol_version;
    }
*/
}
