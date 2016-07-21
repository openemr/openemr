<?php
/*
 * $Id: 938e80d47e941acc8badd2686d960655f025ac3f $
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
 * LoadFileTask
 *
 * Loads a (text) file and stores the contents in a property.
 * Supports filterchains.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id: 938e80d47e941acc8badd2686d960655f025ac3f $
 * @package phing.tasks.ext
 */
class LoadFileTask extends Task
{
    /**
     * File to read
     * @var PhingFile file
     */
    private $file;

    /**
     * Property to be set
     * @var string $property
     */
    private $property;

    /**
     * Array of FilterChain objects
     * @var FilterChain[]
     */
    private $filterChains = array();

    /**
     * Set file to read
     * @param PhingFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Convenience setter to maintain Ant compatibility (@see setFile())
     * @param $srcFile
     * @internal param PhingFile $file
     */
    public function setSrcFile($srcFile)
    {
        $this->file = $srcFile;
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
     * Creates a filterchain
     *
     * @return object The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }

    /**
     * Main method
     *
     * @return void
     * @throws BuildException
     */
    public function main()
    {
        if (empty($this->file)) {
            throw new BuildException("Attribute 'file' required", $this->getLocation());
        }

        if (empty($this->property)) {
            throw new BuildException("Attribute 'property' required", $this->getLocation());
        }

        // read file (through filterchains)
        $contents = "";

        $reader = FileUtils::getChainedReader(new FileReader($this->file), $this->filterChains, $this->project);
        while (-1 !== ($buffer = $reader->read())) {
            $contents .= $buffer;
        }
        $reader->close();

        // publish as property
        $this->project->setProperty($this->property, $contents);
    }
}
