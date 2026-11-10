<?php

/**
 * Render the portal profile page (portal/patient/patientdata) from its Twig template with known
 * variables and compare the HTML to recorded fixtures.
 *
 * Regenerate the fixtures after an intended template change with
 * `UPDATE_FIXTURES=1 vendor/bin/phpunit -c phpunit-isolated.xml tests/Tests/Isolated/Portal/Twig`
 * and review the diff.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal\Twig;

use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Controllers\Portal\Patient\TwigPageRenderer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PortalProfile\PortalProfileService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

#[Group('isolated')]
#[Group('twig')]
final class PatientListViewRenderTest extends TestCase
{
    private const GLOBALS = ['date_display_format', 'disable_translation'];

    /** @var array<string, array{present: bool, value: mixed}> */
    private array $globalsSnapshot = [];

    /**
     * Saves the globals the templates read and sets the ones rendering needs, with translation off.
     */
    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach (self::GLOBALS as $key) {
            $this->globalsSnapshot[$key] = ['present' => $globals->has($key), 'value' => $globals->get($key)];
        }
        if (!$globals->has('date_display_format')) {
            $globals->set('date_display_format', 0);
        }
        // xl() returns the source string and xlt()/xla() only escape.
        $globals->set('disable_translation', true);
    }

    /**
     * Puts the saved globals back, removing any that were not set before.
     */
    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach ($this->globalsSnapshot as $key => $original) {
            if ($original['present']) {
                $globals->set($key, $original['value']);
                continue;
            }
            $globals->remove($key);
            unset($GLOBALS[$key]);
        }
        $this->globalsSnapshot = [];
    }

    /**
     * @param array<string, mixed> $vars
     */
    #[Test]
    #[DataProvider('pageProvider')]
    public function pageRendersExpectedOutput(array $vars, string $fixture): void
    {
        $renderer = new TwigPageRenderer(self::twig(), [
            'ROOT_URL' => '/openemr/portal/patient/',
            'web_root' => '/openemr',
            'assets_static_relative' => '/openemr/public/assets',
            'v_js_includes' => 82,
            'is_portal_session' => false,
            'software_version' => '8.1.0',
            'current_year' => '2026',
        ]);
        $rendered = implode("\n", array_map(rtrim(...), explode("\n", $renderer->render('PatientListView', $vars))));
        $fixturePath = __DIR__ . '/fixtures/' . $fixture;

        // @codeCoverageIgnoreStart
        if (getenv('UPDATE_FIXTURES') === '1') {
            file_put_contents($fixturePath, $rendered);
            self::markTestSkipped('Fixture updated: ' . $fixturePath);
        }
        // @codeCoverageIgnoreEnd

        $expected = file_get_contents($fixturePath);
        self::assertIsString($expected, 'Failed to read fixture: ' . $fixturePath);
        self::assertSame($expected, $rendered, 'Rendered output does not match fixture: ' . $fixturePath);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pageProvider(): array
    {
        $emptyRow = array_fill_keys(PortalProfileService::PROFILE_KEYS, null);
        $providers = [
            ['id' => 4, 'username' => 'davis', 'fname' => 'Ann', 'lname' => 'Davis'],
            ['id' => 5, 'username' => 'obrien', 'fname' => 'Sean', 'lname' => "O'Brien & <Partners>"],
            ['id' => 9, 'username' => 'lee', 'fname' => 'Kim', 'lname' => 'Lee'],
        ];
        $page = [
            'encounter' => 0,
            'exclude' => ['suffixInputContainer', 'refProvideridInputContainer'],
            'users_list' => $providers,
            'select_lists' => [
                'title' => '<select name="title" id="title" class="form-control form-control"><option value="Mr." selected>Mr.</option></select>',
                'sex' => '<select name="sex" id="sex" class="form-control"><option value="Male" selected>Male</option></select>',
                'status' => '<select name="status" id="status" class="form-control form-control"><option value="married" selected>Married</option></select>',
                'state' => '<select name="state" id="state" class="form-control form-control"><option value="CA" selected>California</option></select>',
                'race' => '<select name="race" id="race" class="form-control form-control"><option value="white" selected>White</option></select>',
                'ethnicity' => '<select name="ethnicity" id="ethnicity" class="form-control form-control"><option value="" selected>Unassigned</option></select>',
                'religion' => '<select name="religion" id="religion" class="form-control form-control"><option value="" selected>Unassigned</option></select>',
            ],
            'datetimepicker_js' => "\n//datepicker elements\nvar datepicker_format = 'Y-m-d';\nvar datetimepicker_format = 'Y-m-d H:i:s';\n",
        ];

        return [
            'staff viewing a patient' => [
                [
                    'recid' => 12,
                    'cpid' => 7,
                    'cuser' => '1',
                    'register' => '0',
                    'trow' => array_merge($emptyRow, [
                        'id' => '12',
                        'pid' => '7',
                        'fname' => 'Jane',
                        'lname' => 'Doe',
                        'providerid' => '5',
                        'refProviderid' => '9',
                    ]),
                ] + $page,
                'patient-list-view-staff.html',
            ],
            'registration' => [
                [
                    'recid' => 0,
                    'cpid' => 0,
                    'cuser' => 0,
                    'register' => '1',
                    'trow' => $emptyRow,
                ] + $page,
                'patient-list-view-register.html',
            ],
            'request values that need escaping' => [
                [
                    'recid' => 0,
                    'cpid' => 7,
                    'cuser' => "1';alert(1)//",
                    'register' => '0',
                    'trow' => array_merge($emptyRow, [
                        'providerid' => '"><script>alert(1)</script>',
                        'refProviderid' => "9' x='",
                    ]),
                ] + $page,
                'patient-list-view-escaping.html',
            ],
        ];
    }

    private static function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 5) . '/templates'), ['autoescape' => false]);
        $twig->addExtension(new TwigExtension(OEGlobalsBag::getInstance(), null));
        // The real functions need the application bootstrap and a live session.
        $twig->addFunction(new TwigFunction('setupHeader', fn(): string => '<!-- setupHeader stub -->', ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('csrfTokenRaw', fn(): string => 'test-csrf-token'));

        return $twig;
    }
}
