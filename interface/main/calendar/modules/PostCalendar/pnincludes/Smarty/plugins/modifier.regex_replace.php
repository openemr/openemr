<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     regex_replace
 * Purpose:  regular epxression search/replace
 * -------------------------------------------------------------
 */
function smarty_modifier_regex_replace($string, $search, $replace)
{
    return preg_replace($search, $replace, $string);
}

/* vim: set expandtab: */

?>
