<?php

/**
 * Collects the PHP diagnostics a callable raises.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated;

/**
 * PHPUnit's failOnDeprecation/failOnWarning ignore diagnostics from files it
 * counts as third-party (such as .inc files), so tests that pin warning-free
 * legacy code collect them directly.
 */
trait CollectsPhpDiagnosticsTrait
{
    /**
     * @return list<string> every PHP diagnostic $call raised, as "line: message"
     */
    private static function diagnosticsFrom(callable $call): array
    {
        $diagnostics = [];
        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) use (&$diagnostics): bool {
            $diagnostics[] = $errline . ': ' . $errstr;
            return true;
        });

        try {
            $call();
        } finally {
            restore_error_handler();
        }

        return $diagnostics;
    }
}
