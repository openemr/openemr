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

include_once 'phing/system/io/PhingFile.php';
include_once 'phing/util/FileUtils.php';
include_once 'phing/TaskAdapter.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/BuildEvent.php';
include_once 'phing/input/DefaultInputHandler.php';
include_once 'phing/types/PropertyValue.php';

/**
 *  The Phing project class. Represents a completely configured Phing project.
 *  The class defines the project and all tasks/targets. It also contains
 *  methods to start a build as well as some properties and FileSystem
 *  abstraction.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 *
 * @package   phing
 */
class Project
{

    // Logging level constants.
    const MSG_DEBUG = 4;
    const MSG_VERBOSE = 3;
    const MSG_INFO = 2;
    const MSG_WARN = 1;
    const MSG_ERR = 0;

    /** contains the targets */
    private $targets = array();
    /** global filterset (future use) */
    private $globalFilterSet = array();
    /**  all globals filters (future use) */
    private $globalFilters = array();

    /** Project properties map (usually String to String). */
    private $properties = array();

    /**
     * Map of "user" properties (as created in the Ant task, for example).
     * Note that these key/value pairs are also always put into the
     * project properties, so only the project properties need to be queried.
     * Mapping is String to String.
     */
    private $userProperties = array();

    /**
     * Map of inherited "user" properties - that are those "user"
     * properties that have been created by tasks and not been set
     * from the command line or a GUI tool.
     * Mapping is String to String.
     */
    private $inheritedProperties = array();

    /** task definitions for this project*/
    private $taskdefs = array();

    /** type definitions for this project */
    private $typedefs = array();

    /** holds ref names and a reference to the referred object*/
    private $references = array();

    /** The InputHandler being used by this project. */
    private $inputHandler;

    /* -- properties that come in via xml attributes -- */

    /** basedir (PhingFile object) */
    private $basedir;

    /** the default target name */
    private $defaultTarget = 'all';

    /** project name (required) */
    private $name;

    /** project description */
    private $description;

    /** require phing version */
    private $phingVersion;

    /** a FileUtils object */
    private $fileUtils;

    /**  Build listeneers */
    private $listeners = array();

    /**
     * Keep going flag.
     */
    private $keepGoingMode = false;

    /**
     *  Constructor, sets any default vars.
     */
    public function __construct()
    {
        $this->fileUtils = new FileUtils();
        $this->inputHandler = new DefaultInputHandler();
    }

    /**
     * Sets the input handler
     * @param InputHandler $handler
     */
    public function setInputHandler(InputHandler $handler)
    {
        $this->inputHandler = $handler;
    }

    /**
     * Retrieves the current input handler.
     * @return InputHandler
     */
    public function getInputHandler()
    {
        return $this->inputHandler;
    }

    /** inits the project, called from main app */
    public function init()
    {
        // set builtin properties
        $this->setSystemProperties();

        // load default tasks
        $taskdefs = Phing::getResourcePath("phing/tasks/defaults.properties");

        try { // try to load taskdefs
            $props = new Properties();
            $in = new PhingFile((string) $taskdefs);

            if ($in === null) {
                throw new BuildException("Can't load default task list");
            }
            $props->load($in);

            $enum = $props->propertyNames();
            foreach ($enum as $key) {
                $value = $props->getProperty($key);
                $this->addTaskDefinition($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Can't load default task list");
        }

        // load default tasks
        $typedefs = Phing::getResourcePath("phing/types/defaults.properties");

        try { // try to load typedefs
            $props = new Properties();
            $in = new PhingFile((string) $typedefs);
            if ($in === null) {
                throw new BuildException("Can't load default datatype list");
            }
            $props->load($in);

            $enum = $props->propertyNames();
            foreach ($enum as $key) {
                $value = $props->getProperty($key);
                $this->addDataTypeDefinition($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Can't load default datatype list");
        }
    }

    /** returns the global filterset (future use) */
    public function getGlobalFilterSet()
    {
        return $this->globalFilterSet;
    }

    // ---------------------------------------------------------
    // Property methods
    // ---------------------------------------------------------

    /**
     * Sets a property. Any existing property of the same name
     * is overwritten, unless it is a user property.
     * @param  string $name  The name of property to set.
     *                       Must not be <code>null</code>.
     * @param  string $value The new value of the property.
     *                       Must not be <code>null</code>.
     * @return void
     */
    public function setProperty($name, $value)
    {

        // command line properties take precedence
        if (isset($this->userProperties[$name])) {
            $this->log("Override ignored for user property " . $name, Project::MSG_VERBOSE);

            return;
        }

        if (isset($this->properties[$name])) {
            $this->log("Overriding previous definition of property " . $name, Project::MSG_VERBOSE);
        }

        $this->log("Setting project property: " . $name . " -> " . $value, Project::MSG_DEBUG);
        $this->properties[$name] = $value;
        $this->addReference($name, new PropertyValue($value));
    }

    /**
     * Sets a property if no value currently exists. If the property
     * exists already, a message is logged and the method returns with
     * no other effect.
     *
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     * @since 2.0
     */
    public function setNewProperty($name, $value)
    {
        if (isset($this->properties[$name])) {
            $this->log("Override ignored for property " . $name, Project::MSG_DEBUG);

            return;
        }
        $this->log("Setting project property: " . $name . " -> " . $value, Project::MSG_DEBUG);
        $this->properties[$name] = $value;
        $this->addReference($name, new PropertyValue($value));
    }

    /**
     * Sets a user property, which cannot be overwritten by
     * set/unset property calls. Any previous value is overwritten.
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     * @see #setProperty()
     */
    public function setUserProperty($name, $value)
    {
        $this->log("Setting user project property: " . $name . " -> " . $value, Project::MSG_DEBUG);
        $this->userProperties[$name] = $value;
        $this->properties[$name] = $value;
        $this->addReference($name, new PropertyValue($value));
    }

    /**
     * Sets a user property, which cannot be overwritten by set/unset
     * property calls. Any previous value is overwritten. Also marks
     * these properties as properties that have not come from the
     * command line.
     *
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     * @see #setProperty()
     */
    public function setInheritedProperty($name, $value)
    {
        $this->inheritedProperties[$name] = $value;
        $this->setUserProperty($name, $value);
    }

    /**
     * Sets a property unless it is already defined as a user property
     * (in which case the method returns silently).
     *
     * @param string $name The name of the property.
     *             Must not be <code>null</code>.
     * @param string $value The property value. Must not be <code>null</code>.
     */
    private function setPropertyInternal($name, $value)
    {
        if (isset($this->userProperties[$name])) {
            $this->log("Override ignored for user property " . $name, Project::MSG_VERBOSE);

            return;
        }
        $this->properties[$name] = $value;
        $this->addReference($name, new PropertyValue($value));
    }

    /**
     * Returns the value of a property, if it is set.
     *
     * @param  string $name The name of the property.
     *                      May be <code>null</code>, in which case
     *                      the return value is also <code>null</code>.
     * @return string The property value, or <code>null</code> for no match
     *                     or if a <code>null</code> name is provided.
     */
    public function getProperty($name)
    {
        if (!isset($this->properties[$name])) {
            return null;
        }
        $found = $this->properties[$name];
        // check to see if there are unresolved property references
        if (false !== strpos($found, '${')) {
            // attempt to resolve properties
            $found = $this->replaceProperties($found);
            // save resolved value
            $this->properties[$name] = $found;
        }

        return $found;
    }

    /**
     * Replaces ${} style constructions in the given value with the
     * string value of the corresponding data types.
     *
     * @param string $value The value string to be scanned for property references.
     *                      May be <code>null</code>.
     *
     * @return string the given string with embedded property names replaced
     *                by values, or <code>null</code> if the given string is
     *                <code>null</code>.
     *
     * @exception BuildException if the given value has an unclosed
     *                           property name, e.g. <code>${xxx</code>
     */
    public function replaceProperties($value)
    {
        return ProjectConfigurator::replaceProperties($this, $value, $this->properties);
    }

    /**
     * Returns the value of a user property, if it is set.
     *
     * @param  string $name The name of the property.
     *                      May be <code>null</code>, in which case
     *                      the return value is also <code>null</code>.
     * @return string The property value, or <code>null</code> for no match
     *                     or if a <code>null</code> name is provided.
     */
    public function getUserProperty($name)
    {
        if (!isset($this->userProperties[$name])) {
            return null;
        }

        return $this->userProperties[$name];
    }

    /**
     * Returns a copy of the properties table.
     * @return array A hashtable containing all properties
     *               (including user properties).
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Returns a copy of the user property hashtable
     * @return array a hashtable containing just the user properties
     */
    public function getUserProperties()
    {
        return $this->userProperties;
    }

    /**
     * Copies all user properties that have been set on the command
     * line or a GUI tool from this instance to the Project instance
     * given as the argument.
     *
     * <p>To copy all "user" properties, you will also have to call
     * {@link #copyInheritedProperties copyInheritedProperties}.</p>
     *
     * @param  Project $other the project to copy the properties to.  Must not be null.
     * @return void
     * @since phing 2.0
     */
    public function copyUserProperties(Project $other)
    {
        foreach ($this->userProperties as $arg => $value) {
            if (isset($this->inheritedProperties[$arg])) {
                continue;
            }
            $other->setUserProperty($arg, $value);
        }
    }

    /**
     * Copies all user properties that have not been set on the
     * command line or a GUI tool from this instance to the Project
     * instance given as the argument.
     *
     * <p>To copy all "user" properties, you will also have to call
     * {@link #copyUserProperties copyUserProperties}.</p>
     *
     * @param Project $other the project to copy the properties to.  Must not be null.
     *
     * @since phing 2.0
     */
    public function copyInheritedProperties(Project $other)
    {
        foreach ($this->userProperties as $arg => $value) {
            if ($other->getUserProperty($arg) !== null) {
                continue;
            }
            $other->setInheritedProperty($arg, $value);
        }
    }

    // ---------------------------------------------------------
    //  END Properties methods
    // ---------------------------------------------------------

    /**
     * Sets default target
     * @param string $targetName
     */
    public function setDefaultTarget($targetName)
    {
        $this->defaultTarget = (string) trim($targetName);
    }

    /**
     * Returns default target
     * @return string
     */
    public function getDefaultTarget()
    {
        return (string) $this->defaultTarget;
    }

    /**
     * Sets the name of the current project
     *
     * @param string $name name of project
     * @return void
     * @author Andreas Aderhold, andi@binarycloud.com
     */
    public function setName($name)
    {
        $this->name = (string) trim($name);
        $this->setProperty("phing.project.name", $this->name);
    }

    /**
     * Returns the name of this project
     *
     * @return string projectname
     * @author Andreas Aderhold, andi@binarycloud.com
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * Set the projects description
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) trim($description);
    }

    /**
     * return the description, null otherwise
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the minimum required phing version
     * @param string $version
     */
    public function setPhingVersion($version)
    {
        $version = str_replace('phing', '', strtolower($version));
        $this->phingVersion = (string) trim($version);
    }

    /**
     * Get the minimum required phing version
     * @return string
     */
    public function getPhingVersion()
    {
        if ($this->phingVersion === null) {
            $this->setPhingVersion(Phing::getPhingVersion());
        }

        return $this->phingVersion;
    }

    /**
     * Set basedir object from xm
     * @param PhingFile|string $dir
     * @throws BuildException
     */
    public function setBasedir($dir)
    {
        if ($dir instanceof PhingFile) {
            $dir = $dir->getAbsolutePath();
        }

        $dir = $this->fileUtils->normalize($dir);
        $dir = FileSystem::getFilesystem()->canonicalize($dir);

        $dir = new PhingFile((string) $dir);
        if (!$dir->exists()) {
            throw new BuildException("Basedir " . $dir->getAbsolutePath() . " does not exist");
        }
        if (!$dir->isDirectory()) {
            throw new BuildException("Basedir " . $dir->getAbsolutePath() . " is not a directory");
        }
        $this->basedir = $dir;
        $this->setPropertyInternal("project.basedir", $this->basedir->getAbsolutePath());
        $this->log("Project base dir set to: " . $this->basedir->getPath(), Project::MSG_VERBOSE);

        // [HL] added this so that ./ files resolve correctly.  This may be a mistake ... or may be in wrong place.
        chdir($dir->getAbsolutePath());
    }

    /**
     * Returns the basedir of this project
     *
     * @return PhingFile      Basedir PhingFile object
     *
     * @throws BuildException
     *
     * @author  Andreas Aderhold, andi@binarycloud.com
     */
    public function getBasedir()
    {
        if ($this->basedir === null) {
            try { // try to set it
                $this->setBasedir(".");
            } catch (BuildException $exc) {
                throw new BuildException("Can not set default basedir. " . $exc->getMessage());
            }
        }

        return $this->basedir;
    }

    /**
     * Set &quot;keep-going&quot; mode. In this mode Ant will try to execute
     * as many targets as possible. All targets that do not depend
     * on failed target(s) will be executed.  If the keepGoing settor/getter
     * methods are used in conjunction with the <code>ant.executor.class</code>
     * property, they will have no effect.
     * @param keepGoingMode &quot;keep-going&quot; mode
     */
    public function setKeepGoingMode($keepGoingMode)
    {
        $this->keepGoingMode = $keepGoingMode;
    }

    /**
     * Return the keep-going mode.  If the keepGoing settor/getter
     * methods are used in conjunction with the <code>phing.executor.class</code>
     * property, they will have no effect.
     * @return bool &quot;keep-going&quot; mode
     */
    public function isKeepGoingMode()
    {
        return $this->keepGoingMode;
    }

    /**
     * Sets system properties and the environment variables for this project.
     *
     * @return void
     */
    public function setSystemProperties()
    {

        // first get system properties
        $systemP = array_merge(self::getProperties(), Phing::getProperties());
        foreach ($systemP as $name => $value) {
            $this->setPropertyInternal($name, $value);
        }

        // and now the env vars
        foreach ($_SERVER as $name => $value) {
            // skip arrays
            if (is_array($value)) {
                continue;
            }
            $this->setPropertyInternal('env.' . $name, $value);
        }

        return true;
    }

    /**
     * Adds a task definition.
     * @param string $name      Name of tag.
     * @param string $class     The class path to use.
     * @param string $classpath The classpat to use.
     */
    public function addTaskDefinition($name, $class, $classpath = null)
    {
        if ($class === "") {
            $this->log("Task $name has no class defined.", Project::MSG_ERR);
        } elseif (!isset($this->taskdefs[$name])) {
            Phing::import($class, $classpath);
            $this->taskdefs[$name] = $class;
            $this->log("  +Task definition: $name ($class)", Project::MSG_DEBUG);
        } else {
            $this->log("Task $name ($class) already registered, skipping", Project::MSG_VERBOSE);
        }
    }

    /**
     * Returns the task definitions
     * @return array
     */
    public function getTaskDefinitions()
    {
        return $this->taskdefs;
    }

    /**
     * Adds a data type definition.
     * @param string $typeName  Name of the type.
     * @param string $typeClass The class to use.
     * @param string $classpath The classpath to use.
     */
    public function addDataTypeDefinition($typeName, $typeClass, $classpath = null)
    {
        if (!isset($this->typedefs[$typeName])) {
            Phing::import($typeClass, $classpath);
            $this->typedefs[$typeName] = $typeClass;
            $this->log("  +User datatype: $typeName ($typeClass)", Project::MSG_DEBUG);
        } else {
            $this->log("Type $typeName ($typeClass) already registered, skipping", Project::MSG_VERBOSE);
        }
    }

    /**
     * Returns the data type definitions
     * @return array
     */
    public function getDataTypeDefinitions()
    {
        return $this->typedefs;
    }

    /**
     * Add a new target to the project
     * @param string $targetName
     * @param Target $target
     * @throws BuildException
     */
    public function addTarget($targetName, $target)
    {
        if (isset($this->targets[$targetName])) {
            throw new BuildException("Duplicate target: $targetName");
        }
        $this->addOrReplaceTarget($targetName, $target);
    }

    /**
     * Adds or replaces a target in the project
     * @param string $targetName
     * @param Target $target
     */
    public function addOrReplaceTarget($targetName, &$target)
    {
        $this->log("  +Target: $targetName", Project::MSG_DEBUG);
        $target->setProject($this);
        $this->targets[$targetName] = $target;

        $ctx = $this->getReference("phing.parsing.context");
        $current = $ctx->getCurrentTargets();
        $current[$targetName] = $target;
    }

    /**
     * Returns the available targets
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Create a new task instance and return reference to it. This method is
     * sorta factory like. A _local_ instance is created and a reference returned to
     * that instance. Usually PHP destroys local variables when the function call
     * ends. But not if you return a reference to that variable.
     * This is kinda error prone, because if no reference exists to the variable
     * it is destroyed just like leaving the local scope with primitive vars. There's no
     * central place where the instance is stored as in other OOP like languages.
     *
     * [HL] Well, ZE2 is here now, and this is  still working. We'll leave this alone
     * unless there's any good reason not to.
     *
     * @param  string         $taskType Task name
     * @return Task           A task object
     * @throws BuildException
     *                                 Exception
     */
    public function createTask($taskType)
    {
        try {
            $classname = "";
            $tasklwr = strtolower($taskType);
            foreach ($this->taskdefs as $name => $class) {
                if (strtolower($name) === $tasklwr) {
                    $classname = $class;
                    break;
                }
            }

            if ($classname === "") {
                return null;
            }

            $cls = Phing::import($classname);

            if (!class_exists($cls)) {
                throw new BuildException("Could not instantiate class $cls, even though a class was specified. (Make sure that the specified class file contains a class with the correct name.)");
            }

            $o = new $cls();

            if ($o instanceof Task) {
                $task = $o;
            } else {
                $this->log("  (Using TaskAdapter for: $taskType)", Project::MSG_DEBUG);
                // not a real task, try adapter
                $taskA = new TaskAdapter();
                $taskA->setProxy($o);
                $task = $taskA;
            }
            $task->setProject($this);
            $task->setTaskType($taskType);
            // set default value, can be changed by the user
            $task->setTaskName($taskType);
            $this->log("  +Task: " . $taskType, Project::MSG_DEBUG);
        } catch (Exception $t) {
            throw new BuildException("Could not create task of type: " . $taskType, $t);
        }
        // everything fine return reference
        return $task;
    }

    /**
     * Creates a new condition and returns the reference to it
     *
     * @param  string         $conditionType
     * @return Condition
     * @throws BuildException
     */
    public function createCondition($conditionType)
    {
        try {
            $classname = "";
            $tasklwr = strtolower($conditionType);
            foreach ($this->typedefs as $name => $class) {
                if (strtolower($name) === $tasklwr) {
                    $classname = $class;
                    break;
                }
            }

            if ($classname === "") {
                return null;
            }

            $cls = Phing::import($classname);

            if (!class_exists($cls)) {
                throw new BuildException("Could not instantiate class $cls, even though a class was specified. (Make sure that the specified class file contains a class with the correct name.)");
            }

            $o = new $cls();
            if ($o instanceof Condition) {
                return $o;
            } else {
                throw new BuildException("Not actually a condition");
            }
        } catch (Exception $e) {
            throw new BuildException("Could not create condition of type: " . $conditionType, $e);
        }
    }

    /**
     * Create a datatype instance and return reference to it
     * See createTask() for explanation how this works
     *
     * @param  string         $typeName Type name
     * @return object         A datatype object
     * @throws BuildException
     *                                 Exception
     */
    public function createDataType($typeName)
    {
        try {
            $cls = "";
            $typelwr = strtolower($typeName);
            foreach ($this->typedefs as $name => $class) {
                if (strtolower($name) === $typelwr) {
                    $cls = StringHelper::unqualify($class);
                    break;
                }
            }

            if ($cls === "") {
                return null;
            }

            if (!class_exists($cls)) {
                throw new BuildException("Could not instantiate class $cls, even though a class was specified. (Make sure that the specified class file contains a class with the correct name.)");
            }

            $type = new $cls();
            $this->log("  +Type: $typeName", Project::MSG_DEBUG);
            if (!($type instanceof DataType)) {
                throw new Exception("$class is not an instance of phing.types.DataType");
            }
            if ($type instanceof ProjectComponent) {
                $type->setProject($this);
            }
        } catch (Exception $t) {
            throw new BuildException("Could not create type: $typeName", $t);
        }
        // everything fine return reference
        return $type;
    }

    /**
     * Executes a list of targets
     *
     * @param  array          $targetNames List of target names to execute
     * @return void
     * @throws BuildException
     */
    public function executeTargets($targetNames)
    {
        foreach ($targetNames as $tname) {
            $this->executeTarget($tname);
        }
    }

    /**
     * Executes a target
     *
     * @param  string         $targetName Name of Target to execute
     * @return void
     * @throws BuildException
     */
    public function executeTarget($targetName)
    {

        // complain about executing void
        if ($targetName === null) {
            throw new BuildException("No target specified");
        }

        // invoke topological sort of the target tree and run all targets
        // until targetName occurs.
        $sortedTargets = $this->_topoSort($targetName, $this->targets);

        $curIndex = (int) 0;
        $curTarget = null;
        $thrownException = null;
        $buildException = null;
        do {
            try {
                $curTarget = $sortedTargets[$curIndex++];
                $curTarget->performTasks();
            } catch (BuildException $exc) {
                if (!($this->keepGoingMode)) {
                    throw $exc;
                }
                $thrownException = $exc;
            }
            if ($thrownException != null) {
                if ($thrownException instanceof BuildException) {
                    $this->log(
                        "Target '" . $curTarget->getName()
                        . "' failed with message '"
                        . $thrownException->getMessage() . "'.", Project::MSG_ERR);
                    // only the first build exception is reported
                    if ($buildException === null) {
                        $buildException = $thrownException;
                    }
                } else {
                    $this->log(
                        "Target '" . $curTarget->getName()
                        . "' failed with message '"
                        . $thrownException->getMessage() . "'." . PHP_EOL
                        . $thrownException->getTraceAsString(), Project::MSG_ERR
                    );
                    if ($buildException === null) {
                        $buildException = new BuildException($thrownException);
                    }
                }
            }
        } while ($curTarget->getName() !== $targetName);

        if ($buildException !== null) {
            throw $buildException;
        }
    }

    /**
     * Helper function
     * @param $fileName
     * @param null $rootDir
     * @throws IOException
     * @return \PhingFile
     */
    public function resolveFile($fileName, $rootDir = null)
    {
        if ($rootDir === null) {
            return $this->fileUtils->resolveFile($this->basedir, $fileName);
        } else {
            return $this->fileUtils->resolveFile($rootDir, $fileName);
        }
    }

    /**
     * Topologically sort a set of Targets.
     * @param  string $root is the (String) name of the root Target. The sort is
     *                         created in such a way that the sequence of Targets until the root
     *                         target is the minimum possible such sequence.
     * @param  array $targets is a array representing a "name to Target" mapping
     * @throws BuildException
     * @throws Exception
     * @return array of Strings with the names of the targets in
     *               sorted order.
     */
    public function _topoSort($root, &$targets)
    {

        $root = (string) $root;
        $ret = array();
        $state = array();
        $visiting = array();

        // We first run a DFS based sort using the root as the starting node.
        // This creates the minimum sequence of Targets to the root node.
        // We then do a sort on any remaining unVISITED targets.
        // This is unnecessary for doing our build, but it catches
        // circular dependencies or missing Targets on the entire
        // dependency tree, not just on the Targets that depend on the
        // build Target.

        $this->_tsort($root, $targets, $state, $visiting, $ret);

        $retHuman = "";
        for ($i = 0, $_i = count($ret); $i < $_i; $i++) {
            $retHuman .= $ret[$i]->toString() . " ";
        }
        $this->log("Build sequence for target '$root' is: $retHuman", Project::MSG_VERBOSE);

        $keys = array_keys($targets);
        while ($keys) {
            $curTargetName = (string) array_shift($keys);
            if (!isset($state[$curTargetName])) {
                $st = null;
            } else {
                $st = (string) $state[$curTargetName];
            }

            if ($st === null) {
                $this->_tsort($curTargetName, $targets, $state, $visiting, $ret);
            } elseif ($st === "VISITING") {
                throw new Exception("Unexpected node in visiting state: $curTargetName");
            }
        }

        $retHuman = "";
        for ($i = 0, $_i = count($ret); $i < $_i; $i++) {
            $retHuman .= $ret[$i]->toString() . " ";
        }
        $this->log("Complete build sequence is: $retHuman", Project::MSG_VERBOSE);

        return $ret;
    }

    // one step in a recursive DFS traversal of the target dependency tree.
    // - The array "state" contains the state (VISITED or VISITING or null)
    //   of all the target names.
    // - The stack "visiting" contains a stack of target names that are
    //   currently on the DFS stack. (NB: the target names in "visiting" are
    //    exactly the target names in "state" that are in the VISITING state.)
    // 1. Set the current target to the VISITING state, and push it onto
    //    the "visiting" stack.
    // 2. Throw a BuildException if any child of the current node is
    //    in the VISITING state (implies there is a cycle.) It uses the
    //    "visiting" Stack to construct the cycle.
    // 3. If any children have not been VISITED, tsort() the child.
    // 4. Add the current target to the Vector "ret" after the children
    //    have been visited. Move the current target to the VISITED state.
    //    "ret" now contains the sorted sequence of Targets up to the current
    //    Target.

    /**
     * @param $root
     * @param $targets
     * @param $state
     * @param $visiting
     * @param $ret
     * @throws BuildException
     * @throws Exception
     */
    public function _tsort($root, &$targets, &$state, &$visiting, &$ret)
    {
        $state[$root] = "VISITING";
        $visiting[] = $root;

        if (!isset($targets[$root]) || !($targets[$root] instanceof Target)) {
            $target = null;
        } else {
            $target = $targets[$root];
        }

        // make sure we exist
        if ($target === null) {
            $sb = "Target '$root' does not exist in this project.";
            array_pop($visiting);
            if (!empty($visiting)) {
                $parent = (string) $visiting[count($visiting) - 1];
                $sb .= " It is a dependency of target '$parent'.";
            }
            throw new BuildException($sb);
        }

        $deps = $target->getDependencies();

        while ($deps) {
            $cur = (string) array_shift($deps);
            if (!isset($state[$cur])) {
                $m = null;
            } else {
                $m = (string) $state[$cur];
            }
            if ($m === null) {
                // not been visited
                $this->_tsort($cur, $targets, $state, $visiting, $ret);
            } elseif ($m == "VISITING") {
                // currently visiting this node, so have a cycle
                throw $this->_makeCircularException($cur, $visiting);
            }
        }

        $p = (string) array_pop($visiting);
        if ($root !== $p) {
            throw new Exception("Unexpected internal error: expected to pop $root but got $p");
        }

        $state[$root] = "VISITED";
        $ret[] = $target;
    }

    /**
     * @param string $end
     * @param array $stk
     * @return BuildException
     */
    public function _makeCircularException($end, $stk)
    {
        $sb = "Circular dependency: $end";
        do {
            $c = (string) array_pop($stk);
            $sb .= " <- " . $c;
        } while ($c != $end);

        return new BuildException($sb);
    }

    /**
     * Adds a reference to an object. This method is called when the parser
     * detects a id="foo" attribute. It passes the id as $name and a reference
     * to the object assigned to this id as $value
     * @param string $name
     * @param object $object
     */
    public function addReference($name, $object)
    {
        if (isset($this->references[$name])) {
            $this->log("Overriding previous definition of reference to $name", Project::MSG_VERBOSE);
        }
        $this->log("Adding reference: $name -> " . get_class($object), Project::MSG_DEBUG);
        $this->references[$name] = $object;
    }

    /**
     * Returns the references array.
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Returns a specific reference.
     * @param  string $key The reference id/key.
     * @return object Reference or null if not defined
     */
    public function getReference($key)
    {
        if (isset($this->references[$key])) {
            return $this->references[$key];
        }

        return null; // just to be explicit
    }

    /**
     * Abstracting and simplifyling Logger calls for project messages
     * @param string $msg
     * @param int    $level
     */
    public function log($msg, $level = Project::MSG_INFO)
    {
        $this->logObject($this, $msg, $level);
    }

    /**
     * @param $obj
     * @param $msg
     * @param $level
     */
    public function logObject($obj, $msg, $level)
    {
        $this->fireMessageLogged($obj, $msg, $level);
    }

    /**
     * @param BuildListener $listener
     */
    public function addBuildListener(BuildListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param BuildListener $listener
     */
    public function removeBuildListener(BuildListener $listener)
    {
        $newarray = array();
        for ($i = 0, $size = count($this->listeners); $i < $size; $i++) {
            if ($this->listeners[$i] !== $listener) {
                $newarray[] = $this->listeners[$i];
            }
        }
        $this->listeners = $newarray;
    }

    /**
     * @return array
     */
    public function getBuildListeners()
    {
        return $this->listeners;
    }

    public function fireBuildStarted()
    {
        $event = new BuildEvent($this);
        foreach ($this->listeners as $listener) {
            $listener->buildStarted($event);
        }
    }

    /**
     * @param Exception $exception
     */
    public function fireBuildFinished($exception)
    {
        $event = new BuildEvent($this);
        $event->setException($exception);
        foreach ($this->listeners as $listener) {
            $listener->buildFinished($event);
        }
    }

    /**
     * @param $target
     */
    public function fireTargetStarted($target)
    {
        $event = new BuildEvent($target);
        foreach ($this->listeners as $listener) {
            $listener->targetStarted($event);
        }
    }

    /**
     * @param $target
     * @param $exception
     */
    public function fireTargetFinished($target, $exception)
    {
        $event = new BuildEvent($target);
        $event->setException($exception);
        foreach ($this->listeners as $listener) {
            $listener->targetFinished($event);
        }
    }

    /**
     * @param $task
     */
    public function fireTaskStarted($task)
    {
        $event = new BuildEvent($task);
        foreach ($this->listeners as $listener) {
            $listener->taskStarted($event);
        }
    }

    /**
     * @param $task
     * @param $exception
     */
    public function fireTaskFinished($task, $exception)
    {
        $event = new BuildEvent($task);
        $event->setException($exception);
        foreach ($this->listeners as $listener) {
            $listener->taskFinished($event);
        }
    }

    /**
     * @param $event
     * @param $message
     * @param $priority
     */
    public function fireMessageLoggedEvent($event, $message, $priority)
    {
        $event->setMessage($message, $priority);
        foreach ($this->listeners as $listener) {
            $listener->messageLogged($event);
        }
    }

    /**
     * @param $object
     * @param $message
     * @param $priority
     */
    public function fireMessageLogged($object, $message, $priority)
    {
        $this->fireMessageLoggedEvent(new BuildEvent($object), $message, $priority);
    }
}
