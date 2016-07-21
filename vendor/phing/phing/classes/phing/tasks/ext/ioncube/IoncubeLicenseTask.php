<?php
/**
 * $Id: 46bbb0c463665f3960cef69b836652c4f3f4c32c $
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
require_once 'phing/tasks/ext/ioncube/IoncubeComment.php';

/**
 * Invokes the ionCube "make_license" program
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 46bbb0c463665f3960cef69b836652c4f3f4c32c $
 * @package phing.tasks.ext.ioncube
 * @since 2.2.0
 */
class IoncubeLicenseTask extends Task
{
    private $ioncubePath = "/usr/local/ioncube";

    private $licensePath = "";
    private $passPhrase = "";
    private $allowedServer = "";
    private $expireOn = "";
    private $expireIn = "";
    private $comments = array();

    /**
     * Sets the path to the ionCube encoder
     * @param $ioncubePath
     */
    public function setIoncubePath($ioncubePath)
    {
        $this->ioncubePath = $ioncubePath;
    }

    /**
     * Returns the path to the ionCube encoder
     */
    public function getIoncubePath()
    {
        return $this->ioncubePath;
    }

    /**
     * Sets the path to the license file to use
     * @param $licensePath
     */
    public function setLicensePath($licensePath)
    {
        $this->licensePath = $licensePath;
    }

    /**
     * Returns the path to the license file to use
     */
    public function getLicensePath()
    {
        return $this->licensePath;
    }

    /**
     * Sets the passphrase to use when encoding files
     * @param $passPhrase
     */
    public function setPassPhrase($passPhrase)
    {
        $this->passPhrase = $passPhrase;
    }

    /**
     * Returns the passphrase to use when encoding files
     */
    public function getPassPhrase()
    {
        return $this->passPhrase;
    }

    /**
     * Adds a comment to be used in encoded files
     * @param IoncubeComment $comment
     */
    public function addComment(IoncubeComment $comment)
    {
        $this->comments[] = $comment;
    }

    /**
     * Sets the --allowed-server option to use when generating the license
     * @param $allowedServer
     */
    public function setAllowedServer($allowedServer)
    {
        $this->allowedServer = $allowedServer;
    }

    /**
     * Returns the --allowed-server option
     */
    public function getAllowedServer()
    {
        return $this->allowedServer;
    }

    /**
     * Sets the --expire-on option to use when generating the license
     * @param $expireOn
     */
    public function setExpireOn($expireOn)
    {
        $this->expireOn = $expireOn;
    }

    /**
     * Returns the --expire-on option
     */
    public function getExpireOn()
    {
        return $this->expireOn;
    }

    /**
     * Sets the --expire-in option to use when generating the license
     * @param $expireIn
     */
    public function setExpireIn($expireIn)
    {
        $this->expireIn = $expireIn;
    }

    /**
     * Returns the --expire-in option
     */
    public function getExpireIn()
    {
        return $this->expireIn;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $arguments = $this->constructArguments();

        $makelicense = new PhingFile($this->ioncubePath, 'make_license');

        $this->log("Running ionCube make_license...");

        exec($makelicense->__toString() . " " . $arguments . " 2>&1", $output, $return);

        if ($return != 0) {
            throw new BuildException("Could not execute ionCube make_license: " . implode(' ', $output));
        }
    }

    /**
     * Constructs an argument string for the ionCube make_license
     */
    private function constructArguments()
    {
        $arguments = "";

        if (!empty($this->passPhrase)) {
            $arguments .= "--passphrase '" . $this->passPhrase . "' ";
        }

        foreach ($this->comments as $comment) {
            $arguments .= "--header-line '" . $comment->getValue() . "' ";
        }

        if (!empty($this->licensePath)) {
            $arguments .= "--o '" . $this->licensePath . "' ";
        }

        if (!empty($this->allowedServer)) {
            $arguments .= "--allowed-server {" . $this->allowedServer . "} ";
        }

        if (!empty($this->expireOn)) {
            $arguments .= "--expire-on " . $this->expireOn . " ";
        }

        if (!empty($this->expireIn)) {
            $arguments .= "--expire-in " . $this->expireIn . " ";
        }

        return $arguments;
    }
}
