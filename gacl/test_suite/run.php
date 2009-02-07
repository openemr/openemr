<?php
	// require gacl & phpunit
	require_once(dirname(__FILE__).'/../admin/gacl_admin.inc.php');
	require_once(dirname(__FILE__).'/phpunit/phpunit.php');
	
	/*! class
	 custom test result class for pretty results
	 !*/
	class gacl_test_result extends TestResult {
		
		var $output = '';
		var $class_name;
		
		function report() {
			/* results table */
			echo '<h2>Test Results</h2>' . "\n";
			echo '<table cellspacing="1" cellpadding="1" border="1" class="details">'."\n";
			echo '<tr><th>Function</th><th width="10%">Success?</th></tr>'."\n";
			echo $this->output;
			echo '</table>'."\n";
			
			/* summary */
			$nRun = $this->countTests();
			$nFailures = $this->countFailures();
			echo '<h2>Summary</h2>'."\n";
			printf('<div class="indent"><p>%s test%s run<br />', $nRun, ($nRun == 1) ? '' : 's');
			printf("%s failure%s.</p></div>\n", $nFailures, ($nFailures == 1) ? '' : 's');
		}
		
		function _startTest($test) {
		}
		
		function _endTest($test) {
			if ( $test->classname() != $this->class_name ) {
				$this->class_name = $test->classname();
				$this->output .= '<tr><td colspan="2" class="class_name">'. $test->classname() .'</td></tr>'."\n";
			}
			
			$this->output .= '<tr><td class="function">'. $test->name();
			if ($test->failed()) {
				$this->output .= "<ul>\n";
				foreach ($test->getExceptions() as $exception) {
					$this->output .= '<li>'. $exception->getMessage() ."</li>\n";
				}
				$this->output .= "</ul>\n";
				
				$outcome = ' class="fail">FAIL';
			} else {
				$outcome = ' class="pass">OK';
			}
			
			$this->output .= '</td><td'. $outcome .'</td></tr>'."\n";
		}
	}
	
	/*! class
	 custom TestCase class to allow control of error formatting
	 
	 can also be used for custom assert functions
	 !*/
	class gacl_test_case extends TestCase {
		
		var $gacl_api;
		
		function gacl_test_case($name) {
			$this->TestCase($name);
			$this->gacl_api = &$GLOBALS['gacl_api'];
		}
		
		function setUp() {
		}
		
		function tearDown() {
		}
		
		function _formatValue($value, $class='') {
			if (phpversion() < '4.0.0') {
				return '<code class="'. $class .'">'. htmlentities((string)$value) .'</code>';
			}
			
			switch (TRUE)
			{
				case is_object($value):
					if (method_exists($value, 'toString')) {
						$translateValue = $value->toString();
					} else {
						$translateValue = serialize($value);
					}
					
					$htmlValue = htmlentities($translateValue);
					break;
				case is_array($value):
					ob_start();
						print_r($value);
						$translateValue = ob_get_contents();
					ob_end_clean();
					
					$htmlValue = nl2br(str_replace('    ', '&nbsp; &nbsp; ', htmlentities(rtrim($translateValue))));
					break;
				case is_bool($value):
					$htmlValue = $value ? '<i>true</i>' : '<i>false</i>';
					break;
				case phpversion() >= '4.0.4' && is_null($value):
					$htmlValue = '<i>null</i>';
					break;
				default:
					$htmlValue = htmlentities(strval($value));
			}
			
			$htmlValue = '<code class="'. $class . '">' . $htmlValue . '</code>';
			$htmlValue .= '&nbsp;&nbsp;<span class="typeinfo">';
			$htmlValue .= 'type:' . gettype($value);
			
			if (is_object($value)) {
				$htmlValue .= ', class:' . get_class($value);
			}
			
			$htmlValue .= '</span>';
			
			return $htmlValue;
		}
	}
	
	/*! class
	 custom TestSuite class for future expansion
	 !*/
	class gacl_test_suite extends TestSuite {
		
	}
	
	$title = 'phpGACL Test Suite';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="styles.css" type="text/css" title="GACL Test Suite Styles"/>
  </head>
  <body>
    <h1><?php echo $title; ?></h1>
    <h2>Running Tests</h2>
    <div class="indent">
<?php
	// initialise result
	$result = new gacl_test_result;
	
	// run api tests
	include('unit_tests.php');
	
	// run acl tests
	include('acl_tests.php');
	
	echo '
    </div>
    ';
	
	// show report
	$result->report();
?>
  </body>
</html>
