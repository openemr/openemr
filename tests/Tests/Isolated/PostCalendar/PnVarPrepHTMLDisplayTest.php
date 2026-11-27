<?php

/**
 * pnVarPrepHTMLDisplay() output for input carrying its internal tag markers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class PnVarPrepHTMLDisplayTest extends TestCase
{
    private bool $pnconfigWasSet = false;
    private mixed $originalPnconfig = null;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../../interface/main/calendar/includes/pnAPI.php';
        $this->pnconfigWasSet = array_key_exists('pnconfig', $GLOBALS);
        $this->originalPnconfig = $GLOBALS['pnconfig'] ?? null;
        // Pre-seed the config cache so pnConfigGetVar() does not reach for a DB.
        $GLOBALS['pnconfig'] = ['htmlentities' => 0];
    }

    protected function tearDown(): void
    {
        if ($this->pnconfigWasSet) {
            $GLOBALS['pnconfig'] = $this->originalPnconfig;
        } else {
            unset($GLOBALS['pnconfig']);
        }
    }

    #[DataProvider('markerInputProvider')]
    public function testMarkerBytesInInputDoNotProduceTags(string $input, string $expected): void
    {
        $output = pnVarPrepHTMLDisplay($input);
        self::assertIsString($output);
        self::assertSame($expected, $output);
        self::assertStringNotContainsString('<', $output);
    }

    public function testPlainTextIsHtmlEncoded(): void
    {
        self::assertSame('a &lt;b&gt; &amp; c', pnVarPrepHTMLDisplay('a <b> & c'));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function markerInputProvider(): array
    {
        return [
            'img tag between markers' => [
                "\022img src=x alt=y\024",
                'img src=x alt=y',
            ],
            'markers around quoted attribute' => [
                "note \022b title=&quot;x&quot;\024 end",
                'note b title=&amp;quot;x&amp;quot; end',
            ],
            'stray single markers' => [
                "a\022b\024c",
                'abc',
            ],
        ];
    }
}
