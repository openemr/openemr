<?php
/*
 *  $Id: 4160270de534a38cfabe28205a6d9446acb46f04 $
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
require_once 'phing/tasks/ext/git/GitBaseTask.php';
/**
 * Wrapper around git-commit
 *
 * @package phing.tasks.ext.git
 * @author Jonathan Creasy <jonathan.creasy@gmail.com>
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitCommitTask extends GitBaseTask
{
    /**
     * @var boolean
     */
    private $allFiles = false;

    /**
     * @var string
     */
    private $message;

    /**
     * @var FileSet[]
     */
    private $filesets = array();

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        if ($this->allFiles !== true && empty($this->filesets)) {
            throw new BuildException('"allFiles" cannot be false if no filesets are specified.');
        }

        $options = array();
        if ($this->allFiles === true) {
            $options['all'] = true;
        }

        $arguments = array();
        if ($this->allFiles !== true) {
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($this->project);
                $srcFiles = $ds->getIncludedFiles();

                foreach ($srcFiles as $file) {
                    $arguments[] = $file;
                }
            }
        }

        if (!empty($this->message)) {
            $options['message'] = $this->message;
        } else {
            $options['allow-empty-message'] = true;
        }

        try {
            $client = $this->getGitClient(false, $this->getRepository());

            $command = $client->getCommand('commit');
            $command->setArguments($arguments);
            $command->setOptions($options);
            $command->execute();
        } catch (Exception $e) {
            throw new BuildException('The remote end hung up unexpectedly', $e);
        }

        $this->logCommand($options, $arguments);
    }

    /**
     * @param array $options
     * @param array $arguments
     */
    protected function logCommand(array $options, array $arguments)
    {
        $msg = 'git-commit: Executed git commit ';
        foreach ($options as $option => $value) {
            $msg .= ' --' . $option . '=' . $value;
        }

        foreach ($arguments as $argument) {
            $msg .= ' ' . $argument;
        }

        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * @return bool
     */
    public function getAllFiles()
    {
        return $this->allFiles;
    }

    /**
     * @param $flag
     */
    public function setAllFiles($flag)
    {
        $this->allFiles = (bool) $flag;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
}
