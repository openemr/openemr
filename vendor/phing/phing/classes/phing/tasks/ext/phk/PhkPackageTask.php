<?php
/**
 * $Id: 70e48a7d52be99d566ed1d9ad86d1dc5bd9add70 $
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
require_once 'phing/tasks/ext/phk/PhkPackageWebAccess.php';

/**
 * See {@link http://phk.tekwire.net/} for more information about PHK.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package phing.tasks.ext.phk
 */
class PhkPackageTask extends Task
{
    /**
     * @var string
     */
    private $outputFile;
    /**
     * @var string
     */
    private $inputDirectory;
    /**
     * @var string
     */
    private $phkCreatorPath;
    /**
     * @var PhkPackageWebAccess
     */
    private $webAccess;
    /**
     * @var array
     */
    private $modifiers = array();
    /**
     * @var array
     */
    private $options = array();

    /**
     * @return PhkPackageWebAccess
     */
    public function createWebAccess()
    {
        return ($this->webAccess = new PhkPackageWebAccess());
    }

    /**
     * @param string $crcCheck
     */
    public function setCrcCheck($crcCheck)
    {
        $this->options['crc_check'] = ('true' == $crcCheck ? true : false);
    }

    /**
     * @param string $webRunScript
     */
    public function setWebRunScript($webRunScript)
    {
        $this->options['web_run_script'] = $webRunScript;
    }

    /**
     * @param string $cliRunScript
     */
    public function setCliRunScript($cliRunScript)
    {
        $this->options['cli_run_script'] = $cliRunScript;
    }

    /**
     * @param string $libRunScript
     */
    public function setLibRunScript($libRunScript)
    {
        $this->options['lib_run_script'] = $libRunScript;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->options['name'] = $name;
    }

    /**
     * @param string $webMainRedirect
     */
    public function setWebMainRedirect($webMainRedirect)
    {
        $this->options['web_main_redirect'] = ('true' == $webMainRedirect ? true : false);
    }

    /**
     * @param string $pluginClass
     */
    public function setPluginClass($pluginClass)
    {
        $this->options['plugin_class'] = $pluginClass;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->options['version'] = $version;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->options['summary'] = $summary;
    }

    /**
     * @param string $inputDirectory
     */
    public function setInputDirectory($inputDirectory)
    {
        $this->inputDirectory = $inputDirectory;
    }

    /**
     * @param string $outputFile
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     * May be none, gzip or bzip2.
     *
     * @param string $compress
     */
    public function setCompress($compress)
    {
        $this->modifiers['compress'] = $compress;
    }

    /**
     * True or false.
     *
     * @param srting $strip
     */
    public function setStrip($strip)
    {
        $this->modifiers['strip'] = $strip;
    }

    /**
     * Path to PHK_Creator.phk file.
     *
     * @param srting $path
     */
    public function setPhkCreatorPath($path)
    {
        $this->phkCreatorPath = $path;
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * Main method...
     */
    public function main()
    {
        /*
         * Check for empty first - speed ;)
         */
        if (!is_file($this->phkCreatorPath)) {
            throw new BuildException('You must specify the "phkcreatorpath" attribute for PHK task.');
        }
        if (empty($this->inputDirectory)) {
            throw new BuildException('You must specify the "inputdirectory" attribute for PHK task.');
        }
        if (empty($this->outputFile)) {
            throw new BuildException('You must specify the "outputfile" attribute for PHK task.');
        }

        require_once $this->phkCreatorPath;

        $mountPoint = PHK_Mgr::mount($this->outputFile, PHK::F_CREATOR);
        $phkManager = PHK_Mgr::instance($mountPoint);

        /*
         * Add files.
         */
        $phkManager->ftree()->merge_file_tree('/', $this->inputDirectory, $this->modifiers);

        /*
         * Add web_access to options, if present.
         */
        if (!is_null($this->webAccess)) {
            $webAccessPaths = $this->webAccess->getPaths();
            if (!empty($webAccessPaths)) {
                $this->options['web_access'] = $webAccessPaths;
            }
        }

        $phkManager->set_options($this->options);

        /*
         * Intercept output (in PHP we can't intercept stream).
         */
        ob_start();
        /*
         * Create file...
         */
        $phkManager->dump();
        /*
         * Print with Phing log...
         */
        $output = trim(ob_get_clean());
        $output = explode("\n", $output);
        foreach ($output as $line) {
            /*
             * Delete all '--- *' lines. Bluh!
             */
            /*
             * TODO Change preg_math to more faster alternative.
             */
            if (preg_match('/^---/', $line)) {
                continue;
            }

            $this->log($line);
        }

        /*
         * Set rights for generated file... Don't use umask() - see
         * notes in official documentation for this function.
         */
        chmod($this->outputFile, 0644);
    }
}
