<?php

/**
 * Capsule is a simple "template" engine that essentially provides an isolated context
 * for PHP scripts.
 *
 * There is no special templating language, and therefore no limitations to what
 * can be accomplished within templates. The main purpose of Capsule is to separate
 * the business logic from display / output logic.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @package phing.lib
 */
class Capsule
{
    /**
     * Look for templates here (if relative path provided).
     * @var string
     */
    protected $templatePath;

    /**
     * Where should output files be written?
     * (This is named inconsistently to be compatible w/ Texen.)
     * @var string
     */
    protected $outputDirectory;

    /**
     * The variables that can be used by the templates.
     * @var array Hash of variables.
     */
    public $vars = array();

    /**
     * Has template been initialized.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Stores the pre-parse() include_path.
     * @var string
     */
    private $old_include_path;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Clears one or several or all variables.
     * @param  mixed $which String name of var, or array of names.
     * @return void
     */
    public function clear($which = null)
    {
        if ($which === null) {
            $this->vars = array();
        } elseif (is_array($which)) {
            foreach ($which as $var) {
                unset($this->vars[$var]);
            }
        } else {
            unset($this->vars[$which]);
        }
    }

    /**
     * Set the basepath to use for template lookups.
     * @param string $v
     */
    public function setTemplatePath($v)
    {
        $this->templatePath = rtrim($v, DIRECTORY_SEPARATOR . '/');
    }

    /**
     * Get the basepath to use for template lookups.
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Set a basepath to use for output file creation.
     * @param string $v
     */
    public function setOutputDirectory($v)
    {
        $this->outputDirectory = rtrim($v, DIRECTORY_SEPARATOR . '/');
    }

    /**
     * Get basepath to use for output file creation.
     * @return string
     */
    public function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * Low overhead (no output buffering) method to simply dump template
     * to buffer.
     *
     * @param  string    $__template
     * @return void
     * @throws Exception - if template cannot be found
     */
    public function display($__template)
    {

        // Prepend "private" variable names with $__ in this function
        // to keep namespace conflict potential to a minimum.

        // Alias this class to $generator.
        $generator = $this;

        if (isset($this->vars['this'])) {
            throw new Exception("Assigning a variable named \$this to a context conflicts with class namespace.");
        }

        // extract variables into local namespace
        extract($this->vars);

        // prepend template path to include path,
        // so that include "path/relative/to/templates"; can be used within templates
        $__old_inc_path = ini_get('include_path');
        ini_set('include_path', $this->templatePath . PATH_SEPARATOR . $__old_inc_path);

        @ini_set('track_errors', true);
        include $__template;
        @ini_restore('track_errors');

        // restore the include path
        ini_set('include_path', $__old_inc_path);

        if (!empty($php_errormsg)) {
            throw new Exception("Unable to parse template " . $__template . ": " . $php_errormsg);
        }
    }

    /**
     * Fetches the results of a template parse and either returns
     * the string or writes results to a specified output file.
     *
     * @param  string    $template   The template filename (relative to templatePath or absolute).
     * @param  string    $outputFile If specified, contents of template will also be written to this file.
     * @param  boolean   $append     Should output be appended to source file?
     * @return string    The "parsed" template output.
     * @throws Exception - if template not found.
     */
    public function parse($template, $outputFile = null, $append = false)
    {

        // main work done right here:
        // hopefully this works recursively ... fingers crossed.
        ob_start();

        try {
            $this->display($template);
        } catch (Exception $e) {
            ob_end_flush(); // flush the output on error (so we can see up to what point it parsed everything)
            throw $e;
        }

        $output = ob_get_contents();
        ob_end_clean();

        if ($outputFile !== null) {
            $outputFile = $this->resolvePath($outputFile, $this->outputDirectory);

            $flags = null;
            if ($append) {
                $flags = FILE_APPEND;
            }

            if (!file_put_contents($outputFile, $output, $flags) && $output != "") {
                throw new Exception("Unable to write output to " . $outputFile);
            }
        }

        return $output;
    }

    /**
     * This returns a "best guess" path for the given file.
     *
     * @param  string $file     File name or possibly absolute path.
     * @param  string $basepath The basepath that should be prepended if $file is not absolute.
     * @return string "Best guess" path for this file.
     */
    protected function resolvePath($file, $basepath)
    {
        if (!($file{0} == DIRECTORY_SEPARATOR || $file{0} == '/')
            // also account for C:\ style path
            && !($file{1} == ':' && ($file{2} == DIRECTORY_SEPARATOR || $file{2} == '/'))
        ) {
            if ($basepath != null) {
                $file = $basepath . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $file;
    }

    /**
     * Gets value of specified var or NULL if var has not been put().
     * @param  string $name Variable name to retrieve.
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->vars[$name])) {
            return null;
        }

        return $this->vars[$name];
    }

    /**
     * Merges in passed hash to vars array.
     *
     * Given an array like:
     *
     *            array(     'myvar' => 'Hello',
     *                    'myvar2' => 'Hello')
     *
     * Resulting template will have access to $myvar and $myvar2.
     *
     * @param  array   $vars
     * @param  boolean $recursiveMerge Should matching keys be recursively merged?
     * @return void
     */
    public function putAll($vars, $recursiveMerge = false)
    {
        if ($recursiveMerge) {
            $this->vars = array_merge_recursive($this->vars, $vars);
        } else {
            $this->vars = array_merge($this->vars, $vars);
        }
    }

    /**
     * Adds a variable to the context.
     *
     * Resulting template will have access to ${$name$} variable.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function put($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Put a variable into the context, assigning it by reference.
     * This means that if the template modifies the variable, then it
     * will also be modified in the context.
     *
     * @param $name
     * @param &$value
     */
    public function putRef($name, &$value)
    {
        $this->vars[$name] = & $value;
    }

    /**
     * Makes a copy of the value and puts it into the context.
     * This is primarily to force copying (cloning) of objects, rather
     * than the default behavior which is to assign them by reference.
     * @param string $name
     * @param mixed  $value
     */
    public function putCopy($name, $value)
    {
        if (is_object($value)) {
            $value = clone $value;
        }
        $this->vars[$name] = $value;
    }

}
