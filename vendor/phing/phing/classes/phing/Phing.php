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

require_once 'phing/Diagnostics.php';
require_once 'phing/Project.php';
require_once 'phing/ProjectComponent.php';
require_once 'phing/Target.php';
require_once 'phing/Task.php';

include_once 'phing/BuildException.php';
include_once 'phing/ConfigurationException.php';
include_once 'phing/BuildEvent.php';

include_once 'phing/parser/Location.php';
include_once 'phing/parser/ExpatParser.php';
include_once 'phing/parser/AbstractHandler.php';
include_once 'phing/parser/ProjectConfigurator.php';
include_once 'phing/parser/RootHandler.php';
include_once 'phing/parser/ProjectHandler.php';
include_once 'phing/parser/TargetHandler.php';
include_once 'phing/parser/DataTypeHandler.php';

include_once 'phing/system/util/Properties.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/OutputStream.php';
include_once 'phing/system/io/PrintStream.php';
include_once 'phing/system/io/FileOutputStream.php';
include_once 'phing/system/io/FileReader.php';
include_once 'phing/system/util/Register.php';

/**
 * Entry point into Phing.  This class handles the full lifecycle of a build -- from
 * parsing & handling commandline arguments to assembling the project to shutting down
 * and cleaning up in the end.
 *
 * If you are invoking Phing from an external application, this is still
 * the class to use.  Your application can invoke the start() method, passing
 * any commandline arguments or additional properties.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 *
 * @package   phing
 */
class Phing
{
    /** Alias for phar file */
    const PHAR_ALIAS = 'phing.phar';

    /** The default build file name */
    const DEFAULT_BUILD_FILENAME = "build.xml";
    const PHING_HOME = 'phing.home';
    const PHP_VERSION = 'php.version';
    const PHP_INTERPRETER = 'php.interpreter';

    /** Our current message output status. Follows Project::MSG_XXX */
    private static $msgOutputLevel = Project::MSG_INFO;

    /** PhingFile that we are using for configuration */
    private $buildFile = null;

    /** The build targets */
    private $targets = array();

    /**
     * Set of properties that are passed in from commandline or invoking code.
     * @var Properties
     */
    private static $definedProps;

    /** Names of classes to add as listeners to project */
    private $listeners = array();

    /**
     * keep going mode
     * @var bool $keepGoingMode
     */
    private $keepGoingMode = false;

    private $loggerClassname = null;

    /** The class to handle input (can be only one). */
    private $inputHandlerClassname;

    /**
     * Whether or not log output should be reduced to the minimum.
     *
     * @var bool $silent
     */
    private $silent = false;

    /** Indicates if this phing should be run */
    private $readyToRun = false;

    /** Indicates we should only parse and display the project help information */
    private $projectHelp = false;

    /** Used by utility function getResourcePath() */
    private static $importPaths;

    /** System-wide static properties (moved from System) */
    private static $properties = array();

    /** Static system timer. */
    private static $timer;

    /** The current Project */
    private static $currentProject;

    /** Whether to capture PHP errors to buffer. */
    private static $phpErrorCapture = false;

    /** Array of captured PHP errors */
    private static $capturedPhpErrors = array();

    /**
     * @var OUtputStream Stream for standard output.
     */
    private static $out;

    /**
     * @var OutputStream Stream for error output.
     */
    private static $err;

    /**
     * @var boolean Whether we are using a logfile.
     */
    private static $isLogFileUsed = false;

    /**
     * Array to hold original ini settings that Phing changes (and needs
     * to restore in restoreIni() method).
     *
     * @var array Struct of array(setting-name => setting-value)
     * @see restoreIni()
     */
    private static $origIniSettings = array();

    /** Whether or not output to the log is to be unadorned. */
    private $emacsMode = false;

    /**
     * Entry point allowing for more options from other front ends.
     *
     * This method encapsulates the complete build lifecycle.
     *
     * @param  array     $args                     The commandline args passed to phing shell script.
     * @param  array     $additionalUserProperties Any additional properties to be passed to Phing (alternative front-end might implement this).
     *                                             These additional properties will be available using the getDefinedProperty() method and will
     *                                             be added to the project's "user" properties
     * @see execute()
     * @see runBuild()
     * @throws Exception - if there is an error during build
     */
    public static function start($args, array $additionalUserProperties = null)
    {

        try {
            $m = new Phing();
            $m->execute($args);
        } catch (Exception $exc) {
            self::handleLogfile();
            self::printMessage($exc);
            self::statusExit(1);
            return;
        }

        if ($additionalUserProperties !== null) {
            foreach ($additionalUserProperties as $key => $value) {
                $m->setDefinedProperty($key, $value);
            }
        }

        // expect the worst
        $exitCode = 1;
        try {
            try {
                $m->runBuild();
                $exitCode = 0;
            } catch (ExitStatusException $ese) {
                $exitCode = $ese->getCode();
                if ($exitCode != 0) {
                    self::handleLogfile();
                    throw $ese;
                }
            }
        } catch (BuildException $exc) {
            // avoid printing output twice: self::printMessage($exc);
        } catch (Exception $exc) {
             echo $exc->getTraceAsString();
             self::printMessage($exc);
         }

        // everything fine, shutdown
        self::handleLogfile();
        self::statusExit($exitCode);
    }

    /**
     * This operation is expected to call `exit($int)`, which
     * is what the base version does.
     * However, it is possible to do something else.
     * @param int $exitCode code to exit with
     */
    protected static function statusExit($exitCode)
    {
        exit($exitCode);
    }

    /**
     * Prints the message of the Exception if it's not null.
     * @param Exception $t
     */
    public static function printMessage(Exception $t)
    {
        if (self::$err === null) { // Make sure our error output is initialized
            self::initializeOutputStreams();
        }
        if (self::getMsgOutputLevel() >= Project::MSG_VERBOSE) {
            self::$err->write($t->__toString() . PHP_EOL);
        } else {
            self::$err->write($t->getMessage() . PHP_EOL);
        }
    }

    /**
     * Sets the stdout and stderr streams if they are not already set.
     */
    private static function initializeOutputStreams()
    {
        if (self::$out === null) {
            if (!defined('STDOUT')) {
                self::$out = new OutputStream(fopen('php://stdout', 'w'));
            } else {
                self::$out = new OutputStream(STDOUT);
            }
        }
        if (self::$err === null) {
            if (!defined('STDERR')) {
                self::$err = new OutputStream(fopen('php://stderr', 'w'));
            } else {
                self::$err = new OutputStream(STDERR);
            }
        }
    }

    /**
     * Sets the stream to use for standard (non-error) output.
     *
     * @param OutputStream $stream The stream to use for standard output.
     */
    public static function setOutputStream(OutputStream $stream)
    {
        self::$out = $stream;
    }

    /**
     * Gets the stream to use for standard (non-error) output.
     *
     * @return OutputStream
     */
    public static function getOutputStream()
    {
        return self::$out;
    }

    /**
     * Sets the stream to use for error output.
     *
     * @param OutputStream $stream The stream to use for error output.
     */
    public static function setErrorStream(OutputStream $stream)
    {
        self::$err = $stream;
    }

    /**
     * Gets the stream to use for error output.
     *
     * @return OutputStream
     */
    public static function getErrorStream()
    {
        return self::$err;
    }

    /**
     * Close logfiles, if we have been writing to them.
     *
     * @since Phing 2.3.0
     *
     * @return void
     */
    private static function handleLogfile()
    {
        if (self::$isLogFileUsed) {
            self::$err->close();
            self::$out->close();
        }
    }

    /**
     * Making output level a static property so that this property
     * can be accessed by other parts of the system, enabling
     * us to display more information -- e.g. backtraces -- for "debug" level.
     *
     * @return int
     */
    public static function getMsgOutputLevel()
    {
        return self::$msgOutputLevel;
    }

    /**
     * Command line entry point. This method kicks off the building
     * of a project object and executes a build using either a given
     * target or the default target.
     *
     * @param  array $args Command line args.
     *
     * @return void
     */
    public static function fire($args)
    {
        self::start($args, null);
    }

    /**
     * Setup/initialize Phing environment from commandline args.
     * @param  array $args commandline args passed to phing shell.
     *
     * @throws ConfigurationException
     *
     * @return void
     */
    public function execute($args)
    {

        self::$definedProps = new Properties();
        $this->searchForThis = null;

        // 1) First handle any options which should always
        // Note: The order in which these are executed is important (if multiple of these options are specified)

        if (in_array('-help', $args) || in_array('-h', $args)) {
            $this->printUsage();

            return;
        }

        if (in_array('-version', $args) || in_array('-v', $args)) {
            $this->printVersion();

            return;
        }

        if (in_array('-diagnostics', $args)) {
            Diagnostics::doReport(new PrintStream(self::$out));

            return;
        }

        // 2) Next pull out stand-alone args.
        // Note: The order in which these are executed is important (if multiple of these options are specified)

        if (false !== ($key = array_search('-quiet', $args, true)) || false !== ($key = array_search(
                '-q',
                $args,
                true
            ))
        ) {
            self::$msgOutputLevel = Project::MSG_WARN;
            unset($args[$key]);
        }

        if (
            false !== ($key = array_search('-emacs', $args, true))
            ||
            false !== ($key = array_search('-e', $args, true))
        ) {
            $this->emacsMode = true;
            unset($args[$key]);
        }

        if (false !== ($key = array_search('-verbose', $args, true))) {
            self::$msgOutputLevel = Project::MSG_VERBOSE;
            unset($args[$key]);
        }

        if (false !== ($key = array_search('-debug', $args, true))) {
            self::$msgOutputLevel = Project::MSG_DEBUG;
            unset($args[$key]);
        }

        if (
            false !== ($key = array_search('-silent', $args, true))
            ||
            false !== ($key = array_search('-S', $args, true))
        ) {
            $this->silent = true;
            unset($args[$key]);
        }

        // 3) Finally, cycle through to parse remaining args
        //
        $keys = array_keys($args); // Use keys and iterate to max(keys) since there may be some gaps
        $max = $keys ? max($keys) : -1;
        for ($i = 0; $i <= $max; $i++) {

            if (!array_key_exists($i, $args)) {
                // skip this argument, since it must have been removed above.
                continue;
            }

            $arg = $args[$i];

            if ($arg == "-logfile") {
                try {
                    // see: http://phing.info/trac/ticket/65
                    if (!isset($args[$i + 1])) {
                        $msg = "You must specify a log file when using the -logfile argument\n";
                        throw new ConfigurationException($msg);
                    } else {
                        $logFile = new PhingFile($args[++$i]);
                        $out = new FileOutputStream($logFile); // overwrite
                        self::setOutputStream($out);
                        self::setErrorStream($out);
                        self::$isLogFileUsed = true;
                    }
                } catch (IOException $ioe) {
                    $msg = "Cannot write on the specified log file. Make sure the path exists and you have write permissions.";
                    throw new ConfigurationException($msg, $ioe);
                }
            } elseif ($arg == "-buildfile" || $arg == "-file" || $arg == "-f") {
                if (!isset($args[$i + 1])) {
                    $msg = "You must specify a buildfile when using the -buildfile argument.";
                    throw new ConfigurationException($msg);
                } else {
                    $this->buildFile = new PhingFile($args[++$i]);
                }
            } elseif ($arg == "-listener") {
                if (!isset($args[$i + 1])) {
                    $msg = "You must specify a listener class when using the -listener argument";
                    throw new ConfigurationException($msg);
                } else {
                    $this->listeners[] = $args[++$i];
                }
            } elseif (StringHelper::startsWith("-D", $arg)) {
                // Evaluating the property information //
                // Checking whether arg. is not just a switch, and next arg. does not starts with switch identifier
                if (('-D' == $arg) && (!StringHelper::startsWith('-', $args[$i + 1]))) {
                    $name = $args[++$i];
                } else {
                    $name = substr($arg, 2);
                }

                $value = null;
                $posEq = strpos($name, "=");
                if ($posEq !== false) {
                    $value = substr($name, $posEq + 1);
                    $name = substr($name, 0, $posEq);
                } elseif ($i < count($args) - 1 && !StringHelper::startsWith("-D", $args[$i + 1])) {
                    $value = $args[++$i];
                }
                self::$definedProps->setProperty($name, $value);
            } elseif ($arg == "-logger") {
                if (!isset($args[$i + 1])) {
                    $msg = "You must specify a classname when using the -logger argument";
                    throw new ConfigurationException($msg);
                } else {
                    $this->loggerClassname = $args[++$i];
                }
            } elseif ($arg == "-inputhandler") {
                if ($this->inputHandlerClassname !== null) {
                    throw new ConfigurationException("Only one input handler class may be specified.");
                }
                if (!isset($args[$i + 1])) {
                    $msg = "You must specify a classname when using the -inputhandler argument";
                    throw new ConfigurationException($msg);
                } else {
                    $this->inputHandlerClassname = $args[++$i];
                }
            } elseif ($arg == "-propertyfile") {
                if (!isset($args[$i + 1])) {
                    $msg = "You must specify a filename when using the -propertyfile argument";
                    throw new ConfigurationException($msg);
                } else {
                    $p = new Properties();
                    $p->load(new PhingFile($args[++$i]));
                    foreach ($p->getProperties() as $prop => $value) {
                        $this->setProperty($prop, $value);
                    }
                }
            } elseif ($arg == "-keep-going" || $arg == "-k") {
                $this->keepGoingMode = true;
            } elseif ($arg == "-longtargets") {
                self::$definedProps->setProperty('phing.showlongtargets', 1);
            } elseif ($arg == "-projecthelp" || $arg == "-targets" || $arg == "-list" || $arg == "-l" || $arg == "-p") {
                // set the flag to display the targets and quit
                $this->projectHelp = true;
            } elseif ($arg == "-find") {
                // eat up next arg if present, default to build.xml
                if ($i < count($args) - 1) {
                    $this->searchForThis = $args[++$i];
                } else {
                    $this->searchForThis = self::DEFAULT_BUILD_FILENAME;
                }
            } elseif (substr($arg, 0, 1) == "-") {
                // we don't have any more args
                self::printUsage();
                self::$err->write(PHP_EOL);
                throw new ConfigurationException("Unknown argument: " . $arg);
            } else {
                // if it's no other arg, it may be the target
                array_push($this->targets, $arg);
            }
        }

        // if buildFile was not specified on the command line,
        if ($this->buildFile === null) {
            // but -find then search for it
            if ($this->searchForThis !== null) {
                $this->buildFile = $this->_findBuildFile(self::getProperty("user.dir"), $this->searchForThis);
            } else {
                $this->buildFile = new PhingFile(self::DEFAULT_BUILD_FILENAME);
            }
        }

        try {
            // make sure buildfile (or buildfile.dist) exists
            if (!$this->buildFile->exists()) {
                $distFile = new PhingFile($this->buildFile->getAbsolutePath() . ".dist");
                if (!$distFile->exists()) {
                    throw new ConfigurationException("Buildfile: " . $this->buildFile->__toString(
                        ) . " does not exist!");
                }
                $this->buildFile = $distFile;
            }

            // make sure it's not a directory
            if ($this->buildFile->isDirectory()) {
                throw new ConfigurationException("Buildfile: " . $this->buildFile->__toString() . " is a dir!");
            }
        } catch (IOException $e) {
            // something else happened, buildfile probably not readable
            throw new ConfigurationException("Buildfile: " . $this->buildFile->__toString() . " is not readable!");
        }

        $this->readyToRun = true;
    }

    /**
     * Helper to get the parent file for a given file.
     *
     * @param  PhingFile $file
     *
     * @return PhingFile Parent file or null if none
     */
    private function _getParentFile(PhingFile $file)
    {
        $filename = $file->getAbsolutePath();
        $file = new PhingFile($filename);
        $filename = $file->getParent();

        return ($filename === null) ? null : new PhingFile($filename);
    }

    /**
     * Search parent directories for the build file.
     *
     * Takes the given target as a suffix to append to each
     * parent directory in search of a build file.  Once the
     * root of the file-system has been reached an exception
     * is thrown.
     *
     * @param  string $start Start file path.
     * @param  string $suffix Suffix filename to look for in parents.
     *
     * @throws ConfigurationException
     *
     * @return PhingFile A handle to the build file
     */
    private function _findBuildFile($start, $suffix)
    {
        $startf = new PhingFile($start);
        $parent = new PhingFile($startf->getAbsolutePath());
        $file = new PhingFile($parent, $suffix);

        // check if the target file exists in the current directory
        while (!$file->exists()) {
            // change to parent directory
            $parent = $this->_getParentFile($parent);

            // if parent is null, then we are at the root of the fs,
            // complain that we can't find the build file.
            if ($parent === null) {
                throw new ConfigurationException("Could not locate a build file!");
            }
            // refresh our file handle
            $file = new PhingFile($parent, $suffix);
        }

        return $file;
    }

    /**
     * Executes the build.
     *
     * @throws ConfigurationException
     * @throws Exception
     *
     * @return void
     */
    public function runBuild()
    {

        if (!$this->readyToRun) {
            return;
        }

        $project = new Project();

        self::setCurrentProject($project);
        set_error_handler(array('Phing', 'handlePhpError'));

        $error = null;

        $this->addBuildListeners($project);
        $this->addInputHandler($project);

        // set this right away, so that it can be used in logging.
        $project->setUserProperty("phing.file", $this->buildFile->getAbsolutePath());
        $project->setUserProperty("phing.dir", dirname($this->buildFile->getAbsolutePath()));

        try {
            $project->fireBuildStarted();
            $project->init();
        } catch (Exception $exc) {
            $project->fireBuildFinished($exc);
            throw $exc;
        }

        $project->setKeepGoingMode($this->keepGoingMode);

        $project->setUserProperty("phing.version", $this->getPhingVersion());

        $e = self::$definedProps->keys();
        while (count($e)) {
            $arg = (string) array_shift($e);
            $value = (string) self::$definedProps->getProperty($arg);
            $project->setUserProperty($arg, $value);
        }
        unset($e);

        $project->setUserProperty("phing.file", $this->buildFile->getAbsolutePath());

        // first use the Configurator to create the project object
        // from the given build file.

        try {
            ProjectConfigurator::configureProject($project, $this->buildFile);
        } catch (Exception $exc) {
            $project->fireBuildFinished($exc);
            restore_error_handler();
            self::unsetCurrentProject();
            throw $exc;
        }

        // make sure that we have a target to execute
        if (count($this->targets) === 0) {
            $this->targets[] = $project->getDefaultTarget();
        }

        // make sure that minimum required phing version is satisfied
        try {
            $this->comparePhingVersion($project->getPhingVersion());
        } catch (Exception $exc) {
            $project->fireBuildFinished($exc);
            restore_error_handler();
            self::unsetCurrentProject();
            throw $exc;
        }

        // execute targets if help param was not given
        if (!$this->projectHelp) {

            try {
                $project->executeTargets($this->targets);
            } catch (Exception $exc) {
                $project->fireBuildFinished($exc);
                restore_error_handler();
                self::unsetCurrentProject();
                throw $exc;
            }
        }
        // if help is requested print it
        if ($this->projectHelp) {
            try {
                $this->printDescription($project);
                $this->printTargets($project);
            } catch (Exception $exc) {
                $project->fireBuildFinished($exc);
                restore_error_handler();
                self::unsetCurrentProject();
                throw $exc;
            }
        }

        // finally {
        if (!$this->projectHelp) {
            $project->fireBuildFinished(null);
        }

        restore_error_handler();
        self::unsetCurrentProject();
    }

    /**
     * @param string $version
     *
     * @return int|void
     *
     * @throws BuildException
     * @throws ConfigurationException
     */
    private function comparePhingVersion($version)
    {
        $current = strtolower(self::getPhingVersion());
        $current = trim(str_replace('phing', '', $current));

        // make sure that version checks are not applied to trunk
        if ('dev' === $current) {
            return 1;
        }

        if (-1 == version_compare($current, $version)) {
            throw new BuildException(
                sprintf('Incompatible Phing version (%s). Version "%s" required.', $current, $version));
        }
    }

    /**
     * Bind any registered build listeners to this project.
     *
     * This means adding the logger and any build listeners that were specified
     * with -listener arg.
     *
     * @param  Project $project
     * @throws BuildException
     * @throws ConfigurationException
     * @return void
     */
    private function addBuildListeners(Project $project)
    {
        // Add the default listener
        $project->addBuildListener($this->createLogger());

        foreach ($this->listeners as $listenerClassname) {
            try {
                $clz = Phing::import($listenerClassname);
            } catch (Exception $e) {
                $msg = "Unable to instantiate specified listener "
                    . "class " . $listenerClassname . " : "
                    . $e->getMessage();
                throw new ConfigurationException($msg);
            }

            $listener = new $clz();

            if ($listener instanceof StreamRequiredBuildLogger) {
                throw new ConfigurationException("Unable to add " . $listenerClassname . " as a listener, since it requires explicit error/output streams. (You can specify it as a -logger.)");
            }
            $project->addBuildListener($listener);
        }
    }

    /**
     * Creates the InputHandler and adds it to the project.
     *
     * @param Project $project the project instance.
     *
     * @throws ConfigurationException
     */
    private function addInputHandler(Project $project)
    {
        if ($this->inputHandlerClassname === null) {
            $handler = new DefaultInputHandler();
        } else {
            try {
                $clz = Phing::import($this->inputHandlerClassname);
                $handler = new $clz();
                if ($project !== null && method_exists($handler, 'setProject')) {
                    $handler->setProject($project);
                }
            } catch (Exception $e) {
                $msg = "Unable to instantiate specified input handler "
                    . "class " . $this->inputHandlerClassname . " : "
                    . $e->getMessage();
                throw new ConfigurationException($msg);
            }
        }
        $project->setInputHandler($handler);
    }

    /**
     * Creates the default build logger for sending build events to the log.
     * @throws BuildException
     * @return BuildLogger The created Logger
     */
    private function createLogger()
    {
        if ($this->silent) {
            require_once 'phing/listener/SilentLogger.php';
            $logger = new SilentLogger();
            self::$msgOutputLevel = Project::MSG_WARN;
        } elseif ($this->loggerClassname !== null) {
            self::import($this->loggerClassname);
            // get class name part
            $classname = self::import($this->loggerClassname);
            $logger = new $classname();
            if (!($logger instanceof BuildLogger)) {
                throw new BuildException($classname . ' does not implement the BuildLogger interface.');
            }
        } else {
            require_once 'phing/listener/DefaultLogger.php';
            $logger = new DefaultLogger();
        }
        $logger->setMessageOutputLevel(self::$msgOutputLevel);
        $logger->setOutputStream(self::$out);
        $logger->setErrorStream(self::$err);
        $logger->setEmacsMode($this->emacsMode);

        return $logger;
    }

    /**
     * Sets the current Project
     * @param Project $p
     */
    public static function setCurrentProject($p)
    {
        self::$currentProject = $p;
    }

    /**
     * Unsets the current Project
     */
    public static function unsetCurrentProject()
    {
        self::$currentProject = null;
    }

    /**
     * Gets the current Project.
     * @return Project Current Project or NULL if none is set yet/still.
     */
    public static function getCurrentProject()
    {
        return self::$currentProject;
    }

    /**
     * A static convenience method to send a log to the current (last-setup) Project.
     * If there is no currently-configured Project, then this will do nothing.
     * @param string $message
     * @param int    $priority Project::MSG_INFO, etc.
     */
    public static function log($message, $priority = Project::MSG_INFO)
    {
        $p = self::getCurrentProject();
        if ($p) {
            $p->log($message, $priority);
        }
    }

    /**
     * Error handler for PHP errors encountered during the build.
     * This uses the logging for the currently configured project.
     *
     * @param $level
     * @param string $message
     * @param $file
     * @param $line
     */
    public static function handlePhpError($level, $message, $file, $line)
    {

        // don't want to print suppressed errors
        if (error_reporting() > 0) {

            if (self::$phpErrorCapture) {

                self::$capturedPhpErrors[] = array(
                    'message' => $message,
                    'level' => $level,
                    'line' => $line,
                    'file' => $file
                );

            } else {

                $message = '[PHP Error] ' . $message;
                $message .= ' [line ' . $line . ' of ' . $file . ']';

                switch ($level) {
                    case 16384: // E_USER_DEPRECATED
                    case 8192: // E_DEPRECATED
                    case E_STRICT:
                    case E_NOTICE:
                    case E_USER_NOTICE:
                        self::log($message, Project::MSG_VERBOSE);
                        break;
                    case E_WARNING:
                    case E_USER_WARNING:
                        self::log($message, Project::MSG_WARN);
                        break;
                    case E_ERROR:
                    case E_USER_ERROR:
                    default:
                        self::log($message, Project::MSG_ERR);

                } // switch

            } // if phpErrorCapture

        } // if not @

    }

    /**
     * Begins capturing PHP errors to a buffer.
     * While errors are being captured, they are not logged.
     */
    public static function startPhpErrorCapture()
    {
        self::$phpErrorCapture = true;
        self::$capturedPhpErrors = array();
    }

    /**
     * Stops capturing PHP errors to a buffer.
     * The errors will once again be logged after calling this method.
     */
    public static function stopPhpErrorCapture()
    {
        self::$phpErrorCapture = false;
    }

    /**
     * Clears the captured errors without affecting the starting/stopping of the capture.
     */
    public static function clearCapturedPhpErrors()
    {
        self::$capturedPhpErrors = array();
    }

    /**
     * Gets any PHP errors that were captured to buffer.
     * @return array array('message' => message, 'line' => line number, 'file' => file name, 'level' => error level)
     */
    public static function getCapturedPhpErrors()
    {
        return self::$capturedPhpErrors;
    }

    /**  Prints the usage of how to use this class */
    public static function printUsage()
    {

        $msg = "";
        $msg .= "phing [options] [target [target2 [target3] ...]]" . PHP_EOL;
        $msg .= "Options: " . PHP_EOL;
        $msg .= "  -h -help               print this message" . PHP_EOL;
        $msg .= "  -l -list               list available targets in this project" . PHP_EOL;
        $msg .= "  -v -version            print the version information and exit" . PHP_EOL;
        $msg .= "  -q -quiet              be extra quiet" . PHP_EOL;
        $msg .= "  -S -silent             print nothing but task outputs and build failures" . PHP_EOL;
        $msg .= "  -verbose               be extra verbose" . PHP_EOL;
        $msg .= "  -debug                 print debugging information" . PHP_EOL;
        $msg .= "  -emacs, -e             produce logging information without adornments" . PHP_EOL;
        $msg .= "  -diagnostics           print diagnostics information" . PHP_EOL;
        $msg .= "  -longtargets           show target descriptions during build" . PHP_EOL;
        $msg .= "  -logfile <file>        use given file for log" . PHP_EOL;
        $msg .= "  -logger <classname>    the class which is to perform logging" . PHP_EOL;
        $msg .= "  -listener <classname>  add an instance of class as a project listener" . PHP_EOL;
        $msg .= "  -f -buildfile <file>   use given buildfile" . PHP_EOL;
        $msg .= "  -D<property>=<value>   use value for given property" . PHP_EOL;
        $msg .= "  -keep-going, -k        execute all targets that do not depend" . PHP_EOL;
        $msg .= "                         on failed target(s)" . PHP_EOL;
        $msg .= "  -propertyfile <file>   load all properties from file" . PHP_EOL;
        $msg .= "  -find <file>           search for buildfile towards the root of the" . PHP_EOL;
        $msg .= "                         filesystem and use it" . PHP_EOL;
        $msg .= "  -inputhandler <file>   the class to use to handle user input" . PHP_EOL;
        //$msg .= "  -recursive <file>      search for buildfile downwards and use it" . PHP_EOL;
        $msg .= PHP_EOL;
        $msg .= "Report bugs to <dev@phing.tigris.org>" . PHP_EOL;
        self::$err->write($msg);
    }

    /**
     * Prints the current Phing version.
     */
    public static function printVersion()
    {
        self::$out->write(self::getPhingVersion() . PHP_EOL);
    }

    /**
     * Copied from https://github.com/sebastianbergmann/version/blob/master/src/Version.php
     * @param string $path
     * @return bool|string
     */
    private static function getGitInformation($path)
    {
        if (!is_dir($path . DIRECTORY_SEPARATOR . '.git')) {
            return false;
        }
        $dir = getcwd();
        chdir($path);
        $result = @exec('git describe --tags 2>&1', $output, $returnCode);
        chdir($dir);
        if ($returnCode !== 0) {
            return false;
        }
        return $result;
    }

    /**
     * Gets the current Phing version based on VERSION.TXT file.
     *
     * @throws ConfigurationException
     *
     * @return string
     */
    public static function getPhingVersion()
    {
        $path = dirname(dirname(dirname(__FILE__)));

        $gitInformation = self::getGitInformation($path);

        if ($gitInformation) {
            return "Phing " . $gitInformation;
        }

        $versionPath = self::getResourcePath("phing/etc/VERSION.TXT");
        if ($versionPath === null) {
            $versionPath = self::getResourcePath("etc/VERSION.TXT");
        }
        if ($versionPath === null) {
            throw new ConfigurationException("No VERSION.TXT file found; try setting phing.home environment variable.");
        }
        try { // try to read file
            $file = new PhingFile($versionPath);
            $reader = new FileReader($file);
            $phingVersion = trim($reader->read());
        } catch (IOException $iox) {
            throw new ConfigurationException("Can't read version information file");
        }

        return "Phing " . $phingVersion;
    }

    /**
     * Print the project description, if any
     *
     * @param Project $project
     *
     * @throws IOException
     */
    public static function printDescription(Project $project)
    {
        if ($project->getDescription() !== null) {
            self::$out->write($project->getDescription() . PHP_EOL);
        }
    }

    /** Print out a list of all targets in the current buildfile
     * @param $project
     */
    public function printTargets($project)
    {
        // find the target with the longest name
        $maxLength = 0;
        $targets = $project->getTargets();
        $targetNames = array_keys($targets);
        $targetName = null;
        $targetDescription = null;
        $currentTarget = null;

        // split the targets in top-level and sub-targets depending
        // on the presence of a description

        $subNames = array();
        $topNameDescMap = array();

        foreach ($targets as $currentTarget) {
            $targetName = $currentTarget->getName();
            $targetDescription = $currentTarget->getDescription();
            if ($currentTarget->isHidden()) {
                continue;
            }

            // subtargets are targets w/o descriptions
            if ($targetDescription === null) {
                $subNames[] = $targetName;
            } else {
                // topNames and topDescriptions are handled later
                // here we store in hash map (for sorting purposes)
                $topNameDescMap[$targetName] = $targetDescription;
                if (strlen($targetName) > $maxLength) {
                    $maxLength = strlen($targetName);
                }
            }
        }

        // Sort the arrays
        sort($subNames); // sort array values, resetting keys (which are numeric)
        ksort($topNameDescMap); // sort the keys (targetName) keeping key=>val associations

        $topNames = array_keys($topNameDescMap);
        $topDescriptions = array_values($topNameDescMap);

        $defaultTarget = $project->getDefaultTarget();

        if ($defaultTarget !== null && $defaultTarget !== "") {
            $defaultName = array();
            $defaultDesc = array();
            $defaultName[] = $defaultTarget;

            $indexOfDefDesc = array_search($defaultTarget, $topNames, true);
            if ($indexOfDefDesc !== false && $indexOfDefDesc >= 0) {
                $defaultDesc = array();
                $defaultDesc[] = $topDescriptions[$indexOfDefDesc];
            }

            $this->_printTargets($defaultName, $defaultDesc, "Default target:", $maxLength);

        }
        $this->_printTargets($topNames, $topDescriptions, "Main targets:", $maxLength);
        $this->_printTargets($subNames, null, "Subtargets:", 0);
    }

    /**
     * Writes a formatted list of target names with an optional description.
     *
     * @param array  $names        The names to be printed.
     *                             Must not be <code>null</code>.
     * @param array  $descriptions The associated target descriptions.
     *                             May be <code>null</code>, in which case
     *                             no descriptions are displayed.
     *                             If non-<code>null</code>, this should have
     *                             as many elements as <code>names</code>.
     * @param string $heading      The heading to display.
     *                             Should not be <code>null</code>.
     * @param int    $maxlen       The maximum length of the names of the targets.
     *                             If descriptions are given, they are padded to this
     *                             position so they line up (so long as the names really
     *                             <i>are</i> shorter than this).
     */
    private function _printTargets($names, $descriptions, $heading, $maxlen)
    {

        $spaces = '  ';
        while (strlen($spaces) < $maxlen) {
            $spaces .= $spaces;
        }
        $msg = "";
        $msg .= $heading . PHP_EOL;
        $msg .= str_repeat("-", 79) . PHP_EOL;

        $total = count($names);
        for ($i = 0; $i < $total; $i++) {
            $msg .= " ";
            $msg .= $names[$i];
            if (!empty($descriptions)) {
                $msg .= substr($spaces, 0, $maxlen - strlen($names[$i]) + 2);
                $msg .= $descriptions[$i];
            }
            $msg .= PHP_EOL;
        }
        if ($total > 0) {
            self::$out->write($msg . PHP_EOL);
        }
    }

    /**
     * Import a path, supporting the following conventions:
     * - PEAR style (@link http://pear.php.net/manual/en/standards.naming.php)
     * - PSR-0 (@link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
     * - dot-path
     *
     * @param string         $dotPath   Path
     * @param mixed          $classpath String or object supporting __toString()
     *
     * @return string         The unqualified classname (which can be instantiated).
     *
     * @throws BuildException - if cannot find the specified file
     */
    public static function import($dotPath, $classpath = null)
    {

        if (strpos($dotPath, '.') !== false) {
            $classname = StringHelper::unqualify($dotPath);
        } else {
            $classname = $dotPath;
            $dotPath = '';
            $shortClassName = $classname;
            if (($lastNsPos = strripos($shortClassName, '\\'))) {
                $namespace = substr($shortClassName, 0, $lastNsPos);
                $shortClassName = substr($shortClassName, $lastNsPos + 1);
                $dotPath = str_replace('\\', '.', $namespace) . '.';
            }
            $dotPath .= str_replace('_', '.', $shortClassName);
        }

        // first check to see that the class specified hasn't already been included.
        // (this also handles case where this method is called w/ a classname rather than dotpath)
        if (class_exists($classname)) {
            return $classname;
        }

        $dotClassname = basename($dotPath);
        $dotClassnamePos = strlen($dotPath) - strlen($dotClassname);

        // 1- temporarily replace escaped '.' with another illegal char (#)
        $tmp = str_replace('\.', '##', $dotClassname);
        // 2- swap out the remaining '.' with DIR_SEP
        $tmp = strtr($tmp, '.', DIRECTORY_SEPARATOR);
        // 3- swap back the escaped '.'
        $tmp = str_replace('##', '.', $tmp);

        $classFile = $tmp . ".php";

        $path = substr_replace($dotPath, $classFile, $dotClassnamePos);

        Phing::__import($path, $classpath);

        return $classname;
    }

    /**
     * Import a PHP file
     *
     * @param  string $path Path to the PHP file
     * @param  mixed $classpath String or object supporting __toString()
     *
     * @throws ConfigurationException
     */
    public static function __import($path, $classpath = null)
    {

        if ($classpath) {

            // Apparently casting to (string) no longer invokes __toString() automatically.
            if (is_object($classpath)) {
                $classpath = $classpath->__toString();
            }

            // classpaths are currently additive, but we also don't want to just
            // indiscriminantly prepand/append stuff to the include_path.  This means
            // we need to parse current incldue_path, and prepend any
            // specified classpath locations that are not already in the include_path.
            //
            // NOTE:  the reason why we do it this way instead of just changing include_path
            // and then changing it back, is that in many cases applications (e.g. Propel) will
            // include/require class files from within method calls.  This means that not all
            // necessary files will be included in this import() call, and hence we can't
            // change the include_path back without breaking those apps.  While this method could
            // be more expensive than switching & switching back (not sure, but maybe), it makes it
            // possible to write far less expensive run-time applications (e.g. using Propel), which is
            // really where speed matters more.

            $curr_parts = Phing::explodeIncludePath();
            $add_parts = Phing::explodeIncludePath($classpath);
            $new_parts = array_diff($add_parts, $curr_parts);
            if ($new_parts) {
                set_include_path(implode(PATH_SEPARATOR, array_merge($new_parts, $curr_parts)));
            }
        }

        $ret = include_once $path;

        if ($ret === false) {
            $msg = "Error importing $path";
            if (self::getMsgOutputLevel() >= Project::MSG_DEBUG) {
                $x = new Exception("for-path-trace-only");
                $msg .= $x->getTraceAsString();
            }
            throw new ConfigurationException($msg);
        }
    }

    /**
     * Looks on include path for specified file.
     *
     * @param string $path
     *
     * @return string File found (null if no file found).
     */
    public static function getResourcePath($path)
    {

        if (self::$importPaths === null) {
            self::$importPaths = self::explodeIncludePath();
        }

        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        foreach (self::$importPaths as $prefix) {
            $testPath = $prefix . DIRECTORY_SEPARATOR . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        // Check for the property phing.home
        $homeDir = self::getProperty(self::PHING_HOME);
        if ($homeDir) {
            $testPath = $homeDir . DIRECTORY_SEPARATOR . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        // Check for the phing home of phar archive
        if (strpos(self::$importPaths[0], 'phar://') === 0) {
            $testPath = self::$importPaths[0] . '/../' . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        // If we are using this via PEAR then check for the file in the data dir
        // This is a bit of a hack, but works better than previous solution of assuming
        // data_dir is on the include_path.
        $dataDir = '@DATA-DIR@';
        if ($dataDir{0} != '@') { // if we're using PEAR then the @ DATA-DIR @ token will have been substituted.
            if (!file_exists($dataDir)) {
                self::log("The PEAR data_dir setting is incorrect: {$dataDir}.", Project::MSG_ERR);
                self::log("Please edit using 'pear config-set data_dir ...' and re-install Phing.", Project::MSG_ERR);

                return null;
            }

            $testPath = $dataDir . DIRECTORY_SEPARATOR . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        } else {
            // We're not using PEAR, so do one additional check based on path of
            // current file (Phing.php)
            $maybeHomeDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
            $testPath = $maybeHomeDir . DIRECTORY_SEPARATOR . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        return null;
    }

    /**
     * Explode an include path into an array
     *
     * If no path provided, uses current include_path. Works around issues that
     * occur when the path includes stream schemas.
     *
     * Pulled from Zend_Loader::explodeIncludePath() in ZF1.
     *
     * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
     * @license http://framework.zend.com/license/new-bsd New BSD License
     * @param  string|null $path
     * @return array
     */
    public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }

        return $paths;
    }

    // -------------------------------------------------------------------------------------------
    // System-wide methods (moved from System class, which had namespace conflicts w/ PEAR System)
    // -------------------------------------------------------------------------------------------

    /**
     * Set System constants which can be retrieved by calling Phing::getProperty($propName).
     * @return void
     */
    private static function setSystemConstants()
    {

        /*
         * PHP_OS returns on
         *   WindowsNT4.0sp6  => WINNT
         *   Windows2000      => WINNT
         *   Windows ME       => WIN32
         *   Windows 98SE     => WIN32
         *   FreeBSD 4.5p7    => FreeBSD
         *   Redhat Linux     => Linux
         *   Mac OS X         => Darwin
         */
        self::setProperty('host.os', PHP_OS);

        // this is used by some tasks too
        self::setProperty('os.name', PHP_OS);

        // it's still possible this won't be defined,
        // e.g. if Phing is being included in another app w/o
        // using the phing.php script.
        if (!defined('PHP_CLASSPATH')) {
            define('PHP_CLASSPATH', get_include_path());
        }

        self::setProperty('php.classpath', PHP_CLASSPATH);

        // try to determine the host filesystem and set system property
        // used by Fileself::getFileSystem to instantiate the correct
        // abstraction layer

        switch (strtoupper(PHP_OS)) {
            case 'WINNT':
                self::setProperty('host.fstype', 'WINNT');
                break;
            case 'WIN32':
                self::setProperty('host.fstype', 'WIN32');
                break;
            default:
                self::setProperty('host.fstype', 'UNIX');
                break;
        }

        if (defined('PHP_BINARY')) {
            self::setProperty('php.interpreter', PHP_BINARY);
        } else {
            self::setProperty('php.interpreter', getenv('PHP_COMMAND'));
        }
        self::setProperty('line.separator', PHP_EOL);
        self::setProperty('php.version', PHP_VERSION);
        self::setProperty('php.tmpdir', sys_get_temp_dir());
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            self::setProperty('user.home', getenv('HOME'));
        } else {
            self::setProperty('user.home', getenv('HOMEDRIVE') . getenv('HOMEPATH'));
        }
        self::setProperty('application.startdir', getcwd());
        self::setProperty('phing.startTime', gmdate('D, d M Y H:i:s', time()) . ' GMT');
        self::setProperty('php.tmpdir', sys_get_temp_dir());

        // try to detect machine dependent information
        $sysInfo = array();
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && function_exists("posix_uname")) {
            $sysInfo = posix_uname();
        } else {
            $sysInfo['nodename'] = php_uname('n');
            $sysInfo['machine'] = php_uname('m');
            //this is a not so ideal substition, but maybe better than nothing
            $sysInfo['domain'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "unknown";
            $sysInfo['release'] = php_uname('r');
            $sysInfo['version'] = php_uname('v');
        }

        self::setProperty("host.name", isset($sysInfo['nodename']) ? $sysInfo['nodename'] : "unknown");
        self::setProperty("host.arch", isset($sysInfo['machine']) ? $sysInfo['machine'] : "unknown");
        self::setProperty("host.domain", isset($sysInfo['domain']) ? $sysInfo['domain'] : "unknown");
        self::setProperty("host.os.release", isset($sysInfo['release']) ? $sysInfo['release'] : "unknown");
        self::setProperty("host.os.version", isset($sysInfo['version']) ? $sysInfo['version'] : "unknown");
        unset($sysInfo);
    }

    /**
     * This gets a property that was set via command line or otherwise passed into Phing.
     * "Defined" in this case means "externally defined".  The reason this method exists is to
     * provide a public means of accessing commandline properties for (e.g.) logger or listener
     * scripts.  E.g. to specify which logfile to use, PearLogger needs to be able to access
     * the pear.log.name property.
     *
     * @param  string $name
     * @return string value of found property (or null, if none found).
     */
    public static function getDefinedProperty($name)
    {
        return self::$definedProps->getProperty($name);
    }

    /**
     * This sets a property that was set via command line or otherwise passed into Phing.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed value of found property (or null, if none found).
     */
    public static function setDefinedProperty($name, $value)
    {
        return self::$definedProps->setProperty($name, $value);
    }

    /**
     * Returns property value for a System property.
     * System properties are "global" properties like application.startdir,
     * and user.dir.  Many of these correspond to similar properties in Java
     * or Ant.
     *
     * @param  string $propName
     * @return string Value of found property (or null, if none found).
     */
    public static function getProperty($propName)
    {

        // some properties are detemined on each access
        // some are cached, see below

        // default is the cached value:
        $val = isset(self::$properties[$propName]) ? self::$properties[$propName] : null;

        // special exceptions
        switch ($propName) {
            case 'user.dir':
                $val = getcwd();
                break;
        }

        return $val;
    }

    /** Retuns reference to all properties*/
    public static function &getProperties()
    {
        return self::$properties;
    }

    /**
     * @param $propName
     * @param $propValue
     * @return string
     */
    public static function setProperty($propName, $propValue)
    {
        $propName = (string) $propName;
        $oldValue = self::getProperty($propName);
        self::$properties[$propName] = $propValue;

        return $oldValue;
    }

    /**
     * @return float
     */
    public static function currentTimeMillis()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }

    /**
     * Sets the include path to PHP_CLASSPATH constant (if this has been defined).
     * @return void
     * @throws ConfigurationException - if the include_path could not be set (for some bizarre reason)
     */
    private static function setIncludePaths()
    {
        if (defined('PHP_CLASSPATH')) {
            $result = set_include_path(PHP_CLASSPATH);
            if ($result === false) {
                throw new ConfigurationException("Could not set PHP include_path.");
            }
            self::$origIniSettings['include_path'] = $result; // save original value for setting back later
        }
    }

    /**
     * Converts shorthand notation values as returned by ini_get()
     * @see http://www.php.net/ini_get
     * @param string $val
     * @return int|string
     */
    private static function convertShorthand($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Sets PHP INI values that Phing needs.
     * @return void
     */
    private static function setIni()
    {

        self::$origIniSettings['error_reporting'] = error_reporting(E_ALL);

        // We won't bother storing original max_execution_time, since 1) the value in
        // php.ini may be wrong (and there's no way to get the current value) and
        // 2) it would mean something very strange to set it to a value less than time script
        // has already been running, which would be the likely change.

        set_time_limit(0);

        self::$origIniSettings['magic_quotes_gpc'] = ini_set('magic_quotes_gpc', 'off');
        self::$origIniSettings['short_open_tag'] = ini_set('short_open_tag', 'off');
        self::$origIniSettings['default_charset'] = ini_set('default_charset', 'iso-8859-1');
        self::$origIniSettings['register_globals'] = ini_set('register_globals', 'off');
        self::$origIniSettings['allow_call_time_pass_reference'] = ini_set('allow_call_time_pass_reference', 'on');
        self::$origIniSettings['track_errors'] = ini_set('track_errors', 1);

        $mem_limit = (int) self::convertShorthand(ini_get('memory_limit'));
        if ($mem_limit < (32 * 1024 * 1024) && $mem_limit > -1) {
            // We do *not* need to save the original value here, since we don't plan to restore
            // this after shutdown (we don't trust the effectiveness of PHP's garbage collection).
            ini_set('memory_limit', '32M'); // nore: this may need to be higher for many projects
        }
    }

    /**
     * Restores [most] PHP INI values to their pre-Phing state.
     *
     * Currently the following settings are not restored:
     *  - max_execution_time (because getting current time limit is not possible)
     *  - memory_limit (which may have been increased by Phing)
     *
     * @return void
     */
    private static function restoreIni()
    {
        foreach (self::$origIniSettings as $settingName => $settingValue) {
            switch ($settingName) {
                case 'error_reporting':
                    error_reporting($settingValue);
                    break;
                default:
                    ini_set($settingName, $settingValue);
            }
        }
    }

    /**
     * Returns reference to Timer object.
     * @return Timer
     */
    public static function getTimer()
    {
        if (self::$timer === null) {
            include_once 'phing/system/util/Timer.php';
            self::$timer = new Timer();
        }

        return self::$timer;
    }

    /**
     * Start up Phing.
     * Sets up the Phing environment but does not initiate the build process.
     * @return void
     * @throws Exception - If the Phing environment cannot be initialized.
     */
    public static function startup()
    {

        // setup STDOUT and STDERR defaults
        self::initializeOutputStreams();

        // some init stuff
        self::getTimer()->start();

        self::setSystemConstants();
        self::setIncludePaths();
        self::setIni();
    }

    /**
     * Halts the system.
     * @deprecated This method is deprecated and is no longer called by Phing internally.  Any
     *              normal shutdown routines are handled by the shutdown() method.
     * @see shutdown()
     */
    public static function halt()
    {
        self::shutdown();
    }

    /**
     * Performs any shutdown routines, such as stopping timers.
     * @return void
     */
    public static function shutdown()
    {
        self::$msgOutputLevel = Project::MSG_INFO;
        self::restoreIni();
        self::getTimer()->stop();
    }
}
