<?php
/**
 * Smarty shared plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Function: smarty_make_timestamp<br>
 * Purpose:  used by other smarty functions to make a timestamp
 *           from a string.
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @return string
 */
function smarty_make_timestamp($string)
{
    if(empty($string)) {
        // use "now":
        $time = time();

    } elseif (preg_match('/^\d{14}$/', (string) $string)) {
        // it is mysql timestamp format of YYYYMMDDHHMMSS?
        $time = mktime(substr((string) $string, 8, 2),substr((string) $string, 10, 2),substr((string) $string, 12, 2),
                       substr((string) $string, 4, 2),substr((string) $string, 6, 2),substr((string) $string, 0, 4));

    } elseif (is_numeric($string)) {
        // it is a numeric string, we handle it as timestamp
        $time = (int)$string;

    } else {
        // strtotime should handle it
        $time = strtotime((string) $string);
        if ($time == -1 || $time === false) {
            // strtotime() was not able to parse $string, use "now":
            $time = time();
        }
    }
    return $time;

}

/* vim: set expandtab: */

?>
