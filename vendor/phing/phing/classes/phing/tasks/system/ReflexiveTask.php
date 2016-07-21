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
 * This task is for using filter chains to make changes to files and overwrite the original files.
 *
 * This task was created to serve the need for "cleanup" tasks -- e.g. a ReplaceRegexp task or strip task
 * being used to modify files and then overwrite the modified files.  In many (most?) cases you probably
 * should just use a copy task  to preserve the original source files, but this task supports situations
 * where there is no src vs. build directory, and modifying source files is actually desired.
 *
 * <code>
 *    <reflexive>
 *        <fileset dir=".">
 *            <include pattern="*.html">
 *        </fileset>
 *        <filterchain>
 *            <replaceregexp>
 *                <regexp pattern="\n\r" replace="\n"/>
 *            </replaceregexp>
 *        </filterchain>
 *    </reflexive>
 * </code>
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 *
 * @package   phing.tasks.system
 */
class ReflexiveTask extends Task
{

    /** Single file to process. */
    private $file;

    /**
     * Any filesets that should be processed.
     *
     * @var FileSet[]
     */
    private $filesets = array();

    /**
     * Any filters to be applied before append happens.
     *
     * @var FilterChain[]
     */
    private $filterChains = array();

    /** Alias for setFrom()
     * @param PhingFile $f
     */
    public function setFile(PhingFile $f)
    {
        $this->file = $f;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Creates a filterchain
     *
     * @return FilterChain The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }

    /** Append the file(s). */
    public function main()
    {

        if ($this->file === null && empty($this->filesets)) {
            throw new BuildException("You must specify a file or fileset(s) for the <reflexive> task.");
        }

        // compile a list of all files to modify, both file attrib and fileset elements
        // can be used.

        $files = array();

        if ($this->file !== null) {
            $files[] = $this->file;
        }

        if (!empty($this->filesets)) {
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

        $this->log("Applying reflexive processing to " . count($files) . " files.");

        // These "slots" allow filters to retrieve information about the currently-being-process files
        $slot = $this->getRegisterSlot("currentFile");
        $basenameSlot = $this->getRegisterSlot("currentFile.basename");

        foreach ($files as $file) {
            // set the register slots

            $slot->setValue($file->getPath());
            $basenameSlot->setValue($file->getName());

            // 1) read contents of file, pulling through any filters
            $in = null;
            try {
                $contents = "";
                $in = FileUtils::getChainedReader(new FileReader($file), $this->filterChains, $this->project);
                while (-1 !== ($buffer = $in->read())) {
                    $contents .= $buffer;
                }
                $in->close();
            } catch (Exception $e) {
                if ($in) {
                    $in->close();
                }
                $this->log("Erorr reading file: " . $e->getMessage(), Project::MSG_WARN);
            }

            try {
                // now create a FileWriter w/ the same file, and write to the file
                $out = new FileWriter($file);
                $out->write($contents);
                $out->close();
                $this->log("Applying reflexive processing to " . $file->getPath(), Project::MSG_VERBOSE);
            } catch (Exception $e) {
                if ($out) {
                    $out->close();
                }
                $this->log("Error writing file back: " . $e->getMessage(), Project::MSG_WARN);
            }

        }
    }
}
