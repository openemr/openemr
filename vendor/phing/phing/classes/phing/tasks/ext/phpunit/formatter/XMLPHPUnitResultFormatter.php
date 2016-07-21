<?php
/**
 * $Id: 8f7dc1ae4c1d6ac3a4d531968cb58034ad9bc754 $
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

require_once 'phing/tasks/ext/phpunit/formatter/PHPUnitResultFormatter.php';

/**
 * Prints XML output of the test to a specified Writer
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 8f7dc1ae4c1d6ac3a4d531968cb58034ad9bc754 $
 * @package phing.tasks.ext.formatter
 * @since 2.1.0
 */
class XMLPHPUnitResultFormatter extends PHPUnitResultFormatter
{
    /**
     * @var PHPUnit_Util_Log_JUnit
     */
    private $logger = null;

    /**
     * @param PHPUnitTask $parentTask
     */
    public function __construct(PHPUnitTask $parentTask)
    {
        parent::__construct($parentTask);

        $logIncompleteSkipped = $parentTask->getHaltonincomplete() || $parentTask->getHaltonskipped();

        $this->logger = new PHPUnit_Util_Log_JUnit(null, $logIncompleteSkipped);
        $this->logger->setWriteDocument(false);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return ".xml";
    }

    /**
     * @return string
     */
    public function getPreferredOutfile()
    {
        return "testsuites";
    }

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::startTestSuite($suite);

        $this->logger->startTestSuite($suite);
    }

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::endTestSuite($suite);

        $this->logger->endTestSuite($suite);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        parent::startTest($test);

        $this->logger->startTest($test);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        $this->logger->endTest($test, $time);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        parent::addError($test, $e, $time);

        $this->logger->addError($test, $e, $time);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        parent::addFailure($test, $e, $time);

        $this->logger->addFailure($test, $e, $time);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        parent::addIncompleteTest($test, $e, $time);

        $this->logger->addIncompleteTest($test, $e, $time);
    }

    public function endTestRun()
    {
        parent::endTestRun();

        if ($this->out) {
            $this->out->write($this->logger->getXML());
            $this->out->close();
        }
    }
}
