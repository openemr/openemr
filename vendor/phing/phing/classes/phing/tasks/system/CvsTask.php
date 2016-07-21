<?php
/*
 *  $Id: c8232b68045eec9d40b1bda18c4b758c6cfbd80f $
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
include_once 'phing/tasks/system/ExecTask.php';
include_once 'phing/types/Commandline.php';

/**
 * Task for performing CVS operations.
 *
 *  NOTE: This implementation has been moved here from Cvs.java with
 *  the addition of some accessors for extensibility.  Another task
 *  can extend this with some customized output processing.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author costin@dnt.ro (Ant)
 * @author stefano@apache.org (Ant)
 * @author Wolfgang Werner <wwerner@picturesafe.de> (Ant)
 * @author Kevin Ross <kevin.ross@bredex.com> (Ant)
 * @version $Id: c8232b68045eec9d40b1bda18c4b758c6cfbd80f $
 * @package phing.tasks.system
 */
class CvsTask extends Task
{

    /**
     * Default compression level to use, if compression is enabled via
     * setCompression( true ).
     */
    const DEFAULT_COMPRESSION_LEVEL = 3;

    private $cmd;

    /**
     * List of Commandline children
     * @var array Commandline[]
     */
    private $commandlines = array();

    /**
     * the CVSROOT variable.
     */
    private $cvsRoot;

    /**
     * the CVS_RSH variable.
     */
    private $cvsRsh;

    /**
     * the package/module to check out.
     */
    private $cvsModule;

    /**
     * the default command.
     */
    private static $default_command = "checkout";

    /**
     * the CVS command to execute.
     */
    private $command = null;

    /**
     * suppress information messages.
     */
    private $quiet = false;

    /**
     * compression level to use.
     */
    private $compression = 0;

    /**
     * report only, don't change any files.
     */
    private $noexec = false;

    /**
     * CVS port
     */
    private $port = 0;

    /**
     * CVS password file
     * @var File
     */
    private $passFile = null;

    /**
     * the directory where the checked out files should be placed.
     * @var File
     */
    private $dest;

    private $error;

    private $output;

    /**
     * If true it will stop the build if cvs exits with error.
     * Default is false. (Iulian)
     * @var boolean
     */
    private $failOnError = false;

    public function init()
    {
        $this->cmd = new Commandline();
    }

    /**
     * Sets up the environment for toExecute and then runs it.
     * @param  Commandline    $toExecute
     * @throws BuildException
     */
    protected function runCommand(Commandline $toExecute)
    {

        // We are putting variables into the script's environment
        // and not removing them (!)  This should be fine, but is
        // worth remembering and testing.

        if ($this->port > 0) {
            putenv("CVS_CLIENT_PORT=" . $this->port);
        }

        // Need a better cross platform integration with <cvspass>, so
        // use the same filename.

        if ($this->passFile === null) {
            $defaultPassFile = new PhingFile(Phing::getProperty("cygwin.user.home", Phing::getProperty("user.home"))
                . DIRECTORY_SEPARATOR . ".cvspass");
            if ($defaultPassFile->exists()) {
                $this->setPassfile($defaultPassFile);
            }
        }

        if ($this->passFile !== null) {
            if ($this->passFile->isFile() && $this->passFile->canRead()) {
                putenv("CVS_PASSFILE=" . $this->passFile->__toString());
                $this->log("Using cvs passfile: " . $this->passFile->__toString(), Project::MSG_INFO);
            } elseif (!$this->passFile->canRead()) {
                $this->log(
                    "cvs passfile: " . $this->passFile->__toString()
                    . " ignored as it is not readable",
                    Project::MSG_WARN
                );
            } else {
                $this->log(
                    "cvs passfile: " . $this->passFile->__toString()
                    . " ignored as it is not a file",
                    Project::MSG_WARN
                );
            }
        }

        if ($this->cvsRsh !== null) {
            putenv("CVS_RSH=" . $this->cvsRsh);
        }

        // Use the ExecTask to handle execution of the command
        $exe = new ExecTask($this->project);
        $exe->setProject($this->project);

        //exe.setAntRun(project);
        if ($this->dest === null) {
            $this->dest = $this->project->getBaseDir();
        }

        if (!$this->dest->exists()) {
            $this->dest->mkdirs();
        }

        if ($this->output !== null) {
            $exe->setOutput($this->output);
        }

        if ($this->error !== null) {
            $exe->setError($this->error);
        }

        $exe->setDir($this->dest);

        if (is_object($toExecute)) {
            $toExecuteStr = $toExecute->__toString(); // unfortunately no more automagic for initial 5.0.0 release :(
        }

        $exe->setCommand($toExecuteStr);

        try {
            $actualCommandLine = $toExecuteStr; // we converted to string above
            $this->log($actualCommandLine, Project::MSG_INFO);
            $retCode = $exe->main();
            $this->log("retCode=" . $retCode, Project::MSG_DEBUG);
            /*Throw an exception if cvs exited with error. (Iulian)*/
            if ($this->failOnError && $retCode !== 0) {
                throw new BuildException("cvs exited with error code "
                    . $retCode
                    . PHP_EOL
                    . "Command line was ["
                    . $toExecute->describeCommand() . "]", $this->getLocation());
            }
        } catch (IOException $e) {
            if ($this->failOnError) {
                throw new BuildException($e, $this->getLocation());
            } else {
                $this->log("Caught exception: " . $e, Project::MSG_WARN);
            }
        } catch (BuildException $e) {
            if ($this->failOnError) {
                throw $e;
            } else {
                $t = $e->getCause();
                if ($t === null) {
                    $t = $e;
                }
                $this->log("Caught exception: " . $t, Project::MSG_WARN);
            }
        } catch (Exception $e) {
            if ($this->failOnError) {
                throw new BuildException($e, $this->getLocation());
            } else {
                $this->log("Caught exception: " . $e, Project::MSG_WARN);
            }
        }
    }

    /**
     *
     * @throws Exception
     * @return void
     */
    public function main()
    {

        $savedCommand = $this->getCommand();

        if ($this->getCommand() === null && empty($this->commandlines)) {
            // re-implement legacy behaviour:
            $this->setCommand(self::$default_command);
        }

        $c = $this->getCommand();
        $cloned = null;
        if ($c !== null) {
            $cloned = $this->cmd->__copy();
            $cloned->createArgument(true)->setLine($c);
            $this->addConfiguredCommandline($cloned, true);
        }

        try {
            for ($i = 0, $vecsize = count($this->commandlines); $i < $vecsize; $i++) {
                $this->runCommand($this->commandlines[$i]);
            }

            // finally    {
            if ($cloned !== null) {
                $this->removeCommandline($cloned);
            }
            $this->setCommand($savedCommand);

        } catch (Exception $e) {
            // finally {
            if ($cloned !== null) {
                $this->removeCommandline($cloned);
            }
            $this->setCommand($savedCommand);
            throw $e;
        }
    }

    /**
     * The CVSROOT variable.
     *
     * @param string $root
     */
    public function setCvsRoot($root)
    {

        // Check if not real cvsroot => set it to null
        if ($root !== null) {
            if (trim($root) == "") {
                $root = null;
            }
        }

        $this->cvsRoot = $root;
    }

    public function getCvsRoot()
    {
        return $this->cvsRoot;
    }

    /**
     * The CVS_RSH variable.
     *
     * @param rsh
     */
    public function setCvsRsh($rsh)
    {
        // Check if not real cvsrsh => set it to null
        if ($rsh !== null) {
            if (trim($rsh) == "") {
                $rsh = null;
            }
        }

        $this->cvsRsh = $rsh;
    }

    public function getCvsRsh()
    {
        return $this->cvsRsh;
    }

    /**
     * Port used by CVS to communicate with the server.
     *
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Password file to read passwords from.
     *
     * @param PhingFile $passFile
     * @internal param $passFile
     */
    public function setPassfile(PhingFile $passFile)
    {
        $this->passFile = $passFile;
    }

    /**
     * @return File
     */
    public function getPassFile()
    {
        return $this->passFile;
    }

    /**
     * The directory where the checked out files should be placed.
     *
     * @param PhingFile $dest
     */
    public function setDest(PhingFile $dest)
    {
        $this->dest = $dest;
    }

    /**
     * @return File
     */
    public function getDest()
    {
        return $this->dest;
    }

    /**
     * The package/module to operate upon.
     *
     * @param $m
     * @internal param string $p
     */
    public function setModule($m)
    {
        $this->cvsModule = $m;
    }

    public function getModule()
    {
        return $this->cvsModule;
    }

    /**
     * The tag of the package/module to operate upon.
     * @param string $p
     */
    public function setTag($p)
    {
        // Check if not real tag => set it to null
        if ($p !== null && trim($p) !== "") {
            $this->appendCommandArgument("-r");
            $this->appendCommandArgument($p);
        }
    }

    /**
     * This needs to be public to allow configuration
     *      of commands externally.
     * @param $arg
     */
    public function appendCommandArgument($arg)
    {
        $this->cmd->createArgument()->setValue($arg);
    }

    /**
     * Use the most recent revision no later than the given date.
     * @param p
     */
    public function setDate($p)
    {
        if ($p !== null && trim($p) !== "") {
            $this->appendCommandArgument("-D");
            $this->appendCommandArgument($p);
        }
    }

    /**
     * The CVS command to execute.
     * @param string $c
     */
    public function setCommand($c)
    {
        $this->command = $c;
    }

    /**
     * @return null
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * If true, suppress informational messages.
     * @param boolean $q
     */
    public function setQuiet($q)
    {
        $this->quiet = $q;
    }

    /**
     * If true, report only and don't change any files.
     *
     * @param boolean $ne
     */
    public function setNoexec($ne)
    {
        $this->noexec = (boolean) $ne;
    }

    /**
     * Stop the build process if the command exits with
     * a return code other than 0.
     * Defaults to false.
     * @param boolean $failOnError
     */
    public function setFailOnError($failOnError)
    {
        $this->failOnError = (boolean) $failOnError;
    }

    /**
     * Configure a commandline element for things like cvsRoot, quiet, etc.
     * @param $c
     * @return string
     */
    protected function configureCommandline($c)
    {
        if ($c === null) {
            return;
        }
        $c->setExecutable("cvs");

        if ($this->cvsModule !== null) {
            $c->createArgument()->setLine($this->cvsModule);
        }
        if ($this->compression > 0 && $this->compression < 10) {
            $c->createArgument(true)->setValue("-z" . $this->compression);
        }
        if ($this->quiet) {
            $c->createArgument(true)->setValue("-q");
        }
        if ($this->noexec) {
            $c->createArgument(true)->setValue("-n");
        }
        if ($this->cvsRoot !== null) {
            $c->createArgument(true)->setLine("-d" . $this->cvsRoot);
        }
    }

    /**
     * @param Commandline $c
     * @return bool
     */
    protected function removeCommandline(Commandline $c)
    {
        $idx = array_search($c, $this->commandlines, true);
        if ($idx === false) {
            return false;
        }
        $this->commandlines = array_splice($this->commandlines, $idx, 1);

        return true;
    }

    /**
     * Configures and adds the given Commandline.
     * @param Commandline $c
     * @param bool|If $insertAtStart
     * @internal param If $insertAtStart true, c is
     */
    public function addConfiguredCommandline(Commandline $c, $insertAtStart = false)
    {
        if ($c === null) {
            return;
        }
        $this->configureCommandline($c);
        if ($insertAtStart) {
            array_unshift($this->commandlines, $c);
        } else {
            array_push($this->commandlines, $c);
        }
    }

    /**
     * If set to a value 1-9 it adds -zN to the cvs command line, else
     * it disables compression.
     * @param int $level
     */
    public function setCompressionLevel($level)
    {
        $this->compression = $level;
    }

    /**
     * If true, this is the same as compressionlevel="3".
     *
     * @param boolean $usecomp If true, turns on compression using default
     *                         level, AbstractCvsTask.DEFAULT_COMPRESSION_LEVEL.
     */
    public function setCompression($usecomp)
    {
        $this->setCompressionLevel(
            $usecomp ?
                self::DEFAULT_COMPRESSION_LEVEL : 0
        );
    }

    /**
     * File to which output should be written.
     * @param PhingFile $f
     * @internal param PhingFile $output
     */
    public function setOutput(PhingFile $f)
    {
        $this->output = $f;
    }

    /**
     * File to which error output should be written.
     * @param PhingFile $f
     * @internal param PhingFile $output
     */
    public function setError(PhingFile $f)
    {
        $this->error = $f;
    }

}
