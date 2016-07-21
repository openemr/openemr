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

include_once 'phing/Task.php';
include_once 'phing/BuildException.php';
include_once 'phing/lib/Capsule.php';
include_once 'phing/util/StringHelper.php';

/**
 * A phing task for generating output by using Capsule.
 *
 * This is based on the interface to TexenTask from Apache's Velocity engine.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 *
 * @package   phing.tasks.ext
 */
class CapsuleTask extends Task
{
    /**
     * Capsule "template" engine.
     * @var Capsule
     */
    protected $context;

    /**
     * Any vars assigned via the build file.
     * @var array AssignedVar[]
     */
    protected $assignedVars = array();

    /**
     * This is the control template that governs the output.
     * It may or may not invoke the services of worker
     * templates.
     * @var string
     */
    protected $controlTemplate;

    /**
     * This is where Velocity will look for templates
     * using the file template loader.
     * @var string
     */
    protected $templatePath;

    /**
     * This is where texen will place all the output
     * that is a product of the generation process.
     * @var string
     */
    protected $outputDirectory;

    /**
     * This is the file where the generated text
     * will be placed.
     * @var string
     */
    protected $outputFile;

    /**
     * <p>
     * These are properties that are fed into the
     * initial context from a properties file. This
     * is simply a convenient way to set some values
     * that you wish to make available in the context.
     * </p>
     * <p>
     * These values are not critical, like the template path
     * or output path, but allow a convenient way to
     * set a value that may be specific to a particular
     * generation task.
     * </p>
     * <p>
     * For example, if you are generating scripts to allow
     * user to automatically create a database, then
     * you might want the <code>$databaseName</code>
     * to be placed
     * in the initial context so that it is available
     * in a script that might look something like the
     * following:
     * <code><pre>
     * #!bin/sh
     *
     * echo y | mysqladmin create $databaseName
     * </pre></code>
     * The value of <code>$databaseName</code> isn't critical to
     * output, and you obviously don't want to change
     * the ant task to simply take a database name.
     * So initial context values can be set with
     * properties file.
     *
     * @var array
     */
    protected $contextProperties;

    // -----------------------------------------------------------------------
    // The following getters & setters are used by phing to set properties
    // specified in the XML for the capsule task.
    // -----------------------------------------------------------------------

    /**
     * [REQUIRED] Set the control template for the
     * generating process.
     * @param  string $controlTemplate
     * @return void
     */
    public function setControlTemplate($controlTemplate)
    {
        $this->controlTemplate = $controlTemplate;
    }

    /**
     * Get the control template for the
     * generating process.
     * @return string
     */
    public function getControlTemplate()
    {
        return $this->controlTemplate;
    }

    /**
     * [REQUIRED] Set the path where Velocity will look
     * for templates using the file template
     * loader.
     * @param $templatePath
     * @return void
     */
    public function setTemplatePath($templatePath)
    {
        $resolvedPath = "";
        $tok = strtok($templatePath, ",");
        while ($tok) {
            // resolve relative path from basedir and leave
            // absolute path untouched.
            $fullPath = $this->project->resolveFile($tok);
            $cpath = $fullPath->getCanonicalPath();
            if ($cpath === false) {
                $this->log("Template directory does not exist: " . $fullPath->getAbsolutePath());
            } else {
                $resolvedPath .= $cpath;
            }
            $tok = strtok(",");
            if ($tok) {
                $resolvedPath .= ",";
            }
        }
        $this->templatePath = $resolvedPath;
    }

    /**
     * Get the path where Velocity will look
     * for templates using the file template
     * loader.
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * [REQUIRED] Set the output directory. It will be
     * created if it doesn't exist.
     * @param  PhingFile $outputDirectory
     * @return void
     * @throws Exception
     */
    public function setOutputDirectory(PhingFile $outputDirectory)
    {
        try {
            if (!$outputDirectory->exists()) {
                $this->log(
                    "Output directory does not exist, creating: " . $outputDirectory->getPath(),
                    Project::MSG_VERBOSE
                );
                if (!$outputDirectory->mkdirs()) {
                    throw new IOException("Unable to create Ouptut directory: " . $outputDirectory->getAbsolutePath());
                }
            }
            $this->outputDirectory = $outputDirectory->getCanonicalPath();
        } catch (IOException $ioe) {
            throw new BuildException($ioe);
        }
    }

    /**
     * Get the output directory.
     * @return string
     */
    public function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * [REQUIRED] Set the output file for the
     * generation process.
     * @param  string $outputFile (TODO: change this to File)
     * @return void
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     * Get the output file for the
     * generation process.
     * @return string
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * Set the context properties that will be
     * fed into the initial context be the
     * generating process starts.
     * @param  string $file
     * @throws BuildException
     * @return void
     */
    public function setContextProperties($file)
    {
        $sources = explode(",", $file);
        $this->contextProperties = new Properties();

        // Always try to get the context properties resource
        // from a file first. Templates may be taken from a JAR
        // file but the context properties resource may be a
        // resource in the filesystem. If this fails than attempt
        // to get the context properties resource from the
        // classpath.
        for ($i = 0, $sourcesLength = count($sources); $i < $sourcesLength; $i++) {
            $source = new Properties();

            try {

                // resolve relative path from basedir and leave
                // absolute path untouched.
                $fullPath = $this->project->resolveFile($sources[$i]);
                $this->log("Using contextProperties file: " . $fullPath->toString());
                $source->load($fullPath);

            } catch (Exception $e) {

                throw new BuildException("Context properties file " . $sources[$i] .
                    " could not be found in the file system!");

            }

            $keys = $source->keys();

            foreach ($keys as $key) {
                $name = $key;
                $value = $this->project->replaceProperties($source->getProperty($name));
                $this->contextProperties->setProperty($name, $value);
            }
        }
    }

    /**
     * Get the context properties that will be
     * fed into the initial context be the
     * generating process starts.
     * @return Properties
     */
    public function getContextProperties()
    {
        return $this->contextProperties;
    }

    /**
     * Creates an "AssignedVar" class.
     */
    public function createAssign()
    {
        $a = new AssignedVar();
        $this->assignedVars[] = $a;

        return $a;
    }

    // ---------------------------------------------------------------
    // End of XML setters & getters
    // ---------------------------------------------------------------

    /**
     * Creates a Smarty object.
     *
     * @return Capsule   initialized (cleared) Smarty context.
     * @throws Exception the execute method will catch
     *                   and rethrow as a <code>BuildException</code>
     */
    public function initControlContext()
    {
        $this->context->clear();
        foreach ($this->assignedVars as $var) {
            $this->context->put($var->getName(), $var->getValue());
        }

        return $this->context;
    }

    /**
     * Execute the input script with Velocity
     *
     * @throws BuildException
     *                        BuildExceptions are thrown when required attributes are missing.
     *                        Exceptions thrown by Velocity are rethrown as BuildExceptions.
     */
    public function main()
    {

        // Make sure the template path is set.
        if (empty($this->templatePath)) {
            throw new BuildException("The template path needs to be defined!");
        }

        // Make sure the control template is set.
        if ($this->controlTemplate === null) {
            throw new BuildException("The control template needs to be defined!");
        }

        // Make sure the output directory is set.
        if ($this->outputDirectory === null) {
            throw new BuildException("The output directory needs to be defined!");
        }

        // Make sure there is an output file.
        if ($this->outputFile === null) {
            throw new BuildException("The output file needs to be defined!");
        }

        // Setup Smarty runtime.

        // Smarty uses one object to store properties and to store
        // the context for the template (unlike Velocity).  We setup this object, calling it
        // $this->context, and then initControlContext simply zeros out
        // any assigned variables.
        $this->context = new Capsule();

        if ($this->templatePath !== null) {
            $this->log("Using templatePath: " . $this->templatePath);
            $this->context->setTemplatePath($this->templatePath);
        }

        // Make sure the output directory exists, if it doesn't
        // then create it.
        $outputDir = new PhingFile($this->outputDirectory);
        if (!$outputDir->exists()) {
            $this->log("Output directory does not exist, creating: " . $outputDir->getAbsolutePath());
            $outputDir->mkdirs();
        }

        $this->context->setOutputDirectory($outputDir->getAbsolutePath());

        $path = $this->outputDirectory . DIRECTORY_SEPARATOR . $this->outputFile;
        $this->log("Generating to file " . $path);

        //$writer = new FileWriter($path);

        // The generator and the output path should
        // be placed in the init context here and
        // not in the generator class itself.
        $c = $this->initControlContext();

        // Set any variables that need to always
        // be loaded
        $this->populateInitialContext($c);

        // Feed all the options into the initial
        // control context so they are available
        // in the control/worker templates.
        if ($this->contextProperties !== null) {

            foreach ($this->contextProperties->keys() as $property) {

                $value = $this->contextProperties->getProperty($property);

                // Special exception (from Texen)
                // for properties ending in file.contents:
                // in that case we dump the contents of the file
                // as the "value" for the Property.
                if (preg_match('/file\.contents$/', $property)) {
                    // pull in contents of file specified

                    $property = substr($property, 0, strpos($property, "file.contents") - 1);

                    // reset value, and then
                    // read in the contents of the file into that var
                    $value = "";
                    $f = new PhingFile($this->project->resolveFile($value)->getCanonicalPath());
                    if ($f->exists()) {
                        $fr = new FileReader($f);
                        $fr->readInto($value);
                    }

                } // if ends with file.contents

                if (StringHelper::isBoolean($value)) {
                    $value = StringHelper::booleanValue($value);
                }

                $c->put($property, $value);

            } // foreach property

        } // if contextProperties !== null

        try {
            $this->log("Parsing control template: " . $this->controlTemplate);
            $c->parse($this->controlTemplate, $path);
        } catch (Exception $ioe) {
            throw new BuildException("Cannot write parsed template: " . $ioe->getMessage());
        }

        $this->cleanup();
    }

    /**
     * Place useful objects into the initial context.
     *
     *
     * @param  Capsule   $context The context to populate, as retrieved from
     *                            {@link #initControlContext()}.
     * @return void
     * @throws Exception Error while populating context.  The {@link
     *                           #main()} method will catch and rethrow as a
     *                           <code>BuildException</code>.
     */
    protected function populateInitialContext(Capsule $context)
    {
        $this->context->put("now", strftime("%c", time()));
        $this->context->put("task", $this);
    }

    /**
     * A hook method called at the end of {@link #execute()} which can
     * be overridden to perform any necessary cleanup activities (such
     * as the release of database connections, etc.).  By default,
     * does nothing.
     *
     * @return void
     */
    protected function cleanup()
    {
    }
}

/**
 * An "inner" class for holding assigned var values.
 * May be need to expand beyond name/value in the future.
 *
 * @package phing.tasks.ext
 */
class AssignedVar
{
    private $name;
    private $value;

    /**
     * @param string $v
     */
    public function setName($v)
    {
        $this->name = $v;
    }

    /**
     * @param mixed $v
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
