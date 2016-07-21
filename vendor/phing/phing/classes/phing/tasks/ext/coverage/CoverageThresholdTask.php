<?php
/**
 * $Id: 1cf68f1a4ec652811a5fd9db8353cae9a88ee03e $
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
require_once 'phing/system/util/Properties.php';
require_once 'phing/types/Excludes.php';

/**
 * Stops the build if any of the specified coverage threshold was not reached
 *
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: 1cf68f1a4ec652811a5fd9db8353cae9a88ee03e $
 * @package phing.tasks.ext.coverage
 * @since   2.4.1
 */
class CoverageThresholdTask extends Task
{
    /**
     * Holds an optional classpath
     *
     * @var Path
     */
    private $_classpath = null;

    /**
     * Holds the exclusions
     *
     * @var Excludes
     */
    private $_excludes = null;

    /**
     * Holds an optional database file
     *
     * @var PhingFile
     */
    private $_database = null;

    /**
     * Holds the coverage threshold for the entire project
     *
     * @var integer
     */
    private $_perProject = 25;

    /**
     * Holds the coverage threshold for any class
     *
     * @var integer
     */
    private $_perClass = 25;

    /**
     * Holds the coverage threshold for any method
     *
     * @var integer
     */
    private $_perMethod = 25;

    /**
     * Holds the minimum found coverage value for a class
     *
     * @var integer
     */
    private $_minClassCoverageFound = null;

    /**
     * Holds the minimum found coverage value for a method
     *
     * @var integer
     */
    private $_minMethodCoverageFound = null;

    /**
     * Number of statements in the entire project
     *
     * @var integer
     */
    private $_projectStatementCount = 0;

    /**
     * Number of covered statements in the entire project
     *
     * @var integer
     */
    private $_projectStatementsCovered = 0;

    /**
     * Whether to enable detailed logging
     *
     * @var boolean
     */
    private $_verbose = false;

    /**
     * Sets an optional classpath
     *
     * @param Path $classpath The classpath
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->_classpath === null) {
            $this->_classpath = $classpath;
        } else {
            $this->_classpath->append($classpath);
        }
    }

    /**
     * Sets the optional coverage database to use
     *
     * @param PhingFile The database file
     */
    public function setDatabase(PhingFile $database)
    {
        $this->_database = $database;
    }

    /**
     * Create classpath object
     *
     * @return Path
     */
    public function createClasspath()
    {
        $this->_classpath = new Path();

        return $this->_classpath;
    }

    /**
     * Sets the coverage threshold for entire project
     *
     * @param integer $threshold Coverage threshold for entire project
     */
    public function setPerProject($threshold)
    {
        $this->_perProject = $threshold;
    }

    /**
     * Sets the coverage threshold for any class
     *
     * @param integer $threshold Coverage threshold for any class
     */
    public function setPerClass($threshold)
    {
        $this->_perClass = $threshold;
    }

    /**
     * Sets the coverage threshold for any method
     *
     * @param integer $threshold Coverage threshold for any method
     */
    public function setPerMethod($threshold)
    {
        $this->_perMethod = $threshold;
    }

    /**
     * Sets whether to enable detailed logging or not
     *
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->_verbose = StringHelper::booleanValue($verbose);
    }

    /**
     * Filter covered statements
     *
     * @param  integer $var Coverage CODE/count
     * @return boolean
     */
    protected function filterCovered($var)
    {
        return ($var >= 0 || $var === -2);
    }

    /**
     * Create excludes object
     *
     * @return Excludes
     */
    public function createExcludes()
    {
        $this->_excludes = new Excludes($this->project);

        return $this->_excludes;
    }

    /**
     * Calculates the coverage threshold
     *
     * @param string $filename The filename to analyse
     * @param array $coverageInformation Array with coverage information
     * @throws BuildException
     */
    protected function calculateCoverageThreshold($filename, $coverageInformation)
    {
        $classes = PHPUnitUtil::getDefinedClasses($filename, $this->_classpath);

        if (is_array($classes)) {
            foreach ($classes as $className) {
                // Skip class if excluded from coverage threshold validation
                if ($this->_excludes !== null) {
                    if (in_array($className, $this->_excludes->getExcludedClasses())) {
                        continue;
                    }
                }

                $reflection = new ReflectionClass($className);
                $classStartLine = $reflection->getStartLine();

                // Strange PHP5 reflection bug, classes without parent class
                // or implemented interfaces seem to start one line off
                if ($reflection->getParentClass() === null
                    && count($reflection->getInterfaces()) === 0
                ) {
                    unset($coverageInformation[$classStartLine + 1]);
                } else {
                    unset($coverageInformation[$classStartLine]);
                }

                reset($coverageInformation);

                $methods = $reflection->getMethods();

                foreach ($methods as $method) {
                    // PHP5 reflection considers methods of a parent class
                    // to be part of a subclass, we don't
                    if ($method->getDeclaringClass()->getName() != $reflection->getName()) {
                        continue;
                    }

                    // Skip method if excluded from coverage threshold validation
                    if ($this->_excludes !== null) {
                        $excludedMethods = $this->_excludes->getExcludedMethods();

                        if (isset($excludedMethods[$className])) {
                            if (in_array($method->getName(), $excludedMethods[$className])
                                || in_array($method->getName() . '()', $excludedMethods[$className])
                            ) {
                                continue;
                            }
                        }
                    }

                    $methodStartLine = $method->getStartLine();
                    $methodEndLine = $method->getEndLine();

                    // small fix for XDEBUG_CC_UNUSED
                    if (isset($coverageInformation[$methodStartLine])) {
                        unset($coverageInformation[$methodStartLine]);
                    }

                    if (isset($coverageInformation[$methodEndLine])) {
                        unset($coverageInformation[$methodEndLine]);
                    }

                    if ($method->isAbstract()) {
                        continue;
                    }

                    $lineNr = key($coverageInformation);

                    while ($lineNr !== null && $lineNr < $methodStartLine) {
                        next($coverageInformation);
                        $lineNr = key($coverageInformation);
                    }

                    $methodStatementsCovered = 0;
                    $methodStatementCount = 0;

                    while ($lineNr !== null && $lineNr <= $methodEndLine) {
                        $methodStatementCount++;

                        $lineCoverageInfo = $coverageInformation[$lineNr];
                        // set covered when CODE is other than -1 (not executed)
                        if ($lineCoverageInfo > 0 || $lineCoverageInfo === -2) {
                            $methodStatementsCovered++;
                        }

                        next($coverageInformation);
                        $lineNr = key($coverageInformation);
                    }

                    if ($methodStatementCount > 0) {
                        $methodCoverage = ($methodStatementsCovered
                                / $methodStatementCount) * 100;
                    } else {
                        $methodCoverage = 0;
                    }

                    if ($methodCoverage < $this->_perMethod
                        && !$method->isAbstract()
                    ) {
                        throw new BuildException(
                            'The coverage (' . round($methodCoverage, 2) . '%) '
                            . 'for method "' . $method->getName() . '" is lower'
                            . ' than the specified threshold ('
                            . $this->_perMethod . '%), see file: "'
                            . $filename . '"'
                        );
                    } elseif ($methodCoverage < $this->_perMethod
                        && $method->isAbstract()
                        && $this->_verbose === true
                    ) {
                        $this->log(
                            'Skipped coverage threshold for abstract method "'
                            . $method->getName() . '"'
                        );
                    }

                    // store the minimum coverage value for logging (see #466)
                    if ($this->_minMethodCoverageFound !== null) {
                        if ($this->_minMethodCoverageFound > $methodCoverage) {
                            $this->_minMethodCoverageFound = $methodCoverage;
                        }
                    } else {
                        $this->_minMethodCoverageFound = $methodCoverage;
                    }
                }

                $classStatementCount = count($coverageInformation);
                $classStatementsCovered = count(
                    array_filter(
                        $coverageInformation,
                        array($this, 'filterCovered')
                    )
                );

                if ($classStatementCount > 0) {
                    $classCoverage = ($classStatementsCovered
                            / $classStatementCount) * 100;
                } else {
                    $classCoverage = 0;
                }

                if ($classCoverage < $this->_perClass
                    && !$reflection->isAbstract()
                ) {
                    throw new BuildException(
                        'The coverage (' . round($classCoverage, 2) . '%) for class "'
                        . $reflection->getName() . '" is lower than the '
                        . 'specified threshold (' . $this->_perClass . '%), '
                        . 'see file: "' . $filename . '"'
                    );
                } elseif ($classCoverage < $this->_perClass
                    && $reflection->isAbstract()
                    && $this->_verbose === true
                ) {
                    $this->log(
                        'Skipped coverage threshold for abstract class "'
                        . $reflection->getName() . '"'
                    );
                }

                // store the minimum coverage value for logging (see #466)
                if ($this->_minClassCoverageFound !== null) {
                    if ($this->_minClassCoverageFound > $classCoverage) {
                        $this->_minClassCoverageFound = $classCoverage;
                    }
                } else {
                    $this->_minClassCoverageFound = $classCoverage;
                }

                $this->_projectStatementCount += $classStatementCount;
                $this->_projectStatementsCovered += $classStatementsCovered;
            }
        }
    }

    public function main()
    {
        if ($this->_database === null) {
            $coverageDatabase = $this->project
                ->getProperty('coverage.database');

            if (!$coverageDatabase) {
                throw new BuildException(
                    'Either include coverage-setup in your build file or set '
                    . 'the "database" attribute'
                );
            }

            $database = new PhingFile($coverageDatabase);
        } else {
            $database = $this->_database;
        }

        $this->log(
            'Calculating coverage threshold: min. '
            . $this->_perProject . '% per project, '
            . $this->_perClass . '% per class and '
            . $this->_perMethod . '% per method is required'
        );

        $props = new Properties();
        $props->load($database);

        foreach ($props->keys() as $filename) {
            $file = unserialize($props->getProperty($filename));

            // Skip file if excluded from coverage threshold validation
            if ($this->_excludes !== null) {
                if (in_array($file['fullname'], $this->_excludes->getExcludedFiles())) {
                    continue;
                }
            }

            $this->calculateCoverageThreshold(
                $file['fullname'],
                $file['coverage']
            );
        }

        if ($this->_projectStatementCount > 0) {
            $coverage = ($this->_projectStatementsCovered
                    / $this->_projectStatementCount) * 100;
        } else {
            $coverage = 0;
        }

        if ($coverage < $this->_perProject) {
            throw new BuildException(
                'The coverage (' . round($coverage, 2) . '%) for the entire project '
                . 'is lower than the specified threshold ('
                . $this->_perProject . '%)'
            );
        }

        $this->log(
            'Passed coverage threshold. Minimum found coverage values are: '
            . round($coverage, 2) . '% per project, '
            . round($this->_minClassCoverageFound, 2) . '% per class and '
            . round($this->_minMethodCoverageFound, 2) . '% per method'
        );
    }
}
