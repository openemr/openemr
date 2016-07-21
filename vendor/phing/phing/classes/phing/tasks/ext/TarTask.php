<?php
/**
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

require_once 'phing/tasks/system/MatchingTask.php';
include_once 'phing/util/SourceFileScanner.php';
include_once 'phing/mappers/MergeMapper.php';
include_once 'phing/util/StringHelper.php';

/**
 * Creates a tar archive using PEAR Archive_Tar.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Stefano Mazzocchi <stefano@apache.org> (Ant)
 * @author    Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @author    Magesh Umasankar
 *
 * @package   phing.tasks.ext
 */
class TarTask extends MatchingTask
{

    const TAR_NAMELEN = 100;

    const WARN = "warn";
    const FAIL = "fail";
    const OMIT = "omit";

    private $tarFile;
    private $baseDir;
    private $includeEmpty = true; // Whether to include empty dirs in the TAR

    private $longFileMode = "warn";

    private $filesets = array();
    private $fileSetFiles = array();

    /**
     * Indicates whether the user has been warned about long files already.
     */
    private $longWarningGiven = false;

    /**
     * Compression mode.  Available options "gzip", "bzip2", "none" (null).
     */
    private $compression = null;

    /**
     * File path prefix in the tar archive
     *
     * @var string
     */
    private $prefix = null;

    /**
     * Ensures that PEAR lib exists.
     */
    public function init()
    {
        include_once 'Archive/Tar.php';
        if (!class_exists('Archive_Tar')) {
            throw new BuildException("You must have installed the PEAR Archive_Tar class in order to use TarTask.");
        }
    }

    /**
     * Add a new fileset
     * @return FileSet
     */
    public function createTarFileSet()
    {
        $this->fileset = new TarFileSet();
        $this->filesets[] = $this->fileset;

        return $this->fileset;
    }

    /**
     * Add a new fileset.  Alias to createTarFileSet() for backwards compatibility.
     * @return FileSet
     * @see createTarFileSet()
     */
    public function createFileSet()
    {
        $this->fileset = new TarFileSet();
        $this->filesets[] = $this->fileset;

        return $this->fileset;
    }

    /**
     * Set is the name/location of where to create the tar file.
     * @param PhingFile $destFile The output of the tar
     */
    public function setDestFile(PhingFile $destFile)
    {
        $this->tarFile = $destFile;
    }

    /**
     * This is the base directory to look in for things to tar.
     * @param PhingFile $baseDir
     */
    public function setBasedir(PhingFile $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Set the include empty dirs flag.
     *
     * @param  boolean $bool Flag if empty dirs should be tarred too
     *
     * @return void
     */
    public function setIncludeEmptyDirs($bool)
    {
        $this->includeEmpty = (boolean) $bool;
    }

    /**
     * Set how to handle long files, those with a path&gt;100 chars.
     * Optional, default=warn.
     * <p>
     * Allowable values are
     * <ul>
     * <li>  truncate - paths are truncated to the maximum length
     * <li>  fail - paths greater than the maximim cause a build exception
     * <li>  warn - paths greater than the maximum cause a warning and GNU is used
     * <li>  gnu - GNU extensions are used for any paths greater than the maximum.
     * <li>  omit - paths greater than the maximum are omitted from the archive
     * </ul>
     * @param $mode
     */
    public function setLongfile($mode)
    {
        $this->longFileMode = $mode;
    }

    /**
     * Set compression method.
     * Allowable values are
     * <ul>
     * <li>  none - no compression
     * <li>  gzip - Gzip compression
     * <li>  bzip2 - Bzip2 compression
     * </ul>
     * @param string $mode
     */
    public function setCompression($mode)
    {
        switch ($mode) {
            case "gzip":
                $this->compression = "gz";
                break;
            case "bzip2":
                $this->compression = "bz2";
                break;
            case "none":
                $this->compression = null;
                break;
            default:
                $this->log("Ignoring unknown compression mode: " . $mode, Project::MSG_WARN);
                $this->compression = null;
        }
    }

    /**
     * Sets the file path prefix for file in the tar file.
     *
     * @param string $prefix Prefix
     *
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * do the work
     * @throws BuildException
     */
    public function main()
    {
        if ($this->tarFile === null) {
            throw new BuildException("tarfile attribute must be set!", $this->getLocation());
        }

        if ($this->tarFile->exists() && $this->tarFile->isDirectory()) {
            throw new BuildException("tarfile is a directory!", $this->getLocation());
        }

        if ($this->tarFile->exists() && !$this->tarFile->canWrite()) {
            throw new BuildException("Can not write to the specified tarfile!", $this->getLocation());
        }

        // shouldn't need to clone, since the entries in filesets
        // themselves won't be modified -- only elements will be added
        $savedFileSets = $this->filesets;

        try {
            if ($this->baseDir !== null) {
                if (!$this->baseDir->exists()) {
                    throw new BuildException("basedir '" . (string) $this->baseDir . "' does not exist!", $this->getLocation(
                        ));
                }
                if (empty($this->filesets)) { // if there weren't any explicit filesets specivied, then
                    // create a default, all-inclusive fileset using the specified basedir.
                    $mainFileSet = new TarFileSet($this->fileset);
                    $mainFileSet->setDir($this->baseDir);
                    $this->filesets[] = $mainFileSet;
                }
            }

            if (empty($this->filesets)) {
                throw new BuildException("You must supply either a basedir "
                    . "attribute or some nested filesets.",
                    $this->getLocation());
            }

            // check if tar is out of date with respect to each fileset
            if ($this->tarFile->exists() && $this->isArchiveUpToDate()) {
                $this->log("Nothing to do: " . $this->tarFile->__toString() . " is up to date.", Project::MSG_INFO);
                return;
            }

            $this->log("Building tar: " . $this->tarFile->__toString(), Project::MSG_INFO);

            $tar = new Archive_Tar($this->tarFile->getAbsolutePath(), $this->compression);
            $pear = new PEAR();

            if ($pear->isError($tar->error_object)) {
                throw new BuildException($tar->error_object->getMessage());
            }

            foreach ($this->filesets as $fs) {
                $files = $fs->getFiles($this->project, $this->includeEmpty);
                if (count($files) > 1 && strlen($fs->getFullpath()) > 0) {
                    throw new BuildException("fullpath attribute may only "
                        . "be specified for "
                        . "filesets that specify a "
                        . "single file.");
                }
                $fsBasedir = $fs->getDir($this->project);
                $filesToTar = array();
                for ($i = 0, $fcount = count($files); $i < $fcount; $i++) {
                    $f = new PhingFile($fsBasedir, $files[$i]);
                    $filesToTar[] = $f->getAbsolutePath();
                    $this->log("Adding file " . $f->getPath() . " to archive.", Project::MSG_VERBOSE);
                }
                $tar->addModify($filesToTar, $this->prefix, $fsBasedir->getAbsolutePath());

                if ($pear->isError($tar->error_object)) {
                    throw new BuildException($tar->error_object->getMessage());
                }
            }


        } catch (IOException $ioe) {
            $msg = "Problem creating TAR: " . $ioe->getMessage();
            $this->filesets = $savedFileSets;
            throw new BuildException($msg, $ioe, $this->getLocation());
        }

        $this->filesets = $savedFileSets;
    }

    /**
     * @param  array     $files array of filenames
     * @param  PhingFile $dir
     *
     * @return boolean
     */
    protected function areFilesUpToDate($files, $dir)
    {
        $sfs = new SourceFileScanner($this);
        $mm = new MergeMapper();
        $mm->setTo($this->tarFile->getAbsolutePath());

        return count($sfs->restrict($files, $dir, null, $mm)) == 0;
    }

    /**
     * @return array
     * @throws BuildException
     */
    private function isArchiveUpToDate()
    {
        foreach ($this->filesets as $fs) {
            $files = $fs->getFiles($this->project, $this->includeEmpty);
            if (!$this->areFilesUpToDate($files, $fs->getDir($this->project))) {
                return false;
            }
            for ($i = 0, $fcount = count($files); $i < $fcount; $i++) {
                if ($this->tarFile->equals(new PhingFile($fs->getDir($this->project), $files[$i]))) {
                    throw new BuildException("A tar file cannot include itself", $this->getLocation());
                }
            }
        }
        return true;
    }
}


/**
 * This is a FileSet with the option to specify permissions.
 *
 * Permissions are currently not implemented by PEAR Archive_Tar,
 * but hopefully they will be in the future.
 *
 * @package   phing.tasks.ext
 */
class TarFileSet extends FileSet
{

    private $files = null;

    private $mode = 0100644;

    private $userName = "";
    private $groupName = "";
    private $prefix = "";
    private $fullpath = "";
    private $preserveLeadingSlashes = false;

    /**
     * Get a list of files and directories specified in the fileset.
     *
     * @param Project $p
     * @param bool $includeEmpty
     *
     * @throws BuildException
     *
     * @return array a list of file and directory names, relative to
     *               the baseDir for the project.
     */
    public function getFiles(Project $p, $includeEmpty = true)
    {

        if ($this->files === null) {

            $ds = $this->getDirectoryScanner($p);
            $this->files = $ds->getIncludedFiles();

            if ($includeEmpty) {

                // first any empty directories that will not be implicitly added by any of the files
                $implicitDirs = array();
                foreach ($this->files as $file) {
                    $implicitDirs[] = dirname($file);
                }

                $incDirs = $ds->getIncludedDirectories();

                // we'll need to add to that list of implicit dirs any directories
                // that contain other *directories* (and not files), since otherwise
                // we get duplicate directories in the resulting tar
                foreach ($incDirs as $dir) {
                    foreach ($incDirs as $dircheck) {
                        if (!empty($dir) && $dir == dirname($dircheck)) {
                            $implicitDirs[] = $dir;
                        }
                    }
                }

                $implicitDirs = array_unique($implicitDirs);

                // Now add any empty dirs (dirs not covered by the implicit dirs)
                // to the files array.

                foreach ($incDirs as $dir) { // we cannot simply use array_diff() since we want to disregard empty/. dirs
                    if ($dir != "" && $dir != "." && !in_array($dir, $implicitDirs)) {
                        // it's an empty dir, so we'll add it.
                        $this->files[] = $dir;
                    }
                }
            } // if $includeEmpty

        } // if ($this->files===null)

        return $this->files;
    }

    /**
     * A 3 digit octal string, specify the user, group and
     * other modes in the standard Unix fashion;
     * optional, default=0644
     * @param string $octalString
     */
    public function setMode($octalString)
    {
        $octal = (int) $octalString;
        $this->mode = 0100000 | $octal;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * The username for the tar entry
     * This is not the same as the UID, which is
     * not currently set by the task.
     * @param $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * The groupname for the tar entry; optional, default=""
     * This is not the same as the GID, which is
     * not currently set by the task.
     * @param $groupName
     */
    public function setGroup($groupName)
    {
        $this->groupName = $groupName;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->groupName;
    }

    /**
     * If the prefix attribute is set, all files in the fileset
     * are prefixed with that path in the archive.
     * optional.
     * @param bool $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * If the fullpath attribute is set, the file in the fileset
     * is written with that path in the archive. The prefix attribute,
     * if specified, is ignored. It is an error to have more than one file specified in
     * such a fileset.
     * @param $fullpath
     */
    public function setFullpath($fullpath)
    {
        $this->fullpath = $fullpath;
    }

    /**
     * @return string
     */
    public function getFullpath()
    {
        return $this->fullpath;
    }

    /**
     * Flag to indicates whether leading `/'s` should
     * be preserved in the file names.
     * Optional, default is <code>false</code>.
     *
     * @param bool $b
     *
     * @return void
     */
    public function setPreserveLeadingSlashes($b)
    {
        $this->preserveLeadingSlashes = (boolean) $b;
    }

    /**
     * @return bool
     */
    public function getPreserveLeadingSlashes()
    {
        return $this->preserveLeadingSlashes;
    }
}
