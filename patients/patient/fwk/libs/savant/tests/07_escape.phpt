--TEST--
output escaping: escape() and eprint()
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('07_escape.tpl.php');

$tpl->setEscape('stripslashes');
$tpl->addEscape('htmlspecialchars');

$text = <<<EOF
<html>
    <body>
        This\'s special & so\'s that.
    </body>
</html>
EOF;

$tpl->text = $text;
echo $tpl;

?>
--EXPECT--
&lt;html&gt;
    &lt;body&gt;
        This's special &amp; so's that.
    &lt;/body&gt;
&lt;/html&gt;
&lt;html&gt;
    &lt;body&gt;
        This's special &amp; so's that.
    &lt;/body&gt;
&lt;/html&gt;