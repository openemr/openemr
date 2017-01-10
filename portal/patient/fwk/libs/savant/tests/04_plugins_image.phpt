--TEST--
image plugin
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

// fake the $_SERVER settings
$_SERVER['HTTP_USER_AGENT'] = "Mozilla";
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../tests');

$tpl = new Savant3($conf);
$tpl->setPluginConf(
	'image',
	array(
		'imageDir' => 'resources/'
	)
);

$tpl->setTemplate('04_plugins_image.tpl.php');
echo $tpl;

?>
--EXPECT--
<img src="resources/savant.gif" alt="Savant Template System" height="36" width="108" />
<img src="http://phpsavant.com/etc/fester.jpg" alt="fester.jpg" />