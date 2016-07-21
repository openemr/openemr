<?php

/*
 *  $Id: 29768b2a378d342338d3432a2781f3a2fe1e28b0 $
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
include_once 'phing/BuildException.php';
include_once 'phing/util/StringHelper.php';

/**
 * A phing task for generating output by using Smarty.
 *
 * This is based on the TexenTask from Apache's Velocity engine.  This class
 * was originally proted in order to provide a template compiling system for
 * Torque.
 *
 * TODO:
 *        - Add Path / useClasspath support?
 *
 * @author    Hans Lellelid <hans@xmpl.org> (SmartyTask)
 * @author    Jason van Zyl <jvanzyl@apache.org> (TexenTask)
 * @author    Robert Burrell Donkin <robertdonkin@mac.com>
 * @version   $Id: 29768b2a378d342338d3432a2781f3a2fe1e28b0 $
 * @package   phing.tasks.ext
 */
class SmartyTask extends Task
{

    /**
     * Smarty template engine.
     * @var Smarty
     */
    protected $context;

    /**
     * Variables that are assigned to the context on parse/compile.
     * @var array
     */
    protected $properties = array();

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

    /**
     * Smarty compiles templates before parsing / replacing tokens in them.
     * By default it will try ./templates_c, but you may wish to override this.
     * @var string
     */
    protected $compilePath;

    /**
     * Whether to force Smarty to recompile templates.
     * Smarty does check file modification time, but you can set this
     * to be *sure* that the template will be compiled (of course it will
     * be slower if you do).
     * @var boolean
     */
    protected $forceCompile = false;

    /**
     * Smarty can use config files.
     * This tells Smarty where to look for the config files.
     * @var string
     */
    protected $configPath;

    /**
     * Customize the left delimiter for Smarty tags.
     * @var string
     */
    protected $leftDelimiter;

    /**
     * Customize the right delimiter for Smarty tags.
     * @var string
     */
    protected $rightDelimiter;

    // -----------------------------------------------------------------------
    // The following getters & setters are used by phing to set properties
    // specified in the XML for the smarty task.
    // -----------------------------------------------------------------------

    public function init()
    {
        // This check returns true for smarty 3 and false otherwise.
        if (stream_resolve_include_path('SmartyBC.class.php')) {
            include_once 'SmartyBC.class.php';
        } else {
            include_once 'Smarty.class.php';
        }

        if (!class_exists('Smarty')) {
            throw new BuildException("To use SmartyTask, you must have the path to Smarty.class.php on your include_path or your \$PHP_CLASSPATH environment variable.");
        }
    }

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
            throw new BuildException($ioe->getMessage());
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
     * @param $outputFile
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
     * Set the path Smarty uses as a "cache" for compiled templates.
     * @param string $compilePath
     */
    public function setCompilePath($compilePath)
    {
        $this->compilePath = $compilePath;
    }

    /**
     * Get the path Smarty uses for compiling templates.
     * @return string
     */
    public function getCompilePath()
    {
        return $this->compilePath;
    }

    /**
     * Set whether Smarty should always recompile templates.
     * @param  boolean $force
     * @return void
     */
    public function setForceCompile($force)
    {
        $this->forceCompile = (boolean) $force;
    }

    /**
     * Get whether Smarty should always recompile template.
     * @return boolean
     */
    public function getForceCompile()
    {
        return $this->forceCompile;
    }

    /**
     * Set where Smarty looks for config files.
     * @param  string $configPath
     * @return void
     */
    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * Get the path that Smarty uses for looking for config files.
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * Set Smarty template left delimiter.
     * @param  string $delim
     * @return void
     */
    public function setLeftDelimiter($delim)
    {
        $this->leftDelimiter = $delim;
    }

    /**
     * Get Smarty template right delimiter
     * @return string
     */
    public function getLeftDelimiter()
    {
        return $this->leftDelimiter;
    }

    /**
     * Set Smarty template right delimiter.
     * @param  string $delim
     * @return void
     */
    public function setRightDelimiter($delim)
    {
        $this->rightDelimiter = $delim;
    }

    /**
     * Get Smarty template right delimiter
     * @return string
     */
    public function getRightDelimiter()
    {
        return $this->rightDelimiter;
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
                $this->log("Using contextProperties file: " . $fullPath->__toString());
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

    // ---------------------------------------------------------------
    // End of XML setters & getters
    // ---------------------------------------------------------------

    /**
     * Creates a Smarty object.
     *
     * @return Smarty    initialized (cleared) Smarty context.
     * @throws Exception the execute method will catch
     *                   and rethrow as a <code>BuildException</code>
     */
    public function initControlContext()
    {
        $this->context->clear_all_assign();

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
        //
        // Use the smarty backwards compatibility layer if existent.
        if (class_exists('SmartyBC')) {
            $this->context = new SmartyBC();
        } else {
            $this->context = new Smarty();
        }

        if ($this->compilePath !== null) {
            $this->log("Using compilePath: " . $this->compilePath);
            $this->context->compile_dir = $this->compilePath;
        }

        if ($this->configPath !== null) {
            $this->log("Using configPath: " . $this->configPath);
            $this->context->config_dir = $this->configPath;
        }

        if ($this->forceCompile !== null) {
            $this->context->force_compile = $this->forceCompile;
        }

        if ($this->leftDelimiter !== null) {
            $this->context->left_delimiter = $this->leftDelimiter;
        }

        if ($this->rightDelimiter !== null) {
            $this->context->right_delimiter = $this->rightDelimiter;
        }

        if ($this->templatePath !== null) {
            $this->log("Using templatePath: " . $this->templatePath);
            $this->context->template_dir = $this->templatePath;
        }

        $smartyCompilePath = new PhingFile($this->context->compile_dir);
        if (!$smartyCompilePath->exists()) {
            $this->log(
                "Compile directory does not exist, creating: " . $smartyCompilePath->getPath(),
                Project::MSG_VERBOSE
            );
            if (!$smartyCompilePath->mkdirs()) {
                throw new BuildException("Smarty needs a place to compile templates; specify a 'compilePath' or create " . $this->context->compile_dir);
            }
        }

        // Make sure the output directory exists, if it doesn't
        // then create it.
        $file = new PhingFile($this->outputDirectory);
        if (!$file->exists()) {
            $this->log("Output directory does not exist, creating: " . $file->getAbsolutePath());
            $file->mkdirs();
        }

        $path = $this->outputDirectory . DIRECTORY_SEPARATOR . $this->outputFile;
        $this->log("Generating to file " . $path);

        $writer = new FileWriter($path);

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
                if (StringHelper::endsWith("file.contents", $property)) {
                    // pull in contents of file specified

                    $property = substr($property, 0, strpos($property, "file.contents") - 1);

                    // reset value, and then
                    // read in the contents of the file into that var
                    $value = "";
                    $f = new PhingFile($this->project->resolveFile($value)->getCanonicalPath());
                    if ($f->exists()) {
                        try {
                            $fr = new FileReader($f);
                            $fr->readInto($value);
                        } catch (Exception $e) {
                            throw $e;
                        }
                    }

                } // if ends with file.contents

                if (StringHelper::isBoolean($value)) {
                    $value = StringHelper::booleanValue($value);
                }

                $c->assign($property, $value);

            } // foreach property

        } // if contextProperties !== null

        try {
            //$c->display($this->controlTemplate);
            $writer->write($c->fetch($this->controlTemplate));
            $writer->close();
        } catch (IOException $ioe) {
            $writer->close();
            throw new BuildException("Cannot write parsed template.");
        }

        $this->cleanup();
    }

    /**
     * <p>Place useful objects into the initial context.</p>
     *
     * <p>TexenTask places <code>Date().toString()</code> into the
     * context as <code>$now</code>.  Subclasses who want to vary the
     * objects in the context should override this method.</p>
     *
     * <p><code>$generator</code> is not put into the context in this
     * method.</p>
     *
     * @param Smarty|The $context
     * @internal param The $context context to populate, as retrieved from
     * {@link #initControlContext()}.
     * @return void
     */
    protected function populateInitialContext(Smarty $context)
    {
    }

    /**
     * A hook method called at the end of {@link #execute()} which can
     * be overridden to perform any necessary cleanup activities (such
     * as the release of database connections, etc.).  By default,
     * does nothing.
     * @return void
     * @throws Exception Problem cleaning up.
     */
    protected function cleanup()
    {
    }
}
