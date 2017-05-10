<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mb_truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mb_truncate<br>
 * Purpose:  truncate's 'mb' version.
 *
 * @author   李伟(Weicky) - weickys@163.com
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @param string
 * @return string
 */
function smarty_modifier_truncatenew($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false, $charset = 'utf-8')
{
    if ($length == 0)
        return '';

    if (mb_strlen($string, $charset) > $length) {
        $length -= min($length, mb_strlen($etc, $charset));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, $charset));
        }
        if(!$middle) {
            return mb_substr($string, 0, $length, $charset) . $etc;
        } else {
            return mb_substr($string, 0, $length/2, $charset) . $etc . mb_substr($string, -$length/2, $length/2, $charset);
        }
    } else {
        return $string;
    }
}

/* vim: set expandtab: */

?>