<?php
/*
 *  $Id: 6352a87c4c3b940a0afd55b21645206a23960e0b $
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
require_once 'phing/util/DataStore.php';
require_once 'phing/system/io/FileWriter.php';

/**
 * A PHP lint task. Checking syntax of one or more PHP source file.
 *
 * @author   Knut Urdalen <knut.urdalen@telio.no>
 * @author   Stefan Priebsch <stefan.priebsch@e-novative.de>
 * @version  $Id: 6352a87c4c3b940a0afd55b21645206a23960e0b $
 * @package  phing.tasks.ext
 */
class PhpLintTask extends Task
{

    protected $file; // the source file (from xml attribute)
    protected $filesets = array(); // all fileset objects assigned to this task

    protected $errorProperty;
    protected $haltOnFailure = false;
    protected $hasErrors = false;
    protected $badFiles = array();
    protected $interpreter = ''; // php interpreter to use for linting

    protected $logLevel = Project::MSG_VERBOSE;

    protected $cache = null;

    protected $tofile = null;

    protected $deprecatedAsError = false;

    /**
     * Initialize the interpreter with the Phing property php.interpreter
     */
    public function init()
    {
        $this->setInterpreter($this->project->getProperty('php.interpreter'));
    }

    /**
     * Override default php interpreter
     * @todo    Do some sort of checking if the path is correct but would
     *          require traversing the systems executeable path too
     * @param string $sPhp
     */
    public function setInterpreter($sPhp)
    {
        if (strpos($sPhp, ' ') !== false) {
            $sPhp = escapeshellarg($sPhp);
        }
        $this->interpreter = $sPhp;
    }

    /**
     * The haltonfailure property
     * @param boolean $aValue
     */
    public function setHaltOnFailure($aValue)
    {
        $this->haltOnFailure = $aValue;
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
     * Set an property name in which to put any errors.
     * @param string $propname
     */
    public function setErrorproperty($propname)
    {
        $this->errorProperty = $propname;
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
     * File to save error messages to
     *
     * @param PhingFile $tofile
     * @internal param PhingFile $file
     */
    public function setToFile(PhingFile $tofile)
    {
        $this->tofile = $tofile;
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
     * Set level of log messages generated (default = info)
     * @param string $level
     */
    public function setLevel($level)
    {
        switch ($level) {
            case "error":
                $this->logLevel = Project::MSG_ERR;
                break;
            case "warning":
                $this->logLevel = Project::MSG_WARN;
                break;
            case "info":
                $this->logLevel = Project::MSG_INFO;
                break;
            case "verbose":
                $this->logLevel = Project::MSG_VERBOSE;
                break;
            case "debug":
                $this->logLevel = Project::MSG_DEBUG;
                break;
        }
    }

    /**
     * Sets whether to treat deprecated warnings (introduced in PHP 5.3) as errors
     * @param boolean $deprecatedAsError
     */
    public function setDeprecatedAsError($deprecatedAsError)
    {
        $this->deprecatedAsError = $deprecatedAsError;
    }

    /**
     * Execute lint check against PhingFile or a FileSet
     */
    public function main()
    {
        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if ($this->file instanceof PhingFile) {
            $this->lint($this->file->getPath());
        } else { // process filesets
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getPath();
                foreach ($files as $file) {
                    $this->lint($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        // write list of 'bad files' to file (if specified)
        if ($this->tofile) {
            $writer = new FileWriter($this->tofile);

            foreach ($this->badFiles as $file => $messages) {
                foreach ($messages as $msg) {
                    $writer->write($file . "=" . $msg . PHP_EOL);
                }
            }

            $writer->close();
        }

        $message = '';
        foreach ($this->badFiles as $file => $messages) {
            foreach ($messages as $msg) {
                $message .= $file . "=" . $msg . PHP_EOL;
            }
        }

        // save list of 'bad files' with errors to property errorproperty (if specified)
        if ($this->errorProperty) {
            $this->project->setProperty($this->errorProperty, $message);
        }

        if (!empty($this->cache)) {
            $this->cache->commit();
        }

        if ($this->haltOnFailure && $this->hasErrors) {
            throw new BuildException('Syntax error(s) in PHP files: ' . $message);
        }
    }

    /**
     * Performs the actual syntax check
     *
     * @param  string $file
     * @throws BuildException
     * @return void
     */
    protected function lint($file)
    {
        $command = $this->interpreter == ''
            ? 'php'
            : $this->interpreter;

        if (strpos($command, 'hhvm') !== false) {
            $command .= ' --no-config -l';
        } else {
            if ($this->deprecatedAsError) {
                $command .= ' -d error_reporting=32767 ';
            }

            $command .= ' -n -l ';
        }

        if (! file_exists($file)) {
            throw new BuildException('File not found: ' . $file);
        }

        if (! is_readable($file)) {
            throw new BuildException('Permission denied: ' . $file);
        }

        if ($this->cache) {
            $lastmtime = $this->cache->get($file);

            if ($lastmtime >= filemtime($file)) {
                $this->log("Not linting '" . $file . "' due to cache", Project::MSG_DEBUG);

                return false;
            }
        }

        $messages = array();
        $errorCount = 0;

        exec($command . '"' . $file . '" 2>&1', $messages);

        for ($i = 0; $i < count($messages); $i++) {
            $message = $messages[$i];
            if (trim($message) == '') {
                continue;
            }

            if ((!preg_match('/^(.*)Deprecated:/', $message) || $this->deprecatedAsError) && !preg_match(
                    '/^No syntax errors detected/',
                    $message
                )
            ) {
                $this->log($message, Project::MSG_ERR);

                if (!isset($this->badFiles[$file])) {
                    $this->badFiles[$file] = array();
                }

                array_push($this->badFiles[$file], $message);

                $this->hasErrors = true;
                $errorCount++;
            }
        }

        if (!$errorCount) {
            $this->log($file . ': No syntax errors detected', $this->logLevel);

            if ($this->cache) {
                $this->cache->put($file, filemtime($file));
            }
        }
    }
}
