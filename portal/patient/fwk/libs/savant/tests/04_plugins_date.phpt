--TEST--
date plugin
--FILE--
<?php
require_once '00_prepend.php';


// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('04_plugins_date.tpl.php');

$conf = array(
	'default' => '%b %d, %Y'
);

$tpl->setPluginConf('date', $conf);

$tpl->date = '2004-12-24';
echo $tpl;

$tpl->date = '1970-08-08';
echo $tpl;

?>
--EXPECT--
<p>This is a paragraph with a date of Dec 24, 2004</p>
<p>This is a paragraph with a date of Aug 08, 1970</p>