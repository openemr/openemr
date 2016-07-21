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

include_once 'phing/Task.php';

/**
 * Base class for Subversion tasks
 *
 * @author Michiel Rook <mrook@php.net>
 * @author Andrew Eddie <andrew.eddie@jamboworks.com>
 *
 * @package phing.tasks.ext.svn
 *
 * @see VersionControl_SVN
 * @since 2.2.0
 */
abstract class SvnBaseTask extends Task
{
    /**
     * @var string
     */
    private $workingCopy = "";

    /**
     * @var string
     */
    private $repositoryUrl = "";

    /**
     * @var string
     */
    private $svnPath = "/usr/bin/svn";

    protected $svn = null;

    private $mode = "";

    private $svnArgs = array();

    private $svnSwitches = array();

    private $toDir = "";

    protected $fetchMode;

    protected $oldVersion = false;

    /**
     * Initialize Task.
     * This method includes any necessary SVN libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     */
    public function init()
    {
        include_once 'VersionControl/SVN.php';
        $this->fetchMode = VERSIONCONTROL_SVN_FETCHMODE_ASSOC;
        if (!class_exists('VersionControl_SVN')) {
            throw new Exception("The SVN tasks depend on PEAR VersionControl_SVN package being installed.");
        }
    }

    /**
     * Sets the path to the workingcopy
     * @param $workingCopy
     */
    public function setWorkingCopy($workingCopy)
    {
        $this->workingCopy = $workingCopy;
    }

    /**
     * Returns the path to the workingcopy
     */
    public function getWorkingCopy()
    {
        return $this->workingCopy;
    }

    /**
     * Sets the path/URI to the repository
     * @param $repositoryUrl
     */
    public function setRepositoryUrl($repositoryUrl)
    {
        $this->repositoryUrl = $repositoryUrl;
    }

    /**
     * Returns the path/URI to the repository
     */
    public function getRepositoryUrl()
    {
        return $this->repositoryUrl;
    }

    /**
     * Sets the path to the SVN executable
     * @param $svnPath
     */
    public function setSvnPath($svnPath)
    {
        $this->svnPath = $svnPath;
    }

    /**
     * Returns the path to the SVN executable
     */
    public function getSvnPath()
    {
        return $this->svnPath;
    }

    //
    // Args
    //

    /**
     * Sets the path to export/checkout to
     * @param $toDir
     */
    public function setToDir($toDir)
    {
        $this->toDir = $toDir;
    }

    /**
     * Returns the path to export/checkout to
     */
    public function getToDir()
    {
        return $this->toDir;
    }

    //
    // Switches
    //

    /**
     * Sets the force switch
     * @param $value
     */
    public function setForce($value)
    {
        $this->svnSwitches['force'] = $value;
    }

    /**
     * Returns the force switch
     */
    public function getForce()
    {
        return isset($this->svnSwitches['force']) ? $this->svnSwitches['force'] : '';
    }

    /**
     * Sets the username of the user to export
     * @param $value
     */
    public function setUsername($value)
    {
        $this->svnSwitches['username'] = $value;
    }

    /**
     * Returns the username
     */
    public function getUsername()
    {
        return isset($this->svnSwitches['username']) ? $this->svnSwitches['username'] : '';
    }

    /**
     * Sets the password of the user to export
     * @param $value
     */
    public function setPassword($value)
    {
        $this->svnSwitches['password'] = $value;
    }

    /**
     * Returns the password
     */
    public function getPassword()
    {
        return isset($this->svnSwitches['password']) ? $this->svnSwitches['password'] : '';
    }

    /**
     * Sets the no-auth-cache switch
     * @param $value
     */
    public function setNoCache($value)
    {
        $this->svnSwitches['no-auth-cache'] = $value;
    }

    /**
     * Returns the no-auth-cache switch
     */
    public function getNoCache()
    {
        return isset($this->svnSwitches['no-auth-cache']) ? $this->svnSwitches['no-auth-cache'] : '';
    }

    /**
     * Sets the recursive switch
     * @deprecated
     * @param $value
     */
    public function setRecursive($value)
    {
    }

    /**
     * Returns the recursive switch
     * @deprecated
     */
    public function getRecursive()
    {
    }

    /**
     * Sets the depth switch
     * @param $value
     */
    public function setDepth($value)
    {
        $this->svnSwitches['depth'] = $value;
    }

    /**
     * Returns the depth switch
     */
    public function getDepth()
    {
        return isset($this->svnSwitches['depth']) ? $this->svnSwitches['depth'] : '';
    }

    /**
     * Sets the ignore-externals switch
     * @param $value
     */
    public function setIgnoreExternals($value)
    {
        $this->svnSwitches['ignore-externals'] = $value;
    }

    /**
     * Returns the ignore-externals switch
     */
    public function getIgnoreExternals()
    {
        return isset($this->svnSwitches['ignore-externals']) ? $this->svnSwitches['ignore-externals'] : '';
    }

    /**
     * Sets the trust-server-cert switch
     * @param $value
     */
    public function setTrustServerCert($value)
    {
        $this->svnSwitches['trust-server-cert'] = $value;
    }

    /**
     * Returns the trust-server-cert switch
     */
    public function getTrustServerCert()
    {
        return isset($this->svnSwitches['trust-server-cert']) ? $this->svnSwitches['trust-server-cert'] : '';
    }

    /**
     * Creates a VersionControl_SVN class based on $mode
     *
     * @param string The SVN mode to use (info, export, checkout, ...)
     * @throws BuildException
     */
    protected function setup($mode)
    {
        $this->mode = $mode;

        // Set up runtime options. Will be passed to all
        // subclasses.
        $options = array('fetchmode' => $this->fetchMode);

        if ($this->oldVersion) {
            $options['svn_path'] = $this->getSvnPath();
        } else {
            $options['binaryPath'] = $this->getSvnPath();
        }

        // Pass array of subcommands we need to factory
        $this->svn = VersionControl_SVN::factory($mode, $options);

        if (get_parent_class($this->svn) !== 'VersionControl_SVN_Command') {
            $this->oldVersion = true;
            $this->svn->use_escapeshellcmd = false;
        }

        if (!empty($this->repositoryUrl)) {
            $this->svnArgs = array($this->repositoryUrl);
        } else {
            if (!empty($this->workingCopy)) {
                if (is_dir($this->workingCopy)) {
                    $this->svnArgs = array($this->workingCopy);
                } else {
                    if ($mode == 'info') {
                        if (is_file($this->workingCopy)) {
                            $this->svnArgs = array($this->workingCopy);
                        } else {
                            throw new BuildException("'" . $this->workingCopy . "' is not a directory nor a file");
                        }
                    } else {
                        throw new BuildException("'" . $this->workingCopy . "' is not a directory");
                    }
                }
            }
        }
    }

    /**
     * Executes the constructed VersionControl_SVN instance
     *
     * @param array $args
     * @param array $switches
     * @throws BuildException
     * @internal param Additional $array arguments to pass to SVN.
     * @internal param Switches $array to pass to SVN.
     * @return string Output generated by SVN.
     */
    protected function run($args = array(), $switches = array())
    {
        $tempArgs = array_merge($this->svnArgs, $args);
        $tempSwitches = array_merge($this->svnSwitches, $switches);

        if ($this->oldVersion) {
            $svnstack = PEAR_ErrorStack::singleton('VersionControl_SVN');

            if ($output = $this->svn->run($tempArgs, $tempSwitches)) {
                return $output;
            }

            if (count($errs = $svnstack->getErrors())) {
                $err = current($errs);
                $errorMessage = $err['message'];

                if (isset($err['params']['errstr'])) {
                    $errorMessage = $err['params']['errstr'];
                }

                throw new BuildException("Failed to run the 'svn " . $this->mode . "' command: " . $errorMessage);
            }
        } else {
            try {
                return $this->svn->run($tempArgs, $tempSwitches);
            } catch (Exception $e) {
                throw new BuildException("Failed to run the 'svn " . $this->mode . "' command: " . $e->getMessage());
            }
        }
    }
}
