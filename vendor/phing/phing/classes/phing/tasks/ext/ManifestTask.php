<?php
/**
 * $Id: b8d07ffcebc0e254a037a4351394cf6d124f8d97 $
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

require_once "phing/Task.php";
require_once 'phing/system/io/PhingFile.php';

/**
 * ManifestTask
 *
 * Generates a simple Manifest file with optional checksums.
 *
 *
 * Manifest schema:
 * ...
 * path/to/file     CHECKSUM    [CHECKSUM2]     [CHECKSUM3]
 * path/to/secondfile       CHECKSUM    [CHECKSUM2]     [CHECKSUM3]
 * ...
 *
 * Example usage:
 * <manifest checksum="crc32" file="${dir_build}/Manifest">
 *      <fileset refid="files_build" />
 * </manifest>
 *
 * <manifest checksum="md5,adler32,sha256" file="${dir_build}/Manifest">
 *      <fileset refid="files_build" />
 * </manifest>
 *
 *
 *
 * @author David Persson <davidpersson at qeweurope dot org>
 *
 * @package phing.tasks.ext
 *
 * @since 2.3.1
 */
class ManifestTask extends Task
{
    public $taskname = 'manifest';

    /**
     * Action
     *
     * "w" for reading in files from fileSet
     * and writing manifest
     *
     * or
     *
     * "r" for reading in files from fileSet
     * and checking against manifest
     *
     * @var string "r" or "w"
     */
    private $action = 'w';

    /**
     * The target file passed in the buildfile.
     */
    private $destFile = null;

    /**
     * Holds filesets
     *
     * @var array An Array of objects
     */
    private $filesets = array();

    /**
     * Enable/Disable checksuming or/and select algorithm
     * true defaults to md5
     * false disables checksuming
     * string "md5,sha256,..." enables generation of multiple checksums
     * string "sha256" generates sha256 checksum only
     *
     * @var bool|string
     */
    private $checksum = false;

    /**
     * A string used in hashing method
     *
     * @var string
     */
    private $salt = '';

    /**
     * Holds some data collected during runtime
     *
     * @var array
     */
    private $meta = array('totalFileCount' => 0, 'totalFileSize' => 0);

    /**
     * The setter for the attribute "file".
     * This is where the manifest will be written to/read from
     *
     * @param PhingFile $file Path to readable file
     *
     * @return void
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * The setter for the attribute "checksum"
     *
     * @param  mixed $mixed
     *
     * @return void
     */
    public function setChecksum($mixed)
    {
        if (is_string($mixed)) {
            $data = array(strtolower($mixed));

            if (strpos($data[0], ',')) {
                $data = explode(',', $mixed);
            }

            $this->checksum = $data;

        } elseif ($mixed === true) {
            $this->checksum = array('md5');
        }
    }

    /**
     * The setter for the optional attribute "salt"
     *
     * @param  string $string
     *
     * @return void
     */
    public function setSalt($string)
    {
        $this->salt = $string;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     *
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * The init method: Do init steps.
     *
     * {@inheritdoc}
     *
     * @internal nothing to do here
     */
    public function init()
    {
    }

    /**
     * Delegate the work.
     *
     * {@inheritdoc}
     */
    public function main()
    {
        $this->validateAttributes();

        if ($this->action == 'w') {
            $this->write();

        } elseif ($this->action == 'r') {
            $this->read();

        }
    }

    /**
     * Creates Manifest file
     * Writes to $this->file
     *
     * @throws BuildException
     */
    private function write()
    {
        $project = $this->getProject();

        if (!touch($this->file->getPath())) {
            throw new BuildException("Unable to write to " . $this->file->getPath() . ".");
        }

        $this->log("Writing to " . $this->file->__toString(), Project::MSG_INFO);

        if (is_array($this->checksum)) {
            $this->log("Using " . implode(', ', $this->checksum) . " for checksuming.", Project::MSG_INFO);
        }

        foreach ($this->filesets as $fs) {

            $dir = $fs->getDir($this->project)->getPath();

            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            foreach ($ds->getIncludedFiles() as $file_path) {
                $line = $file_path;
                if ($this->checksum) {
                    foreach ($this->checksum as $algo) {
                        if (!$hash = $this->hashFile($dir . '/' . $file_path, $algo)) {
                            throw new BuildException("Hashing $dir/$file_path with $algo failed!");
                        }

                        $line .= "\t" . $hash;
                    }
                }
                $line .= "\n";
                $manifest[] = $line;
                $this->log("Adding file " . $file_path, Project::MSG_VERBOSE);
                $this->meta['totalFileCount']++;
                $this->meta['totalFileSize'] += filesize($dir . '/' . $file_path);
            }

        }

        file_put_contents($this->file, $manifest);

        $this->log(
            "Done. Total files: " . $this->meta['totalFileCount'] . ". Total file size: " . $this->meta['totalFileSize'] . " bytes.",
            Project::MSG_INFO
        );
    }

    /**
     * @todo implement
     */
    private function read()
    {
        throw new BuildException("Checking against manifest not yet supported.");
    }

    /**
     * Wrapper method for hash generation
     * Automatically selects extension
     * Falls back to built-in functions
     *
     * @link  http://www.php.net/mhash
     * @link  http://www.php.net/hash
     *
     * @param  string $msg  The string that should be hashed
     * @param  string $algo Algorithm
     *
     * @return mixed  String on success, false if $algo is not available
     */
    private function hash($msg, $algo)
    {
        if (extension_loaded('hash')) {
            $algo = strtolower($algo);

            if (in_array($algo, hash_algos())) {
                return hash($algo, $this->salt . $msg);
            }

        }

        if (extension_loaded('mhash')) {
            $algo = strtoupper($algo);

            if (defined('MHASH_' . $algo)) {
                return mhash('MHASH_' . $algo, $this->salt . $msg);

            }
        }

        switch (strtolower($algo)) {
            case 'md5':
                return md5($this->salt . $msg);
            case 'crc32':
                return abs(crc32($this->salt . $msg));
        }

        return false;
    }

    /**
     * Hash a file's contents
     *
     * @param  string $file
     * @param  string $algo
     *
     * @return mixed  String on success, false if $algo is not available
     */
    private function hashFile($file, $algo)
    {
        if (!file_exists($file)) {
            return false;
        }

        $msg = file_get_contents($file);

        return $this->hash($msg, $algo);
    }

    /**
     * Validates attributes coming in from XML
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function validateAttributes()
    {
        if ($this->action != 'r' && $this->action != 'w') {
            throw new BuildException("'action' attribute has non valid value. Use 'r' or 'w'");
        }

        if (empty($this->salt)) {
            $this->log("No salt provided. Specify one with the 'salt' attribute.", Project::MSG_WARN);
        }

        if (is_null($this->file) && count($this->filesets) === 0) {
            throw new BuildException("Specify at least sources and destination - a file or a fileset.");
        }

        if (!is_null($this->file) && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Destination file cannot be a directory.");
        }
    }
}
