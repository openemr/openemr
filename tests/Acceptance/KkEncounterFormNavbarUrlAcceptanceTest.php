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
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * Panther-driven acceptance port of tests/Tests/E2e/KkEncounterFormNavbarUrlTest.
 *
 * Phase 4e-4 (fourth + last Small warmup of Phase 4e — reuse
 * tests/Tests/E2e/* flows against the SHIPPED docker release image
 * booted by tests/Acceptance/bin/boot-docker.sh). Extends the pattern
 * established by GgUserMenuLinksAcceptanceTest (#13338) and
 * AaLoginAcceptanceTest (#13336): Panther client via BrowserSession
 * factory, base URI resolved from ACCEPTANCE_ARTIFACT_URL. Closures
 * inside `wait()->until(...)` MUST explicitly type their `$driver`
 * parameter as `WebDriver` (or `JavaScriptExecutor` when calling
 * executeScript) — otherwise phpstan level 10 infers `mixed` and
 * rejects downstream method calls. `WebDriverExpectedCondition`
 * carries its own types so plain-`until(WebDriverExpectedCondition::...)`
 * needs no annotation.
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
 * Product-registration-modal dismissal: the shipped release image
 * shows a "Product Registration" modal on first login whose backdrop
 * intercepts clicks on top-right shell elements. The seeding flow
 * clicks the user icon adjacent area (new-patient tab launch via
 * mainMenu, not user menu), but the modal's full-page backdrop can
 * intercept clicks on ANY element outside the modal's own boundary
 * until dismissed — so the same helper as GgUserMenuLinksAcceptanceTest
 * is included here defensively. Cheaper to always dismiss than to
 * debug a downstream "element not interactable" 30s post-login.
 *
 * The source-side KkEncounterFormNavbarUrlTest continues to run
 * against the dev stack unchanged — this class is purely additive
 * against the release artifact.
 */
#[Group('fresh-install')]
final class KkEncounterFormNavbarUrlAcceptanceTest extends TestCase
{
    private const PATIENT_FNAME = 'Ftest';
    private const PATIENT_LNAME = 'Ltest';
    private const PATIENT_DOB = '1958-05-02';
    private const PATIENT_SEX = 'Male';

    private ?Client $client = null;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            // Panther leaves a Chrome subprocess + ChromeDriver session
            // dangling if not explicitly quit. Same discipline as
            // E2eCriticalPathTest, AaLoginAcceptanceTest, and
            // GgUserMenuLinksAcceptanceTest.
            $this->client->quit();
            $this->client = null;
        }
    }

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
        $this->loginAsAdminAndWaitForMenu();

        // Seed a patient + encounter via UI navigation — release image
        // ships with no fixtures, so these calls create the state the
        // navbar-URL assertion needs. Order matters: patient first,
        // then encounter within that patient's context.
        $this->addPatientViaUi();
        $this->addEncounterViaUi();

        // Navigate into the encounter forms iframe where the navbar
        // lives. Chain: defaultContent → encounter iframe → forms
        // iframe. Mirrors the source-side flow after encounter add.
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="enc"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="enc"]');
        $client->waitFor('//iframe[@src="forms.php"]', 30);
        $this->switchToIFrame('//iframe[@src="forms.php"]');

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

    /**
     * Log in as admin/pass and wait for the Knockout-rendered
     * #mainMenu to actually populate. Same two-gate discipline as
     * E2eCriticalPathTest and GgUserMenuLinksAcceptanceTest so the
     * subsequent seeding + navigation don't race the SPA boot.
     */
    private function loginAsAdminAndWaitForMenu(): void
    {
        $client = $this->requireClient();
        $client->request('GET', '/interface/login/login.php?site=default');

        self::assertSame(
            'OpenEMR Login',
            $client->getTitle(),
            'Login page must render before submitting credentials — a different title means the login route itself is broken',
        );

        $client->submitForm('login-button', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
        ]);

        try {
            $client->wait(10)->until(
                static fn(WebDriver $driver): bool => $driver->getTitle() === 'OpenEMR',
            );
        } catch (TimeoutException) {
            self::fail(
                'Post-login shell title never became "OpenEMR" (10s timeout). '
                . 'Landing URL: ' . $client->getCurrentURL() . '. '
                . 'Title: ' . $client->getTitle() . '. '
                . 'This usually means the login POST did not authenticate '
                . '(credentials wrong, POST rejected, or login handler broke).'
            );
        }

        try {
            $client->waitFor('#mainMenu', 30);
        } catch (TimeoutException) {
            self::fail(
                'Post-login #mainMenu element never appeared (30s timeout). '
                . 'Landing URL: ' . $client->getCurrentURL() . '. '
                . 'Title: ' . $client->getTitle() . '. '
                . 'This likely means the login POST did not authenticate, or '
                . 'the post-login shell (interface/main/tabs/main.php) never rendered.'
            );
        }

        try {
            $client->wait(30)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                'return document.getElementById("mainMenu")?.children.length > 0'
            ));
        } catch (TimeoutException) {
            self::fail(
                'Knockout main menu never populated (30s timeout after #mainMenu appeared). '
                . 'This means the SPA shell loaded but the JS framework (Knockout) either did '
                . 'not load or did not apply bindings — symptom of a broken webpack build, '
                . 'missing JS asset, or template regression.'
            );
        }

        $this->dismissProductRegistrationModalIfPresent();
    }

    /**
     * The shipped release image renders a "Product Registration" modal
     * on first login whose backdrop can intercept clicks on elements
     * outside its own hit area. Dismiss it via the modal's own hide()
     * when visible; do nothing (don't fail) when absent, so this stays
     * robust if a future release image tweaks the trigger heuristic
     * or drops the modal entirely.
     *
     * Copied verbatim from GgUserMenuLinksAcceptanceTest — same
     * modal, same dismissal shape. Extracting to a shared helper is
     * deferred to the post-Small-tier audit called out in Brady's
     * plan doc.
     */
    private function dismissProductRegistrationModalIfPresent(): void
    {
        $client = $this->requireClient();
        // 5s poll (not 15s): product_reg.js's XHR fires during
        // document.ready which has already completed by the time the
        // Knockout-ready gate above passes, so the modal either shows
        // within the first few hundred ms of this poll or it isn't
        // going to.
        //
        // Try/catch scoped ONLY to this presence wait: TimeoutException
        // here is benign ("no modal appeared, nothing to dismiss") and
        // should not be conflated with a dismissal failure below.
        try {
            $client->wait(5)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                <<<'JS_WRAP'
                    var modal = document.querySelector('.product-registration-modal.show');
                    return modal !== null && modal.style.display === 'block';
                JS_WRAP,
            ));
        } catch (TimeoutException) {
            // No modal appeared within the poll window — proceed.
            return;
        }
        // Modal detected — failures from here propagate.

        // Small settle after the show transition — Bootstrap's
        // internal state machine ignores hide() until the fade-in
        // completes. Empirically ~150ms is the fade-in duration;
        // 500ms gives comfortable margin.
        usleep(500_000);
        $client->executeScript(
            <<<'JS_WRAP'
                if (window.jQuery) {
                    window.jQuery(".product-registration-modal").modal("hide");
                }
            JS_WRAP,
        );
        // Wait for the fade-out to complete so the subsequent
        // click isn't intercepted by the modal backdrop mid-transition.
        $client->wait(10)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
            <<<'JS_WRAP'
                var modal = document.querySelector('.product-registration-modal.show');
                var backdrop = document.querySelector('.modal-backdrop');
                return modal === null && backdrop === null;
            JS_WRAP,
        ));
    }

    /**
     * Add a fresh patient (Ftest Ltest) via the "Patient/Client → New/
     * Search" main-menu path. Mirrors the source-side PatientAddTrait
     * flow shape: open New/Search tab → fill the DEM form in the
     * patient iframe → click Create → confirm in the modal iframe →
     * accept the resulting duplicate-check alert → wait for the
     * Medical Record Dashboard header to appear.
     */
    private function addPatientViaUi(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();

        // Open the Patient → New/Search tab. The main menu is a
        // Knockout-rendered tree — the top-level "Patient" dropdown
        // toggle opens the child dropdown, then the "New/Search"
        // child label fires the tab. Both labels are rendered as
        // <div class="menuLabel"> (not <a> or <span>), so match on
        // that discriminator plus the exact label text.
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//div[@id="mainMenu"]//div[normalize-space(text())="Patient"'
                . ' and contains(concat(" ",normalize-space(@class)," ")," dropdown-toggle ")]',
            ),
            'Patient top-level menu',
        );
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//div[@id="mainMenu"]//div[normalize-space(text())="New/Search"'
                . ' and contains(concat(" ",normalize-space(@class)," ")," menuLabel ")]',
            ),
            'New/Search menu item',
        );

        // Wait for the Search-or-Add-Patient tab title to render, then
        // switch into the patient iframe where the create form lives.
        $this->assertActiveTab('Search or Add Patient');
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');

        // Wait for the DEM form to be interactive, then fill and submit.
        // The wait-for-clickable gate on the fname input is the anti-
        // flake sync — the form node exists in the DOM before its JS
        // bindings apply.
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//input[@type='text' and @name='form_fname']"),
            ),
        );

        // Fill the visible required fields directly through the DOM.
        // Panther's HttpBrowser-style ->submitForm doesn't work cleanly
        // inside iframes, and this form has enough JS-bound side effects
        // (sex/sex_identified twinning) that submitting the form element
        // is more robust than reconstructing a POST.
        $this->setInputValue("//input[@type='text' and @name='form_fname']", self::PATIENT_FNAME);
        $this->setInputValue("//input[@type='text' and @name='form_lname']", self::PATIENT_LNAME);
        $this->setInputValue("//input[@name='form_DOB']", self::PATIENT_DOB);
        $this->setSelectValue("//select[@name='form_sex']", self::PATIENT_SEX);
        $this->setSelectValue("//select[@name='form_sex_identified']", self::PATIENT_SEX);

        $this->waitAndClick(
            WebDriverBy::xpath("//*[@id='create']"),
            'Create patient button',
        );

        // Confirm dialog renders inside a modal iframe. Switch out of
        // the patient iframe first, then into the modal frame.
        $client->switchTo()->defaultContent();
        $client->waitFor("//iframe[@id='modalframe']", 30);
        $this->switchToIFrame("//iframe[@id='modalframe']");
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//*[@id='confirmCreate']"),
            ),
        );
        // Empirical stabilization — the modal form is clickable before
        // its JS finishes wiring the confirm handler; a bare click can
        // race and no-op. Same 5s wait the source-side PatientAddTrait
        // uses.
        sleep(5);
        $client->findElement(WebDriverBy::xpath("//*[@id='confirmCreate']"))->click();

        // A duplicate-check JS alert fires after confirm — accept it
        // and continue.
        $client->wait(15)->until(WebDriverExpectedCondition::alertIsPresent());
        $client->switchTo()->alert()->accept();

        // Switch back to defaults, into the patient iframe, wait for
        // the Medical Record Dashboard header — proof the create
        // succeeded and the browser landed on the summary.
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $client->waitFor(
            '//*[text()="Medical Record Dashboard - ' . self::PATIENT_FNAME . ' ' . self::PATIENT_LNAME . '"]',
            30,
        );
    }

    /**
     * Add a fresh encounter for the just-created patient via the
     * per-patient New-Encounter shortcut in the shell. Mirrors the
     * source-side EncounterAddTrait: click the shell's New-Encounter
     * link, fill the encounter form's category + reason, save, wait
     * for the encounter forms iframe to render the encounter title.
     *
     * Post-condition on return: browser is inside the forms iframe
     * with the navbar rendered — same DOM location the assertion
     * needs. Caller does NOT need to re-navigate.
     */
    private function addEncounterViaUi(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();

        // Click the "New Encounter" shortcut in the outer shell (not
        // inside an iframe — it lives in the patient-context bar).
        $this->waitAndClick(
            WebDriverBy::xpath('//a[@title="New Encounter"]'),
            'New Encounter shell shortcut',
        );

        // Switch into the encounter iframe where the create form
        // renders.
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="enc"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="enc"]');
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//button[@id='saveEncounter']"),
            ),
        );

        // Set the required-by-form fields via JS. CATID=5 matches the
        // source-side EncounterTestData; REASON is arbitrary.
        $this->setSelectValue("//select[@name='pc_catid']", '5');
        $this->setInputValue("//*[@name='reason']", 'Testing encounter');

        $client->findElement(WebDriverBy::xpath("//button[@id='saveEncounter']"))->click();

        // Post-save the browser lands inside the encounter forms
        // iframe with the navbar rendered. Wait for the navbar title
        // to confirm the encounter is open (and, incidentally, that
        // the assertion caller doesn't need to re-navigate).
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="enc"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="enc"]');
        $client->waitFor('//iframe[@src="forms.php"]', 30);
        $this->switchToIFrame('//iframe[@src="forms.php"]');
        $client->waitFor(
            '//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for '
                . self::PATIENT_FNAME . ' ' . self::PATIENT_LNAME . '")]',
            30,
        );
    }

    /**
     * Switch the Panther client into an iframe located by XPath.
     * Mirrors the source-side BaseTrait::switchToIFrame helper shape.
     */
    private function switchToIFrame(string $xpath): void
    {
        $client = $this->requireClient();
        $iframe = $client->findElement(WebDriverBy::xpath($xpath));
        $client->switchTo()->frame($iframe);
    }

    /**
     * Set a text input's value via JS + fire an `input`/`change` event
     * so any bound listeners run. More robust inside iframes than
     * ->sendKeys() for forms that read via JS event listeners.
     */
    private function setInputValue(string $xpath, string $value): void
    {
        $client = $this->requireClient();
        $client->executeScript(
            <<<'JS_WRAP'
                var xpath = arguments[0];
                var value = arguments[1];
                var el = document.evaluate(
                    xpath,
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null,
                ).singleNodeValue;
                if (el) {
                    el.value = value;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            JS_WRAP,
            [$xpath, $value],
        );
    }

    /**
     * Set a <select>'s value + fire change so any bound listeners run.
     * The category dropdown on the encounter form conditionally reveals
     * fields based on the chosen category, so the change event is
     * necessary for the form to accept the selection.
     */
    private function setSelectValue(string $xpath, string $value): void
    {
        $client = $this->requireClient();
        $client->executeScript(
            <<<'JS_WRAP'
                var xpath = arguments[0];
                var value = arguments[1];
                var el = document.evaluate(
                    xpath,
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null,
                ).singleNodeValue;
                if (el) {
                    el.value = value;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            JS_WRAP,
            [$xpath, $value],
        );
    }

    /**
     * Wait for an element matched by $by to be clickable, then click
     * it. Narrows WebDriverWait::until()'s `mixed` return through an
     * instanceof check so phpstan level 10 accepts the ->click() call
     * without inline @var casts.
     */
    private function waitAndClick(WebDriverBy $by, string $description): void
    {
        $client = $this->requireClient();
        $element = $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable($by),
        );
        if (!$element instanceof WebDriverElement) {
            self::fail(
                "elementToBeClickable returned a non-element for {$description}; got "
                . get_debug_type($element),
            );
        }
        $element->click();
    }

    /**
     * Wait for the currently-active tab in #tabs_div to display the
     * expected title, with the "Loading" placeholder resolved. Same
     * discipline as GgUserMenuLinksAcceptanceTest::assertActiveTab.
     */
    private function assertActiveTab(string $expectedTitle): void
    {
        $client = $this->requireClient();
        $activeTabXpath = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

        $client->waitFor($activeTabXpath, 30);
        $client->wait(30)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
            <<<'JS_WRAP'
                var el = document.evaluate(
                    arguments[0],
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null,
                ).singleNodeValue;
                if (!el) { return false; }
                var text = (el.textContent || '').trim();
                return text.length > 0 && text.indexOf('Loading') === -1;
            JS_WRAP,
            [$activeTabXpath],
        ));

        $activeTab = $client->findElement(WebDriverBy::xpath($activeTabXpath));
        self::assertSame(
            $expectedTitle,
            trim($activeTab->getText()),
            "[{$expectedTitle}] menu action did not open the expected tab — "
            . 'the click reached the app but the resulting tab title differs, '
            . 'which means either the menu entry routes somewhere unexpected '
            . 'or the tab label regressed',
        );
    }

    /**
     * Narrow the nullable $this->client for phpstan and for a clearer
     * failure mode if a helper is somehow called before setUp has run.
     */
    private function requireClient(): Client
    {
        if ($this->client === null) {
            self::fail('BrowserSession client not initialized — helper called before create()');
        }
        return $this->client;
    }
}
