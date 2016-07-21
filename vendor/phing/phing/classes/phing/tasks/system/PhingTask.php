<?php

/*
 *  $Id: 43be7ce86f93e18cd2030bb4c764b3e385feebe6 $
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

include_once 'phing/Task.php';
include_once 'phing/util/FileUtils.php';
include_once 'phing/types/Reference.php';
include_once 'phing/tasks/system/PropertyTask.php';

/**
 * Task that invokes phing on another build file.
 *
 * Use this task, for example, if you have nested buildfiles in your project. Unlike
 * AntTask, PhingTask can even support filesets:
 *
 * <pre>
 *   <phing>
 *    <fileset dir="${srcdir}">
 *      <include name="** /build.xml" /> <!-- space added after ** is there because of PHP comment syntax -->
 *      <exclude name="build.xml" />
 *    </fileset>
 *   </phing>
 * </pre>
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: 43be7ce86f93e18cd2030bb4c764b3e385feebe6 $
 * @package   phing.tasks.system
 */
class PhingTask extends Task
{

    /** the basedir where is executed the build file */
    private $dir;

    /** build.xml (can be absolute) in this case dir will be ignored */
    private $phingFile;

    /** the target to call if any */
    protected $newTarget;

    /** should we inherit properties from the parent ? */
    private $inheritAll = true;

    /** should we inherit references from the parent ? */
    private $inheritRefs = false;

    /** the properties to pass to the new project */
    private $properties = array();

    /** the references to pass to the new project */
    private $references = array();

    /** The filesets that contain the files PhingTask is to be run on. */
    private $filesets = array();

    /** the temporary project created to run the build file */
    private $newProject;

    /** Fail the build process when the called build fails? */
    private $haltOnFailure = false;

    /**
     *  If true, abort the build process if there is a problem with or in the target build file.
     *  Defaults to false.
     *
     * @param boolean new value
     */
    public function setHaltOnFailure($hof)
    {
        $this->haltOnFailure = (boolean) $hof;
    }

    /**
     * Creates a Project instance for the project to call.
     * @return void
     */
    public function init()
    {
        $this->newProject = new Project();
        $tdf = $this->project->getTaskDefinitions();
        $this->newProject->addTaskDefinition("property", $tdf["property"]);
    }

    /**
     * Called in execute or createProperty if newProject is null.
     *
     * <p>This can happen if the same instance of this task is run
     * twice as newProject is set to null at the end of execute (to
     * save memory and help the GC).</p>
     *
     * <p>Sets all properties that have been defined as nested
     * property elements.</p>
     */
    private function reinit()
    {
        $this->init();
        $count = count($this->properties);
        for ($i = 0; $i < $count; $i++) {
            $p = $this->properties[$i];
            $newP = $this->newProject->createTask("property");
            $newP->setName($p->getName());
            if ($p->getValue() !== null) {
                $newP->setValue($p->getValue());
            }
            if ($p->getFile() !== null) {
                $newP->setFile($p->getFile());
            }
            if ($p->getPrefix() !== null) {
                $newP->setPrefix($p->getPrefix());
            }
            if ($p->getRefid() !== null) {
                $newP->setRefid($p->getRefid());
            }
            if ($p->getEnvironment() !== null) {
                $newP->setEnvironment($p->getEnvironment());
            }
            if ($p->getUserProperty() !== null) {
                $newP->setUserProperty($p->getUserProperty());
            }
            if ($p->getOverride() !== null) {
                $newP->setOverride($p->getOverride());
            }
            $this->properties[$i] = $newP;
        }
    }

    /**
     * Main entry point for the task.
     *
     * @return void
     */
    public function main()
    {

        // Call Phing on the file set with the attribute "phingfile"
        if ($this->phingFile !== null or $this->dir !== null) {
            $this->processFile();
        }

        // if no filesets are given stop here; else process filesets
        if (!empty($this->filesets)) {
            // preserve old settings
            $savedDir = $this->dir;
            $savedPhingFile = $this->phingFile;
            $savedTarget = $this->newTarget;

            // set no specific target for files in filesets
            // [HL] I'm commenting this out; I don't know why this should not be supported!
            // $this->newTarget = null;

            foreach ($this->filesets as $fs) {

                $ds = $fs->getDirectoryScanner($this->project);

                $fromDir = $fs->getDir($this->project);
                $srcFiles = $ds->getIncludedFiles();

                foreach ($srcFiles as $fname) {
                    $f = new PhingFile($ds->getbasedir(), $fname);
                    $f = $f->getAbsoluteFile();
                    $this->phingFile = $f->getAbsolutePath();
                    $this->dir = $f->getParentFile();
                    $this->processFile(); // run Phing!
                }
            }

            // side effect free programming ;-)
            $this->dir = $savedDir;
            $this->phingFile = $savedPhingFile;
            $this->newTarget = $savedTarget;

            // [HL] change back to correct dir
            if ($this->dir !== null) {
                chdir($this->dir->getAbsolutePath());
            }
        }

        // Remove any dangling references to help the GC
        foreach ($this->properties as $property) {
            $property->setFallback(null);
        }
    }

    /**
     * Execute phing file.
     *
     * @throws BuildException
     * @return void
     */
    private function processFile()
    {

        $buildFailed = false;
        $savedDir = $this->dir;
        $savedPhingFile = $this->phingFile;
        $savedTarget = $this->newTarget;

        $savedBasedirAbsPath = null; // this is used to save the basedir *if* we change it

        try {

            if ($this->newProject === null) {
                $this->reinit();
            }

            $this->initializeProject();

            if ($this->dir !== null) {

                $dirAbsPath = $this->dir->getAbsolutePath();

                // BE CAREFUL! -- when the basedir is changed for a project,
                // all calls to getAbsolutePath() on a relative-path dir will
                // be made relative to the project's basedir!  This means
                // that subsequent calls to $this->dir->getAbsolutePath() will be WRONG!

                // We need to save the current project's basedir first.
                $savedBasedirAbsPath = $this->getProject()->getBasedir()->getAbsolutePath();

                $this->newProject->setBasedir($this->dir);

                // Now we must reset $this->dir so that it continues to resolve to the same
                // path.
                $this->dir = new PhingFile($dirAbsPath);

                if ($savedDir !== null) { // has been set explicitly
                    $this->newProject->setInheritedProperty("project.basedir", $this->dir->getAbsolutePath());
                }

            } else {

                // Since we're not changing the basedir here (for file resolution),
                // we don't need to worry about any side-effects in this scanrio.
                $this->dir = $this->getProject()->getBasedir();
            }

            $this->overrideProperties();
            if ($this->phingFile === null) {
                $this->phingFile = "build.xml";
            }

            $fu = new FileUtils();
            $file = $fu->resolveFile($this->dir, $this->phingFile);
            $this->phingFile = $file->getAbsolutePath();

            $this->log("Calling Buildfile '" . $this->phingFile . "' with target '" . $this->newTarget . "'", Project::MSG_VERBOSE);

            $this->newProject->setUserProperty("phing.file", $this->phingFile);

            ProjectConfigurator::configureProject($this->newProject, new PhingFile($this->phingFile));

            if ($this->newTarget === null) {
                $this->newTarget = $this->newProject->getDefaultTarget();
            }

            // Are we trying to call the target in which we are defined?
            if ($this->newProject->getBaseDir() == $this->project->getBaseDir() &&
                $this->newProject->getProperty("phing.file") == $this->project->getProperty("phing.file") &&
                $this->getOwningTarget() !== null &&
                $this->newTarget == $this->getOwningTarget()->getName()
            ) {

                throw new BuildException("phing task calling its own parent target");
            }

            $this->addReferences();
            $this->newProject->executeTarget($this->newTarget);

        } catch (Exception $e) {
            $buildFailed = true;
            $this->log($e->getMessage(), Project::MSG_ERR);
            if (Phing::getMsgOutputLevel() <= Project::MSG_DEBUG) {
                $lines = explode("\n", $e->getTraceAsString());
                foreach ($lines as $line) {
                    $this->log($line, Project::MSG_DEBUG);
                }
            }
            // important!!! continue on to perform cleanup tasks.
        }

        // reset environment values to prevent side-effects.

        $this->newProject = null;
        $pkeys = array_keys($this->properties);
        foreach ($pkeys as $k) {
            $this->properties[$k]->setProject(null);
        }

        $this->dir = $savedDir;
        $this->phingFile = $savedPhingFile;
        $this->newTarget = $savedTarget;

        // If the basedir for any project was changed, we need to set that back here.
        if ($savedBasedirAbsPath !== null) {
            chdir($savedBasedirAbsPath);
        }

        if ($this->haltOnFailure && $buildFailed) {
            throw new BuildException("Execution of the target buildfile failed. Aborting.");
        }
    }

    /**
     * Configure the Project, i.e. make intance, attach build listeners
     * (copy from father project), add Task and Datatype definitions,
     * copy properties and references from old project if these options
     * are set via the attributes of the XML tag.
     *
     * Developer note:
     * This function replaces the old methods "init", "_reinit" and
     * "_initializeProject".
     *
     */
    private function initializeProject()
    {

        $this->newProject->setInputHandler($this->project->getInputHandler());

        foreach ($this->project->getBuildListeners() as $listener) {
            $this->newProject->addBuildListener($listener);
        }

        /* Copy things from old project. Datatypes and Tasks are always
         * copied, properties and references only if specified so/not
         * specified otherwise in the XML definition.
         */
        // Add Datatype definitions
        foreach ($this->project->getDataTypeDefinitions() as $typeName => $typeClass) {
            $this->newProject->addDataTypeDefinition($typeName, $typeClass);
        }

        // Add Task definitions
        foreach ($this->project->getTaskDefinitions() as $taskName => $taskClass) {
            if ($taskClass == "propertytask") {
                // we have already added this taskdef in init()
                continue;
            }
            $this->newProject->addTaskDefinition($taskName, $taskClass);
        }

        // set user-defined properties
        $this->project->copyUserProperties($this->newProject);

        if (!$this->inheritAll) {
            // set System built-in properties separately,
            // b/c we won't inherit them.
            $this->newProject->setSystemProperties();

        } else {
            // set all properties from calling project
            $properties = $this->project->getProperties();
            foreach ($properties as $name => $value) {
                if ($name == "basedir" || $name == "phing.file" || $name == "phing.version") {
                    // basedir and phing.file get special treatment in main()
                    continue;
                }
                // don't re-set user properties, avoid the warning message
                if ($this->newProject->getProperty($name) === null) {
                    // no user property
                    $this->newProject->setNewProperty($name, $value);
                }
            }

        }

    }

    /**
     * Override the properties in the new project with the one
     * explicitly defined as nested elements here.
     * @return void
     * @throws BuildException
     */
    private function overrideProperties()
    {
        foreach (array_keys($this->properties) as $i) {
            $p = $this->properties[$i];
            $p->setProject($this->newProject);
            $p->main();
        }
        $this->project->copyInheritedProperties($this->newProject);
    }

    /**
     * Add the references explicitly defined as nested elements to the
     * new project.  Also copy over all references that don't override
     * existing references in the new project if inheritrefs has been
     * requested.
     *
     * @return void
     * @throws BuildException
     */
    private function addReferences()
    {

        // parent project references
        $projReferences = $this->project->getReferences();

        $newReferences = $this->newProject->getReferences();

        $subprojRefKeys = array();

        if (count($this->references) > 0) {
            for ($i = 0, $count = count($this->references); $i < $count; $i++) {
                $ref = $this->references[$i];
                $refid = $ref->getRefId();

                if ($refid === null) {
                    throw new BuildException("the refid attribute is required"
                        . " for reference elements");
                }
                if (!isset($projReferences[$refid])) {
                    $this->log(
                        "Parent project doesn't contain any reference '"
                        . $refid . "'",
                        Project::MSG_WARN
                    );
                    continue;
                }

                $subprojRefKeys[] = $refid;
                //thisReferences.remove(refid);
                $toRefid = $ref->getToRefid();
                if ($toRefid === null) {
                    $toRefid = $refid;
                }
                $this->copyReference($refid, $toRefid);
            }
        }

        // Now add all references that are not defined in the
        // subproject, if inheritRefs is true
        if ($this->inheritRefs) {

            // get the keys that are were not used by the subproject
            $unusedRefKeys = array_diff(array_keys($projReferences), $subprojRefKeys);

            foreach ($unusedRefKeys as $key) {
                if (isset($newReferences[$key])) {
                    continue;
                }
                $this->copyReference($key, $key);
            }
        }
    }

    /**
     * Try to clone and reconfigure the object referenced by oldkey in
     * the parent project and add it to the new project with the key
     * newkey.
     *
     * <p>If we cannot clone it, copy the referenced object itself and
     * keep our fingers crossed.</p>
     *
     * @param  string $oldKey
     * @param  string $newKey
     * @throws BuildException
     * @return void
     */
    private function copyReference($oldKey, $newKey)
    {
        $orig = $this->project->getReference($oldKey);
        if ($orig === null) {
            $this->log(
                "No object referenced by " . $oldKey . ". Can't copy to "
                . $newKey,
                Project::MSG_WARN
            );

            return;
        }

        $copy = clone $orig;

        if ($copy instanceof ProjectComponent) {
            $copy->setProject($this->newProject);
        } elseif (in_array('setProject', get_class_methods(get_class($copy)))) {
            $copy->setProject($this->newProject);
        } elseif ($copy instanceof Project) {
            // don't copy the old "Project" itself
        } else {
            $msg = "Error setting new project instance for "
                . "reference with id " . $oldKey;
            throw new BuildException($msg);
        }

        $this->newProject->addReference($newKey, $copy);
    }

    /**
     * If true, pass all properties to the new phing project.
     * Defaults to true.
     *
     * @param $value
     */
    public function setInheritAll($value)
    {
        $this->inheritAll = (boolean) $value;
    }

    /**
     * If true, pass all references to the new phing project.
     * Defaults to false.
     *
     * @param $value
     */
    public function setInheritRefs($value)
    {
        $this->inheritRefs = (boolean) $value;
    }

    /**
     * The directory to use as a base directory for the new phing project.
     * Defaults to the current project's basedir, unless inheritall
     * has been set to false, in which case it doesn't have a default
     * value. This will override the basedir setting of the called project.
     *
     * @param $d
     */
    public function setDir($d)
    {
        if (is_string($d)) {
            $this->dir = new PhingFile($d);
        } else {
            $this->dir = $d;
        }
    }

    /**
     * The build file to use.
     * Defaults to "build.xml". This file is expected to be a filename relative
     * to the dir attribute given.
     *
     * @param $s
     */
    public function setPhingfile($s)
    {
        // it is a string and not a file to handle relative/absolute
        // otherwise a relative file will be resolved based on the current
        // basedir.
        $this->phingFile = $s;
    }

    /**
     * Alias function for setPhingfile
     *
     * @param $s
     */
    public function setBuildfile($s)
    {
        $this->setPhingFile($s);
    }

    /**
     * The target of the new Phing project to execute.
     * Defaults to the new project's default target.
     *
     * @param $s
     */
    public function setTarget($s)
    {
        $this->newTarget = $s;
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
     * Property to pass to the new project.
     * The property is passed as a 'user property'
     *
     */
    public function createProperty()
    {
        $p = new PropertyTask();
        $p->setFallback($this->newProject);
        $p->setUserProperty(true);
        $this->properties[] = $p;

        return $p;
    }

    /**
     * Reference element identifying a data type to carry
     * over to the new project.
     *
     */
    public function createReference()
    {
        $num = array_push($this->references, new PhingReference());

        return $this->references[$num - 1];
    }

}

/**
 * Helper class that implements the nested <reference>
 * element of <phing> and <phingcall>.
 *
 * @package   phing.tasks.system
 */
class PhingReference extends Reference
{

    private $targetid = null;

    /**
     * Set the id that this reference to be stored under in the
     * new project.
     *
     * @param the $targetid
     * @internal param the $targetid id under which this reference will be passed to
     *        the new project
     */
    public function setToRefid($targetid)
    {
        $this->targetid = $targetid;
    }

    /**
     * Get the id under which this reference will be stored in the new
     * project
     *
     * @return the id of the reference in the new project.
     */
    public function getToRefid()
    {
        return $this->targetid;
    }
}
