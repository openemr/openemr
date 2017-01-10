--TEST--
filters (manage and apply)
--FILE--
<?php
require_once '00_prepend.php';

// simple filter class
class TestFilter {
	
	public static function thisthat($text)
	{
		$text = str_replace('this', 'these', $text);
		$text = str_replace('that', 'those', $text);
		return $text;
	}
	
	public function overunder($text)
	{
		$text = str_replace('over', 'here', $text);
		$text = str_replace('under', 'there', $text);
		return $text;
	}
}

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('03_filters.tpl.php');

// add a function filter and output
$tpl->addFilters('htmlspecialchars');
echo $tpl->fetch();

// add a static method and output
$tpl->addFilters(array('TestFilter', 'thisthat'));
echo $tpl;

// add an instance method and output
$testFilter = new TestFilter();
$tpl->addFilters(array($testFilter, 'overunder'));
echo $tpl;

// clear them all and output
$tpl->setFilters();
echo $tpl;

// set many at once and output
$tpl->setFilters(
	array('Savant3_Filter_trimwhitespace', 'filter'),
	'htmlspecialchars',
	array('TestFilter', 'thisthat'),
	array($testFilter, 'overunder')
);
echo $tpl;

// reset, add many at once, and output
$tpl->setFilters();
$tpl->addFilters(
	array('Savant3_Filter_trimwhitespace', 'filter'),
	'htmlspecialchars',
	array('TestFilter', 'thisthat'),
	array($testFilter, 'overunder')
);
echo $tpl;

?>
--EXPECT--
&lt;p&gt;This is a paragraph&lt;/p&gt;



&lt;pre&gt;
Some

Special

Test
&lt;/pre&gt;



&lt;p&gt;Change this to that&lt;/p&gt;



&lt;p&gt;Switch from over to under&lt;/p&gt;



----- END -----

&lt;p&gt;This is a paragraph&lt;/p&gt;



&lt;pre&gt;
Some

Special

Test
&lt;/pre&gt;



&lt;p&gt;Change these to those&lt;/p&gt;



&lt;p&gt;Switch from over to under&lt;/p&gt;



----- END -----

&lt;p&gt;This is a paragraph&lt;/p&gt;



&lt;pre&gt;
Some

Special

Test
&lt;/pre&gt;



&lt;p&gt;Change these to those&lt;/p&gt;



&lt;p&gt;Switch from here to there&lt;/p&gt;



----- END -----

<p>This is a paragraph</p>



<pre>
Some

Special

Test
</pre>



<p>Change this to that</p>



<p>Switch from over to under</p>



----- END -----

&lt;p&gt;This is a paragraph&lt;/p&gt;
&lt;pre&gt;
Some

Special

Test
&lt;/pre&gt;
&lt;p&gt;Change these to those&lt;/p&gt;
&lt;p&gt;Switch from here to there&lt;/p&gt;
----- END -----&lt;p&gt;This is a paragraph&lt;/p&gt;
&lt;pre&gt;
Some

Special

Test
&lt;/pre&gt;
&lt;p&gt;Change these to those&lt;/p&gt;
&lt;p&gt;Switch from here to there&lt;/p&gt;
----- END -----