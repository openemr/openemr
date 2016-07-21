<?php

/**
 * $Id: 11e97cff8a9f2331750049c1c8fe4fb57f3cc962 $
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

/**
 * Task to run PhpDocumentor.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 11e97cff8a9f2331750049c1c8fe4fb57f3cc962 $
 * @package phing.tasks.ext.phpdoc
 */
class PhpDocumentorTask extends Task
{

    /**
     * @var string Title for browser window / package index.
     */
    protected $title;

    /**
     * @var PhingFile The target directory for output files.
     */
    protected $destdir;

    /**
     * @var array FileSet[] Filesets for files to parse.
     */
    protected $filesets = array();

    /**
     * @var array FileSet[] Project documentation (README/INSTALL/CHANGELOG) files.
     */
    protected $projDocFilesets = array();

    /**
     * @var string Package output format.
     */
    protected $output;

    /**
     * @var boolean Whether to generate sourcecode for each file parsed.
     */
    protected $linksource = false;

    /**
     * @var boolean Whether to parse private members.
     */
    protected $parsePrivate = false;

    /**
     * @var boolean Whether to use javadoc descriptions (more primitive).
     */
    protected $javadocDesc = false;

    /**
     * @var PhingFile Base directory for locating template files.
     */
    protected $templateBase;

    /**
     * @var boolean Wheter to suppress output.
     */
    protected $quiet = false;

    /**
     * @var string Comma-separated list of packages to output.
     */
    protected $packages;

    /**
     * @var string Comma-separated list of tags to ignore.
     */
    protected $ignoreTags;

    /**
     * @var string Default package name.
     */
    protected $defaultPackageName;

    /**
     * @var string Default category name.
     */
    protected $defaultCategoryName;

    /**
     * @var PhingFile Directory in which to look for examples.
     */
    protected $examplesDir;

    /**
     * @var PhingFile Directory in which to look for configuration files.
     */
    protected $configDir;

    /**
     * @var boolean Whether to parse as a PEAR repository.
     */
    protected $pear = false;

    /**
     * @var boolean Control whether or not warnings will be shown for
     *              undocumented elements. Useful for identifying classes and
     *              methods that haven't yet been documented.
     */
    protected $undocumentedelements = false;

    /**
     * @var string  custom tags, will be recognized and put in tags[] instead of
     *              unknowntags[].
     */
    protected $customtags = '';

    /**
     * @var string  files to ignore
     */
    protected $ignore = '';

    /**
     * Set the title for the generated documentation
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set the destination directory for the generated documentation
     * @param PhingFile $destdir
     */
    public function setDestdir(PhingFile $destdir)
    {
        $this->destdir = $destdir;
    }

    /**
     * Alias for {@link setDestdir()}.
     * @see setDestdir()
     * @param PhingFile $destdir
     */
    public function setTarget(PhingFile $destdir)
    {
        $this->setDestdir($destdir);
    }

    /**
     * Set the output format (e.g. HTML:Smarty:PHP).
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Set whether to generate sourcecode for each file parsed
     * @param boolean
     */
    public function setSourcecode($b)
    {
        $this->setLinksource($b);
    }

    /**
     * Set whether to generate sourcecode for each file parsed
     * @param boolean
     */
    public function setLinksource($b)
    {
        $this->linksource = $b;
    }

    /**
     * Set whether to suppress output.
     * @param boolean $b
     */
    public function setQuiet($b)
    {
        $this->quiet = $b;
    }

    /**
     * Should private members/classes be documented
     * @param boolean
     */
    public function setParseprivate($parseprivate)
    {
        $this->parsePrivate = $parseprivate;
    }

    /**
     * Whether to use javadoc descriptions (more primitive).
     * @param boolean
     */
    public function setJavadocdesc($javadoc)
    {
        $this->javadocDesc = $javadoc;
    }

    /**
     * Set (comma-separated) list of packages to output.
     *
     * @param string $packages
     */
    public function setPackageoutput($packages)
    {
        $this->packages = $packages;
    }

    /**
     * Set (comma-separated) list of tags to ignore.
     *
     * @param string $tags
     */
    public function setIgnoretags($tags)
    {
        $this->ignoreTags = $tags;
    }

    /**
     * Set a directory to search for examples in.
     * @param PhingFile $d
     */
    public function setExamplesdir(PhingFile $d)
    {
        $this->examplesDir = $d;
    }

    /**
     * Set a directory to search for configuration files in.
     * @param PhingFile $d
     */
    public function setConfigdir(PhingFile $d)
    {
        $this->configDir = $d;
    }

    /**
     * Sets the default package name.
     * @param string $name
     */
    public function setDefaultpackagename($name)
    {
        $this->defaultPackageName = $name;
    }

    /**
     * Sets the default category name.
     * @param string $name
     */
    public function setDefaultcategoryname($name)
    {
        $this->defaultCategoryName = $name;
    }

    /**
     * Set whether to parse as PEAR repository.
     * @param boolean $b
     */
    public function setPear($b)
    {
        $this->pear = $b;
    }

    /**
     * Creates a FileSet.
     * @return FileSet
     */
    public function createFileset()
    {
        $num = array_push($this->filesets, new FileSet());

        return $this->filesets[$num - 1];
    }

    /**
     * Creates a readme/install/changelog fileset.
     * @return FileSet
     */
    public function createProjdocfileset()
    {
        $num = array_push($this->projDocFilesets, new FileSet());

        return $this->projDocFilesets[$num - 1];
    }

    /**
     * Control whether or not warnings will be shown for undocumented elements.
     * Useful for identifying classes and methods that haven't yet been
     * documented.
     * @param boolean $b
     */
    public function setUndocumentedelements($b)
    {
        $this->undocumentedelements = $b;
    }

    /**
     * custom tags, will be recognized and put in tags[] instead of
     * unknowntags[].
     *
     * @param string $sCustomtags
     */
    public function setCustomtags($sCustomtags)
    {
        $this->customtags = $sCustomtags;
    }

    /**
     * Set base location of all templates for this parse.
     *
     * @param PhingFile $oTemplateBase
     * @internal param PhingFile $destdir
     */
    public function setTemplateBase(PhingFile $oTemplateBase)
    {
        $this->templateBase = $oTemplateBase;
    }

    /**
     * Set files to ignore
     * @param string $sIgnore
     */
    public function setIgnore($sIgnore)
    {
        $this->ignore = $sIgnore;
    }

    /**
     * Searches include_path for PhpDocumentor install and adjusts include_path appropriately.
     * @throws BuildException - if unable to find PhpDocumentor on include_path
     */
    protected function findPhpDocumentorInstall()
    {
        $found = null;
        foreach (Phing::explodeIncludePath() as $path) {
            $testpath = $path . DIRECTORY_SEPARATOR . 'PhpDocumentor';
            if (file_exists($testpath)) {
                $found = $testpath;
                break;
            }
        }
        if (!$found) {
            throw new BuildException("PhpDocumentor task depends on PhpDocumentor being installed and on include_path.", $this->getLocation(
            ));
        }
        // otherwise, adjust the include_path to path to include the PhpDocumentor directory ...
        set_include_path(get_include_path() . PATH_SEPARATOR . $found);
        include_once "phpDocumentor/Setup.inc.php";
        if (!class_exists('phpDocumentor_setup')) {
            throw new BuildException("Error including PhpDocumentor setup class file.");
        }
    }

    /**
     * Main entrypoint of the task
     * Loads the necessary environment for running PhpDoc, then runs PhpDoc
     *
     * @throws BuildException - if the phpdoc classes can't be loaded.
     */
    public function main()
    {
        $this->findPhpDocumentorInstall();
        include_once 'phing/tasks/ext/phpdoc/PhingPhpDocumentorSetup.php';

        $this->validate();
        $configdir = $this->configDir ? $this->configDir->getAbsolutePath() : null;
        $phpdoc = new PhingPhpDocumentorSetup($configdir, $this);
        $this->setPhpDocumentorOptions($phpdoc);
        //$phpdoc->readCommandLineSettings();
        $phpdoc->setupConverters($this->output);
        $phpdoc->createDocs();
    }

    /**
     * Validates that necessary minimum options have been set.
     * @throws BuildException if validation doesn't pass
     */
    protected function validate()
    {
        if (!$this->destdir) {
            throw new BuildException("You must specify a destdir for phpdoc.", $this->getLocation());
        }
        if (!$this->output) {
            throw new BuildException("You must specify an output format for phpdoc (e.g. HTML:frames:default).", $this->getLocation(
            ));
        }
        if (empty($this->filesets)) {
            throw new BuildException("You have not specified any files to include (<fileset>) for phpdoc.", $this->getLocation(
            ));
        }
    }

    /**
     * Sets the options on the passed-in phpdoc setup object.
     * @param PhingPhpDocumentorSetup $phpdoc
     */
    protected function setPhpDocumentorOptions(PhingPhpDocumentorSetup $phpdoc)
    {

        // Title MUST be set first ... (because it re-initializes the internal state of the PhpDocu renderer)
        if ($this->title) {
            $phpdoc->setTitle($this->title);
        }

        if ($this->parsePrivate) {
            $phpdoc->setParsePrivate();
        }

        if ($this->javadocDesc) {
            $phpdoc->setJavadocDesc();
        }

        if ($this->quiet) {
            $phpdoc->setQuietMode();
        }

        if ($this->destdir) {
            $phpdoc->setTargetDir($this->destdir->getAbsolutePath());
        }

        if ($this->packages) {
            $phpdoc->setPackageOutput($this->packages);
        }

        if ($this->templateBase) {
            $phpdoc->setTemplateBase($this->templateBase->getAbsolutePath());
        }

        if ($this->linksource) {
            $phpdoc->setGenerateSourcecode($this->linksource);
        }

        if ($this->examplesDir) {
            $phpdoc->setExamplesDir($this->examplesDir->getAbsolutePath());
        }

        if ($this->ignoreTags) {
            $phpdoc->setIgnoreTags($this->ignoreTags);
        }

        if ($this->defaultPackageName) {
            $phpdoc->setDefaultPackageName($this->defaultPackageName);
        }

        if ($this->defaultCategoryName) {
            $phpdoc->setDefaultCategoryName($this->defaultCategoryName);
        }

        if ($this->pear) {
            $phpdoc->setPear($this->pear);
        }

        if ($this->ignore) {
            $phpdoc->setIgnore($this->ignore);
        }

        // append any files in filesets
        $filesToParse = array();
        foreach ($this->filesets as $fs) {
            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
            foreach ($files as $filename) {
                $f = new PhingFile($fs->getDir($this->project), $filename);
                $filesToParse[] = $f->getAbsolutePath();
            }
        }
        //print_r(implode(",", $filesToParse));
        $phpdoc->setFilesToParse(implode(",", $filesToParse));

        // append any files in filesets
        $ricFiles = array();
        foreach ($this->projDocFilesets as $fs) {
            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
            foreach ($files as $filename) {
                $f = new PhingFile($fs->getDir($this->project), $filename);
                $ricFiles[] = $f->getName();
            }
        }
        $phpdoc->setRicFiles($ricFiles);

        if ($this->undocumentedelements) {
            $phpdoc->setUndocumentedelements($this->undocumentedelements);
        }

        if ($this->customtags) {
            $phpdoc->setCustomtags($this->customtags);
        }
    }
}
