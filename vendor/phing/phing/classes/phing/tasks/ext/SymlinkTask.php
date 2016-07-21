<?php

/*
 *  $Id: 774d989f0f224564952ea51682006030b243ea9c $
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

require_once "phing/Task.php";

/**
 * Generates symlinks based on a target / link combination.
 * Can also symlink contents of a directory, individually
 *
 * Single target symlink example:
 * <code>
 *     <symlink target="/some/shared/file" link="${project.basedir}/htdocs/my_file" />
 * </code>
 *
 * Symlink entire contents of directory
 *
 * This will go through the contents of "/my/shared/library/*"
 * and create a symlink for each entry into ${project.basedir}/library/
 * <code>
 *     <symlink link="${project.basedir}/library">
 *         <fileset dir="/my/shared/library">
 *             <include name="*" />
 *         </fileset>
 *     </symlink>
 * </code>
 *
 * @author Andrei Serdeliuc <andrei@serdeliuc.ro>
 * @extends Task
 * @version $ID$
 * @package phing.tasks.ext
 */
class SymlinkTask extends Task
{
    /**
     * What we're symlinking from
     *
     * (default value: null)
     *
     * @var string
     */
    private $_target = null;

    /**
     * Symlink location
     *
     * (default value: null)
     *
     * @var string
     */
    private $_link = null;

    /**
     * Collection of filesets
     * Used when linking contents of a directory
     *
     * (default value: array())
     *
     * @var array
     */
    private $_filesets = array();

    /**
     * Whether to override the symlink if it exists but points
     * to a different location
     *
     * (default value: false)
     *
     * @var boolean
     */
    private $_overwrite = false;

    /**
     * setter for _target
     *
     * @param  string $target
     * @return void
     */
    public function setTarget($target)
    {
        $this->_target = $target;
    }

    /**
     * setter for _link
     *
     * @param  string $link
     * @return void
     */
    public function setLink($link)
    {
        $this->_link = $link;
    }

    /**
     * creator for _filesets
     *
     * @return FileSet
     */
    public function createFileset()
    {
        $num = array_push($this->_filesets, new FileSet());

        return $this->_filesets[$num - 1];
    }

    /**
     * setter for _overwrite
     *
     * @param  boolean $overwrite
     * @return void
     */
    public function setOverwrite($overwrite)
    {
        $this->_overwrite = $overwrite;
    }

    /**
     * getter for _target
     *
     * @throws BuildException
     * @return string
     */
    public function getTarget()
    {
        if ($this->_target === null) {
            throw new BuildException('Target not set');
        }

        return $this->_target;
    }

    /**
     * getter for _link
     *
     * @throws BuildException
     * @return string
     */
    public function getLink()
    {
        if ($this->_link === null) {
            throw new BuildException('Link not set');
        }

        return $this->_link;
    }

    /**
     * getter for _filesets
     *
     * @return array
     */
    public function getFilesets()
    {
        return $this->_filesets;
    }

    /**
     * getter for _overwrite
     *
     * @return boolean
     */
    public function getOverwrite()
    {
        return $this->_overwrite;
    }

    /**
     * Generates an array of directories / files to be linked
     * If _filesets is empty, returns getTarget()
     *
     * @throws BuildException
     * @return array|string
     */
    protected function getMap()
    {
        $fileSets = $this->getFilesets();

        // No filesets set
        // We're assuming single file / directory
        if (empty($fileSets)) {
            return $this->getTarget();
        }

        $targets = array();

        foreach ($fileSets as $fs) {
            if (!($fs instanceof FileSet)) {
                continue;
            }

            // We need a directory to store the links
            if (!is_dir($this->getLink())) {
                throw new BuildException('Link must be an existing directory when using fileset');
            }

            $fromDir = $fs->getDir($this->getProject())->getAbsolutePath();

            if (!is_dir($fromDir)) {
                $this->log('Directory doesn\'t exist: ' . $fromDir, Project::MSG_WARN);
                continue;
            }

            $fsTargets = array();

            $ds = $fs->getDirectoryScanner($this->getProject());

            $fsTargets = array_merge(
                $fsTargets,
                $ds->getIncludedDirectories(),
                $ds->getIncludedFiles()
            );

            // Add each target to the map
            foreach ($fsTargets as $target) {
                if (!empty($target)) {
                    $targets[$target] = $fromDir . DIRECTORY_SEPARATOR . $target;
                }
            }
        }

        return $targets;
    }

    /**
     * Main entry point for task
     *
     * @return bool
     */
    public function main()
    {
        $map = $this->getMap();

        // Single file symlink
        if (is_string($map)) {
            return $this->symlink($map, $this->getLink());
        }

        // Multiple symlinks
        foreach ($map as $name => $targetPath) {
            $this->symlink($targetPath, $this->getLink() . DIRECTORY_SEPARATOR . $name);
        }

        return true;
    }

    /**
     * Create the actual link
     *
     * @param  string $target
     * @param  string $link
     * @return bool
     */
    protected function symlink($target, $link)
    {
        $fs = FileSystem::getFileSystem();

        if (is_link($link) && @readlink($link) == $target) {
            $this->log('Link exists: ' . $link, Project::MSG_INFO);

            return true;
        }

        if (file_exists($link) || is_link($link)) {
            if (!$this->getOverwrite()) {
                $this->log('Not overwriting existing link ' . $link, Project::MSG_ERR);

                return false;
            }

            if (is_link($link) || is_file($link)) {
                $fs->unlink($link);
                $this->log('Link removed: ' . $link, Project::MSG_INFO);
            } else {
                $fs->rmdir($link, true);
                $this->log('Directory removed: ' . $link, Project::MSG_INFO);
            }
        }

        $this->log('Linking: ' . $target . ' to ' . $link, Project::MSG_INFO);

        return $fs->symlink($target, $link);
    }
}
