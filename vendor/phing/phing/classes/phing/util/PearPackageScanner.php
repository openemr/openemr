<?php
/**
 * Part of phing, the PHP build tool
 *
 * PHP version 5
 *
 * @category Util
 * @package  phing.util
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @version  SVN: $Id: 439ffcc0d1099571cd299fcebeec55c98e4a6b71 $
 * @link     http://www.phing.info/
 */
require_once 'phing/util/DirectoryScanner.php';

/**
 * Scans for files in a PEAR package.
 *
 * @category Util
 * @package  phing.util
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */
class PearPackageScanner extends DirectoryScanner
{
    protected $packageInfo;
    protected $role = 'php';
    protected $config;
    protected $package;
    protected $channel = 'pear.php.net';
    protected $packageFile;

    /**
     * Load PEAR_Config and PEAR_PackageFile
     */
    public function __construct()
    {
        @include_once 'PEAR/Config.php';
        @include_once 'PEAR/PackageFile.php';

        if (!class_exists('PEAR_Config')) {
            throw new BuildException(__CLASS__ . " requires PEAR to be installed");
        }
    }

    /**
     * Sets the package.xml file to read, instead of using the
     * local pear installation.
     *
     * @param string $descfile Name of package xml file
     *
     * @throws BuildException
     * @return void
     */
    public function setDescFile($descfile)
    {
        if ($descfile != '' && !file_exists($descfile)) {
            throw new BuildException(
                'PEAR package xml file "' . $descfile . '" does not exist'
            );
        }

        $this->packageFile = $descfile;
    }

    /**
     * Sets the name of the PEAR package to get the files from
     *
     * @param string $package Package name without channel
     *
     * @return void
     */
    public function setPackage($package)
    {
        $this->package = $package;
    }

    /**
     * Sets the name of the package channel name
     *
     * @param string $channel package channel name or alias
     *
     * @return void
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Sets the full path to the PEAR configuration file
     *
     * @param string $config Configuration file
     *
     * @throws BuildException
     * @return void
     */
    public function setConfig($config)
    {
        if ($config != '') {
            if (!file_exists($config)) {
                throw new BuildException(
                    'PEAR configuration file "' . $config . '" does not exist'
                );
            }
        } else {
            // try to auto-detect a pear local installation
            if (DIRECTORY_SEPARATOR == '/') {
                $config = '.pearrc';
            } else {
                $config = 'pear.ini';
            }
            $config = PEAR_CONFIG_DEFAULT_BIN_DIR . DIRECTORY_SEPARATOR . $config;
            if (!file_exists($config)) {
                // cannot find a pear local installation
                $config = '';
            }
        }

        $this->config = $config;
    }

    /**
     * Sets the role of files that should be included.
     * Examples are php,doc,script
     *
     * @param string $role PEAR file role
     *
     * @throws BuildException
     * @return void
     *
     * @internal
     * We do not verify the role against a hardcoded list since that
     * would break packages with additional roles.
     */
    public function setRole($role)
    {
        if ($role == '' && $this->packageFile == '') {
            throw new BuildException('A non-empty role is required');
        }

        $this->role = $role;
    }

    /**
     * Loads the package information.
     *
     * @return void
     *
     * @uses $packageInfo
     */
    protected function init()
    {
        if (!$this->packageInfo) {
            $this->packageInfo = $this->loadPackageInfo();
        }
    }

    /**
     * Loads and returns the PEAR package information.
     *
     * @return PEAR_PackageFile_v2 Package information object
     *
     * @throws BuildException When the package does not exist
     */
    protected function loadPackageInfo()
    {
        $config = PEAR_Config::singleton($this->config);

        if (empty($this->packageFile)) {
            // loads informations from PEAR package installed
            $reg = $config->getRegistry();
            if (!$reg->packageExists($this->package, $this->channel)) {
                throw new BuildException(
                    sprintf(
                        'PEAR package %s/%s does not exist',
                        $this->channel,
                        $this->package
                    )
                );
            }
            $packageInfo = $reg->getPackage($this->package, $this->channel);
        } else {
            // loads informations from PEAR package XML description file
            PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
            $pkg = new PEAR_PackageFile($config);
            $packageInfo = $pkg->fromPackageFile($this->packageFile, PEAR_VALIDATE_NORMAL);
            PEAR::staticPopErrorHandling();
            if (PEAR::isError($packageInfo)) {
                throw new BuildException("Errors in package file: " . $packageInfo->getMessage());
            }
        }

        return $packageInfo;
    }

    /**
     * Generates the list of included files and directories
     *
     * @return boolean True if all went well, false if something was wrong
     *
     * @uses $filesIncluded
     * @uses $filesDeselected
     * @uses $filesNotIncluded
     * @uses $filesExcluded
     * @uses $everythingIncluded
     * @uses $dirsIncluded
     * @uses $dirsDeselected
     * @uses $dirsNotIncluded
     * @uses $dirsExcluded
     */
    public function scan()
    {
        $this->init();
        $list = $this->packageInfo->getFilelist();

        if ($this->includes === null) {
            // No includes supplied, so set it to 'matches all'
            $this->includes = array("**");
        }
        if ($this->excludes === null) {
            $this->excludes = array();
        }

        $this->filesIncluded = array();
        $this->filesNotIncluded = array();
        $this->filesExcluded = array();
        $this->filesDeselected = array();

        $this->dirsIncluded = array();
        $this->dirsNotIncluded = array();
        $this->dirsExcluded = array();
        $this->dirsDeselected = array();
        $origFirstFile = null;

        foreach ($list as $file => $att) {
            if ($att['role'] != $this->role && $this->role != '') {
                continue;
            }
            $origFile = $file;
            if (isset($att['install-as'])) {
                $file = $att['install-as'];
            } else {
                if (isset($att['baseinstalldir'])) {
                    $file = ltrim($att['baseinstalldir'] . '/' . $file, '/');
                }
            }
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

            if ($this->isIncluded($file)) {

                if ($this->isExcluded($file)) {
                    $this->everythingIncluded = false;
                    if (@is_dir($file)) {
                        $this->dirsExcluded[] = $file;
                    } else {
                        $this->filesExcluded[] = $file;
                    }
                } else {
                    if (@is_dir($file)) {
                        $this->dirsIncluded[] = $file;
                    } else {
                        $this->filesIncluded[] = $file;
                        if ($origFirstFile === null) {
                            $origFirstFile = $origFile;
                        }
                    }
                }
            } else {
                $this->everythingIncluded = false;
                if (@is_dir($file)) {
                    $this->dirsNotIncluded[] = $file;
                } else {
                    $this->filesNotIncluded[] = $file;
                }
            }
        }

        if (count($this->filesIncluded) > 0) {
            if (empty($this->packageFile)) {
                $att = $list[$origFirstFile];
                $base_dir = substr(
                    $att['installed_as'],
                    0,
                    -strlen($this->filesIncluded[0])
                );
            } else {
                $base_dir = dirname($this->packageFile);
            }
            $this->setBaseDir($base_dir);
        }

        return true;
    }

}
