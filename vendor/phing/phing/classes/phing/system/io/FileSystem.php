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

/**
 * This is an abstract class for platform specific filesystem implementations
 * you have to implement each method in the platform specific filesystem implementation
 * classes Your local filesytem implementation must extend this class.
 * You should also use this class as a template to write your local implementation
 * Some native PHP filesystem specific methods are abstracted here as well. Anyway
 * you _must_ always use this methods via a PhingFile object (that by nature uses the
 * *FileSystem drivers to access the real filesystem via this class using natives.
 *
 * FIXME:
 *  - Error handling reduced to min fallthrough runtime exceptions
 *    more precise errorhandling is done by the PhingFile class
 *
 * @author Charlie Killian <charlie@tizac.com>
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @package phing.system.io
 */
abstract class FileSystem
{

    /**
     * @var int
     */
    const BA_EXISTS = 0x01;

    /**
     * @var int
     */
    const BA_REGULAR = 0x02;

    /**
     * @var int
     */
    const BA_DIRECTORY = 0x04;

    /**
     * @var int
     */
    const BA_HIDDEN = 0x08;

    /**
     * Instance for getFileSystem() method.
     * @var FileSystem
     */
    private static $fs;

    /**
     * Static method to return the FileSystem singelton representing
     * this platform's local filesystem driver.
     *
     * @return FileSystem
     * @throws IOException
     */
    public static function getFileSystem()
    {
        if (self::$fs === null) {
            switch (Phing::getProperty('host.fstype')) {
                case 'UNIX':
                    include_once 'phing/system/io/UnixFileSystem.php';
                    self::$fs = new UnixFileSystem();
                    break;
                case 'WIN32':
                    include_once 'phing/system/io/Win32FileSystem.php';
                    self::$fs = new Win32FileSystem();
                    break;
                case 'WINNT':
                    include_once 'phing/system/io/WinNTFileSystem.php';
                    self::$fs = new WinNTFileSystem();
                    break;
                default:
                    throw new IOException("Host uses unsupported filesystem, unable to proceed");
            }
        }

        return self::$fs;
    }

    /* -- Normalization and construction -- */

    /**
     * Return the local filesystem's name-separator character.
     */
    abstract public function getSeparator();

    /**
     * Return the local filesystem's path-separator character.
     */
    abstract public function getPathSeparator();

    /**
     * Convert the given pathname string to normal form.  If the string is
     * already in normal form then it is simply returned.
     *
     * @param string $strPath
     */
    abstract public function normalize($strPath);

    /**
     * Compute the length of this pathname string's prefix.  The pathname
     * string must be in normal form.
     *
     * @param string $pathname
     */
    abstract public function prefixLength($pathname);

    /**
     * Resolve the child pathname string against the parent.
     * Both strings must be in normal form, and the result
     * will be a string in normal form.
     *
     * @param string $parent
     * @param string $child
     */
    abstract public function resolve($parent, $child);

    /**
     * Resolve the given abstract pathname into absolute form.  Invoked by the
     * getAbsolutePath and getCanonicalPath methods in the PhingFile class.
     *
     * @param PhingFile $f
     */
    abstract public function resolveFile(PhingFile $f);

    /**
     * Return the parent pathname string to be used when the parent-directory
     * argument in one of the two-argument PhingFile constructors is the empty
     * pathname.
     */
    abstract public function getDefaultParent();

    /**
     * Post-process the given URI path string if necessary.  This is used on
     * win32, e.g., to transform "/c:/foo" into "c:/foo".  The path string
     * still has slash separators; code in the PhingFile class will translate them
     * after this method returns.
     *
     * @param string $path
     */
    abstract public function fromURIPath($path);

    /* -- Path operations -- */

    /**
     * Tell whether or not the given abstract pathname is absolute.
     *
     * @param PhingFile $f
     */
    abstract public function isAbsolute(PhingFile $f);

    /**
     * canonicalize filename by checking on disk
     * @param  string $strPath
     * @return mixed  Canonical path or false if the file doesn't exist.
     */
    public function canonicalize($strPath)
    {
        return @realpath($strPath);
    }

    /* -- Attribute accessors -- */

    /**
     * Return the simple boolean attributes for the file or directory denoted
     * by the given abstract pathname, or zero if it does not exist or some
     * other I/O error occurs.
     *
     * @param PhingFile $f
     * @throws IOException
     */
    public function getBooleanAttributes($f)
    {
        throw new IOException("getBooleanAttributes() not implemented by fs driver");
    }

    /**
     * Check whether the file or directory denoted by the given abstract
     * pathname may be accessed by this process.  If the second argument is
     * false, then a check for read access is made; if the second
     * argument is true, then a check for write (not read-write)
     * access is made.  Return false if access is denied or an I/O error
     * occurs.
     *
     * @param PhingFile $f
     * @param boolean $write
     * @return bool
     */
    public function checkAccess(PhingFile $f, $write = false)
    {
        // we clear stat cache, its expensive to look up from scratch,
        // but we need to be sure
        @clearstatcache();


        // Shouldn't this be $f->GetAbsolutePath() ?
        // And why doesn't GetAbsolutePath() work?

        $strPath = (string) $f->getPath();

        // FIXME
        // if file object does denote a file that yet not existst
        // path rights are checked
        if (!@file_exists($strPath) && !is_dir($strPath)) {
            $strPath = $f->getParent();
            if ($strPath === null || !is_dir($strPath)) {
                $strPath = Phing::getProperty("user.dir");
            }
            //$strPath = dirname($strPath);
        }

        if (!$write) {
            return (boolean) @is_readable($strPath);
        } else {
            return (boolean) @is_writable($strPath);
        }
    }

    /**
     * Whether file can be deleted.
     * @param  PhingFile $f
     * @return boolean
     */
    public function canDelete(PhingFile $f)
    {
        clearstatcache();
        $dir = dirname($f->getAbsolutePath());

        return (bool) @is_writable($dir);
    }

    /**
     * Return the time at which the file or directory denoted by the given
     * abstract pathname was last modified, or zero if it does not exist or
     * some other I/O error occurs.
     *
     * @param  PhingFile   $f
     * @return int
     * @throws IOException
     */
    public function getLastModifiedTime(PhingFile $f)
    {

        if (!$f->exists()) {
            return 0;
        }

        @clearstatcache();
        $strPath = (string) $f->getPath();

        if (@is_link($strPath)) {
            $stats = @lstat($strPath);

            if (!isset($stats['mtime'])) {
                $mtime = false;
            } else {
                $mtime = $stats['mtime'];
            }
        } else {
            $mtime = @filemtime($strPath);
        }

        if (false === $mtime) {
            $msg = "FileSystem::getLastModifiedTime() FAILED. Can not get modified time of $strPath. $php_errormsg";
            throw new IOException($msg);
        }

        return (int) $mtime;
    }

    /**
     * Return the length in bytes of the file denoted by the given abstract
     * pathname, or zero if it does not exist, is a directory, or some other
     * I/O error occurs.
     *
     * @param  PhingFile   $f
     * @throws IOException
     * @return int
     */
    public function getLength(PhingFile $f)
    {
        $strPath = (string) $f->getAbsolutePath();
        $fs = filesize((string) $strPath);
        if ($fs !== false) {
            return $fs;
        } else {
            $msg = "FileSystem::Read() FAILED. Cannot get filesize of $strPath. $php_errormsg";
            throw new IOException($msg);
        }
    }

    /* -- File operations -- */

    /**
     * Create a new empty file with the given pathname.  Return
     * true if the file was created and false if a
     * file or directory with the given pathname already exists.  Throw an
     * IOException if an I/O error occurs.
     *
     * @param  string      $strPathname Path of the file to be created.
     * @throws IOException
     * @return boolean
     */
    public function createNewFile($strPathname)
    {
        if (@file_exists($strPathname)) {
            return false;
        }

        // Create new file
        $fp = @fopen($strPathname, "w");
        if ($fp === false) {
            throw new IOException("The file \"$strPathname\" could not be created");
        }
        @fclose($fp);

        return true;
    }

    /**
     * Delete the file or directory denoted by the given abstract pathname,
     * returning true if and only if the operation succeeds.
     *
     * @param  PhingFile $f
     * @param  boolean   $recursive
     * @return void
     */
    public function delete(PhingFile $f, $recursive = false)
    {
        if ($f->isDirectory()) {
            return $this->rmdir($f->getPath(), $recursive);
        } else {
            return $this->unlink($f->getPath());
        }
    }

    /**
     * Arrange for the file or directory denoted by the given abstract
     * pathname to be deleted when Phing::shutdown is called, returning
     * true if and only if the operation succeeds.
     *
     * @param  PhingFile   $f
     * @throws IOException
     */
    public function deleteOnExit($f)
    {
        throw new IOException("deleteOnExit() not implemented by local fs driver");
    }

    /**
     * List the elements of the directory denoted by the given abstract
     * pathname.  Return an array of strings naming the elements of the
     * directory if successful; otherwise, return <code>null</code>.
     *
     * @param PhingFile $f
     * @return array
     */
    public function listDir(PhingFile $f)
    {
        $strPath = (string) $f->getAbsolutePath();
        $d = @dir($strPath);
        if (!$d) {
            return null;
        }
        $list = array();
        while ($entry = $d->read()) {
            if ($entry != "." && $entry != "..") {
                array_push($list, $entry);
            }
        }
        $d->close();
        unset($d);

        return $list;
    }

    /**
     * Create a new directory denoted by the given abstract pathname,
     * returning true if and only if the operation succeeds.
     *
     * NOTE: umask() is reset to 0 while executing mkdir(), and restored afterwards
     *
     * @param  PhingFile $f
     * @param  int       $mode
     * @return boolean
     */
    public function createDirectory(&$f, $mode = 0755)
    {
        $old_umask = umask(0);
        $return = @mkdir($f->getAbsolutePath(), $mode);
        umask($old_umask);

        return $return;
    }

    /**
     * Rename the file or directory denoted by the first abstract pathname to
     * the second abstract pathname, returning true if and only if
     * the operation succeeds.
     *
     * @param  PhingFile   $f1 abstract source file
     * @param  PhingFile   $f2 abstract destination file
     * @return void
     * @throws IOException if rename cannot be performed
     */
    public function rename(PhingFile $f1, PhingFile $f2)
    {
        // get the canonical paths of the file to rename
        $src = $f1->getAbsolutePath();
        $dest = $f2->getAbsolutePath();
        if (false === @rename($src, $dest)) {
            $msg = "Rename FAILED. Cannot rename $src to $dest. $php_errormsg";
            throw new IOException($msg);
        }
    }

    /**
     * Set the last-modified time of the file or directory denoted by the
     * given abstract pathname returning true if and only if the
     * operation succeeds.
     *
     * @param  PhingFile   $f
     * @param  int         $time
     * @return void
     * @throws IOException
     */
    public function setLastModifiedTime(PhingFile $f, $time)
    {
        $path = $f->getPath();
        $success = @touch($path, $time);
        if (!$success) {
            throw new IOException("Could not touch '" . $path . "' due to: $php_errormsg");
        }
    }

    /**
     * Mark the file or directory denoted by the given abstract pathname as
     * read-only, returning <code>true</code> if and only if the operation
     * succeeds.
     *
     * @param  PhingFile   $f
     * @throws IOException
     */
    public function setReadOnly($f)
    {
        throw new IOException("setReadonly() not implemented by local fs driver");
    }

    /* -- Filesystem interface -- */

    /**
     * List the available filesystem roots, return array of PhingFile objects
     * @throws IOException
     */
    public function listRoots()
    {
        throw new IOException("listRoots() not implemented by local fs driver");
    }

    /* -- Basic infrastructure -- */

    /**
     * Compare two abstract pathnames lexicographically.
     *
     * @param PhingFile $f1
     * @param PhingFile $f2
     * @throws IOException
     */
    public function compare(PhingFile $f1, PhingFile $f2)
    {
        throw new IOException("compare() not implemented by local fs driver");
    }

    /**
     * Copy a file.
     *
     * @param PhingFile $src  Source path and name file to copy.
     * @param PhingFile $dest Destination path and name of new file.
     *
     * @return void
     *
     * @throws IOException if file cannot be copied.
     */
    public function copy(PhingFile $src, PhingFile $dest)
    {
        global $php_errormsg;

        // Recursively copy a directory
        if ($src->isDirectory()) {
            return $this->copyr($src->getAbsolutePath(), $dest->getAbsolutePath());
        }

        $srcPath = $src->getAbsolutePath();
        $destPath = $dest->getAbsolutePath();

        if (false === @copy($srcPath, $destPath)) { // Copy FAILED. Log and return err.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "FileSystem::copy() FAILED. Cannot copy $srcPath to $destPath. $php_errormsg";
            throw new IOException($msg);
        }

        $dest->setMode($src->getMode());
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/repos/v/function.copyr.php
     *
     * @param  string $source Source path
     * @param  string $dest   Destination path
     *
     * @return bool   Returns TRUE on success, FALSE on failure
     */
    public function copyr($source, $dest)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->copyr("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();

        return true;
    }

    /**
     * Change the ownership on a file or directory.
     *
     * @param string $pathname Path and name of file or directory.
     * @param string $user     The user name or number of the file or directory. See http://us.php.net/chown
     *
     * @return void
     *
     * @throws IOException if operation failed.
     */
    public function chown($pathname, $user)
    {
        if (false === @chown($pathname, $user)) { // FAILED.
            $msg = "FileSystem::chown() FAILED. Cannot chown $pathname. User $user." . (isset($php_errormsg) ? ' ' . $php_errormsg : "");
            throw new IOException($msg);
        }
    }

    /**
     * Change the group on a file or directory.
     *
     * @param string $pathname Path and name of file or directory.
     * @param string $group    The group of the file or directory. See http://us.php.net/chgrp
     *
     * @return void
     * @throws IOException if operation failed.
     */
    public function chgrp($pathname, $group)
    {
        if (false === @chgrp($pathname, $group)) { // FAILED.
            $msg = "FileSystem::chgrp() FAILED. Cannot chown $pathname. Group $group." . (isset($php_errormsg) ? ' ' . $php_errormsg : "");
            throw new IOException($msg);
        }
    }

    /**
     * Change the permissions on a file or directory.
     *
     * @param string $pathname Path and name of file or directory.
     * @param int    $mode     The mode (permissions) of the file or
     *                         directory. If using octal add leading 0. eg. 0777.
     *                         Mode is affected by the umask system setting.
     *
     * @return void
     * @throws IOException if operation failed.
     */
    public function chmod($pathname, $mode)
    {
        $str_mode = decoct($mode); // Show octal in messages.
        if (false === @chmod($pathname, $mode)) { // FAILED.
            $msg = "FileSystem::chmod() FAILED. Cannot chmod $pathname. Mode $str_mode." . (isset($php_errormsg) ? ' ' . $php_errormsg : "");
            throw new IOException($msg);
        }
    }

    /**
     * Locks a file and throws an Exception if this is not possible.
     *
     * @param  PhingFile $f
     * @return void
     * @throws IOException
     */
    public function lock(PhingFile $f)
    {
        $filename = $f->getPath();
        $fp = @fopen($filename, "w");
        $result = @flock($fp, LOCK_EX);
        @fclose($fp);
        if (!$result) {
            throw new IOException("Could not lock file '$filename'");
        }
    }

    /**
     * Unlocks a file and throws an IO Error if this is not possible.
     *
     * @param  PhingFile $f
     * @throws IOException
     * @return void
     */
    public function unlock(PhingFile $f)
    {
        $filename = $f->getPath();
        $fp = @fopen($filename, "w");
        $result = @flock($fp, LOCK_UN);
        fclose($fp);
        if (!$result) {
            throw new IOException("Could not unlock file '$filename'");
        }
    }

    /**
     * Delete a file.
     *
     * @param string $file Path and/or name of file to delete.
     *
     * @return void
     * @throws IOException - if an error is encountered.
     */
    public function unlink($file)
    {
        global $php_errormsg;
        if (false === @unlink($file)) {
            $msg = "FileSystem::unlink() FAILED. Cannot unlink '$file'. $php_errormsg";
            throw new IOException($msg);
        }
    }

    /**
     * Symbolically link a file to another name.
     *
     * Currently symlink is not implemented on Windows. Don't use if the application is to be portable.
     *
     * @param  string $target Path and/or name of file to link.
     * @param  string $link Path and/or name of link to be created.
     * @throws IOException
     * @return void
     */
    public function symlink($target, $link)
    {

        // If Windows OS then symlink() will report it is not supported in
        // the build. Use this error instead of checking for Windows as the OS.

        if (false === @symlink($target, $link)) {
            // Add error from php to end of log message. $php_errormsg.
            $msg = "FileSystem::Symlink() FAILED. Cannot symlink '$target' to '$link'. $php_errormsg";
            throw new IOException($msg);
        }

    }

    /**
     * Set the modification and access time on a file to the present time.
     *
     * @param  string $file Path and/or name of file to touch.
     * @param  int $time
     * @throws Exception
     * @return void
     */
    public function touch($file, $time = null)
    {
        global $php_errormsg;

        if (null === $time) {
            $error = @touch($file);
        } else {
            $error = @touch($file, $time);
        }

        if (false === $error) { // FAILED.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "FileSystem::touch() FAILED. Cannot touch '$file'. $php_errormsg";
            throw new Exception($msg);
        }
    }

    /**
     * Delete an empty directory OR a directory and all of its contents.
     *
     * @param string $dir Path and/or name of directory to delete.
     * @param bool $children     False: don't delete directory contents.
     *                           True: delete directory contents.
     *
     * @throws Exception
     *
     * @return void
     */
    public function rmdir($dir, $children = false)
    {
        global $php_errormsg;

        // If children=FALSE only delete dir if empty.
        if (false === $children) {

            if (false === @rmdir($dir)) { // FAILED.
                // Add error from php to end of log message. $php_errormsg.
                $msg = "FileSystem::rmdir() FAILED. Cannot rmdir $dir. $php_errormsg";
                throw new Exception($msg);
            }

        } else { // delete contents and dir.

            $handle = @opendir($dir);

            if (false === $handle) { // Error.

                $msg = "FileSystem::rmdir() FAILED. Cannot opendir() $dir. $php_errormsg";
                throw new Exception($msg);

            } else { // Read from handle.

                // Don't error on readdir().
                while (false !== ($entry = @readdir($handle))) {

                    if ($entry != '.' && $entry != '..') {

                        // Only add / if it isn't already the last char.
                        // This ONLY serves the purpose of making the Logger
                        // output look nice:)

                        if (strpos(strrev($dir), DIRECTORY_SEPARATOR) === 0) { // there is a /
                            $next_entry = $dir . $entry;
                        } else { // no /
                            $next_entry = $dir . DIRECTORY_SEPARATOR . $entry;
                        }

                        // NOTE: As of php 4.1.1 is_dir doesn't return FALSE it
                        // returns 0. So use == not ===.

                        // Don't error on is_dir()
                        if (false == @is_dir($next_entry)) { // Is file.

                            try {
                                self::unlink($next_entry); // Delete.
                            } catch (Exception $e) {
                                $msg = "FileSystem::Rmdir() FAILED. Cannot FileSystem::Unlink() $next_entry. " . $e->getMessage(
                                    );
                                throw new Exception($msg);
                            }

                        } else { // Is directory.

                            try {
                                self::rmdir($next_entry, true); // Delete
                            } catch (Exception $e) {
                                $msg = "FileSystem::rmdir() FAILED. Cannot FileSystem::rmdir() $next_entry. " . $e->getMessage(
                                    );
                                throw new Exception($msg);
                            }

                        } // end is_dir else
                    } // end .. if
                } // end while
            } // end handle if

            // Don't error on closedir()
            @closedir($handle);

            if (false === @rmdir($dir)) { // FAILED.
                // Add error from php to end of log message. $php_errormsg.
                $msg = "FileSystem::rmdir() FAILED. Cannot rmdir $dir. $php_errormsg";
                throw new Exception($msg);
            }

        }

    }

    /**
     * Set the umask for file and directory creation.
     *
     * @param Int $mode
     * @throws Exception
     * @internal param Int $mode . Permissions usually in ocatal. Use leading 0 for
     *                    octal. Number between 0 and 0777.
     *
     * @return void
     */
    public function umask($mode)
    {
        global $php_errormsg;

        // CONSIDERME:
        // Throw a warning if mode is 0. PHP converts illegal octal numbers to
        // 0 so 0 might not be what the user intended.

        $str_mode = decoct($mode); // Show octal in messages.

        if (false === @umask($mode)) { // FAILED.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "FileSystem::Umask() FAILED. Value $mode. $php_errormsg";
            throw new Exception($msg);
        }
    }

    /**
     * Compare the modified time of two files.
     *
     * @param string $file1 Path and name of file1.
     * @param string $file2 Path and name of file2.
     *
     * @return int  1 if file1 is newer.
     *              -1 if file2 is newer.
     *              0 if files have the same time.
     *              Err object on failure.
     *
     * @throws Exception - if cannot get modified time of either file.
     */
    public function compareMTimes($file1, $file2)
    {

        $mtime1 = filemtime($file1);
        $mtime2 = filemtime($file2);

        if ($mtime1 === false) { // FAILED. Log and return err.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "FileSystem::compareMTimes() FAILED. Cannot can not get modified time of $file1.";
            throw new Exception($msg);
        } elseif ($mtime2 === false) { // FAILED. Log and return err.
            // Add error from php to end of log message. $php_errormsg.
            $msg = "FileSystem::compareMTimes() FAILED. Cannot can not get modified time of $file2.";
            throw new Exception($msg);
        } else { // Worked. Log and return compare.
            // Compare mtimes.
            if ($mtime1 == $mtime2) {
                return 0;
            } else {
                return ($mtime1 < $mtime2) ? -1 : 1;
            } // end compare
        }
    }
}
