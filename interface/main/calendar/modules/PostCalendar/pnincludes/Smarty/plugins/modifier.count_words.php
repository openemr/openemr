<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     count_words
 * Purpose:  count the number of words in a text
 * -------------------------------------------------------------
 */
function smarty_modifier_count_words($string)
{
    // split text by ' ',\r,\n,\f,\t
    $split_array = preg_split('/\s+/',$string);
    // count matches that contain alphanumerics
    $word_count = preg_grep('/[a-zA-Z0-9]/', $split_array);

    return count($word_count);
}

/* vim: set expandtab: */

?>
