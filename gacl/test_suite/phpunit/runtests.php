<?php // -*- mode: html; mmm-classes: html-php -*-

include("phpunit_test.php");
// above set $suite to self-test suite

$title = 'PhpUnit test run';
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
      Unlike a typical test run, <strong>expect many test cases to
      fail</strong>.  Exactly those with <code>pass</code> in their
      name should succeed.
    <p>
      For each test we display both the test result -- <span
      class="Pass">ok</span>, <span class="Failure">FAIL</span>, or
      <span class="Error">ERROR</span> -- and also a meta-result --
      <span class="Expected">as expected</span>, <span
      class="Unexpected">UNEXPECTED</span>, or <span
      class="Unknown">unknown</span> -- that indicates whether the
      expected test result occurred.  Although many test results will
      be 'FAIL' here, all meta-results should be 'as expected', except
      for a few 'unknown' meta-results (because of errors) when running
      in PHP3.
    <h2>Test Results</h2>
      <?php
       if (isset($only))
	$suite = new TestSuite($only);
	$result = new SelfTestResult;

	$suite->run($result);
	print('</table>');
	$result->report();
	?>
  </body>
</html>
