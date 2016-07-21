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

include_once 'phing/Task.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/BuildException.php';

/**
 * This task sets a property to the name of a temporary file.
 * Unlike {@link PhingFile::createTempFile()}, this task does not (by default) actually create the
 * temporary file, but it does guarantee that the file did not
 * exist when the task was executed.
 *
 * Examples:
 *
 * `<tempfile property="temp.file" />`
 *
 * create a temporary file
 *
 * `<tempfile property="temp.file" suffix=".xml" />`
 *
 * create a temporary file with the .xml suffix.
 *
 * `<tempfile property="temp.file" destDir="build"/>`
 *
 * create a temp file in the build subdir
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class TempFile extends Task
{
    /**
     * Name of property to set.
     */
    private $property = '';

    /**
     * Directory to create the file in. Can be null.
     */
    private $destDir;

    /**
     * Prefix for the file.
     */
    private $prefix;

    /**
     * Suffix for the file.
     */
    private $suffix = '';

    /** deleteOnExit flag */
    private $deleteOnExit;

    /** createFile flag */
    private $createFile;

    /**
     * Sets the property you wish to assign the temporary file to.
     *
     * @param string $property The property to set
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Sets the destination directory. If not set,
     * the basedir directory is used instead.
     *
     * @param string|PhingFile $destDir The new destDir value
     */
    public function setDestDir($destDir)
    {
        if ($destDir instanceof PhingFile) {
            $this->destDir = $destDir;
        } else  {
            $this->destDir = new PhingFile($destDir);
        }
    }


    /**
     * Sets the optional prefix string for the temp file.
     *
     * @param string $prefix string to prepend to generated string
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }


    /**
     * Sets the optional suffix string for the temp file.
     *
     * @param string $suffix suffix including any "." , e.g ".xml"
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * Set whether the tempfile created by this task should be set
     * for deletion on normal VM exit.
     * @param boolean $deleteOnExit boolean flag.
     */
    public function setDeleteOnExit($deleteOnExit)
    {
        $this->deleteOnExit = $deleteOnExit;
    }

    /**
     * Learn whether deleteOnExit is set for this tempfile task.
     * @return boolean deleteOnExit flag.
     */
    public function isDeleteOnExit()
    {
        return $this->deleteOnExit;
    }

    /**
     * If set the file is actually created, if not just a name is created.
     * @param boolean $createFile boolean flag.
     */
    public function setCreateFile($createFile)
    {
        $this->createFile = $createFile;
    }

    /**
     * Learn whether createFile flag is set for this tempFile task.
     * @return boolean the createFile flag.
     */
    public function isCreateFile()
    {
        return $this->createFile;
    }

    /**
     * Creates the temporary file.
     *
     * @throws BuildException if something goes wrong with the build
     */
    public function main()
    {
        if ($this->property === '') {
            throw new BuildException('no property specified');
        }
        if ($this->destDir === null) {
            $this->destDir = $this->getProject()->resolveFile('.');
        }
        $fu = new FileUtils;
        $tmpFile = $fu->createTempFile(
            $this->prefix,
            $this->suffix,
            $this->destDir,
            $this->deleteOnExit,
            $this->createFile
        );
        $this->getProject()->setNewProperty($this->property, $tmpFile->toString());
    }
}
