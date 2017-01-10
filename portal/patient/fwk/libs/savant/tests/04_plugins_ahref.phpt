--TEST--
ahref plugin
--FILE--
<?php
require_once '00_prepend.php';


// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('04_plugins_ahref.tpl.php');

// tests

$tpl->text = 'Plain old regular text';
$tpl->href = 'http://example.com?foo=bar&baz=boo#frag';
$tpl->attr = 'title="My Links & Stuff"';
echo $tpl;

$tpl->text = '<img src="something.jpg" />';
$tpl->attr = array('title' => 'My Links & Stuff');
echo $tpl;

$tpl->text = "This & that & the other thing";
$tpl->href = parse_url('http://example.com?foo=bar&baz=boo#frag');
echo $tpl;

?>
--EXPECT--
<a href="http://example.com?foo=bar&amp;baz=boo#frag" title="My Links &amp; Stuff">Plain old regular text</a>
<a href="http://example.com?foo=bar&amp;baz=boo#frag" title="My Links &amp; Stuff"><img src="something.jpg" /></a>
<a href="http://example.com?foo=bar&amp;baz=boo#frag" title="My Links &amp; Stuff">This & that & the other thing</a>
