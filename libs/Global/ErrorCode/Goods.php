<?php

/**
 * 礼物相关错误码: '003xxx'
 * Class Global_ErrorCode_Goods
 * author: chusonghui@baidu.com
 * time: 2016-08-19 16:38:46
 */
class Global_ErrorCode_Goods {
    // 礼物不存在
    const GOODS_NOT_EXISTS = '003001';
    // 不能给自己送礼
    const CAN_NOT_SEND_GIFT_BY_YOURSELF = '003002';
    // 创建送礼记录失败
    const GOODS_CREATE_GOODS_RECORD_FAILED = '003003';
    // 创建消费记录失败
    const GOODS_CREATE_CONSUME_RECORD_FAILED = '003004';
    // 创建收入记录失败
    const GOODS_CREATE_REVENUE_RECORD_FAILED = '003005';
    // 不能给自己送心
    const CAN_NOT_SEND_LOVE_TO_SELF = '003006';
}