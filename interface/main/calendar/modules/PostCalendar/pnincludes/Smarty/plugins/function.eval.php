<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     eval
 * Purpose:  evaluate a template variable as a template
 * -------------------------------------------------------------
 */
function smarty_function_eval($params, &$this)
{
    extract($params);

    if (!isset($var)) {
        $this->trigger_error("eval: missing 'var' parameter");
        return;
    }
	if($var == '') {
		return;
	}

	$this->_compile_template("evaluated template", $var, $source);
	
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
