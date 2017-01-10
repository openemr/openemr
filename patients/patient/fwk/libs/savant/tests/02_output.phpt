--TEST--
general output
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);

// assign variables
$array = array(
	'key0' => 'val0',
	'key1' => 'val1',
	'key2' => 'val2',
);

$var1 = 'variable1';
$var2 = 'variable2';
$var3 = 'variable3';

$ref1 = 'reference1';
$ref2 = 'reference2';
$ref3 = 'reference3';

$tpl->assign($var1, $var1);
$tpl->assign($var2, $var2);
$tpl->assign($var3, $var3);

$tpl->assign('set', $array);
$tpl->assign($array);

$tpl->$ref1 =& $ref1;
$tpl->$ref2 =& $ref2;
$tpl->$ref3 =& $ref3;

// echo non-existent template
$tpl->setTemplate('no_such_template.tpl.php');
echo $tpl;

// echo existing template
$tpl->setTemplate('02_output.tpl.php');
echo $tpl;

// fetch existent template
echo $tpl->fetch('02_output.tpl.php');

?>
--EXPECT--
template error, examine fetch() result

<p>variable1</p>
<p>variable2</p>
<p>variable3</p>
<p>val0</p>
<p>val1</p>
<p>val2</p>
<p>reference1</p>
<p>reference2</p>
<p>reference3</p>
<ul>
<li>key0 = val0</li>
<li>key1 = val1</li>
<li>key2 = val2</li>
</ul>
<p>variable1</p>
<p>variable2</p>
<p>variable3</p>
<p>val0</p>
<p>val1</p>
<p>val2</p>
<p>reference1</p>
<p>reference2</p>
<p>reference3</p>
<ul>
<li>key0 = val0</li>
<li>key1 = val1</li>
<li>key2 = val2</li>
</ul>