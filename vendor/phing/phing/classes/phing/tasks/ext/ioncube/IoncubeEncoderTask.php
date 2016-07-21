<?php
/**
 * $Id: 03f95f42b0965ac16a78438fbc7e77c9eaf89e9c $
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
 * Invokes the ionCube Encoder (PHP4 or PHP5)
 *
 * @author Michiel Rook <mrook@php.net>
 * @author Andrew Eddie <andrew.eddie@jamboworks.com>
 * @author Domenico Sgarbossa <sbraaaa@yahoo.it>
 * @version $Id: 03f95f42b0965ac16a78438fbc7e77c9eaf89e9c $
 * @package phing.tasks.ext.ioncube
 * @since 2.2.0
 */
class IoncubeEncoderTask extends Task
{
    private $ionSwitches = array();

    private $ionOptions = array();

    private $ionOptionsXS = array();

    private $comments = array();

    private $encoderName = 'ioncube_encoder';

    private $fromDir = '';

    private $ioncubePath = '/usr/local/ioncube';

    private $phpVersion = '5';

    private $targetOption = '';

    private $toDir = '';

    private $showCommandLine = false;

    /**
     * Sets whether to show command line before it is executed
     * @param $value
     */
    public function setShowCommandLine($value)
    {
        $this->showCommandLine = $value;
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
     * Sets the allowed server
     * @param $value
     */
    public function setAllowedServer($value)
    {
        $this->ionOptionsXS['allowed-server'] = $value;
    }

    /**
     * Returns the allowed server setting
     */
    public function getAllowedServer()
    {
        return $this->ionOptionsXS['allowed-server'];
    }

    /**
     * Sets the binary option
     * @param $value
     */
    public function setBinary($value)
    {
        $this->ionSwitches['binary'] = $value;
    }

    /**
     * Returns the binary option
     */
    public function getBinary()
    {
        return $this->ionSwitches['binary'];
    }

    /**
     * Sets files or folders to copy (separated by space)
     * @param $value
     */
    public function setCopy($value)
    {
        $this->ionOptionsXS['copy'] = $value;
    }

    /**
     * Returns the copy setting
     */
    public function getCopy()
    {
        return $this->ionOptionsXS['copy'];
    }

    /**
     * Sets additional file patterns, files or directories to encode,
     * or to reverse the effect of copy (separated by space)
     * @param $value
     */
    public function setEncode($value)
    {
        $this->ionOptionsXS['encode'] = $value;
    }

    /**
     * Returns the encode setting
     */
    public function getEncode()
    {
        return $this->ionOptionsXS['encode'];
    }

    /**
     * Sets regexps of additional files to encrypt (separated by space)
     * @param $value
     */
    public function setEncrypt($value)
    {
        $this->ionOptionsXS['encrypt'] = $value;
    }

    /**
     * Returns regexps of additional files to encrypt (separated by space)
     */
    public function getEncrypt()
    {
        return $this->ionOptionsXS['encrypt'];
    }

    /**
     * Sets a period after which the files expire
     * @param $value
     */
    public function setExpirein($value)
    {
        $this->ionOptions['expire-in'] = $value;
    }

    /**
     * Returns the expireIn setting
     */
    public function getExpirein()
    {
        return $this->ionOptions['expire-in'];
    }

    /**
     * Sets a YYYY-MM-DD date to expire the files
     * @param $value
     */
    public function setExpireon($value)
    {
        $this->ionOptions['expire-on'] = $value;
    }

    /**
     * Returns the expireOn setting
     */
    public function getExpireon()
    {
        return $this->ionOptions['expire-on'];
    }

    /**
     * Sets the source directory
     * @param $value
     */
    public function setFromDir($value)
    {
        $this->fromDir = $value;
    }

    /**
     * Returns the source directory
     */
    public function getFromDir()
    {
        return $this->fromDir;
    }

    /**
     * Set files and directories to ignore entirely and exclude from the target directory
     * (separated by space).
     * @param $value
     */
    public function setIgnore($value)
    {
        $this->ionOptionsXS['ignore'] = $value;
    }

    /**
     * Returns the ignore setting
     */
    public function getIgnore()
    {
        return $this->ionOptionsXS['ignore'];
    }

    /**
     * Sets the path to the ionCube encoder
     * @param $value
     */
    public function setIoncubePath($value)
    {
        $this->ioncubePath = $value;
    }

    /**
     * Returns the path to the ionCube encoder
     */
    public function getIoncubePath()
    {
        return $this->ioncubePath;
    }

    /**
     * Set files and directories not to be ignored (separated by space).
     * @param $value
     */
    public function setKeep($value)
    {
        $this->ionOptionsXS['keep'] = $value;
    }

    /**
     * Returns the ignore setting
     */
    public function getKeep()
    {
        return $this->ionOptionsXS['keep'];
    }

    /**
     * Sets the path to the license file to use
     * @param $value
     */
    public function setLicensePath($value)
    {
        $this->ionOptions['with-license'] = $value;
    }

    /**
     * Returns the path to the license file to use
     */
    public function getLicensePath()
    {
        return $this->ionOptions['with-license'];
    }

    /**
     * Sets the no-doc-comments option
     * @param $value
     */
    public function setNoDocComments($value)
    {
        $this->ionSwitches['no-doc-comment'] = $value;
    }

    /**
     * Returns the no-doc-comments option
     */
    public function getNoDocComments()
    {
        return $this->ionSwitches['no-doc-comment'];
    }

    /**
     * Sets the obfuscate option
     * @param $value
     */
    public function setObfuscate($value)
    {
        $this->ionOptionsXS['obfuscate'] = $value;
    }

    /**
     * Returns the optimize option
     */
    public function getObfuscate()
    {
        return $this->ionOptionsXS['obfuscate'];
    }

    /**
     * Sets the obfuscation key (required if using the obfuscate option)
     * @param $value
     */
    public function setObfuscationKey($value)
    {
        $this->ionOptions['obfuscation-key'] = $value;
    }

    /**
     * Returns the optimize option
     */
    public function getObfuscationKey()
    {
        return $this->ionOptions['obfuscation-key'];
    }

    /**
     * Sets the optimize option
     * @param $value
     */
    public function setOptimize($value)
    {
        $this->ionOptions['optimize'] = $value;
    }

    /**
     * Returns the optimize option
     */
    public function getOptimize()
    {
        return $this->ionOptions['optimize'];
    }

    /**
     * Sets the passphrase to use when encoding files
     * @param $value
     */
    public function setPassPhrase($value)
    {
        $this->ionOptions['passphrase'] = $value;
    }

    /**
     * Returns the passphrase to use when encoding files
     */
    public function getPassPhrase()
    {
        return $this->ionOptions['passphrase'];
    }

    /**
     * Sets the version of PHP to use (defaults to 5)
     * @param $value
     */
    public function setPhpVersion($value)
    {
        $this->phpVersion = $value;
    }

    /**
     * Returns the version of PHP to use (defaults to 5)
     */
    public function getPhpVersion()
    {
        return $this->phpVersion;
    }

    /**
     * Sets the target directory
     * @param $value
     */
    public function setToDir($value)
    {
        $this->toDir = $value;
    }

    /**
     * Returns the target directory
     */
    public function getToDir()
    {
        return $this->toDir;
    }

    /**
     * Sets the without-runtime-loader-support option
     * @param $value
     */
    public function setWithoutRuntimeLoaderSupport($value)
    {
        $this->ionSwitches['without-runtime-loader-support'] = $value;
    }

    /**
     * Returns the without-runtime-loader-support option
     */
    public function getWithoutRuntimeLoaderSupport()
    {
        return $this->ionSwitches['without-runtime-loader-support'];
    }

    /**
     * Sets the no-short-open-tags option
     * @param $value
     */
    public function setNoShortOpenTags($value)
    {
        $this->ionSwitches['no-short-open-tags'] = $value;
    }

    /**
     * Returns the no-short-open-tags option
     */
    public function getNoShortOpenTags()
    {
        return $this->ionSwitches['no-short-open-tags'];
    }

    /**
     * Sets the ignore-deprecated-warnings option
     * @param $value
     */
    public function setIgnoreDeprecatedWarnings($value)
    {
        $this->ionSwitches['ignore-deprecated-warnings'] = $value;
    }

    /**
     * Returns the ignore-deprecated-warnings option
     */
    public function getIgnoreDeprecatedWarnings()
    {
        return $this->ionSwitches['ignore-deprecated-warnings'];
    }

    /**
     * Sets the ignore-strict-warnings option
     * @param $value
     */
    public function setIgnoreStrictWarnings($value)
    {
        $this->ionSwitches['ignore-strict-warnings'] = $value;
    }

    /**
     * Returns the ignore-strict-warnings option
     */
    public function getIgnoreStrictWarnings()
    {
        return $this->ionSwitches['ignore-strict-warnings'];
    }

    /**
     * Sets the allow-encoding-into-source option
     * @param $value
     */
    public function setAllowEncodingIntoSource($value)
    {
        $this->ionSwitches['allow-encoding-into-source'] = $value;
    }

    /**
     * Returns the allow-encoding-into-source option
     */
    public function getAllowEncodingIntoSource()
    {
        return $this->ionSwitches['allow-encoding-into-source'];
    }

    /**
     * Sets the message-if-no-loader option
     * @param $value
     */
    public function setMessageIfNoLoader($value)
    {
        $this->ionOptions['message-if-no-loader'] = $value;
    }

    /**
     * Returns the message-if-no-loader option
     */
    public function getMessageIfNoLoader()
    {
        return $this->ionOptions['message-if-no-loader'];
    }

    /**
     * Sets the action-if-no-loader option
     * @param $value
     */
    public function setActionIfNoLoader($value)
    {
        $this->ionOptions['action-if-no-loader'] = $value;
    }

    /**
     * Returns the action-if-no-loader option
     */
    public function getActionIfNoLoader()
    {
        return $this->ionOptions['action-if-no-loader'];
    }

    /**
     * Sets the option to use when encoding target directory already exists (defaults to none)
     * @param $targetOption
     */
    public function setTargetOption($targetOption)
    {
        $this->targetOption = $targetOption;
    }

    /**
     * Returns the option to use when encoding target directory already exists (defaults to none)
     */
    public function getTargetOption()
    {
        return $this->targetOption;
    }

    /**
     * Sets the callback-file option
     * @param $value
     */
    public function setCallbackFile($value)
    {
        $this->ionOptions['callback-file'] = $value;
    }

    /**
     * Returns the callback-file option
     */
    public function getCallbackFile()
    {
        return $this->ionOptions['callback-file'];
    }

    /**
     * Sets the obfuscation-exclusions-file option
     * @param $value
     */
    public function setObfuscationExclusionFile($value)
    {
        $this->ionOptions['obfuscation-exclusion-file'] = $value;
    }

    /**
     * Returns the obfuscation-exclusions-file option
     */
    public function getObfuscationExclusionFile()
    {
        return $this->ionOptions['obfuscation-exclusion-file'];
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $arguments = $this->constructArguments();

        if (in_array($this->phpVersion, array(5, 53, 54, 55, 56))) {
            $encoderName = $this->encoderName . $this->phpVersion;
        } else {
            $encoderName = $this->encoderName;
        }
        $encoder = new PhingFile($this->ioncubePath, $encoderName);

        $this->log("Running ionCube Encoder...");

        if ($this->showCommandLine) {
            $this->log("Command line: " . $encoder->__toString() . ' ' . $arguments);
        }

        exec($encoder->__toString() . ' ' . $arguments . " 2>&1", $output, $return);

        if ($return != 0) {
            throw new BuildException("Could not execute ionCube Encoder: " . implode(' ', $output));
        }
    }

    /**
     * Constructs an argument string for the ionCube encoder
     */
    private function constructArguments()
    {
        $arguments = '';

        foreach ($this->ionSwitches as $name => $value) {
            if ($value) {
                $arguments .= "--$name ";
            }
        }

        foreach ($this->ionOptions as $name => $value) {
            /**
             * action-if-no-loader value is a php source snippet so it is
             * better to handle it this way to prevent quote problems!
             */
            if ($name == 'action-if-no-loader') {
                $arguments .= "--$name \"$value\" ";
            } else {
                $arguments .= "--$name '$value' ";
            }
        }

        foreach ($this->ionOptionsXS as $name => $value) {
            foreach (explode(' ', $value) as $arg) {
                $arguments .= "--$name '$arg' ";
            }
        }

        foreach ($this->comments as $comment) {
            $arguments .= "--add-comment '" . $comment->getValue() . "' ";
        }

        if (!empty($this->targetOption)) {
            switch ($this->targetOption) {
                case "replace":
                case "merge":
                case "update":
                case "rename":
                {
                    $arguments .= "--" . $this->targetOption . "-target ";
                }
                    break;

                default:
                    {
                    throw new BuildException("Unknown target option '" . $this->targetOption . "'");
                    }
                    break;
            }
        }

        if ($this->fromDir != '') {
            $arguments .= $this->fromDir . ' ';
        }

        if ($this->toDir != '') {
            $arguments .= "-o " . $this->toDir . ' ';
        }

        return $arguments;
    }
}
