<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     debug
 * Version:  1.0
 * Date:     July 1, 2002
 * Author:	 Monte Ohrt <monte@ispi.net>
 * Purpose:  popup debug window
 * -------------------------------------------------------------
 */
function smarty_function_debug($params, &$smarty)
{
	if($params['output']) {
		$smarty->assign('_smarty_debug_output',$params['output']);
	}
	echo $smarty->_generate_debug_output();
}

/* vim: set expandtab: */

?>
