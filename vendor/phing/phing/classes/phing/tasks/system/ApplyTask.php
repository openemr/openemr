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

require_once 'phing/Task.php';
include_once 'phing/types/FileList.php';
include_once 'phing/types/FileSet.php';
include_once 'phing/types/DirSet.php';

/**
 * Executes a command on the (filtered) file list/set.
 * (Loosely based on the "Ant Apply" task - http://ant.apache.org/manual/Tasks/apply.html)
 *
 * @author    Utsav Handa <handautsav at hotmail dot com>
 * @package   phing.tasks.system
 *
 * @todo      Add support for mapper, targetfile expressions
 */
class ApplyTask extends Task
{

    /**
     * Configuration(s)
     *
     */
    //[TBA]const TARGETFILE_ID = '__TARGETFILE__';
    const SOURCEFILE_ID = '__SOURCEFILE__';

    /**
     * File Set/List of files.
     * @var array
     */
    protected $filesets = array();
    protected $filelists = array();

    /**
     * Commandline managing object
     * @var commandline
     */
    protected $commandline;

    /**
     * Working directory
     * @var phingfile
     */
    protected $dir;
    protected $currentdirectory;

    /**
     * Command to be executed
     * @var string
     */
    protected $realCommand;

    /**
     * Escape (shell) command using 'escapeshellcmd' before execution
     * @var boolean
     */
    protected $escape = false;

    /**
     * Where to direct output
     * @var phingfile
     */
    protected $output;

    /**
     * Where to direct error
     * @var phingfile
     */
    protected $error;

    /**
     * Whether output should be appended to or overwrite an existing file
     * @var boolean
     */
    protected $appendoutput = false;

    /**
     * Runs the command only once, appending all files as arguments
     * else command will be executed once for every file.
     * @var boolean
     */
    protected $parallel = false;

    /**
     * Whether source file name should be added to the end of command automatically
     * @var boolean
     */
    protected $addsourcefile = true;

    /**
     * Whether to spawn the command execution as a background process
     * @var boolean
     */
    protected $spawn = false;

    /**
     * Property name to set with return value
     * @var string
     */
    protected $returnProperty;

    /**
     * Property name to set with output value
     * @var string
     */
    protected $outputProperty;

    /**
     * Whether the filenames should be passed on the command line as relative pathnames (relative to the base directory of the corresponding fileset/list)
     * @var boolean
     */
    protected $relative = false;

    /**
     * Operating system information
     * @var string
     */
    protected $os;
    protected $currentos;
    protected $osvariant;

    /**
     * Logging level for status messages
     * @var integer
     */
    protected $loglevel = null;

    /**
     * Fail on command that exits with a returncode other than zero
     * @var boolean
     *
     */
    protected $failonerror = false;

    /**
     * Whether to use PHP's passthru() function instead of exec()
     * @var boolean
     */
    protected $passthru = false;


    /**
     * Whether to use forward-slash as file-separator on the file names
     * @var boolean
     */
    protected $forwardslash = false;


    /**
     * Limit the amount of parallelism by passing at most this many sourcefiles at once
     * (Set it to <= 0 for unlimited)
     * @var integer
     */
    protected $maxparallel = 0;


    /**
     * Supports embedded <filelist> element.
     *
     * @return FileList
     */
    public function createFileList()
    {
        $num = array_push($this->filelists, new FileList());

        return $this->filelists[$num - 1];
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
     * Nested adder, adds a set of dirs (nested dirset attribute).
     *
     * @param DirSet $dirSet
     * @return void
     */
    public function addDirSet(DirSet $dirSet)
    {
        $this->filesets[] = $dirSet;
    }


    /**
     * Sets the command executable information
     *
     * @param string $executable Executable path
     *
     * @return void
     */
    public function setExecutable($executable)
    {
        $this->commandline->setExecutable((string) $executable);
    }


    /**
     * Specify the working directory for the command execution.
     *
     * @param PhingFile $dir Set the working directory as specified
     *
     * @return void
     */
    public function setDir(PhingFile $dir)
    {
        $this->dir = $dir;
    }


    /**
     * Escape command using 'escapeshellcmd' before execution
     *
     * @param boolean $escape Escape command before execution
     *
     * @return void
     */
    public function setEscape($escape)
    {
        $this->escape = (bool) $escape;
    }


    /**
     * File to which output should be written
     *
     * @param PhingFile $outputfile Output log file
     *
     * @return void
     */
    public function setOutput(PhingFile $outputfile)
    {
        $this->output = $outputfile;
    }


    /**
     * File to which output should be written
     *
     * @param $append
     * @internal param PhingFile $outputfile Output log file
     *
     * @return void
     */
    public function setAppend($append)
    {
        $this->appendoutput = (bool) $append;
    }


    /**
     * Run the command only once, appending all files as arguments
     *
     * @param Boolean $parallel Identifier for files as arguments appending
     *
     * @return void
     */
    public function setParallel($parallel)
    {
        $this->parallel = (bool) $parallel;
    }


    /**
     * To add the source filename at the end of command of automatically
     *
     * @param Boolean $addsourcefile Identifier for adding source file at the end of command
     *
     * @return void
     */
    public function setAddsourcefile($addsourcefile)
    {
        $this->addsourcefile = (bool) $addsourcefile;
    }


    /**
     * File to which error output should be written
     *
     * @param PhingFile $errorfile Error log file
     *
     * @return void
     */
    public function setError(PhingFile $errorfile)
    {
        $this->error = $errorfile;
    }


    /**
     * Whether to spawn the command and run as background process
     *
     * @param boolean $spawn If the command is to be run as a background process
     *
     * @return void
     */
    public function setSpawn($spawn)
    {
        $this->spawn = (bool) $spawn;
    }


    /**
     * The name of property to set to return value
     *
     * @param string $propertyname Property name
     *
     * @return void
     */
    public function setReturnProperty($propertyname)
    {
        $this->returnProperty = (string) $propertyname;
    }


    /**
     * The name of property to set to output value
     *
     * @param string $propertyname Property name
     *
     * @return void
     */
    public function setOutputProperty($propertyname)
    {
        $this->outputProperty = (string) $propertyname;
    }


    /**
     * Whether the filenames should be passed on the command line as relative
     * pathnames (relative to the base directory of the corresponding fileset/list)
     *
     * @param $relative
     * @internal param bool $escape Escape command before execution
     *
     * @return void
     */
    public function setRelative($relative)
    {
        $this->relative = (bool) $relative;
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
     * Fail on command exits with a returncode other than zero
     *
     * @param boolean $failonerror Indicator to fail on error
     *
     * @return void
     */
    public function setFailonerror($failonerror)
    {
        $this->failonerror = (bool) $failonerror;
    }

    /**
     * @param $failonerror
     */
    public function setCheckreturn($failonerror)
    {
        $this->setFailonerror($failonerror);
    }

    /**
     * Whether to use forward-slash as file-separator on the file names
     *
     * @param boolean $forwardslash Indicator to use forward-slash
     *
     * @return void
     */
    public function setForwardslash($forwardslash)
    {
        $this->forwardslash = (bool) $forwardslash;
    }

    /**
     * Limit the amount of parallelism by passing at most this many sourcefiles at once
     *
     * @param $max
     * @internal param bool $forwardslash Indicator to use forward-slash
     *
     * @return void
     */
    public function setMaxparallel($max)
    {
        $this->maxparallel = (int) $max;
    }

    /** [TBA]
     * Supports embedded <targetfile> element.
     *
     * @return void
     */
    /**public function createTargetfile() {
     * return $this->commandline->addArguments( array(self::TARGETFILE_ID) );
     * }*/

    /**
     * Supports embedded <srcfile> element.
     *
     * @return void
     */
    public function createSrcfile()
    {
        return $this->commandline->addArguments(array(self::SOURCEFILE_ID));
    }

    /**
     * Supports embedded <arg> element.
     *
     * @return CommandlineArgument
     */
    public function createArg()
    {
        return $this->commandline->createArgument();
    }

    /**********************************************************************************/
    /**************************** T A S K  M E T H O D S ******************************/
    /**********************************************************************************/

    /**
     * Class Initialization
     * @return void
     */
    public function init()
    {

        $this->commandline = new Commandline();
        $this->loglevel = Project::MSG_VERBOSE;
    }

    /**
     * Do work
     * @throws BuildException
     */
    public function main()
    {

        // Log
        $this->log('Started ', $this->loglevel);

        // Initialize //
        $this->initialize();

        // Validate O.S. applicability
        if ($this->validateOS()) {

            // Build the command //
            $this->buildCommand();

            // Process //
            // - FileLists
            foreach ($this->filelists as $fl) {
                $this->process($fl->getFiles($this->project), $fl->getDir($this->project));
            }
            unset($this->filelists);

            // - FileSets
            foreach ($this->filesets as $fs) {
                $this->process(
                    $fs->getDirectoryScanner($this->project)->getIncludedFiles(),
                    $fs->getDir($this->project)
                );
            }
            unset($this->filesets);

        }

        /// Cleanup //
        $this->cleanup();

        // Log
        $this->log('End ', $this->loglevel);

    }

    /**********************************************************************************/
    /********************** T A S K  C O R E  M E T H O D S ***************************/
    /**********************************************************************************/

    /**
     * Checks whether the current O.S. should be supported
     *
     * @return boolean False if the exec command shall not be run
     */
    protected function validateOS()
    {

        // Log
        $this->log('Validating Operating System information ', $this->loglevel);

        // Checking whether'os' information is specified
        if (empty($this->os)) {

            // Log
            $this->log("Operating system information not specified. Skipped checking. ", $this->loglevel);

            return true;
        }

        // Validating the operating system information
        $matched = (strpos(strtolower($this->os), strtolower($this->currentos)) !== false) ? true : false;

        // Log
        $this->log(
            "Operating system '" . $this->currentos . "' " . ($matched ? '' : 'not ') . "found in " . $this->os,
            $this->loglevel
        );

        return $matched;
    }

    /**
     * Initializes the task operations, i.e.
     * - Required information validation
     * - Working directory
     *
     * @param  none
     *
     * @return void
     */
    private function initialize()
    {

        // Log
        $this->log('Initializing started ', $this->loglevel);

        ///// Validating the required parameters /////

        // Executable
        if ($this->commandline->getExecutable() === null) {
            return $this->throwBuildException('Please provide "executable" information');
        }

        // Retrieving the current working directory
        $this->currentdirectory = getcwd();

        // Directory (in which the command should be executed)
        if ($this->dir !== null) {

            // Try expanding (any) symbolic links
            if (!$this->dir->getCanonicalFile()->isDirectory()) {
                return $this->throwBuildException("'" . $this->dir . "' is not a valid directory");
            }

            // Change working directory
            $dirchangestatus = @chdir($this->dir->getPath());

            // Log
            $this->log(
                'Working directory change ' . ($dirchangestatus ? 'successful' : 'failed') . ' to ' . $this->dir->getPath(
                ),
                $this->loglevel
            );

        }

        ///// Preparing the task environment /////

        // Getting current operationg system
        $this->currentos = Phing::getProperty('os.name');

        // Log
        $this->log('Operating System identified : ' . $this->currentos, $this->loglevel);

        // Getting the O.S. type identifier
        // Validating the 'filesystem' for determining the OS type [UNIX, WINNT and WIN32]
        // (Another usage could be with 'os.name' for determination)
        if ('WIN' == strtoupper(substr(Phing::getProperty('host.fstype'), 0, 3))) {
            $this->osvariant = 'WIN'; // Probable Windows flavour
        } else {
            $this->osvariant = 'LIN'; // Probable GNU/Linux flavour
        }

        // Log
        $this->log('Operating System variant identified : ' . $this->osvariant, $this->loglevel);

        // Log
        $this->log('Initializing completed ', $this->loglevel);

        return;
    }

    /**
     * Builds the full command to execute and stores it in $realCommand.
     *
     * @return void
     */
    private function buildCommand()
    {

        // Log
        $this->log('Command building started ', $this->loglevel);

        // Building the executable
        $this->realCommand = Commandline::toString($this->commandline->getCommandline(), $this->escape);

        // Adding the source filename at the end of command, validating the existing
        // sourcefile position explicit mentioning
        if (($this->addsourcefile === true) && (strpos($this->realCommand, self::SOURCEFILE_ID) === false)) {
            $this->realCommand .= ' ' . self::SOURCEFILE_ID;
        }

        // Setting command output redirection with content appending
        if ($this->output !== null) {

            $this->realCommand .= ' 1>';
            $this->realCommand .= ($this->appendoutput ? '>' : ''); // Append output
            $this->realCommand .= ' ' . escapeshellarg($this->output->getPath());

        } elseif ($this->spawn) { // Validating the 'spawn' configuration, and redirecting the output to 'null'

            // Validating the O.S. variant
            if ('WIN' == $this->osvariant) {
                $this->realCommand .= ' > NUL'; // MS Windows output nullification
            } else {
                $this->realCommand .= ' 1>/dev/null'; // GNU/Linux output nullification
            }

            $this->log("For process spawning, setting Output nullification ", $this->loglevel);
        }

        // Setting command error redirection with content appending
        if ($this->error !== null) {
            $this->realCommand .= ' 2>';
            $this->realCommand .= ($this->appendoutput ? '>' : ''); // Append error
            $this->realCommand .= ' ' . escapeshellarg($this->error->getPath());
        }

        // Setting the execution as a background process
        if ($this->spawn) {

            // Validating the O.S. variant
            if ('WIN' == $this->osvariant) {
                $this->realCommand = 'start /b ' . $this->realCommand; // MS Windows background process forking
            } else {
                $this->realCommand .= ' &'; // GNU/Linux background process forking
            }

        }

        // Log
        $this->log('Command built : ' . $this->realCommand, $this->loglevel);

        // Log
        $this->log('Command building completed ', $this->loglevel);

        return;
    }

    /**
     * Processes the files list with provided information for execution
     *
     * @param array $files File list for processing
     * @param string $basedir Base directory of the file list
     *
     * @return void
     */
    private function process($files, $basedir)
    {

        // Log
        $this->log("Processing Filelist with base directory ($basedir) ", $this->loglevel);

        // Process each file in the list for applying the 'realcommand'
        foreach ($files as $count => $file) {

            // Preparing the absolute filename with relative path information
            $absolutefilename = $this->getFilePath($file, $basedir, $this->relative);

            // Checking whether 'parallel' information is enabled. If enabled, append all
            // the file names as arguments, and run only once.
            if ($this->parallel) {

                // Checking whether 'maxparallel' setting describes parallelism limitation
                // by passing at most 'maxparallel' many sourcefiles at once
                $slicedfiles = array_splice(
                    $files,
                    0,
                    (($this->maxparallel > 0) ? $this->maxparallel : count($files))
                );;

                $absolutefilename = implode(' ', $this->getFilePath($slicedfiles, $basedir, $this->relative));
            }

            // Checking whether the forward-slash as file-separator has been set.
            // (Applicability: The source {and target} file names must use the forward slash as file separator)
            if ($this->forwardslash) {
                $absolutefilename = str_replace(DIRECTORY_SEPARATOR, '/', $absolutefilename);
            }

            // Preparing the command to be executed
            $filecommand = str_replace(array(self::SOURCEFILE_ID), array($absolutefilename), $this->realCommand);

            // Command execution
            list($returncode, $output) = $this->executeCommand($filecommand);

            // Process the stuff on the first command execution only
            if (0 == $count) {

                // Sets the return property
                if ($this->returnProperty) {
                    $this->project->setProperty($this->returnProperty, $returncode);
                }

            }

            // Sets the output property
            if ($this->outputProperty) {
                $previousValue = $this->project->getProperty($this->outputProperty);
                if (! empty($previousValue)) {
                    $previousValue .= "\n";
                }
                $this->project->setProperty($this->outputProperty, $previousValue . implode("\n", $output));
            }

            // Validating the 'return-code'
            if (($this->failonerror) && ($returncode != 0)) {
                $this->throwBuildException("Task exited with code ($returncode)");
            }

            // Validate the 'parallel' information for command execution. If the command has been
            // executed with the filenames as argument, considering 'maxparallel', just break.
            if (($this->parallel) && (!array_key_exists($count, $files))) {
                break;
            }

        } // Each file processing loop ends

        return;
    }

    /**
     * Executes the specified command and returns the return code & output.
     *
     * @param string $command
     *
     * @return array array(return code, array with output)
     */
    private function executeCommand($command)
    {

        // Var(s)
        $output = array();
        $return = null;

        // Validating the command executor container
        ($this->passthru ? passthru($command, $return) : exec($command, $output, $return));

        // Log
        $this->log(
            'Command execution : (' . ($this->passthru ? 'passthru' : 'exec') . ') : ' . $command . " : completed with return code ($return) ",
            $this->loglevel
        );

        return array($return, $output);
    }

    /**
     * Runs cleanup tasks post execution
     * - Restore working directory
     *
     * @return void
     */
    private function cleanup()
    {

        // Restore working directory
        if ($this->dir !== null) {
            @chdir($this->currentdirectory);
        }

        return;
    }

    /**
     * Prepares the filename per base directory and relative path information
     *
     * @param $filename
     * @param $basedir
     * @param $relative
     *
     * @return mixed processed filenames
     */
    public function getFilePath($filename, $basedir, $relative)
    {

        // Var(s)
        $files = array();

        // Validating the 'file' information
        $files = (is_array($filename)) ? $filename : array($filename);

        // Processing the file information
        foreach ($files as $index => $file) {
            $absolutefilename = (($relative === false) ? ($basedir . DIRECTORY_SEPARATOR) : '');
            $absolutefilename .= $file;
            $files[$index] = $absolutefilename;
        }

        return (is_array($filename) ? $files : $files[0]);
    }

    /**
     * Throws the exception with specified information
     *
     * @param  $information Exception information
     *
     * @throws BuildException
     * @return void
     */
    private function throwBuildException($information)
    {
        throw new BuildException('ApplyTask: ' . (string) $information);
    }

}
