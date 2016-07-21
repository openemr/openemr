<?php
/**
 * $Id: 736c93aeebfee971ec02e321baf6e0120d58e937 $
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
require_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/FileWriter.php';
require_once 'phing/util/ExtendedFileStream.php';

/**
 * Transform a PHPUnit xml report using XSLT.
 * This transformation generates an html report in either framed or non-framed
 * style. The non-framed style is convenient to have a concise report via mail,
 * the framed report is much more convenient if you want to browse into
 * different packages or testcases since it is a Javadoc like report.
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 736c93aeebfee971ec02e321baf6e0120d58e937 $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
class PHPUnitReportTask extends Task
{
    private $format = "noframes";
    private $styleDir = "";

    /**
     * @var PhingFile
     */
    private $toDir;

    /**
     * Whether to use the sorttable JavaScript library, defaults to false
     * See {@link http://www.kryogenix.org/code/browser/sorttable/)}
     *
     * @var boolean
     */
    private $useSortTable = false;

    /** the directory where the results XML can be found */
    private $inFile = "testsuites.xml";

    /**
     * Set the filename of the XML results file to use.
     * @param PhingFile $inFile
     * @return void
     */
    public function setInFile(PhingFile $inFile)
    {
        $this->inFile = $inFile;
    }

    /**
     * Set the format of the generated report. Must be noframes or frames.
     * @param $format
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Set the directory where the stylesheets are located.
     * @param $styleDir
     * @return void
     */
    public function setStyleDir($styleDir)
    {
        $this->styleDir = $styleDir;
    }

    /**
     * Set the directory where the files resulting from the
     * transformation should be written to.
     * @param PhingFile $toDir
     * @return void
     */
    public function setToDir(PhingFile $toDir)
    {
        $this->toDir = $toDir;
    }

    /**
     * Sets whether to use the sorttable JavaScript library, defaults to false
     * See {@link http://www.kryogenix.org/code/browser/sorttable/)}
     *
     * @param boolean $useSortTable
     * @return void
     */
    public function setUseSortTable($useSortTable)
    {
        $this->useSortTable = (boolean) $useSortTable;
    }

    /**
     * Returns the path to the XSL stylesheet
     * @throws BuildException
     */
    protected function getStyleSheet()
    {
        $xslname = "phpunit-" . $this->format . ".xsl";

        if ($this->styleDir) {
            $file = new PhingFile($this->styleDir, $xslname);
        } else {
            $path = Phing::getResourcePath("phing/etc/$xslname");

            if ($path === null) {
                $path = Phing::getResourcePath("etc/$xslname");

                if ($path === null) {
                    throw new BuildException("Could not find $xslname in resource path");
                }
            }

            $file = new PhingFile($path);
        }

        if (!$file->exists()) {
            throw new BuildException("Could not find file " . $file->getPath());
        }

        return $file;
    }

    /**
     * Transforms the DOM document
     * @param DOMDocument $document
     * @throws BuildException
     * @throws IOException
     */
    protected function transform(DOMDocument $document)
    {
        if (!$this->toDir->exists()) {
            throw new BuildException("Directory '" . $this->toDir . "' does not exist");
        }

        $xslfile = $this->getStyleSheet();

        $xsl = new DOMDocument();
        $xsl->load($xslfile->getAbsolutePath());

        $proc = new XSLTProcessor();
        if (defined('XSL_SECPREF_WRITE_FILE')) {
            if (version_compare(PHP_VERSION, '5.4', "<")) {
                ini_set("xsl.security_prefs", XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
            } else {
                $proc->setSecurityPrefs(XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
            }
        }

        $proc->importStyleSheet($xsl);
        $proc->setParameter('', 'output.sorttable', (string) $this->useSortTable);

        if ($this->format == "noframes") {
            $writer = new FileWriter(new PhingFile($this->toDir, "phpunit-noframes.html"));
            $writer->write($proc->transformToXML($document));
            $writer->close();
        } else {
            ExtendedFileStream::registerStream();

            $toDir = (string) $this->toDir;

            // urlencode() the path if we're on Windows
            if (FileSystem::getFileSystem()->getSeparator() == '\\') {
                $toDir = urlencode($toDir);
            }

            // no output for the framed report
            // it's all done by extension...
            $proc->setParameter('', 'output.dir', $toDir);
            $proc->transformToXML($document);

            ExtendedFileStream::unregisterStream();
        }
    }

    /**
     * Fixes DOM document tree:
     *   - adds package="default" to 'testsuite' elements without
     *     package attribute
     *   - removes outer 'testsuite' container(s)
     * @param DOMDocument $document
     */
    protected function fixDocument(DOMDocument $document)
    {
        $rootElement = $document->firstChild;

        $xp = new DOMXPath($document);

        $nodes = $xp->query("/testsuites/testsuite");

        foreach ($nodes as $node) {
            $children = $xp->query("./testsuite", $node);

            if ($children->length) {
                foreach ($children as $child) {
                    $rootElement->appendChild($child);

                    if ($child->hasAttribute('package')) {
                        continue;
                    }

                    if ($child->hasAttribute('namespace')) {
                        $child->setAttribute('package', $child->getAttribute('namespace'));
                        continue;
                    }

                    $package = 'default';
                    $refClass = new ReflectionClass($child->getAttribute('name'));

                    if (preg_match('/@package\s+(.*)\r?\n/m', $refClass->getDocComment(), $matches)) {
                        $package = end($matches);
                    } elseif (method_exists($refClass, 'getNamespaceName')) {
                        $namespace = $refClass->getNamespaceName();

                        if ($namespace !== '') {
                            $package = $namespace;
                        }
                    }

                    $child->setAttribute('package', trim($package));
                }

                $rootElement->removeChild($node);
            }
        }
    }

    /**
     * Initialize the task
     */
    public function init()
    {
        if (!class_exists('XSLTProcessor')) {
            throw new BuildException("PHPUnitReportTask requires the XSL extension");
        }
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $testSuitesDoc = new DOMDocument();
        $testSuitesDoc->load((string) $this->inFile);

        $this->fixDocument($testSuitesDoc);

        $this->transform($testSuitesDoc);
    }
}
