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

require_once 'phing/Task.php';

/**
 * ReplaceRegExp is a directory based task for replacing the occurrence of a
 * given regular expression with a substitution pattern in a selected file or
 * set of files.
 *
 * <code>
 * <replaceregexp file="${src}/build.properties"
 *                        match="OldProperty=(.*)"
 *                        replace="NewProperty=\1"
 *                        byline="true"/>
 * </code>
 *
 * @author    Jonathan Bond-Caron <jbondc@openmv.com>
 *
 * @package   phing.tasks.system
 *
 * @link      http://ant.apache.org/manual/OptionalTasks/replaceregexp.html
 */
class ReplaceRegexpTask extends Task
{
    /** Single file to process. */
    private $file;

    /**
     * Any filesets that should be processed.
     *
     * @var array $filesets
     */
    private $filesets = array();

    /**
     * Regular expression
     *
     * @var RegularExpression
     */
    private $_regexp;

    /**
     * File to apply regexp on
     *
     * @param PhingFile $path
     *
     * @return void
     */
    public function setFile(PhingFile $path)
    {
        $this->file = $path;
    }

    /**
     * Sets the regexp match pattern
     *
     * @param string $regexp
     *
     * @return void
     */
    public function setMatch($regexp)
    {
        $this->_regexp->setPattern($regexp);
    }

    /**
     * @see setMatch()
     *
     * @param $regexp
     *
     * @return void
     */
    public function setPattern($regexp)
    {
        $this->setMatch($regexp);
    }

    /**
     * Sets the replacement string
     *
     * @param string $string
     *
     * @return void
     */
    public function setReplace($string)
    {
        $this->_regexp->setReplace($string);
    }

    /**
     * Sets the regexp flags
     *
     * @param string $flags
     *
     * @return void
     *
     * todo ... `$this->_regexp->setFlags( $flags );`
     */
    public function setFlags($flags)
    {
    }

    /**
     * Match only per line
     *
     * @param bool $yesNo
     *
     * @return void
     */
    public function setByline($yesNo)
    {
        // TODO... $this->_regexp->
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
     * {@inheritdoc}
     *
     * @return void
     */
    public function init()
    {
        $this->_regexp = new RegularExpression();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws BuildException
     */
    public function main()
    {
        if ($this->file === null && empty($this->filesets)) {
            throw new BuildException("You must specify a file or fileset(s) for the <ReplaceRegexp> task.");
        }

        // compile a list of all files to modify, both file attrib and fileset elements
        // can be used.
        $files = array();

        if ($this->file !== null) {
            $files[] = $this->file;
        }

        if (!empty($this->filesets)) {
            $filenames = array();
            foreach ($this->filesets as $fs) {
                try {
                    $ds = $fs->getDirectoryScanner($this->project);
                    $filenames = $ds->getIncludedFiles(); // get included filenames
                    $dir = $fs->getDir($this->project);
                    foreach ($filenames as $fname) {
                        $files[] = new PhingFile($dir, $fname);
                    }
                } catch (BuildException $be) {
                    $this->log($be->getMessage(), Project::MSG_WARN);
                }
            }
        }

        $this->log("Applying Regexp processing to " . count($files) . " files.");

        // These "slots" allow filters to retrieve information about the currently-being-process files
        $slot = $this->getRegisterSlot("currentFile");
        $basenameSlot = $this->getRegisterSlot("currentFile.basename");

        $filter = new FilterChain($this->project);

        $r = new ReplaceRegexp();
        $r->setRegexps(array($this->_regexp));

        $filter->addReplaceRegexp($r);
        $filters = array($filter);

        foreach ($files as $file) {
            // set the register slots

            $slot->setValue($file->getPath());
            $basenameSlot->setValue($file->getName());

            // 1) read contents of file, pulling through any filters
            $in = null;
            try {
                $contents = "";
                $in = FileUtils::getChainedReader(new FileReader($file), $filters, $this->project);
                while (-1 !== ($buffer = $in->read())) {
                    $contents .= $buffer;
                }
                $in->close();
            } catch (Exception $e) {
                if ($in) {
                    $in->close();
                }
                $this->log("Error reading file: " . $e->getMessage(), Project::MSG_WARN);
            }

            try {
                // now create a FileWriter w/ the same file, and write to the file
                $out = new FileWriter($file);
                $out->write($contents);
                $out->close();
                $this->log("Applying regexp processing to " . $file->getPath(), Project::MSG_VERBOSE);
            } catch (Exception $e) {
                if ($out) {
                    $out->close();
                }
                $this->log("Error writing file back: " . $e->getMessage(), Project::MSG_WARN);
            }

        }

    }
}
