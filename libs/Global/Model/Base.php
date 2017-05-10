<?php
/***************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/


/**
 * @file   Base.php
 * @author liuzhenhua03(com@baidu.com)
 * @date   2014/05/08 10:01:58
 * @brief
 *
 **/
class Global_Model_Base extends Bd_DB {
    protected static $_dbConf;

    protected static $_instanceList;

    /**
     * 获取数据库实例
     * @param
     * @return
     */
    public static function getInstance($insName = 'HiClubApp') {
        if (!isset(self::$_instanceList[$insName]) || !self::$_instanceList[$insName]) {
            self::$_instanceList[$insName] = new Global_Model_Base($insName);
        }
        return self::$_instanceList[$insName];
    }

    /**
     * @param
     * @return
     */
    public function __construct($insName) {
        mysqli_report(MYSQLI_REPORT_STRICT);
        self::$_dbConf = include_once(CONFIG_PATH . "DB.Conf.php");
        parent::__construct();
        if (!isset(self::$_dbConf[$insName])) {
            Bingo_Log::warning("no db config of $insName ", 'dal');
            $result = array(
                "message" => array(
                    "code"        => 400,
                    "messageInfo" => "no db config of $insName ",
                ),
            );
            echo json_encode($result);
            exit;
        }

        $conf = self::$_dbConf[$insName];
        try {
            $this->connect($conf['host'], $conf['username'], $conf['password'],
                $conf['dbname'], $conf['port']);
        } catch (Exception $e) {
            Bingo_Log::warning($e->getMessage(), 'dal');
            $result = array(
                "message" => array(
                    "code"        => 400,
                    "messageInfo" => "db connect fail " . json_encode($conf),
                ),
            );
            echo json_encode($result);
            exit;
        }

        $this->charset($conf['charset']);
    }

}
