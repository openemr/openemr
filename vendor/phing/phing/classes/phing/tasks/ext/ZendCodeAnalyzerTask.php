<?php
/*
 *  $Id: d1bcb58746c4f9bb6e63c43e61c9074f511ad456 $
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
 * ZendCodeAnalyzerTask analyze PHP source code using the ZendCodeAnalyzer included in Zend Studio 5.1
 *
 * Available warnings:
 * <b>zend-error</b> - %s(line %d): %s
 * <b>oneline-comment</b> - One-line comment ends with  tag.
 * <b>bool-assign</b> - Assignment seen where boolean expression is expected. Did you mean '==' instead of '='?
 * <b>bool-print</b> - Print statement used when boolean expression is expected.
 * <b>bool-array</b> - Array used when boolean expression is expected.
 * <b>bool-object</b> - Object used when boolean expression is expected.
 * <b>call-time-ref</b> - Call-time reference is deprecated. Define function as accepting parameter by reference instead.
 * <b>if-if-else</b> - In if-if-else construction else relates to the closest if. Use braces to make the code clearer.
 * <b>define-params</b> - define() requires two or three parameters.
 * <b>define-const</b> - First parameter for define() should be string. Maybe you forgot quotes?
 * <b>break-var</b> - Break/continue with variable is dangerous - break level can be out of scope.
 * <b>break-depth</b> - Break/continue with depth more than current nesting level.
 * <b>var-once</b> - Variable '%s' encountered only once. May be a typo?
 * <b>var-arg-unused</b> - Function argument '%s' is never used.
 * <b>var-global-unused</b> - Global variable '%s' is defined but never used.
 * <b>var-use-before-def</b> - Variable '%s' is used before it was assigned.
 * <b>var-use-before-def-global</b> - Global variable '%s' is used without being assigned. You are probably relying on register_globals feature of PHP. Note that this feature is off by default.
 * <b>var-no-global</b> - PHP global variable '%s' is used as local. Maybe you wanted to define '%s' as global?
 * <b>var-value-unused</b> - Value assigned to variable '%s' is never used
 * <b>var-ref-notmodified</b> - Function parameter '%s' is passed by reference but never modified. Consider passing by value.
 * <b>return-empty-val</b> - Function '%s' has both empty return and return with value.
 * <b>return-empty-used</b> - Function '%s' has empty return but return value is used.
 * <b>return-noref</b> - Function '%s' returns reference but the value is not assigned by reference. Maybe you meant '=&' instead of '='?
 * <b>return-end-used</b> - Control reaches the end of function '%s'(file %s, line %d) but return value is used.
 * <b>sprintf-miss-args</b> - Missing arguments for sprintf: format reqires %d arguments but %d are supplied.
 * <b>sprintf-extra-args</b> - Extra arguments for sprintf: format reqires %d arguments but %d are supplied.
 * <b>unreach-code</b> - Unreachable code in function '%s'.
 * <b>include-var</b> - include/require with user-accessible variable can be dangerous. Consider using constant instead.
 * <b>non-object</b> - Variable '%s' used as object, but has different type.
 * <b>bad-escape</b> - Bad escape sequence: \%c, did you mean \\%c?
 * <b>empty-cond</b> - Condition without a body
 * <b>expr-unused</b> - Expression result is never used
 *
 * @author   Knut Urdalen <knut.urdalen@gmail.com>
 * @version  $Id: d1bcb58746c4f9bb6e63c43e61c9074f511ad456 $
 * @package  phing.tasks.ext
 */
class ZendCodeAnalyzerTask extends Task
{
    protected $analyzerPath = ""; // Path to ZendCodeAnalyzer binary
    protected $file = ""; // the source file (from xml attribute)
    protected $filesets = array(); // all fileset objects assigned to this task
    protected $counter = 0;
    protected $disable = array();
    protected $enable = array();

    private $haltonwarning = false;

    /**
     * File to be analyzed
     *
     * @param PhingFile $file
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * Path to ZendCodeAnalyzer binary
     *
     * @param string $analyzerPath
     */
    public function setAnalyzerPath($analyzerPath)
    {
        $this->analyzerPath = $analyzerPath;
    }

    /**
     * Disable warning levels. Separate warning levels with ','
     *
     * @param string $disable
     */
    public function setDisable($disable)
    {
        $this->disable = explode(",", $disable);
    }

    /**
     * Enable warning levels. Separate warning levels with ','
     *
     * @param string $enable
     */
    public function setEnable($enable)
    {
        $this->enable = explode(",", $enable);
    }

    /**
     * Sets the haltonwarning flag
     * @param boolean $value
     */
    public function setHaltonwarning($value)
    {
        $this->haltonwarning = $value;
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
     * Analyze against PhingFile or a FileSet
     */
    public function main()
    {
        if (!isset($this->analyzerPath)) {
            throw new BuildException("Missing attribute 'analyzerPath'");
        }

        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if ($this->file instanceof PhingFile) {
            $this->analyze($this->file->getPath());
        } else { // process filesets
            $project = $this->getProject();

            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getPath();

                foreach ($files as $file) {
                    $this->analyze($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        $this->log("Number of findings: " . $this->counter, Project::MSG_INFO);
    }

    /**
     * Analyze file
     *
     * @param  string $file
     * @throws BuildException
     * @return void
     */
    protected function analyze($file)
    {
        if (file_exists($file)) {
            if (is_readable($file)) {
                // Construct shell command
                $cmd = $this->analyzerPath . " ";

                foreach ($this->enable as $enable) { // Enable warning levels
                    $cmd .= " --enable $enable ";
                }

                foreach ($this->disable as $disable) { // Disable warning levels
                    $cmd .= " --disable $disable ";
                }

                $cmd .= "$file 2>&1";

                // Execute command
                $result = shell_exec($cmd);
                $result = explode("\n", $result);

                for ($i = 2, $size = count($result); $i < ($size - 1); $i++) {
                    $this->counter++;
                    $this->log($result[$i], Project::MSG_WARN);
                }

                $total = count($result) - 3;

                if ($total > 0 && $this->haltonwarning) {
                    throw new BuildException('zendcodeanalyzer detected ' . $total . ' warning' . ($total > 1 ? 's' : '') . ' in ' . $file);
                }
            } else {
                throw new BuildException('Permission denied: ' . $file);
            }
        } else {
            throw new BuildException('File not found: ' . $file);
        }
    }
}
