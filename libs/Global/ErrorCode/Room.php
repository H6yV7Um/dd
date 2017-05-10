<?php

/**
 * *************************************************************************
 *
 * Copyright (c) 2015. All Rights Reserved
 *
 * ************************************************************************
 *
 * User: muchao(http://git.oschina.net/muchao01)
 * Date: 2016-08-18
 * Time: 18:57
 *
 */

/**
 * 房间相关错误码: '002xxx'
 * Class Global_ErrorCode_Room
 */
class Global_ErrorCode_Room {
    const PERMISSION_DENIED = '002001';
    // 禁言
    const ROOM_GAGGED_FAILED   = '002002';
    // 更新房间信息失败
    const ROOM_UPDATE_ROOM_INFO_FAILED = '002003';
    // 房间不存在
    const ROOM_NOT_EXIST = '002004';
    // 创建房间失败
    const CREATE_ROOM_FAILED = '002005';
}