<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     date_format
 * Purpose:  format datestamps via strftime
 * Input:    string: input date string
 *           format: strftime format for output
 *           default_date: default date if $string is empty
 * -------------------------------------------------------------
 */
require_once $this->_get_plugin_filepath('shared','make_timestamp');
function smarty_modifier_date_format($string, $format="%b %e, %Y", $default_date=null)
{
	if($string != '') {
    	return strftime($format, smarty_make_timestamp($string));
	} elseif (isset($default_date) && $default_date != '') {		
    	return strftime($format, smarty_make_timestamp($default_date));
	} else {
		return;
	}
}

/* vim: set expandtab: */

?>
