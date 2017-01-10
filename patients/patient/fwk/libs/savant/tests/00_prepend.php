<?php
// header('Content-type: text/plain');
ini_set ( 'error_reporting', E_ALL | E_STRICT );
ini_set ( 'display_errors', true );
ini_set ( 'html_errors', false );

// for 5.1RC1 timezone strictness
ini_set ( 'date.timezone', 'America/Chicago' );

// for dumping variables to output
function dump(&$var, $label = null) {
	if ($label) {
		echo $label . " ";
	}
	ob_start ();
	var_dump ( $var );
	$output = ob_get_clean ();
	$output = preg_replace ( "/\]\=\>\n(\s+)/m", "] => ", $output );
	echo $output;
}

// add to the include_path
$add = realpath ( dirname ( __FILE__ ) . '/../' );
set_include_path ( $add );

chdir ( dirname ( __FILE__ ) );

// make sure we have Savant ;-)
require_once 'Savant3.php';

?>