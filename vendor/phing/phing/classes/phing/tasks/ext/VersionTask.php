<?php
/*
 * $Id: 3e7c572ae9bdb739905b54e831e3985194d65071 $
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
 * VersionTask
 *
 * Increments a three-part version number from a given file
 * and writes it back to the file.
 * Incrementing is based on given releasetype, which can be one
 * of Major, Minor and Bugfix.
 * Resulting version number is also published under supplied property.
 *
 * @author      Mike Wittje <mw@mike.wittje.de>
 * @version     $Id: 3e7c572ae9bdb739905b54e831e3985194d65071 $ $Rev $Id: 3e7c572ae9bdb739905b54e831e3985194d65071 $ $Author$
 * @package     phing.tasks.ext
 */
class VersionTask extends Task
{
    /**
     * Property for Releasetype
     * @var string $releasetype
     */
    private $releasetype;

    /**
     * Property for File
     * @var PhingFile file
     */
    private $file;

    /**
     * Property to be set
     * @var string $property
     */
    private $property;

    /* Allowed Releastypes */
    const RELEASETYPE_MAJOR = 'MAJOR';
    const RELEASETYPE_MINOR = 'MINOR';
    const RELEASETYPE_BUGFIX = 'BUGFIX';

    /**
     * Set Property for Releasetype (Minor, Major, Bugfix)
     * @param string $releasetype
     */
    public function setReleasetype($releasetype)
    {
        $this->releasetype = strtoupper($releasetype);
    }

    /**
     * Set Property for File containing versioninformation
     * @param PhingFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Set name of property to be set
     * @param $property
     * @return void
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Main-Method for the Task
     *
     * @return void
     * @throws BuildException
     */
    public function main()
    {
        // check supplied attributes
        $this->checkReleasetype();
        $this->checkFile();
        $this->checkProperty();

        // read file
        $filecontent = trim(file_get_contents($this->file));

        // get new version
        $newVersion = $this->getVersion($filecontent);

        // write new Version to file
        file_put_contents($this->file, $newVersion . "\n");

        // publish new version number as property
        $this->project->setProperty($this->property, $newVersion);

    }

    /**
     * Returns new version number corresponding to Release type
     *
     * @param  string $filecontent
     * @return string
     */
    private function getVersion($filecontent)
    {
        // init
        $newVersion = '';

        // Extract version
        list($major, $minor, $bugfix) = explode(".", $filecontent);

        // Return new version number
        switch ($this->releasetype) {
            case self::RELEASETYPE_MAJOR:
                $newVersion = sprintf(
                    "%d.%d.%d",
                    ++$major,
                    0,
                    0
                );
                break;

            case self::RELEASETYPE_MINOR:
                $newVersion = sprintf(
                    "%d.%d.%d",
                    $major,
                    ++$minor,
                    0
                );
                break;

            case self::RELEASETYPE_BUGFIX:
                $newVersion = sprintf(
                    "%d.%d.%d",
                    $major,
                    $minor,
                    ++$bugfix
                );
                break;
        }

        return $newVersion;
    }

    /**
     * checks releasetype attribute
     * @return void
     * @throws BuildException
     */
    private function checkReleasetype()
    {
        // check Releasetype
        if (is_null($this->releasetype)) {
            throw new BuildException('releasetype attribute is required', $this->location);
        }
        // known releasetypes
        $releaseTypes = array(
            self::RELEASETYPE_MAJOR,
            self::RELEASETYPE_MINOR,
            self::RELEASETYPE_BUGFIX
        );

        if (!in_array($this->releasetype, $releaseTypes)) {
            throw new BuildException(sprintf(
                'Unknown Releasetype %s..Must be one of Major, Minor or Bugfix',
                $this->releasetype
            ), $this->location);
        }
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
            throw new BuildException('You must specify a file containing the version number', $this->location);
        }

        $content = file_get_contents($this->file);
        if (strlen($content) == 0) {
            throw new BuildException(sprintf('Supplied file %s is empty', $this->file), $this->location);
        }

        // check for three-part number
        $split = explode('.', $content);
        if (count($split) !== 3) {
            throw new BuildException('Unknown version number format', $this->location);
        }

    }

    /**
     * checks property attribute
     * @return void
     * @throws BuildException
     */
    private function checkProperty()
    {
        if (is_null($this->property) ||
            strlen($this->property) === 0
        ) {
            throw new BuildException('Property for publishing version number is not set', $this->location);
        }
    }
}
