--TEST--
__autoload() checks
--FILE--
<?php
require_once '00_prepend.php';

function __autoload($class) {
    echo "***trying autoload***\n";
}

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);

echo "Without Autoload -- \n";
$result = $tpl->date('1970-08-08');
dump($result, 'Date plugin:');
$result = $tpl->noSuch();
$class = get_class($result);
dump($class, 'NoSuch plugin:');

$tpl->setAutoload(true);

echo "\nWith Autoload -- \n";
$result = $tpl->date('1970-08-08');
dump($result, 'Date plugin:');
$result = $tpl->noSuch();
$class = get_class($result);
dump($class, 'NoSuch plugin:');
?>
--EXPECT--
Without Autoload -- 
Date plugin: string(24) "Sat Aug  8 00:00:00 1970"
NoSuch plugin: string(13) "Savant3_Error"

With Autoload -- 
Date plugin: string(24) "Sat Aug  8 00:00:00 1970"
***trying autoload***
NoSuch plugin: string(13) "Savant3_Error"