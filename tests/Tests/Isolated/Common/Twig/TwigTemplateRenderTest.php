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
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Twig;

use OpenEMR\Common\Twig\TwigContainer;
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
                'target'                 => 'https://example.com/telehealth',
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
                'target'                 => 'https://example.com/telehealth',
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
