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
include_once 'phing/system/lang/NullPointerException.php';

/**
 * An abstract representation of file and directory pathnames.
 *
 * @package   phing.system.io
 */
class PhingFile
{
    /** separator string, static, obtained from FileSystem */
    public static $separator;

    /** path separator string, static, obtained from FileSystem (; or :)*/
    public static $pathSeparator;

    /**
     * This abstract pathname's normalized pathname string.  A normalized
     * pathname string uses the default name-separator character and does not
     * contain any duplicate or redundant separators.
     */
    private $path = null;

    /**
     * The length of this abstract pathname's prefix, or zero if it has no prefix.
     * @var int
     */
    private $prefixLength = 0;

    /**
     * constructor
     *
     * @param mixed $arg1
     * @param mixed $arg2
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function __construct($arg1 = null, $arg2 = null)
    {

        if (self::$separator === null || self::$pathSeparator === null) {
            $fs = FileSystem::getFileSystem();
            self::$separator = $fs->getSeparator();
            self::$pathSeparator = $fs->getPathSeparator();
        }

        /* simulate signature identified constructors */
        if ($arg1 instanceof PhingFile && is_string($arg2)) {
            $this->_constructFileParentStringChild($arg1, $arg2);
        } elseif (is_string($arg1) && ($arg2 === null)) {
            $this->_constructPathname($arg1);
        } elseif (is_string($arg1) && is_string($arg2)) {
            $this->_constructStringParentStringChild($arg1, $arg2);
        } else {
            if ($arg1 === null) {
                throw new NullPointerException("Argument1 to function must not be null");
            }
            $this->path = (string) $arg1;
            $this->prefixLength = (int) $arg2;
        }
    }

    /**
     * Returns the length of this abstract pathname's prefix.
     *
     * @return int
     */
    public function getPrefixLength()
    {
        return (int) $this->prefixLength;
    }

    /* -- constructors not called by signature match, so we need some helpers --*/

    /**
     * @param string $pathname
     *
     * @throws NullPointerException
     */
    protected function _constructPathname($pathname)
    {
        // obtain ref to the filesystem layer
        $fs = FileSystem::getFileSystem();

        if ($pathname === null) {
            throw new NullPointerException("Argument to function must not be null");
        }

        $this->path = (string) $fs->normalize($pathname);
        $this->prefixLength = (int) $fs->prefixLength($this->path);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $parent
     * @param unknown_type $child
     * @throws NullPointerException
     */
    protected function _constructStringParentStringChild($parent, $child = null)
    {
        // obtain ref to the filesystem layer
        $fs = FileSystem::getFileSystem();

        if ($child === null) {
            throw new NullPointerException("Argument to function must not be null");
        }
        if ($parent !== null) {
            if ($parent === "") {
                $this->path = $fs->resolve($fs->getDefaultParent(), $fs->normalize($child));
            } else {
                $this->path = $fs->resolve($fs->normalize($parent), $fs->normalize($child));
            }
        } else {
            $this->path = (string) $fs->normalize($child);
        }
        $this->prefixLength = (int) $fs->prefixLength($this->path);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $parent
     * @param unknown_type $child
     * @throws NullPointerException
     */
    protected function _constructFileParentStringChild($parent, $child = null)
    {
        // obtain ref to the filesystem layer
        $fs = FileSystem::getFileSystem();

        if ($child === null) {
            throw new NullPointerException("Argument to function must not be null");
        }

        if ($parent !== null) {
            if ($parent->path === "") {
                $this->path = $fs->resolve($fs->getDefaultParent(), $fs->normalize($child));
            } else {
                $this->path = $fs->resolve($parent->path, $fs->normalize($child));
            }
        } else {
            $this->path = $fs->normalize($child);
        }
        $this->prefixLength = $fs->prefixLength($this->path);
    }

    /* -- Path-component accessors -- */

    /**
     * Returns the name of the file or directory denoted by this abstract
     * pathname.  This is just the last name in the pathname's name
     * sequence.  If the pathname's name sequence is empty, then the empty
     * string is returned.
     *
     * @return string The name of the file or directory denoted by this abstract
     *                pathname, or the empty string if this pathname's name sequence
     *                is empty
     */
    public function getName()
    {
        // that's a lastIndexOf
        $index = ((($res = strrpos($this->path, self::$separator)) === false) ? -1 : $res);
        if ($index < $this->prefixLength) {
            return substr($this->path, $this->prefixLength);
        }

        return substr($this->path, $index + 1);
    }

    /**
     * Returns the pathname string of this abstract pathname's parent, or
     * null if this pathname does not name a parent directory.
     *
     * The parent of an abstract pathname consists of the pathname's prefix,
     * if any, and each name in the pathname's name sequence except for the last.
     * If the name sequence is empty then the pathname does not name a parent
     * directory.
     *
     * @return string $pathname string of the parent directory named by this
     *                          abstract pathname, or null if this pathname does not name a parent
     */
    public function getParent()
    {
        // that's a lastIndexOf
        $index = ((($res = strrpos($this->path, self::$separator)) === false) ? -1 : $res);
        if ($index < $this->prefixLength) {
            if (($this->prefixLength > 0) && (strlen($this->path) > $this->prefixLength)) {
                return substr($this->path, 0, $this->prefixLength);
            }

            return null;
        }

        return substr($this->path, 0, $index);
    }

    /**
     * Returns the abstract pathname of this abstract pathname's parent,
     * or null if this pathname does not name a parent directory.
     *
     * The parent of an abstract pathname consists of the pathname's prefix,
     * if any, and each name in the pathname's name sequence except for the
     * last.  If the name sequence is empty then the pathname does not name
     * a parent directory.
     *
     * @return The abstract pathname of the parent directory named by this
     *             abstract pathname, or null if this pathname
     *             does not name a parent
     */
    public function getParentFile()
    {
        $p = $this->getParent();
        if ($p === null) {
            return null;
        }

        return new PhingFile((string) $p, (int) $this->prefixLength);
    }

    /**
     * Converts this abstract pathname into a pathname string.  The resulting
     * string uses the default name-separator character to separate the names
     * in the name sequence.
     *
     * @return string The string form of this abstract pathname
     */
    public function getPath()
    {
        return (string) $this->path;
    }

    /**
     * Returns path without leading basedir.
     *
     * @param string $basedir Base directory to strip
     *
     * @return string Path without basedir
     *
     * @uses getPath()
     */
    public function getPathWithoutBase($basedir)
    {
        if (!StringHelper::endsWith(self::$separator, $basedir)) {
            $basedir .= self::$separator;
        }
        $path = $this->getPath();
        if (!substr($path, 0, strlen($basedir)) == $basedir) {
            //path does not begin with basedir, we don't modify it
            return $path;
        }

        return substr($path, strlen($basedir));
    }

    /**
     * Tests whether this abstract pathname is absolute.  The definition of
     * absolute pathname is system dependent.  On UNIX systems, a pathname is
     * absolute if its prefix is "/".  On Win32 systems, a pathname is absolute
     * if its prefix is a drive specifier followed by "\\", or if its prefix
     * is "\\".
     *
     * @return boolean true if this abstract pathname is absolute, false otherwise
     */
    public function isAbsolute()
    {
        return ($this->prefixLength !== 0);
    }

    /**
     * Returns the file extension for a given file. For example test.php would be returned as php.
     *
     * @return string The name of the extension.
     */
    public function getFileExtension()
    {
        return pathinfo((string) $this->getAbsolutePath(), PATHINFO_EXTENSION);
    }

    /**
     * Returns the absolute pathname string of this abstract pathname.
     *
     * If this abstract pathname is already absolute, then the pathname
     * string is simply returned as if by the getPath method.
     * If this abstract pathname is the empty abstract pathname then
     * the pathname string of the current user directory, which is named by the
     * system property user.dir, is returned.  Otherwise this
     * pathname is resolved in a system-dependent way.  On UNIX systems, a
     * relative pathname is made absolute by resolving it against the current
     * user directory.  On Win32 systems, a relative pathname is made absolute
     * by resolving it against the current directory of the drive named by the
     * pathname, if any; if not, it is resolved against the current user
     * directory.
     *
     * @return string The absolute pathname string denoting the same file or
     *                directory as this abstract pathname
     * @see    #isAbsolute()
     */
    public function getAbsolutePath()
    {
        $fs = FileSystem::getFileSystem();

        return $fs->resolveFile($this);
    }

    /**
     * Returns the absolute form of this abstract pathname.  Equivalent to
     * getAbsolutePath.
     *
     * @return PhingFile The absolute abstract pathname denoting the same file or
     *                directory as this abstract pathname
     */
    public function getAbsoluteFile()
    {
        return new PhingFile((string) $this->getAbsolutePath());
    }


    /**
     * Returns the canonical pathname string of this abstract pathname.
     *
     * A canonical pathname is both absolute and unique. The precise
     * definition of canonical form is system-dependent. This method first
     * converts this pathname to absolute form if necessary, as if by invoking the
     * getAbsolutePath() method, and then maps it to its unique form in a
     * system-dependent way.  This typically involves removing redundant names
     * such as "." and .. from the pathname, resolving symbolic links
     * (on UNIX platforms), and converting drive letters to a standard case
     * (on Win32 platforms).
     *
     * Every pathname that denotes an existing file or directory has a
     * unique canonical form.  Every pathname that denotes a nonexistent file
     * or directory also has a unique canonical form.  The canonical form of
     * the pathname of a nonexistent file or directory may be different from
     * the canonical form of the same pathname after the file or directory is
     * created.  Similarly, the canonical form of the pathname of an existing
     * file or directory may be different from the canonical form of the same
     * pathname after the file or directory is deleted.
     *
     * @return string The canonical pathname string denoting the same file or
     *                directory as this abstract pathname
     */
    public function getCanonicalPath()
    {
        $fs = FileSystem::getFileSystem();

        return $fs->canonicalize($this->path);
    }


    /**
     * Returns the canonical form of this abstract pathname.  Equivalent to
     * getCanonicalPath(.
     *
     * @return PhingFile The canonical pathname string denoting the same file or
     *                   directory as this abstract pathname
     */
    public function getCanonicalFile()
    {
        return new PhingFile($this->getCanonicalPath());
    }

    /**
     * Converts this abstract pathname into a file: URL.  The
     * exact form of the URL is system-dependent.  If it can be determined that
     * the file denoted by this abstract pathname is a directory, then the
     * resulting URL will end with a slash.
     *
     * Usage note: This method does not automatically escape
     * characters that are illegal in URLs.  It is recommended that new code
     * convert an abstract pathname into a URL by first converting it into a
     * URI, via the toURI() method, and then converting the URI
     * into a URL via the URI::toURL()
     *
     * @return void A URL object representing the equivalent file URL
     * @todo   Not implemented yet
     *
     */
    public function toURL()
    {
        /*
        // URL class not implemented yet
        return new URL("file", "", $this->_slashify($this->getAbsolutePath(), $this->isDirectory()));
        */
    }

    /**
     * Constructs a file: URI that represents this abstract pathname.
     * @todo   Not implemented yet
     * @return void
     */
    public function toURI()
    {
        /*
        $f = $this->getAbsoluteFile();
           $sp = (string) $this->slashify($f->getPath(), $f->isDirectory());
           if (StringHelper::startsWith('//', $sp))
        $sp = '//' + sp;

           return new URI('file', null, $sp, null);
        */
    }

    /**
     *
     * Enter description here ...
     * @param  PhingFile|string $path
     * @param  boolean          $isDirectory
     * @return string
     */
    public function _slashify($path, $isDirectory)
    {
        $p = (string) $path;

        if (self::$separator !== '/') {
            $p = str_replace(self::$separator, '/', $p);
        }

        if (!StringHelper::startsWith('/', $p)) {
            $p = '/' . $p;
        }

        if (!StringHelper::endsWith('/', $p) && $isDirectory) {
            $p = $p . '/';
        }

        return $p;
    }

    /* -- Attribute accessors -- */

    /**
     * Tests whether the application can read the file denoted by this
     * abstract pathname.
     *
     * @return boolean true if and only if the file specified by this
     *                 abstract pathname exists and can be read by the
     *                 application; false otherwise
     */
    public function canRead()
    {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess($this)) {
            return (boolean) @is_link($this->getAbsolutePath()) || @is_readable($this->getAbsolutePath());
        }

        return false;
    }

    /**
     * Tests whether the application can modify to the file denoted by this
     * abstract pathname.
     *
     * @return boolean true if and only if the file system actually
     *                 contains a file denoted by this abstract pathname and
     *                 the application is allowed to write to the file;
     *                 false otherwise.
     */
    public function canWrite()
    {
        $fs = FileSystem::getFileSystem();

        return $fs->checkAccess($this, true);
    }

    /**
     * Tests whether the file denoted by this abstract pathname exists.
     *
     * @return boolean true if and only if the file denoted by this
     *                 abstract pathname exists; false otherwise
     */
    public function exists()
    {
        clearstatcache();

        if (is_link($this->path)) {
            return true;
        } else {
            if ($this->isDirectory()) {
                return true;
            } else {
                return @file_exists($this->path) || is_link($this->path);
            }
        }
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a
     * directory.
     *
     * @throws IOException
     * @return boolean true if and only if the file denoted by this
     *                 abstract pathname exists and is a directory;
     *                 false otherwise
     */
    public function isDirectory()
    {
        clearstatcache();
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->path);
        }

        return @is_dir($this->path) && !@is_link($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a normal
     * file.  A file is normal if it is not a directory and, in
     * addition, satisfies other system-dependent criteria.  Any non-directory
     * file created by a Java application is guaranteed to be a normal file.
     *
     * @return boolean true if and only if the file denoted by this
     *                 abstract pathname exists and is a normal file;
     *                 false otherwise
     */
    public function isFile()
    {
        clearstatcache();
        //$fs = FileSystem::getFileSystem();
        return @is_file($this->path);
    }

    /**
     * Tests whether the file named by this abstract pathname is a hidden
     * file.  The exact definition of hidden is system-dependent.  On
     * UNIX systems, a file is considered to be hidden if its name begins with
     * a period character ('.').  On Win32 systems, a file is considered to be
     * hidden if it has been marked as such in the filesystem. Currently there
     * seems to be no way to dermine isHidden on Win file systems via PHP
     *
     * @throws IOException
     * @return boolean true if and only if the file denoted by this
     *                 abstract pathname is hidden according to the conventions of the
     *                 underlying platform
     */
    public function isHidden()
    {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->path);
        }

        return (($fs->getBooleanAttributes($this) & FileSystem::BA_HIDDEN) !== 0);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a symbolic link.
     *
     * @throws IOException
     * @return boolean true if and only if the file denoted by this
     *                 abstract pathname exists and is a symbolic link;
     *                 false otherwise
     */
    public function isLink()
    {
        clearstatcache();
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->path);
        }

        return @is_link($this->path);
    }

    /**
     * Returns the target of the symbolic link denoted by this abstract pathname
     *
     * @return string the target of the symbolic link denoted by this abstract pathname
     */
    public function getLinkTarget()
    {
        return @readlink($this->path);
    }

    /**
     * Returns the time that the file denoted by this abstract pathname was
     * last modified.
     *
     * @throws IOException
     * @return int An integer value representing the time the file was
     *             last modified, measured in seconds since the epoch
     *             (00:00:00 GMT, January 1, 1970), or 0 if the
     *             file does not exist or if an I/O error occurs
     */
    public function lastModified()
    {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->path);
        }

        return $fs->getLastModifiedTime($this);
    }

    /**
     * Returns the length of the file denoted by this abstract pathname.
     * The return value is unspecified if this pathname denotes a directory.
     *
     * @throws IOException
     * @return int The length, in bytes, of the file denoted by this abstract
     *             pathname, or 0 if the file does not exist
     */
    public function length()
    {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->path . "\n");
        }

        return $fs->getLength($this);
    }

    /**
     * Convenience method for returning the contents of this file as a string.
     * This method uses file_get_contents() to read file in an optimized way.
     * @return string
     * @throws Exception - if file cannot be read
     */
    public function contents()
    {
        if (!$this->canRead() || !$this->isFile()) {
            throw new IOException("Cannot read file contents!");
        }

        return file_get_contents($this->getAbsolutePath());
    }

    /* -- File operations -- */

    /**
     * Atomically creates a new, empty file named by this abstract pathname if
     * and only if a file with this name does not yet exist.  The check for the
     * existence of the file and the creation of the file if it does not exist
     * are a single operation that is atomic with respect to all other
     * filesystem activities that might affect the file.
     *
     * @param bool $parents
     * @param int $mode
     * @throws IOException
     * @return boolean     true if the named file does not exist and was
     *                     successfully created; <code>false</code> if the named file
     *                     already exists
     */
    public function createNewFile($parents = true, $mode = 0777)
    {
        $file = FileSystem::getFileSystem()->createNewFile($this->path);

        return $file;
    }

    /**
     * Deletes the file or directory denoted by this abstract pathname.  If
     * this pathname denotes a directory, then the directory must be empty in
     * order to be deleted.
     *
     * @param bool $recursive
     * @throws IOException
     * @return boolean true if and only if the file or directory is
     *                 successfully deleted; false otherwise
     */
    public function delete($recursive = false)
    {
        $fs = FileSystem::getFileSystem();
        if ($fs->canDelete($this) !== true) {
            throw new IOException("Cannot delete " . $this->path . "\n");
        }

        return $fs->delete($this, $recursive);
    }

    /**
     * Requests that the file or directory denoted by this abstract pathname
     * be deleted when php terminates.  Deletion will be attempted only for
     * normal termination of php and if and if only Phing::shutdown() is
     * called.
     *
     * Once deletion has been requested, it is not possible to cancel the
     * request.  This method should therefore be used with care.
     *
     */
    public function deleteOnExit()
    {
        $fs = FileSystem::getFileSystem();
        $fs->deleteOnExit($this);
    }

    /**
     * Returns an array of strings naming the files and directories in the
     * directory denoted by this abstract pathname.
     *
     * If this abstract pathname does not denote a directory, then this
     * method returns null  Otherwise an array of strings is
     * returned, one for each file or directory in the directory.  Names
     * denoting the directory itself and the directory's parent directory are
     * not included in the result.  Each string is a file name rather than a
     * complete path.
     *
     * There is no guarantee that the name strings in the resulting array
     * will appear in any specific order; they are not, in particular,
     * guaranteed to appear in alphabetical order.
     *
     * @param string $filter
     * @return array An array of strings naming the files and directories in the
     *               directory denoted by this abstract pathname.  The array will be
     *               empty if the directory is empty.  Returns null if
     *               this abstract pathname does not denote a directory, or if an
     *               I/O error occurs.
     */
    public function listDir($filter = null)
    {
        $fs = FileSystem::getFileSystem();

        return $fs->lister($this, $filter);
    }

    /**
     * @param string $filter
     *
     * @return array
     */
    public function listFiles($filter = null)
    {
        $ss = $this->listDir($filter);
        if ($ss === null) {
            return null;
        }
        $n = count($ss);
        $fs = array();
        for ($i = 0; $i < $n; $i++) {
            $fs[$i] = new PhingFile((string) $this->path, (string) $ss[$i]);
        }

        return $fs;
    }

    /**
     * Creates the directory named by this abstract pathname, including any
     * necessary but nonexistent parent directories.  Note that if this
     * operation fails it may have succeeded in creating some of the necessary
     * parent directories.
     *
     * @param int $mode
     * @throws IOException
     * @return boolean     true if and only if the directory was created,
     *                     along with all necessary parent directories; false
     *                     otherwise
     */
    public function mkdirs($mode = 0755)
    {
        if ($this->exists()) {
            return false;
        }
        try {
            if ($this->mkdir($mode)) {
                return true;
            }
        } catch (IOException $ioe) {
            // IOException from mkdir() means that directory propbably didn't exist.
        }
        $parentFile = $this->getParentFile();

        return (($parentFile !== null) && ($parentFile->mkdirs($mode) && $this->mkdir($mode)));
    }

    /**
     * Creates the directory named by this abstract pathname.
     *
     * @param int $mode
     * @throws IOException
     * @return boolean     true if and only if the directory was created; false otherwise
     */
    public function mkdir($mode = 0755)
    {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess(new PhingFile($this->path), true) !== true) {
            throw new IOException("No write access to " . $this->getPath());
        }

        return $fs->createDirectory($this, $mode);
    }

    /**
     * Renames the file denoted by this abstract pathname.
     *
     * @param  PhingFile $destFile The new abstract pathname for the named file
     * @throws IOException
     * @return boolean   true if and only if the renaming succeeded; false otherwise
     */
    public function renameTo(PhingFile $destFile)
    {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No write access to " . $this->getPath());
        }

        return $fs->rename($this, $destFile);
    }

    /**
     * Simple-copies file denoted by this abstract pathname into another
     * PhingFile
     *
     * @param  PhingFile $destFile The new abstract pathname for the named file
     * @throws IOException
     * @return boolean   true if and only if the renaming succeeded; false otherwise
     */
    public function copyTo(PhingFile $destFile)
    {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess($this) !== true) {
            throw new IOException("No read access to " . $this->getPath() . "\n");
        }

        if ($fs->checkAccess($destFile, true) !== true) {
            throw new IOException("File::copyTo() No write access to " . $destFile->getPath());
        }

        return $fs->copy($this, $destFile);
    }

    /**
     * Sets the last-modified time of the file or directory named by this
     * abstract pathname.
     *
     * All platforms support file-modification times to the nearest second,
     * but some provide more precision.  The argument will be truncated to fit
     * the supported precision.  If the operation succeeds and no intervening
     * operations on the file take place, then the next invocation of the
     * lastModified method will return the (possibly truncated) time argument
     * that was passed to this method.
     *
     * @param  int $time The new last-modified time, measured in milliseconds since
     *                       the epoch (00:00:00 GMT, January 1, 1970)
     * @throws Exception
     * @return boolean true if and only if the operation succeeded; false otherwise
     */
    public function setLastModified($time)
    {
        $time = (int) $time;
        if ($time < 0) {
            throw new Exception("IllegalArgumentException, Negative $time\n");
        }

        $fs = FileSystem::getFileSystem();

        return $fs->setLastModifiedTime($this, $time);
    }

    /**
     * Marks the file or directory named by this abstract pathname so that
     * only read operations are allowed.  After invoking this method the file
     * or directory is guaranteed not to change until it is either deleted or
     * marked to allow write access.  Whether or not a read-only file or
     * directory may be deleted depends upon the underlying system.
     *
     * @throws IOException
     * @return boolean true if and only if the operation succeeded; false otherwise
     */
    public function setReadOnly()
    {
        $fs = FileSystem::getFileSystem();
        if ($fs->checkAccess($this, true) !== true) {
            // Error, no write access
            throw new IOException("No write access to " . $this->getPath());
        }

        return $fs->setReadOnly($this);
    }

    /**
     * Sets the owner of the file.
     *
     * @param mixed $user User name or number.
     *
     * @throws IOException
     */
    public function setUser($user)
    {
        $fs = FileSystem::getFileSystem();

        return $fs->chown($this->getPath(), $user);
    }

    /**
     * Retrieve the owner of this file.
     *
     * @return int User ID of the owner of this file.
     */
    public function getUser()
    {
        return @fileowner($this->getPath());
    }

    /**
     * Sets the group of the file.
     *
     * @param string $group
     *
     * @throws IOException
     */
    public function setGroup($group)
    {
        $fs = FileSystem::getFileSystem();

        return $fs->chgrp($this->getPath(), $group);
    }

    /**
     * Retrieve the group of this file.
     *
     * @return int User ID of the owner of this file.
     */
    public function getGroup()
    {
        return @filegroup($this->getPath());
    }

    /**
     * Sets the mode of the file
     *
     * @param int $mode Ocatal mode.
     */
    public function setMode($mode)
    {
        $fs = FileSystem::getFileSystem();

        return $fs->chmod($this->getPath(), $mode);
    }

    /**
     * Retrieve the mode of this file.
     *
     * @return int
     */
    public function getMode()
    {
        return @fileperms($this->getPath());
    }

    /* -- Filesystem interface -- */

    /**
     * List the available filesystem roots.
     *
     * A particular platform may support zero or more hierarchically-organized
     * file systems.  Each file system has a root  directory from which all
     * other files in that file system can be reached.
     * Windows platforms, for example, have a root directory for each active
     * drive; UNIX platforms have a single root directory, namely "/".
     * The set of available filesystem roots is affected by various system-level
     * operations such the insertion or ejection of removable media and the
     * disconnecting or unmounting of physical or virtual disk drives.
     *
     * This method returns an array of PhingFile objects that
     * denote the root directories of the available filesystem roots.  It is
     * guaranteed that the canonical pathname of any file physically present on
     * the local machine will begin with one of the roots returned by this
     * method.
     *
     * The canonical pathname of a file that resides on some other machine
     * and is accessed via a remote-filesystem protocol such as SMB or NFS may
     * or may not begin with one of the roots returned by this method.  If the
     * pathname of a remote file is syntactically indistinguishable from the
     * pathname of a local file then it will begin with one of the roots
     * returned by this method.  Thus, for example, PhingFile objects
     * denoting the root directories of the mapped network drives of a Windows
     * platform will be returned by this method, while PhingFile
     * objects containing UNC pathnames will not be returned by this method.
     *
     * @return array An array of PhingFile objects denoting the available
     *               filesystem roots, or null if the set of roots
     *               could not be determined.  The array will be empty if there are
     *               no filesystem roots.
     */
    public function listRoots()
    {
        $fs = FileSystem::getFileSystem();

        return (array) $fs->listRoots();
    }

    /* -- Tempfile management -- */

    /**
     * Returns the path to the temp directory.
     * @return string
     */
    public static function getTempDir()
    {
        return Phing::getProperty('php.tmpdir');
    }

    /**
     * Static method that creates a unique filename whose name begins with
     * $prefix and ends with $suffix in the directory $directory. $directory
     * is a reference to a PhingFile Object.
     * Then, the file is locked for exclusive reading/writing.
     *
     * @author manuel holtgrewe, grin@gmx.net
     *
     * @param $prefix
     * @param $suffix
     * @param PhingFile $directory
     *
     * @throws IOException
     * @return PhingFile
     */
    public static function createTempFile($prefix, $suffix, PhingFile $directory)
    {

        // quick but efficient hack to create a unique filename ;-)
        $result = null;
        do {
            $result = new PhingFile($directory, $prefix . substr(md5(time()), 0, 8) . $suffix);
        } while (file_exists($result->getPath()));

        $fs = FileSystem::getFileSystem();
        $fs->createNewFile($result->getPath());
        $fs->lock($result);

        return $result;
    }

    /**
     * If necessary, $File the lock on $File is removed and then the file is
     * deleted.
     */
    public function removeTempFile()
    {
        $fs = FileSystem::getFileSystem();
        // catch IO Exception
        $fs->unlock($this);
        $this->delete();
    }

    /* -- Basic infrastructure -- */

    /**
     * Compares two abstract pathnames lexicographically.  The ordering
     * defined by this method depends upon the underlying system.  On UNIX
     * systems, alphabetic case is significant in comparing pathnames; on Win32
     * systems it is not.
     *
     * @param PhingFile $file Th file whose pathname sould be compared to the pathname of this file.
     *
     * @return int Zero if the argument is equal to this abstract pathname, a
     *             value less than zero if this abstract pathname is
     *             lexicographically less than the argument, or a value greater
     *             than zero if this abstract pathname is lexicographically
     *             greater than the argument
     */
    public function compareTo(PhingFile $file)
    {
        $fs = FileSystem::getFileSystem();

        return $fs->compare($this, $file);
    }

    /**
     * Tests this abstract pathname for equality with the given object.
     * Returns <code>true</code> if and only if the argument is not
     * <code>null</code> and is an abstract pathname that denotes the same file
     * or directory as this abstract pathname.  Whether or not two abstract
     * pathnames are equal depends upon the underlying system.  On UNIX
     * systems, alphabetic case is significant in comparing pathnames; on Win32
     * systems it is not.
     *
     * @param PhingFile $obj
     *
     * @return boolean
     */
    public function equals($obj)
    {
        if (($obj !== null) && ($obj instanceof PhingFile)) {
            return ($this->compareTo($obj) === 0);
        }

        return false;
    }

    /**
     * Backwards compatibility - @see __toString()
     *
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Return string representation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }
}
