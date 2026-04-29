<?php

/**
 * SmartyXlPluginsTest
 *
 * Tests the Smarty xlt/xla/xlj function and modifier plugins that forward
 * dynamic template strings to the xlt()/xla()/xlj() helpers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class SmartyXlPluginsTest extends TestCase
{
    private const PLUGINS_DIR = __DIR__ . '/../../../../library/smarty/plugins';

    protected function setUp(): void
    {
        // Make xl() a pass-through so we test the escaping behavior of the
        // Smarty plugins in isolation, without touching the DB or the
        // translation cache.
        $GLOBALS['disable_translation'] = true;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['disable_translation']);
    }

    private function loadPlugin(string $file): void
    {
        require_once self::PLUGINS_DIR . '/' . $file;
    }

    // -- function plugins -----------------------------------------------------

    /**
     * @return array<string, array{string, string, string, string}>
     *     [$functionName, $file, $label, $expectedOutput]
     */
    public static function functionPluginProvider(): array
    {
        return [
            'xlt' => ['smarty_function_xlt', 'function.xlt.php', 'xlt', 'Hello &amp; goodbye'],
            'xla' => ['smarty_function_xla', 'function.xla.php', 'xla', 'Hello &amp; goodbye'],
            'xlj' => ['smarty_function_xlj', 'function.xlj.php', 'xlj', '"Hello & goodbye"'],
        ];
    }

    /**
     * @param callable-string $functionName
     */
    #[DataProvider('functionPluginProvider')]
    public function testFunctionPluginEchoesTranslatedValue(
        string $functionName,
        string $file,
        string $label,
        string $expectedOutput
    ): void {
        $this->loadPlugin($file);

        $this->expectOutputString($expectedOutput);

        $smarty = null;
        $functionName(['t' => 'Hello & goodbye'], $smarty);
    }

    /**
     * @param callable-string $functionName
     */
    #[DataProvider('functionPluginProvider')]
    public function testFunctionPluginWarnsOnMissingTParam(
        string $functionName,
        string $file,
        string $label
    ): void {
        $this->loadPlugin($file);

        $this->expectOutputString('');
        $captured = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$captured): bool {
            $captured = [$errno, $errstr];
            return true;
        });

        try {
            $smarty = null;
            $functionName([], $smarty);
        } finally {
            restore_error_handler();
        }

        $this->assertNotNull($captured, "[$label] should trigger a warning when 't' is missing");
        $this->assertSame(E_USER_WARNING, $captured[0]);
        $this->assertStringContainsString($label, $captured[1]);
        $this->assertStringContainsString("'t'", $captured[1]);
    }

    /**
     * @param callable-string $functionName
     */
    #[DataProvider('functionPluginProvider')]
    public function testFunctionPluginWarnsOnNonStringTParam(
        string $functionName,
        string $file,
        string $label
    ): void {
        $this->loadPlugin($file);

        $this->expectOutputString('');
        $captured = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$captured): bool {
            $captured = [$errno, $errstr];
            return true;
        });

        try {
            $smarty = null;
            // Truthy non-string: clears the empty() guard but trips is_string().
            $functionName(['t' => ['not', 'a', 'string']], $smarty);
        } finally {
            restore_error_handler();
        }

        $this->assertNotNull($captured, "[$label] should trigger a warning for non-string 't'");
        $this->assertSame(E_USER_WARNING, $captured[0]);
    }

    // -- modifier plugins -----------------------------------------------------

    public function testSmartyModifierXltEscapesAndTranslates(): void
    {
        $this->loadPlugin('modifier.xlt.php');

        $this->assertSame('Hello &amp; goodbye', smarty_modifier_xlt('Hello & goodbye'));
    }

    public function testSmartyModifierXltReturnsEmptyStringForNull(): void
    {
        $this->loadPlugin('modifier.xlt.php');

        $this->assertSame('', smarty_modifier_xlt(null));
    }

    public function testSmartyModifierXlaEscapesAndTranslates(): void
    {
        $this->loadPlugin('modifier.xla.php');

        // xla() uses ENT_QUOTES so double quotes become &quot;.
        $this->assertSame('&quot;Hi&quot;', smarty_modifier_xla('"Hi"'));
    }

    public function testSmartyModifierXlaReturnsEmptyStringForNull(): void
    {
        $this->loadPlugin('modifier.xla.php');

        $this->assertSame('', smarty_modifier_xla(null));
    }
}
