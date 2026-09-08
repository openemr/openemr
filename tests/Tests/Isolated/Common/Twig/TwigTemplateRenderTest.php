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

        // The dashboard preference/care-team cards render the Edit pencil with
        // linkMethod 'javascript': the href stays '#' and the expression goes
        // in an onclick. A literal 'javascript:' href would be stripped to '#'
        // by |safe_href and lose the behavior entirely.
        yield 'patient/card/appointments edit button via javascript linkMethod' => [
            'patient/card/appointments.html.twig',
            [
                'title'               => 'Appointments',
                'id'                  => 'appointments_ps_expand',
                'initiallyCollapsed'  => false,
                'btnLabel'            => 'Edit',
                'btnClass'            => 'js-card-toggle-edit',
                'btnLink'             => 'event.preventDefault();',
                'linkMethod'          => 'javascript',
                'appts'               => [],
                'recurrAppts'         => [],
                'pastAppts'           => [],
                'displayAppts'        => false,
                'displayRecurrAppts'  => false,
                'displayPastAppts'    => false,
                'extraApptDate'       => '',
                'therapyGroupCategories' => [],
                'auth'                => true,
                'resNotNull'          => false,
            ],
            $fixtureDir . '/appointments-edit-javascript-link.html',
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

        // Install Code Set page. The first case covers the post-upload render (messages of both
        // types, a selected code type, the replace checkbox reflecting an unchecked submission and
        // the RXCUI help paragraph); the second covers a module-only install where core's own
        // importers have been filtered out.
        yield 'super/load_codes with messages' => [
            'super/load_codes.html.twig',
            [
                'csrfToken'          => 'test-csrf-token',
                'messages'           => [
                    'success' => ['Code set load successful.', 'Codes inserted: 12, codes updated: 3'],
                    'error'   => ['The code set could not be imported. Check the system log for details.'],
                ],
                'supportedCodeTypes' => ['RXCUI', 'LOINC', 'ICPC2'],
                'selectedCodeType'   => 'LOINC',
                'formReplace'        => false,
                'maxFileSize'        => 350000000,
                'showRxcuiHelp'      => true,
            ],
            $fixtureDir . '/load-codes-with-messages.html',
        ];

        yield 'super/load_codes without rxcui' => [
            'super/load_codes.html.twig',
            [
                'csrfToken'          => 'test-csrf-token',
                'messages'           => [],
                'supportedCodeTypes' => ['ICPC2'],
                'selectedCodeType'   => '',
                'formReplace'        => true,
                'maxFileSize'        => 350000000,
                'showRxcuiHelp'      => false,
            ],
            $fixtureDir . '/load-codes-no-rxcui.html',
        ];

        $reasonCodeStatii = [
            '' => ['code' => '', 'description' => 'Select a status code'],
            'negated' => ['code' => 'negated', 'description' => 'Negated'],
        ];

        yield 'forms/care_plan reason row (empty)' => [
            '/forms/care_plan/templates/partials/_reason_row.html.twig',
            [
                'row' => [],
                'rowIndex' => 1,
                'reasonCodeStatii' => $reasonCodeStatii,
            ],
            $fixtureDir . '/care-plan-reason-row-empty.html',
        ];

        yield 'forms/care_plan reason row (populated)' => [
            '/forms/care_plan/templates/partials/_reason_row.html.twig',
            [
                'row' => [
                    'reason_code' => 'SNOMED-CT:183932001',
                    'reason_description' => 'Procedure contraindicated',
                    'reason_status' => 'negated',
                    'reason_date_low' => '2026-01-05 09:00',
                    'reason_date_high' => '2026-02-05 09:00',
                ],
                'rowIndex' => 2,
                'reasonCodeStatii' => $reasonCodeStatii,
            ],
            $fixtureDir . '/care-plan-reason-row-populated.html',
        ];

        yield 'forms/care_plan row actions' => [
            '/forms/care_plan/templates/partials/_actions.html.twig',
            ['rowIndex' => 1],
            $fixtureDir . '/care-plan-actions.html',
        ];

        yield 'forms/care_plan report' => [
            '/forms/care_plan/templates/care_plan_report.html.twig',
            [
                'rows' => [
                    [
                        'user' => 'admin',
                        'care_plan_type' => 'plan_of_care',
                        'plan_engagement_category' => 'active',
                        'code' => 'SNOMED-CT:168731009',
                        'codetext' => 'Standard chest x-ray',
                        'description' => "First line\nSecond line",
                        'date' => '2026-01-05 09:00:00',
                    ],
                ],
            ],
            $fixtureDir . '/care-plan-report.html',
        ];

        yield 'patient/card care plan empty' => [
            'patient/card/care_plan.html.twig',
            [
                'id' => 'card_care_plan',
                'title' => 'Care Plan',
                'initiallyCollapsed' => false,
                'forceAlwaysOpen' => false,
                'auth' => false,
                'card_bg_color' => '',
                'card_text_color' => '',
                'pid' => 1,
                'rows' => [],
                'mostRecentDate' => null,
                'encounter' => null,
            ],
            $fixtureDir . '/care-plan-card-empty.html',
        ];

        yield 'patient/card care plan populated' => [
            'patient/card/care_plan.html.twig',
            [
                'id' => 'card_care_plan',
                'title' => 'Care Plan',
                'initiallyCollapsed' => false,
                'forceAlwaysOpen' => false,
                'auth' => false,
                'card_bg_color' => '',
                'card_text_color' => '',
                'pid' => 1,
                'rows' => [
                    [
                        'user' => 'admin',
                        'care_plan_type' => 'plan_of_care',
                        'code' => 'SNOMED-CT:168731009',
                        'codetext' => 'Standard chest x-ray',
                        'description' => "First line\nSecond line",
                        'date' => '2026-01-05 09:00:00',
                    ],
                ],
                'mostRecentDate' => '2026-01-05',
                'encounter' => 12,
            ],
            $fixtureDir . '/care-plan-card-populated.html',
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

        // Also load interface/ so encounter form templates resolve under the same
        // names they use in production, e.g. /forms/care_plan/templates/x.html.twig.
        $twigContainer = new TwigContainer(self::fileroot() . '/interface');
        $twig = $twigContainer->getTwig();

        // getListItemTitle() reads list_options from the database. Stub it so
        // templates resolving list values render isolated, with the lookup visible
        // in the fixture as [list_id:option_id].
        $twig->addFunction(new TwigFunction(
            'getListItemTitle',
            fn (string $listId, ?string $optionId): string => '[' . $listId . ':' . ($optionId ?? '') . ']',
        ));

        // Override setupHeader() before the first render initializes extensions.
        // The real function requires $kernel for event dispatching; the stub
        // returns an HTML comment so templates that extend base.html.twig
        // render without the full application bootstrap, and the fixture files
        // show exactly where the real function's output would appear.
        $twig->addFunction(new TwigFunction(
            'setupHeader',
            fn (): string => '<!-- setupHeader stub -->',
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
