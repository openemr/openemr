<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     indent
 * Purpose:  indent lines of text
 * -------------------------------------------------------------
 */
function smarty_modifier_indent($string,$chars=4,$char=" ")
{
	return preg_replace('!^!m',str_repeat($char,$chars),$string);
}

?>
