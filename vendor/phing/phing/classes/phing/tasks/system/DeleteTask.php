<?php
/*
 *  $Id: 4c715ad7eb460825b33303a50fbca1d25599cbdf $
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
 * Deletes a file or directory, or set of files defined by a fileset.
 *
 * @version   $Id: 4c715ad7eb460825b33303a50fbca1d25599cbdf $
 * @package   phing.tasks.system
 */
class DeleteTask extends Task
{

    protected $file;
    protected $dir;
    protected $filesets = array();
    protected $includeEmpty = false;

    protected $quiet = false;
    protected $failonerror = false;
    protected $verbosity = Project::MSG_VERBOSE;

    /** Any filelists of files that should be deleted. */
    private $filelists = array();

    /**
     * Set the name of a single file to be removed.
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * Set the directory from which files are to be deleted.
     * @param PhingFile $dir
     */
    public function setDir(PhingFile $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Used to force listing of all names of deleted files.
     * @param boolean $verbosity
     */
    public function setVerbose($verbosity)
    {
        if ($verbosity) {
            $this->verbosity = Project::MSG_INFO;
        } else {
            $this->verbosity = Project::MSG_VERBOSE;
        }
    }

    /**
     * If the file does not exist, do not display a diagnostic
     * message or modify the exit status to reflect an error.
     * This means that if a file or directory cannot be deleted,
     * then no error is reported. This setting emulates the
     * -f option to the Unix rm command. Default is false
     * meaning things are verbose
     * @param bool $bool
     * @return void
     */
    public function setQuiet($bool)
    {
        $this->quiet = $bool;
        if ($this->quiet) {
            $this->failonerror = false;
        }
    }

    /** this flag means 'note errors to the output, but keep going'
     * @param bool $bool
     * @retujrn void
     */
    public function setFailOnError($bool)
    {
        $this->failonerror = $bool;
    }

    /**
     * Used to delete empty directories.
     * @param bool $includeEmpty
     * @return void
     */
    public function setIncludeEmptyDirs($includeEmpty)
    {
        $this->includeEmpty = (boolean) $includeEmpty;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     * @return FileList
     */
    public function createFileList()
    {
        $num = array_push($this->filelists, new FileList());

        return $this->filelists[$num - 1];
    }

    /**
     * Delete the file(s).
     * @throws BuildException
     */
    public function main()
    {
        if ($this->file === null && $this->dir === null && count($this->filesets) === 0 && count(
                $this->filelists
            ) === 0
        ) {
            throw new BuildException("At least one of the file or dir attributes, or a fileset element, or a filelist element must be set.");
        }

        if ($this->quiet && $this->failonerror) {
            throw new BuildException("quiet and failonerror cannot both be set to true", $this->location);
        }

        // delete a single file
        if ($this->file !== null) {
            if ($this->file->exists()) {
                if ($this->file->isDirectory()) {
                    $this->log(
                        "Directory " . $this->file->__toString(
                        ) . " cannot be removed using the file attribute. Use dir instead."
                    );
                } else {
                    $this->log("Deleting: " . $this->file->__toString());
                    try {
                        $this->file->delete();
                    } catch (Exception $e) {
                        $message = "Unable to delete file " . $this->file->__toString() . ": " . $e->getMessage();
                        if ($this->failonerror) {
                            throw new BuildException($message);
                        } else {
                            $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                        }
                    }
                }
            } else {
                $message = "Could not find file " . $this->file->getAbsolutePath() . " to delete.";

                if ($this->failonerror) {
                    throw new BuildException($message);
                } else {
                    $this->log($message, ($this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN));
                }
            }
        }

        // delete the directory
        if ($this->dir !== null) {
            if ($this->dir->exists() && $this->dir->isDirectory()) {
                if ($this->verbosity === Project::MSG_VERBOSE) {
                    $this->log("Deleting directory " . $this->dir->__toString());
                }
                $this->removeDir($this->dir);
            } else {
                $message = "Directory " . $this->dir->getAbsolutePath() . " does not exist or is not a directory.";

                if ($this->failonerror) {
                    throw new BuildException($message);
                } else {
                    $this->log($message, ($this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN));
                }
            }
        }

        // delete the files in the filelists
        foreach ($this->filelists as $fl) {
            try {
                $files = $fl->getFiles($this->project);
                $this->removeFiles($fl->getDir($this->project), $files, $empty = array());
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                } else {
                    $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }

        // delete the files in the filesets
        foreach ($this->filesets as $fs) {
            try {
                $ds = $fs->getDirectoryScanner($this->project);
                $files = $ds->getIncludedFiles();
                $dirs = $ds->getIncludedDirectories();
                $this->removeFiles($fs->getDir($this->project), $files, $dirs);
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                } else {
                    $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }
    }

    /**
     * Recursively removes a directory.
     * @param PhingFile $d The directory to remove.
     * @throws BuildException
     */
    private function removeDir($d)
    {
        $list = $d->listDir();
        if ($list === null) {
            $list = array();
        }

        foreach ($list as $s) {
            $f = new PhingFile($d, $s);
            if ($f->isDirectory()) {
                $this->removeDir($f);
            } else {
                $this->log("Deleting " . $f->__toString(), $this->verbosity);
                try {
                    $f->delete();
                } catch (Exception $e) {
                    $message = "Unable to delete file " . $f->__toString() . ": " . $e->getMessage();
                    if ($this->failonerror) {
                        throw new BuildException($message);
                    } else {
                        $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                    }
                }
            }
        }
        $this->log("Deleting directory " . $d->getAbsolutePath(), $this->verbosity);
        try {
            $d->delete();
        } catch (Exception $e) {
            $message = "Unable to delete directory " . $d->__toString() . ": " . $e->getMessage();
            if ($this->failonerror) {
                throw new BuildException($message);
            } else {
                $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
            }
        }
    }

    /**
     * remove an array of files in a directory, and a list of subdirectories
     * which will only be deleted if 'includeEmpty' is true
     * @param PhingFile $d directory to work from
     * @param array &$files array of files to delete; can be of zero length
     * @param array &$dirs array of directories to delete; can of zero length
     * @throws BuildException
     */
    private function removeFiles(PhingFile $d, &$files, &$dirs)
    {
        if (count($files) > 0) {
            $this->log("Deleting " . count($files) . " files from " . $d->__toString());
            for ($j = 0, $_j = count($files); $j < $_j; $j++) {
                $f = new PhingFile($d, $files[$j]);
                $this->log("Deleting " . $f->getAbsolutePath(), $this->verbosity);
                try {
                    $f->delete();
                } catch (Exception $e) {
                    $message = "Unable to delete file " . $f->__toString() . ": " . $e->getMessage();
                    if ($this->failonerror) {
                        throw new BuildException($message);
                    } else {
                        $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                    }
                }

            }
        }

        if (count($dirs) > 0 && $this->includeEmpty) {
            $dirCount = 0;
            for ($j = count($dirs) - 1; $j >= 0; --$j) {
                $dir = new PhingFile($d, $dirs[$j]);
                $dirFiles = $dir->listDir();
                if ($dirFiles === null || count($dirFiles) === 0) {
                    $this->log("Deleting " . $dir->__toString(), $this->verbosity);
                    try {
                        $dir->delete();
                        $dirCount++;
                    } catch (Exception $e) {
                        $message = "Unable to delete directory " . $dir->__toString();
                        if ($this->failonerror) {
                            throw new BuildException($message);
                        } else {
                            $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                        }
                    }
                }
            }
            if ($dirCount > 0) {
                $this->log("Deleted $dirCount director" . ($dirCount == 1 ? "y" : "ies") . " from " . $d->__toString());
            }
        }
    }
}
