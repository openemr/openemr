<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     count_paragraphs
 * Purpose:  count the number of paragraphs in a text
 * -------------------------------------------------------------
 */
function smarty_modifier_count_paragraphs($string)
{
    // count \r or \n characters
    return count(preg_split('/[\r\n]+/', $string));
}

/* vim: set expandtab: */

?>
