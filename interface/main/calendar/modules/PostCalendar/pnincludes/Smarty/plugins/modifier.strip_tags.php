<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     strip_tags
 * Purpose:  strip html tags from text
 * -------------------------------------------------------------
 */
function smarty_modifier_strip_tags($string, $replace_with_space = true)
{
    if ($replace_with_space)
        return preg_replace('!<[^>]*?>!', ' ', $string);
    else
        return strip_tags($string);
}

/* vim: set expandtab: */

?>
