<?php
/**
 * $Id: c430c57cb2db4bff221d0bcf96ed4396b738da6d $
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
 * A wrapper for the implementations of PHPCPDResultFormatter.
 *
 * @package phing.tasks.ext.phpcpd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: c430c57cb2db4bff221d0bcf96ed4396b738da6d $
 */
class PHPCPDFormatterElement
{
    /**
     * The report result formatter.
     *
     * @var PHPCPDResultFormatter
     */
    protected $formatter = null;

    /**
     * The type of the formatter.
     *
     * @var string
     */
    protected $type = '';

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
     * The parent task
     *
     * @var PHPCPDTask
     */
    private $parentTask;

    /**
     * Construct a new PHPCPDFormatterElement with parent task.
     *
     * @param PHPCPDTask $parentTask
     */
    public function __construct(PHPCPDTask $parentTask)
    {
        $this->parentTask = $parentTask;
    }

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
            case 'pmd':
                if ($this->useFile === false) {
                    throw new BuildException('Formatter "' . $this->type . '" can only print the result to an file');
                }

                include_once 'phing/tasks/ext/phpcpd/formatter/PMDPHPCPDResultFormatter.php';

                $this->formatter = new PMDPHPCPDResultFormatter();
                break;

            case 'default':
                include_once 'phing/tasks/ext/phpcpd/formatter/DefaultPHPCPDResultFormatter.php';

                $this->formatter = new DefaultPHPCPDResultFormatter();
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
     * Returns the report formatter.
     *
     * @return PHPCPDResultFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }
}
