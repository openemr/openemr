<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     count_characteres
 * Purpose:  count the number of characters in a text
 * -------------------------------------------------------------
 */
function smarty_modifier_count_characters($string, $include_spaces = false)
{
    if ($include_spaces)
       return(strlen($string));

    return preg_match_all("/[^\s]/",$string, $match);
}

/* vim: set expandtab: */

?>
