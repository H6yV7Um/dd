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

    // 注册参数错误
    const USER_PARAMS_ERROR                = '001001';
    // 用户已注册
    const USER_ALREADY_REGISTER            = '001002';
    // 注册失败
    const USER_REG_FAILED                  = '001003';
    // 登录失败
    const USER_LOGIN_FAILED                  = '001004';
    // 登出失败
    const USER_LOGOUT_FAILED                 = '001005';
    // 更新用户信息失败
    const USER_SET_USER_INFO_FAILED          = '001006';
    // 用户不存在
    const USER_NOT_EXISTS                    = '001007';
    // 验证码错误
    const USER_PHONE_CODE_ERROR = '001008';
    const USER_CHANGE_PWD_FAILED = '001009';
    const USER_RESET_PWD_FAILED = '001010';

    // message相关
    // 请求过于频繁, 请稍后再试
    const MESSAGE_REQUEST_FREQUENT = '002001';
    const MESSAGE_SEND_MSG_FAILED  = '002002';
    const MESSAGE_SEND_PHONE_CODE_FAILED = '002003';


}