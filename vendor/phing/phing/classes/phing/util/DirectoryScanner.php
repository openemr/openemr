<?php
/*
 *  $Id: cc81e1eb8ef44a36e9fcc01ff1ce3acc8933f3f3 $
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

require_once 'phing/types/selectors/SelectorScanner.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/types/selectors/SelectorUtils.php';

/**
 * Class for scanning a directory for files/directories that match a certain
 * criteria.
 *
 * These criteria consist of a set of include and exclude patterns. With these
 * patterns, you can select which files you want to have included, and which
 * files you want to have excluded.
 *
 * The idea is simple. A given directory is recursively scanned for all files
 * and directories. Each file/directory is matched against a set of include
 * and exclude patterns. Only files/directories that match at least one
 * pattern of the include pattern list, and don't match a pattern of the
 * exclude pattern list will be placed in the list of files/directories found.
 *
 * When no list of include patterns is supplied, "**" will be used, which
 * means that everything will be matched. When no list of exclude patterns is
 * supplied, an empty list is used, such that nothing will be excluded.
 *
 * The pattern matching is done as follows:
 * The name to be matched is split up in path segments. A path segment is the
 * name of a directory or file, which is bounded by DIRECTORY_SEPARATOR
 * ('/' under UNIX, '\' under Windows).
 * E.g. "abc/def/ghi/xyz.php" is split up in the segments "abc", "def", "ghi"
 * and "xyz.php".
 * The same is done for the pattern against which should be matched.
 *
 * Then the segments of the name and the pattern will be matched against each
 * other. When '**' is used for a path segment in the pattern, then it matches
 * zero or more path segments of the name.
 *
 * There are special case regarding the use of DIRECTORY_SEPARATOR at
 * the beginning of the pattern and the string to match:
 * When a pattern starts with a DIRECTORY_SEPARATOR, the string
 * to match must also start with a DIRECTORY_SEPARATOR.
 * When a pattern does not start with a DIRECTORY_SEPARATOR, the
 * string to match may not start with a DIRECTORY_SEPARATOR.
 * When one of these rules is not obeyed, the string will not
 * match.
 *
 * When a name path segment is matched against a pattern path segment, the
 * following special characters can be used:
 *   '*' matches zero or more characters,
 *   '?' matches one character.
 *
 * Examples:
 *
 * "**\*.php" matches all .php files/dirs in a directory tree.
 *
 * "test\a??.php" matches all files/dirs which start with an 'a', then two
 * more characters and then ".php", in a directory called test.
 *
 * "**" matches everything in a directory tree.
 *
 * "**\test\**\XYZ*" matches all files/dirs that start with "XYZ" and where
 * there is a parent directory called test (e.g. "abc\test\def\ghi\XYZ123").
 *
 * Case sensitivity may be turned off if necessary.  By default, it is
 * turned on.
 *
 * Example of usage:
 *   $ds = new DirectroyScanner();
 *   $includes = array("**\*.php");
 *   $excludes = array("modules\*\**");
 *   $ds->SetIncludes($includes);
 *   $ds->SetExcludes($excludes);
 *   $ds->SetBasedir("test");
 *   $ds->SetCaseSensitive(true);
 *   $ds->Scan();
 *
 *   print("FILES:");
 *   $files = ds->GetIncludedFiles();
 *   for ($i = 0; $i < count($files);$i++) {
 *     println("$files[$i]\n");
 *   }
 *
 * This will scan a directory called test for .php files, but excludes all
 * .php files in all directories under a directory called "modules"
 *
 * This class is complete preg/ereg free port of the Java class
 * org.apache.tools.ant.DirectoryScanner. Even functions that use preg/ereg
 * internally (like split()) are not used. Only the _fast_ string functions
 * and comparison operators (=== !=== etc) are used for matching and tokenizing.
 *
 * @author   Arnout J. Kuiper, ajkuiper@wxs.nl
 * @author   Magesh Umasankar, umagesh@rediffmail.com
 * @author   Andreas Aderhold, andi@binarycloud.com
 *
 * @version   $Id: cc81e1eb8ef44a36e9fcc01ff1ce3acc8933f3f3 $
 * @package   phing.util
 */
class DirectoryScanner implements SelectorScanner
{

    /** default set of excludes */
    protected $DEFAULTEXCLUDES = array(
        "**/*~",
        "**/#*#",
        "**/.#*",
        "**/%*%",
        "**/CVS",
        "**/CVS/**",
        "**/.cvsignore",
        "**/SCCS",
        "**/SCCS/**",
        "**/vssver.scc",
        "**/.svn",
        "**/.svn/**",
        "**/._*",
        "**/.DS_Store",
        "**/.darcs",
        "**/.darcs/**",
        "**/.git",
        "**/.git/**",
        "**/.gitattributes",
        "**/.gitignore",
        "**/.gitmodules",
    );

    /** The base directory which should be scanned. */
    protected $basedir;

    /** The patterns for the files that should be included. */
    protected $includes = null;

    /** The patterns for the files that should be excluded. */
    protected $excludes = null;

    /** Whether to expand/dereference symbolic links, default is false */
    protected $expandSymbolicLinks = false;

    /**
     * The files that where found and matched at least one includes, and matched
     * no excludes.
     */
    protected $filesIncluded;

    /** The files that where found and did not match any includes. Trie */
    protected $filesNotIncluded;

    /**
     * The files that where found and matched at least one includes, and also
     * matched at least one excludes. Trie object.
     */
    protected $filesExcluded;

    /**
     * The directories that where found and matched at least one includes, and
     * matched no excludes.
     */
    protected $dirsIncluded;

    /** The directories that where found and did not match any includes. */
    protected $dirsNotIncluded;

    /**
     * The files that where found and matched at least one includes, and also
     * matched at least one excludes.
     */
    protected $dirsExcluded;

    /** Have the vars holding our results been built by a slow scan? */
    protected $haveSlowResults = false;

    /** Should the file system be treated as a case sensitive one? */
    protected $isCaseSensitive = true;

    /** Selectors */
    protected $selectors = null;

    protected $filesDeselected;
    protected $dirsDeselected;

    /** if there are no deselected files */
    protected $everythingIncluded = true;

    /**
     * Does the path match the start of this pattern up to the first "**".
     * This is a static mehtod and should always be called static
     *
     * This is not a general purpose test and should only be used if you
     * can live with false positives.
     *
     * pattern=**\a and str=b will yield true.
     *
     * @param string $pattern
     * @param string $str
     * @param bool $isCaseSensitive
     * @internal param the $pattern pattern to match against
     * @internal param the $str string (path) to match
     * @internal param must $isCaseSensitive matches be case sensitive?
     * @return boolean true if matches, otherwise false
     */
    public function matchPatternStart($pattern, $str, $isCaseSensitive = true)
    {
        return SelectorUtils::matchPatternStart($pattern, $str, $isCaseSensitive);
    }

    /**
     * Matches a path against a pattern.
     *
     * @param string $pattern        the (non-null) pattern to match against
     * @param string $str            the (non-null) string (path) to match
     * @param bool $isCaseSensitive  must a case sensitive match be done?
     *
     * @return bool true when the pattern matches against the string.
     *              false otherwise.
     */
    public function matchPath($pattern, $str, $isCaseSensitive = true)
    {
        return SelectorUtils::matchPath($pattern, $str, $isCaseSensitive);
    }

    /**
     * Matches a string against a pattern. The pattern contains two special
     * characters:
     * '*' which means zero or more characters,
     * '?' which means one and only one character.
     *
     * @param string $pattern pattern to match against
     * @param string $str string that must be matched against the
     *                    pattern
     * @param bool $isCaseSensitive
     *
     * @return boolean true when the string matches against the pattern,
     *                 false otherwise.
     */
    public function match($pattern, $str, $isCaseSensitive = true)
    {
        return SelectorUtils::match($pattern, $str, $isCaseSensitive);
    }

    /**
     * Sets the basedir for scanning. This is the directory that is scanned
     * recursively. All '/' and '\' characters are replaced by
     * DIRECTORY_SEPARATOR
     *
     * @param basedir the (non-null) basedir for scanning
     */
    public function setBasedir($_basedir)
    {
        $_basedir = str_replace('\\', DIRECTORY_SEPARATOR, $_basedir);
        $_basedir = str_replace('/', DIRECTORY_SEPARATOR, $_basedir);
        $this->basedir = $_basedir;
    }

    /**
     * Gets the basedir that is used for scanning. This is the directory that
     * is scanned recursively.
     *
     * @return string the basedir that is used for scanning
     */
    public function getBasedir()
    {
        return $this->basedir;
    }

    /**
     * Sets the case sensitivity of the file system
     *
     * @param bool $_isCaseSensitive specifies if the filesystem is case sensitive
     */
    public function setCaseSensitive($_isCaseSensitive)
    {
        $this->isCaseSensitive = ($_isCaseSensitive) ? true : false;
    }

    /**
     * Sets the set of include patterns to use. All '/' and '\' characters are
     * replaced by DIRECTORY_SEPARATOR. So the separator used need
     * not match DIRECTORY_SEPARATOR.
     *
     * When a pattern ends with a '/' or '\', "**" is appended.
     *
     * @param array $_includes
     * @internal param list $includes of include patterns
     */
    public function setIncludes($_includes = array())
    {
        if (empty($_includes) || is_null($_includes)) {
            $this->includes = null;
        } else {
            for ($i = 0; $i < count($_includes); $i++) {
                $pattern = null;
                $pattern = str_replace('\\', DIRECTORY_SEPARATOR, $_includes[$i]);
                $pattern = str_replace('/', DIRECTORY_SEPARATOR, $pattern);
                if (StringHelper::endsWith(DIRECTORY_SEPARATOR, $pattern)) {
                    $pattern .= "**";
                }
                $this->includes[] = $pattern;
            }
        }
    }

    /**
     * Sets the set of exclude patterns to use. All '/' and '\' characters are
     * replaced by <code>File.separatorChar</code>. So the separator used need
     * not match <code>File.separatorChar</code>.
     *
     * When a pattern ends with a '/' or '\', "**" is appended.
     *
     * @param array $_excludes
     * @internal param list $excludes of exclude patterns
     */

    public function setExcludes($_excludes = array())
    {
        if (empty($_excludes) || is_null($_excludes)) {
            $this->excludes = null;
        } else {
            for ($i = 0; $i < count($_excludes); $i++) {
                $pattern = null;
                $pattern = str_replace('\\', DIRECTORY_SEPARATOR, $_excludes[$i]);
                $pattern = str_replace('/', DIRECTORY_SEPARATOR, $pattern);
                if (StringHelper::endsWith(DIRECTORY_SEPARATOR, $pattern)) {
                    $pattern .= "**";
                }
                $this->excludes[] = $pattern;
            }
        }
    }

    /**
     * Sets whether to expand/dereference symbolic links
     *
     * @param boolean $expandSymbolicLinks
     */
    public function setExpandSymbolicLinks($expandSymbolicLinks)
    {
        $this->expandSymbolicLinks = $expandSymbolicLinks;
    }

    /**
     * Scans the base directory for files that match at least one include
     * pattern, and don't match any exclude patterns.
     *
     */
    public function scan()
    {

        if ((empty($this->basedir)) || (!@is_dir($this->basedir))) {
            return false;
        }

        if ($this->includes === null) {
            // No includes supplied, so set it to 'matches all'
            $this->includes = array("**");
        }
        if (is_null($this->excludes)) {
            $this->excludes = array();
        }

        $this->filesIncluded = array();
        $this->filesNotIncluded = array();
        $this->filesExcluded = array();
        $this->dirsIncluded = array();
        $this->dirsNotIncluded = array();
        $this->dirsExcluded = array();
        $this->dirsDeselected = array();
        $this->filesDeselected = array();

        if ($this->isIncluded("")) {
            if (!$this->isExcluded("")) {
                if ($this->isSelected("", $this->basedir)) {
                    $this->dirsIncluded[] = "";
                } else {
                    $this->dirsDeselected[] = "";
                }
            } else {
                $this->dirsExcluded[] = "";
            }
        } else {
            $this->dirsNotIncluded[] = "";
        }

        $this->scandir($this->basedir, "", true);

        return true;
    }

    /**
     * Toplevel invocation for the scan.
     *
     * Returns immediately if a slow scan has already been requested.
     */
    protected function slowScan()
    {

        if ($this->haveSlowResults) {
            return;
        }

        // copy trie object add CopyInto() method
        $excl = $this->dirsExcluded;
        $notIncl = $this->dirsNotIncluded;

        for ($i = 0, $_i = count($excl); $i < $_i; $i++) {
            if (!$this->couldHoldIncluded($excl[$i])) {
                $this->scandir($this->basedir . $excl[$i], $excl[$i] . DIRECTORY_SEPARATOR, false);
            }
        }

        for ($i = 0, $_i = count($notIncl); $i < $_i; $i++) {
            if (!$this->couldHoldIncluded($notIncl[$i])) {
                $this->scandir($this->basedir . $notIncl[$i], $notIncl[$i] . DIRECTORY_SEPARATOR, false);
            }
        }

        $this->haveSlowResults = true;
    }

    /**
     * Lists contens of a given directory and returns array with entries
     *
     * @param   src String. Source path and name file to copy.
     *
     * @return array directory entries
     * @author  Albert Lash, alash@plateauinnovation.com
     */

    public function listDir($_dir)
    {
        $d = dir($_dir);
        $list = array();
        while (($entry = $d->read()) !== false) {
            if ($entry != "." && $entry != "..") {
                $list[] = $entry;
            }
        }
        $d->close();

        return $list;
    }

    /**
     * Scans the passed dir for files and directories. Found files and
     * directories are placed in their respective collections, based on the
     * matching of includes and excludes. When a directory is found, it is
     * scanned recursively.
     *
     * @param $_rootdir
     * @param $_vpath
     * @param $_fast
     * @internal param the $dir directory to scan
     * @internal param the $vpath path relative to the basedir (needed to prevent
     *              problems with an absolute path when using dir)
     *
     * @see #filesIncluded
     * @see #filesNotIncluded
     * @see #filesExcluded
     * @see #dirsIncluded
     * @see #dirsNotIncluded
     * @see #dirsExcluded
     */
    private function scandir($_rootdir, $_vpath, $_fast)
    {

        if (!is_readable($_rootdir)) {
            return;
        }

        $newfiles = self::listDir($_rootdir);

        for ($i = 0, $_i = count($newfiles); $i < $_i; $i++) {

            $file = $_rootdir . DIRECTORY_SEPARATOR . $newfiles[$i];
            $name = $_vpath . $newfiles[$i];

            if (@is_link($file) && !$this->expandSymbolicLinks) {
                if ($this->isIncluded($name)) {
                    if (!$this->isExcluded($name)) {
                        if ($this->isSelected($name, $file)) {
                            $this->filesIncluded[] = $name;
                        } else {
                            $this->everythingIncluded = false;
                            $this->filesDeselected[] = $name;
                        }
                    } else {
                        $this->everythingIncluded = false;
                        $this->filesExcluded[] = $name;
                    }
                } else {
                    $this->everythingIncluded = false;
                    $this->filesNotIncluded[] = $name;
                }
            } else {
                if (@is_dir($file)) {
                    if ($this->isIncluded($name)) {
                        if (!$this->isExcluded($name)) {
                            if ($this->isSelected($name, $file)) {
                                $this->dirsIncluded[] = $name;
                                if ($_fast) {
                                    $this->scandir($file, $name . DIRECTORY_SEPARATOR, $_fast);
                                }
                            } else {
                                $this->everythingIncluded = false;
                                $this->dirsDeselected[] = $name;
                                if ($_fast && $this->couldHoldIncluded($name)) {
                                    $this->scandir($file, $name . DIRECTORY_SEPARATOR, $_fast);
                                }
                            }
                        } else {
                            $this->everythingIncluded = false;
                            $this->dirsExcluded[] = $name;
                            if ($_fast && $this->couldHoldIncluded($name)) {
                                $this->scandir($file, $name . DIRECTORY_SEPARATOR, $_fast);
                            }
                        }
                    } else {
                        $this->everythingIncluded = false;
                        $this->dirsNotIncluded[] = $name;
                        if ($_fast && $this->couldHoldIncluded($name)) {
                            $this->scandir($file, $name . DIRECTORY_SEPARATOR, $_fast);
                        }
                    }

                    if (!$_fast) {
                        $this->scandir($file, $name . DIRECTORY_SEPARATOR, $_fast);
                    }

                } elseif (@is_file($file)) {
                    if ($this->isIncluded($name)) {
                        if (!$this->isExcluded($name)) {
                            if ($this->isSelected($name, $file)) {
                                $this->filesIncluded[] = $name;
                            } else {
                                $this->everythingIncluded = false;
                                $this->filesDeselected[] = $name;
                            }
                        } else {
                            $this->everythingIncluded = false;
                            $this->filesExcluded[] = $name;
                        }
                    } else {
                        $this->everythingIncluded = false;
                        $this->filesNotIncluded[] = $name;
                    }
                }
            }
        }
    }

    /**
     * Tests whether a name matches against at least one include pattern.
     *
     * @param $_name
     * @internal param the $name name to match
     * @return bool <code>true</code> when the name matches against at least one
     */
    protected function isIncluded($_name)
    {
        for ($i = 0, $_i = count($this->includes); $i < $_i; $i++) {
            if (DirectoryScanner::matchPath($this->includes[$i], $_name, $this->isCaseSensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tests whether a name matches the start of at least one include pattern.
     *
     * @param string $_name the name to match
     * @return bool <code>true</code> when the name matches against at least one
     *                           include pattern, <code>false</code> otherwise.
     */
    protected function couldHoldIncluded($_name)
    {
        for ($i = 0; $i < count($this->includes); $i++) {
            if (DirectoryScanner::matchPatternStart($this->includes[$i], $_name, $this->isCaseSensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tests whether a name matches against at least one exclude pattern.
     *
     * @param string $_name the name to match
     * @return bool <code>true</code> when the name matches against at least one
     *                           exclude pattern, <code>false</code> otherwise.
     */
    protected function isExcluded($_name)
    {
        for ($i = 0; $i < count($this->excludes); $i++) {
            if (DirectoryScanner::matchPath($this->excludes[$i], $_name, $this->isCaseSensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the names of the files that matched at least one of the include
     * patterns, and matched none of the exclude patterns.
     * The names are relative to the basedir.
     *
     * @return the names of the files
     */
    public function getIncludedFiles()
    {
        return $this->filesIncluded;
    }

    /**
     * Get the names of the files that matched at none of the include patterns.
     * The names are relative to the basedir.
     *
     * @return the names of the files
     */
    public function getNotIncludedFiles()
    {
        $this->slowScan();

        return $this->filesNotIncluded;
    }

    /**
     * Get the names of the files that matched at least one of the include
     * patterns, an matched also at least one of the exclude patterns.
     * The names are relative to the basedir.
     *
     * @return the names of the files
     */

    public function getExcludedFiles()
    {
        $this->slowScan();

        return $this->filesExcluded;
    }

    /**
     * <p>Returns the names of the files which were selected out and
     * therefore not ultimately included.</p>
     *
     * <p>The names are relative to the base directory. This involves
     * performing a slow scan if one has not already been completed.</p>
     *
     * @return the names of the files which were deselected.
     *
     * @see #slowScan
     */
    public function getDeselectedFiles()
    {
        $this->slowScan();

        return $this->filesDeselected;
    }

    /**
     * Get the names of the directories that matched at least one of the include
     * patterns, an matched none of the exclude patterns.
     * The names are relative to the basedir.
     *
     * @return the names of the directories
     */

    public function getIncludedDirectories()
    {
        return $this->dirsIncluded;
    }

    /**
     * Get the names of the directories that matched at none of the include
     * patterns.
     * The names are relative to the basedir.
     *
     * @return the names of the directories
     */
    public function getNotIncludedDirectories()
    {
        $this->slowScan();

        return $this->dirsNotIncluded;
    }

    /**
     * <p>Returns the names of the directories which were selected out and
     * therefore not ultimately included.</p>
     *
     * <p>The names are relative to the base directory. This involves
     * performing a slow scan if one has not already been completed.</p>
     *
     * @return the names of the directories which were deselected.
     *
     * @see #slowScan
     */
    public function getDeselectedDirectories()
    {
        $this->slowScan();

        return $this->dirsDeselected;
    }

    /**
     * Get the names of the directories that matched at least one of the include
     * patterns, an matched also at least one of the exclude patterns.
     * The names are relative to the basedir.
     *
     * @return the names of the directories
     */
    public function getExcludedDirectories()
    {
        $this->slowScan();

        return $this->dirsExcluded;
    }

    /**
     * Adds the array with default exclusions to the current exclusions set.
     *
     */
    public function addDefaultExcludes()
    {
        //$excludesLength = ($this->excludes == null) ? 0 : count($this->excludes);
        foreach ($this->DEFAULTEXCLUDES as $pattern) {
            $pattern = str_replace('\\', DIRECTORY_SEPARATOR, $pattern);
            $pattern = str_replace('/', DIRECTORY_SEPARATOR, $pattern);
            $this->excludes[] = $pattern;
        }
    }

    /**
     * Sets the selectors that will select the filelist.
     *
     * @param specifies $selectors
     * @internal param specifies $selectors the selectors to be invoked on a scan
     */
    public function setSelectors($selectors)
    {
        $this->selectors = $selectors;
    }

    /**
     * Returns whether or not the scanner has included all the files or
     * directories it has come across so far.
     *
     * @return bool <code>true</code> if all files and directories which have
     */
    public function isEverythingIncluded()
    {
        return $this->everythingIncluded;
    }

    /**
     * Tests whether a name should be selected.
     *
     * @param  string  $name The filename to check for selecting.
     * @param  string  $file The full file path.
     * @return boolean False when the selectors says that the file
     *                      should not be selected, True otherwise.
     */
    protected function isSelected($name, $file)
    {
        if ($this->selectors !== null) {
            $basedir = new PhingFile($this->basedir);
            $file = new PhingFile($file);
            if (!$file->canRead()) {
                return false;
            }

            foreach ($this->selectors as $selector) {
                if (!$selector->isSelected($basedir, $name, $file)) {
                    return false;
                }
            }
        }

        return true;
    }

}
