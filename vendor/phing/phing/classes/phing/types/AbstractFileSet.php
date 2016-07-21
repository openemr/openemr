<?php
/*
 *  $Id: 0df7e2e9aeb1d0ee94e2ead72891b5342ae13af8 $
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

include_once 'phing/system/io/PhingFile.php';
include_once 'phing/types/DataType.php';
include_once 'phing/types/PatternSet.php';
include_once 'phing/types/selectors/BaseSelector.php';
include_once 'phing/types/selectors/SelectorContainer.php';
include_once 'phing/types/selectors/BaseSelectorContainer.php';

// Load all of the selectors (not really necessary but
// helps reveal parse errors right away)

include_once 'phing/types/selectors/AndSelector.php';
include_once 'phing/types/selectors/ContainsSelector.php';
include_once 'phing/types/selectors/ContainsRegexpSelector.php';
include_once 'phing/types/selectors/DateSelector.php';
include_once 'phing/types/selectors/DependSelector.php';
include_once 'phing/types/selectors/DepthSelector.php';
include_once 'phing/types/selectors/ExtendSelector.php';
include_once 'phing/types/selectors/FilenameSelector.php';
include_once 'phing/types/selectors/MajoritySelector.php';
include_once 'phing/types/selectors/NoneSelector.php';
include_once 'phing/types/selectors/NotSelector.php';
include_once 'phing/types/selectors/OrSelector.php';
include_once 'phing/types/selectors/PresentSelector.php';
include_once 'phing/types/selectors/SizeSelector.php';
include_once 'phing/types/selectors/TypeSelector.php';
include_once 'phing/types/selectors/ReadableSelector.php';
include_once 'phing/types/selectors/WritableSelector.php';

include_once 'phing/util/DirectoryScanner.php';

/**
 * The FileSet class provides methods and properties for accessing
 * and managing filesets. It extends ProjectComponent and thus inherits
 * all methods and properties (not explicitly declared). See ProjectComponent
 * for further detail.
 *
 * TODO:
 *   - merge this with patternsets: FileSet extends PatternSet !!!
 *     requires additional mods to the parsing algo
 *         [HL] .... not sure if that really makes so much sense.  I think
 *            that perhaps they should use common utility class if there really
 *            is that much shared functionality
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version    $Id: 0df7e2e9aeb1d0ee94e2ead72891b5342ae13af8 $
 * @see        ProjectComponent
 * @package    phing.types
 */
class AbstractFileSet extends DataType implements SelectorContainer
{

    // These vars are public for cloning purposes

    /**
     * @var boolean
     */
    public $useDefaultExcludes = true;

    /**
     * Whether to expand/dereference symbolic links, default is false
     * @var boolean
     */
    protected $expandSymbolicLinks = false;

    /**
     * @var PatternSet
     */
    public $defaultPatterns;

    public $additionalPatterns = array();
    public $dir;
    public $isCaseSensitive = true;
    public $selectors = array();

    /**
     * @param null $fileset
     */
    public function __construct($fileset = null)
    {
        if ($fileset !== null && ($fileset instanceof FileSet)) {
            $this->dir = $fileset->dir;
            $this->defaultPatterns = $fileset->defaultPatterns;
            $this->additionalPatterns = $fileset->additionalPatterns;
            $this->useDefaultExcludes = $fileset->useDefaultExcludes;
            $this->isCaseSensitive = $fileset->isCaseSensitive;
            $this->selectors = $fileset->selectors;
        }
        $this->defaultPatterns = new PatternSet();
    }

    /**
     * Sets whether to expand/dereference symbolic links, default is false
     * @var boolean
     */
    public function setExpandSymbolicLinks($expandSymbolicLinks)
    {
        $this->expandSymbolicLinks = $expandSymbolicLinks;
    }

    /**
     * Makes this instance in effect a reference to another PatternSet
     * instance.
     * You must not set another attribute or nest elements inside
     * this element if you make it a reference.
     * @param Reference $r
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if ((isset($this->dir) && !is_null($this->dir)) || $this->defaultPatterns->hasPatterns()) {
            throw $this->tooManyAttributes();
        }
        if (!empty($this->additionalPatterns)) {
            throw $this->noChildrenAllowed();
        }
        if (!empty($this->selectors)) {
            throw $this->noChildrenAllowed();
        }
        parent::setRefid($r);
    }

    /**
     * @param $dir
     * @throws BuildException
     */
    public function setDir($dir)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($dir instanceof PhingFile) {
            $dir = $dir->getPath();
        }
        $this->dir = new PhingFile((string) $dir);
    }

    /**
     * @param Project $p
     * @return mixed
     * @throws BuildException
     */
    public function getDir(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getDir($p);
        }

        return $this->dir;
    }

    /**
     * @return mixed
     * @throws BuildException
     */
    public function createPatternSet()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $num = array_push($this->additionalPatterns, new PatternSet());

        return $this->additionalPatterns[$num - 1];
    }

    /**
     * add a name entry on the include list
     */
    public function createInclude()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createInclude();
    }

    /**
     * add a name entry on the include files list
     */
    public function createIncludesFile()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createIncludesFile();
    }

    /**
     * add a name entry on the exclude list
     */
    public function createExclude()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createExclude();
    }

    /**
     * add a name entry on the include files list
     */
    public function createExcludesFile()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createExcludesFile();
    }

    public function setFile(PhingFile $file)
    {
        $this->setDir($file->getParentFile());
        $this->createInclude()->setName($file->getName());
    }

    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     * @param $includes
     * @throws BuildException
     */
    public function setIncludes($includes)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->defaultPatterns->setIncludes($includes);
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     * @param $excludes
     * @throws BuildException
     */
    public function setExcludes($excludes)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->defaultPatterns->setExcludes($excludes);
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param PhingFile $incl The file to fetch the include patterns from.
     * @throws BuildException
     */
    public function setIncludesfile(PhingFile $incl)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->defaultPatterns->setIncludesfile($incl);
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param $excl The file to fetch the exclude patterns from.
     * @throws BuildException
     */
    public function setExcludesfile($excl)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->defaultPatterns->setExcludesfile($excl);
    }

    /**
     * Sets whether default exclusions should be used or not.
     *
     * @param $useDefaultExcludes "true"|"on"|"yes" when default exclusions
     *                           should be used, "false"|"off"|"no" when they
     *                           shouldn't be used.
     * @throws BuildException
     * @return void
     */
    public function setDefaultexcludes($useDefaultExcludes)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->useDefaultExcludes = $useDefaultExcludes;
    }

    /**
     * Sets case sensitivity of the file system
     * @param $isCaseSensitive
     */
    public function setCaseSensitive($isCaseSensitive)
    {
        $this->isCaseSensitive = $isCaseSensitive;
    }

    /** returns a reference to the dirscanner object belonging to this fileset
     * @param Project $p
     * @throws BuildException
     * @throws Exception
     * @return \DirectoryScanner
     */
    public function getDirectoryScanner(Project $p)
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            return $o->getDirectoryScanner($p);
        }

        if ($this->dir === null) {
            throw new BuildException("No directory specified for fileset.");
        }
        if (!$this->dir->exists()) {
            throw new BuildException("Directory " . $this->dir->getAbsolutePath() . " not found.");
        }
        if (!$this->dir->isLink() || !$this->expandSymbolicLinks) {
            if (!$this->dir->isDirectory()) {
                throw new BuildException($this->dir->getAbsolutePath() . " is not a directory.");
            }
        }
        $ds = new DirectoryScanner();
        $ds->setExpandSymbolicLinks($this->expandSymbolicLinks);
        $this->setupDirectoryScanner($ds, $p);
        $ds->scan();

        return $ds;
    }

    /** feed dirscanner with infos defined by this fileset
     * @param DirectoryScanner $ds
     * @param Project $p
     * @throws BuildException
     * @throws Exception
     */
    protected function setupDirectoryScanner(DirectoryScanner $ds, Project $p)
    {
        if ($ds === null) {
            throw new Exception("DirectoryScanner cannot be null");
        }
        // FIXME - pass dir directly when dirscanner supports File
        $ds->setBasedir($this->dir->getPath());

        foreach ($this->additionalPatterns as $addPattern) {
            $this->defaultPatterns->append($addPattern, $p);
        }

        $ds->setIncludes($this->defaultPatterns->getIncludePatterns($p));
        $ds->setExcludes($this->defaultPatterns->getExcludePatterns($p));

        $p->log(
            "FileSet: Setup file scanner in dir " . $this->dir->__toString(
            ) . " with " . $this->defaultPatterns->toString(),
            Project::MSG_DEBUG
        );

        if ($ds instanceof SelectorScanner) {
            $ds->setSelectors($this->getSelectors($p));
        }

        if ($this->useDefaultExcludes) {
            $ds->addDefaultExcludes();
        }
        $ds->setCaseSensitive($this->isCaseSensitive);
    }


    /**
     * Performs the check for circular references and returns the
     * referenced FileSet.
     *
     * @param Project $p
     *
     * @throws BuildException
     *
     * @return FileSet
     */
    public function getRef(Project $p)
    {
        if (!$this->checked) {
            $stk = array();
            array_push($stk, $this);
            $this->dieOnCircularReference($stk, $p);
        }

        $o = $this->ref->getReferencedObject($p);
        if (!($o instanceof FileSet)) {
            $msg = $this->ref->getRefId() . " doesn't denote a fileset";
            throw new BuildException($msg);
        } else {
            return $o;
        }
    }

    // SelectorContainer methods

    /**
     * Indicates whether there are any selectors here.
     *
     * @return boolean Whether any selectors are in this container
     */
    public function hasSelectors()
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->hasSelectors();
        }

        return !empty($this->selectors);
    }

    /**
     * Indicates whether there are any patterns here.
     *
     * @return boolean Whether any patterns are in this container.
     */
    public function hasPatterns()
    {

        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->hasPatterns();
        }

        if ($this->defaultPatterns->hasPatterns($this->getProject())) {
            return true;
        }

        for ($i = 0, $size = count($this->additionalPatterns); $i < $size; $i++) {
            $ps = $this->additionalPatterns[$i];
            if ($ps->hasPatterns($this->getProject())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gives the count of the number of selectors in this container
     *
     * @throws Exception
     * @return int The number of selectors in this container
     */
    public function selectorCount()
    {
        if ($this->isReference() && $this->getProject() !== null) {
            try {
                return $this->getRef($this->getProject())->selectorCount();
            } catch (Exception $e) {
                throw $e;
            }
        }

        return count($this->selectors);
    }

    /**
     * Returns the set of selectors as an array.
     *
     * @param Project $p
     * @throws BuildException
     * @return array of selectors in this container
     */
    public function getSelectors(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getSelectors($p);
        } else {
            // *copy* selectors
            $result = array();
            for ($i = 0, $size = count($this->selectors); $i < $size; $i++) {
                $result[] = clone $this->selectors[$i];
            }

            return $result;
        }
    }

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return array The array of selectors
     */
    public function selectorElements()
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->selectorElements();
        }

        return $this->selectors;
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector new selector to add
     *
     * @throws BuildException
     *
     * @return void
     */
    public function appendSelector(FileSelector $selector)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $this->selectors[] = $selector;
    }

    /* Methods below all add specific selectors */

    /**
     * add a "Select" selector entry on the selector list
     *
     * @return SelectSelector
     */
    public function createSelector()
    {
        $o = new SelectSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add an "And" selector entry on the selector list
     *
     * @return AndSelector
     */
    public function createAnd()
    {
        $o = new AndSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add an "Or" selector entry on the selector list
     *
     * @return OrSelector
     */
    public function createOr()
    {
        $o = new OrSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a "Not" selector entry on the selector list
     */
    public function createNot()
    {
        $o = new NotSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a "None" selector entry on the selector list
     */
    public function createNone()
    {
        $o = new NoneSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a majority selector entry on the selector list
     */
    public function createMajority()
    {
        $o = new MajoritySelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a selector date entry on the selector list
     */
    public function createDate()
    {
        $o = new DateSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a selector size entry on the selector list
     */
    public function createSize()
    {
        $o = new SizeSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a selector filename entry on the selector list
     */
    public function createFilename()
    {
        $o = new FilenameSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add an extended selector entry on the selector list
     */
    public function createCustom()
    {
        $o = new ExtendSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a contains selector entry on the selector list
     */
    public function createContains()
    {
        $o = new ContainsSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a contains selector entry on the selector list
     */
    public function createContainsRegexp()
    {
        $o = new ContainsRegexpSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a present selector entry on the selector list
     */
    public function createPresent()
    {
        $o = new PresentSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a depth selector entry on the selector list
     */
    public function createDepth()
    {
        $o = new DepthSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a depends selector entry on the selector list
     */
    public function createDepend()
    {
        $o = new DependSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a type selector entry on the selector list
     */
    public function createType()
    {
        $o = new TypeSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a readable selector entry on the selector list
     */
    public function createReadable()
    {
        $o = new ReadableSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a writable selector entry on the selector list
     */
    public function createWritable()
    {
        $o = new WritableSelector();
        $this->appendSelector($o);

        return $o;
    }
}
