<?php
/*
 * $Id: c60780a77af28eabb790e7b9f1ae725dfd4afbc5 $
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

include_once 'phing/system/io/BufferedReader.php';
include_once 'phing/system/io/FileReader.php';
include_once 'phing/BuildException.php';
include_once 'phing/system/lang/FileNotFoundException.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/parser/PhingXMLContext.php';
include_once 'phing/IntrospectionHelper.php';

/**
 * The datatype handler class.
 *
 * This class handles the occurrence of registered datatype tags like
 * FileSet
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: c60780a77af28eabb790e7b9f1ae725dfd4afbc5 $
 * @package   phing.parser
 */
class ProjectConfigurator
{

    public $project;
    public $locator;

    public $buildFile;
    public $buildFileParent;

    /** Synthetic target that will be called at the end to the parse phase */
    private $parseEndTarget;

    /** Name of the current project */
    private $currentProjectName;

    private $isParsing = true;

    /**
     * Indicates whether the project tag attributes are to be ignored
     * when processing a particular build file.
     */
    private $ignoreProjectTag = false;

    /**
     * Static call to ProjectConfigurator. Use this to configure a
     * project. Do not use the new operator.
     *
     * @param  Project $project  the Project instance this configurator should use
     * @param  PhingFile $buildFile  the buildfile object the parser should use
     */
    public static function configureProject(Project $project, PhingFile $buildFile)
    {
        $pc = new ProjectConfigurator($project, $buildFile);
        $pc->parse();
    }

    /**
     * Constructs a new ProjectConfigurator object
     * This constructor is private. Use a static call to
     * <code>configureProject</code> to configure a project.
     *
     * @param  Project $project     the Project instance this configurator should use
     * @param  PhingFile $buildFile the buildfile object the parser should use
     */
    public function __construct(Project $project, PhingFile $buildFile)
    {
        $this->project = $project;
        $this->buildFile = new PhingFile($buildFile->getAbsolutePath());
        $this->buildFileParent = new PhingFile($this->buildFile->getParent());
        $this->parseEndTarget = new Target();
    }

    /**
     * find out the build file
     * @return PhingFile the build file to which the xml context belongs
     */
    public function getBuildFile()
    {
        return $this->buildFile;
    }

    /**
     * find out the parent build file of this build file
     * @return PhingFile the parent build file of this build file
     */
    public function getBuildFileParent()
    {
        return $this->buildFileParent;
    }

    /**
     * find out the current project name
     * @return string current project name
     */
    public function getCurrentProjectName()
    {
        return $this->currentProjectName;
    }

    /**
     * set the name of the current project
     * @param string $name name of the current project
     */
    public function setCurrentProjectName($name)
    {
        $this->currentProjectName = $name;
    }

    /**
     * tells whether the project tag is being ignored
     * @return bool whether the project tag is being ignored
     */
    public function isIgnoringProjectTag()
    {
        return $this->ignoreProjectTag;
    }

    /**
     * sets the flag to ignore the project tag
     * @param bool $flag flag to ignore the project tag
     */
    public function setIgnoreProjectTag($flag)
    {
        $this->ignoreProjectTag = $flag;
    }

    /**
     * @return bool
     */
    public function isParsing()
    {
        return $this->isParsing;
    }

    /**
     * Creates the ExpatParser, sets root handler and kick off parsing
     * process.
     *
     * @throws BuildException if there is any kind of execption during
     *                        the parsing process
     */
    protected function parse()
    {
        try {
            // get parse context
            $ctx = $this->project->getReference("phing.parsing.context");
            if (null == $ctx) {
                // make a new context and register it with project
                $ctx = new PhingXMLContext($this->project);
                $this->project->addReference("phing.parsing.context", $ctx);
            }

            //record this parse with context
            $ctx->addImport($this->buildFile);

            if (count($ctx->getImportStack()) > 1) {
                $currentImplicit = $ctx->getImplicitTarget();
                $currentTargets = $ctx->getCurrentTargets();

                $newCurrent = new Target();
                $newCurrent->setProject($this->project);
                $newCurrent->setName('');
                $ctx->setCurrentTargets(array());
                $ctx->setImplicitTarget($newCurrent);

                // this is an imported file
                // modify project tag parse behavior
                $this->setIgnoreProjectTag(true);
                $this->_parse($ctx);
                $newCurrent->main();

                $ctx->setImplicitTarget($currentImplicit);
                $ctx->setCurrentTargets($currentTargets);
            } else {
                $ctx->setCurrentTargets(array());
                $this->_parse($ctx);
                $ctx->getImplicitTarget()->main();
            }

        } catch (Exception $exc) {
            //throw new BuildException("Error reading project file", $exc);
            throw $exc;
        }
    }

    /**
     * @param PhingXMLContext $ctx
     * @throws ExpatParseException
     */
    protected function _parse(PhingXMLContext $ctx)
    {
        // push action onto global stack
        $ctx->startConfigure($this);

        $reader = new BufferedReader(new FileReader($this->buildFile));
        $parser = new ExpatParser($reader);
        $parser->parserSetOption(XML_OPTION_CASE_FOLDING, 0);
        $parser->setHandler(new RootHandler($parser, $this, $ctx));
        $this->project->log("parsing buildfile " . $this->buildFile->getName(), Project::MSG_VERBOSE);
        $parser->parse();
        $reader->close();

        // mark parse phase as completed
        $this->isParsing = false;
        // execute delayed tasks
        $this->parseEndTarget->main();
        // pop this action from the global stack
        $ctx->endConfigure();
    }

    /**
     * Delay execution of a task until after the current parse phase has
     * completed.
     *
     * @param Task $task Task to execute after parse
     */
    public function delayTaskUntilParseEnd($task)
    {
        $this->parseEndTarget->addTask($task);
    }

    /**
     * Configures an element and resolves eventually given properties.
     *
     * @param mixed $target element to configure
     * @param array $attrs element's attributes
     * @param Project $project project this element belongs to
     * @throws BuildException
     * @throws Exception
     */
    public static function configure($target, $attrs, Project $project)
    {

        if ($target instanceof TaskAdapter) {
            $target = $target->getProxy();
        }

        // if the target is an UnknownElement, this means that the tag had not been registered
        // when the enclosing element (task, target, etc.) was configured.  It is possible, however,
        // that the tag was registered (e.g. using <taskdef>) after the original configuration.
        // ... so, try to load it again:
        if ($target instanceof UnknownElement) {
            $tryTarget = $project->createTask($target->getTaskType());
            if ($tryTarget) {
                $target = $tryTarget;
            }
        }

        $bean = get_class($target);
        $ih = IntrospectionHelper::getHelper($bean);

        foreach ($attrs as $key => $value) {
            if ($key == 'id') {
                continue;
                // throw new BuildException("Id must be set Extermnally");
            }
            $value = self::replaceProperties($project, $value, $project->getProperties());
            try { // try to set the attribute
                $ih->setAttribute($project, $target, strtolower($key), $value);
            } catch (BuildException $be) {
                // id attribute must be set externally
                if ($key !== "id") {
                    throw $be;
                }
            }
        }
    }

    /**
     * Configures the #CDATA of an element.
     *
     * @param  object  the project this element belongs to
     * @param  object  the element to configure
     * @param  string  the element's #CDATA
     */
    public static function addText($project, $target, $text = null)
    {
        if ($text === null || strlen(trim($text)) === 0) {
            return;
        }
        $ih = IntrospectionHelper::getHelper(get_class($target));
        $text = self::replaceProperties($project, $text, $project->getProperties());
        $ih->addText($project, $target, $text);
    }

    /**
     * Stores a configured child element into its parent object
     *
     * @param  object  the project this element belongs to
     * @param  object  the parent element
     * @param  object  the child element
     * @param  string  the XML tagname
     */
    public static function storeChild($project, $parent, $child, $tag)
    {
        $ih = IntrospectionHelper::getHelper(get_class($parent));
        $ih->storeElement($project, $parent, $child, $tag);
    }

    // The following three properties are a sort of hack
    // to enable a static function to serve as the callback
    // for preg_replace_callback().  Clearly we cannot use object
    // variables, since the replaceProperties() is called statically.
    // This is IMO better than using global variables in the callback.

    private static $propReplaceProject;
    private static $propReplaceProperties;
    private static $propReplaceLogLevel = Project::MSG_VERBOSE;

    /**
     * Replace ${} style constructions in the given value with the
     * string value of the corresponding data types. This method is
     * static.
     *
     * @param object|Project $project the project that should be used for property look-ups
     * @param  string $value the string to be scanned for property references
     * @param  array $keys property keys
     * @param int $logLevel the level of generated log messages
     * @return string  the replaced string or <code>null</code> if the string
     *                          itself was null
     */
    public static function replaceProperties(Project $project, $value, $keys, $logLevel = Project::MSG_VERBOSE)
    {

        if ($value === null) {
            return null;
        }

        // These are a "hack" to support static callback for preg_replace_callback()

        // make sure these get initialized every time
        self::$propReplaceProperties = $keys;
        self::$propReplaceProject = $project;
        self::$propReplaceLogLevel = $logLevel;

        // Because we're not doing anything special (like multiple passes),
        // regex is the simplest / fastest.  PropertyTask, though, uses
        // the old parsePropertyString() method, since it has more stringent
        // requirements.

        $sb = $value;
        $iteration = 0;

        // loop to recursively replace tokens
        while (strpos($sb, '${') !== false) {
            $sb = preg_replace_callback(
                '/\$\{([^\$}]+)\}/',
                array('ProjectConfigurator', 'replacePropertyCallback'),
                $sb
            );

            // keep track of iterations so we can break out of otherwise infinite loops.
            $iteration++;
            if ($iteration == 5) {
                return $sb;
            }
        }

        return $sb;
    }

    /**
     * Private [static] function for use by preg_replace_callback to replace a single param.
     * This method makes use of a static variable to hold the
     * @param $matches
     * @return string
     */
    private static function replacePropertyCallback($matches)
    {
        $propertyName = $matches[1];
        if (!isset(self::$propReplaceProperties[$propertyName])) {
            self::$propReplaceProject->log(
                'Property ${' . $propertyName . '} has not been set.',
                self::$propReplaceLogLevel
            );

            return $matches[0];
        } else {
            self::$propReplaceProject->log(
                'Property ${' . $propertyName . '} => ' . self::$propReplaceProperties[$propertyName],
                self::$propReplaceLogLevel
            );
        }

        $propertyValue = self::$propReplaceProperties[$propertyName];

        if (is_bool($propertyValue)) {
            if ($propertyValue === true) {
                $propertyValue = "true";
            } else {
                $propertyValue = "false";
            }
        }

        return $propertyValue;
    }

    /**
     * Scan Attributes for the id attribute and maybe add a reference to
     * project.
     *
     * @param object the element's object
     * @param array  the element's attributes
     */
    public function configureId($target, $attr)
    {
        if (isset($attr['id']) && $attr['id'] !== null) {
            $this->project->addReference($attr['id'], $target);
        }
    }
}
