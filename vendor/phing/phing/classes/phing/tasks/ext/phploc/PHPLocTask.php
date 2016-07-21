<?php
/**
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
require_once 'phing/BuildException.php';
require_once 'phing/tasks/ext/phploc/PHPLocFormatterElement.php';
require_once 'phing/tasks/ext/phploc/PHPLocFormatterFactory.php';

/**
 * Runs phploc a tool for quickly measuring the size of PHP projects.
 *
 * @package phing.tasks.ext.phploc
 * @author  Raphael Stolt <raphael.stolt@gmail.com>
 */
class PHPLocTask extends Task
{
    /**
     * @var array
     */
    protected $suffixesToCheck = array('php');

    /**
     * @var array
     */
    protected $acceptedReportTypes = array('cli', 'txt', 'xml', 'csv');

    /**
     * @var null
     */
    protected $reportDirectory = null;

    /**
     * @var string
     */
    protected $reportType = 'cli';

    /**
     * @var string
     */
    protected $reportFileName = 'phploc-report';

    /**
     * @var bool
     */
    protected $countTests = false;

    /**
     * @var null|PhingFile
     */
    protected $fileToCheck = null;

    /**
     * @var array
     */
    protected $filesToCheck = array();

    /**
     * @var FileSet[]
     */
    protected $fileSets = array();

    /**
     * @var PHPLocFormatterElement[]
     */
    protected $formatterElements = array();

    /**
     * @var string
     */
    private $pharLocation = "";

    /**
     * @param string $suffixListOrSingleSuffix
     */
    public function setSuffixes($suffixListOrSingleSuffix)
    {
        if (stripos($suffixListOrSingleSuffix, ',')) {
            $suffixes = explode(',', $suffixListOrSingleSuffix);
            $this->suffixesToCheck = array_map('trim', $suffixes);
        } else {
            $this->suffixesToCheck[] = trim($suffixListOrSingleSuffix);
        }
    }

    /**
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        $this->fileToCheck = trim($file);
    }

    /**
     * @param boolean $countTests
     */
    public function setCountTests($countTests)
    {
        $this->countTests = StringHelper::booleanValue($countTests);
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->fileSets[] = $fs;
    }

    /**
     * @param string $type
     */
    public function setReportType($type)
    {
        $this->reportType = trim($type);
    }

    /**
     * @param string $name
     */
    public function setReportName($name)
    {
        $this->reportFileName = trim($name);
    }

    /**
     * @param string $directory
     */
    public function setReportDirectory($directory)
    {
        $this->reportDirectory = trim($directory);
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * @param PHPLocFormatterElement $formatterElement
     */
    public function addFormatter(PHPLocFormatterElement $formatterElement)
    {
        $this->formatterElements[] = $formatterElement;
    }

    /**
     * @throws BuildException
     */
    protected function loadDependencies()
    {
        if (!empty($this->pharLocation)) {
            // hack to prevent PHPLOC from starting in CLI mode and halting Phing
            eval("namespace SebastianBergmann\PHPLOC\CLI;
class Application
{
    public function run() {}
}");

            ob_start();
            include $this->pharLocation;
            ob_end_clean();
        }

        if (!class_exists('\SebastianBergmann\PHPLOC\Analyser')) {
            if (!@include_once 'SebastianBergmann/PHPLOC/autoload.php') {
                throw new BuildException(
                    'PHPLocTask depends on PHPLoc being installed and on include_path.',
                    $this->getLocation()
                );
            }
        }
    }

    public function main()
    {
        $this->loadDependencies();

        $this->validateProperties();

        if (count($this->fileSets) > 0) {
            foreach ($this->fileSets as $fileSet) {
                $directoryScanner = $fileSet->getDirectoryScanner($this->project);
                $files = $directoryScanner->getIncludedFiles();
                $directory = $fileSet->getDir($this->project)->getPath();

                foreach ($files as $file) {
                    if ($this->isFileSuffixSet($file)) {
                        $this->filesToCheck[] = $directory . DIRECTORY_SEPARATOR . $file;
                    }
                }
            }

            $this->filesToCheck = array_unique($this->filesToCheck);
        }

        $this->runPhpLocCheck();
    }

    /**
     * @throws BuildException
     */
    private function validateProperties()
    {
        if ($this->fileToCheck === null && count($this->fileSets) === 0) {
            throw new BuildException('Missing either a nested fileset or the attribute "file" set.');
        }

        if ($this->fileToCheck !== null) {
            if (!file_exists($this->fileToCheck)) {
                throw new BuildException("File to check doesn't exist.");
            }

            if (!$this->isFileSuffixSet($this->fileToCheck)) {
                throw new BuildException('Suffix of file to check is not defined in "suffixes" attribute.');
            }

            if (count($this->fileSets) > 0) {
                throw new BuildException('Either use a nested fileset or "file" attribute; not both.');
            }
        }

        if (count($this->suffixesToCheck) === 0) {
            throw new BuildException('No file suffix defined.');
        }

        if (count($this->formatterElements) == 0) {
            if ($this->reportType === null) {
                throw new BuildException('No report type or formatters defined.');
            }

            if ($this->reportType !== null && !in_array($this->reportType, $this->acceptedReportTypes)) {
                throw new BuildException('Unaccepted report type defined.');
            }

            if ($this->reportType !== 'cli' && $this->reportDirectory === null) {
                throw new BuildException('No report output directory defined.');
            }

            if ($this->reportDirectory !== null && !is_dir($this->reportDirectory)) {
                $reportOutputDir = new PhingFile($this->reportDirectory);

                $logMessage = "Report output directory doesn't exist, creating: "
                    . $reportOutputDir->getAbsolutePath() . '.';

                $this->log($logMessage);
                $reportOutputDir->mkdirs();
            }

            if ($this->reportType !== 'cli') {
                $this->reportFileName .= '.' . $this->reportType;
            }

            $formatterElement = new PHPLocFormatterElement();
            $formatterElement->setType($this->reportType);
            $formatterElement->setUseFile($this->reportDirectory !== null);
            $formatterElement->setToDir($this->reportDirectory);
            $formatterElement->setOutfile($this->reportFileName);
            $this->formatterElements[] = $formatterElement;
        }
    }

    /**
     * @param string $filename
     *
     * @return boolean
     */
    protected function isFileSuffixSet($filename)
    {
        return in_array(pathinfo($filename, PATHINFO_EXTENSION), $this->suffixesToCheck);
    }

    protected function runPhpLocCheck()
    {
        $files = $this->getFilesToCheck();
        $count = $this->getCountForFiles($files);

        foreach ($this->formatterElements as $formatterElement) {
            $formatter = PHPLocFormatterFactory::createFormatter($formatterElement);

            if ($formatterElement->getType() != 'cli') {
                $logMessage = 'Writing report to: '
                    . $formatterElement->getToDir() . DIRECTORY_SEPARATOR . $formatterElement->getOutfile();

                $this->log($logMessage);
            }

            $formatter->printResult($count, $this->countTests);
        }
    }

    /**
     * @return SplFileInfo[]
     */
    protected function getFilesToCheck()
    {
        $files = array();

        if (count($this->filesToCheck) > 0) {
            foreach ($this->filesToCheck as $file) {
                $files[] = new SplFileInfo($file);
            }
        } elseif ($this->fileToCheck !== null) {
            $files = array(new SplFileInfo($this->fileToCheck));
        }

        return $files;
    }

    /**
     * @param SplFileInfo[] $files
     *
     * @return array
     */
    protected function getCountForFiles(array $files)
    {
        $analyserClass = '\\SebastianBergmann\\PHPLOC\\Analyser';
        $analyser = new $analyserClass();

        return $analyser->countFiles($files, $this->countTests);
    }
}
