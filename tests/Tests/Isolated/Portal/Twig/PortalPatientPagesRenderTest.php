<?php

/**
 * Render the portal/patient Portal Dashboard, staff review list and error pages from their Twig
 * templates with known variables and compare the HTML to recorded fixtures.
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
final class PortalPatientPagesRenderTest extends TestCase
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
    public function pageRendersExpectedOutput(string $page, array $vars, string $fixture): void
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
        $rendered = implode("
", array_map(rtrim(...), explode("
", $renderer->render($page, $vars))));
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
     * @return array<string, array{string, array<string, mixed>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pageProvider(): array
    {
        return [
            'dashboard' => ['ProviderHome', ['cpid' => 0, 'cuser' => 1, 'pdf_language' => 'en'], 'provider-home.html'],
            'review list' => ['OnsiteActivityViewListView', ['cuser' => 'admin'], 'onsite-activity-view-list-view.html'],
            'review list escapes the user' => ['OnsiteActivityViewListView', ['cuser' => "o'neil</script>"], 'onsite-activity-view-list-view-escaping.html'],
            'not found' => ['DefaultError404', [], 'error-404.html'],
            'failed request' => ['DefaultErrorFatal', ['message' => 'An unexpected error occurred <b>', 'stacktrace' => '', 'code' => 0], 'error-fatal.html'],
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
