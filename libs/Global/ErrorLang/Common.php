<?php

/**
 * Created by PhpStorm.
 * User: gedejin(com@baidu.com)
 * Date: 15/8/25
 * Time: 上午11:57
 */
class Global_ErrorLang_Common {

    /**
     * @param        $code
     * @param string $default_msg
     *
     * @return string
     */
    public static function getMessage($code, $default_msg = 'this error has not msg') {
        $LangClass = "Global_ErrorLang_Conf";
        if (class_exists($LangClass)) {
            $langObj = new $LangClass();
            $message = $langObj::$message;
            if (!empty($message[$code])) {
                return $message[$code];
            }
        }
        return $default_msg;
    }
}