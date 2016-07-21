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

require_once 'phing/Task.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/util/FileUtils.php';
include_once 'phing/util/SourceFileScanner.php';
include_once 'phing/mappers/IdentityMapper.php';
include_once 'phing/mappers/FlattenMapper.php';

/**
 * A phing copy task.  Copies a file or directory to a new file
 * or directory.  Files are only copied if the source file is newer
 * than the destination file, or when the destination file does not
 * exist. It is possible to explicitly overwrite existing files.
 *
 * @author   Andreas Aderhold, andi@binarycloud.com
 *
 * @package  phing.tasks.system
 */
class CopyTask extends Task
{
    protected $file = null; // the source file (from xml attribute)
    protected $destFile = null; // the destiantion file (from xml attribute)
    protected $destDir = null; // the destination dir (from xml attribute)
    protected $overwrite = false; // overwrite destination (from xml attribute)
    protected $preserveLMT = false; // sync timestamps (from xml attribute)
    protected $preservePermissions = true; // sync permissions (from xml attribute)
    protected $includeEmpty = true; // include empty dirs? (from XML)
    protected $flatten = false; // apply the FlattenMapper right way (from XML)
    protected $mapperElement = null;

    protected $fileCopyMap = array(); // asoc array containing mapped file names
    protected $dirCopyMap = array(); // asoc array containing mapped file names
    protected $completeDirMap = array(); // asoc array containing complete dir names
    protected $fileUtils = null; // a instance of fileutils
    protected $filesets = array(); // all fileset objects assigned to this task
    protected $filelists = array(); // all filelist objects assigned to this task
    protected $filterChains = array(); // all filterchains objects assigned to this task

    protected $verbosity = Project::MSG_VERBOSE;

    /** @var int $mode */
    protected $mode = 0; // mode to create directories with

    /** @var bool $haltonerror */
    protected $haltonerror = true; // stop build on errors

    protected $enableMultipleMappings = false;

    /**
     * Sets up this object internal stuff.
     * i.e. the Fileutils instance and default mode.
     */
    public function __construct()
    {
        $this->fileUtils = new FileUtils();
        $this->mode = 0777 - umask();
    }

    /**
     * Set the overwrite flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean $bool Overwrite the destination file(s) if it/they already exist
     *
     * @return void
     */
    public function setOverwrite($bool)
    {
        $this->overwrite = (boolean) $bool;
    }

    /**
     * Used to force listing of all names of copied files.
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
     * @see CopyTask::setPreserveLastModified
     * @param $bool
     */
    public function setTstamp($bool)
    {
        $this->setPreserveLastModified($bool);
    }

    /**
     * Set the preserve timestamp flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean  Preserve the timestamp on the destination file
     * @return void
     */
    public function setPreserveLastModified($bool)
    {
        $this->preserveLMT = (boolean) $bool;
    }

    /**
     * Set the preserve permissions flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean $bool Preserve the timestamp on the destination file
     * @return void
     */
    public function setPreservepermissions($bool)
    {
        $this->preservePermissions = (boolean) $bool;
    }

    /**
     * @param $bool
     */
    public function setPreservemode($bool)
    {
        $this->setPreservepermissions($bool);
    }

    /**
     * Set the include empty dirs flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean $bool Flag if empty dirs should be cpoied too
     * @return void
     */
    public function setIncludeEmptyDirs($bool)
    {
        $this->includeEmpty = (boolean) $bool;
    }

    /**
     * Set the file. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if necessary.
     *
     * @param PhingFile $file The source file. Either a string or an PhingFile object
     *
     * @return void
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * Set the toFile. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if necessary.
     *
     * @param PhingFile $file The dest file. Either a string or an PhingFile object
     *
     * @return void
     */
    public function setTofile(PhingFile $file)
    {
        $this->destFile = $file;
    }

    /**
     * Sets the mode to create destination directories with (ignored on Windows).
     * Default mode is taken from umask()
     *
     * @param integer $mode Octal mode
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->mode = (int) base_convert($mode, 8, 10);
    }

    /**
     * Set the toDir. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if necessary.
     *
     * @param PhingFile $dir The directory, either a string or an PhingFile object
     *
     * @return void
     */
    public function setTodir(PhingFile $dir)
    {
        $this->destDir = $dir;
    }

    public function setEnableMultipleMappings($enableMultipleMappings)
    {
        $this->enableMultipleMappings = (boolean) $enableMultipleMappings;
    }

    public function isEnabledMultipleMappings()
    {
        return $this->enableMultipleMappings;
    }

    /**
     * Set the haltonerror attribute - when true, will
     * make the build fail when errors are detected.
     *
     * @param boolean $haltonerror Flag if the build should be stopped on errors
     *
     * @return void
     */
    public function setHaltonerror($haltonerror)
    {
        $this->haltonerror = (boolean) $haltonerror;
    }

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @param FileSet $fs Set of files to copy
     *
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     *
     * @return FileList The created filelist object
     */
    public function createFileList()
    {
        $num = array_push($this->filelists, new FileList());

        return $this->filelists[$num - 1];
    }

    /**
     * Creates a filterchain
     *
     * @return FilterChain The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }

    /**
     * Nested creator, creates one Mapper for this task
     *
     * @return Mapper         The created Mapper type object
     * @throws BuildException
     */
    public function createMapper()
    {
        if ($this->mapperElement !== null) {
            throw new BuildException("Cannot define more than one mapper", $this->location);
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * The main entry point where everything gets in motion.
     *
     * @return true           on success
     * @throws BuildException
     */
    public function main()
    {

        $this->validateAttributes();

        if ($this->file !== null) {
            if ($this->file->exists()) {
                if ($this->destFile === null) {
                    $this->destFile = new PhingFile($this->destDir, (string) $this->file->getName());
                }
                if ($this->overwrite === true || ($this->file->lastModified() > $this->destFile->lastModified())) {
                    $this->fileCopyMap[$this->file->getAbsolutePath()] = $this->destFile->getAbsolutePath();
                } else {
                    $this->log($this->file->getName() . " omitted, is up to date");
                }
            } else {
                // terminate build
                $this->logError("Could not find file " . $this->file->__toString() . " to copy.");
            }
        }

        $project = $this->getProject();

        // process filelists
        foreach ($this->filelists as $fl) {
            $fromDir = $fl->getDir($project);
            $srcFiles = $fl->getFiles($project);
            $srcDirs = array($fl->getDir($project));

            if (!$this->flatten && $this->mapperElement === null) {
                $this->completeDirMap[$fromDir->getAbsolutePath()] = $this->destDir->getAbsolutePath();
            }

            $this->_scan($fromDir, $this->destDir, $srcFiles, $srcDirs);
        }

        // process filesets
        foreach ($this->filesets as $fs) {
            try {
                $ds = $fs->getDirectoryScanner($project);
                $fromDir = $fs->getDir($project);
                $srcFiles = $ds->getIncludedFiles();
                $srcDirs = $ds->getIncludedDirectories();

                if (!$this->flatten && $this->mapperElement === null &&
                    $ds->isEverythingIncluded()
                ) {
                    $this->completeDirMap[$fromDir->getAbsolutePath()] = $this->destDir->getAbsolutePath();
                }

                $this->_scan($fromDir, $this->destDir, $srcFiles, $srcDirs);
            } catch (BuildException $e) {
                if ($this->haltonerror == true) {
                    throw $e;
                }

                $this->logError($e->getMessage());
            }
        }

        // go and copy the stuff
        $this->doWork();

        if ($this->destFile !== null) {
            $this->destDir = null;
        }
    }

    /**
     * Validates attributes coming in from XML
     *
     * @return void
     * @throws BuildException
     */
    protected function validateAttributes()
    {

        if ($this->file === null && count($this->filesets) === 0 && count($this->filelists) === 0) {
            throw new BuildException("CopyTask. Specify at least one source - a file, fileset or filelist.");
        }

        if ($this->destFile !== null && $this->destDir !== null) {
            throw new BuildException("Only one of destfile and destdir may be set.");
        }

        if ($this->destFile === null && $this->destDir === null) {
            throw new BuildException("One of destfile or destdir must be set.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Use a fileset to copy directories.");
        }

        if ($this->destFile !== null && count($this->filesets) > 0) {
            throw new BuildException("Cannot concatenate multiple files into a single file.");
        }

        if ($this->destFile !== null) {
            $this->destDir = new PhingFile($this->destFile->getParent());
        }
    }

    /**
     * Compares source files to destination files to see if they
     * should be copied.
     *
     * @param $fromDir
     * @param $toDir
     * @param $files
     * @param $dirs
     *
     * @return void
     */
    private function _scan(&$fromDir, &$toDir, &$files, &$dirs)
    {
        /* mappers should be generic, so we get the mappers here and
        pass them on to builMap. This method is not redundan like it seems */
        $mapper = $this->getMapper();
        
        $this->buildMap($fromDir, $toDir, $files, $mapper, $this->fileCopyMap);
        
        if ($this->includeEmpty) {
            $this->buildMap($fromDir, $toDir, $dirs, $mapper, $this->dirCopyMap);
        }
    }

    private function getMapper()
    {
        $mapper = null;
        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        } elseif ($this->flatten) {
            $mapper = new FlattenMapper();
        } else {
            $mapper = new IdentityMapper();
        }
        return $mapper;
    }

    /**
     * Builds a map of filenames (from->to) that should be copied
     *
     * @param $fromDir
     * @param $toDir
     * @param $names
     * @param $mapper
     * @param $map
     *
     * @return void
     */
    private function buildMap(&$fromDir, &$toDir, &$names, &$mapper, &$map)
    {
        $toCopy = null;
        if ($this->overwrite) {
            $v = array();
            foreach ($names as $name) {
                $result = $mapper->main($name);
                if ($result !== null) {
                    $v[] = $name;
                }
            }
            $toCopy = $v;
        } else {
            $ds = new SourceFileScanner($this);
            $toCopy = $ds->restrict($names, $fromDir, $toDir, $mapper);
        }

        for ($i = 0, $_i = count($toCopy); $i < $_i; $i++) {
            $src = new PhingFile($fromDir, $toCopy[$i]);
            $mapped = $mapper->main($toCopy[$i]);
            if (!$this->enableMultipleMappings) {
                $dest = new PhingFile($toDir, $mapped[0]);
                $map[$src->getAbsolutePath()] = $dest->getAbsolutePath();
            } else {
                foreach ($mapped as $mappedFile) {
                    if ($mappedFile === null) {
                        continue;
                    }
                    $dest = new PhingFile($toDir, $mappedFile);
                    $mappedFiles[] = $dest->getAbsolutePath();
                }
                $map[$src->getAbsolutePath()] = $mappedFiles;
            }
        }
    }

    /**
     * Actually copies the files
     *
     * @return void
     * @throws BuildException
     */
    protected function doWork()
    {

        // These "slots" allow filters to retrieve information about the currently-being-process files
        $fromSlot = $this->getRegisterSlot("currentFromFile");
        $fromBasenameSlot = $this->getRegisterSlot("currentFromFile.basename");

        $toSlot = $this->getRegisterSlot("currentToFile");
        $toBasenameSlot = $this->getRegisterSlot("currentToFile.basename");

        $mapSize = count($this->fileCopyMap);
        $total = $mapSize;

        // handle empty dirs if appropriate
        if ($this->includeEmpty) {
            $count = 0;
            foreach ($this->dirCopyMap as $srcdir => $destdir) {
                $s = new PhingFile((string) $srcdir);
                $d = new PhingFile((string) $destdir);
                if (!$d->exists()) {

                    // Setting source directory permissions to target
                    // (On permissions preservation, the target directory permissions
                    // will be inherited from the source directory, otherwise the 'mode'
                    // will be used)
                    $dirMode = ($this->preservePermissions ? $s->getMode() : $this->mode);

                    // Directory creation with specific permission mode
                    if (!$d->mkdirs($dirMode)) {
                        $this->logError("Unable to create directory " . $d->__toString());
                    } else {
                        if ($this->preserveLMT) {
                            $d->setLastModified($s->lastModified());
                        }

                        $count++;
                    }
                }
            }
            if ($count > 0) {
                $this->log(
                    "Created " . $count . " empty director" . ($count == 1 ? "y" : "ies") . " in " . $this->destDir->getAbsolutePath(
                    )
                );
            }
        }

        if ($mapSize == 0) {
            return;
        }

        $this->log(
            "Copying " . $mapSize . " file" . (($mapSize) === 1 ? '' : 's') . " to " . $this->destDir->getAbsolutePath(
            )
        );
        // walks the map and actually copies the files
        $count = 0;
        foreach ($this->fileCopyMap as $from => $toFiles) {
            if (is_array($toFiles)) {
                foreach ($toFiles as $to) {
                    $this->copyToSingleDestination($from, $to, $fromSlot, $fromBasenameSlot, $toSlot, $toBasenameSlot, $count, $total);
                }
            } else {
                $this->copyToSingleDestination($from, $toFiles, $fromSlot, $fromBasenameSlot, $toSlot, $toBasenameSlot, $count, $total);
            }
        }
    }

    private function copyToSingleDestination($from, $to, $fromSlot, $fromBasenameSlot, $toSlot, $toBasenameSlot, &$count, &$total)
    {
        if ($from === $to) {
            $this->log("Skipping self-copy of " . $from, $this->verbosity);
            $total--;
            return;
        }
        $this->log("From " . $from . " to " . $to, $this->verbosity);
        try { // try to copy file

            $fromFile = new PhingFile($from);
            $toFile = new PhingFile($to);

            $fromSlot->setValue($fromFile->getPath());
            $fromBasenameSlot->setValue($fromFile->getName());

            $toSlot->setValue($toFile->getPath());
            $toBasenameSlot->setValue($toFile->getName());

            $this->fileUtils->copyFile(
                $fromFile,
                $toFile,
                $this->overwrite,
                $this->preserveLMT,
                $this->filterChains,
                $this->getProject(),
                $this->mode,
                $this->preservePermissions
            );

            $count++;
        } catch (IOException $ioe) {
            $this->logError("Failed to copy " . $from . " to " . $to . ": " . $ioe->getMessage());
        }
    }

    /**
     * @param string $message
     * @param null $location
     *
     * @throws BuildException
     */
    protected function logError($message, $location = null)
    {
        if ($this->haltonerror) {
            throw new BuildException($message, $location);
        } else {
            $this->log($message, Project::MSG_ERR);
        }
    }
}
