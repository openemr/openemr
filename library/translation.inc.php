<?php
// include_once($GLOBALS['srcdir'] . '/sql.inc');
include_once(dirname(__FILE__) . '/sql.inc'); // fixes vulnerability with register_globals

// this function is related to the interface/language module.

function xl($constant,$mode='r',$prepend='',$append='') {
	$lang_id=LANGUAGE;
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

/**
HEADER HTML

shows some informations for pages html header 

@param none
@return void
*/
function html_header_show() {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> '."\n";
}

?>
