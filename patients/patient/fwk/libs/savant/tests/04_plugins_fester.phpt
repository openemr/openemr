--TEST--
local resource plugin (fester)
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('04_plugins_fester.tpl.php');
echo $tpl;

?>
--EXPECT--
Fester is printing this: Gomez (0)
Fester is printing this: Morticia (1)
Fester is printing this: Thing (2)
