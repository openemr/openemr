<?php

/**
 * Regression test for version.php standalone inclusion.
 *
 * version.php is included by admin.php without the Composer autoloader,
 * so it must not reference any classes.
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

class VersionFileTest extends TestCase
{
    private string $versionFile;

    protected function setUp(): void
    {
        $this->versionFile = dirname(__DIR__, 3) . '/version.php';
        $this->assertFileExists($this->versionFile);
    }

    /**
     * version.php must be includable without the Composer autoloader.
     *
     * admin.php includes version.php in a bare PHP environment. If
     * version.php references any class, that inclusion fatals.
     */
    public function testStandaloneInclusion(): void
    {
        $code = sprintf('include %s;', var_export($this->versionFile, true));
        exec('php -r ' . escapeshellarg($code) . ' 2>&1', $output, $exitCode);
        $this->assertSame(
            0,
            $exitCode,
            "version.php must be includable without the autoloader.\nOutput: " . implode("\n", $output)
        );
    }

    /**
     * version.php must not contain any class dependencies.
     *
     * Guard against future additions of use statements, static calls,
     * or object instantiation.
     */
    public function testNoClassDependencies(): void
    {
        $contents = file_get_contents($this->versionFile);
        $this->assertIsString($contents);
        $tokens = token_get_all($contents);

        $forbidden = [T_USE, T_DOUBLE_COLON, T_NEW];
        $forbiddenNames = array_map(token_name(...), $forbidden);

        foreach ($tokens as $token) {
            if (is_array($token) && in_array($token[0], $forbidden, true)) {
                $this->fail(sprintf(
                    'version.php must not contain class dependencies, but found %s on line %d: %s',
                    token_name($token[0]),
                    $token[2],
                    trim($token[1])
                ));
            }
        }

        // If we reach here, no forbidden tokens were found
        $this->addToAssertionCount(1);
    }
}
