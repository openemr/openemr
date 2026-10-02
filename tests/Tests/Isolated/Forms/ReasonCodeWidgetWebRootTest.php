<?php

/**
 * Verify that encounter forms hand their JavaScript the web root unchanged.
 *
 * Regression test for issue #13016. vitals.html.twig and care_plan_form.html.twig
 * passed the web root to their init() through the js_url filter, which URL-encodes
 * it: a web root of "/openemr" reached the reason code widget as "%2Fopenemr", so
 * "Select Reason Code" opened
 * "%2Fopenemr/interface/patient_file/encounter/find_code_popup.php", a relative URL
 * that does not exist ("Not Found"). Installs served from the domain root have an
 * empty web root and never saw the problem.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms;

use OpenEMR\Common\Twig\TwigExtension;
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
class ReasonCodeWidgetWebRootTest extends TestCase
{
    /** @var array<string, Environment> */
    private static array $twig = [];

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function webRootProvider(): array
    {
        return [
            'subdirectory install' => ['/openemr'],
            'domain root install' => [''],
        ];
    }

    /**
     * The vitals form passes the web root to vitalsForm.init() as a plain JS string.
     */
    #[Test]
    #[DataProvider('webRootProvider')]
    public function vitalsInitReceivesTheWebRootUnencoded(string $webRoot): void
    {
        $output = self::environment('/interface/forms/vitals/templates/vitals')->render('vitals.html.twig', [
            'FORM_ACTION' => $webRoot,
            'DONT_SAVE_LINK' => $webRoot . '/interface/patient_file/encounter/encounter_top.php',
            'CSRF_TOKEN_FORM' => 'token',
            'vitals' => self::vitalsStub(),
            'vitalFields' => [],
            'vitalsHistoryLookback' => [],
            'results' => [],
            'has_id' => false,
            'hasMoreVitals' => false,
            'patient_age' => 40,
            'patient_dob' => '1986-01-01',
        ]);

        self::assertSame($webRoot, self::initArgument('/window\.vitalsForm\.init\((.*?), vitalsTranslations\)/', $output));
    }

    /**
     * The care plan form passes the web root to careplanForm.init() as a plain JS string.
     */
    #[Test]
    #[DataProvider('webRootProvider')]
    public function carePlanInitReceivesTheWebRootUnencoded(string $webRoot): void
    {
        $output = self::environment('/interface')->render('/forms/care_plan/templates/care_plan_form.html.twig', [
            'webroot' => $webRoot,
            'rootdir' => $webRoot . '/interface',
            'v_js_includes' => 1,
            'existingFormMessage' => '',
            'formId' => 0,
            'csrfToken' => 'token',
            'rows' => [],
            'reasonCodeStatii' => [],
            'authUser' => 'admin',
        ]);

        self::assertSame($webRoot, self::initArgument('/window\.careplanForm\.init\((.*?), null, \{/', $output));
    }

    /**
     * Decode the JSON literal a rendered form passes as the first argument of its init() call.
     */
    private static function initArgument(string $pattern, string $output): mixed
    {
        self::assertSame(1, preg_match($pattern, $output, $matches), 'init() call not found in the rendered form');
        return json_decode($matches[1]);
    }

    /**
     * The vitals template only reads these getters; the real FormVitals needs a database.
     */
    private static function vitalsStub(): object
    {
        return new class {
            public function get_date(): string
            {
                return '2026-09-27 10:00:00';
            }

            public function get_id(): string
            {
                return '';
            }

            public function get_uuid_string(): string
            {
                return '';
            }

            public function get_activity(): int
            {
                return 1;
            }

            public function get_pid(): int
            {
                return 1;
            }

            public function get_height(): string
            {
                return '';
            }

            public function get_weight(): string
            {
                return '';
            }

            public function get_head_circ(): string
            {
                return '';
            }
        };
    }

    /**
     * A Twig environment rooted at $templateDir, with the database- and kernel-bound functions stubbed.
     */
    private static function environment(string $templateDir): Environment
    {
        if (isset(self::$twig[$templateDir])) {
            return self::$twig[$templateDir];
        }

        $GLOBALS['fileroot'] ??= dirname(__DIR__, 4);
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;

        // Built like TwigContainer::getTwig(), without the kernel.
        $twig = new Environment(new FilesystemLoader([dirname(__DIR__, 4) . $templateDir]), ['autoescape' => false]);
        $twig->addExtension(new TwigExtension(OEGlobalsBag::getInstance()));
        // These read the database or need the application kernel; the tests only
        // look at the init() call.
        $twig->addFunction(new TwigFunction('setupHeader', fn (): string => '', ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('jqueryDateTimePicker', fn (): string => '', ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('selectList', fn (): string => '', ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('getListItemTitle', fn (): string => ''));
        self::$twig[$templateDir] = $twig;
        return $twig;
    }
}
