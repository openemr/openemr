<?php
/*
 *  $Id: d9b238ee17a88afe34f9db53a4c944c92ac27278 $
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
 * A XML lint task. Checking syntax of one or more XML files against an XML Schema using the DOM extension.
 *
 * @author   Knut Urdalen <knut.urdalen@telio.no>
 * @version  $Id: d9b238ee17a88afe34f9db53a4c944c92ac27278 $
 * @package  phing.tasks.ext
 */
class XmlLintTask extends Task
{

    protected $file; // the source file (from xml attribute)
    protected $schema; // the schema file (from xml attribute)
    protected $filesets = array(); // all fileset objects assigned to this task
    protected $useRNG = false;

    protected $haltonfailure = true;

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
     * XML Schema Description file to validate against
     *
     * @param PhingFile $schema
     */
    public function setSchema(PhingFile $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Use RNG instead of DTD schema validation
     *
     * @param bool $bool
     */
    public function setUseRNG($bool)
    {
        $this->useRNG = (boolean) $bool;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     *
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Sets the haltonfailure attribute
     *
     * @param bool $haltonfailure
     *
     * @return void
     */
    public function setHaltonfailure($haltonfailure)
    {
        $this->haltonfailure = (bool) $haltonfailure;
    }

    /**
     * Execute lint check against PhingFile or a FileSet.
     *
     * {@inheritdoc}
     *
     * @throws BuildException
     *
     * @return void
     */
    public function main()
    {
        if (isset($this->schema) && !file_exists($this->schema->getPath())) {
            throw new BuildException("Schema file not found: " . $this->schema->getPath());
        }
        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        set_error_handler(array($this, 'errorHandler'));
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
        restore_error_handler();
    }

    /**
     * @param $message
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function logError($message)
    {
        if ($this->haltonfailure) {
            throw new BuildException($message);
        } else {
            $this->log($message, Project::MSG_ERR);
        }
    }

    /**
     * Performs validation
     *
     * @param  string $file
     *
     * @return void
     */
    protected function lint($file)
    {
        if (file_exists($file)) {
            if (is_readable($file)) {
                $dom = new DOMDocument();
                if ($dom->load($file) === false) {
                    $error = libxml_get_last_error();
                    $this->logError($file . ' is not well-formed (See messages above)');
                } else {
                    if (isset($this->schema)) {
                        if ($this->useRNG) {
                            if ($dom->relaxNGValidate($this->schema->getPath())) {
                                $this->log($file . ' validated with RNG grammar', Project::MSG_INFO);
                            } else {
                                $this->logError($file . ' fails to validate (See messages above)');
                            }
                        } else {
                            if ($dom->schemaValidate($this->schema->getPath())) {
                                $this->log($file . ' validated with schema', Project::MSG_INFO);
                            } else {
                                $this->logError($file . ' fails to validate (See messages above)');
                            }
                        }
                    } else {
                        $this->log(
                            $file . ' is well-formed (not validated due to missing schema specification)',
                            Project::MSG_INFO
                        );
                    }
                }
            } else {
                $this->logError('Permission denied to read file: ' . $file);
            }
        } else {
            $this->logError('File not found: ' . $file);
        }
    }

    /**
     * Local error handler to catch validation errors and log them through Phing
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param mixed $context
     *
     * @return void
     */
    public function errorHandler($level, $message, $file, $line, $context)
    {
        $matches = array();
        preg_match('/^.*\(\): (.*)$/', $message, $matches);
        $this->log($matches[1], Project::MSG_ERR);
    }
}
