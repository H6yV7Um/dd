<?php

class Message_Phone_Service extends Global_Service_Base {
    /**
     * @return Message_Phone_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }
    
    protected function __construct() {
        $this->initRedis();
    }

    /**
     * 发送手机短信
     * @param $phoneNum
     * @param $msg
     * @return bool
     */
    public static function sendMessage($phoneNum, $msg) {
        // WebService
        // WebService信息返回格式
        $webType = array(
            '-01' => '当前账号余额不足！',
            '-02' => '当前用户ID错误！',
            '-03' => '当前密码错误！',
            '-04' => '参数不够或参数内容的类型错误！',
            '-05' => '手机号码格式不对！',
            '-06' => '短信内容编码不对！',
            '-07' => '短信内容含有敏感字符！',
            '-08' => '无接收数据',
            '-09' => '系统维护中..',
            '-10' => '手机号码数量超长！' ,
            '-11' => '短信内容超长！（70个字符）',
            '-12' => '其它错误！',
            '-13' => '文件传输错误'
        );
        // WSDL
        $WSDL = 'http://service2.winic.org/Service.asmx?WSDL';
        $soapClient = new \SoapClient($WSDL);
        $param = [
            'uid'   => 'chu0080',
            'pwd'   => 'chu0080',
            'tos'   => $phoneNum,
            'msg'   => $msg,
            'otime' => '',
        ];
        $msgObj     = $soapClient->SendMessages($param);
        $returnCode = $msgObj->SendMessagesResult;
        Bingo_Log::notice("send phone message. res: $returnCode");
        
        if(strlen($returnCode) == 16 || $returnCode == '000') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送手机验证码
     * @param $phoneNum
     * @return bool|string
     * @throws Exception
     */
    public function sendPhoneCode($phoneNum) {
        if(!$phoneNum) {
            throw new \Exception('params error', Global_ErrorCode_Common::COMMON_PARAMS_ERROR);
        }

        $str       = '123456789456123789456789123987654321';
        $phoneCode = substr(str_shuffle($str), 0, 6);
        $msg       = "您的验证码是: {$phoneCode} 验证码30分钟内有效, 请不要把验证码泄露给其他人.";
        if(!self::sendMessage($phoneNum, $msg)) {
            throw new \Exception('"send message failed"', Global_ErrorCode_Common::MESSAGE_SEND_MSG_FAILED);
        }

        // 记录发送flag
        $msgType = "code";
        $key     = sprintf(Global_CacheKey_KeyMap::MESSAGE_PHONE_MESSAGE_FLAG, $phoneNum, $msgType);
        $ttl     = 60;
        $this->redis->setex($key, $ttl, 1);

        // 记录本次发送的验证码
        $key = sprintf(Global_CacheKey_KeyMap::MESSAGE_PHONE_CODE, $phoneNum, $msgType);
        $ttl = 1800;
        $this->redis->setex($key, $ttl, $phoneCode);

        return true;
    }

    /**
     * 是否频繁发送
     * @param $phoneNum
     */
    public function isFrequent($phoneNum, $msgType = 'code') {
        $key = sprintf(Global_CacheKey_KeyMap::MESSAGE_PHONE_MESSAGE_FLAG, $phoneNum, $msgType);
        if($this->redis->get($key)) {
            return true;
        }

        return false;
    }
}