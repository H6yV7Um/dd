<?php

/**
 * 
 * 用户标记推送
 * 
 * @version 2013-5-2 16:34:47
 * @author xinmeng@baidu.com
 * @copyright (c) 2013-2014 Baidu.com, Inc. All Rights Reserved
 */
class Bd_Passport_UserTags {

    private $tags;

    const VERSION = 1.0;

    /**
     * SAPI
     * @var Bd_Passport_SAPI
     */
    private $sapi;
    private $url;

    /**
     * 
     * 配置：
     * passport.conf
     * [sapi]
     * $tpl_$appid : $pass
     * [usertags]
     * sapi_url : https://passport.baidu.com/v2/api/usertags
     * 
     * @param int $appid SAPI appid
     * @param string $tpl 产品线标示
     */
    function __construct($appid = 1, $tpl = null) {
        $this->sapi = new Bd_Passport_SAPI($appid, $tpl);
        $config = Bd_Passport_Conf::getConf('usertags');
        $this->url = $config['sapi_url'];
    }

    /**
     * 获取签名
     * 
     * @param array $params tpl appid 
     * @param string $json post 内容
     * @return string 签名 
     */
    private function getSign($params, $json) {
        $params['content'] = md5($json);

        ksort($params);

        $params = http_build_query($params);

        $sign = sprintf('%s&sign_key=%s', $params, $this->sapi->getKey());
        return md5($sign);
    }

    /**
     * 标记为高价值用户
     * 
     * @param array $ids 用户编号列表
     */
    public function setVip($ids) {
        $this->tags['vip'] = $ids;
    }

    /**
     * 取消用户高价值标记
     * 
     * @param array $ids 用户编号列表
     */
    public function removeVip($ids) {
        $this->tags['vip_remove'] = $ids;
    }

    /**
     * 标记为“被盗”用户
     * 
     * @param array $ids 用户编号列表
     */
    public function setStolen($ids) {
        $this->tags['stolen'] = $ids;
    }

    /**
     * 取消用户“被盗”标记
     * 
     * @param array $ids 用户编号列表
     */
    public function removeStolen($ids) {
        $this->tags['stolen_remove'] = $ids;
    }

    /**
     * 标记为手机短信提醒用户
     *      2013-5-2 标记用户登录（全开）或异地登陆（半开）时发送短信提醒
     * 
     * @param array $ids 用户编号列表
     */
    public function setSendSMS($ids) {
        $this->tags['sendsms'] = $ids;
    }

    /**
     * 取消用户”手机短信提醒“标记
     * 
     * @param array $ids 用户编号列表
     */
    public function removeSendSMS($ids) {
        $this->tags['sendsms_remove'] = $ids;
    }

    /**
     * 标记为两步验证用户
     * 
     * @param array $ids 用户编号列表
     */
    public function setAuthTwice($ids) {
        $this->tags['authtwice'] = $ids;
    }

    /**
     * 取消用户”两步验证“标记
     * 
     * @param array $ids 用户编号列表
     */
    public function removeAuthTwice($ids) {
        $this->tags['authtwice_remove'] = $ids;
    }

    /**
     * 发送请求
     * 
     * @return array
     * 
     *          array (
     *            'errno' => 140001,
     *            'errmsg' => 'success',
     *          )
     *        errno 140001  操作完全成功
     *        errno 410001  redis操作失败失败的tag放在fails内
     * 
     *        array (
     *            'errno' => 410001,
     *            'errmsg' => 'fail',
     *            'fails' => array(
     *                  'vip' => array(9865432, 23564789)
     *             ),
     *        )
     * 
     */
    public function send() {

        $content = json_encode($this->tags);

        $params = array();
        $params['tpl'] = $this->sapi->getTpl();
        $params['appid'] = $this->sapi->getAppid();
        $params['version'] = self::VERSION;

        $params['sig'] = $this->getSign($params, $content);


        $url = sprintf('%s?%s', $this->url, http_build_query($params));

        $conn = curl_init();
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($conn, CURLOPT_URL, $url);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_POST, 1);
        curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
        curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $content = curl_exec($conn);
        return json_decode($content, true);
    }

}
