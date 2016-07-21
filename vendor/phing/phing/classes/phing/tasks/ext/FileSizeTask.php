<?php
/*
 * $Id: d4b97a2e512ffe10394e203d6a94c498e11ca986 $
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
 * fileHash
 *
 * Calculate either MD5 or SHA hash value of a specified file and retun the
 * value in a property
 *
 * @author      Johan Persson <johan162@gmail.com>
 * @version     $Id: d4b97a2e512ffe10394e203d6a94c498e11ca986 $
 * @package     phing.tasks.ext
 */
class FileSizeTask extends Task
{
    /**
     * Property for File
     * @var PhingFile file
     */
    private $file;

    /**
     * Property where the file size will be stored
     * @var string $property
     */
    private $propertyName = "filesize";

    /**
     * Which file to calculate the file size of
     * @param PhingFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Set the name of the property to store the file size
     * @param $property
     * @return void
     */
    public function setPropertyName($property)
    {
        $this->propertyName = $property;
    }

    /**
     * Main-Method for the Task
     *
     * @return void
     * @throws BuildException
     */
    public function main()
    {
        $this->checkFile();
        $this->checkPropertyName();

        $size = filesize($this->file);

        if ($size === false) {
            throw new BuildException(sprintf('[FileSize] Cannot determine size of file: %s', $this->file));

        }

        // publish hash value
        $this->project->setProperty($this->propertyName, $size);

    }

    /**
     * checks file attribute
     * @return void
     * @throws BuildException
     */
    private function checkFile()
    {
        // check File
        if ($this->file === null ||
            strlen($this->file) == 0
        ) {
            throw new BuildException('[FileSize] You must specify an input file.', $this->file);
        }

        if (!is_readable($this->file)) {
            throw new BuildException(sprintf(
                '[FileSize] Input file does not exist or is not readable: %s',
                $this->file
            ));
        }

    }

    /**
     * checks property attribute
     * @return void
     * @throws BuildException
     */
    private function checkPropertyName()
    {
        if (is_null($this->propertyName) ||
            strlen($this->propertyName) === 0
        ) {
            throw new BuildException('[FileSize] Property name for publishing file size is not set');
        }
    }
}
