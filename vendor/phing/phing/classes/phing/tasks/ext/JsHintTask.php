<?php
/*
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
 * JsHintTask
 *
 * Checks the JavaScript code using JSHint
 * See http://www.jshint.com/
 *
 * @author Martin Hujer <mhujer@gmail.com>
 * @package phing.tasks.ext
 * @version $Id: 6ad9401ea56613ccf4b9c8c135430296245adecc $
 * @since 2.6.2
 */
class JsHintTask extends Task
{

    /**
     * The source file (from xml attribute)
     *
     * @var string
     */
    protected $file;

    /**
     * All fileset objects assigned to this task
     *
     * @var FileSet[]
     */
    protected $filesets = array();

    /**
     * Should the build fail on JSHint errors
     *
     * @var boolean
     */
    private $haltOnError = false;

    /**
     * Should the build fail on JSHint warnings
     *
     * @var boolean
     */
    private $haltOnWarning = false;

    /**
     * reporter
     *
     * @var string
     */
    private $reporter = 'checkstyle';

    /**
     * xmlAttributes
     *
     * @var array
     */
    private $xmlAttributes;

    /**
     * Path where the the report in Checkstyle format should be saved
     *
     * @var string
     */
    private $checkstyleReportPath;

    /**
     * File to be performed syntax check on
     *
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
     * @param $haltOnError
     */
    public function setHaltOnError($haltOnError)
    {
        $this->haltOnError = $haltOnError;
    }

    /**
     * @param $haltOnWarning
     */
    public function setHaltOnWarning($haltOnWarning)
    {
        $this->haltOnWarning = $haltOnWarning;
    }

    /**
     * @param $checkstyleReportPath
     */
    public function setCheckstyleReportPath($checkstyleReportPath)
    {
        $this->checkstyleReportPath = $checkstyleReportPath;
    }

    /**
     * @param $reporter
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;

        switch ($this->reporter) {
            case 'jslint':
                $this->xmlAttributes = array(
                    'severity' => array('error' => 'E', 'warning' => 'W', 'info' => 'I'),
                    'fileError' => 'issue',
                    'line' => 'line',
                    'column' => 'char',
                    'message' => 'reason',
                );
                break;
            default:
                $this->xmlAttributes = array(
                    'severity' => array('error' => 'error', 'warning' => 'warning', 'info' => 'info'),
                    'fileError' => 'error',
                    'line' => 'line',
                    'column' => 'column',
                    'message' => 'message',
                );
                break;
        }
    }

    public function main()
    {
        if (!isset($this->file) && count($this->filesets) === 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (!isset($this->file)) {
            $fileList = array();
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($files as $file) {
                    $fileList[] = $dir . DIRECTORY_SEPARATOR . $file;
                }
            }
        } else {
            $fileList = array($this->file);
        }

        $this->_checkJsHintIsInstalled();

        $fileList = array_map('escapeshellarg', $fileList);
        $command = sprintf('jshint --reporter=%s %s', $this->reporter, implode(' ', $fileList));
        $output = array();
        exec($command, $output);
        $output = implode(PHP_EOL, $output);
        $xml = simplexml_load_string($output);

        $projectBasedir = $this->_getProjectBasedir();
        $errorsCount = 0;
        $warningsCount = 0;
        foreach ($xml->file as $file) {
            $fileAttributes = $file->attributes();
            $fileName = (string) $fileAttributes['name'];
            foreach ($file->error as $error) {
                $errAttr = (array) $error->attributes();
                $attrs = current($errAttr);

                if ($attrs['severity'] === $this->xmlAttributes['severity']['error']) {
                    $errorsCount++;
                } elseif ($attrs['severity'] === $this->xmlAttributes['severity']['warning']) {
                    $warningsCount++;
                } elseif ($attrs['severity'] !== $this->xmlAttributes['severity']['info']) {
                    throw new BuildException(sprintf('Unknown severity "%s"', $attrs['severity']));
                }
                $e = sprintf(
                    '%s: line %d, col %d, %s',
                    str_replace($projectBasedir, '', $fileName),
                    $attrs[$this->xmlAttributes['line']],
                    $attrs[$this->xmlAttributes['column']],
                    $attrs[$this->xmlAttributes['message']]
                );
                $this->log($e);
            }
        }

        $message = sprintf(
            'JSHint detected %d errors and %d warnings.',
            $errorsCount,
            $warningsCount
        );
        if ($this->haltOnError && $errorsCount) {
            throw new BuildException($message);
        } elseif ($this->haltOnWarning && $warningsCount) {
            throw new BuildException($message);
        } else {
            $this->log('');
            $this->log($message);
        }

        if ($this->checkstyleReportPath) {
            file_put_contents($this->checkstyleReportPath, $output);
            $this->log('');
            $this->log('Checkstyle report saved to ' . $this->checkstyleReportPath);
        }
    }

    /**
     * @return Path to the project basedir
     */
    private function _getProjectBasedir()
    {
        return $this->getProject()->getBaseDir()->getAbsolutePath() . DIRECTORY_SEPARATOR;
    }

    /**
     * Checks, wheter the JSHint can be executed
     */
    private function _checkJsHintIsInstalled()
    {
        exec('jshint -v', $output, $return);
        if ($return !== 0) {
            throw new BuildException('JSHint is not installed!');
        }
    }
}
