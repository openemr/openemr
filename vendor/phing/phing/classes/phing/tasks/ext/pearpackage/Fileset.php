<?php
/*
 *  $Id: 4287f56d47c2d793bc22417402f8c37daf3745f3 $
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

/**
 * Builds list of files for PEAR_PackageFileManager using a Phing FileSet.
 *
 * Some code here is taken from PEAR_PackageFileManager_File -- getting results from flat
 * array into the assoc array expected from getFileList().
 *
 * @author   Greg Beaver
 * @author   Hans Lellelid <hans@xmpl.org>
 * @package  phing.tasks.ext.pearpackage
 * @version  $Id: 4287f56d47c2d793bc22417402f8c37daf3745f3 $
 */
class PEAR_PackageFileManager_Fileset
{

    /**
     * Current Phing Project.
     * @var Project
     */
    private $project;

    /**
     * FileSets to use.
     * @var array FileSet[]
     */
    private $filesets = array();

    /**
     * Set up the FileSet filelist generator
     *
     * 'project' and 'filesets' are the only options that this class uses.
     *
     * @param PEAR_PackageFileManager
     * @param array
     */
    public function __construct($options)
    {
        if (!is_array($options)) {
            $options = $options->getOptions();
        }

        $this->project = $options['phing_project'];
        $this->filesets = $options['phing_filesets'];
    }

    /**
     * Generate the <filelist></filelist> section
     * of the package file.
     *
     * This function performs the backend generation of the array
     * containing all files in this package
     * @return array structure of all files to include
     */
    public function getFileList()
    {

        $allfiles = array();

        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);

            $files = $ds->getIncludedFiles();

            // We need to store these files keyed by the basedir from DirectoryScanner
            // so that we can resolve the fullpath of the file later.
            if (isset($allfiles[$ds->getBasedir()])) {
                $allfiles[$ds->getBasedir()] = array_merge($allfiles[$ds->getBasedir()], $files);
            } else {
                $allfiles[$ds->getBasedir()] = $files;
            }
        }

        $struc = array();

        foreach ($allfiles as $basedir => $files) {

            foreach ($files as $file) {

                // paths are relative to $basedir above
                $path = strtr(dirname($file), DIRECTORY_SEPARATOR, '/');

                if (!$path || $path == '.') {
                    $path = '/'; // for array index
                }

                $parts = explode('.', basename($file));
                $ext = array_pop($parts);
                if (strlen($ext) == strlen($file)) {
                    $ext = '';
                }

                $f = new PhingFile($basedir, $file);

                $struc[$path][] = array(
                    'file' => basename($file),
                    'ext' => $ext,
                    'path' => (($path == '/') ? basename($file) : $path . '/' . basename($file)),
                    'fullpath' => $f->getAbsolutePath()
                );
            }
        }

        uksort($struc, 'strnatcasecmp');
        foreach ($struc as $key => $ind) {
            usort($ind, array($this, 'sortfiles'));
            $struc[$key] = $ind;
        }

        $tempstruc = $struc;
        $struc = array('/' => $tempstruc['/']);
        $bv = 0;
        foreach ($tempstruc as $key => $ind) {
            $save = $key;
            if ($key != '/') {
                $struc['/'] = $this->setupDirs($struc['/'], explode('/', $key), $tempstruc[$key]);
            }
        }
        uksort($struc['/'], array($this, 'mystrucsort'));

        return $struc;
    }

    /**
     * Recursively move contents of $struc into associative array
     *
     * The contents of $struc have many indexes like 'dir/subdir/subdir2'.
     * This function converts them to
     * array('dir' => array('subdir' => array('subdir2')))
     * @param array $struc is array('dir' => array of files in dir,
     *              'dir/subdir' => array of files in dir/subdir,...)
     * @param $dir
     * @param $contents
     * @internal param array $array form of 'dir/subdir/subdir2' array('dir','subdir','subdir2')
     * @return array same as struc but with array('dir' =>
     *               array(file1,file2,'subdir' => array(file1,...)))
     */
    private function setupDirs($struc, $dir, $contents)
    {

        if (!count($dir)) {
            foreach ($contents as $dir => $files) {
                if (is_string($dir)) {
                    if (strpos($dir, '/')) {
                        $test = true;
                        $a = $contents[$dir];
                        unset($contents[$dir]);
                        $b = explode('/', $dir);
                        $c = array_shift($b);
                        if (isset($contents[$c])) {
                            $contents[$c] = $this->setDir($contents[$c], $this->setupDirs(array(), $b, $a));
                        } else {
                            $contents[$c] = $this->setupDirs(array(), $b, $a);
                        }
                    }
                }
            }

            return $contents;
        }
        $me = array_shift($dir);
        if (!isset($struc[$me])) {
            $struc[$me] = array();
        }
        $struc[$me] = $this->setupDirs($struc[$me], $dir, $contents);

        return $struc;
    }

    /**
     * Recursively add all the subdirectories of $contents to $dir without erasing anything in
     * $dir
     * @param array
     * @param array
     * @return array processed $dir
     */
    public function setDir($dir, $contents)
    {
        while (list($one, $two) = each($contents)) {
            if (isset($dir[$one])) {
                $dir[$one] = $this->setDir($dir[$one], $contents[$one]);
            } else {
                $dir[$one] = $two;
            }
        }

        return $dir;
    }

    /**
     * Sorting functions for the file list
     * @param string
     * @param string
     * @return int
     */
    private function sortfiles($a, $b)
    {
        return strnatcasecmp($a['file'], $b['file']);
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private function mystrucsort($a, $b)
    {
        if (is_numeric($a) && is_string($b)) {
            return 1;
        }
        if (is_numeric($b) && is_string($a)) {
            return -1;
        }
        if (is_numeric($a) && is_numeric($b)) {
            if ($a > $b) {
                return 1;
            }
            if ($a < $b) {
                return -1;
            }
            if ($a == $b) {
                return 0;
            }
        }

        return strnatcasecmp($a, $b);
    }
}
