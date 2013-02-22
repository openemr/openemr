<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * user_info() version for smarty templates
 * Kevin Yeh 2013
 */


/**
 * Smarty {user_info} function plugin
 *
 * Type:     function<br>
 * Name:     user_info<br>
 * Purpose:  Return the user info for a given ID<br>
 * 
 * Examples:
 * 
 * {user_info id=1}
 * 
 * @param array
 * @param Smarty
 */

require_once(dirname(__FILE__) . '../../user.inc');

function smarty_function_user_info($params, &$smarty)
{
	if (empty($params['id'])) {
		$smarty->trigger_error("user_info: missing 'id' parameter");
        	return;
	} else {
        	$user_id = $params['id'];
	}

        $user_info=getUserIDInfo($user_id);
        if($user_info)
        {
            echo $user_info['fname']." ".$user_info['lname'];            
        }
}


?>
