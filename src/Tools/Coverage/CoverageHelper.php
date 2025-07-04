<?php

namespace OpenEMR\Tools\Coverage;

use ReflectionMethod;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use PHPUnit\Framework\Attributes\Test;

use function microtime;
use function register_shutdown_function;

/**
 * Code Coverage Helper responsible for managing php code coverage for the code base.
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class CoverageHelper
{
    public static function createTargetedCodeCoverage(string $shutdownExportBasePath)
    {
        // empty filter so we want coverage on EVERYTHING
        $filter = new Filter();
        $coverage = new CodeCoverage((new Selector())->forLineCoverage($filter), $filter);
        // When the process is shut down, dump a partial coverage report in PHP format
        register_shutdown_function(function () use ($shutdownExportBasePath, $coverage): void {
            $coverage->stop();
            // now clean it all up.
            $id = (string)microtime(as_float: true);
            $covPath = $shutdownExportBasePath . '/' . $id . '.cov';
            (new PHP())->process($coverage, $covPath);
        });
        return $coverage;
    }

    /**
     * @param string[] $dirs - List of directories to collect coverage from
     * @param $shutdownExportBasePath - Directory where the coverage report will be dumped
     */
    public static function createCoverageForDirectories(
        array $dirs,
        string $shutdownExportBasePath,
    ): CodeCoverage {
        // Determine from what directories we want coverage to be collected
        $filter = new Filter();
        foreach ($dirs as $dir) {
            foreach ((new FileIteratorFacade())->getFilesAsArray($dir) as $file) {
                $filter->includeFile($file);
            }
        }

        $coverage = new CodeCoverage((new Selector())->forLineCoverage($filter), $filter);

        // When the process is shut down, dump a partial coverage report in PHP format
        register_shutdown_function(function () use ($shutdownExportBasePath, $coverage): void {
            $id = (string)microtime(as_float: true);
            $covPath = $shutdownExportBasePath . '/' . $id . '.cov';
            (new PHP())->process($coverage, $covPath);
        });

        return $coverage;
    }

    // TODO: @adunsulag I don't like using the filesystem to store the current coverage id as we lose
    // any kind of test parallelization.  Until we can figure out a way to pass the coverage to
    // the chain of calls from test runner to inferno to openemr, we will use the filesystem and
    // the long running tests will need to be run serially.
    public static function setCurrentCoverageId(string $className)
    {
        $coverageId = self::resolveCoverageId($className, "");
        file_put_contents("/tmp/oe_php_coverage_settings", $coverageId);
    }

    public static function getCurrentCoverageId(): string
    {
        $coverageSettings = file_get_contents("/tmp/oe_php_coverage_settings");
        if ($coverageSettings === false) {
            return '';
        }
        return trim($coverageSettings);
    }

    public static function resolveCoverageId(string $baseClass, string|int $dataName): string
    {
        return $baseClass . self::resolveTestMethod($baseClass) . self::resolveTestDataSet($dataName);
    }

    private static function resolveTestDataSet(string|int $dataName): string
    {
        return !empty($dataName) ? '#' . $dataName : '';
    }

    private static function resolveTestMethod(string $baseClass): string
    {
        $stack = debug_backtrace();

        // Get the first class in the stack which is baseClass, then get its first test method
        foreach ($stack as $t) {
            if (!isset($t['object'], $t['class']) || $t['class'] !== $baseClass) {
                continue;
            }

            // The test method is the first one in the backtrace which has the Test attribute
            $ref = new ReflectionMethod($t['object'], $t['function']);
            $attributes = $ref->getAttributes();
            foreach ($attributes as $attr) {
                if ($attr->getName() === Test::class) {
                    return '::' . $t['function'];
                }
            }
        }

        return '';
    }
}
