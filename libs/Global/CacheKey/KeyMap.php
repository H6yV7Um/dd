<?php

class Global_CacheKey_KeyMap {
    // 发送短信的flag: prefix_$phone_$type
    const MESSAGE_PHONE_MESSAGE_FLAG = "message_phone_flag_%s_%s";
    // 验证码: prefix_$phoneNum_$type
    const MESSAGE_PHONE_CODE = "message_phone_code_%s_%s";
}
