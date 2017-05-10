<?php

/**
 * SAPI SDK
 * 
 * [sapi]
 * $tpl_$appid : $pass
 * 
 * 申请 appid 和 密钥（key） 请联系 hi : pass_help
 * 详细：Pass之窗 http://passport.sys.baidu.com/
 * 
 * @version 2013-1-30 11:38:01
 * @author xinmeng@baidu.com
 * @copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 */
class Bd_Passport_SAPI {

    /**
     * sapi appid
     * @var int
     */
    private $appid;

    /**
     * sapi tpl
     * 
     * @var string
     */
    private $tpl;

    /**
     * 密钥
     * 
     * @var string
     */
    private $key;

    /**
     * 配置
     * [sapi]
     * $tpl_$appid : $pass
     */
    function __construct($appid = 1, $tpl = null) {
        if (!$tpl) {
            $tpl = Bd_Passport_Conf::getConf('tpl');
        }
        $this->tpl = $tpl;
        $this->appid = $appid;
        $config = Bd_Passport_Conf::getConf('sapi');
        $this->key = $config[sprintf('%s_%s', $tpl, $appid)];
    }

    public function getTpl() {
        return $this->tpl;
    }

    public function getAppid() {
        return $this->appid;
    }

    /**
     * SAPI 密钥
     * 
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * 签名校验
     * 
     * @param string $key APP密钥
     * @param string $sign 签名
     * @param array $params 参数
     * @return boolean 签名是否有效
     */
    public function checkSign(array $params = null, $sign = null) {

        return $sign == $this->getSign($params) || $sign == $this->getRawSign($params);
    }

    /**
     * 获取签名
     * 
     * @param string $key APP密钥 
     * @param array $params 参数。 sig 参数会被剔除。
     * @return string 签名
     */
    public function getSign(array $params) {

        unset($params['sig']);
        ksort($params);
        $params = http_build_query($params);

        $sign = sprintf('%s&sign_key=%s', $params, $this->key);
        return md5($sign);
    }

    /**
     * 获取未加工的签名
     * 
     * @deprecated 不建议使用
     * @param string $key APP密钥
     * @param array $params 参数
     * @return string 签名
     */
    public function getRawSign(array $params) {
        $list = array();
        unset($params['sig']);
        ksort($params);
        foreach ($params as $key => $value) {
            $list[] = "$key=$value";
        }

        $list[] = "sign_key={$this->key}";
        return md5(join('&', $list));
    }

    /**
     * SAPI 请求
     * 
     * @param string $url 接口链接
     * @param array $params  参数 存在时会自动加上sig参数
     * @return string
     */
    public function send($url, $params = null) {
        if (is_array($params)) {
            $params['appid'] = $this->appid;
            $params['tpl'] = $this->tpl;
            $params['sig'] = $this->getSign($params);
        }


        $conn = curl_init();
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($conn, CURLOPT_URL, $url);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_POST, 1);
        curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($params));

        return curl_exec($conn);
    }

    /**
     * 大量数据传输SAPI
     * 签名所需参数放在url中，推送内容放在 content 内。
     * 
     * @param string $url 接口链接
     * @param array $params  参数 存在时会自动加上sig参数
     * @param string/array 内容
     * @return string
     */
    public function sendContent($url, $params = null, $content = null) {

        if (is_array($params)) {
            $params['appid'] = $this->appid;
            $params['tpl'] = $this->tpl;
            $params['sig'] = $this->getSign($params);
            $url = sprintf('%s?%s', $url, http_build_query($params));
        }

        $conn = curl_init();
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($conn, CURLOPT_URL, $url);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_POST, 1);
        curl_setopt($conn, CURLOPT_POSTFIELDS, is_array($content) ? http_build_query($content) : $content);

        return curl_exec($conn);
    }

    /**
     * SAPI 请求 使用 JSON 反解序列
     * 
     * @param string $url 接口链接
     * @param array $params  参数 存在时会自动加上sig参数
     * @return mixed
     */
    public function getJSON($url, $params = null) {
        return json_decode($this->send($url, $params), true);
    }

    /**
     * 大量数据传输SAPI 使用 JSON 反解序列
     * 签名所需参数放在url中，推送内容放在 content 内。
     * 
     * @param string $url 接口链接
     * @param array $params  参数 存在时会自动加上sig参数
     * @param string/array 内容
     * @return mixed
     */
    public function getJSONByContent($url, $params = null, $content = null) {
        return json_decode($this->sendContent($url, $params, $content), true);
    }

}

