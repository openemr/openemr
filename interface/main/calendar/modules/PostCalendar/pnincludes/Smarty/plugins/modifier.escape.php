<?php

/*
 * Smarty plugin
 * ------------------------------------------------------------
 * Type:     modifier
 * Name:     escape
 * Purpose:  Escape the string according to escapement type
 * ------------------------------------------------------------
 */
function smarty_modifier_escape($string, $esc_type = 'html')
{
    switch ($esc_type) {
        case 'html':
            return htmlspecialchars($string, ENT_QUOTES);

        case 'htmlall':
            return htmlentities($string, ENT_QUOTES);

        case 'url':
            return urlencode($string);

        case 'quotes':
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);

		case 'hex':
			// escape every character into hex
			for ($x=0; $x < strlen($string); $x++) {
				$return .= '%' . bin2hex($string[$x]);
			}
			return $return;
            
		case 'hexentity':
			for ($x=0; $x < strlen($string); $x++) {
				$return .= '&#x' . bin2hex($string[$x]) . ';';
			}
			return $return;

        default:
            return $string;
    }
}

/* vim: set expandtab: */

?>
