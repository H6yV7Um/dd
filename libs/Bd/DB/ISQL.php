<?php
/**
* brief of BD_DB_ISQL.class.php:
*
* interface of SQL generator
*
* @author zhangdongjin
*/


interface BD_DB_ISQL
{
    // return SQL text or false on error
    public function getSQL();
}