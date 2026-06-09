<?php

/**
 * Render Twig templates with known parameters and compare output to fixtures.
 *
 * The compilation test (TwigTemplateCompilationTest) verifies that every
 * template parses and references valid filters/functions, but never renders
 * templates with actual data. This test fills that gap: it renders real
 * templates with fixture data and asserts the full HTML output matches
 * an expected file, catching structural bugs like wrong attributes, missing
 * prefixes, or broken escaping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Twig;

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\PostCalendar\PostCalendarTwigExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\TwigFunction;

#[Group('isolated')]
#[Group('twig')]
class TwigTemplateRenderTest extends TestCase
{
    private static ?Environment $twig = null;

    protected function setUp(): void
    {
        $GLOBALS['fileroot'] ??= self::fileroot();
        $GLOBALS['date_display_format'] ??= 0;
        // Bypass database-dependent translation lookups so xl() returns the
        // original string and xlt()/xla() apply only escaping.
        $GLOBALS['disable_translation'] = true;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('renderCaseProvider')]
    public function templateRendersExpectedOutput(string $templateName, array $parameters, string $fixturePath): void
    {
        $twig = self::twigEnvironment();
        // Normalize immediately so fixtures and comparisons use the same form.
        // Twig's block processing leaves trailing whitespace on empty lines;
        // stripping it here keeps fixture files clean for pre-commit hooks.
        $rendered = self::normalizeTrailingWhitespace(
            $twig->render($templateName, $parameters)
        );

        // @codeCoverageIgnoreStart
        if (getenv('UPDATE_FIXTURES') === '1') {
            file_put_contents($fixturePath, $rendered);
            self::markTestSkipped("Fixture updated: $fixturePath");
        }
        // @codeCoverageIgnoreEnd

        $expected = file_get_contents($fixturePath);
        self::assertIsString($expected, "Failed to read fixture: $fixturePath");
        self::assertSame(
            $expected,
            $rendered,
            "Rendered output does not match fixture: $fixturePath\n"
            . "If you modified this template, update fixtures with: composer update-twig-fixtures\n"
            . "Review the changes with `git diff` before committing."
        );
    }

    /**
     * Provide [templateName, parameters, fixturePath] for each render test case.
     *
     * To add a new test case:
     * 1. Add a yield below with the template name, parameters, and fixture path.
     * 2. Generate the expected output file:
     *    composer update-twig-fixtures
     * 3. Review the generated fixture with `git diff` and commit it.
     *
     * @return iterable<string, array{string, array<string, mixed>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function renderCaseProvider(): iterable
    {
        $fixtureDir = __DIR__ . '/fixtures/render';

        yield 'portal/partial/_nav_icon local link (defaults)' => [
            'portal/partial/_nav_icon.html.twig',
            [
                'id'      => 'test-nav',
                'url'     => 'testSection',
                'navText' => 'Test Nav',
                'icon'    => 'home',
            ],
            $fixtureDir . '/nav-icon-local-link.html',
        ];

        yield 'portal/partial/_nav_icon external link' => [
            'portal/partial/_nav_icon.html.twig',
            [
                'id'        => 'test-nav',
                'url'       => 'https://example.com',
                'navText'   => 'External',
                'icon'      => 'globe',
                'localLink' => false,
            ],
            $fixtureDir . '/nav-icon-external-link.html',
        ];

        yield 'oauth2/ehr-launch-autosubmit' => [
            'oauth2/ehr-launch-autosubmit.html.twig',
            [
                'endpoint' => '/oauth2/launch',
            ],
            $fixtureDir . '/ehr-launch-autosubmit.html',
        ];

        yield 'portal/login/autologin pin required' => [
            'portal/login/autologin.html.twig',
            [
                'pagetitle'              => 'Telehealth Login',
                'images_static_relative' => '/public/images',
                'pin_required'           => 1,
                'action'                 => '/portal/autologin',
                'csrf_token'             => 'test-csrf-token',
                'service_auth'           => 'test-auth-value',
            ],
            $fixtureDir . '/autologin-pin-required.html',
        ];

        yield 'portal/login/autologin no pin' => [
            'portal/login/autologin.html.twig',
            [
                'pagetitle'              => 'Telehealth Login',
                'images_static_relative' => '/public/images',
                'pin_required'           => false,
                'action'                 => '/portal/autologin',
                'csrf_token'             => 'test-csrf-token',
                'service_auth'           => 'test-auth-value',
            ],
            $fixtureDir . '/autologin-no-pin.html',
        ];

        // Appointments card test cases - verify display flag behavior
        // When user lacks permission, demographics.php doesn't render the card at all.
        // These tests verify the template correctly handles the display flags.

        yield 'patient/card/appointments all sections hidden' => [
            'patient/card/appointments.html.twig',
            [
                'title'               => 'Appointments',
                'id'                  => 'appointments_ps_expand',
                'initiallyCollapsed'  => false,
                'btnLabel'            => 'Add',
                'btnLink'             => 'return newEvt()',
                'linkMethod'          => 'javascript',
                'appts'               => [],
                'recurrAppts'         => [],
                'pastAppts'           => [],
                'displayAppts'        => false,
                'displayRecurrAppts'  => false,
                'displayPastAppts'    => false,
                'extraApptDate'       => '',
                'therapyGroupCategories' => [],
                'auth'                => false,
                'resNotNull'          => false,
            ],
            $fixtureDir . '/appointments-all-hidden.html',
        ];

        yield 'patient/card/appointments future only with empty list' => [
            'patient/card/appointments.html.twig',
            [
                'title'               => 'Appointments',
                'id'                  => 'appointments_ps_expand',
                'initiallyCollapsed'  => false,
                'btnLabel'            => 'Add',
                'btnLink'             => 'return newEvt()',
                'linkMethod'          => 'javascript',
                'appts'               => [],
                'recurrAppts'         => [],
                'pastAppts'           => [],
                'displayAppts'        => true,
                'displayRecurrAppts'  => false,
                'displayPastAppts'    => false,
                'extraApptDate'       => '',
                'therapyGroupCategories' => [],
                'auth'                => true,
                'resNotNull'          => true,
            ],
            $fixtureDir . '/appointments-future-empty.html',
        ];

        // Calendar render cases — see CalendarRenderDataBuilder for the
        // shape each template iterates. Print views are tested with empty
        // events; the per-event content path is unit-covered by
        // CalendarRenderDataBuilderTest. Screen views are tested with
        // empty events because their per-event decoration runs through
        // dateformat() (DB-dependent).
        $emptyMini = ['monthLabel' => 'March 2026', 'weeks' => []];
        $defaultDowList = [0, 1, 2, 3, 4, 5, 6];
        $defaultDayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        yield 'calendar month_print empty' => [
            'calendar/default/views/month_print/outlook_ajax_template.html.twig',
            [
                'providers'         => [['id' => 1, 'fname' => 'Alice', 'lname' => 'Smith']],
                'dowList'           => $defaultDowList,
                'A_SHORT_DAY_NAMES' => $defaultDayNames,
                'dateLabel'         => 'March 2026',
                'dayHeaderDates'    => [],
                'currentMonthMini'  => $emptyMini,
                'nextMonthMini'     => ['monthLabel' => 'April 2026', 'weeks' => []],
                'A_EVENTS'          => [],
                'dowOfDate'         => [],
            ],
            $fixtureDir . '/calendar-month-print-empty.html',
        ];

        yield 'calendar week_print empty' => [
            'calendar/default/views/week_print/outlook_ajax_template.html.twig',
            [
                'providers'         => [['id' => 1, 'fname' => 'Alice', 'lname' => 'Smith', 'dayPairs' => []]],
                'dowList'           => $defaultDowList,
                'A_SHORT_DAY_NAMES' => $defaultDayNames,
                'dateRange'         => ['firstMonth' => 'March', 'firstDay' => '15', 'lastMonth' => 'March', 'lastDay' => '21'],
                'currentMonthMini'  => $emptyMini,
                'nextMonthMini'     => ['monthLabel' => 'April 2026', 'weeks' => []],
            ],
            $fixtureDir . '/calendar-week-print-empty.html',
        ];

        yield 'calendar day_print empty' => [
            'calendar/default/views/day_print/outlook_ajax_template.html.twig',
            [
                'providers'         => [['id' => 1, 'fname' => 'Alice', 'lname' => 'Smith', 'events' => []]],
                'dowList'           => $defaultDowList,
                'A_SHORT_DAY_NAMES' => $defaultDayNames,
                'dateHeader'        => ['dateLabel' => '15 March 2026', 'weekdayLabel' => 'Sunday'],
                'currentMonthMini'  => $emptyMini,
                'nextMonthMini'     => ['monthLabel' => 'April 2026', 'weeks' => []],
                'timeRows'          => [],
                'timeslotCss'       => '20px',
            ],
            $fixtureDir . '/calendar-day-print-empty.html',
        ];

        // Screen views share a large set of chrome variables (nav URLs,
        // chevron icons, monthSelectorHtml, facility picker, provider
        // picker). Empty providersGrid / dayColumns + empty facilities
        // / provinfo render the page chrome only — the per-event paths
        // are covered by CalendarRenderDataBuilderTest.
        $screenCommon = [
            'dowList'                 => $defaultDowList,
            'A_SHORT_DAY_NAMES'       => $defaultDayNames,
            'prevMonth'               => '20260201',
            'nextMonth'               => '20260401',
            'prevMonthName'           => 'February',
            'nextMonthName'           => 'April',
            'currentMiniCal'          => $emptyMini,
            'monthSelectorHtml'       => '<select id="monthPicker"></select>',
            'showFacilitySelect'      => false,
            'showAllFacilitiesOption' => true,
            'pc_facility'             => 0,
            'facilities'              => [],
            'provinfo'                => [],
            'selectedUsernames'       => [],
            'chevron_icon_left'       => 'fa-chevron-left',
            'chevron_icon_right'      => 'fa-chevron-right',
            'isToday'                 => false,
            'webroot'                 => '',
            'body_class'              => '',
        ];

        yield 'calendar month-screen empty' => [
            'calendar/default/views/month/ajax_template.html.twig',
            array_merge($screenCommon, [
                'viewtype'           => 'month',
                'Date'               => '20260315',
                'currentMonthLabel'  => 'March 2026',
                'PREV_MONTH_URL'     => '?prev',
                'NEXT_MONTH_URL'     => '?next',
                'providersGrid'      => [],
            ]),
            $fixtureDir . '/calendar-month-screen-empty.html',
        ];

        yield 'calendar day-screen empty' => [
            'calendar/default/views/day/ajax_template.html.twig',
            array_merge($screenCommon, [
                'viewtype'        => 'day',
                'Date'            => '20260315',
                'dayHeaderLabel'  => 'Sunday March 15 2026',
                'PREV_DAY_URL'    => '?prev',
                'NEXT_DAY_URL'    => '?next',
                'timeRows'        => [],
                'timeslotCss'     => '20px',
                'providers'       => [],
            ]),
            $fixtureDir . '/calendar-day-screen-empty.html',
        ];

        yield 'calendar week-screen empty' => [
            'calendar/default/views/week/ajax_template.html.twig',
            array_merge($screenCommon, [
                'viewtype'        => 'week',
                'Date'            => '20260315',
                'weekHeaderLabel' => 'Mar 15 - Mar 21 2026',
                'PREV_WEEK_URL'   => '?prev',
                'NEXT_WEEK_URL'   => '?next',
                'timeRows'        => [],
                'timeslotCss'     => '20px',
                'providers'       => [],
            ]),
            $fixtureDir . '/calendar-week-screen-empty.html',
        ];

        yield 'patient/card/appointments with future appointments' => [
            'patient/card/appointments.html.twig',
            [
                'title'               => 'Appointments',
                'id'                  => 'appointments_ps_expand',
                'initiallyCollapsed'  => false,
                'btnLabel'            => 'Add',
                'btnLink'             => 'return newEvt()',
                'linkMethod'          => 'javascript',
                'appts'               => [
                    [
                        'pc_catid'      => 5,
                        'pc_catname'    => 'Office Visit',
                        'pc_hometext'   => '',
                        'pc_recurrtype' => 0,
                        'jsEvent'       => '123,456',
                        'dayName'       => 'Monday',
                        'pc_eventDate'  => '2026-03-15',
                        'pc_eventTime'  => '10:00',
                        'displayMeridiem' => 'AM',
                        'uname'         => 'Dr. Smith',
                        'pc_status'     => '-',
                        'bgColor'       => '#ffffff',
                    ],
                ],
                'recurrAppts'         => [],
                'pastAppts'           => [],
                'displayAppts'        => true,
                'displayRecurrAppts'  => false,
                'displayPastAppts'    => false,
                'extraApptDate'       => '',
                'therapyGroupCategories' => [],
                'auth'                => true,
                'resNotNull'          => true,
            ],
            $fixtureDir . '/appointments-with-future.html',
        ];
    }

    /**
     * Build and cache the Twig environment with stubs for isolated render testing.
     *
     * Stubs setupHeader() because the real implementation needs the kernel and
     * event dispatcher, which aren't available in isolated tests. Render tests
     * verify template structure, not header generation.
     *
     */
    private static function twigEnvironment(): Environment
    {
        if (self::$twig !== null) {
            return self::$twig;
        }

        $GLOBALS['fileroot'] ??= self::fileroot();
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;

        $twigContainer = new TwigContainer();
        $twig = $twigContainer->getTwig();

        // Override setupHeader() before the first render initializes extensions.
        // The real function requires $kernel for event dispatching; the stub
        // returns an HTML comment so templates that extend base.html.twig
        // render without the full application bootstrap, and the fixture files
        // show exactly where the real function's output would appear.
        $twig->addFunction(new TwigFunction(
            'setupHeader',
            fn () => '<!-- setupHeader stub -->',
            ['is_safe' => ['html']]
        ));

        // PostCalendar templates use pc_sort_events and
        // pc_event_time_anchor — register the extension that supplies
        // them so calendar render cases parse and render correctly.
        $twig->addExtension(new PostCalendarTwigExtension());

        self::$twig = $twig;
        return $twig;
    }

    /**
     * Strip trailing whitespace from each line.
     *
     */
    private static function normalizeTrailingWhitespace(string $text): string
    {
        return implode("\n", array_map(rtrim(...), explode("\n", $text)));
    }

    /** @codeCoverageIgnore */
    private static function fileroot(): string
    {
        return dirname(__DIR__, 5);
    }
}
