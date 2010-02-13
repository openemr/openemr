<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * simplified xl() version for smarty templates
 * Christian Navalici 2007
 */


/**
 * Smarty {xl} function plugin
 *
 * Type:     function<br>
 * Name:     xl<br>
 * Purpose:  translate in OpenEMR - Smarty templates<br>
 * 
 * Examples:
 * 
 * {xl t="some words"}
 * 
 * @param array
 * @param Smarty
 */

require_once(dirname(__FILE__) . '../../translation.inc.php');

function smarty_function_xl($params, &$smarty)
{
	if (empty($params['t'])) {
		$smarty->trigger_error("xk: missing 't' parameter");
        	return;
	} else {
        	$translate = $params['t'];
	}

	xl($translate,'e');
}

/* vim: set expandtab: */

?>
