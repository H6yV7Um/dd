<?php

/**
 * 通用错误码: '000xxx'
 * Class Global_ErrorCode_Common
 * author: chusonghui@baidu.com
 * time: 16/8/25 下午5:17
 */
class Global_ErrorCode_Common {
    //100~299 500
    public static $COMMON_SUCCESS = 200;
    public static $COMMON_API_NOT_EXISTS = 100;

    public static $COMMON_PARAMS_ERROR = 101;

    const COMMON_SUCCESS = 200;

    //配置文件错误
    public static $COMMON_CONFIG_ERROR = 108;
    // 异常未知错误

    const COMMON_UNEXCEPTION_ERROR = -1;

    // 未登录
    const COMMON_NEED_LOGIN = '000500';
    // 参数错误
    const COMMON_PARAMS_ERROR = '000101';
    // HTTP方法错误
    const REQUEST_METHOD_ERROR = '001002';
    // 已经举报过了
    const USER_ALREADY_REPORT = '001011';
    // 举报失败
    const USER_REPORT_FAILED  = '001012';
    // 反馈失败
    const USER_FEEDBACK_FAILED = '001013';

    // mysql error
    const MYSQL_SELECT_ERROR = '001014';


}