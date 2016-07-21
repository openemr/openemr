<?php

/*
 *  $Id: b020d667f8bb1d09d43607ffaa4ff6e33bd91dbd $
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
include_once 'phing/types/Reference.php';

/**
 * Handles Creole configuration needed by SQL type tasks.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Nick Chalko <nick@chalko.com> (Ant)
 * @author    Jeff Martin <jeff@custommonkey.org> (Ant)
 * @author    Michael McCallum <gholam@xtra.co.nz> (Ant)
 * @author    Tim Stephenson <tim.stephenson@sybase.com> (Ant)
 * @version   $Id: b020d667f8bb1d09d43607ffaa4ff6e33bd91dbd $
 * @package   phing.tasks.system
 */
abstract class CreoleTask extends Task
{

    /**
     * Used for caching loaders / driver. This is to avoid
     * getting an OutOfMemoryError when calling this task
     * multiple times in a row.
     *
     * NOT IMPLEMENTED YET
     */
    private static $loaderMap = array();

    private $caching = true;

    /**
     * Autocommit flag. Default value is false
     */
    private $autocommit = false;

    /**
     * [optional] Classpath to Creole driver to use.
     * @param string
     */
    private $driver;

    /**
     * DB url.
     */
    private $url;

    /**
     * User name.
     */
    private $userId;

    /**
     * Password
     */
    private $password;

    /**
     * RDBMS Product needed for this SQL.
     **/
    private $rdbms;

    /**
     * Initialize CreoleTask.
     * This method includes any necessary Creole libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     */
    public function init()
    {
        include_once 'creole/Creole.php';
        if (!class_exists('Creole')) {
            throw new Exception("Creole task depends on Creole classes being on include_path. (i.e. include of 'creole/Creole.php' failed.)");
        }
    }

    /**
     * Caching loaders / driver. This is to avoid
     * getting an OutOfMemoryError when calling this task
     * multiple times in a row; default: true
     * @param bool $enable
     */
    public function setCaching($enable)
    {
        $this->caching = $enable;
    }

    /**
     * Sets the database connection URL; required.
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Set the Creole driver to be used.
     *
     * @param string $driver driver class name
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * Sets the password; required.
     * @param string $password The password to set
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Auto commit flag for database connection;
     * optional, default false.
     *
     * @param bool $autocommit The autocommit to set
     */
    public function setAutocommit($autocommit)
    {
        $this->autocommit = $autocommit;
    }

    /**
     * Sets the version string, execute task only if
     * rdbms version match; optional.
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    protected function getLoaderMap()
    {
        return self::$loaderMap;
    }

    /**
     * Creates a new Connection as using the driver, url, userid and password specified.
     * The calling method is responsible for closing the connection.
     *
     * @return Connection     the newly created connection.
     *
     * @throws BuildException if the UserId/Password/Url is not set or there is no suitable driver or the driver fails to load.
     */
    protected function getConnection()
    {

        if ($this->url === null) {
            throw new BuildException("Url attribute must be set!", $this->location);
        }

        try {

            $this->log("Connecting to " . $this->getUrl(), Project::MSG_VERBOSE);
            $info = new Properties();

            $dsn = Creole::parseDSN($this->url);

            if (!isset($dsn["username"]) && $this->userId === null) {
                throw new BuildException("Username must be in URL or userid attribute must be set.", $this->location);
            }

            if ($this->userId) {
                $dsn["username"] = $this->getUserId();
            }

            if ($this->password) {
                $dsn["password"] = $this->getPassword();
            }

            if ($this->driver) {
                Creole::registerDriver($dsn['phptype'], $this->driver);
            }

            $conn = Creole::getConnection($dsn);
            $conn->setAutoCommit($this->autocommit);

            return $conn;

        } catch (SQLException $e) {
            throw new BuildException($e->getMessage(), $this->location);
        }

    }

    /**
     * @param $value
     */
    public function isCaching($value)
    {
        $this->caching = $value;
    }

    /**
     * Gets the autocommit.
     * @return Returns a boolean
     */
    public function isAutocommit()
    {
        return $this->autocommit;
    }

    /**
     * Gets the url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets the userId.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user name for the connection; required.
     * @param string $userId
     */
    public function setUserid($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Gets the password.
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
