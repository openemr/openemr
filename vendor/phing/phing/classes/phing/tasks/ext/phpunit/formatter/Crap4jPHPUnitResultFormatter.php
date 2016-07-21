<?php
/**
 * $Id: 213f2cf3a9f85b00a9a962f71513d1d99f6dc72d $
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
 * Prints Clover XML output of the test
 *
 * @author Daniel Kreckel <daniel@kreckel.koeln>
 * @version $Id: 213f2cf3a9f85b00a9a962f71513d1d99f6dc72d $
 * @package phing.tasks.ext.formatter
 * @since 2.1.1
 */
class Crap4jPHPUnitResultFormatter extends PHPUnitResultFormatter
{
    /**
     * @var PHPUnit_Framework_TestResult
     */
    private $result = null;
    /**
     * PHPUnit version
     * @var string
     */
    private $version = null;
    /**
     * @param PHPUnitTask $parentTask
     */
    public function __construct(PHPUnitTask $parentTask)
    {
        parent::__construct($parentTask);
        $this->version = PHPUnit_Runner_Version::id();
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
        return "crap4j-coverage";
    }
	
    /**
     * @param PHPUnit_Framework_TestResult $result
     */
    public function processResult(PHPUnit_Framework_TestResult $result)
    {
        $this->result = $result;
    }
	
    public function endTestRun()
    {
        $coverage = $this->result->getCodeCoverage();
        if (!empty($coverage)) {
            $crap = new PHP_CodeCoverage_Report_Crap4j();
            $contents = $crap->process($coverage);
            if ($this->out) {
                $this->out->write($contents);
                $this->out->close();
            }
        }
        parent::endTestRun();
    }
}
