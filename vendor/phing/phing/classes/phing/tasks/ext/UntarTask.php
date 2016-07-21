<?php
/*
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

require_once 'phing/tasks/ext/ExtractBaseTask.php';

/**
 * Extracts one or several tar archives using PEAR Archive_Tar
 *
 * @author    Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @version   $Id: a7c20154729a165ba27135dfc3db76a96a11908b $
 * @package   phing.tasks.ext
 * @since     2.2.0
 */
class UntarTask extends ExtractBaseTask
{

    /**
     * @var bool
     */
    private $preservePermissions = false;

    /**
     * @param bool $preservePermissions
     */
    public function setPreservePermissions($preservePermissions)
    {
        $this->preservePermissions = $preservePermissions;
    }

    /**
     * Ensures that PEAR lib exists.
     */
    public function init()
    {
        include_once 'Archive/Tar.php';
        if (!class_exists('Archive_Tar')) {
            throw new BuildException("You must have installed the PEAR Archive_Tar class in order to use UntarTask.");
        }
    }

    /**
     * @param PhingFile $tarfile
     * @return mixed|void
     * @throws BuildException
     */
    protected function extractArchive(PhingFile $tarfile)
    {
        $this->log(
            "Extracting tar file: " . $tarfile->__toString() . ' to ' . $this->todir->__toString(),
            Project::MSG_INFO
        );

        try {
            $tar = $this->initTar($tarfile);
            if (!$tar->extractModify($this->todir->getAbsolutePath(), $this->removepath, $this->preservePermissions)) {
                throw new BuildException('Failed to extract tar file: ' . $tarfile->getAbsolutePath() . '. Error: ' . $tar->error_object->getMessage());
            }
        } catch (IOException $ioe) {
            $msg = "Could not extract tar file: " . $ioe->getMessage();
            throw new BuildException($msg, $ioe, $this->getLocation());
        }
    }

    /**
     * @param PhingFile $tarfile
     * @return array|int
     */
    protected function listArchiveContent(PhingFile $tarfile)
    {
        $tar = $this->initTar($tarfile);

        return $tar->listContent();
    }

    /**
     * Init a Archive_Tar class with correct compression for the given file.
     *
     * @param  PhingFile   $tarfile
     * @return Archive_Tar the tar class instance
     */
    private function initTar(PhingFile $tarfile)
    {
        $compression = null;
        $tarfileName = $tarfile->getName();
        $mode = strtolower(substr($tarfileName, strrpos($tarfileName, '.')));

        $compressions = array(
            'gz' => array('.gz', '.tgz',),
            'bz2' => array('.bz2',),
        );
        foreach ($compressions as $algo => $ext) {
            if (array_search($mode, $ext) !== false) {
                $compression = $algo;
                break;
            }
        }

        return new Archive_Tar($tarfile->getAbsolutePath(), $compression);
    }
}
