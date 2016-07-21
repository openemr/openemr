<?php
/**
 * $Id: f06dbbcba80e0b0ea474734e7e0dbc439d957479 $
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
require_once 'phing/tasks/ext/pdo/PlainPDOResultFormatter.php';
require_once 'phing/tasks/ext/pdo/XMLPDOResultFormatter.php';
require_once 'phing/util/LogWriter.php';

/**
 * A class to represent the nested <formatter> element for PDO SQL results.
 *
 * This class is inspired by the similarly-named class in the PHPUnit tasks.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.ext.pdo
 * @since 2.3.0
 */
class PDOSQLExecFormatterElement
{
    /**
     * @var PDOResultFormatter
     */
    private $formatter;

    /**
     * The type of the formatter (used for built-in formatter classes).
     * @var string
     */
    private $type = "";

    /**
     * Whether to use file (or write output to phing log).
     * @var boolean
     */
    private $useFile = true;

    /**
     * Output file for formatter.
     * @var PhingFile
     */
    private $outfile;

    /**
     * Print header columns.
     * @var boolean
     */
    private $showheaders = true;

    /**
     * Whether to format XML output.
     * @var boolean
     */
    private $formatoutput = true;

    /**
     * Encoding for XML output.
     * @var string
     */
    private $encoding;

    /**
     * Column delimiter.
     * Defaults to ','
     * @var string
     */
    private $coldelimiter = ",";

    /**
     * Row delimiter.
     * Defaults to PHP_EOL.
     * @var string
     */
    private $rowdelimiter = PHP_EOL;

    /**
     * Append to an existing file or overwrite it?
     * @var boolean
     */
    private $append = false;

    /**
     * Parameters for a custom formatter.
     * @var array Parameter[]
     */
    private $formatterParams = array();

    /**
     * @var PDOSQLExecTask
     */
    private $parentTask;

    /**
     * Construct a new PDOSQLExecFormatterElement with parent task.
     * @param PDOSQLExecTask $parentTask
     */
    public function __construct(PDOSQLExecTask $parentTask)
    {
        $this->parentTask = $parentTask;
    }

    /**
     * Supports nested <param> element (for custom formatter classes).
     * @return Parameter
     */
    public function createParam()
    {
        $num = array_push($this->parameters, new Parameter());

        return $this->parameters[$num - 1];
    }

    /**
     * Gets a configured output writer.
     * @return Writer
     */
    private function getOutputWriter()
    {
        if ($this->useFile) {
            $of = $this->getOutfile();
            if (!$of) {
                $of = new PhingFile($this->formatter->getPreferredOutfile());
            }

            return new FileWriter($of, $this->append);
        } else {
            return $this->getDefaultOutput();
        }
    }

    /**
     * Configures wrapped formatter class with any attributes on this element.
     */
    public function prepare()
    {

        if (!$this->formatter) {
            throw new BuildException("No formatter specified (use type or classname attribute)", $this->getLocation());
        }

        $out = $this->getOutputWriter();

        $this->parentTask->log("Setting output writer to: " . get_class($out), Project::MSG_VERBOSE);
        $this->formatter->setOutput($out);

        if ($this->formatter instanceof PlainPDOResultFormatter) {
            // set any options that apply to the plain formatter
            $this->formatter->setShowheaders($this->showheaders);
            $this->formatter->setRowdelim($this->rowdelimiter);
            $this->formatter->setColdelim($this->coldelimiter);
        } elseif ($this->formatter instanceof XMLPDOResultFormatter) {
            // set any options that apply to the xml formatter
            $this->formatter->setEncoding($this->encoding);
            $this->formatter->setFormatOutput($this->formatoutput);
        }

        foreach ($this->formatterParams as $param) {
            $param = new Parameter();
            $method = 'set' . $param->getName();
            if (!method_exists($this->formatter, $param->getName())) {
                throw new BuildException("Formatter " . get_class(
                        $this->formatter
                    ) . " does not have a $method method.", $this->getLocation());
            }
            call_user_func(array($this->formatter, $method), $param->getValue());
        }
    }

    /**
     * Sets the formatter type.
     * @param string $type
     * @throws BuildException
     */
    public function setType($type)
    {
        $this->type = $type;
        if ($this->type == "xml") {
            $this->formatter = new XMLPDOResultFormatter();
        } elseif ($this->type == "plain") {
            $this->formatter = new PlainPDOResultFormatter();
        } else {
            throw new BuildException("Formatter '" . $this->type . "' not implemented");
        }
    }

    /**
     * Set classname for a custom formatter (must extend PDOResultFormatter).
     * @param string $className
     */
    public function setClassName($className)
    {
        $classNameNoDot = Phing::import($className);
        $this->formatter = new $classNameNoDot();
    }

    /**
     * Set whether to write formatter results to file.
     * @param boolean $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = (boolean) $useFile;
    }

    /**
     * Return whether to write formatter results to file.
     * @return boolean
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * Sets the output file for the formatter results.
     * @param PhingFile $outfile
     * @internal param PhingFile $outFile
     */
    public function setOutfile(PhingFile $outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Get the output file.
     * @return PhingFile
     */
    public function getOutfile()
    {
        return $this->outfile;
        /*
        } else {
            return new PhingFile($this->formatter->getPreferredOutfile());
        }*/
    }

    /**
     * whether output should be appended to or overwrite
     * an existing file.  Defaults to false.
     * @param boolean $append
     */
    public function setAppend($append)
    {
        $this->append = (boolean) $append;
    }

    /**
     * Whether output should be appended to file.
     * @return boolean
     */
    public function getAppend()
    {
        return $this->append;
    }

    /**
     * Print headers for result sets from the
     * statements; optional, default true.
     * @param boolean $showheaders
     */
    public function setShowheaders($showheaders)
    {
        $this->showheaders = (boolean) $showheaders;
    }

    /**
     * Sets the column delimiter.
     * @param string $v
     */
    public function setColdelim($v)
    {
        $this->coldelimiter = $v;
    }

    /**
     * Sets the row delimiter.
     * @param string $v
     */
    public function setRowdelim($v)
    {
        $this->rowdelimiter = $v;
    }

    /**
     * Set the DOM document encoding.
     * @param string $v
     */
    public function setEncoding($v)
    {
        $this->encoding = $v;
    }

    /**
     * @param boolean $v
     */
    public function setFormatOutput($v)
    {
        $this->formatOutput = (boolean) $v;
    }

    /**
     * Gets a default output writer for this task.
     * @return Writer
     */
    private function getDefaultOutput()
    {
        return new LogWriter($this->parentTask);
    }

    /**
     * Gets the formatter that has been configured based on this element.
     * @return PDOResultFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }
}
