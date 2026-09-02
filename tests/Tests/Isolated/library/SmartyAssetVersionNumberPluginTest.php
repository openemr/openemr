<?php

/**
 * SmartyAssetVersionNumberPluginTest
 *
 * Tests the Smarty {assetVersionNumber} function plugin, which supplies the
 * cache-busting query value for script and style asset includes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[BackupGlobals(true)]
class SmartyAssetVersionNumberPluginTest extends TestCase
{
    private const PLUGIN = __DIR__ . '/../../../../library/smarty/plugins/function.assetVersionNumber.php';

    protected function setUp(): void
    {
        require_once self::PLUGIN;
    }

    public function testReturnsTheConfiguredVersion(): void
    {
        $GLOBALS['v_js_includes'] = '82';

        $smarty = null;
        $this->assertSame('82', smarty_function_assetVersionNumber([], $smarty));
    }

    public function testEscapesTheConfiguredVersionForUseInAUrl(): void
    {
        $GLOBALS['v_js_includes'] = 'a b&c';

        $smarty = null;
        $this->assertSame('a+b%26c', smarty_function_assetVersionNumber([], $smarty));
    }

    public function testFallsBackToTheCurrentTimestampWhenTheVersionIsUnset(): void
    {
        unset($GLOBALS['v_js_includes']);

        $before = time();
        $smarty = null;
        $version = smarty_function_assetVersionNumber([], $smarty);
        $after = time();

        // A per-request value, not a constant: an unknown version must always
        // miss the browser cache rather than pin every asset to one URL.
        $this->assertMatchesRegularExpression('/^\d+$/', $version);
        $this->assertGreaterThanOrEqual($before, (int) $version);
        $this->assertLessThanOrEqual($after, (int) $version);
    }
}
