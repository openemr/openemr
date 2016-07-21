<?php
/*
 *  $Id: c6629f7d06eb05a6f89fe92917286e370512e787 $
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

include_once 'phing/Task.php';

/**
 * Echos a message to the logging system or to a file
 *
 * @author   Michiel Rook <mrook@php.net>
 * @author   Andreas Aderhold, andi@binarycloud.com
 * @version  $Id: c6629f7d06eb05a6f89fe92917286e370512e787 $
 * @package  phing.tasks.system
 */
class EchoTask extends Task
{

    protected $msg = "";

    protected $file = "";

    protected $append = false;

    protected $level = "info";

    protected $filesets = array();

    public function main()
    {
        switch ($this->level) {
            case "error":
                $loglevel = Project::MSG_ERR;
                break;
            case "warning":
                $loglevel = Project::MSG_WARN;
                break;
            case "info":
                $loglevel = Project::MSG_INFO;
                break;
            case "verbose":
                $loglevel = Project::MSG_VERBOSE;
                break;
            case "debug":
                $loglevel = Project::MSG_DEBUG;
                break;
        }

        if (count($this->filesets)) {
            if (trim(substr($this->msg, -1)) != '') {
                $this->msg .= "\n";
            }
            $this->msg .= $this->getFilesetsMsg();
        }

        if (empty($this->file)) {
            $this->log($this->msg, $loglevel);
        } else {
            if ($this->append) {
                $handle = fopen($this->file, "a");
            } else {
                $handle = fopen($this->file, "w");
            }

            fwrite($handle, $this->msg);

            fclose($handle);
        }
    }

    /**
     * Merges all filesets into a string to be echoed out
     *
     * @return string String to echo
     */
    protected function getFilesetsMsg()
    {
        $project = $this->getProject();
        $msg = '';
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);
            $srcDirs = $ds->getIncludedDirectories();
            $srcFiles = $ds->getIncludedFiles();
            $msg .= 'Directory: ' . $fromDir . ' => '
                . realpath($fromDir) . "\n";
            foreach ($srcDirs as $dir) {
                $relPath = $fromDir . DIRECTORY_SEPARATOR . $dir;
                $msg .= $relPath . "\n";
            }
            foreach ($srcFiles as $file) {
                $relPath = $fromDir . DIRECTORY_SEPARATOR . $file;
                $msg .= $relPath . "\n";
            }
        }

        return $msg;
    }

    /** setter for file
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = (string) $file;
    }

    /** setter for level
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = (string) $level;
    }

    /** setter for append
     * @param $append
     */
    public function setAppend($append)
    {
        $this->append = $append;
    }

    /** setter for message
     * @param $msg
     */
    public function setMsg($msg)
    {
        $this->setMessage($msg);
    }

    /** alias setter
     * @param $msg
     */
    public function setMessage($msg)
    {
        $this->msg = (string) $msg;
    }

    /** Supporting the <echo>Message</echo> syntax.
     * @param $msg
     */
    public function addText($msg)
    {
        $this->msg = (string) $msg;
    }

    /**
     * Adds a fileset to echo the files of
     *
     * @param FileSet $fs Set of files to echo
     *
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }
}
