<?php
/*
 *  $Id: 3e199443346a7e474e65c407ba691e5bdaf40f5a $
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

require_once 'phing/tasks/system/MatchingTask.php';
include_once 'phing/util/SourceFileScanner.php';
include_once 'phing/mappers/MergeMapper.php';
include_once 'phing/util/StringHelper.php';

/**
 * Creates a zip archive using PHP ZipArchive extension/
 *
 * @author    Michiel Rook <mrook@php.net>
 * @version   $Id: 3e199443346a7e474e65c407ba691e5bdaf40f5a $
 * @package   phing.tasks.ext
 * @since     2.1.0
 */
class ZipTask extends MatchingTask
{

    /**
     * @var PhingFile
     */
    private $zipFile;

    /**
     * @var PhingFile
     */
    private $baseDir;

    /**
     * Whether to include empty dirs in the archive.
     */
    private $includeEmpty = true;

    private $filesets = array();
    
    private $ignoreLinks = false;

    /**
     * File path prefix in zip archive
     *
     * @var string
     */
    private $prefix = null;

    /**
     * Comment for zip archive.
     *
     * @var string $comment
     */
    private $comment = '';

    /**
     * Add a new fileset.
     * @return FileSet
     */
    public function createFileSet()
    {
        $this->fileset = new ZipFileSet();
        $this->filesets[] = $this->fileset;

        return $this->fileset;
    }

    /**
     * Set is the name/location of where to create the zip file.
     * @param PhingFile $destFile The output of the zip
     */
    public function setDestFile(PhingFile $destFile)
    {
        $this->zipFile = $destFile;
    }

    /**
     * This is the base directory to look in for things to zip.
     * @param PhingFile $baseDir
     */
    public function setBasedir(PhingFile $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Sets the file path prefix for file in the zip file.
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
     * Set the include empty dirs flag.
     * @param  boolean  Flag if empty dirs should be tarred too
     * @return void
     */
    public function setIncludeEmptyDirs($bool)
    {
        $this->includeEmpty = (boolean) $bool;
    }

    /**
     * Set the ignore symlinks flag.
     * @param  boolean $bool Flag if symlinks should be ignored
     * @return void
     */
    public function setIgnoreLinks($bool)
    {
        $this->ignoreLinks = (boolean) $bool;
    }

    /**
     * Add a comment to the zip archive.
     *
     * @param string $text
     *
     * @return void
     */
    public function setComment($text)
    {
        $this->comment = $text;
    }

    /**
     * do the work
     * @throws BuildException
     */
    public function main()
    {
        if (!extension_loaded('zip')) {
            throw new BuildException("Zip extension is required");
        }

        if ($this->zipFile === null) {
            throw new BuildException("zipfile attribute must be set!", $this->getLocation());
        }

        if ($this->zipFile->exists() && $this->zipFile->isDirectory()) {
            throw new BuildException("zipfile is a directory!", $this->getLocation());
        }

        if ($this->zipFile->exists() && !$this->zipFile->canWrite()) {
            throw new BuildException("Can not write to the specified zipfile!", $this->getLocation());
        }

        try {
            if ($this->baseDir !== null) {
                if (!$this->baseDir->exists()) {
                    throw new BuildException("basedir '" . (string) $this->baseDir . "' does not exist!", $this->getLocation());
                }

                if (empty($this->filesets)) {
                    // add the main fileset to the list of filesets to process.
                    $mainFileSet = new ZipFileSet($this->fileset);
                    $mainFileSet->setDir($this->baseDir);
                    $this->filesets[] = $mainFileSet;
                }
            }

            if (empty($this->filesets)) {
                throw new BuildException("You must supply either a basedir "
                    . "attribute or some nested filesets.",
                    $this->getLocation());
            }

            // check if zip is out of date with respect to each
            // fileset
            if ($this->areFilesetsUpToDate()) {
                $this->log("Nothing to do: " . $this->zipFile->__toString() . " is up to date.", Project::MSG_INFO);

                return;
            }

            $this->log("Building zip: " . $this->zipFile->__toString(), Project::MSG_INFO);

            $zip = new ZipArchive();
            $res = $zip->open($this->zipFile->getAbsolutePath(), ZIPARCHIVE::CREATE);

            if ($res !== true) {
                throw new Exception("ZipArchive::open() failed with code " . $res);
            }

            if ($this->comment !== '') {
                $isCommented = $zip->setArchiveComment($this->comment);
                if ($isCommented === false) {
                    $this->log("Could not add a comment for the Archive.", Project::MSG_INFO);
                }
            }

            $this->addFilesetsToArchive($zip);

            $zip->close();
        } catch (IOException $ioe) {
            $msg = "Problem creating ZIP: " . $ioe->getMessage();
            throw new BuildException($msg, $ioe, $this->getLocation());
        }
    }

    /**
     * @param  array     $files array of filenames
     * @param  PhingFile $dir
     * @return boolean
     */
    private function archiveIsUpToDate($files, $dir)
    {
        $sfs = new SourceFileScanner($this);
        $mm = new MergeMapper();
        $mm->setTo($this->zipFile->getAbsolutePath());

        return count($sfs->restrict($files, $dir, null, $mm)) == 0;
    }

    /**
     * @return array
     * @throws BuildException
     */
    public function areFilesetsUpToDate()
    {
        foreach ($this->filesets as $fs) {
            $files = $fs->getFiles($this->project, $this->includeEmpty);
            if (!$this->archiveIsUpToDate($files, $fs->getDir($this->project))) {
                return false;
            }
            for ($i = 0, $fcount = count($files); $i < $fcount; $i++) {
                if ($this->zipFile->equals(new PhingFile($fs->getDir($this->project), $files[$i]))) {
                    throw new BuildException("A zip file cannot include itself", $this->getLocation());
                }
            }
        }
        return true;
    }

    /**
     * @param $zip
     */
    private function addFilesetsToArchive($zip)
    {
        foreach ($this->filesets as $fs) {
            $fsBasedir = (null != $this->baseDir) ? $this->baseDir :
                $fs->getDir($this->project);

            $files = $fs->getFiles($this->project, $this->includeEmpty);

            for ($i = 0, $fcount = count($files); $i < $fcount; $i++) {
                $f = new PhingFile($fsBasedir, $files[$i]);

                $pathInZip = $this->prefix
                    . $f->getPathWithoutBase($fsBasedir);

                $pathInZip = str_replace('\\', '/', $pathInZip);

                if ($this->ignoreLinks && $f->isLink()) {
                    continue;
                } elseif ($f->isDirectory()) {
                    if ($pathInZip != '.') {
                        $zip->addEmptyDir($pathInZip);
                    }
                } else {
                    $zip->addFile($f->getAbsolutePath(), $pathInZip);
                }
                $this->log("Adding " . $f->getPath() . " as " . $pathInZip . " to archive.", Project::MSG_VERBOSE);
            }
        }
    }

}

/**
 * This is a FileSet with the to specify permissions.
 *
 * Permissions are currently not implemented by PEAR Archive_Tar,
 * but hopefully they will be in the future.
 *
 * @package phing.tasks.ext
 */
class ZipFileSet extends FileSet
{

    private $files = null;

    /**
     *  Get a list of files and directories specified in the fileset.
     * @param Project $p
     * @param bool $includeEmpty
     * @throws BuildException
     * @return array a list of file and directory names, relative to
     *               the baseDir for the project.
     */
    public function getFiles(Project $p, $includeEmpty = true)
    {

        if ($this->files === null) {

            $ds = $this->getDirectoryScanner($p);
            $this->files = $ds->getIncludedFiles();

            // build a list of directories implicitly added by any of the files
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

            $emptyDirectories = array();

            if ($includeEmpty) {
                // Now add any empty dirs (dirs not covered by the implicit dirs)
                // to the files array.

                foreach ($incDirs as $dir) { // we cannot simply use array_diff() since we want to disregard empty/. dirs
                    if ($dir != "" && $dir != "." && !in_array($dir, $implicitDirs)) {
                        // it's an empty dir, so we'll add it.
                        $emptyDirectories[] = $dir;
                    }
                }
            } // if $includeEmpty

            $this->files = array_merge($implicitDirs, $emptyDirectories, $this->files);
            sort($this->files);
        } // if ($this->files===null)

        return $this->files;
    }
}
