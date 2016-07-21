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

include_once 'phing/system/io/FileSystem.php';

/**
 * UnixFileSystem class. This class encapsulates the basic file system functions
 * for platforms using the unix (posix)-stylish filesystem. It wraps php native
 * functions suppressing normal PHP error reporting and instead uses Exception
 * to report and error.
 *
 * This class is part of a oop based filesystem abstraction and targeted to run
 * on all supported php platforms.
 *
 * Note: For debugging turn track_errors on in the php.ini. The error messages
 * and log messages from this class will then be clearer because $php_errormsg
 * is passed as part of the message.
 *
 * FIXME:
 *  - Comments
 *  - Error handling reduced to min, error are handled by PhingFile mainly
 *
 * @author    Andreas Aderhold, andi@binarycloud.com
 *
 * @package   phing.system.io
 */
class UnixFileSystem extends FileSystem
{
    /**
     * returns OS dependent path separator char
     *
     * @return string
     */
    public function getSeparator()
    {
        return '/';
    }

    /**
     * returns OS dependent directory separator char
     *
     * @return string
     */
    public function getPathSeparator()
    {
        return ':';
    }

    /**
     * A normal Unix pathname contains no duplicate slashes and does not end
     * with a slash.  It may be the empty string.
     *
     * Check that the given pathname is normal.  If not, invoke the real
     * normalizer on the part of the pathname that requires normalization.
     * This way we iterate through the whole pathname string only once.
     *
     * NOTE: this method no longer expands the tilde (~) character!
     *
     * @param string $strPathname
     *
     * @return string
     */
    public function normalize($strPathname)
    {
        if (!strlen($strPathname)) {
            return;
        }

        // Start normalising after any scheme that is present.
        // This prevents phar:///foo being normalised into phar:/foo
        // Use a regex as some paths may not by parsed by parse_url().
        if (preg_match('{^[a-z][a-z0-9+\-\.]+://}', $strPathname)) {
            $i = strpos($strPathname, '://') + 3;
        } else {
            $i = 0;
        }

        $n = strlen($strPathname);
        $prevChar = 0;
        for (; $i < $n; $i++) {
            $c = $strPathname{$i};
            if (($prevChar === '/') && ($c === '/')) {
                return self::normalizer($strPathname, $n, $i - 1);
            }
            $prevChar = $c;
        }
        if ($prevChar === '/') {
            return self::normalizer($strPathname, $n, $n - 1);
        }

        return $strPathname;
    }

    /**
     * Normalize the given pathname, whose length is $len, starting at the given
     * $offset; everything before this offset is already normal.
     *
     * @param string $pathname
     * @param int $len
     * @param int $offset
     *
     * @return string
     */
    protected function normalizer($pathname, $len, $offset)
    {
        if ($len === 0) {
            return $pathname;
        }
        $n = (int) $len;
        while (($n > 0) && ($pathname{$n - 1} === '/')) {
            $n--;
        }
        if ($n === 0) {
            return '/';
        }
        $sb = "";

        if ($offset > 0) {
            $sb .= substr($pathname, 0, $offset);
        }
        $prevChar = 0;
        for ($i = $offset; $i < $n; $i++) {
            $c = $pathname{$i};
            if (($prevChar === '/') && ($c === '/')) {
                continue;
            }
            $sb .= $c;
            $prevChar = $c;
        }

        return $sb;
    }

    /**
     * Compute the length of the pathname string's prefix.  The pathname
     * string must be in normal form.
     *
     * @param string $pathname
     *
     * @return int
     */
    public function prefixLength($pathname)
    {
        if (strlen($pathname) === 0) {
            return 0;
        }

        if (class_exists('Phar', false) && method_exists('Phar', 'running')) {
            $phar = Phar::running();
            $pharAlias = 'phar://' . Phing::PHAR_ALIAS;

            if ($phar && strpos($pathname, $phar) === 0) {
                return strlen($phar);
            }

            if ($phar && strpos($pathname, $pharAlias) === 0) {
                return strlen($pharAlias);
            }
        }

        return (($pathname{0} === '/') ? 1 : 0);
    }

    /**
     * Resolve the child pathname string against the parent.
     * Both strings must be in normal form, and the result
     * will be in normal form.
     *
     * @param string $parent
     * @param string $child
     *
     * @return string
     */
    public function resolve($parent, $child)
    {

        if ($child === "") {
            return $parent;
        }

        if ($child{0} === '/') {
            if ($parent === '/') {
                return $child;
            }

            return $parent . $child;
        }

        if ($parent === '/') {
            return $parent . $child;
        }

        return $parent . '/' . $child;
    }

    /**
     * @return string
     */
    public function getDefaultParent()
    {
        return '/';
    }

    /**
     * @param PhingFile $f
     *
     * @return bool
     */
    public function isAbsolute(PhingFile $f)
    {
        return ($f->getPrefixLength() !== 0);
    }

    /**
     * the file resolver
     *
     * @param PhingFile $f
     *
     * @return string
     */
    public function resolveFile(PhingFile $f)
    {
        // resolve if parent is a file oject only
        if ($this->isAbsolute($f)) {
            return $f->getPath();
        } else {
            return $this->resolve(Phing::getProperty("user.dir"), $f->getPath());
        }
    }

    /* -- most of the following is mapped to the php natives wrapped by FileSystem */

    /* -- Attribute accessors -- */
    /**
     * @param PhingFile $f
     * @return int
     */
    public function getBooleanAttributes($f)
    {
        //$rv = getBooleanAttributes0($f);
        $name = $f->getName();
        $hidden = (strlen($name) > 0) && ($name{0} == '.');

        return ($hidden ? FileSystem::BA_HIDDEN : 0);
    }

    /**
     * set file readonly on unix
     * @param PhingFile $f
     * @throws Exception
     * @throws IOException
     */
    public function setReadOnly($f)
    {
        if ($f instanceof PhingFile) {
            $strPath = (string) $f->getPath();
            $perms = (int) (@fileperms($strPath) & 0444);

            return FileSystem::getFileSystem()->chmod($strPath, $perms);
        } else {
            throw new Exception("IllegalArgumentType: Argument is not File");
        }
    }

    /**
     * compares file paths lexicographically
     * @param PhingFile $f1
     * @param PhingFile $f2
     * @return int|void
     */
    public function compare(PhingFile $f1, PhingFile $f2)
    {
        $f1Path = $f1->getPath();
        $f2Path = $f2->getPath();

        return strcmp((string) $f1Path, (string) $f2Path);
    }

    /**
     * Copy a file, takes care of symbolic links
     *
     * @param PhingFile $src  Source path and name file to copy.
     * @param PhingFile $dest Destination path and name of new file.
     *
     * @return void
     * @throws Exception if file cannot be copied.
     */
    public function copy(PhingFile $src, PhingFile $dest)
    {
        global $php_errormsg;

        if (!$src->isLink()) {
            return parent::copy($src, $dest);
        }

        $srcPath = $src->getAbsolutePath();
        $destPath = $dest->getAbsolutePath();

        $linkTarget = $src->getLinkTarget();
        if (false === @symlink($linkTarget, $destPath)) {
            $msg = "FileSystem::copy() FAILED. Cannot create symlink from $destPath to $linkTarget.";
            throw new Exception($msg);
        }
    }

    /* -- fs interface --*/

    /**
     * @return array
     */
    public function listRoots()
    {
        if (!$this->checkAccess('/', false)) {
            die ("Can not access root");
        }

        return array(new PhingFile("/"));
    }

    /**
     * returns the contents of a directory in an array
     * @param $f
     * @throws Exception
     * @return array
     */
    public function lister($f)
    {
        $dir = @opendir($f->getAbsolutePath());
        if (!$dir) {
            throw new Exception("Can't open directory " . $f->__toString());
        }
        $vv = array();
        while (($file = @readdir($dir)) !== false) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $vv[] = (string) $file;
        }
        @closedir($dir);

        return $vv;
    }

    /**
     * @param string $p
     * @return string
     */
    public function fromURIPath($p)
    {
        if (StringHelper::endsWith("/", $p) && (strlen($p) > 1)) {

            // "/foo/" --> "/foo", but "/" --> "/"
            $p = substr($p, 0, strlen($p) - 1);

        }

        return $p;
    }

    /**
     * Whether file can be deleted.
     * @param  PhingFile $f
     * @return boolean
     */
    public function canDelete(PhingFile $f)
    {
        @clearstatcache();
        $dir = dirname($f->getAbsolutePath());

        return (bool) @is_writable($dir);
    }

}
