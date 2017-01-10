--TEST--
error reporting
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);

// basic errors
$result = $tpl->error(
	'ERR_UNKNOWN',
	array(
		'type' => 'Savant_Error',
		'key1' => 'val1',
		'key2' => 'val2'
	),
	E_USER_NOTICE, // severity level
	false // no trace
);
dump($result);

// PHP5 exceptions
$tpl->setExceptions(true);
try {
	$result = $tpl->error(
		'ERR_UNKNOWN',
		array(
			'type' => 'Savant_Error_exception',
			'key1' => 'val1',
			'key2' => 'val2'
		),
		E_USER_NOTICE, // severity level
		false // no trace
	);
	dump($result);
} catch (Savant3_Exception $e) {
	echo "\nCaught exception.\n";
}


?>
--EXPECT--
object(Savant3_Error)#2 (4) {
  ["code"] => string(11) "ERR_UNKNOWN"
  ["info"] => array(3) {
    ["type"] => string(12) "Savant_Error"
    ["key1"] => string(4) "val1"
    ["key2"] => string(4) "val2"
  }
  ["level"] => int(1024)
  ["trace"] => bool(false)
}

Caught exception.