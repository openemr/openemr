<?php

/**
 * Render the portal documents page (portal/patient/onsitedocuments) from its Twig template with
 * known variables and compare the HTML to recorded fixtures.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

#[Group('isolated')]
#[Group('twig')]
final class OnsiteDocumentListViewRenderTest extends TestCase
{
    private const GLOBALS = ['date_display_format', 'disable_translation'];

    /** Stand-in for the release value of v_js_includes, so the cache-busters render. */
    private const ASSET_VERSION = 82;

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
    public function pageRendersExpectedOutput(array $vars, bool $portalSession, string $fixture): void
    {
        $renderer = new TwigPageRenderer(self::twig(), [
            'ROOT_URL' => '/openemr/portal/patient/',
            'web_root' => '/openemr',
            'assets_static_relative' => '/openemr/public/assets',
            'v_js_includes' => self::ASSET_VERSION,
            'is_portal_session' => $portalSession,
            'software_version' => '8.1.0',
            'current_year' => '2026',
        ]);
        $rendered = implode("\n", array_map(rtrim(...), explode("\n", $renderer->render('OnsiteDocumentListView', $vars))));
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
     * @return array<string, array{array<string, mixed>, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pageProvider(): array
    {
        // What OnsiteDocumentController::ListView() passes for a bare request.
        $request = [
            'language_defs' => ['Documents' => 'Documentos'],
            'doc_edit' => 0,
            'recid' => 0,
            'help_id' => 3,
            'encounter' => 0,
            'docid' => '',
            'is_module' => 0,
            'save_catid' => '',
            'category_name' => null,
            'new_filename' => '',
            'auto_render' => 0,
            'audit_render' => 0,
            'referer_flag' => 0,
            'auto_render_name' => 0,
            'allow_portal_uploads' => true,
            'form_names_whitelist' => ['questionnaire_assessments', 'history_sdoh'],
            'template_menu' => '',
            'datetimepicker_js' => "\n//datepicker elements\nvar datepicker_format = 'Y-m-d';\nvar datetimepicker_format = 'Y-m-d H:i:s';\n",
            'portal_home_url' => '../home.php?site=default',
            'exit_url' => '../home.php?site=default',
        ];
        $menu = '<a class="dropdown-item template-item text-primary btn btn-link font-weight-bold" id="9" data-history_id="0" href="#" onclick="page.newDocument(7, \'-patient-\', \'Intake\', 9)">Intake</a>';

        return [
            'staff reviewing a patient' => [
                [
                    'cpid' => 7,
                    'cuser' => 1,
                    'pt_name' => 7,
                    'is_portal' => 0,
                ] + $request,
                false,
                'onsite-document-list-view-staff.html',
            ],
            'portal patient opening a one-time form' => [
                [
                    'cpid' => 7,
                    'cuser' => '-patient-',
                    'pt_name' => 'Jane Doe',
                    'is_portal' => 1,
                    'auto_render' => '9',
                    'auto_render_name' => 'Intake',
                    'allow_portal_uploads' => false,
                    'template_menu' => $menu,
                ] + $request,
                true,
                'onsite-document-list-view-portal.html',
            ],
            'templates module with values that need escaping' => [
                [
                    'cpid' => 7,
                    'cuser' => 1,
                    'pt_name' => "O'Brien & <Sons>",
                    'is_portal' => 0,
                    'is_module' => '1',
                    'doc_edit' => '1',
                    'recid' => '9',
                    'docid' => "Consent \"A\" </script>",
                    'save_catid' => '5&x=1',
                    'category_name' => "Lab <Results> & 'Notes'",
                    'new_filename' => 'Intake',
                    'referer_flag' => '1',
                    'template_menu' => $menu,
                ] + $request,
                false,
                'onsite-document-list-view-module.html',
            ],
        ];
    }

    private static function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 5) . '/templates'), ['autoescape' => false]);
        $twig->addExtension(new TwigExtension(OEGlobalsBag::getInstance(), null));
        // The real functions need the application bootstrap and a live session. The header stub
        // shows the assets asked for, and whether setupHeader() was left to echo them as well.
        $twig->addFunction(new TwigFunction('setupHeader', self::setupHeaderStub(...), ['is_safe' => ['html']]));
        // A token with markup in it, to show where the page escapes it.
        $twig->addFunction(new TwigFunction('csrfTokenRaw', fn(): string => 'test-csrf-token"<&>'));

        return $twig;
    }

    /**
     * @param list<string> $assets
     */
    private static function setupHeaderStub(array $assets = [], bool $echoOutput = true): string
    {
        return '<!-- setupHeader stub: ' . implode(', ', $assets) . ($echoOutput ? ' (echoed)' : '') . ' -->';
    }
}
