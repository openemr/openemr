<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     replace
 * Purpose:  simple search/replace
 * -------------------------------------------------------------
 */
function smarty_modifier_replace($string, $search, $replace)
{
    return str_replace($search, $replace, $string);
}

/* vim: set expandtab: */

?>
