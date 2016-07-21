<?php
/*
 *  $Id: e35435d78a6eca384d1c6735565077868b265778 $
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

require_once 'phing/tasks/system/MatchingTask.php';
include_once 'phing/types/FileSet.php';

/**
 * A task to create PEAR package.xml file.
 *
 * This class uses the PEAR_PackageFileMaintainer class to perform the work.
 *
 * This class is designed to be very flexible -- i.e. account for changes to the package.xml w/o
 * requiring changes to this class.  We've accomplished this by having generic <option> and <mapping>
 * nested elements.  All options are set using PEAR_PackageFileMaintainer::setOptions().
 *
 * The <option> tag is used to set a simple option value.
 * <code>
 * <option name="option_name" value="option_value"/>
 * or <option name="option_name">option_value</option>
 * </code>
 *
 * The <mapping> tag represents a complex data type.  You can use nested <element> (and nested <element> with
 * <element> tags) to represent the full complexity of the structure.  Bear in mind that what you are creating
 * will be mapped to an associative array that will be passed in via PEAR_PackageFileMaintainer::setOptions().
 * <code>
 * <mapping name="option_name">
 *  <element key="key_name" value="key_val"/>
 *  <element key="key_name" value="key_val"/>
 * </mapping>
 * </code>
 *
 * Here's an over-simple example of how this could be used:
 * <code>
 * <pearpkg name="phing" dir="${build.src.dir}" destFile="${build.base.dir}/package.xml">
 *  <fileset>
 *   <include name="**"/>
 *  </fileset>
 *  <option name="notes">Sample release notes here.</option>
 *  <option name="description">Package description</option>
 *  <option name="summary">Short description</option>
 *  <option name="version" value="2.0.0b1"/>
 *  <option name="state" value="beta"/>
 *  <mapping name="maintainers">
 *   <element>
 *    <element key="handle" value="hlellelid"/>
 *    <element key="name" value="Hans"/>
 *    <element key="email" value="hans@xmpl.org"/>
 *    <element key="role" value="lead"/>
 *   </element>
 *  </mapping>
 * </pearpkg>
 * </code>
 *
 * Look at the build.xml in the Phing base directory (assuming you have the full distro / CVS version of Phing) to
 * see a more complete example of how to call this script.
 *
 * @author   Hans Lellelid <hans@xmpl.org>
 * @package  phing.tasks.ext
 * @version  $Id: e35435d78a6eca384d1c6735565077868b265778 $
 */
class PearPackageTask extends MatchingTask
{

    /** */
    protected $package;

    /** Base directory for reading files. */
    protected $dir;

    /** Package file */
    private $packageFile;

    /** @var array FileSet[] */
    private $filesets = array();

    /** @var PEAR_PackageFileManager */
    protected $pkg;

    private $preparedOptions = array();

    /** @var array PearPkgOption[] */
    protected $options = array();

    /** Nested <mapping> (complex options) types. */
    protected $mappings = array();

    /**
     * Nested <role> elements
     * @var PearPkgRole[]
     */
    protected $roles = array();

    public function init()
    {
        include_once 'PEAR/PackageFileManager.php';
        if (!class_exists('PEAR_PackageFileManager')) {
            throw new BuildException("You must have installed PEAR_PackageFileManager in order to create a PEAR package.xml file.");
        }
    }

    /**
     * Sets PEAR package.xml options, based on class properties.
     * @throws BuildException
     * @return void
     */
    protected function setOptions()
    {

        // 1) first prepare/populate options
        $this->populateOptions();

        // 2) make any final adjustments (this could move into populateOptions() also)

        // default PEAR basedir would be the name of the package (e.g."phing")
        if (!isset($this->preparedOptions['baseinstalldir'])) {
            $this->preparedOptions['baseinstalldir'] = $this->package;
        }

        // unless filelistgenerator has been overridden, we use Phing FileSet generator
        if (!isset($this->preparedOptions['filelistgenerator'])) {
            if (empty($this->filesets)) {
                throw new BuildException("You must use a <fileset> tag to specify the files to include in the package.xml");
            }
            $this->preparedOptions['filelistgenerator'] = 'Fileset';
            $this->preparedOptions['usergeneratordir'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pearpackage';
            // Some PHING-specific options needed by our Fileset reader
            $this->preparedOptions['phing_project'] = $this->project;
            $this->preparedOptions['phing_filesets'] = $this->filesets;
        } elseif ($this->preparedOptions['filelistgenerator'] != 'Fileset' && !empty($this->filesets)) {
            throw new BuildException("You cannot use <fileset> element if you have specified the \"filelistgenerator\" option.");
        }

        // 3) Set the options

        // No need for excessive validation here, since the  PEAR class will do its own
        // validation & return errors
        $e = $this->pkg->setOptions($this->preparedOptions);

        if (PEAR::isError($e)) {
            throw new BuildException("Unable to set options.", new Exception($e->getMessage()));
        }

        // convert roles
        foreach ($this->roles as $role) {
            $this->pkg->addRole($role->getExtension(), $role->getRole());
        }
    }

    /**
     * Fixes the boolean in optional dependencies
     * @param $deps
     * @return
     */
    private function fixDeps($deps)
    {
        foreach (array_keys($deps) as $dep) {
            if (isset($deps[$dep]['optional']) && $deps[$dep]['optional']) {
                $deps[$dep]['optional'] = "yes";
            }
        }

        return $deps;
    }

    /**
     * Adds the options that are set via attributes and the nested tags to the options array.
     */
    private function populateOptions()
    {

        // These values could be overridden if explicitly defined using nested tags
        $this->preparedOptions['package'] = $this->package;
        $this->preparedOptions['packagedirectory'] = $this->dir->getAbsolutePath();

        if ($this->packageFile !== null) {
            // create one w/ full path
            $f = new PhingFile($this->packageFile->getAbsolutePath());
            $this->preparedOptions['packagefile'] = $f->getName();
            // must end in trailing slash
            $this->preparedOptions['outputdirectory'] = $f->getParent() . DIRECTORY_SEPARATOR;
            $this->log("Creating package file: " . $f->__toString(), Project::MSG_INFO);
        } else {
            $this->log("Creating [default] package.xml file in base directory.", Project::MSG_INFO);
        }

        // converts option objects and mapping objects into
        // key => value options that can be passed to PEAR_PackageFileManager

        foreach ($this->options as $opt) {
            $this->preparedOptions[$opt->getName()] = $opt->getValue(
            ); //no arrays yet. preg_split('/\s*,\s*/', $opt->getValue());
        }

        foreach ($this->mappings as $map) {
            $value = $map->getValue(); // getValue returns complex value

            if ($map->getName() == 'deps') {
                $value = $this->fixDeps($value);
            }

            $this->preparedOptions[$map->getName()] = $value;
        }
    }

    /**
     * Main entry point.
     * @throws BuildException
     * @return void
     */
    public function main()
    {

        if ($this->dir === null) {
            throw new BuildException("You must specify the \"dir\" attribute for PEAR package task.");
        }

        if ($this->package === null) {
            throw new BuildException("You must specify the \"name\" attribute for PEAR package task.");
        }

        $this->pkg = new PEAR_PackageFileManager();

        $this->setOptions();

        $e = $this->pkg->writePackageFile();
        if (PEAR::isError($e)) {
            throw new BuildException("Unable to write package file.", new Exception($e->getMessage()));
        }

    }

    /**
     * Used by the PEAR_PackageFileManager_PhingFileSet lister.
     * @return array FileSet[]
     */
    public function getFileSets()
    {
        return $this->filesets;
    }

    // -------------------------------
    // Set properties from XML
    // -------------------------------

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @param FileSet $fs
     * @internal param FileSet $fileset Set of files to add to the package
     *
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Set "package" property from XML.
     * @see setName()
     * @param  string $v
     * @return void
     */
    public function setPackage($v)
    {
        $this->package = $v;
    }

    /**
     * Sets "dir" property from XML.
     * @param  PhingFile $f
     * @return void
     */
    public function setDir(PhingFile $f)
    {
        $this->dir = $f;
    }

    /**
     * Sets "name" property from XML.
     * @param  string $v
     * @return void
     */
    public function setName($v)
    {
        $this->package = $v;
    }

    /**
     * Sets the file to use for generated package.xml
     * @param PhingFile $f
     */
    public function setDestFile(PhingFile $f)
    {
        $this->packageFile = $f;
    }

    /**
     * Handles nested generic <option> elements.
     */
    public function createOption()
    {
        $o = new PearPkgOption();
        $this->options[] = $o;

        return $o;
    }

    /**
     * Handles nested generic <option> elements.
     */
    public function createMapping()
    {
        $o = new PearPkgMapping();
        $this->mappings[] = $o;

        return $o;
    }

    /**
     * Handles nested <role> elements
     * @return PearPkgRole
     */
    public function createRole()
    {
        $role = new PearPkgRole();
        $this->roles[] = $role;

        return $role;
    }
}

/**
 * Generic option class is used for non-complex options.
 *
 * @package  phing.tasks.ext
 */
class PearPkgOption
{

    private $name;
    private $value;

    /**
     * @param $v
     */
    public function setName($v)
    {
        $this->name = $v;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $v
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $txt
     */
    public function addText($txt)
    {
        $this->value = trim($txt);
    }

}

/**
 * Handles complex options <mapping> elements which are hashes (assoc arrays).
 *
 * @package  phing.tasks.ext
 */
class PearPkgMapping
{

    private $name;
    private $elements = array();

    /**
     * @param $v
     */
    public function setName($v)
    {
        $this->name = $v;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PearPkgMappingElement
     */
    public function createElement()
    {
        $e = new PearPkgMappingElement();
        $this->elements[] = $e;

        return $e;
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns the PHP hash or array of hashes (etc.) that this mapping represents.
     * @return array
     */
    public function getValue()
    {
        $value = array();
        foreach ($this->getElements() as $el) {
            if ($el->getKey() !== null) {
                $value[$el->getKey()] = $el->getValue();
            } else {
                $value[] = $el->getValue();
            }
        }

        return $value;
    }
}

/**
 * Sub-element of <mapping>.
 *
 * @package  phing.tasks.ext
 */
class PearPkgMappingElement
{

    private $key;
    private $value;
    private $elements = array();

    /**
     * @param $v
     */
    public function setKey($v)
    {
        $this->key = $v;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $v
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * Returns either the simple value or
     * the calculated value (array) of nested elements.
     * @return mixed
     */
    public function getValue()
    {
        if (!empty($this->elements)) {
            $value = array();
            foreach ($this->elements as $el) {
                if ($el->getKey() !== null) {
                    $value[$el->getKey()] = $el->getValue();
                } else {
                    $value[] = $el->getValue();
                }
            }

            return $value;
        } else {
            return $this->value;
        }
    }

    /**
     * Handles nested <element> tags.
     */
    public function createElement()
    {
        $e = new PearPkgMappingElement();
        $this->elements[] = $e;

        return $e;
    }

}

/**
 * Encapsulates file roles
 *
 * @package phing.tasks.ext
 */
class PearPkgRole
{
    /**
     * @var string
     */
    private $extension;

    /**
     * @var string
     */
    private $role;

    /**
     * Sets the file extension
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Retrieves the file extension
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Sets the role
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Retrieves the role
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}
