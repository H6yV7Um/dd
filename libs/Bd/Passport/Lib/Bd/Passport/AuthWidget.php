<?php

/**
 * 验证控件
 * 
 * @version 2013-5-27 21:05:55
 * @author xinmeng@baidu.com
 * @copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 */
class Bd_Passport_AuthWidget {
    /**
     * 默认验证名称
     */

    const NAME_DEFAULT = 'def';

    /**
     * SAPI
     * @var Bd_Passport_SAPI
     */
    private $sapi;

    /**
     * api 地址
     * 
     * @var string
     */
    private $url;

    /**
     * 已验证信息
     * @var array
     */
    private $authed;

    /**
     * 需要验证的会化名
     * 
     * @var array
     */
    private $names;

    /**
     * 是否使用高安全会话（随机名称）
     * 
     * @var boolean
     */
    private $isSecure;

    /**
     * 验证描述
     * 
     * @var string
     */
    private $brief;

    /**
     * 会话编号（动态name）
     * 
     * @var string
     */
    private $authsid;

    /**
     * 可验证字段 邮箱
     */

    const FIELD_EMAIL = 'email';
    /**
     * 可验证字段 手机 
     */
    const FIELD_MOBILE = 'mobile';
    /**
     * 可验证字段 动态密码 
     */
    const FIELD_OTP = 'otp';
    /**
     * 可验证字段 二维码
     */
    const FIELD_QRCODE = 'qrcode';
    /**
     * 可验证字段 一键验证
     */
    const FIELD_ONEKEY = 'onekey';
    /**
     * 可验证字段 密码
     */
    const FIELD_PASSWORD = 'password';
    /**
     * 验证可选性 任何
     */
    const SELECT_ANY = 'any';
    /**
     * 验证可选性 最高级
     */
    const SELECT_TOP = 'top';

    /**
     * 
     * 
     * 配置：
     * passport.conf
     * [sapi]
     * $tpl_$appid : $pass
     * [authwidget]
     * sapi_url : https://passport.baidu.com/v2/sapi/authwidget
     * 
     * @param int $appid SAPI appid
     * @param string $tpl 产品线标示
     */
    function __construct($appid = 1, $tpl = null) {
        $this->sapi = new Bd_Passport_SAPI($appid, $tpl);
        $config = Bd_Passport_Conf::getConf('authwidget');
        $this->url = $config['sapi_url'];
    }

    /**
     * 使用高安全级别的会话过程
     * 验证成功将返回authsid，验证查询将需要authsid。
     * 
     * @param boolean $isSecure 是否启用安全会话过程
     * @return Bd_Passport_AuthWidget
     */
    public function setIsSecure($isSecure) {
        $this->isSecure = $isSecure;
        return $this;
    }

    /**
     * 设置认证描述
     * 
     * @param string $brief
     */
    public function setBrief($brief) {
        $this->brief = $brief;
        return $this;
    }

    /**
     * 获取会话token（获取控件时使用）
     * @example 
     *          $widget = new Bd_Passport_AuthWidget(1, 'pp');
     *          $widget->getToken($userid, $baiduid, 'auth_mobile', $ip );
     * 
     * @param int $userid 用户编号
     * @param string $baiduid 客户端唯一识别码。浏览器取Cookies内baiduid，客户端应用自行生成，保证验证会话过程中不改变即可。
     * @param string $name 验证名称。同一验证名对应多个验证方式和可选性时会造成验证错误。
     *                      如同一产品线有多个功能需要进行身份验证，请使用不同的名称。
     *                      不同名称反作弊计数相互隔离。
     * @param string $ip 客户IP（IPv4）。客户端使用时，此为可选参数。服务端使用时请传递准确客户端连接IP。
     * @param array  $fields 验证方式，可选参数建议留空。为空时返回所有可用验证方式。可选填多个。
     * @param string $select 可选择性 
     *                        top 选择最高级别验证方式 Bd_Passport_AuthWidget::SELECT_TOP
     *                        any 可任意选择验证方式   Bd_Passport_AuthWidget::SELECT_ANY 
     * @return string 
     */
    public function getToken($userid, $baiduid, $name, $ip, $fields = null, $select = null) {

        $info = json_encode(array(
            $userid,
            $baiduid,
            $name ? $name : self::NAME_DEFAULT,
            $fields,
            $select,
            $ip,
            $this->isSecure ? 1 : 0,
            $this->brief
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
     * 安全会话验证成功后返回的authsid
     * 
     * @param string $authsid
     * @return Bd_Passport_AuthWidget
     */
    public function setAuthSID($authsid) {
        $this->authsid = $authsid;
        $this->isSecure = true;
        return $this;
    }

    /**
     * 设定查询和验证的验证名称（如不设定则使用默认查询名称）
     * query 前使用
     * isAuthed 前使用，验证名称可为query子集
     * 
     * @param string $name 验证名称。同一验证名对应多个验证方式和可选性时会造成验证错误。
     *                      如同一产品线有多个功能需要进行身份验证，请使用不同的名称。
     *                      不同名称反作弊计数相互隔离。
     * @param string $_  验证名称 可多个
     * @param string $__
     * @return Bd_Passport_AuthWidget
     */
    public function setNames($name, $_ = null, $__ = null) {
        $this->names = func_get_args();
        return $this;
    }

    /**
     * 查询已验证的信息
     * @example 
     *          $widget = new Bd_Passport_AuthWidget(1, 'pp');
     *          $authed = $widget->setNames('test')->query('104113153', 'test123', '127.0.0.1', true);
     * 
     * @param int $userid 用户编号
     * @param string $baiduid 客户端唯一识别码。浏览器取Cookies内baiduid，客户端应用自行生成，保证验证会话过程中不改变即可。
     * @param string $ip 客户IP（IPv4）。客户端使用时，此为可选参数。服务端使用时请传递准确客户端连接IP。
     * @param boolean $isdel 查询后是否删除数据
     * @return array
     *              errno 错误码
     *              errmsg 错误信息
     *              fields 可用验证字段。 错误码为 0、1 均包含.
     *              authed 已验证字段与验证时间，错误码为 0 时输出。
     */
    public function query($userid, $baiduid, $ip, $isdel = false) {
        if (!$this->names) {
            $this->setNames(self::NAME_DEFAULT);
        }
        do {
            $names = $this->names;
            if ($this->isSecure) {

                if (empty($this->authsid)) {
                    break;
                }

                foreach ($names as $key => $value) {
                    $names[$key] = $value . $this->authsid;
                }
                $this->names = $names;
            }
            $this->authed = $this->sapi->getJSON($this->url, array(
                'action'  => 'query',
                'isdel'   => $isdel,
                'userid'  => $userid,
                'baiduid' => $baiduid,
                'names'   => $names,
                'time'    => time(),
                'ip'      => $ip,
            ));
        } while (false);

        return $this->authed;
    }

    /**
     * 需要验证的字段
     * @var array 
     */
    private $fields;

    /**
     * 验证选择性
     * @var string
     */
    private $select;

    /**
     * 数据超时时间
     * @var int
     */
    private $expire;

    /**
     * 设定验证字段
     * isAuthed 前使用
     * 
     * @param array  $fields 验证方式，可选参数建议留空。为空时返回所有可用验证方式。可选填多个，均不存在时返回可用验证方式。
     * @param string $select 可选择性 
     *                        top 选择最高级别验证方式 Bd_Passport_AuthWidget::SELECT_TOP
     *                        any 可任意选择验证方式   Bd_Passport_AuthWidget::SELECT_ANY 
     * @return Bd_Passport_AuthWidget
     */
    public function setFields($fields = null, $select = null) {
        $this->fields = $fields;
        $this->select = $select;
        return $this;
    }

    /**
     * 设定验证超时
     * isAuthed 前使用
     * 
     * @param int $expire 超时时间，不可超过 24 小时。
     * @return Bd_Passport_AuthWidget
     */
    public function setExpire($expire = 0) {
        $this->expire = $expire;
        return $this;
    }

    /**
     * 判断用户是否已验证
     * 
     * 使用前需要执行 query
     * 
     * @example 
     *          $widget = new Bd_Passport_AuthWidget(1, 'pp');
     *          $authed = $widget->setNames('test')->query('104113153', 'test123', '127.0.0.1');
     *          $isAuthed = $widget->isAuthed();
     * 
     * @param boolean $allowNotReal 是否允许非真实用户（未绑定邮箱，手机等）

     */
    public function isAuthed($allowNotReal = false) {
        $pass = false;

        do {
            $names = $this->names;
            $fields = $this->fields;
            $select = $this->select;
            $expire = $this->expire;
            if (!$this->authed) {
                break;
            }
            /**
             * 检查可校验的字段
             */
            $binds = $this->authed['fields'];

            if (!$binds) {
                $pass = $allowNotReal;
                break;
            }

            if ($this->authed['errno'] !== 0) {
                break;
            }

            if (!$fields) {
                $fields = array(
                    self::FIELD_EMAIL,
                    self::FIELD_MOBILE,
                    self::FIELD_OTP,
                    self::FIELD_QRCODE,
                    self::FIELD_ONEKEY
                );
            }

            if (!$select) {
                $select = self::SELECT_ANY;
            }
            if (!$expire || $expire > 24 * 3600) {
                $expire = 24 * 3600;
            }


            foreach ($names as $name) {
                if (!isset($this->authed['authed'][$name])) {
                    continue;
                }

                $authed = $this->authed['authed'][$name];
                if ($select == self::SELECT_TOP) {
                    do {
                        /**
                         * 必须使用手机或APP验证
                         */
                        $toped = false;
                        if (in_array(self::FIELD_MOBILE, $fields) !== false && in_array(self::FIELD_MOBILE, $binds) !== false) {
                            $toped = true;
                            if (isset($authed[self::FIELD_MOBILE]) && time() - $authed[self::FIELD_MOBILE] < $expire) {
                                $pass = true;
                                break 2;
                            }
                        }

                        if (in_array(self::FIELD_OTP, $fields) !== false && in_array(self::FIELD_OTP, $binds) !== false) {
                            $toped = true;
                            if (isset($authed[self::FIELD_OTP]) && time() - $authed[self::FIELD_OTP] < $expire) {
                                $pass = true;
                                break 2;
                            }
                        }
                        if (in_array(self::FIELD_QRCODE, $fields) !== false && in_array(self::FIELD_QRCODE, $binds) !== false) {
                            $toped = true;
                            if (isset($authed[self::FIELD_QRCODE]) && time() - $authed[self::FIELD_QRCODE] < $expire) {
                                $pass = true;
                                break 2;
                            }
                        }

                        if (in_array(self::FIELD_ONEKEY, $fields) !== false && in_array(self::FIELD_ONEKEY, $binds) !== false) {
                            $toped = true;
                            if (isset($authed[self::FIELD_ONEKEY]) && time() - $authed[self::FIELD_ONEKEY] < $expire) {
                                $pass = true;
                                break 2;
                            }
                        }

                        if ($toped) {
                            break;
                        }

                        /**
                         * 必须使用邮箱验证
                         */
                        if (in_array(self::FIELD_EMAIL, $fields) !== false && in_array(self::FIELD_EMAIL, $binds) !== false) {
                            $toped = true;
                            if (!isset($authed[self::FIELD_EMAIL])) {
                                break;
                            }
                            if (time() - $authed[self::FIELD_EMAIL] > $expire) {
                                break;
                            }
                            $pass = true;
                            break 2;
                        }

                        if ($toped) {
                            break;
                        }

                        if (in_array(self::FIELD_PASSWORD, $fields) !== false && in_array(self::FIELD_PASSWORD, $binds) !== false) {
                            $toped = true;
                            if (isset($authed[self::FIELD_PASSWORD]) && time() - $authed[self::FIELD_PASSWORD] < $expire) {
                                $pass = true;
                                break 2;
                            }
                        }
                    } while (false);
                } else {
                    /* $select == self::SELECT_ANY */
                    do {
                        /**
                         * 使用手机验证
                         */
                        if (in_array(self::FIELD_MOBILE, $fields) !== false && in_array(self::FIELD_MOBILE, $binds) !== false) {

                            if (isset($authed[self::FIELD_MOBILE])  // 
                                    && time() - $authed[self::FIELD_MOBILE] < $expire
                            ) {
                                $pass = true;
                                break 2;
                            }
                        }

                        /**
                         * 使用动态口令验证
                         */
                        if (in_array(self::FIELD_OTP, $fields) !== false && in_array(self::FIELD_OTP, $binds) !== false) {

                            if (isset($authed[self::FIELD_OTP])  // 
                                    && time() - $authed[self::FIELD_OTP] < $expire
                            ) {
                                $pass = true;
                                break 2;
                            }
                        }
                        /**
                         * 使用二维码验证
                         */
                        if (in_array(self::FIELD_QRCODE, $fields) !== false && in_array(self::FIELD_QRCODE, $binds) !== false) {

                            if (isset($authed[self::FIELD_QRCODE])  // 
                                    && time() - $authed[self::FIELD_QRCODE] < $expire
                            ) {
                                $pass = true;
                                break 2;
                            }
                        }
                        /**
                         * 使用一键验证
                         */
                        if (in_array(self::FIELD_ONEKEY, $fields) !== false && in_array(self::FIELD_ONEKEY, $binds) !== false) {

                            if (isset($authed[self::FIELD_ONEKEY])  // 
                                    && time() - $authed[self::FIELD_ONEKEY] < $expire
                            ) {
                                $pass = true;
                                break 2;
                            }
                        }

                        /**
                         * 使用邮箱验证
                         */
                        if (in_array(self::FIELD_EMAIL, $fields) !== false && in_array(self::FIELD_EMAIL, $binds) !== false) {

                            if (isset($authed[self::FIELD_EMAIL])  // 
                                    && time() - $authed[self::FIELD_EMAIL] < $expire
                            ) {
                                $pass = true;
                                break 2;
                            }
                        }

                        /**
                         * 使用密码验证
                         */
                        if (in_array(self::FIELD_PASSWORD, $fields) !== false && in_array(self::FIELD_PASSWORD, $binds) !== false) {

                            if (isset($authed[self::FIELD_PASSWORD])  // 
                                    && time() - $authed[self::FIELD_PASSWORD] < $expire
                            ) {
                                $pass = true;
                                break 2;
                            }
                        }
                    } while (false);
                }
            }
        } while (false);

        return $pass;
    }

}

