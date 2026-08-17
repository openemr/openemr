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

use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use OpenEMR\Tests\Acceptance\Support\UiSeedingTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Panther-driven acceptance port of tests/Tests/E2e/KkEncounterFormNavbarUrlTest.
 *
 * Phase 4e-4 (fourth + last Small warmup of Phase 4e — reuse
 * tests/Tests/E2e/* flows against the SHIPPED docker release image
 * booted by tests/Acceptance/bin/boot-docker.sh). Extends
 * PantherAcceptanceTestCase for the shared login + Knockout-ready gate
 * + Product Registration modal dismissal, and consumes UiSeedingTrait
 * for the patient + encounter seeding helpers that the source-side
 * ordered suite gets from CcCreatePatientTest / EeCreateEncounterTest
 * predecessors — the acceptance suite has no such ordering, so this
 * test seeds up front via UI navigation.
 *
 * Regression test for #10844: without pid + encounter URL params on
 * the encounter-form navbar dropdown links, form loading fell back to
 * a stale session encounter (often 0) and new.php substituted
 * `date("Ymd")` as a bogus encounter id, triggering a 404 on redirect.
 * This test proves against a booted release artifact that the fix
 * still ships — every `load_form.php` navbar link carries `pid=` and
 * `encounter=` params, and none has `encounter=0`.
 *
 * Full port — the sole scenario carries unique signal against the
 * booted release artifact (encounter-form navbar URL correctness has
 * no analog in E2eCriticalPathTest, AaLoginAcceptanceTest, or
 * GgUserMenuLinksAcceptanceTest). Not duplicating any existing
 * assertion.
 *
 * Seeding note: the shipped release image ships with no patient
 * fixtures — every acceptance run gets a fresh, empty database. The
 * source-side test relies on Ftest/Ltest existing (seeded by upstream
 * CcCreatePatientTest / EeCreateEncounterTest in the source-side
 * ordered suite), so this port must seed a patient + encounter via UI
 * navigation up front. Black-box discipline — no SQL, no direct DB
 * writes, no fixture files.
 *
 * Product-registration-modal dismissal: handled by the base class's
 * performLoginAsAdmin(), which dismisses the modal as its final gate
 * step. The seeding flow below clicks main-menu / shell shortcuts
 * that would otherwise be blocked by the modal's backdrop, so the
 * dismissal is defensive but load-bearing.
 *
 * The source-side KkEncounterFormNavbarUrlTest continues to run
 * against the dev stack unchanged — this class is purely additive
 * against the release artifact.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class KkEncounterFormNavbarUrlAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Verify that every load_form.php link rendered in the encounter-
     * form navbar dropdown carries `pid=` and `encounter=` query
     * params, and that no link uses `encounter=0` (the stale-session
     * scenario that #10844 fixed).
     *
     * Source: KkEncounterFormNavbarUrlTest::testFormNavbarUrlsContainEncounterAndPid.
     */
    public function testFormNavbarUrlsContainEncounterAndPid(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        // Seed a patient + encounter via UI navigation — release image
        // ships with no fixtures, so these calls create the state the
        // navbar-URL assertion needs. Order matters: patient first,
        // then encounter within that patient's context.
        $this->addPatientViaUi();
        $this->addEncounterViaUi();

        // addEncounterViaUi() leaves the browser already inside the
        // forms.php iframe with the navbar rendered (see its final
        // frame-switch + navbarEncounterTitle wait) — no re-navigation
        // needed here.
        $client = $this->requireClient();

        // Wait for the navbar element that hosts the dropdown items
        // to render — the dropdown items themselves aren't part of the
        // initial paint, they're populated as the navbar builds.
        $client->waitFor('//span[@id="navbarEncounterTitle"]', 30);

        // Extract every onclick attribute from a dropdown item that
        // references load_form.php. Each form link has:
        //   onclick="openNewForm('...load_form.php?formname=X&pid=Y&encounter=Z', ...)"
        // We assert on the raw onclick string (rather than clicking
        // each link and reading the resulting URL) because the
        // regression is in the URL that's WRITTEN INTO the DOM, not
        // in what happens when it's followed — a fix at either the
        // link-write layer OR the load_form.php handler would mask a
        // regression at the other layer if the assertion clicked
        // through instead.
        $rawOnclickValues = $client->executeScript(<<<'JS_WRAP'
            var items = document.querySelectorAll('.dropdown-menu .dropdown-item');
            var results = [];
            items.forEach(function(item) {
                var onclick = item.getAttribute('onclick');
                if (onclick && onclick.indexOf('load_form.php') !== -1) {
                    results.push(onclick);
                }
            });
            return results;
        JS_WRAP);

        // executeScript returns `mixed` — narrow to list<string> so
        // phpstan level 10 accepts the foreach below without inline
        // casts. Anything unexpected here (non-array, non-string
        // elements) means the JS above returned a shape we don't
        // handle; fail loudly rather than silently coerce.
        self::assertIsArray(
            $rawOnclickValues,
            'JS extractor should return an array of onclick strings; a non-array return means the DOM query threw or serialized to an unexpected shape',
        );
        $onclickValues = [];
        foreach ($rawOnclickValues as $value) {
            self::assertIsString(
                $value,
                'Each extracted onclick attribute should be a string; a non-string element means a dropdown item has a non-standard onclick shape',
            );
            $onclickValues[] = $value;
        }

        self::assertNotEmpty(
            $onclickValues,
            'Expected at least one form link with load_form.php in the encounter-form navbar — an empty result means either the navbar rendered without form links (menu regression) or the DOM selector no longer matches the rendered dropdown structure',
        );

        // Every load_form.php URL must include pid= AND encounter=
        // params, and encounter must not be 0. Reading each assertion
        // separately so a failure points at the specific missing
        // constraint rather than a compound message.
        foreach ($onclickValues as $onclick) {
            self::assertStringContainsString(
                'pid=',
                $onclick,
                "Form link missing pid param: {$onclick}",
            );
            self::assertStringContainsString(
                'encounter=',
                $onclick,
                "Form link missing encounter param: {$onclick}",
            );
            // The encounter value must not be 0 (the stale-session
            // scenario that #10844 fixed). Regex boundary avoids
            // false negatives on encounter=10, encounter=20, etc.
            self::assertDoesNotMatchRegularExpression(
                '/encounter=0(?:[^0-9]|$)/',
                $onclick,
                "Form link has encounter=0 (stale session bug from #10844): {$onclick}",
            );
        }
    }
}
