<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     string_format
 * Purpose:  format strings via sprintf
 * -------------------------------------------------------------
 */
function smarty_modifier_string_format($string, $format)
{
    return sprintf($format, $string);
}

/* vim: set expandtab: */

?>
