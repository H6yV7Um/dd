<?php

/**
 * 绑定控件
 * 
 * @version 2013-9-12
 * @author xinmeng@baidu.com
 * @copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 */
class Bd_Passport_BindWidget {

    const ACTION_BIND_MOBILE = 'bindmobile';
    const ACTION_BIND_EMAIL = 'bindemail';
    const ACTION_REBIND_MOBILE = 'rebindmobile';
    const ACTION_REBIND_EMAIL = 'rebindemail';
    const ACTION_UNBIND_MOBILE = 'unbindmobile';
    const ACTION_UNBIND_EMAIL = 'bindemail';

    /**
     * SAPI
     * @var Bd_Passport_SAPI
     */
    private $sapi;

    /**
     * 
     * 配置：
     * passport.conf
     * [sapi]
     * $tpl_$appid : $pass
     * [bindwidget]
     * sapi_url : https://passport.baidu.com/v2/sapi/bindwidget-query
     * 
     * @param int $appid SAPI appid
     * @param string $tpl 产品线标示
     */
    function __construct($appid = 1, $tpl = null) {
        $this->sapi = new Bd_Passport_SAPI($appid, $tpl);
        $config = Bd_Passport_Conf::getConf('bindwidget');
        $this->url = $config['sapi_url'];
    }

    /**
     * 获取会话token（获取控件时使用）
     * 
     * @example 
     *          $widget = new Bd_Passport_BindWidget(1, 'pp');
     *          $widget->getToken('bindemail', $userid, $baiduid, $ip );
     * 
     * @param string $action 操作
     *                        bindemail 绑定邮箱
     *                        bindmobile 绑定手机
     * 
     * @param int $userid 用户编号
     * @param string $baiduid 客户端唯一识别码。浏览器取Cookies内baiduid，客户端应用自行生成，保证验证会话过程中不改变即可。
     * @param string $name 验证名称。同一验证名对应多个验证方式和可选性时会造成验证错误。
     *                      如同一产品线有多个功能需要进行身份验证，请使用不同的名称。
     *                      不同名称反作弊计数相互隔离。
     * @param string $ip 客户IP（IPv4）。客户端使用时，此为可选参数。服务端使用时请传递准确客户端连接IP。
     * 
     * @return string 
     */
    public function getToken($action, $userid, $baiduid, $ip) {

        $info = json_encode(array(
            $action,
            $userid,
            $baiduid,
            $ip,
        ));

        $pack = json_encode(
                array(
                    $this->sapi->getTpl(),
                    $this->sapi->getAppid(),
                    Bd_Crypt_Rc4::rc4($info, 'ENCODE', $this->sapi->getKey()),
                )
        );
        return Bd_Crypt_Rc4::rc4($pack, 'ENCODE');
    }

    /**
     * 查询刚绑定的信息
     * 
     * @param int $userid 用户编号
     * @param string $baiduid BAIDUID，在COOKIE中获取，与绑定时的baiduid需要一致
     * @param string $bindid 绑定成功后前端返回的bindid
     * @param string $ip IPv4
     * @return array   绑定字段名和绑定时间
     *                  {mobile:time(), email:time()}
     */
    public function query($userid, $baiduid, $bindid, $ip) {
        $bound = null;
        $bindInfo = $this->sapi->getJSON($this->url, array(
            'userid'  => $userid,
            'baiduid' => $baiduid,
            'bindid'  => $bindid,
            'time'    => time(),
            'ip'      => $ip,
        ));
        if ($bindInfo && intval($bindInfo['errno']) === 0) {
            $bound = $bindInfo['bound'];
        }
        return $bound;
    }

}
