<?php

/**
 * 用户相关错误码: '001xxx'
 * Class Global_ErrorCode_User
 * author: chusonghui@baidu.com
 * time: 16/8/19 下午4:52
 */
class Global_ErrorCode_User {
    // 注册参数错误
    const USER_PARAMS_ERROR                = '001001';
    // 用户已注册
    const USER_ALREADY_REGISTER            = '001002';
    // 注册失败
    const USER_REG_FAILED                  = '001003';
    // 登录失败
    const USER_LOGIN_FAILED                   = '001004';
    // 登出失败
    const USER_LOGOUT_FAILED                 = '001005';
    // 更新用户信息失败
    const USER_SET_USER_INFO_FAILED                 = '001006';
    // 用户不存在
    const USER_NOT_EXISTS                           = '001007';
}