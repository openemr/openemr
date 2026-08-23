<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Post-install / post-upgrade UI assertion that the About page reports
 * the version the operator expected.
 *
 * Covers the "file version" pipe end-to-end: `version.php` in the
 * artifact → `require_once` at `interface/globals.php:408` →
 * `OEGlobalsBag::set('v_major', ...)` → `VersionService->getSoftwareVersion()`
 * → `SoftwareVersion::fromGlobals()` → `about_page.php`'s
 * `versionNumber` Twig variable → `templates/core/about.html.twig`'s
 * `.version-info strong` render. Any regression along that chain
 * surfaces here as a mismatch against `ACCEPTANCE_EXPECTED_VERSION`.
 *
 * Companion signals (all part of openemr/openemr#13634):
 *   - **DB version** — asserted at shell level in
 *     `tests/Acceptance/bin/boot-package.sh` and
 *     `tests/Acceptance/bin/upgrade-package.sh` (query the `version`
 *     table directly via `docker compose exec mysql`).
 *   - **API version** — asserted in `VersionApiAcceptanceTest`
 *     (tagged `api-enabled`) against `/api/version`.
 *
 * Each signal reads a different code path (file / DB / route), so a
 * future refactor that consolidates any two of them (e.g., /api/version
 * shifting from DB-read to file-read to match About page) doesn't
 * leave a coverage gap.
 *
 * Tagged with its OWN group `version-display` (not `fresh-install` /
 * `post-upgrade` / etc.) so `.github/workflows/acceptance-package.yml`
 * fires it via explicit `--group=version-display` steps at points
 * where `ACCEPTANCE_EXPECTED_VERSION` is definitively set. Piggy-
 * backing the existing scenario groups would leak this test into
 * `acceptance-docker.yml`, which today does not resolve floating
 * image tags (e.g., `latest`, `next`) into X.Y.Z at runtime — so
 * `ACCEPTANCE_EXPECTED_VERSION` couldn't be set there without
 * additional Docker-Hub tag-resolution plumbing. Docker parity is a
 * bounded follow-up.
 */
#[Group('version-display')]
final class VersionDisplayAcceptanceTest extends PantherAcceptanceTestCase
{
    private const ABOUT_URL = '/interface/main/about_page.php';

    /**
     * Login as admin, GET the About page directly, assert the version
     * displayed in `.version-info strong` matches
     * `ACCEPTANCE_EXPECTED_VERSION`.
     *
     * Direct navigation (not via the user-menu "About OpenEMR" click)
     * because the goal is to validate the version-render pipe, not the
     * menu wiring — the menu wiring is already covered by
     * `GgUserMenuLinksAcceptanceTest::testUserMenuLink` for the
     * 'About OpenEMR user menu link' data-provider row.
     */
    public function testAboutPageShowsExpectedVersion(): void
    {
        $expected = getenv('ACCEPTANCE_EXPECTED_VERSION');
        self::assertNotFalse(
            $expected,
            'ACCEPTANCE_EXPECTED_VERSION env is unset — the acceptance-package.yml matrix cell must set this so the test knows which version to assert against. Passing an empty string is not a valid override.',
        );
        self::assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+$/',
            $expected,
            "ACCEPTANCE_EXPECTED_VERSION='{$expected}' does not match required X.Y.Z shape",
        );

        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $client = $this->requireClient();
        $client->request('GET', self::ABOUT_URL);

        // `.version-info strong` is the exact element templates/core/
        // about.html.twig line 14 renders `versionNumber|text` into.
        // If the selector ever moves, this test breaks loudly with the
        // observed page title + URL in the assertion diagnostic —
        // better signal than a silent version-check bypass.
        $element = $client->findElement(WebDriverBy::cssSelector('.version-info strong'));
        $actual = trim($element->getText());

        self::assertSame(
            $expected,
            $actual,
            "About page displayed version '{$actual}' does not match expected '{$expected}'. "
            . 'Landing URL: ' . $client->getCurrentURL() . '. '
            . 'Title: ' . $client->getTitle() . '. '
            . 'This most likely means the artifact ships a version.php that does not match '
            . 'the workflow-declared TO_VERSION (dev-cycle bump missed, backport went to '
            . 'the wrong branch, tarball built from the wrong ref). See '
            . 'openemr/openemr#13634 for the full signal-source rationale.',
        );
    }
}
