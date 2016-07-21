<?php
/*
 *  $Id: 5895883ba4b192c8d78e1a1b71622e899b046e08 $
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
include_once 'phing/util/DirectoryScanner.php';
include_once 'phing/types/FileSet.php';
include_once 'phing/util/FileUtils.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/IOException.php';

/**
 * Touch a file and/or fileset(s); corresponds to the Unix touch command.
 *
 * If the file to touch doesn't exist, an empty one is created.
 *
 * @version $Id: 5895883ba4b192c8d78e1a1b71622e899b046e08 $
 * @package phing.tasks.system
 */
class TouchTask extends Task
{

    private $file;
    private $millis = -1;
    private $dateTime;
    private $filesets = array();
    private $fileUtils;

    /**
     *
     */
    public function __construct()
    {
        $this->fileUtils = new FileUtils();
    }

    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     * @param PhingFile $file
     * @return void
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * the new modification time of the file
     * in milliseconds since midnight Jan 1 1970.
     * Optional, default=now
     * @param $millis
     * @return void
     */
    public function setMillis($millis)
    {
        $this->millis = (int) $millis;
    }

    /**
     * the new modification time of the file
     * in the format MM/DD/YYYY HH:MM AM or PM;
     * Optional, default=now
     * @param $dateTime
     * @return void
     */
    public function setDatetime($dateTime)
    {
        $this->dateTime = (string) $dateTime;
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
     * Execute the touch operation.
     * @throws BuildException
     */
    public function main()
    {
        $savedMillis = $this->millis;

        if ($this->file === null && count($this->filesets) === 0) {
            throw new BuildException("Specify at least one source - a file or a fileset.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Use a fileset to touch directories.");
        }

        try { // try to touch file
            if ($this->dateTime !== null) {
                $this->setMillis(strtotime($this->dateTime));
                if ($this->millis < 0) {
                    throw new BuildException("Date of {$this->dateTime} results in negative milliseconds value relative to epoch (January 1, 1970, 00:00:00 GMT).");
                }
            }
            $this->_touch();
        } catch (Exception $ex) {
            throw new BuildException("Error touch()ing file", $ex, $this->location);
        }

        $this->millis = $savedMillis;

    }

    /**
     * Does the actual work.
     */
    public function _touch()
    {
        if ($this->file !== null) {
            if (!$this->file->exists()) {
                $this->log("Creating " . $this->file->__toString(), Project::MSG_INFO);
                try { // try to create file
                    $this->file->createNewFile();
                } catch (IOException  $ioe) {
                    throw new BuildException("Error creating new file " . $this->file->__toString(
                        ), $ioe, $this->location);
                }
            }
        }

        $resetMillis = false;
        if ($this->millis < 0) {
            $resetMillis = true;
            $this->millis = Phing::currentTimeMillis();
        }

        if ($this->file !== null) {
            $this->touchFile($this->file);
        }

        // deal with the filesets
        foreach ($this->filesets as $fs) {

            $ds = $fs->getDirectoryScanner($this->getProject());
            $fromDir = $fs->getDir($this->getProject());

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            for ($j = 0, $_j = count($srcFiles); $j < $_j; $j++) {
                $this->touchFile(new PhingFile($fromDir, (string) $srcFiles[$j]));
            }

            for ($j = 0, $_j = count($srcDirs); $j < $_j; $j++) {
                $this->touchFile(new PhingFile($fromDir, (string) $srcDirs[$j]));
            }
        }

        if ($resetMillis) {
            $this->millis = -1;
        }
    }

    /**
     * @param $file
     * @throws BuildException
     */
    private function touchFile($file)
    {
        if (!$file->canWrite()) {
            throw new BuildException("Can not change modification date of read-only file " . $file->__toString());
        }
        $file->setLastModified($this->millis);
    }

}
