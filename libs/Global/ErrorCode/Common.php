<?php

/**
 * 通用错误码: '000xxx'
 * Class Global_ErrorCode_Common
 * author: chusonghui@baidu.com
 * time: 16/8/25 下午5:17
 */
class Global_ErrorCode_Common {
    // 参数错误
    const COMMON_PARAMS_ERROR = '000101';
    // HTTP请求方法错误
    const REQUEST_METHOD_ERROR = '000102';

    // mysql error
    const MYSQL_SELECT_ERROR = '001014';

    // message相关
    // 请求过于频繁, 请稍后再试
    const MESSAGE_REQUEST_FREQUENT = '002001';
    const MESSAGE_SEND_MSG_FAILED  = '002002';
    const MESSAGE_SEND_PHONE_CODE_FAILED = '002003';


}