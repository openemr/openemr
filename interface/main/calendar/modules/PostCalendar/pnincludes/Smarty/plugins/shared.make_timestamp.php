<?php

/*======================================================================*\
    Function: smarty_make_timestamp
    Purpose:  used by other smarty functions to make a timestamp
              from a string.
\*======================================================================*/
function smarty_make_timestamp($string)
{
    if(empty($string)) {
        $string = "now";
    }
    $time = strtotime($string);
    if (is_numeric($time) && $time != -1)
        return $time;

    // is mysql timestamp format of YYYYMMDDHHMMSS?
    if (is_numeric($string) && strlen($string) == 14) {
        $time = mktime(substr($string,8,2),substr($string,10,2),substr($string,12,2),
               substr($string,4,2),substr($string,6,2),substr($string,0,4));

        return $time;
    }

    // couldn't recognize it, try to return a time
    $time = (int) $string;
    if ($time > 0)
		return $time;
	else
		return time();
}
/* vim: set expandtab: */

?>
