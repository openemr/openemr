<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     wordwrap
 * Purpose:  wrap a string of text at a given length
 * -------------------------------------------------------------
 */
function smarty_modifier_wordwrap($string,$length=80,$break="\n",$cut=false)
{
	return wordwrap($string,$length,$break,$cut);
}

?>
