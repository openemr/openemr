<?php
/*
 *  $Id: e330e2db38e780989e79afe3bdbd269afe8b02fa $
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
include_once 'phing/system/io/PhingFile.php';

/**
 * Task to create a directory.
 *
 * @author   Andreas Aderhold, andi@binarycloud.com
 * @version  $Id: e330e2db38e780989e79afe3bdbd269afe8b02fa $
 * @package  phing.tasks.system
 */
class MkdirTask extends Task
{

    /**
     * Directory to create.
     * @var PhingFile $dir
     */
    private $dir;

    /**
     * Mode to create directory with
     * @var integer
     */
    private $mode = 0;

    /**
     * Sets up this object internal stuff. i.e. the default mode.
     */
    public function __construct()
    {
        $this->mode = 0777 - umask();
    }

    /**
     * create the directory and all parents
     *
     * @throws BuildException if dir is somehow invalid, or creation failed.
     */
    public function main()
    {
        if ($this->dir === null) {
            throw new BuildException("dir attribute is required", $this->location);
        }
        if ($this->dir->isFile()) {
            throw new BuildException("Unable to create directory as a file already exists with that name: " . $this->dir->getAbsolutePath(
                ));
        }
        if (!$this->dir->exists()) {
            $result = $this->dir->mkdirs($this->mode);
            if (!$result) {
                if ($this->dir->exists()) {
                    $this->log("A different process or task has already created " . $this->dir->getAbsolutePath());

                    return;
                }
                $msg = "Directory " . $this->dir->getAbsolutePath(
                    ) . " creation was not successful for an unknown reason";
                throw new BuildException($msg, $this->location);
            }
            $this->log("Created dir: " . $this->dir->getAbsolutePath());
        }
    }

    /**
     * The directory to create; required.
     * @param PhingFile $dir
     * @return void
     */
    public function setDir(PhingFile $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Sets mode to create directory with
     * @param mixed $mode
     * @return void
     */
    public function setMode($mode)
    {
        $this->mode = base_convert((int) $mode, 8, 10);
    }
}
