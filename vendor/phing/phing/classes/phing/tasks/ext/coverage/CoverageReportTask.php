<?php
/**
 * $Id: 2f73afc3401097333c7f187eacb79de9e16a6f35 $
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
require_once 'phing/system/io/Writer.php';
require_once 'phing/system/util/Properties.php';
require_once 'phing/tasks/ext/phpunit/PHPUnitUtil.php';
require_once 'phing/tasks/ext/coverage/CoverageReportTransformer.php';

/**
 * Transforms information in a code coverage database to XML
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 2f73afc3401097333c7f187eacb79de9e16a6f35 $
 * @package phing.tasks.ext.coverage
 * @since 2.1.0
 */
class CoverageReportTask extends Task
{
    private $outfile = "coverage.xml";

    private $transformers = array();

    /** the classpath to use (optional) */
    private $classpath = null;

    /** the path to the GeSHi library (optional) */
    private $geshipath = "";

    /** the path to the GeSHi language files (optional) */
    private $geshilanguagespath = "";

    /**
     * @param Path $classpath
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * @return null|Path
     */
    public function createClasspath()
    {
        $this->classpath = new Path();

        return $this->classpath;
    }

    /**
     * @param $path
     */
    public function setGeshiPath($path)
    {
        $this->geshipath = $path;
    }

    /**
     * @param $path
     */
    public function setGeshiLanguagesPath($path)
    {
        $this->geshilanguagespath = $path;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->doc = new DOMDocument();
        $this->doc->encoding = 'UTF-8';
        $this->doc->formatOutput = true;
        $this->doc->appendChild($this->doc->createElement('snapshot'));
    }

    /**
     * @param $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Generate a report based on the XML created by this task
     */
    public function createReport()
    {
        $transformer = new CoverageReportTransformer($this);
        $this->transformers[] = $transformer;

        return $transformer;
    }

    /**
     * @param $packageName
     * @return null
     */
    protected function getPackageElement($packageName)
    {
        $packages = $this->doc->documentElement->getElementsByTagName('package');

        foreach ($packages as $package) {
            if ($package->getAttribute('name') == $packageName) {
                return $package;
            }
        }

        return null;
    }

    /**
     * @param $packageName
     * @param $element
     */
    protected function addClassToPackage($packageName, $element)
    {
        $package = $this->getPackageElement($packageName);

        if ($package === null) {
            $package = $this->doc->createElement('package');
            $package->setAttribute('name', $packageName);
            $this->doc->documentElement->appendChild($package);
        }

        $package->appendChild($element);
    }

    /**
     * Adds a subpackage to their package
     *
     * @param string $packageName    The name of the package
     * @param string $subpackageName The name of the subpackage
     *
     * @author Benjamin Schultz <bschultz@proqrent.de>
     * @return void
     */
    protected function addSubpackageToPackage($packageName, $subpackageName)
    {
        $package = $this->getPackageElement($packageName);
        $subpackage = $this->getSubpackageElement($subpackageName);

        if ($package === null) {
            $package = $this->doc->createElement('package');
            $package->setAttribute('name', $packageName);
            $this->doc->documentElement->appendChild($package);
        }

        if ($subpackage === null) {
            $subpackage = $this->doc->createElement('subpackage');
            $subpackage->setAttribute('name', $subpackageName);
        }

        $package->appendChild($subpackage);
    }

    /**
     * Returns the subpackage element
     *
     * @param string $subpackageName The name of the subpackage
     *
     * @author Benjamin Schultz <bschultz@proqrent.de>
     * @return DOMNode|null null when no DOMNode with the given name exists
     */
    protected function getSubpackageElement($subpackageName)
    {
        $subpackages = $this->doc->documentElement->getElementsByTagName('subpackage');

        foreach ($subpackages as $subpackage) {
            if ($subpackage->getAttribute('name') == $subpackageName) {
                return $subpackage;
            }
        }

        return null;
    }

    /**
     * Adds a class to their subpackage
     *
     * @param string  $classname The name of the class
     * @param DOMNode $element   The dom node to append to the subpackage element
     *
     * @author Benjamin Schultz <bschultz@proqrent.de>
     * @return void
     */
    protected function addClassToSubpackage($classname, $element)
    {
        $subpackageName = PHPUnitUtil::getSubpackageName($classname);

        $subpackage = $this->getSubpackageElement($subpackageName);

        if ($subpackage === null) {
            $subpackage = $this->doc->createElement('subpackage');
            $subpackage->setAttribute('name', $subpackageName);
            $this->doc->documentElement->appendChild($subpackage);
        }

        $subpackage->appendChild($element);
    }

    /**
     * @param $source
     * @return string
     */
    protected function stripDiv($source)
    {
        $openpos = strpos($source, "<div");
        $closepos = strpos($source, ">", $openpos);

        $line = substr($source, $closepos + 1);

        $tagclosepos = strpos($line, "</div>");

        $line = substr($line, 0, $tagclosepos);

        return $line;
    }

    /**
     * @param $filename
     * @return array
     */
    protected function highlightSourceFile($filename)
    {
        if ($this->geshipath) {
            require_once $this->geshipath . '/geshi.php';

            $source = file_get_contents($filename);

            $geshi = new GeSHi($source, 'php', $this->geshilanguagespath);

            $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);

            $geshi->enable_strict_mode(true);

            $geshi->enable_classes(true);

            $geshi->set_url_for_keyword_group(3, '');

            $html = $geshi->parse_code();

            $lines = preg_split("#</?li>#", $html);

            // skip first and last line
            array_pop($lines);
            array_shift($lines);

            $lines = array_filter($lines);

            $lines = array_map(array($this, 'stripDiv'), $lines);

            return $lines;
        } else {
            $lines = file($filename);

            for ($i = 0; $i < count($lines); $i++) {
                $line = $lines[$i];

                $line = rtrim($line);

                if (function_exists('mb_check_encoding') && mb_check_encoding($line, 'UTF-8')) {
                    $lines[$i] = $line;
                } else {
                    if (function_exists('mb_convert_encoding')) {
                        $lines[$i] = mb_convert_encoding($line, 'UTF-8');
                    } else {
                        $lines[$i] = utf8_encode($line);
                    }
                }
            }

            return $lines;
        }
    }

    /**
     * @param $filename
     * @param $coverageInformation
     * @param int $classStartLine
     * @return DOMElement
     */
    protected function transformSourceFile($filename, $coverageInformation, $classStartLine = 1)
    {
        $sourceElement = $this->doc->createElement('sourcefile');
        $sourceElement->setAttribute('name', basename($filename));

        /**
         * Add original/full filename to document
         */
        $sourceElement->setAttribute('sourcefile', $filename);

        $filelines = $this->highlightSourceFile($filename);

        $linenr = 1;

        foreach ($filelines as $line) {
            $lineElement = $this->doc->createElement('sourceline');
            $lineElement->setAttribute(
                'coveredcount',
                (isset($coverageInformation[$linenr]) ? $coverageInformation[$linenr] : '0')
            );

            if ($linenr == $classStartLine) {
                $lineElement->setAttribute('startclass', 1);
            }

            $textnode = $this->doc->createTextNode($line);
            $lineElement->appendChild($textnode);

            $sourceElement->appendChild($lineElement);

            $linenr++;
        }

        return $sourceElement;
    }

    /**
     * Transforms the coverage information
     *
     * @param string $filename            The filename
     * @param array  $coverageInformation Array with covergae information
     *
     * @author Michiel Rook <mrook@php.net>
     * @author Benjamin Schultz <bschultz@proqrent.de>
     * @return void
     */
    protected function transformCoverageInformation($filename, $coverageInformation)
    {
        $classes = PHPUnitUtil::getDefinedClasses($filename, $this->classpath);

        if (is_array($classes)) {
            foreach ($classes as $classname) {
                $reflection = new ReflectionClass($classname);
                $methods = $reflection->getMethods();

                if (method_exists($reflection, 'getShortName')) {
                    $className = $reflection->getShortName();
                } else {
                    $className = $reflection->getName();
                }

                $classElement = $this->doc->createElement('class');
                $classElement->setAttribute('name', $className);

                $packageName = PHPUnitUtil::getPackageName($reflection->getName());
                $subpackageName = PHPUnitUtil::getSubpackageName($reflection->getName());

                if ($subpackageName !== null) {
                    $this->addSubpackageToPackage($packageName, $subpackageName);
                    $this->addClassToSubpackage($className, $classElement);
                } else {
                    $this->addClassToPackage($packageName, $classElement);
                }

                $classStartLine = $reflection->getStartLine();

                $methodscovered = 0;
                $methodcount = 0;

                // Strange PHP5 reflection bug, classes without parent class or implemented interfaces seem to start one line off
                if ($reflection->getParentClass() == null && count($reflection->getInterfaces()) == 0) {
                    unset($coverageInformation[$classStartLine + 1]);
                } else {
                    unset($coverageInformation[$classStartLine]);
                }

                // Remove out-of-bounds info
                unset($coverageInformation[0]);

                reset($coverageInformation);

                foreach ($methods as $method) {
                    // PHP5 reflection considers methods of a parent class to be part of a subclass, we don't
                    if ($method->getDeclaringClass()->getName() != $reflection->getName()) {
                        continue;
                    }

                    // small fix for XDEBUG_CC_UNUSED
                    if (isset($coverageInformation[$method->getStartLine()])) {
                        unset($coverageInformation[$method->getStartLine()]);
                    }

                    if (isset($coverageInformation[$method->getEndLine()])) {
                        unset($coverageInformation[$method->getEndLine()]);
                    }

                    if ($method->isAbstract()) {
                        continue;
                    }

                    $linenr = key($coverageInformation);

                    while ($linenr !== null && $linenr < $method->getStartLine()) {
                        next($coverageInformation);
                        $linenr = key($coverageInformation);
                    }

                    $methodCoveredCount = 0;
                    $methodTotalCount = 0;

                    $methodHasCoveredLine = false;

                    while ($linenr !== null && $linenr <= $method->getEndLine()) {
                        $methodTotalCount++;
                        $methodHasCoveredLine = true;

                        // set covered when CODE is other than -1 (not executed)
                        if ($coverageInformation[$linenr] > 0 || $coverageInformation[$linenr] == -2) {
                            $methodCoveredCount++;
                        }

                        next($coverageInformation);
                        $linenr = key($coverageInformation);
                    }

                    if (($methodTotalCount == $methodCoveredCount) && $methodHasCoveredLine) {
                        $methodscovered++;
                    }

                    $methodcount++;
                }

                $statementcount = count(
                    array_filter(
                        $coverageInformation,
                        create_function('$var', 'return ($var != -2);')
                    )
                );

                $statementscovered = count(
                    array_filter(
                        $coverageInformation,
                        create_function('$var', 'return ($var >= 0);')
                    )
                );

                $classElement->appendChild(
                    $this->transformSourceFile($filename, $coverageInformation, $classStartLine)
                );

                $classElement->setAttribute('methodcount', $methodcount);
                $classElement->setAttribute('methodscovered', $methodscovered);
                $classElement->setAttribute('statementcount', $statementcount);
                $classElement->setAttribute('statementscovered', $statementscovered);
                $classElement->setAttribute('totalcount', $methodcount + $statementcount);
                $classElement->setAttribute('totalcovered', $methodscovered + $statementscovered);
            }
        }
    }

    protected function calculateStatistics()
    {
        $packages = $this->doc->documentElement->getElementsByTagName('package');

        $totalmethodcount = 0;
        $totalmethodscovered = 0;

        $totalstatementcount = 0;
        $totalstatementscovered = 0;

        foreach ($packages as $package) {
            $methodcount = 0;
            $methodscovered = 0;

            $statementcount = 0;
            $statementscovered = 0;

            $subpackages = $package->getElementsByTagName('subpackage');

            foreach ($subpackages as $subpackage) {
                $subpackageMethodCount = 0;
                $subpackageMethodsCovered = 0;

                $subpackageStatementCount = 0;
                $subpackageStatementsCovered = 0;

                $subpackageClasses = $subpackage->getElementsByTagName('class');

                foreach ($subpackageClasses as $subpackageClass) {
                    $subpackageMethodCount += $subpackageClass->getAttribute('methodcount');
                    $subpackageMethodsCovered += $subpackageClass->getAttribute('methodscovered');

                    $subpackageStatementCount += $subpackageClass->getAttribute('statementcount');
                    $subpackageStatementsCovered += $subpackageClass->getAttribute('statementscovered');
                }

                $subpackage->setAttribute('methodcount', $subpackageMethodCount);
                $subpackage->setAttribute('methodscovered', $subpackageMethodsCovered);

                $subpackage->setAttribute('statementcount', $subpackageStatementCount);
                $subpackage->setAttribute('statementscovered', $subpackageStatementsCovered);

                $subpackage->setAttribute('totalcount', $subpackageMethodCount + $subpackageStatementCount);
                $subpackage->setAttribute('totalcovered', $subpackageMethodsCovered + $subpackageStatementsCovered);
            }

            $classes = $package->getElementsByTagName('class');

            foreach ($classes as $class) {
                $methodcount += $class->getAttribute('methodcount');
                $methodscovered += $class->getAttribute('methodscovered');

                $statementcount += $class->getAttribute('statementcount');
                $statementscovered += $class->getAttribute('statementscovered');
            }

            $package->setAttribute('methodcount', $methodcount);
            $package->setAttribute('methodscovered', $methodscovered);

            $package->setAttribute('statementcount', $statementcount);
            $package->setAttribute('statementscovered', $statementscovered);

            $package->setAttribute('totalcount', $methodcount + $statementcount);
            $package->setAttribute('totalcovered', $methodscovered + $statementscovered);

            $totalmethodcount += $methodcount;
            $totalmethodscovered += $methodscovered;

            $totalstatementcount += $statementcount;
            $totalstatementscovered += $statementscovered;
        }

        $this->doc->documentElement->setAttribute('methodcount', $totalmethodcount);
        $this->doc->documentElement->setAttribute('methodscovered', $totalmethodscovered);

        $this->doc->documentElement->setAttribute('statementcount', $totalstatementcount);
        $this->doc->documentElement->setAttribute('statementscovered', $totalstatementscovered);

        $this->doc->documentElement->setAttribute('totalcount', $totalmethodcount + $totalstatementcount);
        $this->doc->documentElement->setAttribute('totalcovered', $totalmethodscovered + $totalstatementscovered);
    }

    public function main()
    {
        $coverageDatabase = $this->project->getProperty('coverage.database');

        if (!$coverageDatabase) {
            throw new BuildException("Property coverage.database is not set - please include coverage-setup in your build file");
        }

        $database = new PhingFile($coverageDatabase);

        $this->log("Transforming coverage report");

        $props = new Properties();
        $props->load($database);

        foreach ($props->keys() as $filename) {
            $file = unserialize($props->getProperty($filename));

            $this->transformCoverageInformation($file['fullname'], $file['coverage']);
        }

        $this->calculateStatistics();

        $this->doc->save($this->outfile);

        foreach ($this->transformers as $transformer) {
            $transformer->setXmlDocument($this->doc);
            $transformer->transform();
        }
    }
}
