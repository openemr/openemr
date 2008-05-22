<?php
// include_once($GLOBALS['srcdir'] . '/sql.inc');
include_once(dirname(__FILE__) . '/sql.inc'); // fixes vulnerability with register_globals

// this function is related to the interface/language module.

function xl($constant,$mode='r',$prepend='',$append='') {
	$lang_id=LANGUAGE;
	
	// utf8 compliant
    sqlQuery("SET NAMES utf8");
    	$sql="SELECT * FROM lang_definitions JOIN lang_constants ON " .
    "lang_definitions.cons_id = lang_constants.cons_id WHERE " .
    "lang_id='$lang_id' AND constant_name = '" .
    addslashes($constant) . "' LIMIT 1";
	$res=SqlStatement($sql);
	$row=SqlFetchArray($res);
	$string=$row['definition'];
	if ($string=='') { $string="$constant"; }
	$string="$prepend"."$string"."$append";
	if ($mode=='e'){
		echo $string;
	} else {
		return $string;
	}
}


// ----------------------------------------------------------------------------
/**
HEADER HTML

shows some informations for pages html header 

@param none
@return void
*/
function html_header_show() {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> '."\n";
}


// ----------------------------------------------------------------------------
/**
* Returns a string padded to a certain length with another string.
*
* This method behaves exactly like str_pad but is multibyte safe.
*
* @param string $input    The string to be padded.
* @param int $length      The length of the resulting string.
* @param string $pad      The string to pad the input string with. Must
*                         be in the same charset like the input string.
* @param const $type      The padding type. One of STR_PAD_LEFT,
*                         STR_PAD_RIGHT, or STR_PAD_BOTH.
* @param string $charset  The charset of the input and the padding
*                         strings.
*
* @return string  The padded string.
*/
function mb_strpad($input, $length, $pad = ' ', $type = STR_PAD_RIGHT, $charset = 'UTF-8') {
    mb_internal_encoding($charset);
    $mb_length = mb_strlen($input, $charset);
    $sb_length = strlen($input);
    $pad_length = mb_strlen($pad, $charset);

    /* Return if we already have the length. */
    if ($mb_length >= $length) {
        return $input;
    }

    /* Shortcut for single byte strings. */
    if ($mb_length == $sb_length && $pad_length == strlen($pad)) {
        return str_pad($input, $length, $pad, $type);
    }

    switch ($type) {
        case STR_PAD_LEFT:
            $left = $length - $mb_length;
            $output = mb_substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) . $input;
        break;
        case STR_PAD_BOTH:
            $left = floor(($length - $mb_length) / 2);
            $right = ceil(($length - $mb_length) / 2);
            $output = mb_substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) .
            $input .
            mb_substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
        break;
        case STR_PAD_RIGHT:
            $right = $length - $mb_length;
            $output = $input . mb_substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
        break;
    }

return $output;
}
?>
