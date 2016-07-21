<?php
/**
 *  $Id: a85845d6c6841c7b90d9d2a4689134f88ec69d4e $
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
 * Executes a command on the shell.
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @version $Id: a85845d6c6841c7b90d9d2a4689134f88ec69d4e $
 * @package phing.tasks.system
 */
class ExecTask extends Task
{
    const INVALID = PHP_INT_MAX;

    private $exitValue = self::INVALID;

    /**
     * Command to be executed
     * @var string
     */
    protected $realCommand;

    /**
     * Given command
     * @var string
     */
    protected $command;

    /**
     * Commandline managing object
     *
     * @var Commandline
     */
    protected $commandline;

    /**
     * Working directory.
     * @var PhingFile
     */
    protected $dir;

    protected $currdir;

    /**
     * Operating system.
     * @var string
     */
    protected $os;

    /**
     * Whether to escape shell command using escapeshellcmd().
     * @var boolean
     */
    protected $escape = false;

    /**
     * Where to direct output.
     * @var PhingFile
     */
    protected $output;

    /**
     * Whether to use PHP's passthru() function instead of exec()
     * @var boolean
     */
    protected $passthru = false;

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
     * @var boolean
     */
    protected $logOutput = false;

    /**
     * Logging level for status messages
     * @var integer
     */
    protected $logLevel = Project::MSG_VERBOSE;

    /**
     * Where to direct error output.
     * @var PhingFile
     */
    protected $error;

    /**
     * If spawn is set then [unix] programs will redirect stdout and add '&'.
     * @var boolean
     */
    protected $spawn = false;

    /**
     * Property name to set with return value from exec call.
     *
     * @var string
     */
    protected $returnProperty;

    /**
     * Property name to set with output value from exec call.
     *
     * @var string
     */
    protected $outputProperty;

    /**
     * Whether to check the return code.
     * @var boolean
     */
    protected $checkreturn = false;

    /**
     *
     */
    public function __construct()
    {
        $this->commandline = new Commandline();
    }

    /**
     * Main method: wraps execute() command.
     *
     * @return void
     */
    public function main()
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->prepare();
        $this->buildCommand();
        list($return, $output) = $this->executeCommand();
        $this->cleanup($return, $output);
    }

    /**
     * Checks whether the command shall be executed
     *
     * @return boolean False if the exec command shall not be run
     */
    protected function isApplicable()
    {
        if ($this->os === null) {
            return true;
        }

        $myos = Phing::getProperty('os.name');
        $this->log('Myos = ' . $myos, Project::MSG_VERBOSE);

        if (strpos($this->os, $myos) !== false) {
            // this command will be executed only on the specified OS
            // OS matches
            return true;
        }

        $this->log(
            sprintf(
                'Operating system %s not found in %s',
                $myos,
                $this->os
            ),
            Project::MSG_VERBOSE
        );

        return false;
    }

    /**
     * Prepares the command building and execution, i.e.
     * changes to the specified directory.
     *
     * @throws BuildException
     * @return void
     */
    protected function prepare()
    {
        if ($this->dir === null) {
            return;
        }

        // expand any symbolic links first
        if (!$this->dir->getCanonicalFile()->isDirectory()) {
            throw new BuildException(
                "'" . (string) $this->dir . "' is not a valid directory"
            );
        }
        $this->currdir = getcwd();
        @chdir($this->dir->getPath());
    }

    /**
     * Builds the full command to execute and stores it in $command.
     *
     * @throws BuildException
     * @return void
     * @uses   $command
     */
    protected function buildCommand()
    {
        if ($this->command === null && $this->commandline->getExecutable() === null) {
            throw new BuildException(
                'ExecTask: Please provide "command" OR "executable"'
            );
        } else {
            if ($this->command === null) {
                $this->realCommand = Commandline::toString($this->commandline->getCommandline(), $this->escape);
            } else {
                if ($this->commandline->getExecutable() === null) {
                    $this->realCommand = $this->command;

                    //we need to escape the command only if it's specified directly
                    // commandline takes care of "executable" already
                    if ($this->escape == true) {
                        $this->realCommand = escapeshellcmd($this->realCommand);
                    }
                } else {
                    throw new BuildException(
                        'ExecTask: Either use "command" OR "executable"'
                    );
                }
            }
        }

        if ($this->error !== null) {
            $this->realCommand .= ' 2> ' . escapeshellarg($this->error->getPath());
            $this->log(
                "Writing error output to: " . $this->error->getPath(),
                $this->logLevel
            );
        }

        if ($this->output !== null) {
            $this->realCommand .= ' 1> ' . escapeshellarg($this->output->getPath());
            $this->log(
                "Writing standard output to: " . $this->output->getPath(),
                $this->logLevel
            );
        } elseif ($this->spawn) {
            $this->realCommand .= ' 1>/dev/null';
            $this->log("Sending output to /dev/null", $this->logLevel);
        }

        // If neither output nor error are being written to file
        // then we'll redirect error to stdout so that we can dump
        // it to screen below.

        if ($this->output === null && $this->error === null && $this->passthru === false) {
            $this->realCommand .= ' 2>&1';
        }

        // we ignore the spawn boolean for windows
        if ($this->spawn) {
            $this->realCommand .= ' &';
        }
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @return array array(return code, array with output)
     */
    protected function executeCommand()
    {
        $this->log("Executing command: " . $this->realCommand, $this->logLevel);

        $output = array();
        $return = null;

        if ($this->passthru) {
            passthru($this->realCommand, $return);
        } else {
            exec($this->realCommand, $output, $return);
        }

        return array($return, $output);
    }

    /**
     * Runs all tasks after command execution:
     * - change working directory back
     * - log output
     * - verify return value
     *
     * @param integer $return Return code
     * @param array $output Array with command output
     *
     * @throws BuildException
     * @return void
     */
    protected function cleanup($return, $output)
    {
        if ($this->dir !== null) {
            @chdir($this->currdir);
        }

        $outloglevel = $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE;
        foreach ($output as $line) {
            $this->log($line, $outloglevel);
        }

        if ($this->returnProperty) {
            $this->project->setProperty($this->returnProperty, $return);
        }

        if ($this->outputProperty) {
            $this->project->setProperty(
                $this->outputProperty,
                implode("\n", $output)
            );
        }

        $this->setExitValue($return);

        if ($return != 0 && $this->checkreturn) {
            throw new BuildException("Task exited with code $return");
        }
    }

    /**
     * Set the exit value.
     *
     * @param int $value exit value of the process.
     */
    protected function setExitValue($value)
    {
        $this->exitValue = $value;
    }

    /**
     * Query the exit value of the process.
     *
     * @return int the exit value or self::INVALID if no exit value has
     *             been received.
     */
    public function getExitValue()
    {
        return $this->exitValue;
    }

    /**
     * The command to use.
     *
     * @param mixed $command String or string-compatible (e.g. w/ __toString()).
     *
     * @return void
     */
    public function setCommand($command)
    {
        $this->command = "" . $command;
    }

    /**
     * The executable to use.
     *
     * @param mixed $executable String or string-compatible (e.g. w/ __toString()).
     *
     * @return void
     */
    public function setExecutable($executable)
    {
        $this->commandline->setExecutable((string) $executable);
    }

    /**
     * Whether to use escapeshellcmd() to escape command.
     *
     * @param boolean $escape If the command shall be escaped or not
     *
     * @return void
     */
    public function setEscape($escape)
    {
        $this->escape = (bool) $escape;
    }

    /**
     * Specify the working directory for executing this command.
     *
     * @param PhingFile $dir Working directory
     *
     * @return void
     */
    public function setDir(PhingFile $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Specify OS (or multiple OS) that must match in order to execute this command.
     *
     * @param string $os Operating system string (e.g. "Linux")
     *
     * @return void
     */
    public function setOs($os)
    {
        $this->os = (string) $os;
    }

    /**
     * File to which output should be written.
     *
     * @param PhingFile $f Output log file
     *
     * @return void
     */
    public function setOutput(PhingFile $f)
    {
        $this->output = $f;
    }

    /**
     * File to which error output should be written.
     *
     * @param PhingFile $f Error log file
     *
     * @return void
     */
    public function setError(PhingFile $f)
    {
        $this->error = $f;
    }

    /**
     * Whether to use PHP's passthru() function instead of exec()
     *
     * @param boolean $passthru If passthru shall be used
     *
     * @return void
     */
    public function setPassthru($passthru)
    {
        $this->passthru = (bool) $passthru;
    }

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
     *
     * @param boolean $logOutput If output shall be logged visibly
     *
     * @return void
     */
    public function setLogoutput($logOutput)
    {
        $this->logOutput = (bool) $logOutput;
    }

    /**
     * Whether to suppress all output and run in the background.
     *
     * @param boolean $spawn If the command is to be run in the background
     *
     * @return void
     */
    public function setSpawn($spawn)
    {
        $this->spawn = (bool) $spawn;
    }

    /**
     * Whether to check the return code.
     *
     * @param boolean $checkreturn If the return code shall be checked
     *
     * @return void
     */
    public function setCheckreturn($checkreturn)
    {
        $this->checkreturn = (bool) $checkreturn;
    }

    /**
     * The name of property to set to return value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setReturnProperty($prop)
    {
        $this->returnProperty = $prop;
    }

    /**
     * The name of property to set to output value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }

    /**
     * Set level of log messages generated (default = verbose)
     *
     * @param string $level Log level
     *
     * @throws BuildException
     * @return void
     */
    public function setLevel($level)
    {
        switch ($level) {
            case 'error':
                $this->logLevel = Project::MSG_ERR;
                break;
            case 'warning':
                $this->logLevel = Project::MSG_WARN;
                break;
            case 'info':
                $this->logLevel = Project::MSG_INFO;
                break;
            case 'verbose':
                $this->logLevel = Project::MSG_VERBOSE;
                break;
            case 'debug':
                $this->logLevel = Project::MSG_DEBUG;
                break;
            default:
                throw new BuildException(
                    sprintf('Unknown log level "%s"', $level)
                );
        }
    }

    /**
     * Creates a nested <arg> tag.
     *
     * @return CommandlineArgument Argument object
     */
    public function createArg()
    {
        return $this->commandline->createArgument();
    }
}
