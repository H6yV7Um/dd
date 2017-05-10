<?php

class Base_Model {

    /**
     * @var array
     */
    private static $_instances = [];

    /**
     * @var Global_Model_Base
     */
    public $dbInstance = null;

    /**
     * 字段映射
     * @var array
     */
    protected $_map = [];


    public function __construct($dbName = 'dd') {
        if (empty($dbName)) {
            $this->dbInstance = Global_Model_Base::getInstance();
        } else {
            $this->dbInstance = Global_Model_Base::getInstance($dbName);
        }
    }

    /**
     * @return Base_Model
     */
    public static function getInstance() {
        $className = self::getClassName();
        if (!(self::$_instances[$className] instanceof $className)) {
            self::$_instances[$className] = new $className();
        }

        return self::$_instances[$className];
    }

    /**
     * @return bool
     */
    public static function removeInstance() {
        $className = self::getClassName();
        if (array_key_exists($className, self::$_instances)) {
            unset(self::$_instances[$className]);
        }

        return true;
    }


    /**
     * 获取被实例化的类名
     * @return string
     */
    final private static function getClassName() {
        return get_called_class();
    }

    /**
     * 禁止对象克隆
     */
    final private function __clone() {
    }

    /**
     * 转换结果集索引字段(一维数组)(默认将数据库字段转换为业务字段)
     *
     * @param      $items
     * @param bool $isReverse
     *
     * @return array
     */
    public function transform($items, $isReverse = false) {
        if (!is_array($items)) {
            return [];
        }

        if (!is_array($this->_map) || !$this->_map) {
            return $items;
        }

        foreach ($items as $key => $value) {
            $newKey = $isReverse ? $this->_map[$key] : array_search($key, $this->_map);
            if ($newKey && $newKey != $key) {
                $items[$newKey] = $value;
                unset($items[$key]);
            }
        }

        return $items;
    }

    /**
     * 转换结果集索引字段(二维数组)(默认将数据库字段转换为业务字段)
     *
     * @param $collection
     * @param $isReverse
     *
     * @return array
     */
    public function transformCollection($collection, $isReverse = false) {
        $newCollection = [];
        foreach ($collection as $items) {
            $newCollection[] = $this->transform($items, $isReverse);
        }

        return $newCollection;
    }

    /**
     * 将业务字段转换为数据库字段
     *
     * @param $fields
     *
     * @return array
     */
    public function transformFields($fields) {
        if (!$fields) {
            return [];
        }

        if (!is_array($this->_map) || !$this->_map) {
            return $fields;
        }

        $newFields = [];
        foreach ($fields as $field) {
            if (isset($this->_map[$field]) && $this->_map[$field]) {
                $field = $this->_map[$field];
            }
            $newFields[] = $field;
        }

        return $newFields;
    }

    /**
     * @return mixed
     */
    public function startTransaction() {
        return $this->dbInstance->startTransaction();
    }

    /**
     * @return mixed
     */
    public function commit() {
        return $this->dbInstance->commit();
    }

    /**
     * @return mixed
     */
    public function rollBack() {
        return $this->dbInstance->rollback();
    }


    /**
     * @param $table
     * @param $row
     *
     * @return bool
     */
    public function insert($table, $row) {
        $affectRows = $this->dbInstance->insert($table, $row);
        if (!$affectRows) {
            Bingo_Log::warning("Saved error, Sql: " . $this->dbInstance->getLastSQL() . "  . Error message: " . $this->dbInstance->error(), 'dal');
            return false;
        }
        return true;
    }

    /**
     * @param       $table
     * @param array $fields
     * @param null  $cond
     * @param null  $appendSql
     * @param bool  $bolSingleResult
     *
     * @return array|bool
     */
    public function select($table, $fields = array('*'), $cond = null, $appendSql = null, $bolSingleResult = false) {
        $result = $this->dbInstance->select($table, $fields, $cond, null, $appendSql);
        if ($result === false) {
            Bingo_Log::warning("Select error, Sql: " . $this->dbInstance->getLastSQL() . "  . Error message: " . $this->dbInstance->error(), 'dal');
            return false;
        }
        if ($bolSingleResult) {
            return $result[0];
        }
        return $result;
    }

    /**
     * @param       $table
     * @param array $fields
     * @param null  $cond
     * @param null  $append
     *
     * @return array|Bd_DB_Result|bool
     */
    public function selectOne($table, $fields = array('*'), $cond = null, $append = null) {
        return $this->select($table, $fields, $cond, $append, true);
    }

    /**
     * @param $sql
     *
     * @return mixed
     */
    public function queryBySql($sql) {
        $result = $this->dbInstance->query($sql);
        if ($this->dbInstance->errno()) {
            Bingo_Log::warning("queryBySql Error, sql:" . $this->dbInstance->getLastSQL() . " error:" . $this->dbInstance->error(), 'dal');
            return false;
        }
        return $result;
    }

    /**
     * @param      $table
     * @param      $data
     * @param null $condition
     *
     * @return bool
     */
    public function update($table, $data, $condition = null) {
        $result = $this->dbInstance->update($table, $data, $condition);
        if ($result === false) {
            Bingo_Log::warning("update error, Sql: " . $this->dbInstance->getLastSQL() . "  . Error message: " . $this->dbInstance->error(), 'dal');
            return false;
        }
        return $result;
    }

    /**
     * @param $table
     * @param $condition
     *
     * @return bool
     */
    public function delete($table, $condition) {
        $result = $this->dbInstance->delete($table, $condition);
        if ($result === false) {
            Bingo_Log::warning("delete error, Sql: " . $this->dbInstance->getLastSQL() . "  . Error message: " . $this->dbInstance->error(), 'dal');
            return false;
        }
        return $result;
    }

    /**
     *
     */
    public function close() {
        $this->dbInstance->close();
    }

    /**
     * @return mixed
     */
    public function getLastInsertId() {
        return $this->dbInstance->getInsertID();
    }

    /**
     * @param $tables
     * @param null $cond
     * @param null $options
     * @param null $appends
     * @return bool|int
     */
    public function selectCount($tables, $cond = null, $options = null, $appends = null) {
        $result = $this->dbInstance->selectCount($tables, $cond, $options, $appends);
        if( $result === false ) {
            Bingo_Log::warning("Select count error, Sql: " . $this->dbInstance->getLastSQL() . "  . Error message: " . $this->dbInstance->error(), 'dal');
            return false;
        }
        return $result;
    }

}