<?php
/*
 *  $Id: 01f02b12b7d7946bcd16fd87c1a638fa3a724421 $
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
include_once 'phing/tasks/ext/pearpackage/Fileset.php';

/**
 *
 * @author   Hans Lellelid <hans@xmpl.org>
 * @package  phing.tasks.ext
 * @version  $Revision$
 */
class BuildPhingPEARPackageTask extends MatchingTask
{
    /** Base directory for reading files. */
    private $dir;

    private $version;
    private $state = 'stable';
    private $notes;

    private $mode = 'source';

    private $filesets = array();

    /** Package file */
    private $packageFile;

    public function init()
    {
        include_once 'PEAR/PackageFileManager2.php';
        if (!class_exists('PEAR_PackageFileManager2')) {
            throw new BuildException("You must have installed PEAR_PackageFileManager2 (PEAR_PackageFileManager >= 1.6.0) in order to create a PEAR package.xml file.");
        }
    }

    private function setOptions($pkg)
    {
        $options['baseinstalldir'] = 'phing';
        $options['packagedirectory'] = $this->dir->getAbsolutePath();

        if (empty($this->filesets)) {
            throw new BuildException("You must use a <fileset> tag to specify the files to include in the package.xml");
        }

        $options['filelistgenerator'] = 'Fileset';

        // Some PHING-specific options needed by our Fileset reader
        $options['phing_project'] = $this->getProject();
        $options['phing_filesets'] = $this->filesets;

        if ($this->packageFile !== null) {
            // create one w/ full path
            $f = new PhingFile($this->packageFile->getAbsolutePath());
            $options['packagefile'] = $f->getName();
            // must end in trailing slash
            $options['outputdirectory'] = $f->getParent() . DIRECTORY_SEPARATOR;
            $this->log("Creating package file: " . $f->getPath(), Project::MSG_INFO);
        } else {
            $this->log("Creating [default] package.xml file in base directory.", Project::MSG_INFO);
        }

        if ($this->mode == "docs") {
            $options['dir_roles'] = array(  'phing_guide' => 'doc',
                                            'api' => 'doc',
                                            'example' => 'doc');
        } else {
            // add install exceptions
            $options['installexceptions'] = array(  'bin/phing.php' => '/',
                                                    'bin/pear-phing' => '/',
                                                    'bin/pear-phing.bat' => '/',
                                                    );

            $options['dir_roles'] = array(  'etc' => 'data');

            $options['exceptions'] = array( 'bin/pear-phing.bat' => 'script',
                                            'bin/pear-phing' => 'script',
                                            'CREDITS.md' => 'doc',
                                            'CHANGELOG.md' => 'doc',
                                            'LICENSE' => 'doc',
                                            'README.md' => 'doc');
        }

        $pkg->setOptions($options);

    }

    /**
     * Main entry point.
     * @return void
     */
    public function main()
    {
        if ($this->dir === null) {
            throw new BuildException("You must specify the \"dir\" attribute for PEAR package task.");
        }

        if ($this->version === null) {
            throw new BuildException("You must specify the \"version\" attribute for PEAR package task.");
        }

        $package = new PEAR_PackageFileManager2();

        $this->setOptions($package);

        // the hard-coded stuff
        if ($this->mode == "docs") {
            $package->setPackage('phingdocs');
            $package->setSummary('PHP5 project build system based on Apache Ant (documentation)');
        } else {
            $package->setPackage('phing');
            $package->setSummary('PHP5 project build system based on Apache Ant');
        }

        $package->setDescription('PHing Is Not GNU make; it\'s a project build system based on Apache Ant.
You can do anything with it that you could do with a traditional build system like GNU make, and its use of
simple XML build files and extensible PHP "task" classes make it an easy-to-use and highly flexible build framework.
Features include file transformations (e.g. token replacement, XSLT transformation, Smarty template transformations,
etc.), file system operations, interactive build support, SQL execution, and much more.');
        $package->setChannel('pear.phing.info');
        $package->setPackageType('php');

        $package->setReleaseVersion($this->version);
        $package->setAPIVersion($this->version);

        $package->setReleaseStability($this->state);
        $package->setAPIStability($this->state);

        $package->setNotes($this->notes);

        $package->setLicense('LGPL', 'http://www.gnu.org/licenses/lgpl.html');

        // Add package maintainers
        $package->addMaintainer('lead', 'mrook', 'Michiel Rook', 'mrook@php.net');

        // (wow ... this is a poor design ...)
        //
        // note that the order of the method calls below is creating
        // sub-"release" sections which have specific rules.  This replaces
        // the platformexceptions system in the older version of PEAR's package.xml
        //
        // Programmatically, I feel the need to re-iterate that this API for PEAR_PackageFileManager
        // seems really wrong.  Sub-sections should be encapsulated in objects instead of having
        // a "flat" API that does not represent the structure being created....

        if ($this->mode != "docs") {
            // creating a sub-section for 'windows'
            $package->addRelease();
            $package->setOSInstallCondition('windows');
            $package->addInstallAs('bin/phing.php', 'phing.php');
            $package->addInstallAs('bin/pear-phing.bat', 'phing.bat');
            $package->addIgnoreToRelease('bin/pear-phing');

            // creating a sub-section for non-windows
            $package->addRelease();
            $package->addInstallAs('bin/phing.php', 'phing.php');
            $package->addInstallAs('bin/pear-phing', 'phing');
            $package->addIgnoreToRelease('bin/pear-phing.bat');
        }


        // "core" dependencies
        $package->setPhpDep('5.2.0');
        $package->setPearinstallerDep('1.8.0');

        // "package" dependencies
        if ($this->mode != "docs") {
            $package->addPackageDepWithChannel( 'optional', 'phingdocs', 'pear.phing.info', $this->version);
            $package->addPackageDepWithChannel( 'optional', 'VersionControl_SVN', 'pear.php.net', '0.4.0');
            $package->addPackageDepWithChannel( 'optional', 'VersionControl_Git', 'pear.php.net', '0.4.3');
            $package->addPackageDepWithChannel( 'optional', 'Xdebug', 'pecl.php.net', '2.0.5');
            $package->addPackageDepWithChannel( 'optional', 'Archive_Tar', 'pear.php.net', '1.3.8');
            $package->addPackageDepWithChannel( 'optional', 'PEAR_PackageFileManager', 'pear.php.net', '1.5.2');
            $package->addPackageDepWithChannel( 'optional', 'Services_Amazon_S3', 'pear.php.net', '0.3.1');
            $package->addPackageDepWithChannel( 'optional', 'HTTP_Request2', 'pear.php.net', '2.1.1');
            $package->addPackageDepWithChannel( 'optional', 'PHP_Depend', 'pear.pdepend.org', '0.10.0');
            $package->addPackageDepWithChannel( 'optional', 'PHP_PMD', 'pear.phpmd.org', '1.1.0');
            $package->addPackageDepWithChannel( 'optional', 'phpDocumentor', 'pear.phpdoc.org', '2.0.0b7');
            $package->addPackageDepWithChannel( 'optional', 'PHP_CodeSniffer', 'pear.php.net', '1.5.0');
            $package->addPackageDepWithChannel( 'optional', 'Net_Growl', 'pear.php.net', '2.6.0');

            // now add the replacements, chdir() to source directory
            // to allow addReplacement() to find the specified files
            $cwd = getcwd();
            chdir($this->dir->getAbsolutePath());

            $package->addReplacement('Phing.php', 'pear-config', '@DATA-DIR@', 'data_dir');
            $package->addReplacement('bin/pear-phing.bat', 'pear-config', '@PHP-BIN@', 'php_bin');
            $package->addReplacement('bin/pear-phing.bat', 'pear-config', '@BIN-DIR@', 'bin_dir');
            $package->addReplacement('bin/pear-phing.bat', 'pear-config', '@PEAR-DIR@', 'php_dir');
            $package->addReplacement('bin/pear-phing', 'pear-config', '@PHP-BIN@', 'php_bin');
            $package->addReplacement('bin/pear-phing', 'pear-config', '@BIN-DIR@', 'bin_dir');
            $package->addReplacement('bin/pear-phing', 'pear-config', '@PEAR-DIR@', 'php_dir');

            chdir($cwd);
        }

        $package->generateContents();

        $e = $package->writePackageFile();

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
     * @return FileSet The created fileset object
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());

        return $this->filesets[$num-1];
    }

    /**
     * Set the version we are building.
     * @param  string $v
     * @return void
     */
    public function setVersion($v)
    {
        $this->version = $v;
    }

    /**
     * Set the state we are building.
     * @param  string $v
     * @return void
     */
    public function setState($v)
    {
        $this->state = $v;
    }

    /**
     * Sets release notes field.
     * @param  string $v
     * @return void
     */
    public function setNotes($v)
    {
        $this->notes = $v;
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
     * Sets the file to use for generated package.xml
     */
    public function setDestFile(PhingFile $f)
    {
        $this->packageFile = $f;
    }

    /**
     * Sets mode property
     * @param  string $v
     * @return void
     */
    public function setMode($v)
    {
        $this->mode = $v;
    }
}
