<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     debug_print_var
 * Purpose:  formats variable contents for display in the console
 * -------------------------------------------------------------
 */
function smarty_modifier_debug_print_var($var, $depth = 0, $length = 40)
{
    if (is_array($var)) {
        $results = "<b>Array (".count($var).")</b>";
        foreach ($var as $curr_key => $curr_val) {
            $return = smarty_modifier_debug_print_var($curr_val, $depth+1);
            $results .= '<br>\r'.str_repeat('&nbsp;', $depth*2)."<b>$curr_key</b> =&gt; $return";
        }
        return $results;
    } else if (is_object($var)) {
        $object_vars = get_object_vars($var);
        $results = "<b>".get_class($var)." Object (".count($object_vars).")</b>";
        foreach ($object_vars as $curr_key => $curr_val) {
            $return = smarty_modifier_debug_print_var($curr_val, $depth+1);
            $results .= '<br>\r'.str_repeat('&nbsp;', $depth*2)."<b>$curr_key</b> =&gt; $return";
        }
        return $results;
    } else {
        if (empty($var) && $var != "0") {
            return '<i>empty</i>';
        }
        if (strlen($var) > $length ) {
            $results = substr($var, 0, $length-3).'...';
        } else {
            $results = $var;
        }
        $results = preg_replace("![\r\t\n]!", " ", $results);
        $results = htmlspecialchars(htmlspecialchars($results));
        return $results;
    }
}

/* vim: set expandtab: */

?>
