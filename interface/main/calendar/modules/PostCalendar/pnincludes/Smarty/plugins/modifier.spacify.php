<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     spacify
 * Purpose:  add spaces between characters in a string
 * -------------------------------------------------------------
 */
function smarty_modifier_spacify($string, $spacify_char = ' ')
{
    return implode($spacify_char,
                   preg_split('//', $string, -1, PREG_SPLIT_NO_EMPTY));
}

/* vim: set expandtab: */

?>
