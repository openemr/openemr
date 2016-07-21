<?php
/*
 *  $Id: fff99666d63c88940f9b305c2822856fa00ab5ce $
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
 * PhpDocumentor2 Task (http://www.phpdoc.org)
 * Based on the DocBlox Task
 *
 * @author    Michiel Rook <mrook@php.net>
 * @version   $Id: fff99666d63c88940f9b305c2822856fa00ab5ce $
 * @since     2.4.10
 * @package   phing.tasks.ext.phpdoc
 */
class PhpDocumentor2Task extends Task
{
    /**
     * List of filesets
     * @var FileSet[]
     */
    private $filesets = array();

    /**
     * Destination/target directory
     * @var PhingFile
     */
    private $destDir = null;

    /**
     * name of the template to use
     * @var string
     */
    private $template = "responsive-twig";

    /**
     * Title of the project
     * @var string
     */
    private $title = "API Documentation";

    /**
     * Name of default package
     * @var string
     */
    private $defaultPackageName = "Default";

    /**
     * Path to the phpDocumentor .phar
     * @var string
     */
    private $pharLocation = '';

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
     * Sets destination/target directory
     * @param PhingFile $destDir
     */
    public function setDestDir(PhingFile $destDir)
    {
        $this->destDir = $destDir;
    }

    /**
     * Convenience setter (@see setDestDir)
     * @param PhingFile $output
     */
    public function setOutput(PhingFile $output)
    {
        $this->destDir = $output;
    }

    /**
     * Sets the template to use
     * @param strings $template
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;
    }

    /**
     * Sets the title of the project
     * @param strings $title
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * Sets the default package name
     * @param string $defaultPackageName
     */
    public function setDefaultPackageName($defaultPackageName)
    {
        $this->defaultPackageName = (string) $defaultPackageName;
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * Forces phpDocumentor to be quiet
     * @deprecated
     * @param boolean $quiet
     */
    public function setQuiet($quiet)
    {
        $this->project->log(__CLASS__ . ": the 'quiet' option has been deprecated", Project::MSG_WARN);
    }

    /**
     * Task entry point
     * @see Task::main()
     */
    public function main()
    {
        if (empty($this->destDir)) {
            throw new BuildException("You must supply the 'destdir' attribute", $this->getLocation());
        }

        if (empty($this->filesets)) {
            throw new BuildException("You have not specified any files to include (<fileset>)", $this->getLocation());
        }

        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            throw new BuildException("The phpdocumentor2 task requires PHP 5.3+");
        }

        require_once 'phing/tasks/ext/phpdoc/PhpDocumentor2Wrapper.php';

        $wrapper = new PhpDocumentor2Wrapper();
        $wrapper->setProject($this->project);
        $wrapper->setFilesets($this->filesets);
        $wrapper->setDestDir($this->destDir);
        $wrapper->setTemplate($this->template);
        $wrapper->setTitle($this->title);
        $wrapper->setDefaultPackageName($this->defaultPackageName);
        $wrapper->setPharLocation($this->pharLocation);
        $wrapper->run();
    }
}
