<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     count_sentences
 * Purpose:  count the number of sentences in a text
 * -------------------------------------------------------------
 */
function smarty_modifier_count_sentences($string)
{
    // find periods with a word before but not after.
    return preg_match_all('/[^\s]\.(?!\w)/', $string, $match);
}

/* vim: set expandtab: */

?>
