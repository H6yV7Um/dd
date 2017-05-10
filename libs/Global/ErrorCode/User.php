<?php

/**
 * 用户相关错误码: '001xxx'
 * Class Global_ErrorCode_User
 * author: chusonghui@baidu.com
 * time: 16/8/19 下午4:52
 */
class Global_ErrorCode_User {
    // 注册参数错误
    const USER_REGISTER_PARAMS_ERROR                = '001001';
    // 用户已注册
    const USER_REGISTER_ALREADY_REGISTER            = '001002';
    // 注册方式不存在
    const USER_REGISTER_REG_TYPE_NOT_EXISTS         = '001003';
    // 注册失败
    const USER_REGISTER_REG_FAILED                  = '001004';
    // 登录失败
    const USER_LOGIN_LOGIN_FAILED                   = '001005';
    // 第三方登录认证pass认证失败
    const USER_LOGIN_THIRD_OAUTH_FAILED             = '001006';
    // 登出失败
    const USER_LOGOUT_LOGOUT_FAILED                 = '001007';
    // 解析bduss失败
    const USER_DECODE_BDUSS_FAILED                  = '001008';

    // 更新用户信息失败
    const USER_SET_USER_INFO_FAILED                 = '001009';
    // 用户不存在
    const USER_NOT_EXISTS                           = '001010';


    // 用户帐户不存在
    const USER_ACCOUNT_NOT_EXISTS                   = '001011';
    // 创建用户帐户失败
    const USER_ACCOUNT_CREATE_USER_ACCOUNT_FAILED   = '001012';
    // 计算reduce coin失败
    const USER_ACCOUNT_CALC_REDUCE_COIN_FAILED      = '001013';
    // 余额不足
    const USER_ACCOUNT_IS_NOT_ENOUGH                = '001014';
    // 充值失败
    const USER_ACCOUNT_RECHARGE_FAILED              = '001015';
    // 转账失败
    const USER_ACCOUNT_TRANSFER_FAILED              = '001016';
    // 更新用户等级失败
    const USER_ACCOUNT_UPDATE_USER_LEVEL_FAILED     = '001017';
    // 更新主播等级失败
    const USER_ACCOUNT_UPDATE_ANCHOR_LEVEL_FAILED   = '001018';
    // 扣除余额失败
    const USER_ACCOUNT_DECR_ACCOUNT_FAILED          = '001019';
    // 更新用户帐户失败
    const USER_ACCOUNT_UPDATE_ACCOUNT_FAILED        = '001020';

    //  关注失败
    const USER_RELATION_FOLLOW_USER_FAILED          = '001021';
    // 还没有关注
    const USER_RELATION_NOT_FOLLOW                  = '001022';
    // 已经关注
    const USER_RELATION_ALREADY_FOLLOW              = '001023';
    // 更新关注关系失败
    const USER_RELATION_UPDATE_RELATION_FAILED      = '001024';
    // 删除关注失败
    const USER_RELATION_DELETE_RELATION_FAILED      = '001025';
    // 从pass获取用户信息失败
    const USER_GET_USER_INFO_FROM_PASS_FAILED       = '001026';

    // 更新用户位置信息失败
    const USER_SET_LOCATION_FAILED                  = '001027';
}