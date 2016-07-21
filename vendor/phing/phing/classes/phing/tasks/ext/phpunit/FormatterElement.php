<?php
/**
 * $Id: d9772e92cb3f8171dcfe721066c2f23f46f20c58 $
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
 * A wrapper for the implementations of PHPUnit2ResultFormatter.
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: d9772e92cb3f8171dcfe721066c2f23f46f20c58 $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
class FormatterElement
{
    protected $formatter = null;

    protected $type = "";

    protected $useFile = true;

    protected $toDir = ".";

    protected $outfile = "";

    protected $parent = null;

    /**
     * Sets parent task
     * @param Task $parent Calling Task
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Loads a specific formatter type
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Loads a specific formatter class
     * @param $className
     */
    public function setClassName($className)
    {
        $classNameNoDot = Phing::import($className);

        $this->formatter = new $classNameNoDot();
    }

    /**
     * Sets whether to store formatting results in a file
     * @param $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = $useFile;
    }

    /**
     * Returns whether to store formatting results in a file
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * Sets output directory
     * @param string $toDir
     */
    public function setToDir($toDir)
    {
        if (!is_dir($toDir)) {
            $toDir = new PhingFile($toDir);
            $toDir->mkdirs();
        }

        $this->toDir = $toDir;
    }

    /**
     * Returns output directory
     * @return string
     */
    public function getToDir()
    {
        return $this->toDir;
    }

    /**
     * Sets output filename
     * @param string $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Returns output filename
     * @return string
     */
    public function getOutfile()
    {
        if ($this->outfile) {
            return $this->outfile;
        } else {
            return $this->formatter->getPreferredOutfile() . $this->getExtension();
        }
    }

    /**
     * Returns extension
     * @return string
     */
    public function getExtension()
    {
        return $this->formatter->getExtension();
    }

    /**
     * Returns formatter object
     * @throws BuildException
     * @return PHPUnitResultFormatter
     */
    public function getFormatter()
    {
        if ($this->formatter !== null) {
            return $this->formatter;
        }

        if ($this->type == "summary") {
            require_once 'phing/tasks/ext/phpunit/formatter/SummaryPHPUnitResultFormatter.php';
            $this->formatter = new SummaryPHPUnitResultFormatter($this->parent);
        } elseif ($this->type == "clover") {
            require_once 'phing/tasks/ext/phpunit/formatter/CloverPHPUnitResultFormatter.php';
            $this->formatter = new CloverPHPUnitResultFormatter($this->parent);
        } elseif ($this->type == "xml") {
            require_once 'phing/tasks/ext/phpunit/formatter/XMLPHPUnitResultFormatter.php';
            $this->formatter = new XMLPHPUnitResultFormatter($this->parent);
        } elseif ($this->type == "plain") {
            require_once 'phing/tasks/ext/phpunit/formatter/PlainPHPUnitResultFormatter.php';
            $this->formatter = new PlainPHPUnitResultFormatter($this->parent);
        } elseif ($this->type == "crap4j") {
            require_once 'phing/tasks/ext/phpunit/formatter/Crap4jPHPUnitResultFormatter.php';
            $this->formatter = new Crap4jPHPUnitResultFormatter($this->parent);
        } else {
            throw new BuildException("Formatter '" . $this->type . "' not implemented");
        }

        return $this->formatter;
    }
}
