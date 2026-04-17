<?php

/**
 * Regression test for sql_upgrade.php pre-autoloader section.
 *
 * sql_upgrade.php sets several $GLOBALS flags before including
 * interface/globals.php (which loads the Composer autoloader).
 * Code before that require_once must not depend on the autoloader.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated;

use PHPUnit\Framework\TestCase;

class SqlUpgradeBootstrapTest extends TestCase
{
    /**
     * Code before the autoloader loads must not use OEGlobalsBag.
     *
     * Extract everything before `require_once('interface/globals.php')`
     * and verify it does not reference OEGlobalsBag, which requires
     * the Composer autoloader. Explicitly required classes (like
     * Compatibility\Checker) are fine.
     */
    public function testNoOEGlobalsBagBeforeAutoloader(): void
    {
        $file = dirname(__DIR__, 3) . '/sql_upgrade.php';
        $this->assertFileExists($file);

        $contents = file_get_contents($file);
        $this->assertIsString($contents);

        // Split at the globals.php include — only check code before it
        $marker = "require_once('interface/globals.php')";
        $pos = strpos($contents, $marker);
        $this->assertIsInt($pos, "Expected to find globals.php require in sql_upgrade.php");

        $preAutoloader = substr($contents, 0, $pos);

        $this->assertStringNotContainsString(
            'OEGlobalsBag',
            $preAutoloader,
            'sql_upgrade.php must not reference OEGlobalsBag before the autoloader loads. '
            . 'Use raw $GLOBALS for pre-autoloader bootstrap flags.'
        );
    }
}
