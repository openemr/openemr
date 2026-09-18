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

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\WebDriver;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Panther-driven acceptance port of
 * tests/Tests/E2e/FrontPaymentCssContrastTest.
 *
 * Phase 4e-3 (third Small warmup of Phase 4e — reuse tests/Tests/E2e/*
 * flows against the SHIPPED docker release image booted by
 * tests/Acceptance/bin/boot-docker.sh). Extends PantherAcceptanceTestCase
 * for the BrowserSession lifecycle + shared constants; does NOT invoke
 * performLoginAsAdmin() because the next step is a direct GET to
 * front_payment.php (no clicks in the SPA shell), so the full
 * Knockout-ready + modal-dismiss gate would just add wall-clock without
 * changing the assertion outcome. See loginAsAdminAndWaitForShell()
 * below for the scoped-down login that's actually needed here.
 *
 * Full port — the source has one scenario (testReceiptCssHasExplicitTextColor)
 * and it carries meaningful signal against a booted release artifact:
 * it inspects computed CSS on interface/patient_file/front_payment.php
 * (a real user-facing billing surface that ships in the release image
 * and is exercised at runtime by the front-desk payment flow — nothing
 * dev-checkout-only about it) and asserts the .bg-color / .bg-color-w /
 * .mini_table th rules include an explicit `color` property. The
 * three-theme (light/solar/dark) contrast fix behind the assertion
 * ships to every release image built after openemr#10842's fix landed,
 * so a regression that dropped the color declaration from the shipped
 * CSS would silently produce unreadable receipts on light/solar
 * themes — exactly the kind of theme-interaction bug the acceptance
 * suite is meant to catch against the shipped bundle rather than a
 * dev checkout.
 *
 * Modal-dismiss NOT needed: the assertion is a pure GET to
 * front_payment.php?receipt=1 followed by JS CSS inspection — no
 * clicks in the shell, no interaction with the top-right user icon,
 * so the release image's Product Registration modal (which
 * GgUserMenuLinksAcceptanceTest has to dismiss because it intercepts
 * user-icon clicks) is irrelevant here. Simpler flow keeps the test
 * fast and reduces surface for modal-related flakes.
 *
 * The source-side FrontPaymentCssContrastTest continues to run against
 * the dev stack unchanged — this class is purely additive against the
 * release artifact.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class FrontPaymentCssContrastAcceptanceTest extends PantherAcceptanceTestCase
{
    /**
     * The shipped front-payment receipt page must include explicit
     * `color` declarations on every element that sets a
     * `background-color`, so text stays readable on every theme (light
     * and solar map --secondary to a dark color that turns text
     * invisible when no explicit foreground color is set — see
     * openemr/openemr#10842).
     *
     * Source: FrontPaymentCssContrastTest::testReceiptCssHasExplicitTextColor.
     */
    public function testReceiptCssHasExplicitTextColorOnBackgroundedElements(): void
    {
        $this->client = BrowserSession::create();
        $this->loginAsAdminAndWaitForShell();

        // Navigate to front_payment.php's receipt view directly. The
        // receipt=1 parameter selects the receipt rendering branch that
        // emits the CSS rules under test; patient=1 + a fixed time +
        // pre_payment radio type let the page render without an
        // existing encounter — same URL shape the source-side test uses
        // against the dev stack. The request() lands on the target
        // synchronously (server-rendered PHP, no client-side redirect
        // once authenticated), so no post-navigation wait for a URL
        // change is needed before inspecting the DOM.
        $client = $this->requireClient();
        $client->request(
            'GET',
            '/interface/patient_file/front_payment.php'
            . '?receipt=1&patient=1&time=2026-01-01+12:00:00'
            . '&radio_type_of_payment=pre_payment',
        );

        // Guard against landing on the login page (would mean the
        // session-persistence gate didn't hold across the direct
        // navigation) or on the ACL-denied template (would mean the
        // admin user we authenticated as somehow lacks acct/bill/
        // acct/rep_a/patients/rx — impossible on a stock release
        // image, but a clear-signal failure if the ACL defaults ever
        // regress). URL check is enough — front_payment.php with
        // receipt=1 stays on the same path on success.
        self::assertStringContainsString(
            '/interface/patient_file/front_payment.php',
            $client->getCurrentURL(),
            'Direct GET to front_payment.php?receipt=1 should stay on that route — '
            . 'landing elsewhere means the session did not persist across the navigation '
            . 'or the admin ACL profile lost access to the billing / receipt surface',
        );

        // Inspect computed CSS via JS. Iterate every stylesheet loaded
        // into the page and check whether the .bg-color / .bg-color-w /
        // table.mini_table th rules include a non-empty `color`
        // property. Same JS shape the source-side test uses — the only
        // difference is we wrap it in a small anti-flake wait so a
        // stylesheet that's still loading when the DOM is ready
        // doesn't fail the assertion spuriously (Panther against the
        // release image sees CSS assets served from a real webserver
        // rather than a bundled dev asset pipeline, so first-request
        // asset load can lag the initial DOM ready by a few hundred
        // ms).
        try {
            $client->wait(15)->until(
                fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                    <<<'JS_WRAP'
                        // Wait gate: return true once every stylesheet
                        // in the document has finished loading (either
                        // its cssRules are populated OR it explicitly
                        // errored). Prevents a race where the top-level
                        // DOM is ready but a linked CSS file hasn't
                        // parsed yet, which would leave rule counts at
                        // zero and fail the assertion.
                        var sheets = document.styleSheets;
                        for (var i = 0; i < sheets.length; i++) {
                            try {
                                var rules = sheets[i].cssRules;
                                if (rules === null) {
                                    return false;
                                }
                            } catch(e) {
                                // Cross-origin sheet — treated as
                                // "not our concern"; keep going.
                            }
                        }
                        return true;
                    JS_WRAP,
                ),
            );
        } catch (TimeoutException) {
            self::fail(
                'Front-payment receipt stylesheets never finished loading (15s timeout). '
                . 'Landing URL: ' . $client->getCurrentURL() . '. '
                . 'This likely means an asset 404 or a network stall against the release '
                . 'image; the CSS-rule assertions below would have flaked spuriously without '
                . 'a real signal about the color declarations under test.',
            );
        }

        $rawResult = $client->executeScript(
            <<<'JS_WRAP'
                var results = {bgColor: false, bgColorW: false, miniTableTh: false};
                var sheets = document.styleSheets;
                for (var i = 0; i < sheets.length; i++) {
                    try {
                        var rules = sheets[i].cssRules || sheets[i].rules;
                        if (!rules) { continue; }
                        for (var j = 0; j < rules.length; j++) {
                            var rule = rules[j];
                            if (!rule.selectorText) { continue; }
                            var selector = rule.selectorText.trim();
                            if (selector === '.bg-color' && rule.style.color) {
                                results.bgColor = true;
                            }
                            if (selector === '.bg-color-w' && rule.style.color) {
                                results.bgColorW = true;
                            }
                            if (selector.indexOf('mini_table') !== -1
                                && selector.indexOf('th') !== -1
                                && rule.style.color) {
                                results.miniTableTh = true;
                            }
                        }
                    } catch(e) {
                        // Cross-origin stylesheet — skip and continue.
                    }
                }
                return JSON.stringify(results);
            JS_WRAP,
        );

        self::assertIsString(
            $rawResult,
            'CSS-inspection JS should return a JSON-encoded results string — a non-string '
            . 'return means the executeScript itself failed or the script errored before '
            . 'the return statement',
        );
        $decoded = json_decode($rawResult, true);
        self::assertIsArray(
            $decoded,
            'CSS-inspection JS return value should decode to an array — got: ' . $rawResult,
        );

        self::assertArrayHasKey('bgColor', $decoded);
        self::assertArrayHasKey('bgColorW', $decoded);
        self::assertArrayHasKey('miniTableTh', $decoded);
        self::assertTrue(
            (bool) $decoded['bgColor'],
            '.bg-color CSS rule must include an explicit color property for text contrast — '
            . 'the shipped release image dropped this declaration, which will render receipts '
            . 'unreadable on light and solar themes (see openemr/openemr#10842)',
        );
        self::assertTrue(
            (bool) $decoded['bgColorW'],
            '.bg-color-w CSS rule must include an explicit color property for text contrast — '
            . 'the shipped release image dropped this declaration, which will render receipts '
            . 'unreadable on light and solar themes (see openemr/openemr#10842)',
        );
        self::assertTrue(
            (bool) $decoded['miniTableTh'],
            'table.mini_table th CSS rule must include an explicit color property for text '
            . 'contrast — the shipped release image dropped this declaration, which will '
            . 'render receipt table headers unreadable on light and solar themes '
            . '(see openemr/openemr#10842)',
        );
    }

    /**
     * Log in as admin/pass and wait for the post-login shell title to
     * settle. Scoped-down variant of the base-class performLoginAsAdmin
     * — we don't need the full Knockout-ready gate or the modal dismiss
     * here because the next step is a direct GET to front_payment.php,
     * not a click sequence in the SPA shell. The title-change gate is
     * the minimum sync needed to prove the session was established
     * before we start using it.
     */
    private function loginAsAdminAndWaitForShell(): void
    {
        $client = $this->requireClient();
        $client->request('GET', self::LOGIN_URL);

        self::assertSame(
            self::LOGIN_TITLE,
            $client->getTitle(),
            'Login page must render before submitting credentials — a different title means '
            . 'the login route itself is broken',
        );

        $client->submitForm('login-button', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
        ]);

        // Post-login redirect is client-side / async. Same wait-for-
        // title pattern the base class's performLoginAsAdmin uses,
        // with the TimeoutException wrapped in a diagnostic fail() so
        // a broken login POST surfaces a clear signal (landing URL +
        // observed title) rather than a bare TimeoutException on a
        // helper line.
        try {
            $client->wait(10)->until(
                static fn(WebDriver $driver): bool => $driver->getTitle() === self::SHELL_TITLE,
            );
        } catch (TimeoutException) {
            self::fail(
                'Post-login shell title never became "' . self::SHELL_TITLE . '" (10s timeout). '
                . 'Landing URL: ' . $client->getCurrentURL() . '. '
                . 'Title: ' . $client->getTitle() . '. '
                . 'This usually means the login POST did not authenticate '
                . '(credentials wrong, POST rejected, or login handler broke).',
            );
        }
    }
}
