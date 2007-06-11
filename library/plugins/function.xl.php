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

function smarty_function_xl($params, &$smarty)
{
	if (empty($params['t'])) {
		$smarty->trigger_error("xk: missing 't' parameter");
        	return;
	} else {
        	$translate = $params['t'];
	}

	$lang_id = LANGUAGE;
	$sql = "SELECT * FROM lang_definitions JOIN lang_constants ON " .
    	"lang_definitions.cons_id = lang_constants.cons_id WHERE " .
    	"lang_id='$lang_id' AND constant_name = '" .
    	addslashes($translate) . "' LIMIT 1";
	
	$res = SqlStatement($sql);
	$row = SqlFetchArray($res);

	$string = $row['definition'];

	if ($string=='') { 
		$string="$translate"; 
	}

	echo $string;
}

/* vim: set expandtab: */

?>
