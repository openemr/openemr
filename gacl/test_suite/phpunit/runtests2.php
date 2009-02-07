<?php // -*- mode: sgml-html; mmm-classes: html-php -*-

include("phpunit_test.php");
// above set $suite to self-test suite

$title = 'PhpUnit test run, different output format';
?>
  <html>
    <head>
      <title><?php echo $title; ?></title>
      <STYLE TYPE="text/css">
	<?php
	include ("stylesheet.css");
	?>
      </STYLE>
    </head>
    <body>
      <h1><?php echo $title; ?></h1>
      <p>
	This page runs all the phpUnit self-tests, and uses the
	PrettyTestResult subclass of TestResult to produce nice HTML output.
      </p>
      <p>
	Unlike typical test run, <strong>expect many test cases to
	  fail</strong>.  Exactly those with <code>pass</code> in their name
	should succeed.
      </p>
      <p>
	<?php
	if (isset($only)) {
	$suite = new TestSuite($only);
	}

	$result = new PrettyTestResult;
	$suite->run($result);
	$result->report();
	?>
    </body>
  </html>
