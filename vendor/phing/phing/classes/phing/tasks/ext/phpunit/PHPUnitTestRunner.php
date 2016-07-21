<?php
/**
 * $Id: 77ea1859b96ca9889af0e465b1a27a09600557da $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/tasks/ext/coverage/CoverageMerger.php';
require_once 'phing/system/util/Timer.php';

/**
 * Simple Testrunner for PHPUnit that runs all tests of a testsuite.
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 77ea1859b96ca9889af0e465b1a27a09600557da $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
class PHPUnitTestRunner extends PHPUnit_Runner_BaseTestRunner implements PHPUnit_Framework_TestListener
{
    private $hasErrors = false;
    private $hasFailures = false;
    private $hasIncomplete = false;
    private $hasSkipped = false;
    private $lastErrorMessage = '';
    private $lastFailureMessage = '';
    private $lastIncompleteMessage = '';
    private $lastSkippedMessage = '';
    private $formatters = array();
    private $listeners = array();

    private $codecoverage = null;

    private $project = null;

    private $groups = array();
    private $excludeGroups = array();

    private $processIsolation = false;

    private $useCustomErrorHandler = true;

    /**
     * @param Project $project
     * @param array $groups
     * @param array $excludeGroups
     * @param bool $processIsolation
     */
    public function __construct(
        Project $project,
        $groups = array(),
        $excludeGroups = array(),
        $processIsolation = false
    ) {
        $this->project = $project;
        $this->groups = $groups;
        $this->excludeGroups = $excludeGroups;
        $this->processIsolation = $processIsolation;
    }

    /**
     * @param $codecoverage
     */
    public function setCodecoverage($codecoverage)
    {
        $this->codecoverage = $codecoverage;
    }

    /**
     * @param $useCustomErrorHandler
     */
    public function setUseCustomErrorHandler($useCustomErrorHandler)
    {
        $this->useCustomErrorHandler = $useCustomErrorHandler;
    }

    /**
     * @param $formatter
     */
    public function addFormatter($formatter)
    {
        $this->addListener($formatter);
        $this->formatters[] = $formatter;
    }

    /**
     * @param $level
     * @param $message
     * @param $file
     * @param $line
     */
    public function handleError($level, $message, $file, $line)
    {
        return PHPUnit_Util_ErrorHandler::handleError($level, $message, $file, $line);
    }

    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Run a test
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function run(PHPUnit_Framework_TestSuite $suite)
    {
        $res = new PHPUnit_Framework_TestResult();

        if ($this->codecoverage) {
            $whitelist = CoverageMerger::getWhiteList($this->project);

            $this->codecoverage->filter()->addFilesToWhiteList($whitelist);

            $res->setCodeCoverage($this->codecoverage);
        }

        $res->addListener($this);

        foreach ($this->formatters as $formatter) {
            $res->addListener($formatter);
        }

        /* Set PHPUnit error handler */
        if ($this->useCustomErrorHandler) {
            $oldErrorHandler = set_error_handler(array($this, 'handleError'), E_ALL | E_STRICT);
        }

        $version = PHPUnit_Runner_Version::id();
        if (version_compare($version, '4.0.0') >= 0) {
            $this->injectFilters($suite);
            $suite->run($res);
        } else {
            $suite->run($res, false, $this->groups, $this->excludeGroups, $this->processIsolation);
        }

        foreach ($this->formatters as $formatter) {
            $formatter->processResult($res);
        }

        /* Restore Phing error handler */
        if ($this->useCustomErrorHandler) {
            restore_error_handler();
        }

        if ($this->codecoverage) {
            CoverageMerger::merge($this->project, $this->codecoverage->getData());
        }

        $this->checkResult($res);
    }

    private function checkResult($res)
    {
        if ($res->skippedCount() > 0) {
            $this->hasSkipped = true;
        }

        if ($res->notImplementedCount() > 0) {
            $this->hasIncomplete = true;
        }

        if ($res->failureCount() > 0) {
            $this->hasFailures = true;
        }

        if ($res->errorCount() > 0) {
            $this->hasErrors = true;
        }
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->hasErrors;
    }

    /**
     * @return boolean
     */
    public function hasFailures()
    {
        return $this->hasFailures;
    }

    /**
     * @return boolean
     */
    public function hasIncomplete()
    {
        return $this->hasIncomplete;
    }

    /**
     * @return boolean
     */
    public function hasSkipped()
    {
        return $this->hasSkipped;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    /**
     * @return string
     */
    public function getLastFailureMessage()
    {
        return $this->lastFailureMessage;
    }

    /**
     * @return string
     */
    public function getLastIncompleteMessage()
    {
        return $this->lastIncompleteMessage;
    }

    /**
     * @return string
     */
    public function getLastSkippedMessage()
    {
        return $this->lastSkippedMessage;
    }

    /**
     * @param string $message
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @return string
     */
    protected function composeMessage($message, PHPUnit_Framework_Test $test, Exception $e)
    {
        $message = "Test $message (" . $test->getName() . " in class " . get_class($test) . "): " . $e->getMessage();

        if ($e instanceof PHPUnit_Framework_ExpectationFailedException && $e->getComparisonFailure()) {
            $message .= "\n" . $e->getComparisonFailure()->getDiff();
        }

        return $message;
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->lastErrorMessage = $this->composeMessage("ERROR", $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->lastFailureMessage = $this->composeMessage("FAILURE", $test, $e);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->lastIncompleteMessage = $this->composeMessage("INCOMPLETE", $test, $e);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->lastSkippedMessage = $this->composeMessage("SKIPPED", $test, $e);
    }

    /**
     * Risky test
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A test started.
     *
     * @param string $testName
     */
    public function testStarted($testName)
    {
    }

    /**
     * A test ended.
     *
     * @param string $testName
     */
    public function testEnded($testName)
    {
    }

    /**
     * A test failed.
     *
     * @param integer                                $status
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     */
    public function testFailed($status, PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e)
    {
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param string $message
     * @throws BuildException
     */
    protected function runFailed($message)
    {
        throw new BuildException($message);
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof PHPUnit_Framework_TestCase) {
            if (!$test->hasPerformedExpectationsOnOutput()) {
                echo $test->getActualOutput();
            }
        }
    }

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    private function injectFilters(PHPUnit_Framework_TestSuite $suite)
    {
        $filterFactory = new PHPUnit_Runner_Filter_Factory();

        if (!empty($this->excludeGroups)) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Group_Exclude'),
                $this->excludeGroups
            );
        }

        if (!empty($this->groups)) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Group_Include'),
                $this->groups
            );
        }

        $suite->injectFilter($filterFactory);
    }
}
