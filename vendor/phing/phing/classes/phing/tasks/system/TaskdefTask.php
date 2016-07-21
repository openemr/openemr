<?php

/*
 * $Id: b554ecb4f867f8ccbcd764de62482d6c16800c00 $
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
include_once 'phing/system/io/PhingFile.php';

/**
 * Register a task for use within a buildfile.
 *
 * This is for registering your own tasks -- or any non-core Task -- for use within a buildfile.
 * If you find that you are using a particular class frequently, you may want to edit the
 * phing/tasks/defaults.properties file so that it is included by default. You may also
 * want to submit it (if LGPL or compatible license) to be included in Phing distribution.
 *
 * <pre>
 *   <taskdef name="mytag" classname="path.to.MyHandlingClass"/>
 *   .
 *   .
 *   <mytag param1="val1" param2="val2"/>
 * </pre>
 *
 * TODO:
 *    -- possibly refactor since this is almost the same as TypeDefTask
 *      (right now these are just too simple to really justify creating an abstract class)
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: b554ecb4f867f8ccbcd764de62482d6c16800c00 $
 * @package   phing.tasks.system
 */
class TaskdefTask extends Task
{

    /** Tag name for task that will be used in XML */
    private $name;

    /**
     * Classname of task to register.
     * This can be a dot-path -- relative to a location on PHP include_path.
     * E.g. path.to.MyClass ->  path/to/MyClass.php
     * @var string
     */
    private $classname;

    /**
     * Path to add to PHP include_path to aid in finding specified class.
     * @var Path
     */
    private $classpath;

    /**
     * Refid to already defined classpath
     */
    private $classpathId;

    /**
     * Name of file to load multiple definitions from.
     * @var string
     */
    private $typeFile;

    /**
     * Set the classpath to be used when searching for component being defined
     *
     * @param Path $classpath A Path object containing the classpath.
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * Create the classpath to be used when searching for component being defined
     *
     * @return Path
     */
    public function createClasspath()
    {
        if ($this->classpath === null) {
            $this->classpath = new Path($this->project);
        }

        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     * @param Reference $r
     */
    public function setClasspathRef(Reference $r)
    {
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }

    /**
     * Sets the name that will be used in XML buildfile.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the class name / dotpath to use.
     * @param string $class
     */
    public function setClassname($class)
    {
        $this->classname = $class;
    }

    /**
     * Sets the file of definitionas to use to use.
     * @param string $file
     */
    public function setFile($file)
    {
        $this->typeFile = $file;
    }

    /** Main entry point */
    public function main()
    {
        if ($this->typeFile === null &&
            ($this->name === null || $this->classname === null)
        ) {
            throw new BuildException("You must specify name and class attributes for <taskdef>.");
        }
        if ($this->typeFile == null) {
            $this->log("Task " . $this->name . " will be handled by class " . $this->classname, Project::MSG_VERBOSE);
            $this->project->addTaskDefinition($this->name, $this->classname, $this->classpath);
        } else {
            try { // try to load taskdefs given in file
                $props = new Properties();
                $in = new PhingFile((string) $this->typeFile);

                if ($in === null) {
                    throw new BuildException("Can't load task list {$this->typeFile}");
                }
                $props->load($in);

                $enum = $props->propertyNames();
                foreach ($enum as $key) {
                    $value = $props->getProperty($key);
                    $this->project->addTaskDefinition($key, $value, $this->classpath);
                }
            } catch (IOException $ioe) {
                throw new BuildException("Can't load task list {$this->typeFile}");
            }
        }
    }
}
