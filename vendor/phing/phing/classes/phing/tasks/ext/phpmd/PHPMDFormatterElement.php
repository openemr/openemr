<?php
/**
 * $Id: fa23de508582a5bc1d36fab6153d64df333099c6 $
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

require_once 'phing/system/io/PhingFile.php';

/**
 * A wrapper for the implementations of PHPMDResultFormatter.
 *
 * @package phing.tasks.ext.phpmd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: fa23de508582a5bc1d36fab6153d64df333099c6 $
 * @since   2.4.1
 */
class PHPMDFormatterElement
{
    /**
     * @var PHPMDResultFormatter
     */
    protected $formatter = null;

    /**
     * The type of the formatter.
     *
     * @var string
     */
    protected $type = "";

    /**
     * @var string
     */
    protected $className = "";

    /**
     * Whether to use file (or write output to phing log).
     *
     * @var boolean
     */
    protected $useFile = true;

    /**
     * Output file for formatter.
     *
     * @var PhingFile
     */
    protected $outfile = null;

    /**
     * Sets the formatter type.
     *
     * @param string $type Type of the formatter
     *
     * @throws BuildException
     */
    public function setType($type)
    {
        $this->type = $type;
        switch ($this->type) {
            case 'xml':
                $this->className = 'XMLRenderer';
                break;

            case 'html':
                $this->className = 'HTMLRenderer';
                break;

            case 'text':
                $this->className = 'TextRenderer';
                break;

            default:
                throw new BuildException('Formatter "' . $this->type . '" not implemented');
        }
    }

    /**
     * Get the formatter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set whether to write formatter results to file or not.
     *
     * @param boolean $useFile True or false.
     */
    public function setUseFile($useFile)
    {
        $this->useFile = StringHelper::booleanValue($useFile);
    }

    /**
     * Return whether to write formatter results to file or not.
     *
     * @return boolean
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * Sets the output file for the formatter results.
     *
     * @param PhingFile $outfile The output file
     */
    public function setOutfile(PhingFile $outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Get the output file.
     *
     * @return PhingFile
     */
    public function getOutfile()
    {
        return $this->outfile;
    }

    /**
     * Creates a report renderer instance based on the formatter type.
     *
     * @return PHP_PMD_AbstractRenderer
     * @throws BuildException           When the specified renderer does not exist.
     */
    public function getRenderer()
    {
        if (!class_exists('\\PHPMD\\Writer\\StreamWriter')) {
            $renderClass = 'PHP_PMD_RENDERER_' . $this->className;
            $writerClass = 'PHP_PMD_Writer_Stream';
            include_once 'PHP/PMD/Renderer/' . $this->className . '.php';
            include_once 'PHP/PMD/Writer/Stream.php';
        } else {
            $renderClass = 'PHPMD\Renderer\\' . $this->className;
            $writerClass = '\PHPMD\Writer\StreamWriter';
        }

        $renderer = new $renderClass();

        // Create a report stream
        if ($this->getUseFile() === false || $this->getOutfile() === null) {
            $stream = STDOUT;
        } else {
            $stream = fopen($this->getOutfile()->getAbsoluteFile(), 'wb');
        }

        $renderer->setWriter(new $writerClass($stream));

        return $renderer;
    }
}
