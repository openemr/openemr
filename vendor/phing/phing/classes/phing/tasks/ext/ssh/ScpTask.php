<?php
/*
 *  $Id: 34d699a555092a5473d0636c5e45232dc2019c1d $
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

/**
 * Copy files to and from a remote host using scp.
 *
 * @author    Michiel Rook <mrook@php.net>
 * @author    Johan Van den Brande <johan@vandenbrande.com>
 * @version   $Id: 34d699a555092a5473d0636c5e45232dc2019c1d $
 * @package   phing.tasks.ext
 */

class ScpTask extends Task
{
    protected $file = "";
    protected $filesets = array(); // all fileset objects assigned to this task
    protected $todir = "";
    protected $mode = null;

    protected $host = "";
    protected $port = 22;
    protected $methods = null;
    protected $username = "";
    protected $password = "";
    protected $autocreate = true;
    protected $fetch = false;
    protected $localEndpoint = "";
    protected $remoteEndpoint = "";

    protected $pubkeyfile = '';
    protected $privkeyfile = '';
    protected $privkeyfilepassphrase = '';

    protected $connection = null;
    protected $sftp = null;

    protected $counter = 0;

    protected $logLevel = Project::MSG_VERBOSE;

    /**
     * If number of success of "sftp" is grater than declared number
     * decide to skip "scp" operation.
     *
     * @var int
     */
    protected $heuristicDecision = 5;

    /**
     * Indicate number of failures in sending files via "scp" over "sftp"
     *
     * - If number is negative - scp & sftp failed
     * - If number is positive - scp failed & sftp succeed
     * - If number is 0 - scp succeed
     *
     * @var integer
     */
    protected $heuristicScpSftp = 0;

    /**
     * Sets the remote host
     * @param $h
     */
    public function setHost($h)
    {
        $this->host = $h;
    }

    /**
     * Returns the remote host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the remote host port
     * @param $p
     */
    public function setPort($p)
    {
        $this->port = $p;
    }

    /**
     * Returns the remote host port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the mode value
     * @param $value
     */
    public function setMode($value)
    {
        $this->mode = $value;
    }

    /**
     * Returns the mode value
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets the username of the user to scp
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Returns the username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the password of the user to scp
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the public key file of the user to scp
     * @param $pubkeyfile
     */
    public function setPubkeyfile($pubkeyfile)
    {
        $this->pubkeyfile = $pubkeyfile;
    }

    /**
     * Returns the pubkeyfile
     */
    public function getPubkeyfile()
    {
        return $this->pubkeyfile;
    }

    /**
     * Sets the private key file of the user to scp
     * @param $privkeyfile
     */
    public function setPrivkeyfile($privkeyfile)
    {
        $this->privkeyfile = $privkeyfile;
    }

    /**
     * Returns the private keyfile
     */
    public function getPrivkeyfile()
    {
        return $this->privkeyfile;
    }

    /**
     * Sets the private key file passphrase of the user to scp
     * @param $privkeyfilepassphrase
     */
    public function setPrivkeyfilepassphrase($privkeyfilepassphrase)
    {
        $this->privkeyfilepassphrase = $privkeyfilepassphrase;
    }

    /**
     * Returns the private keyfile passphrase
     * @param $privkeyfilepassphrase
     * @return string
     */
    public function getPrivkeyfilepassphrase($privkeyfilepassphrase)
    {
        return $this->privkeyfilepassphrase;
    }

    /**
     * Sets whether to autocreate remote directories
     * @param $autocreate
     */
    public function setAutocreate($autocreate)
    {
        $this->autocreate = (bool) $autocreate;
    }

    /**
     * Returns whether to autocreate remote directories
     */
    public function getAutocreate()
    {
        return $this->autocreate;
    }

    /**
     * Set destination directory
     * @param $todir
     */
    public function setTodir($todir)
    {
        $this->todir = $todir;
    }

    /**
     * Returns the destination directory
     */
    public function getTodir()
    {
        return $this->todir;
    }

    /**
     * Sets local filename
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Returns local filename
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets whether to send (default) or fetch files
     * @param $fetch
     */
    public function setFetch($fetch)
    {
        $this->fetch = (bool) $fetch;
    }

    /**
     * Returns whether to send (default) or fetch files
     */
    public function getFetch()
    {
        return $this->fetch;
    }

    /**
     * Declare number of successful operations above which "sftp" will be chosen over "scp".
     *
     * @param int $heuristicDecision Number
     */
    public function setHeuristicDecision($heuristicDecision)
    {
        $this->heuristicDecision = (int) $heuristicDecision;
    }

    /**
     * Get declared number of successful operations above which "sftp" will be chosen over "scp".
     *
     * @return int
     */
    public function getHeuristicDecision()
    {
        return $this->heuristicDecision;
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

    /**
     * Creates an Ssh2MethodParam object. Handles the <sshconfig /> nested tag
     * @return Ssh2MethodParam
     */
    public function createSshconfig()
    {
        $this->methods = new Ssh2MethodParam();

        return $this->methods;
    }

    /**
     * Set level of log messages generated (default = verbose)
     * @param string $level
     */
    public function setLevel($level)
    {
        switch ($level) {
            case "error":
                $this->logLevel = Project::MSG_ERR;
                break;
            case "warning":
                $this->logLevel = Project::MSG_WARN;
                break;
            case "info":
                $this->logLevel = Project::MSG_INFO;
                break;
            case "verbose":
                $this->logLevel = Project::MSG_VERBOSE;
                break;
            case "debug":
                $this->logLevel = Project::MSG_DEBUG;
                break;
        }
    }

    public function init()
    {
    }

    public function main()
    {
        $p = $this->getProject();

        if (!function_exists('ssh2_connect')) {
            throw new BuildException("To use ScpTask, you need to install the PHP SSH2 extension.");
        }

        if ($this->file == "" && empty($this->filesets)) {
            throw new BuildException("Missing either a nested fileset or attribute 'file'");
        }

        if ($this->host == "" || $this->username == "") {
            throw new BuildException("Attribute 'host' and 'username' must be set");
        }

        $methods = !empty($this->methods) ? $this->methods->toArray($p) : array();
        $this->connection = ssh2_connect($this->host, $this->port, $methods);
        if (!$this->connection) {
            throw new BuildException("Could not establish connection to " . $this->host . ":" . $this->port . "!");
        }

        $could_auth = null;
        if ($this->pubkeyfile) {
            $could_auth = ssh2_auth_pubkey_file(
                $this->connection,
                $this->username,
                $this->pubkeyfile,
                $this->privkeyfile,
                $this->privkeyfilepassphrase
            );
        } else {
            $could_auth = ssh2_auth_password($this->connection, $this->username, $this->password);
        }
        if (!$could_auth) {
            throw new BuildException("Could not authenticate connection!");
        }

        // prepare sftp resource
        if ($this->autocreate) {
            $this->sftp = ssh2_sftp($this->connection);
        }

        if ($this->file != "") {
            $this->copyFile($this->file, basename($this->file));
        } else {
            if ($this->fetch) {
                throw new BuildException("Unable to use filesets to retrieve files from remote server");
            }

            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($this->project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getPath();
                foreach ($files as $file) {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;

                    // Translate any Windows paths
                    $this->copyFile($path, strtr($file, '\\', '/'));
                }
            }
        }

        $this->log(
            "Copied " . $this->counter . " file(s) " . ($this->fetch ? "from" : "to") . " '" . $this->host . "'"
        );

        // explicitly close ssh connection
        @ssh2_exec($this->connection, 'exit');
    }

    /**
     * @param $local
     * @param $remote
     * @throws BuildException
     */
    protected function copyFile($local, $remote)
    {
        $path = rtrim($this->todir, "/") . "/";

        if ($this->fetch) {
            $localEndpoint = $path . $remote;
            $remoteEndpoint = $local;

            $this->log('Will fetch ' . $remoteEndpoint . ' to ' . $localEndpoint, $this->logLevel);

            $ret = @ssh2_scp_recv($this->connection, $remoteEndpoint, $localEndpoint);

            if ($ret === false) {
                throw new BuildException("Could not fetch remote file '" . $remoteEndpoint . "'");
            }
        } else {
            $localEndpoint = $local;
            $remoteEndpoint = $path . $remote;

            if ($this->autocreate) {
                ssh2_sftp_mkdir(
                    $this->sftp,
                    dirname($remoteEndpoint),
                    (is_null($this->mode) ? 0777 : $this->mode),
                    true
                );
            }

            $this->log('Will copy ' . $localEndpoint . ' to ' . $remoteEndpoint, $this->logLevel);

            $ret = false;
            // If more than "$this->heuristicDecision" successfully send files by "ssh2.sftp" over "ssh2_scp_send"
            // then ship this step (task finish ~40% faster)
            if ($this->heuristicScpSftp < $this->heuristicDecision) {
                if (null !== $this->mode) {
                    $ret = @ssh2_scp_send($this->connection, $localEndpoint, $remoteEndpoint, $this->mode);
                } else {
                    $ret = @ssh2_scp_send($this->connection, $localEndpoint, $remoteEndpoint);
                }
            }

            // sometimes remote server allow only create files via sftp (eg. phpcloud.com)
            if (false === $ret && $this->sftp) {
                // mark failure of "scp"
                --$this->heuristicScpSftp;

                // try create file via ssh2.sftp://file wrapper
                $fh = @fopen("ssh2.sftp://$this->sftp/$remoteEndpoint", 'wb');
                if (is_resource($fh)) {
                    $ret = fwrite($fh, file_get_contents($localEndpoint));
                    fclose($fh);

                    // mark success of "sftp"
                    $this->heuristicScpSftp += 2;
                }
            }

            if ($ret === false) {
                throw new BuildException("Could not create remote file '" . $remoteEndpoint . "'");
            }
        }

        $this->counter++;
    }
}
