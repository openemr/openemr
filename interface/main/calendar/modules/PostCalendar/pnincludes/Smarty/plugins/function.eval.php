<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     eval
 * Purpose:  evaluate a template variable as a template
 * -------------------------------------------------------------
 */
function smarty_function_eval($params, &$smarty)
{
    extract($params);

    if (!isset($var)) {
        $this->trigger_error("eval: missing 'var' parameter");
        return;
    }
	if($var == '') {
		return;
	}

	$smarty->_compile_template("evaluated template", $var, $source);
	$source="";
	
    if (!empty($assign)) {
        ob_start();
		eval('?>' . $source);
        $this->assign($assign, ob_get_contents());
        ob_end_clean();
    } else {
		eval('?>' . $source);
    }
}
/* vim: set expandtab: */

?>
