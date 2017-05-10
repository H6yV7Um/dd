<?php

/**
 * Created by PhpStorm.
 * User: gedejin(com@baidu.com)
 * Date: 15/9/9
 * Time: 下午5:34
 */
class Global_CacheKey_KeyMap {

    // 主播的附加信息(艺术照等)
    const ANCHOR_ADDITION_INFO_PREFIX = 'hiclub_app_anchor_addition_info_%s';

    const USER_SESSION_PREFIX = 'hiclub_app_user_session_%s';


    // 用户详情, 互通
    const USER_INFO_PREFIX = "hiclub_app_UserInfo_%d";
    // 粉丝列表
    const USER_RELATION_FANS_ZSET = "User_Relation_Fans_%d";
    // 关注列表: prefix_$userId
    const USER_RELATION_FOLLOW_ZSET = "User_Relation_Follow_%d";
    // 直播间详情, 互通
    const ROOM_INFO_PREFIX = "RoomInfo_%d";
    // 直播列表, 互通
    const MEDIA_ROOM_LIVE_SET = "live_room_set";
    // 主播上次开播记录
    const MEDIA_HOST_LAST_LIVE_HASH = 'room_publishtime_%s';
    // 消息服务观看直播用户列表
    const MSG_ROOM_LIVE_USER_HASH = 'USERLIST_%d';
    // 观看直播用户列表: %d => $roomId
    const ROOM_LIVE_USER_ZSET = 'ROOM_USER_LIST_%d';
    // 禁言: prefix_$roomId_$toUserId
    const ROOM_FORBIDDEN_PREFIX = "mute_%s_%s";
    // 被踢, 互通: prefix_$roomId_$userId
    const ROOM_KICK_PREFIX = "forbidden_%s_%s";
    // 礼物列表
    const GOODS_LIST_STRING = 'hiclub_app_goods_list';
    // 用户每日送心所得积分: prefix_$userId_$Ymd_$itemType
    const USER_SCORE_ITEM_CNT_PREFIX = 'hiclub_app_user_score_item_%s_%s_%s';
    // 用户每日观看时间所得积分: prefix_$userId_$Ymd
    const ANCHOR_SCORE_ITEM_CNT_PREFIX = 'hiclub_app_anchor_score_item_%s_%s_%s';
    // 等级map
    const USER_LEVEL_MAP_STRING = 'hiclub_app_level_map';
    // 用户最近一次的开播记录
    const USER_LAST_LIVE_RECORD_PREFIX = 'hiclub_app_user_last_live_record_%s';
    // 用户本次直播的观看人数: prefix_$userId
    const USER_VIEW_CNT_CURR_LIVE = 'hiclub_app_view_cnt_curr_live_%s';
    // 用户本次直播的新增粉丝数: prefix_$userId
    const USER_FANS_CNT_CURR_LIVE = 'hiclub_app_fans_cnt_curr_live_%s';
    // 用户本次直播的收礼金额: prefix_$userId
    const USER_GOLD_CNT_CURR_LIVE = 'hiclub_app_gold_cnt_curr_live_%s';
    // 用户本次直播的收心数: prefix_$userId
    const USER_LOVE_CNT_CURR_LIVE = 'hiclub_app_love_cnt_curr_live_%s';
    // 用户上次举报时间
    const USER_REPORT_TIME = 'hiclub_app_report_time_%s_%s';
    // 用户送礼combo次数
    const USER_SEND_GIFT_COMBO_CNT = 'hiclub_app_send_gift_combo_cnt_%s_%s';
    // 用户观看时间记录
    const USER_LAST_WATCH_TIME_PREFIX = 'USER_LAST_WATCH_%s_%s';
    // 房间管理员列表
    const ROOM_ADMIN_LIST_STRING = 'hiclub_app_room_admin_list';
    // 用户直播间首次送心标志 prefix_$fromUserId_$toUserId
    const USER_FIRST_LOVE_IN_ROOM = 'hiclub_app_user_first_love_in_room_%s_%s';
    // 用户直播间首次关注主播标志 prefix_$fromUserId_$toUserId
    const USER_FIRST_FOLLOW_IN_ROOM = 'hiclub_app_user_first_follow_in_room_%s_%s';
    // 房间的机器人Id: prefix_$roomId
    const ROOM_ROBOT_IDS_ZSET_PREFIX = 'hiclub_app_room_robot_ids_%s';
    // 机器人信息: prefix_$robotId
    const ROBOT_INFO_HSET_PREFIX = 'hiclub_app_robot_info_%s';
    // 开播源: [pc|android|ios]
    const LIVE_STREAM_SOURCE_HASH = 'live_room_hashinfo';
    // 用户定位信息: prefix_$userId
    const USER_LOCATION_STATUS_STRING = 'hiclub_app_user_location_status_%s';
}
