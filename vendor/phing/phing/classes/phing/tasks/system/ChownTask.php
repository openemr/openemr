<?php
/*
 *  $Id: 211fc3c1da06e93f652f487022ec9e39941c40ed $
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
include_once 'phing/types/FileSet.php';

/**
 * Task that changes the permissions on a file/directory.
 *
 * @author    Mehmet Emre Yilmaz <mehmety@gmail.com>
 * @version   $Id: 211fc3c1da06e93f652f487022ec9e39941c40ed $
 * @package   phing.tasks.system
 */
class ChownTask extends Task
{

    private $file;

    private $user;
    private $group;

    private $filesets = array();

    private $filesystem;

    private $quiet = false;
    private $failonerror = true;
    private $verbose = true;

    /**
     * This flag means 'note errors to the output, but keep going'
     * @see setQuiet()
     * @param $bool
     */
    public function setFailonerror($bool)
    {
        $this->failonerror = $bool;
    }

    /**
     * Set quiet mode, which suppresses warnings if chown() fails.
     * @see setFailonerror()
     * @param $bool
     */
    public function setQuiet($bool)
    {
        $this->quiet = $bool;
        if ($this->quiet) {
            $this->failonerror = false;
        }
    }

    /**
     * Set verbosity, which if set to false surpresses all but an overview
     * of what happened.
     * @param $bool
     */
    public function setVerbose($bool)
    {
        $this->verbose = (bool) $bool;
    }

    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * Sets the user
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Sets the group
     * @param $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     * @param FileSet $fs
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Execute the touch operation.
     * @return void
     */
    public function main()
    {
        // Check Parameters
        $this->checkParams();
        $this->chown();
    }

    /**
     * Ensure that correct parameters were passed in.
     * @throws BuildException
     * @return void
     */
    private function checkParams()
    {

        if ($this->file === null && empty($this->filesets)) {
            throw new BuildException("Specify at least one source - a file or a fileset.");
        }

        if ($this->user === null && $this->group === null) {
            throw new BuildException("You have to specify either an owner or a group for chown.");
        }
    }

    /**
     * Does the actual work.
     * @return void
     */
    private function chown()
    {
        $userElements = explode('.', $this->user);

        $user = $userElements[0];

        if (count($userElements) > 1) {
            $group = $userElements[1];
        } else {
            $group = $this->group;
        }

        // counters for non-verbose output
        $total_files = 0;
        $total_dirs = 0;

        // one file
        if ($this->file !== null) {
            $total_files = 1;
            $this->chownFile($this->file, $user, $group);
        }

        // filesets
        foreach ($this->filesets as $fs) {

            $ds = $fs->getDirectoryScanner($this->project);
            $fromDir = $fs->getDir($this->project);

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            $filecount = count($srcFiles);
            $total_files = $total_files + $filecount;
            for ($j = 0; $j < $filecount; $j++) {
                $this->chownFile(new PhingFile($fromDir, $srcFiles[$j]), $user, $group);
            }

            $dircount = count($srcDirs);
            $total_dirs = $total_dirs + $dircount;
            for ($j = 0; $j < $dircount; $j++) {
                $this->chownFile(new PhingFile($fromDir, $srcDirs[$j]), $user, $group);
            }
        }

        if (!$this->verbose) {
            $this->log('Total files changed to ' . $user . ($group ? "." . $group : "") . ': ' . $total_files);
            $this->log('Total directories changed to ' . $user . ($group ? "." . $group : "") . ': ' . $total_dirs);
        }

    }

    /**
     * Actually change the mode for the file.
     * @param PhingFile $file
     * @param string $user
     * @param string $group
     * @throws BuildException
     * @throws Exception
     */
    private function chownFile(PhingFile $file, $user, $group = "")
    {
        if (!$file->exists()) {
            throw new BuildException("The file " . $file->__toString() . " does not exist");
        }

        try {
            if (!empty($user)) {
                $file->setUser($user);
            }

            if (!empty($group)) {
                $file->setGroup($group);
            }

            if ($this->verbose) {
                $this->log(
                    "Changed file owner on '" . $file->__toString() . "' to " . $user . ($group ? "." . $group : "")
                );
            }
        } catch (Exception $e) {
            if ($this->failonerror) {
                throw $e;
            } else {
                $this->log($e->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
            }
        }
    }

}
