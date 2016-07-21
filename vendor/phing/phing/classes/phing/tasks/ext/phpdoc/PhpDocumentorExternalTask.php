<?php

/**
 * $Id: b72762694ae5d5619ecebf9b66c08bd6ff868821 $
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

require_once 'phing/tasks/ext/phpdoc/PhpDocumentorTask.php';

/**
 * Task to run phpDocumentor with an external process
 *
 * This classes uses the commandline phpdoc script to build documentation.
 * Use this task instead of the PhpDocumentorTask when you've a clash with the
 * Smarty libraries.
 *
 * @author Michiel Rook <mrook@php.net>
 * @author Markus Fischer <markus@fischer.name>
 * @version $Id: b72762694ae5d5619ecebf9b66c08bd6ff868821 $
 * @package phing.tasks.ext.phpdoc
 */
class PhpDocumentorExternalTask extends PhpDocumentorTask
{
    /**
     * The path to the executable for phpDocumentor
     */
    protected $programPath = 'phpdoc';

    protected $sourcepath = null;

    /**
     * @var bool  ignore symlinks to other files or directories
     */
    protected $ignoresymlinks = false;

    /**
     * Sets the path to the phpDocumentor executable
     * @param $programPath
     */
    public function setProgramPath($programPath)
    {
        $this->programPath = $programPath;
    }

    /**
     * Returns the path to the phpDocumentor executable
     */
    public function getProgramPath()
    {
        return $this->programPath;
    }

    /**
     * Set the source path. A directory or a comma separate list of directories.
     * @param $sourcepath
     */
    public function setSourcepath($sourcepath)
    {
        $this->sourcepath = $sourcepath;
    }

    /**
     * Ignore symlinks to other files or directories.
     *
     * @param bool $bSet
     */
    public function setIgnoresymlinks($bSet)
    {
        $this->ignoresymlinks = $bSet;
    }

    /**
     * Main entrypoint of the task
     */
    public function main()
    {
        $this->validate();
        $arguments = join(' ', $this->constructArguments());

        $this->log("Running phpDocumentor...");

        exec($this->programPath . " " . $arguments, $output, $return);

        if ($return != 0) {
            throw new BuildException("Could not execute phpDocumentor: " . implode(' ', $output));
        }

        foreach ($output as $line) {
            if (strpos($line, 'ERROR') !== false) {
                $this->log($line, Project::MSG_ERR);
                continue;
            }

            $this->log($line, Project::MSG_VERBOSE);
        }
    }

    /**
     * Constructs an argument string for phpDocumentor
     * @return array
     */
    protected function constructArguments()
    {
        $aArgs = array();
        if ($this->title) {
            $aArgs[] = '--title "' . $this->title . '"';
        }

        if ($this->destdir) {
            $aArgs[] = '--target "' . $this->destdir->getAbsolutePath() . '"';
        }

        if ($this->sourcepath) {
            $aArgs[] = '--directory "' . $this->sourcepath . '"';
        }

        if ($this->output) {
            $aArgs[] = '--output ' . $this->output;
        }

        if ($this->linksource) {
            $aArgs[] = '--sourcecode on';
        }

        if ($this->parseprivate) {
            $aArgs[] = '--parseprivate on';
        }

        if ($this->ignore) {
            $aArgs[] = '--ignore ' . $this->ignore;
        }

        // append any files in filesets
        $filesToParse = array();
        foreach ($this->filesets as $fs) {
            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
            foreach ($files as $filename) {
                $f = new PhingFile($fs->getDir($this->project), $filename);
                $filesToParse[] = $f->getAbsolutePath();
            }
        }
        if (count($filesToParse) > 0) {
            $aArgs[] = '--filename "' . join(',', $filesToParse) . '"';
        }

        // append any files in filesets
        $ricFiles = array();
        foreach ($this->projDocFilesets as $fs) {
            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
            foreach ($files as $filename) {
                $f = new PhingFile($fs->getDir($this->project), $filename);
                $ricFiles[] = $f->getAbsolutePath();
            }
        }
        if (count($ricFiles) > 0) {
            $aArgs[] = '--readmeinstallchangelog "' .
                join(',', $ricFiles) . '"';
        }

        if ($this->javadocDesc) {
            $aArgs[] = '--javadocdesc on';
        }

        if ($this->quiet) {
            $aArgs[] = '--quiet on';
        }

        if ($this->packages) {
            $aArgs[] = '--packageoutput "' . $this->packages . '"';
        }

        if ($this->ignoreTags) {
            $aArgs[] = '--ignore-tags "' . $this->ignoreTags . '"';
        }

        if ($this->defaultCategoryName) {
            $aArgs[] = '--defaultcategoryname "' . $this->defaultCategoryName .
                '"';
        }

        if ($this->examplesDir) {
            $aArgs[] = '--examplesdir "' . $this->examplesDir->getAbsolutePath()
                . '"';
        }

        if ($this->templateBase) {
            $aArgs[] = '--templatebase "' . $this->templateBase->getAbsolutePath()
                . '"';
        }

        if ($this->pear) {
            $aArgs[] = '--pear on';
        }

        if ($this->undocumentedelements) {
            $aArgs[] = '--undocumentedelements on';
        }

        if ($this->customtags) {
            $aArgs[] = '--customtags "' . $this->customtags . '"';
        }

        if ($this->ignoresymlinks) {
            $aArgs[] = '--ignoresymlinks on';
        }

        return $aArgs;
    }

    /**
     * Override PhpDocumentorTask::init() because they're specific to the phpdoc
     * API which we don't use.
     */
    public function init()
    {
    }

    /**
     * Validates that necessary minimum options have been set. Based on
     * PhpDocumentorTask::validate().
     */
    protected function validate()
    {
        if (!$this->destdir) {
            throw new BuildException("You must specify a destdir for phpdoc.",
                $this->getLocation());
        }
        if (!$this->output) {
            throw new BuildException("You must specify an output format for " .
                "phpdoc (e.g. HTML:frames:default).", $this->getLocation());
        }
        if (empty($this->filesets) && !$this->sourcepath) {
            throw new BuildException("You have not specified any files to " .
                "include (<fileset> or sourcepath attribute) for phpdoc.",
                $this->getLocation());
        }
        if ($this->configdir) {
            $this->log(
                'Ignoring unsupported configdir-Attribute',
                Project::MSG_VERBOSE
            );
        }
    }
}

;
