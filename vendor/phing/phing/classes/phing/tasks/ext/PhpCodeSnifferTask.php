<?php
/*
 *  $Id: e635b2512b71e17b616bda5e66b412984c0718b0 $
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

/**
 * A PHP code sniffer task. Checking the style of one or more PHP source files.
 *
 * @author  Dirk Thomas <dirk.thomas@4wdmedia.de>
 * @version $Id: e635b2512b71e17b616bda5e66b412984c0718b0 $
 * @package phing.tasks.ext
 */
class PhpCodeSnifferTask extends Task
{

    /**
     * A php source code filename or directory
     *
     * @var PhingFile
     */
    protected $file; // the source file (from xml attribute)

    /**
     * All fileset objects assigned to this task
     *
     * @var FileSet[]
     */
    protected $filesets = array(); // all fileset objects assigned to this task

    // parameters for php code sniffer
    protected $standards = array('Generic');
    protected $sniffs = array();
    protected $showWarnings = true;
    protected $showSources = false;
    protected $reportWidth = 80;
    protected $verbosity = 0;
    protected $tabWidth = 0;
    protected $allowedFileExtensions = array('php', 'inc', 'js', 'css');
    protected $allowedTypes = array();
    protected $ignorePatterns = false;
    protected $noSubdirectories = false;
    protected $configData = array();
    protected $encoding = 'iso-8859-1';

    // parameters to customize output
    protected $showSniffs = false;
    protected $format = 'full';
    protected $formatters = array();

    /**
     * Holds the type of the doc generator
     *
     * @var string
     */
    protected $docGenerator = '';

    /**
     * Holds the outfile for the documentation
     *
     * @var PhingFile
     */
    protected $docFile = null;

    private $haltonerror = false;
    private $haltonwarning = false;
    private $skipversioncheck = false;
    private $propertyName = null;

    /**
     * Cache data storage
     * @var DataStore
     */
    protected $cache;

    /**
     * Load the necessary environment for running PHP_CodeSniffer.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * File to be performed syntax check on
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Sets the coding standard to test for
     *
     * @param string $standards The coding standards
     *
     * @return void
     */
    public function setStandard($standards)
    {
        $this->standards = array();
        $token = ' ,;';
        $ext = strtok($standards, $token);
        while ($ext !== false) {
            $this->standards[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets the sniffs which the standard should be restricted to
     * @param string $sniffs
     */
    public function setSniffs($sniffs)
    {
        $token = ' ,;';
        $sniff = strtok($sniffs, $token);
        while ($sniff !== false) {
            $this->sniffs[] = $sniff;
            $sniff = strtok($token);
        }
    }

    /**
     * Sets the type of the doc generator
     *
     * @param string $generator HTML or Text
     *
     * @return void
     */
    public function setDocGenerator($generator)
    {
        $this->docGenerator = $generator;
    }

    /**
     * Sets the outfile for the documentation
     *
     * @param PhingFile $file The outfile for the doc
     *
     * @return void
     */
    public function setDocFile(PhingFile $file)
    {
        $this->docFile = $file;
    }

    /**
     * Sets the flag if warnings should be shown
     * @param boolean $show
     */
    public function setShowWarnings($show)
    {
        $this->showWarnings = StringHelper::booleanValue($show);
    }

    /**
     * Sets the flag if sources should be shown
     *
     * @param boolean $show Whether to show sources or not
     *
     * @return void
     */
    public function setShowSources($show)
    {
        $this->showSources = StringHelper::booleanValue($show);
    }

    /**
     * Sets the width of the report
     *
     * @param int $width How wide the screen reports should be.
     *
     * @return void
     */
    public function setReportWidth($width)
    {
        $this->reportWidth = (int) $width;
    }

    /**
     * Sets the verbosity level
     * @param int $level
     */
    public function setVerbosity($level)
    {
        $this->verbosity = (int) $level;
    }

    /**
     * Sets the tab width to replace tabs with spaces
     * @param int $width
     */
    public function setTabWidth($width)
    {
        $this->tabWidth = (int) $width;
    }

    /**
     * Sets file encoding
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Sets the allowed file extensions when using directories instead of specific files
     * @param array $extensions
     */
    public function setAllowedFileExtensions($extensions)
    {
        $this->allowedFileExtensions = array();
        $token = ' ,;';
        $ext = strtok($extensions, $token);
        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets the allowed types for the PHP_CodeSniffer::suggestType()
     * @param array $types
     */
    public function setAllowedTypes($types)
    {
        $this->allowedTypes = array();
        $token = ' ,;';
        $type = strtok($types, $token);
        while ($type !== false) {
            $this->allowedTypes[] = $type;
            $type = strtok($token);
        }
    }

    /**
     * Sets the ignore patterns to skip files when using directories instead of specific files
     * @param $patterns
     * @internal param array $extensions
     */
    public function setIgnorePatterns($patterns)
    {
        $this->ignorePatterns = array();
        $token = ' ,;';
        $pattern = strtok($patterns, $token);
        while ($pattern !== false) {
            $this->ignorePatterns[$pattern] = 'relative';
            $pattern = strtok($token);
        }
    }

    /**
     * Sets the flag if subdirectories should be skipped
     * @param boolean $subdirectories
     */
    public function setNoSubdirectories($subdirectories)
    {
        $this->noSubdirectories = StringHelper::booleanValue($subdirectories);
    }

    /**
     * Creates a config parameter for this task
     *
     * @return Parameter The created parameter
     */
    public function createConfig()
    {
        $num = array_push($this->configData, new Parameter());

        return $this->configData[$num - 1];
    }

    /**
     * Sets the flag if the used sniffs should be listed
     * @param boolean $show
     */
    public function setShowSniffs($show)
    {
        $this->showSniffs = StringHelper::booleanValue($show);
    }

    /**
     * Sets the output format
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Create object for nested formatter element.
     * @return CodeSniffer_FormatterElement
     */
    public function createFormatter()
    {
        $num = array_push(
            $this->formatters,
            new PhpCodeSnifferTask_FormatterElement()
        );

        return $this->formatters[$num - 1];
    }

    /**
     * Sets the haltonerror flag
     * @param boolean $value
     */
    public function setHaltonerror($value)
    {
        $this->haltonerror = $value;
    }

    /**
     * Sets the haltonwarning flag
     * @param boolean $value
     */
    public function setHaltonwarning($value)
    {
        $this->haltonwarning = $value;
    }

    /**
     * Sets the skipversioncheck flag
     * @param boolean $value
     */
    public function setSkipVersionCheck($value)
    {
        $this->skipversioncheck = $value;
    }

    /**
     * Sets the name of the property to use
     * @param $propertyName
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Whether to store last-modified times in cache
     *
     * @param PhingFile $file
     */
    public function setCacheFile(PhingFile $file)
    {
        $this->cache = new DataStore($file);
    }

    /**
     * Return the list of files to parse
     *
     * @return string[] list of absolute files to parse
     */
    protected function getFilesToParse()
    {
        $filesToParse = array();

        if ($this->file instanceof PhingFile) {
            $filesToParse[] = $this->file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($fs->getDirectoryScanner($this->project)->getIncludedFiles() as $filename) {
                    $fileAbsolutePath = $dir . DIRECTORY_SEPARATOR . $filename;
                    if ($this->cache) {
                        $lastMTime = $this->cache->get($fileAbsolutePath);
                        $currentMTime = filemtime($fileAbsolutePath);
                        if ($lastMTime >= $currentMTime) {
                            continue;
                        } else {
                            $this->cache->put($fileAbsolutePath, $currentMTime);
                        }
                    }
                    $filesToParse[] = $fileAbsolutePath;
                }
            }
        }
        return $filesToParse;
    }

    /**
     * Executes PHP code sniffer against PhingFile or a FileSet
     */
    public function main()
    {
        if (!class_exists('PHP_CodeSniffer')) {
            @include_once 'PHP/CodeSniffer.php';

            if (!class_exists('PHP_CodeSniffer')) {
                throw new BuildException("This task requires the PHP_CodeSniffer package installed and available on the include path", $this->getLocation(
                ));
            }
        }

        /**
         * Determine PHP_CodeSniffer version number
         */
        if (!$this->skipversioncheck) {
            if (defined('PHP_CodeSniffer::VERSION')) {
                preg_match('/\d\.\d\.\d/', PHP_CodeSniffer::VERSION, $version);
            } else {
                preg_match('/\d\.\d\.\d/', shell_exec('phpcs --version'), $version);
            }

            if (version_compare($version[0], '1.2.2') < 0) {
                throw new BuildException(
                    'PhpCodeSnifferTask requires PHP_CodeSniffer version >= 1.2.2',
                    $this->getLocation()
                );
            }
        }

        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (count($this->formatters) == 0) {
            // turn legacy format attribute into formatter
            $fmt = new PhpCodeSnifferTask_FormatterElement();
            $fmt->setType($this->format);
            $fmt->setUseFile(false);
            $this->formatters[] = $fmt;
        }

        $fileList = $this->getFilesToParse();

        $cwd = getcwd();

        // Save command line arguments because it confuses PHPCS (version 1.3.0)
        $oldArgs = $_SERVER['argv'];
        $_SERVER['argv'] = array();
        $_SERVER['argc'] = 0;

        include_once 'phing/tasks/ext/phpcs/PhpCodeSnifferTask_Wrapper.php';

        $codeSniffer = new PhpCodeSnifferTask_Wrapper($this->verbosity, $this->tabWidth, $this->encoding);
        $codeSniffer->setAllowedFileExtensions($this->allowedFileExtensions);
        if ($this->allowedTypes) {
            PhpCodeSnifferTask_Wrapper::$allowedTypes = $this->allowedTypes;
        }
        if (is_array($this->ignorePatterns)) {
            $codeSniffer->setIgnorePatterns($this->ignorePatterns);
        }
        foreach ($this->configData as $configData) {
            $codeSniffer->setConfigData($configData->getName(), $configData->getValue(), true);
        }

        /*
         * Verifying if standard is installed only after setting config data.
         * Custom standard paths could be provided via installed_paths config parameter.
         */
        foreach($this->standards as $standard) {
            if (PHP_CodeSniffer::isInstalledStandard($standard) === false) {
                // They didn't select a valid coding standard, so help them
                // out by letting them know which standards are installed.
                $installedStandards = PHP_CodeSniffer::getInstalledStandards();
                $numStandards = count($installedStandards);
                $errMsg = '';

                if ($numStandards === 0) {
                    $errMsg = 'No coding standards are installed.';
                } else {
                    $lastStandard = array_pop($installedStandards);

                    if ($numStandards === 1) {
                        $errMsg = 'The only coding standard installed is ' . $lastStandard;
                    } else {
                        $standardList = implode(', ', $installedStandards);
                        $standardList .= ' and ' . $lastStandard;
                        $errMsg = 'The installed coding standards are ' . $standardList;
                    }
                }

                throw new BuildException(
                    'ERROR: the "' . $standard . '" coding standard is not installed. ' . $errMsg,
                    $this->getLocation()
                );
            }
        }

        if (!$this->showWarnings) {
            $codeSniffer->cli->warningSeverity = 0;
        }

        // nasty integration hack
        $values = $codeSniffer->cli->getDefaults();
        $_SERVER['argv'] = array('t');
        $_SERVER['argc'] = 1;
        foreach ($this->formatters as $fe) {
            $output = ($fe->getUseFile() ? $fe->getOutFile() : null);
            $_SERVER['argv'][] = '--report-' . $fe->getType() . '=' . $output;
            $_SERVER['argc']++;
        }

        if ($this->cache) {
            require_once 'phing/tasks/ext/phpcs/Reports_PhingRemoveFromCache.php';
            PHP_CodeSniffer_Reports_PhingRemoveFromCache::setCache($this->cache);
            // add a fake report to remove from cache
            $_SERVER['argv'][] = '--report-phingRemoveFromCache=';
            $_SERVER['argc']++;
        }

        $codeSniffer->process($fileList, $this->standards, $this->sniffs, $this->noSubdirectories);
        $_SERVER['argv'] = array();
        $_SERVER['argc'] = 0;

        if ($this->cache) {
            PHP_CodeSniffer_Reports_PhingRemoveFromCache::setCache(null);
            $this->cache->commit();
        }

        $this->printErrorReport($codeSniffer);

        // generate the documentation
        if ($this->docGenerator !== '' && $this->docFile !== null) {
            ob_start();

            $codeSniffer->generateDocs($this->standards, $this->sniffs, $this->docGenerator);

            $output = ob_get_contents();
            ob_end_clean();

            // write to file
            $outputFile = $this->docFile->getPath();
            $check = file_put_contents($outputFile, $output);

            if (is_bool($check) && !$check) {
                throw new BuildException('Error writing doc to ' . $outputFile);
            }
        } elseif ($this->docGenerator !== '' && $this->docFile === null) {
            $codeSniffer->generateDocs($this->standards, $this->sniffs, $this->docGenerator);
        }

        if ($this->haltonerror && $codeSniffer->reporting->totalErrors > 0) {
            throw new BuildException('phpcodesniffer detected ' . $codeSniffer->reporting->totalErrors . ' error' . ($codeSniffer->reporting->totalErrors > 1 ? 's' : ''));
        }

        if ($this->haltonwarning && $codeSniffer->reporting->totalWarnings > 0) {
            throw new BuildException('phpcodesniffer detected ' . $codeSniffer->reporting->totalWarnings . ' warning' . ($codeSniffer->reporting->totalWarnings > 1 ? 's' : ''));
        }

        $_SERVER['argv'] = $oldArgs;
        $_SERVER['argc'] = count($oldArgs);
        chdir($cwd);
    }

    /**
     * Prints the error report.
     *
     * @param PHP_CodeSniffer $phpcs The PHP_CodeSniffer object containing
     *                               the errors.
     */
    protected function printErrorReport($phpcs)
    {
        $sniffs = $phpcs->getSniffs();
        $sniffStr = '';
        foreach ($sniffs as $sniff) {
            if (is_string($sniff)) {
                $sniffStr .= '- ' . $sniff . PHP_EOL;
            } else {
                $sniffStr .= '- ' . get_class($sniff) . PHP_EOL;
            }
        }
        $this->project->setProperty($this->getPropertyName(), (string) $sniffStr);

        if ($this->showSniffs) {
            $this->log('The list of used sniffs (#' . count($sniffs) . '): ' . PHP_EOL . $sniffStr, Project::MSG_INFO);
        }

        // process output
        $reporting = $phpcs->reporting;
        foreach ($this->formatters as $fe) {
            $reportFile = null;

            if ($fe->getUseFile()) {
                $reportFile = $fe->getOutfile();
                //ob_start();
            }

            // Crude check, but they broke backwards compatibility
            // with a minor version release.
            if (PHP_CodeSniffer::VERSION >= '2.2.0') {
                $cliValues = array('colors' => false);
                $reporting->printReport($fe->getType(),
                                        $this->showSources,
                                        $cliValues,
                                        $reportFile,
                                        $this->reportWidth);
            } else {
                $reporting->printReport($fe->getType(),
                                        $this->showSources,
                                        $reportFile,
                                        $this->reportWidth);
            }

            // reporting class uses ob_end_flush(), but we don't want
            // an output if we use a file
            if ($fe->getUseFile()) {
                //ob_end_clean();
            }
        }
    }

    /**
     * Outputs the results with a custom format
     *
     * @param array $report Packaged list of all errors in each file
     */
    protected function outputCustomFormat($report)
    {
        $files = $report['files'];
        foreach ($files as $file => $attributes) {
            $errors = $attributes['errors'];
            $warnings = $attributes['warnings'];
            $messages = $attributes['messages'];
            if ($errors > 0) {
                $this->log(
                    $file . ': ' . $errors . ' error' . ($errors > 1 ? 's' : '') . ' detected',
                    Project::MSG_ERR
                );
                $this->outputCustomFormatMessages($messages, 'ERROR');
            } else {
                $this->log($file . ': No syntax errors detected', Project::MSG_VERBOSE);
            }
            if ($warnings > 0) {
                $this->log(
                    $file . ': ' . $warnings . ' warning' . ($warnings > 1 ? 's' : '') . ' detected',
                    Project::MSG_WARN
                );
                $this->outputCustomFormatMessages($messages, 'WARNING');
            }
        }

        $totalErrors = $report['totals']['errors'];
        $totalWarnings = $report['totals']['warnings'];
        $this->log(count($files) . ' files were checked', Project::MSG_INFO);
        if ($totalErrors > 0) {
            $this->log($totalErrors . ' error' . ($totalErrors > 1 ? 's' : '') . ' detected', Project::MSG_ERR);
        } else {
            $this->log('No syntax errors detected', Project::MSG_INFO);
        }
        if ($totalWarnings > 0) {
            $this->log($totalWarnings . ' warning' . ($totalWarnings > 1 ? 's' : '') . ' detected', Project::MSG_INFO);
        }
    }

    /**
     * Outputs the messages of a specific type for one file
     * @param array  $messages
     * @param string $type
     */
    protected function outputCustomFormatMessages($messages, $type)
    {
        foreach ($messages as $line => $messagesPerLine) {
            foreach ($messagesPerLine as $column => $messagesPerColumn) {
                foreach ($messagesPerColumn as $message) {
                    $msgType = $message['type'];
                    if ($type == $msgType) {
                        $logLevel = Project::MSG_INFO;
                        if ($msgType == 'ERROR') {
                            $logLevel = Project::MSG_ERR;
                        } else {
                            if ($msgType == 'WARNING') {
                                $logLevel = Project::MSG_WARN;
                            }
                        }
                        $text = $message['message'];
                        $string = $msgType . ' in line ' . $line . ' column ' . $column . ': ' . $text;
                        $this->log($string, $logLevel);
                    }
                }
            }
        }
    }

} //end phpCodeSnifferTask

/**
 * @package phing.tasks.ext
 */
class PhpCodeSnifferTask_FormatterElement extends DataType
{

    /**
     * Type of output to generate
     * @var string
     */
    protected $type = "";

    /**
     * Output to file?
     * @var bool
     */
    protected $useFile = true;

    /**
     * Output file.
     * @var string
     */
    protected $outfile = "";

    /**
     * Validate config.
     */
    public function parsingComplete()
    {
        if (empty($this->type)) {
            throw new BuildException("Format missing required 'type' attribute.");
        }
        if ($this->useFile && empty($this->outfile)) {
            throw new BuildException("Format requires 'outfile' attribute when 'useFile' is true.");
        }

    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = $useFile;
    }

    /**
     * @return bool
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * @param $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * @return string
     */
    public function getOutfile()
    {
        return $this->outfile;
    }

} //end FormatterElement
