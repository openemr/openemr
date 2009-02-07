<?php
require "phpunit.php";

class SelfTestResult extends TextTestResult {
    /* Specialize result class for use in self-tests, to handle
       special situation where many tests are expected to fail. */
    function SelfTestResult() {
	$this->TextTestResult();
	echo '<table class="details">';
	echo '<tr><th>Test name</th><th>Result</th><th>Meta-result</th></tr>';
    }

    function _startTest($test) {
	print('<tr><td>');
	if (phpversion() > '4') {
	    printf("%s - %s ", get_class($test), $test->name());
	} else {
	    printf("%s ", $test->name());
	}
	print('</td>');
	flush();
    }

    function _endTest($test) {
	/* Report both the test result and, for this special situation
	   where some tests are expected to fail, a "meta" test result
	   which indicates whether the test result matches the
	   expected result. */
	$expect_failure = preg_match('/fail/i', $test->name());
	$test_passed = ($test->failed() == 0);

	if ($test->errored())
	    $outcome = "<span class=\"Error\">ERROR</span>";
	else if ($test->failed())
	    $outcome = "<span class=\"Failure\">FAIL</span>";
	else
	    $outcome = "<span class=\"Pass\">OK</span>";

	if ($test->errored())
	    $meta_outcome = '<span class="Unknown">unknown</span>';
	else
	    $meta_outcome = ($expect_failure xor $test_passed)
		? '<span class="Expected">as expected</span>'
		: '<span class="Unexpected">UNEXPECTED</span>';

	printf("<td>$outcome</td><td>$meta_outcome</td></tr>\n");
	flush();
    }
}


class TestFixture extends TestCase {
  function TestFixture($name) {
    $this->TestCase($name);
  }

  function setUp() {
    /* put any common setup here */
    $this->intVal = 1;
    $this->strVal = 'foo';
  }

  function testFail1() {
    $this->assert($this->intVal == 0, "1 == 0"); 
  }

  function testFail2() {
    $this->assert($this->strVal == 'bar');
  }

  function testPass1() {
    $this->assert($this->intVal == 1);
  }
}

$suite = new TestSuite;
$suite->addTest(new TestFixture("testFail1"));
$suite->addTest(new TestFixture("testFail2"));
$suite->addTest(new TestFixture("testPass1"));
//$suite->addTest(new TestFixture("testNotExistFail"));


class Fixture2 extends TestCase {
  function Fixture2($name) {
    $this->TestCase($name);
  }
  function setUp() {
    $this->str1 = 'foo';
    $this->str2 = 'bar';
  }
  function runTest() {
    $this->testStrNotEqual();
    $this->testStrAppend();
  }
  function testStrNotEqual() {
    $this->assert($this->str1 == $this->str2, 'str equal');
  }
  function testStrAppend() {
    $this->assertEquals($this->str1 . 'bar', 'foobars', 'str append');
  }
}

$suite->addTest(new Fixture2("Fail3"));


class TestPass2 extends TestFixture {
  function TestPass2($name) {  $this->TestFixture($name); }
  function runTest() {
    $this->assertEquals($this->strVal . 'x', $this->strVal . 'x');
    $this->assertEquals($this->strVal . 'x', $this->strVal . 'y');
    $this->assertEquals(1, 0);
    $this->assertEquals(1, "1", 'equals int and str');
  }
}
$suite->addTest(new TestPass2("Fail4"));


class MoreTesterTests extends TestCase {
  function MoreTesterTests($name) {  $this->TestCase($name); }

  function testRegexpPass() {
    $this->assertRegexp('/fo+ba[^a-m]/', 'foobar');
  }

  function testRegexpFail() {
    $this->assertRegexp('/fo+ba[^m-z]/', 'foobar');
  }

  function testRegexpFailWithMessage() {
    $this->assertRegexp('/fo+ba[^m-z]/', 'foobar', "This is the message");    
  }
}
$suite->addTest(new TestSuite("MoreTesterTests"));

class ManyFailingTests extends TestCase {
  function ManyFailingTests($name) {  $this->TestCase($name); }

  function testPass1() { $this->assertEquals(0, 0); }
  function testPass2() { $this->assertEquals(0, 0); }
  function testFail1() { $this->assertEquals(1, 0); }
  function testFail2() { $this->assertEquals(1, 0); }
  function testFail3() { $this->assertEquals(1, 0); }
  function testFail4() { $this->assertEquals(1, 0); }
  function testFail5() { $this->assertEquals(1, 0); }
  function testFail6() { $this->assertEquals(1, 0); }
  function testPass3() { $this->assertEquals(0, 0); }
  function testFail7() { $this->assertEquals(1, 0); }
  function testPass4() { $this->assertEquals(0, 0); }
  function testFail8() { $this->assertEquals(1, 0); }
  function testPass5() { $this->assertEquals(0, 0); }
  function testPass6() { $this->assertEquals(0, 0); }
  function testFail9() { $this->assertEquals(1, 0); }
  function testPass7() { $this->assertEquals(0, 0); }
  function testPass8() { $this->assertEquals(0, 0); }
}

$suite->addTest(new TestSuite("ManyFailingTests"));

class DummyClass1 {
    var $fX;
}

class DummyClass2 {
    var $fX;
    var $fY;
    function DummyClass2($x="", $y="") {
	$this->fX = $x;
	$this->fY = $y;
    }
    function equals($another) {
	return $another->fX == $this->fX;
    }
    function toString() {
	return sprintf("DummyClass2(%s, %s)", $this->fX, $this->fY);
    }
}

class AssertEqualsTests extends TestCase {
    function AssertEqualsTests($name) { $this->TestCase($name); }

    function testDiffTypesFail() {
	$this->assertEquals(0, "");
    }
    function testMultiLinePass() {
      $str1 = "line1\nline2\nline3";
      $str2 = "line1\nline2\nline3";
      $this->assertEqualsMultilineStrings($str1, $str2);
    }
    function testMultiLineFail() {
      $str1 = "line1\nline2\nline3";
      $str2 = "line1\nline2 modified\nline3";
      $this->assertEqualsMultilineStrings($str1, $str2);
    }
    function testMultiLineFail2() {
      $str1 = "line1\nline2\nline3";
      $str2 = "line1\nline2\nline3\nline4";
      $this->assertEqualsMultilineStrings($str1, $str2);
    }
}
$suite->addTest(new TestSuite("AssertEqualsTests"));

class AssertEqualsPhp3ErrorTests extends TestCase {
    /* These tests create an ERROR in PHP3 and work as expected in PHP4. */
    function AssertEqualsPhp3ErrorTests($name) { $this->TestCase($name); }
    function testDiffClassFail() {
	$this->assertEquals(new DummyClass1, new DummyClass2);
    }
    function testSameClassPass() {
	$this->assertEquals(new DummyClass1, new DummyClass1);
    }
    function testSameClassFail() {
	$dummy1 = new DummyClass1;
	$dummy2 = new DummyClass1;
	$dummy1->fX = 1;
	$dummy2->fX = 2;
	$this->assertEquals($dummy1, $dummy2);
    }
    function testSameClassEqualsFail() {
	$dummy1 = new DummyClass2(3);
	$dummy2 = new DummyClass2(4);
	$this->assertEquals($dummy1, $dummy2);
    }
    function testSameClassEqualsPass() {
	$dummy1 = new DummyClass2(5, 6);
	$dummy2 = new DummyClass2(5, 7);
	$this->assertEquals($dummy1, $dummy2);
    }
}
$suite->addTest(new TestSuite("AssertEqualsPhp3ErrorTests"));

if (phpversion() >= '4') {
    class AssertEqualsTests4 extends TestCase {
	/* these tests only make sense starting with PHP4 */
	function AssertEqualsTests($name) { $this->TestCase($name); }

	function testNullFail() {
	    $this->assertEquals(0, NULL);
	}
	function testNullPass() {
	    $this->assertEquals(NULL, NULL);
	}
	function testArrayValuesPass1() {
	    $a1 = array('first' => 10, 'second' => 20);
	    $a2 = array('first' => 10, 'second' => 20);
	    $this->assertEquals($a1, $a2);
	}
	function testArrayValuesFail1() {
	    $a1 = array('first' => 10, 'second' => 20);
	    $a2 = array('first' => 10, 'second' => 22);
	    $this->assertEquals($a1, $a2);
	}
    }
    $suite->addTest(new TestSuite("AssertEqualsTests4"));

    class TestClassNameStartingWithTest extends TestCase {
        function TestClassNameStartingWithTest($name) {
            $this->TestCase($name);
        }
        function testWhateverPass() {
            $this->assert(true);
        }
    }
    $suite->addTest(new TestSuite("TestClassNameStartingWithTest"));
}


// $suite now consists of phpUnit self-test suite
?>
