<?php

/**
 * 支付相关错误码 xx8xxx
 * Class Global_ErrorCode_Pay
 * @author: chusonghui@baidu.com
 * @time: 10/25/16 15:32
 */
class Global_ErrorCode_Pay {
    const REQUEST_CODE_METHOD_ERROR = '008001';
    const LACK_OF_NECESSARY_PARAMS = '008002';
    const REQUEST_TOKEN_ERROR = '008003';
    const RECHARGE_FAILED = '008004';
    const CREATE_RECHARGE_RECORD_FAILED = '008005';
    const ORDER_IS_FINISH = '008006';
    const ORDER_STATUS_ERROR = '008007';
    const ORDER_IS_ALREADY_EXISTS = '008008';
    const UPDATE_ORDER_STATUS_FAILED = '008009';
    const CREATE_BILLING_RECORD_FAILED = '008010';
    const INCR_USER_BALANCE_FAILED = '008011';
}