<?php
/*
 * const LOG_NONE = 0x00;
 * const LOG_FATAL = 0x01;
 * const LOG_WARNING = 0x02;
 * const LOG_NOTICE = 0x04;
 * const LOG_TRACE = 0x08;
 * const LOG_DEBUG = 0x10;
 * const LOG_ALL = 0xFF;
 */
return array(
    'ui' => array(
        'file' => LOG_PATH . 'ui.log',
        'level' => 255,
    ),
    'dal' => array(
        'file' => LOG_PATH . 'dal.log',
        'level' => 255,
    ),
    'img_dal' => array(
        'file' => LOG_PATH . 'img_dal.log',
        'level' => 255,
    ),
    'api_cost' => array(
        'file' => LOG_PATH . 'api_cost.log',
        'level' => 255,
    ),
    'sql_error' => array(
        'file' => LOG_PATH . 'sql_error.log',
        'level' => 255,
    ),
);

/* vim: set ft=php expandtab ts=4 sw=4 sts=4 tw=0: */
