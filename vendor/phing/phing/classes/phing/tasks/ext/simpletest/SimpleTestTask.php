<?php
/**
 * $Id: d94834e70b1522ae68a116f449d10cf309c2b063 $
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

require_once 'phing/Task.php';
require_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/Writer.php';
require_once 'phing/util/LogWriter.php';

/**
 * Runs SimpleTest tests.
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: d94834e70b1522ae68a116f449d10cf309c2b063 $
 * @package phing.tasks.ext.simpletest
 * @since 2.2.0
 */
class SimpleTestTask extends Task
{
    private $formatters = array();
    private $haltonerror = false;
    private $haltonfailure = false;
    private $failureproperty;
    private $errorproperty;
    private $printsummary = false;
    private $testfailed = false;
    private $debug = false;

    /**
     * Initialize Task.
     * This method includes any necessary SimpleTest libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     */
    public function init()
    {
        @include_once 'simpletest/scorer.php';

        if (!class_exists('SimpleReporter')) {
            throw new BuildException("SimpleTestTask depends on SimpleTest package being installed.", $this->getLocation(
            ));
        }

        require_once 'simpletest/reporter.php';
        require_once 'simpletest/xml.php';
        require_once 'simpletest/test_case.php';
        require_once 'phing/tasks/ext/simpletest/SimpleTestCountResultFormatter.php';
        require_once 'phing/tasks/ext/simpletest/SimpleTestDebugResultFormatter.php';
        require_once 'phing/tasks/ext/simpletest/SimpleTestFormatterElement.php';
    }

    /**
     * @param $value
     */
    public function setFailureproperty($value)
    {
        $this->failureproperty = $value;
    }

    /**
     * @param $value
     */
    public function setErrorproperty($value)
    {
        $this->errorproperty = $value;
    }

    /**
     * @param $value
     */
    public function setHaltonerror($value)
    {
        $this->haltonerror = $value;
    }

    /**
     * @param $value
     */
    public function setHaltonfailure($value)
    {
        $this->haltonfailure = $value;
    }

    /**
     * @param $printsummary
     */
    public function setPrintsummary($printsummary)
    {
        $this->printsummary = $printsummary;
    }

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Add a new formatter to all tests of this task.
     *
     * @param SimpleTestFormatterElement formatter element
     */
    public function addFormatter(SimpleTestFormatterElement $fe)
    {
        $this->formatters[] = $fe;
    }

    /**
     * Add a new fileset containing the XML results to aggregate
     *
     * @param FileSet the new fileset containing XML results.
     */
    public function addFileSet(FileSet $fileset)
    {
        $this->filesets[] = $fileset;
    }

    /**
     * Iterate over all filesets and return the filename of all files
     * that end with .php.
     *
     * @return array an array of filenames
     */
    private function getFilenames()
    {
        $filenames = array();

        foreach ($this->filesets as $fileset) {
            $ds = $fileset->getDirectoryScanner($this->project);
            $ds->scan();

            $files = $ds->getIncludedFiles();

            foreach ($files as $file) {
                if (strstr($file, ".php")) {
                    $filenames[] = $ds->getBaseDir() . "/" . $file;
                }
            }
        }

        return $filenames;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $suite = new TestSuite();

        $filenames = $this->getFilenames();

        foreach ($filenames as $testfile) {
            $suite->addFile($testfile);
        }

        if ($this->debug) {
            $fe = new SimpleTestFormatterElement();
            $fe->setType('debug');
            $fe->setUseFile(false);
            $this->formatters[] = $fe;
        }

        if ($this->printsummary) {
            $fe = new SimpleTestFormatterElement();
            $fe->setType('summary');
            $fe->setUseFile(false);
            $this->formatters[] = $fe;
        }

        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();
            $formatter->setProject($this->getProject());

            if ($fe->getUseFile()) {
                $destFile = new PhingFile($fe->getToDir(), $fe->getOutfile());

                $writer = new FileWriter($destFile->getAbsolutePath());

                $formatter->setOutput($writer);
            } else {
                $formatter->setOutput($this->getDefaultOutput());
            }
        }

        $this->execute($suite);

        if ($this->testfailed && $this->formatters[0]->getFormatter() instanceof SimpleTestDebugResultFormatter) {
            $this->getDefaultOutput()->write("Failed tests: ");
            $this->formatters[0]->getFormatter()->printFailingTests();
        }

        if ($this->testfailed) {
            throw new BuildException("One or more tests failed");
        }
    }

    /**
     * @param $suite
     */
    private function execute($suite)
    {
        $counter = new SimpleTestCountResultFormatter();
        $reporter = new MultipleReporter();
        $reporter->attachReporter($counter);

        foreach ($this->formatters as $fe) {
            // SimpleTest 1.0.1 workaround
            $formatterList[] = $fe->getFormatter();

            $reporter->attachReporter(end($formatterList));
        }

        $suite->run($reporter);

        $retcode = $counter->getRetCode();

        if ($retcode == SimpleTestCountResultFormatter::ERRORS) {
            if ($this->errorproperty) {
                $this->project->setNewProperty($this->errorproperty, true);
            }

            if ($this->haltonerror) {
                $this->testfailed = true;
            }
        } elseif ($retcode == SimpleTestCountResultFormatter::FAILURES) {
            if ($this->failureproperty) {
                $this->project->setNewProperty($this->failureproperty, true);
            }

            if ($this->haltonfailure) {
                $this->testfailed = true;
            }
        }
    }

    /**
     * @return LogWriter
     */
    private function getDefaultOutput()
    {
        return new LogWriter($this);
    }
}
