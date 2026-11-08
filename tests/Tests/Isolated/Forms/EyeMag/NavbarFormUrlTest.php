<?php

/**
 * Verify that encounter form navbar URLs include pid and encounter parameters.
 *
 * Regression test for issue #10844. The navbar Twig template previously
 * rendered form links as:
 *   load_form.php?formname=eye_mag
 *
 * without pid or encounter, so new.php relied on the session, which
 * could be stale. The fix appends &pid=X&encounter=Y to every form URL.
 *
 * This test renders the actual navbar.html.twig template with known
 * parameters and verifies that the rendered HTML contains the correct
 * URL structure for form links.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Common\Twig\TwigContainer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

#[Group('isolated')]
#[Group('twig')]
class NavbarFormUrlTest extends TestCase
{
    private static ?Environment $twig = null;

    protected function setUp(): void
    {
        $GLOBALS['fileroot'] ??= dirname(__DIR__, 5);
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['rootdir'] ??= '/openemr/interface';
        $GLOBALS['disable_translation'] = true;
    }

    /**
     * Verify that form URLs in the navbar include pid and encounter parameters.
     *
     * This is the core regression test for #10844: without these URL params,
     * load_form.php falls back to the session, which may be stale, causing
     * new.php to create forms with a bogus encounter ID.
     */
    #[Test]
    public function formUrlsIncludePidAndEncounter(): void
    {
        $twig = self::twigEnvironment();

        $pid = 42;
        $encounter = 12345;

        $output = $twig->render('encounter/forms/navbar.html.twig', [
            'encounterDate' => '2026-02-22',
            'patientName' => 'Test Patient',
            'isAdminSuper' => false,
            'enableFollowUpEncounters' => false,
            'encounter' => $encounter,
            'pid' => $pid,
            'menuArray' => [
                'Clinical' => [
                    'children' => [
                        [
                            'directory' => 'eye_mag',
                            'displayText' => 'Eye Exam',
                        ],
                        [
                            'directory' => 'vitals',
                            'displayText' => 'Vitals',
                        ],
                    ],
                ],
            ],
        ]);

        // Every form URL must include pid and encounter parameters
        $this->assertStringContainsString(
            'formname=eye_mag&amp;pid=42&amp;encounter=12345',
            $output,
            'Eye Exam form URL must include pid and encounter params'
        );
        $this->assertStringContainsString(
            'formname=vitals&amp;pid=42&amp;encounter=12345',
            $output,
            'Vitals form URL must include pid and encounter params'
        );

        // Verify the URLs go through load_form.php (not a direct path)
        $this->assertStringContainsString('load_form.php?formname=', $output);
    }

    /**
     * Verify that items with a custom href do NOT get pid/encounter appended.
     *
     * When a menu item has item.href defined, the template uses that URL as-is
     * instead of constructing a load_form.php URL. The pid/encounter params
     * should only appear on the generated load_form.php URLs.
     */
    #[Test]
    public function customHrefItemsAreNotModified(): void
    {
        $twig = self::twigEnvironment();

        $output = $twig->render('encounter/forms/navbar.html.twig', [
            'encounterDate' => '2026-02-22',
            'patientName' => 'Test Patient',
            'isAdminSuper' => false,
            'enableFollowUpEncounters' => false,
            'encounter' => 999,
            'pid' => 1,
            'menuArray' => [
                'Modules' => [
                    'children' => [
                        [
                            'href' => '/custom/module/path.php',
                            'displayText' => 'Custom Module',
                        ],
                    ],
                ],
            ],
        ]);

        // The custom href should appear (attr_js escapes slashes for JS context)
        $this->assertStringContainsString('custom', $output);
        $this->assertStringContainsString('path.php', $output);
        // And the custom href should NOT route through load_form.php
        $this->assertStringNotContainsString('load_form.php', $output);
    }

    /**
     * Verify that items with a custom onclick do NOT get a formURL at all.
     *
     * When item.onclick is defined, the template uses it directly instead of
     * building an openNewForm() call with a URL.
     */
    #[Test]
    public function customOnclickItemsUseOwnHandler(): void
    {
        $twig = self::twigEnvironment();

        $output = $twig->render('encounter/forms/navbar.html.twig', [
            'encounterDate' => '2026-02-22',
            'patientName' => 'Test Patient',
            'isAdminSuper' => false,
            'enableFollowUpEncounters' => false,
            'encounter' => 999,
            'pid' => 1,
            'menuArray' => [
                'Special' => [
                    'children' => [
                        [
                            'onclick' => 'doSpecialThing()',
                            'displayText' => 'Special Form',
                        ],
                    ],
                ],
            ],
        ]);

        // The custom onclick should appear in the rendered output
        $this->assertStringContainsString('doSpecialThing()', $output);
    }

    private static function twigEnvironment(): Environment
    {
        if (self::$twig !== null) {
            return self::$twig;
        }

        $GLOBALS['fileroot'] ??= dirname(__DIR__, 5);
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['rootdir'] ??= '/openemr/interface';
        $GLOBALS['disable_translation'] = true;

        $twigContainer = new TwigContainer();
        self::$twig = $twigContainer->getTwig();
        return self::$twig;
    }
}
