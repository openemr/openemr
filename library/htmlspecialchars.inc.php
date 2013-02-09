<?php
/**
 * library/htmlspecialchars.inc.php Escaping Functions
 *
 * Copyright © 2011 Boyd Stephen Smith Jr.
 * This file is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This file is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Boyd Stephen Smith Jr.
 */

/**
 * Escape a PHP string for use as (part of) an HTML / XML text node.
 *
 * It only escapes a few special chars: the ampersand (&) and both the left-
 * pointing angle bracket (<) and the right-pointing angle bracket (>), since
 * these are the only characters that are special in a text node.  Minimal
 * quoting is preferred because it produces smaller and more easily human-
 * readable output.
 *
 * Some characters simply cannot appear in valid XML documents, even
 * as entities but, this function does not attempt to handle them.
 *
 * NOTE: Attribute values are NOT text nodes, and require additional escaping.
 *
 * @param string $text The string to escape, possibly including "&", "<",
 *                     or ">".
 * @return string The string, with "&", "<", and ">" escaped.
 */
function text($text) {
	return htmlspecialchars($text, ENT_NOQUOTES);
}

/**
 * Escape a PHP string for use as (part of) an HTML / XML attribute value.
 *
 * It escapes several special chars: the ampersand (&), the double quote
 * ("), the singlequote ('), and both the left-pointing angle bracket (<)
 * and the right-pointing angle bracket (>), since these are the characters
 * that are special in an attribute value.
 *
 * Some characters simply cannot appear in valid XML documents, even
 * as entities but, this function does not attempt to handle them.
 *
 * NOTE: This can be used as a "generic" HTML escape since it does maximal
 * quoting.  However, some HTML and XML contexts (CDATA) don't provide
 * escape mechanisms.  Also, further pre- or post-escaping might need to 
 * be done when embdedded other languages (like JavaScript) inside HTML / 
 * XML documents.
 *
 * @param string $text The string to escape, possibly including (&), (<),
 *                     (>), ('), and (").
 * @return string The string, with (&), (<), (>), ("), and (') escaped.
 */
function attr($text) {
	return htmlspecialchars($text, ENT_QUOTES);
}

/*
This function is a compatibility replacement for the out function removed from
the CDR Admin framework.
*/
function out($text) {
	return attr($text);
}

/*
Don't call this function.  You don't see this function.  This function doesn't
exist.

TODO: Hide this function so it can be called from this file but not from PHP
that includes / requires this file.  Either that, or write reasonable
documentation and clean up the name.
*/
function hsc_private_xl_or_warn($key) {
	if (function_exists('xl')) {
		return xl($key);
	} else {
		trigger_error(
			'Translation via xl() was requested, but the xl()'
			. ' function is not defined, yet.',
			E_USER_WARNING
		);
		return $key;
	}
}

/*
Translate via xl() and then escape via text().
*/
function xlt($key) {
	return text(hsc_private_xl_or_warn($key));
}

/*
Translate via xl() and then escape via attr().
*/
function xla($key) {
	return attr(hsc_private_xl_or_warn($key));
}

/*
Translate via xl() and then escape via addslashes for use with javascript literals
*/
function xls($key){
	return addslashes(hsc_private_xl_or_warn($key));
}
return; // Stop include / require from going any further (non-PHP)
?>
