<?php
/**
 * $Id: bde7020012ba4f24e0d97768c76a5a8af99a6fbb $
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
require_once 'phing/system/util/Properties.php';
require_once 'phing/tasks/ext/coverage/CoverageMerger.php';

/**
 * Initializes a code coverage database
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: bde7020012ba4f24e0d97768c76a5a8af99a6fbb $
 * @package phing.tasks.ext.coverage
 * @since 2.1.0
 */
class CoverageSetupTask extends Task
{
    /** the list of filesets containing the .php filename rules */
    private $filesets = array();

    /** Any filelists of files containing the .php filenames */
    private $filelists = array();

    /** the filename of the coverage database */
    private $database = "coverage.db";

    /** the classpath to use (optional) */
    private $classpath = null;

    /**
     * Add a new fileset containing the .php files to process
     *
     * @param FileSet the new fileset containing .php files
     */
    public function addFileSet(FileSet $fileset)
    {
        $this->filesets[] = $fileset;
    }

    /**
     * Supports embedded <filelist> element.
     * @return FileList
     */
    public function createFileList()
    {
        $num = array_push($this->filelists, new FileList());

        return $this->filelists[$num - 1];
    }

    /**
     * Sets the filename of the coverage database to use
     *
     * @param string the filename of the database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @param Path $classpath
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * @return null|Path
     */
    public function createClasspath()
    {
        $this->classpath = new Path();

        return $this->classpath;
    }

    /**
     * Iterate over all filesets and return the filename of all files.
     *
     * @return array an array of (basedir, filenames) pairs
     */
    private function getFilenames()
    {
        $files = array();

        foreach ($this->filelists as $fl) {
            try {
                $list = $fl->getFiles($this->project);
                foreach ($list as $file) {
                    $fs = new PhingFile(strval($fl->getDir($this->project)), $file);
                    $files[] = array('key' => strtolower($fs->getAbsolutePath()), 'fullname' => $fs->getAbsolutePath());
                }
            } catch (BuildException $be) {
                $this->log($be->getMessage(), Project::MSG_WARN);
            }
        }

        foreach ($this->filesets as $fileset) {
            $ds = $fileset->getDirectoryScanner($this->project);
            $ds->scan();

            $includedFiles = $ds->getIncludedFiles();

            foreach ($includedFiles as $file) {
                $fs = new PhingFile(realpath($ds->getBaseDir()), $file);

                $files[] = array('key' => strtolower($fs->getAbsolutePath()), 'fullname' => $fs->getAbsolutePath());
            }
        }

        return $files;
    }

    public function init()
    {
    }

    public function main()
    {
        $files = $this->getFilenames();

        $this->log("Setting up coverage database for " . count($files) . " files");

        $props = new Properties();

        foreach ($files as $file) {
            $fullname = $file['fullname'];
            $filename = $file['key'];

            $props->setProperty($filename, serialize(array('fullname' => $fullname, 'coverage' => array())));
        }

        $dbfile = new PhingFile($this->database);

        $props->store($dbfile);

        $this->project->setProperty('coverage.database', $dbfile->getAbsolutePath());
    }
}
