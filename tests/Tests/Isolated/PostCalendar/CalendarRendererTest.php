<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use OpenEMR\Core\Header;
use OpenEMR\PostCalendar\CalendarRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarRendererTest extends TestCase
{
    private mixed $versionBackup;

    protected function setUp(): void
    {
        $this->versionBackup = $GLOBALS['v_js_includes'] ?? null;
        $GLOBALS['v_js_includes'] = 'test-version';
    }

    protected function tearDown(): void
    {
        if ($this->versionBackup === null) {
            unset($GLOBALS['v_js_includes']);
        } else {
            $GLOBALS['v_js_includes'] = $this->versionBackup;
        }
    }

    /**
     * @param  array<string, string> $templates
     */
    private function buildRenderer(array $templates = []): CalendarRenderer
    {
        return new CalendarRenderer(new Environment(new ArrayLoader($templates)));
    }

    public function testAssignThenGetVarRoundTripsValue(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign('foo', 42);
        self::assertSame(42, $renderer->getVar('foo'));
    }

    public function testGetVarReturnsNullForUnknownKey(): void
    {
        self::assertNull($this->buildRenderer()->getVar('missing'));
    }

    public function testAssignAcceptsAssociativeArray(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign(['a' => 1, 'b' => 'two', 'c' => [3]]);

        self::assertSame(1, $renderer->getVar('a'));
        self::assertSame('two', $renderer->getVar('b'));
        self::assertSame([3], $renderer->getVar('c'));
    }

    public function testAssignIgnoresEmptyStringKey(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign('', 'discarded');
        self::assertNull($renderer->getVar(''));
    }

    public function testAssignArrayFormSkipsEmptyStringKey(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign(['' => 'discarded', 'kept' => 'value']);

        self::assertNull($renderer->getVar(''));
        self::assertSame('value', $renderer->getVar('kept'));
    }

    public function testAssignByRefMirrorsAssign(): void
    {
        $renderer = $this->buildRenderer();
        $value = 'initial';
        $renderer->assign_by_ref('shared', $value);

        // The wrapper copies the value at call-time; mutating $value after
        // assignment doesn't affect what the renderer holds, because Twig
        // copies values when rendering anyway.
        $value = 'mutated';
        self::assertSame('initial', $renderer->getVar('shared'));
    }

    public function testAssignOverwritesPreviousValueForSameKey(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign('k', 'first');
        $renderer->assign('k', 'second');
        self::assertSame('second', $renderer->getVar('k'));
    }

    public function testRenderInterpolatesAssignedVariablesIntoTemplate(): void
    {
        $renderer = $this->buildRenderer([
            'greeting.twig' => 'Hello, {{ name }}!',
        ]);
        $renderer->assign('name', 'World');

        self::assertSame('Hello, World!', $renderer->render('greeting.twig'));
    }

    public function testRenderUsesNamedTemplateNotMostRecentlyAssigned(): void
    {
        $renderer = $this->buildRenderer([
            'a.twig' => 'A says {{ msg }}',
            'b.twig' => 'B says {{ msg }}',
        ]);
        $renderer->assign('msg', 'hi');

        self::assertSame('A says hi', $renderer->render('a.twig'));
        self::assertSame('B says hi', $renderer->render('b.twig'));
    }

    public function testRenderReceivesAccumulatedAssignmentsAcrossCalls(): void
    {
        $renderer = $this->buildRenderer([
            't.twig' => '{{ a }}/{{ b }}/{{ c }}',
        ]);
        $renderer->assign('a', '1');
        $renderer->assign(['b' => '2', 'c' => '3']);

        self::assertSame('1/2/3', $renderer->render('t.twig'));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function calendarViewProvider(): array
    {
        return [
            'month screen' => ['month'],
            'week screen' => ['week'],
            'day screen' => ['day'],
            'month print' => ['month_print'],
            'week print' => ['week_print'],
            'day print' => ['day_print'],
        ];
    }

    #[DataProvider('calendarViewProvider')]
    public function testEveryCalendarViewRendersSharedExtensionAssetsOnce(string $view): void
    {
        $partial = (string) file_get_contents(
            dirname(__DIR__, 4) . '/templates/calendar/default/views/_extension_assets.html.twig'
        );
        $renderer = $this->buildRenderer([
            'calendar/default/views/_extension_assets.html.twig' => $partial,
            $view . '.twig' => "<head>{% include 'calendar/default/views/_extension_assets.html.twig' %}</head>",
        ]);
        $renderer->assign('CALENDAR_EXTENSION_ASSETS', Header::createModuleAssetElements(
            ['/modules/example/calendar.js?mode=compact&name="quoted"'],
            ['/modules/example/calendar.css?theme=light&name="quoted"']
        ));

        $html = $renderer->render($view . '.twig');

        self::assertSame(1, substr_count($html, '<!-- Module Scripts Started -->'));
        self::assertSame(1, substr_count($html, '<!-- Module Styles Started -->'));
        self::assertStringContainsString(
            '<script src="/modules/example/calendar.js?mode=compact&amp;name=&quot;quoted&quot;&amp;v=test-version"></script>',
            $html
        );
        self::assertStringContainsString(
            '<link rel="stylesheet"  href="/modules/example/calendar.css?theme=light&amp;name=&quot;quoted&quot;&amp;v=test-version" />',
            $html
        );
    }

    public function testEmptyExtensionListsRenderNoMarkup(): void
    {
        $renderer = $this->buildRenderer([
            'assets.twig' => "before{% include 'calendar/default/views/_extension_assets.html.twig' %}after",
            'calendar/default/views/_extension_assets.html.twig' => (string) file_get_contents(
                dirname(__DIR__, 4) . '/templates/calendar/default/views/_extension_assets.html.twig'
            ),
        ]);
        $renderer->assign('CALENDAR_EXTENSION_ASSETS', Header::createModuleAssetElements([], []));

        self::assertSame('beforeafter', trim($renderer->render('assets.twig')));
    }

    public function testCalendarEntryPointPreservesDispatchContextAndPropagatesAssets(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/interface/main/calendar/modules/PostCalendar/pnuserapi.php'
        );

        self::assertSame(1, substr_count($source, "new ScriptFilterEvent('pnuserapi.php')"));
        self::assertSame(1, substr_count($source, "new StyleFilterEvent('pnuserapi.php')"));
        self::assertSame(2, substr_count($source, "setContextArgument('viewtype', \$viewtype)"));
        self::assertStringContainsString('Header::createModuleAssetElements(', $source);
        self::assertStringContainsString('$calendarScripts->getScripts()', $source);
        self::assertStringContainsString('$calendarStyles->getStyles()', $source);

        foreach (['header', 'month_print/outlook_ajax_template', 'week_print/outlook_ajax_template', 'day_print/outlook_ajax_template'] as $template) {
            $templateSource = (string) file_get_contents(
                dirname(__DIR__, 4) . '/templates/calendar/default/views/' . $template . '.html.twig'
            );
            self::assertSame(1, substr_count($templateSource, "include 'calendar/default/views/_extension_assets.html.twig'"));
        }
    }
}
