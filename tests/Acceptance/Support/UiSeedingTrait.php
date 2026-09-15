<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use RuntimeException;

/**
 * UI-driven seeding helpers for acceptance tests that need patient +
 * encounter fixtures against a fresh release image.
 *
 * The shipped release image ships with no patient fixtures — every
 * acceptance run gets a fresh, empty database — so tests that assert
 * on encounter/patient-scoped UI must seed via UI navigation. Black-box
 * discipline: no SQL, no direct DB writes, no fixture files.
 *
 * Kept as a trait (not base-class methods) so Small-tier ports that
 * DON'T need seeding (AaLogin, GgUserMenu, FrontPaymentCssContrast,
 * E2eCriticalPath) don't load the helpers. Medium-tier ports that
 * DO need patient/encounter state opt in via `use UiSeedingTrait`.
 *
 * The trait requires the consuming class to extend
 * PantherAcceptanceTestCase — it calls requireClient(), waitAndClick(),
 * assertActiveTab(), and switchToIFrame() from the base class. The
 * type-hint below documents this contract without requiring PHP's
 * awkward abstract-trait-method boilerplate.
 *
 * @method \Symfony\Component\Panther\Client requireClient()
 * @method void waitAndClick(WebDriverBy $by, string $description, int $timeoutSeconds = 15)
 * @method void assertActiveTab(string $expectedTitle)
 * @method void switchToIFrame(string $xpath)
 * @method void performLoginAsAdmin()
 */
trait UiSeedingTrait
{
    /**
     * Base names for the seeded patient. Actual identity is
     * base + random suffix, generated per test instance and
     * returned by seedPatientFname() / seedPatientLname().
     *
     * Ftest/Ltest bases match the source-side PatientAddTrait's
     * chosen fixture names so grep across suites still ties back
     * to the same convention; the suffix isolates each test's
     * seed from every other test's + from prior runs against a
     * persisted DB (upgrade scenario's post-upgrade phase).
     *
     * DOB + SEX stay fixed — patient uniqueness is by
     * (fname, lname, DOB), and varying fname/lname is enough to
     * make each seed unique.
     */
    protected const SEED_PATIENT_FNAME_BASE = 'Ftest';
    protected const SEED_PATIENT_LNAME_BASE = 'Ltest';
    protected const SEED_PATIENT_DOB = '1958-05-02';
    protected const SEED_PATIENT_SEX = 'Male';

    private ?string $seedPatientSuffix = null;

    /**
     * First name for the seeded patient — base + shared random
     * suffix, generated once per test instance and cached so
     * patient-add calls and downstream assertions see the same
     * value. fname and lname share the SAME suffix so the pair
     * correlates cleanly in DB rows + logs ("which test seeded
     * Ftestabc123 / Ltestabc123?" → obvious grep target).
     *
     * Multiple test instances (PHPUnit creates one per test method)
     * each generate their own suffix, so tests running in the
     * same phase against the same DB never collide.
     */
    protected function seedPatientFname(): string
    {
        return self::SEED_PATIENT_FNAME_BASE . $this->seedPatientSuffix();
    }

    /**
     * Last name for the seeded patient — see seedPatientFname().
     */
    protected function seedPatientLname(): string
    {
        return self::SEED_PATIENT_LNAME_BASE . $this->seedPatientSuffix();
    }

    private function seedPatientSuffix(): string
    {
        return $this->seedPatientSuffix ??= bin2hex(random_bytes(8));
    }

    /**
     * Encounter category id — matches source-side EncounterTestData's
     * chosen category (5 = "Office Visit" in the stock installer's
     * pc_catid table).
     */
    protected const SEED_ENCOUNTER_CATEGORY_ID = '5';

    /**
     * Encounter free-text reason — content is arbitrary; kept as a
     * constant so a search-by-reason follow-up test can reuse it.
     */
    protected const SEED_ENCOUNTER_REASON = 'Testing encounter';

    /**
     * Install a browser-prompt muzzle via CDP for the rest of this
     * WebDriver session. Overrides window.alert / .confirm / .prompt
     * with no-ops on every subsequent page navigation, using
     * ChromeDriver's `Page.addScriptToEvaluateOnNewDocument` (runs
     * before any page JS).
     *
     * Motivation: the Medical Record Dashboard fires a native
     * browser alert from `library/clinical_rules.php` via
     * `<img src="empty.gif" onload="alert(...)">` when the widget
     * computes New Due Clinical Reminders for a newly-created
     * patient. On slow CI runners the alert races test-side
     * waits and blocks the WebDriver session with
     * UnexpectedAlertOpenException. Prior mitigation attempts
     * (widening the wait, click retries, `unhandledPromptBehavior`
     * capability) all failed to zero out the flake — the
     * capability path doesn't take effect on Panther's local
     * ChromeDriver in CI, and the wait-based approaches race
     * unpredictable reminders-widget compute time. Muzzling
     * `window.alert` at the source removes the popup entirely
     * so there is no popup for the driver to block on.
     *
     * Scope: called from the seeding helpers that navigate to a
     * patient's dashboard (addPatientViaUi, openPatientViaUi).
     * Non-seeding tests keep their default alert-native
     * behavior — if a future test wants to assert alert
     * content, it just doesn't include UiSeedingTrait.
     *
     * Idempotent — CDP happily accepts the same script being
     * registered multiple times; the no-ops just get installed
     * repeatedly. Cheap.
     */
    private function muzzleBrowserPrompts(): void
    {
        $script = 'window.alert = function () {};'
            . 'window.confirm = function () { return true; };'
            . 'window.prompt = function () { return ""; };';
        // Panther's Client::getWebDriver() returns the WebDriver
        // interface; the CDP escape hatch lives on RemoteWebDriver
        // (the concrete class both driver paths actually return).
        // Narrow explicitly so phpstan sees the method.
        $webDriver = $this->requireClient()->getWebDriver();
        if (!$webDriver instanceof RemoteWebDriver) {
            throw new RuntimeException(sprintf(
                'muzzleBrowserPrompts requires a RemoteWebDriver instance for CDP execute; got %s',
                $webDriver::class,
            ));
        }
        $webDriver->executeCustomCommand(
            '/session/:sessionId/goog/cdp/execute',
            'POST',
            [
                'cmd' => 'Page.addScriptToEvaluateOnNewDocument',
                'params' => ['source' => $script],
            ],
        );
    }

    /**
     * Rip every Bootstrap-modal-related DOM element out of the
     * top-level document. Motivation: OpenEMR's dashboard opens
     * modals via dlgopen() from multiple triggers (dup-check
     * pop-up on patient add, clinical-reminders widget on
     * dashboard load, potentially birthday-popup depending on
     * globals). Leftover modal wrappers intercept subsequent
     * shell clicks even after their content is closed — Bootstrap
     * modal-hide sets display:none but leaves the wrapper div in
     * place, and its child .modal-body sits over the shell z-order.
     *
     * Called at the point of each seeding helper (before/after
     * navigations that trigger dashboard-load modal opens) to
     * keep the shell clickable. Cheap (a single executeScript);
     * safe (only affects test-side DOM; caller may have already
     * cleared them, in which case this is a no-op).
     */
    private function dismissAnyOpenModals(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $client->executeScript(
            // Shotgun — every Bootstrap modal class + dlgopen
            // wrappers + backdrop + our specific modalframe iframe.
            'document.querySelectorAll('
            . '".dialogModal, .modal, .modal-dialog, .modal-content, '
            . '.modal-body, .modal-header, .modal-footer, '
            . '.modal-backdrop, iframe#modalframe"'
            . ').forEach(function (e) { e.remove(); });'
            . 'document.body.classList.remove("modal-open");'
            . 'document.body.style.overflow = "";'
            . 'document.body.style.paddingRight = "";'
        );
    }

    /**
     * Add a fresh patient (Ftest<suffix> Ltest<suffix>) via the
     * "Patient/Client → New/Search" main-menu path. Identity is
     * per-test-instance via seedPatientFname/seedPatientLname so
     * multiple tests seeding in the same phase and post-upgrade
     * runs against an already-seeded DB never collide on a
     * pre-existing patient. Mirrors the source-side
     * PatientAddTrait flow shape: open New/Search tab → fill the DEM
     * form in the patient iframe → click Create → confirm in the modal
     * iframe → wait for the Medical Record Dashboard header.
     *
     * Post-condition is the dashboard header. On landing, the
     * dashboard's clinical-reminders widget would emit a native
     * browser alert (see library/clinical_rules.php), but
     * muzzleBrowserPrompts() has already overridden window.alert
     * to a no-op so no popup fires. The muzzle is the sole
     * mechanism — see BrowserSession's grid + local paths for
     * why the previously-attempted unhandledPromptBehavior=accept
     * capability was yanked.
     *
     * XPath discoveries from KkEncounterFormNavbarUrl port:
     *
     *   - Main-menu top-level label is "Patient" (not "Patient/Client"
     *     as some earlier docs implied)
     *   - Menu labels are rendered as `<div class="menuLabel">` and
     *     top-level toggles as `<div class="dropdown-toggle">`, NOT
     *     `<a>` or `<span>` — Knockout template renders divs.
     */
    protected function addPatientViaUi(): void
    {
        $this->addPatientViaUiWithIdentity(
            $this->seedPatientFname(),
            $this->seedPatientLname(),
            self::SEED_PATIENT_DOB,
            self::SEED_PATIENT_SEX,
        );
    }

    /**
     * Parameterized version of addPatientViaUi(). The unparameterized
     * caller passes the per-instance random seed identity from
     * seedPatientFname()/Lname()/SEED_PATIENT_DOB/SEED_PATIENT_SEX.
     * Persistence-flow tests that need a fixed-identity patient (so
     * both fresh-install + post-upgrade phases look up the same row)
     * pass their own identity constants.
     */
    protected function addPatientViaUiWithIdentity(
        string $fname,
        string $lname,
        string $dob,
        string $sex,
    ): void {
        $client = $this->requireClient();
        $this->muzzleBrowserPrompts();
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
        $this->setInputValue("//input[@type='text' and @name='form_fname']", $fname);
        $this->setInputValue("//input[@type='text' and @name='form_lname']", $lname);
        $this->setInputValue("//input[@name='form_DOB']", $dob);
        $this->setSelectValue("//select[@name='form_sex']", $sex);
        $this->setSelectValue("//select[@name='form_sex_identified']", $sex);

        $this->waitAndClick(
            WebDriverBy::xpath("//*[@id='create']"),
            'Create patient button',
        );

        // Confirm-create: assert the modal + button rendered as a
        // regression signal, then invoke srcConfirmSave() directly.
        //
        // The source-side pattern of finding + clicking #confirmCreate
        // inside the modalframe iframe hits a wiring race where the
        // button becomes WebDriver-clickable before its onclick
        // handler (`dlgclose("srcConfirmSave", false)`) is wired to
        // dlgclose → window[callback] → srcConfirmSave() → form.submit.
        // When the race bites, the click no-ops, the form is never
        // submitted, no redirect, dashboard-header wait times out
        // with no diagnostic signal (source-side E2e/Patient/
        // PatientAddTrait masks the same class of failure with a
        // 3-retry-whole-test loop; per user 2026-08-03 the failure
        // predates any capability / muzzle work we did on the
        // acceptance side).
        //
        // The confirmCreate button's onclick is a single-line
        // `dlgclose("srcConfirmSave", false)` whose only purpose is
        // to close the modal + invoke the parent's srcConfirmSave()
        // callback. srcConfirmSave() itself is `document.forms[0].
        // submit()` (see interface/new/new_comprehensive.php:979).
        // Calling it directly:
        //  - Skips the modalframe-button click-handler wiring race
        //  - Skips the dlgclose iframe/modal cleanup dance
        //  - Skips the window[callback] indirection
        //  - No sleep(5), no elementToBeClickable wait, no race
        //
        // Cleanup: the modal stays open in the DOM, but the
        // post-submit redirect to demographics.php destroys the
        // whole page anyway. Not a concern.
        //
        // Modal-regression signal: because we're skipping the click,
        // we ALSO skip any modal-rendering / button-wiring surface
        // coverage the click would have provided. Compensate with a
        // scoped assertion: switch into the modalframe, verify the
        // #confirmCreate button is present and carries the expected
        // dlgclose onclick, then switch back and invoke the callback.
        // This catches "modal template broke" or "confirmCreate
        // onclick regressed" without depending on click propagation.
        //
        // Phase 13 back-port candidate: source-side PatientAddTrait
        // could adopt the same modal-verify + direct-invoke pattern
        // and drop its 3-retry loop entirely.
        $client->switchTo()->defaultContent();
        $client->waitFor("//iframe[@id='modalframe']", 30);
        $this->switchToIFrame("//iframe[@id='modalframe']");
        // Wait for the button to appear before findElement — the
        // modalframe iframe may still be loading its inner document
        // when we switch into it (rabbit round-1 on #13372).
        $client->waitFor("//*[@id='confirmCreate']", 15);
        $confirmButton = $client->findElement(
            WebDriverBy::xpath("//*[@id='confirmCreate']"),
        );
        self::assertStringContainsString(
            'srcConfirmSave',
            (string) $confirmButton->getAttribute('onclick'),
            'Confirm-create modal rendered but #confirmCreate onclick lost the srcConfirmSave callback wiring — modal-side regression',
        );

        // Two-step bypass — cleanup then submit:
        //
        // (1) Aggressive top-level DOM cleanup — dlgclose stores the
        // callback on a scope we can't reach from executeScript, so
        // invoking it doesn't fire srcConfirmSave. Instead, manually
        // rip out ALL Bootstrap modal wrappers (.dialogModal,
        // .modal-dialog, .modal-backdrop) plus the modalframe iframe,
        // and reset body classes that Bootstrap's modal-open uses.
        // Prevents leftover DOM from intercepting subsequent shell
        // clicks (Kk's "New Encounter" button, Dd's anySearchBox).
        //
        // (2) Submit the form directly from the pat iframe context —
        // srcConfirmSave() is `document.forms[0].submit()` in
        // new_comprehensive.php (loaded inside pat iframe). Calling
        // submit() from that iframe's context is equivalent + no
        // scope-lookup dependency.
        $this->dismissAnyOpenModals();
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $client->executeScript('document.forms[0].submit();');

        // Switch back to defaults, into the patient iframe, wait for
        // the Medical Record Dashboard header — proof the create
        // succeeded and the browser landed on the summary.
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $client->waitFor(
            '//*[text()="Medical Record Dashboard - ' . $fname . ' ' . $lname . '"]',
            30,
        );
        // Second cleanup pass — the dashboard load itself opens more
        // Bootstrap modals (clinical-reminders widget dlgopen, possibly
        // birthday-alert modal for patients with birthday-relevant
        // globals set). These aren't blocked by the CDP alert muzzle
        // — different mechanism (DOM modal, not native window.alert).
        // Nuke them so the next step in the caller's flow
        // (addEncounterViaUi → New Encounter click, openPatientViaUi
        // → search box, or a downstream test's shell click) isn't
        // intercepted.
        $client->switchTo()->defaultContent();
        $this->dismissAnyOpenModals();
    }

    /**
     * Search for an existing patient by lastname via the shell's
     * always-visible anySearchBox (frm_search_globals), click the
     * matching result in the patient-finder iframe, wait for that
     * patient's Medical Record Dashboard. Mirrors source-side
     * PatientOpenTrait::patientOpenIfExist minus the DB-existence
     * pre-check — that check queries the database directly, which
     * violates the acceptance suite's black-box discipline; the
     * caller is responsible for having seeded the patient (typically
     * via addPatientViaUi() earlier in the same test).
     *
     * Post-condition on return: browser is inside the pat iframe
     * with the Medical Record Dashboard rendered for the opened
     * patient. Callers that need to navigate elsewhere should
     * switchTo()->defaultContent() first.
     */
    protected function openPatientViaUi(string $firstname, string $lastname): void
    {
        $client = $this->requireClient();
        $this->muzzleBrowserPrompts();
        $client->switchTo()->defaultContent();
        // Belt-and-suspenders — nuke any leftover modals from prior
        // seeding calls (dashboard load can open reminders / birthday
        // Bootstrap modals that intercept the anySearchBox click).
        $this->dismissAnyOpenModals();

        // Type lastname into anySearchBox. Source-side uses the
        // crawler filterXPath+form API; WebDriver findElement +
        // clear + sendKeys is cleaner and doesn't rely on the
        // crawler being freshly refreshed.
        $anySearchBoxXpath = "//form[@name='frm_search_globals']//input[@name='anySearchBox']";
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath($anySearchBoxXpath),
            ),
        );
        $anySearchBox = $client->findElement(WebDriverBy::xpath($anySearchBoxXpath));
        $anySearchBox->clear();
        $anySearchBox->sendKeys($lastname);

        // Submit the global-search form.
        $this->waitAndClick(
            WebDriverBy::xpath("//button[@id='search_globals']"),
            'Global patient-search submit button',
        );

        // Switch into the patient-finder iframe and click the result
        // matching "Lastname, Firstname". Finder renders each hit as
        // an <a> with exact "Lastname, Firstname" text. Names are
        // wrapped in xpathLiteral() so a name containing ' or " (not
        // possible for today's hex-suffix seed identities but a valid
        // shape a future caller might pass in) does not produce an
        // invalid XPath.
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="fin"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="fin"]');
        $resultXpath = '//a[text()=' . self::xpathLiteral($lastname . ', ' . $firstname) . ']';
        $client->waitFor($resultXpath, 30);
        $client->findElement(WebDriverBy::xpath($resultXpath))->click();

        // Back to default, into the patient iframe, wait for the
        // Medical Record Dashboard header for THIS specific patient.
        // Same assertion shape as addPatientViaUi's post-create wait —
        // the header only renders for the patient we opened, so
        // reaching this wait passing = search+open succeeded.
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $client->waitFor(
            '//*[text()=' . self::xpathLiteral('Medical Record Dashboard - ' . $firstname . ' ' . $lastname) . ']',
            30,
        );
    }

    /**
     * Encode an arbitrary string as an XPath 1.0 string literal safe
     * to embed inside a larger XPath expression. XPath 1.0 has no
     * built-in escape mechanism, so a string containing both ' and "
     * must be composed via concat(). Standard pattern used across
     * WebDriver / Selenium ecosystems.
     */
    private static function xpathLiteral(string $value): string
    {
        if (!str_contains($value, "'")) {
            return "'" . $value . "'";
        }
        if (!str_contains($value, '"')) {
            return '"' . $value . '"';
        }
        // Both quote types present — concat() the pieces around every '.
        // e.g. "Foo's \"Bar\"" -> concat('Foo', "'", 's "Bar"').
        $parts = explode("'", $value);
        return "concat('" . implode("', \"'\", '", $parts) . "')";
    }

    /**
     * Add a fresh encounter for the just-created patient via the
     * per-patient New-Encounter shortcut in the shell. Mirrors the
     * source-side EncounterAddTrait: click the shell's New-Encounter
     * link, fill the encounter form's category + reason, save, wait
     * for the encounter forms iframe to render the encounter title.
     *
     * Post-condition on return: browser is inside the forms iframe
     * with the navbar rendered — same DOM location the encounter-form
     * navbar assertion needs. Caller does NOT need to re-navigate.
     */
    protected function addEncounterViaUi(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        // Belt-and-suspenders — nuke any leftover modals from the
        // preceding addPatientViaUi (dashboard load can open reminders
        // / birthday Bootstrap modals that intercept the "New
        // Encounter" shell click).
        $this->dismissAnyOpenModals();

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
        $this->setSelectValue("//select[@name='pc_catid']", self::SEED_ENCOUNTER_CATEGORY_ID);
        $this->setInputValue("//*[@name='reason']", self::SEED_ENCOUNTER_REASON);

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
                . $this->seedPatientFname() . ' ' . $this->seedPatientLname() . '")]',
            30,
        );
    }

    /**
     * Open an existing encounter for the currently-open patient via
     * the shell's Past Encounters dropdown. Mirrors the source-side
     * EncounterOpenTrait::encounterOpenIfExist minus the DB-existence
     * pre-check — that check queries the database directly, which
     * violates the acceptance suite's black-box discipline; the caller
     * is responsible for having seeded the encounter (typically via
     * addEncounterViaUi() earlier in the same test) and for having
     * navigated back to the patient dashboard so the Past Encounters
     * button is reachable in the shell.
     *
     * Post-condition on return: browser is inside the encounter forms
     * iframe with the navbar rendered — same DOM location the
     * encounter-navbar assertion needs. Caller does NOT need to
     * re-navigate.
     *
     * Note: the "first Office Visit entry in the dropdown" match is
     * intentional — the acceptance seed identity uses category id 5
     * (Office Visit) via SEED_ENCOUNTER_CATEGORY_ID + a single
     * encounter per test instance, so the first Office Visit li is
     * unambiguously the seeded encounter. If a future test seeds
     * multiple encounters per patient, this helper needs a name/date
     * discriminator to select the right entry.
     */
    protected function openEncounterViaUi(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        // Belt-and-suspenders — nuke any leftover modals from the
        // preceding openPatientViaUi (dashboard load can open reminders
        // / birthday Bootstrap modals that intercept the pastEncounters
        // dropdown button click).
        $this->dismissAnyOpenModals();

        // Click the Past Encounters dropdown button in the shell.
        // Source-side XPath: //button[@id="pastEncounters"].
        $this->waitAndClick(
            WebDriverBy::xpath('//button[@id="pastEncounters"]'),
            'Past Encounters dropdown button',
        );

        // Click the first Office Visit entry in the dropdown menu.
        // Source-side XPath:
        //   //ul[contains(@class, "dropdown-menu")]/li[1]/a[1]/span[text()="Office Visit"]
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//ul[contains(@class, "dropdown-menu")]/li[1]/a[1]/span[text()="Office Visit"]',
            ),
            'First Office Visit entry in Past Encounters dropdown',
        );

        // Switch into the encounter iframe → forms iframe → wait for
        // the navbar title for THIS specific patient. Mirrors
        // addEncounterViaUi's final frame-switch chain so the
        // post-condition is identical between "created and opened"
        // and "opened after creation".
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="enc"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="enc"]');
        $client->waitFor('//iframe[@src="forms.php"]', 30);
        $this->switchToIFrame('//iframe[@src="forms.php"]');
        $client->waitFor(
            '//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for '
                . $this->seedPatientFname() . ' ' . $this->seedPatientLname() . '")]',
            30,
        );
    }

    /**
     * Set a text input's value via JS + fire an `input`/`change` event
     * so any bound listeners run. More robust inside iframes than
     * ->sendKeys() for forms that read via JS event listeners.
     */
    protected function setInputValue(string $xpath, string $value): void
    {
        $client = $this->requireClient();
        $applied = $client->executeScript(
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
                if (!el) {
                    return false;
                }
                el.value = value;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            JS_WRAP,
            [$xpath, $value],
        );
        // Fail fast at the seeding call; a silent no-op would surface as
        // a cryptic downstream wait timeout after the form was submitted
        // with an unset field.
        self::assertTrue(
            $applied === true,
            sprintf('setInputValue: no element found for XPath %s', $xpath),
        );
    }

    /**
     * Set a <select>'s value + fire change so any bound listeners run.
     * The category dropdown on the encounter form conditionally reveals
     * fields based on the chosen category, so the change event is
     * necessary for the form to accept the selection.
     */
    protected function setSelectValue(string $xpath, string $value): void
    {
        $client = $this->requireClient();
        $applied = $client->executeScript(
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
                if (!el) {
                    return false;
                }
                el.value = value;
                el.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            JS_WRAP,
            [$xpath, $value],
        );
        self::assertTrue(
            $applied === true,
            sprintf('setSelectValue: no element found for XPath %s', $xpath),
        );
    }

    /**
     * Base names for the seeded staff user. Actual identity is base +
     * random suffix, generated per test instance and returned by
     * seedStaffUsername(). Same shape + rationale as the patient seed
     * identity (see seedPatientFname()) — per-instance suffix isolates
     * each test's seed and prevents collisions on post-upgrade DBs
     * where prior test runs left users behind.
     */
    protected const SEED_STAFF_USERNAME_BASE = 'foobar';
    protected const SEED_STAFF_FIRSTNAME = 'Foo';
    protected const SEED_STAFF_LASTNAME = 'Bar';
    /**
     * Password chosen to satisfy the checkpwd_validation.js
     * `passwordvalidate` rules (>= 8 chars, at least 3 of:
     * lower / upper / digit / special). Matches the source-side
     * UserTestData::PASSWORD value.
     */
    protected const SEED_STAFF_PASSWORD = 'Test12te$t';
    /**
     * Admin re-authentication password — the staff-create form's
     * adminPass field validates the current admin user's password
     * server-side before permitting the create. Stock release image's
     * admin/pass credentials.
     */
    protected const SEED_STAFF_ADMIN_PASSWORD = 'pass';

    private ?string $seedStaffSuffix = null;

    /**
     * Username for the seeded staff user — base + per-instance random
     * suffix. Multiple test instances (PHPUnit creates one per test
     * method) each generate their own suffix, so tests running in the
     * same phase against the same DB never collide, and post-upgrade
     * runs against an already-populated DB won't clash with prior
     * runs' foobar entries. Suffix is hex-only to satisfy the source-
     * side `checkUsername` regex when erx_enable is on (the ONE codepath
     * where character-class restrictions kick in).
     */
    protected function seedStaffUsername(): string
    {
        return self::SEED_STAFF_USERNAME_BASE . ($this->seedStaffSuffix ??= bin2hex(random_bytes(8)));
    }

    /**
     * Add a fresh staff user via the Admin → Users main-menu path.
     * Mirrors the source-side UserAddTrait flow shape: open
     * Admin → Users → click Add User in the admin iframe → fill the
     * new-user form in the modalframe iframe → click Save → wait for
     * modal close → wait for the new user row to appear in the admin
     * iframe's users table.
     *
     * Historical flakiness note: source-side BbCreateStaffTest is the
     * flakiest test in the E2E suite (per user 2026-08-04). Prior
     * debug work pointed at the JS password check on the staff-create
     * form (`checkpwd_validation.js` `passwordvalidate`, or the
     * `checkPasswordStrength` onkeyup handler bound to the `stiltskin`
     * password input) as the suspected culprit, but that was never
     * definitively diagnosed / fixed. This port follows the same
     * defensive patterns the source-side trait grew (JS value
     * assignment via `clearAndType`, field-value verification loop,
     * modal-close diagnostics), adapted for the acceptance suite's
     * black-box discipline: no DB reads for the pre-existence check,
     * no cleanup DELETE in setUp/tearDown, per-instance random
     * usernames replace the DB-level user-uniqueness assumption.
     *
     * Post-condition on return: browser is inside the admin iframe
     * with the users table rendered and the new user row visible.
     * Caller does NOT need to re-navigate.
     */
    protected function addStaffUserViaUi(): void
    {
        $client = $this->requireClient();
        $this->muzzleBrowserPrompts();
        $client->switchTo()->defaultContent();
        // Defensive: performLoginAsAdmin already dismissed Product
        // Registration modal, but if a future release image adds a
        // login-time modal in a different DOM path, this catches it.
        $this->dismissAnyOpenModals();

        // Open Admin → Users. The submenu selector shape matches the
        // Patient-menu form used by addPatientViaUi — divs with
        // dropdown-toggle / menuLabel classes rather than <a>/<span>.
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//div[@id="mainMenu"]//div[normalize-space(text())="Admin"'
                . ' and contains(concat(" ",normalize-space(@class)," ")," dropdown-toggle ")]',
            ),
            'Admin top-level menu',
        );
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//div[@id="mainMenu"]//div[normalize-space(text())="Users"'
                . ' and contains(concat(" ",normalize-space(@class)," ")," menuLabel ")]',
            ),
            'Users menu item',
        );
        $this->assertActiveTab('User / Groups');

        // Switch into the admin iframe and click Add User. Use a
        // longer waitFor on the iframe because the users tab does a
        // full server-render round-trip on activation, unlike the
        // patient tab which fills asynchronously.
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="adm"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="adm"]');
        $this->waitAndClick(
            WebDriverBy::xpath("//a[text()='Add User']"),
            'Add User link in Users admin iframe',
        );

        // The Add User link opens a dlgopen()-driven modal that loads
        // usergroup_admin_add.php into a nested #modalframe iframe.
        // Switch out to defaultContent first, then wait for + switch
        // into the modalframe.
        $client->switchTo()->defaultContent();
        $client->waitFor("//iframe[@id='modalframe']", 30);
        $this->switchToIFrame("//iframe[@id='modalframe']");

        // Wait for the new_user form + the rumple (username) input to
        // be clickable — the form is rendered before its JS bindings
        // apply, and clearAndType via JS value-set can silently no-op
        // if the field's not yet in a state where events would fire
        // listeners.
        $client->waitFor("//form[@id='new_user']", 30);
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//input[@type='text' and @name='rumple']"),
            ),
        );
        // Wait for submitform() to be defined before proceeding — the
        // Save button's onclick calls it, and if it's not yet in scope
        // the click no-ops silently (mirrors the source-side gate at
        // UserAddTrait.php:98).
        $client->wait(15)->until(
            fn(WebDriver $driver): bool => $driver instanceof \Facebook\WebDriver\JavaScriptExecutor
                && $driver->executeScript('return typeof submitform === "function";') === true,
        );

        // Fill required fields via JS value assignment. The password
        // field (`stiltskin`) has `onkeyup="checkPasswordStrength(this);"`
        // wired on the source-side; going through JS value-set + input
        // /change events (not keyup) side-steps the strength-meter
        // race entirely. Fill in the order the source-side trait
        // established: fname/lname/adminPass/stiltskin, then rumple
        // last so earlier field handlers can't overwrite the username.
        $username = $this->seedStaffUsername();
        $this->setInputValue("//input[@name='fname']", self::SEED_STAFF_FIRSTNAME);
        $this->setInputValue("//input[@name='lname']", self::SEED_STAFF_LASTNAME);
        $this->setInputValue("//input[@name='adminPass']", self::SEED_STAFF_ADMIN_PASSWORD);
        $this->setInputValue("//input[@name='stiltskin']", self::SEED_STAFF_PASSWORD);
        $this->setInputValue("//input[@name='rumple']", $username);

        // Verify each field kept its value. Under CI load, JS event
        // handlers wired on some fields (email autocorrect,
        // password-strength widget) have historically cleared or
        // rewritten values after our set. If a field silently reverted,
        // the server-side create fails silently (modal stays open, no
        // diagnostic), so guard here.
        $client->wait(10)->until(
            fn(WebDriver $driver): bool => $driver->findElement(WebDriverBy::name('rumple'))->getAttribute('value') === $username
                && $driver->findElement(WebDriverBy::name('stiltskin'))->getAttribute('value') === self::SEED_STAFF_PASSWORD
                && $driver->findElement(WebDriverBy::name('fname'))->getAttribute('value') === self::SEED_STAFF_FIRSTNAME
                && $driver->findElement(WebDriverBy::name('lname'))->getAttribute('value') === self::SEED_STAFF_LASTNAME
                && $driver->findElement(WebDriverBy::name('adminPass'))->getAttribute('value') === self::SEED_STAFF_ADMIN_PASSWORD,
        );

        // Click Save. #form_save's onclick calls submitform() which
        // validates → passwordvalidate → AJAX POST → dlgclose('reload')
        // on success. If validation fails the muzzled alert() no-ops
        // and dlgclose never fires; the wait-for-modal-close below
        // will time out and surface the failure.
        $this->waitAndClick(
            WebDriverBy::xpath("//a[@id='form_save']"),
            'Save (create user) button',
        );

        // Switch to defaultContent so the modal-close wait sees the
        // top-level document — the modalframe iframe disappearing IS
        // the signal we want.
        $client->switchTo()->defaultContent();
        // On success the server echoes an empty response body and the
        // AJAX .done() handler fires dlgclose('reload'), which closes
        // the modal + reloads the admin iframe with the new users
        // list. Historically the AJAX-handler-to-dlgclose chain has
        // a ~30% single-shot flake rate (source-side Bb test masks
        // this with a 3-retry-whole-test loop; verified via rel-820
        // sync PR #13390 exhibiting 2/6 failures at exactly this
        // wait). The recovery block below turns the flake into a
        // benign retry: if the modal-close wait times out, force-
        // clean modal DOM + refresh admin iframe, then let the
        // downstream users-table row wait act as the oracle. If the
        // user was actually created (typical flake mode: server
        // succeeded, JS handler race lost dlgclose), the row wait
        // succeeds and the test passes. If the user was NOT created
        // (real regression), the row wait fails and the test surfaces
        // the underlying issue.
        try {
            $client->wait(30)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(
                    WebDriverBy::xpath("//iframe[@id='modalframe']"),
                ),
            );
            // Positive-path breadcrumb. Paired with the recovery-path
            // breadcrumb in the catch block below — together they let
            // us confirm the recovery mechanism is running end-to-end
            // by grepping CI logs. Without the happy-path breadcrumb,
            // "0 recovery-path breadcrumbs across 18 runs" is ambiguous:
            // could mean "Bb never flaked" OR "the recovery logic was
            // wired wrong and always short-circuits." Emitting on the
            // clean path proves the wait actually completed via the
            // expected non-catch code path.
            fwrite(STDERR, "[acceptance/Bb] Modal-close wait passed cleanly.\n");
        } catch (TimeoutException) {
            // STDERR breadcrumb so CI logs show when the recovery
            // path fired — lets us track the flake rate over time
            // without needing a green-vs-red signal.
            fwrite(
                STDERR,
                "[acceptance/Bb] Modal-close wait timed out after Save; entering recovery path "
                . "(the AJAX-handler-to-dlgclose chain has a documented flake mode).\n",
            );
            // Force-clean modal DOM (dlgclose didn't fire; strip
            // Bootstrap modal wrappers manually).
            $this->dismissAnyOpenModals();
            // Refresh the admin iframe so its content is fresh —
            // if the user was created, the reload will populate the
            // users table with the new row.
            $client->switchTo()->defaultContent();
            $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="adm"]', 30);
            $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="adm"]');
            $client->executeScript('window.location.reload();');
            $client->switchTo()->defaultContent();
        }

        // Users-table row wait serves as the final oracle regardless
        // of which path we took (clean modal-close OR recovery). If
        // the user exists, the row is there. If not (real regression
        // OR AJAX response was actually an error masked by muzzled
        // alert), this wait times out and surfaces the failure.
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="adm"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="adm"]');
        $client->waitFor("//table//a[text()=" . self::xpathLiteral($username) . "]", 30);
    }

    // ==============================================================
    // Persistence-flow seeding helpers
    // --------------------------------------------------------------
    // Fixed-identity fixtures for tests that need to survive a docker
    // upgrade cycle. Unlike the random per-instance identities above
    // (Ftest<suffix>/Ltest<suffix>/foobar<suffix>), these use stable
    // constants so the fresh-install phase of the upgrade scenario
    // matrix cell seeds a fixture that the post-upgrade phase can
    // still look up by the same identifier.
    //
    // Each helper is idempotent: check-then-create. On the first run
    // against a fresh DB (fresh-install phase, fresh-install-from /
    // -to cells) the helper creates the fixture. On subsequent runs
    // against a DB where the fixture already exists (post-upgrade
    // phase, or local dev iteration against a persisted volume) the
    // helper skips creation and lets the assertion run against the
    // pre-existing state.
    // ==============================================================

    /** Fixed-identity persist-through-upgrade fixture patient. */
    protected const PERSIST_PATIENT_FNAME = 'PersistCheck';
    protected const PERSIST_PATIENT_LNAME = 'PersistPatient';
    protected const PERSIST_PATIENT_DOB = '1970-01-01';
    protected const PERSIST_PATIENT_SEX = 'Male';

    /**
     * Fixed-identity target date for the persist-through-upgrade
     * appointment. Far future so it's obviously a fixture (never
     * collides with real appointment data a human might inspect on
     * "today"), close enough to be within any reasonable calendar-
     * navigation UI horizon (some year pickers cap at century+).
     */
    protected const PERSIST_APPT_DATE = '2099-06-15';

    /**
     * In-office slot bracketing the appointment — 08:00 for 540
     * minutes (9 hours) covers standard business hours + the 10:00
     * appointment time below. Prevents any "appointment scheduled
     * outside working hours" validation modal from firing when the
     * appointment is created.
     */
    protected const PERSIST_INOFFICE_HOUR = 8;
    protected const PERSIST_INOFFICE_MINUTE = 0;
    protected const PERSIST_INOFFICE_DURATION_MIN = 540;

    /**
     * Appointment identity. Title is used as the existence-check
     * discriminator on the Flow Board results table.
     */
    protected const PERSIST_APPT_HOUR = 10;
    protected const PERSIST_APPT_MINUTE = 0;
    protected const PERSIST_APPT_DURATION_MIN = 30;
    protected const PERSIST_APPT_TITLE = 'PersistCheckAppt';
    /** Office Visit category. Matches SEED_ENCOUNTER_CATEGORY_ID. */
    protected const PERSIST_APPT_CATEGORY_ID = '5';
    /** In Office category (openemr_postcalendar_categories row 2). */
    protected const PERSIST_INOFFICE_CATEGORY_ID = '2';

    /** Fixed-identity persist-through-upgrade fixture document. */
    protected const PERSIST_DOC_FILENAME = 'persist-check.png';
    /** Medical Record category (documents.parent_id=3 in the seed schema). */
    protected const PERSIST_DOC_CATEGORY_ID = '3';

    /**
     * Ensure we're on the SPA shell (main menu present). Persistence
     * tests navigate to standalone report URLs (Flow Board) for
     * existence checks + assertions; those URLs render outside the
     * SPA shell so subsequent main-menu clicks would fail. Direct
     * navigation to /interface/main/tabs/main.php bounces to login
     * when the required token_main URL param is absent, so the
     * robust reset is a full re-login (idempotent — the session
     * cookie is preserved so login is a no-op auth-wise; it's the
     * shell + Knockout render we want back).
     */
    protected function ensureShellContext(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $onShell = $client->executeScript(
            'return document.getElementById("mainMenu") !== null'
            . ' && document.getElementById("mainMenu").children.length > 0;',
        );
        if ($onShell === true) {
            return;
        }
        $this->performLoginAsAdmin();
    }

    /**
     * Idempotent: if the persist-check patient already exists, open
     * them and return their pid; otherwise create + return the new
     * pid. Post-condition on return: browser is inside the pat iframe
     * on the persist patient's Medical Record Dashboard.
     */
    protected function seedPersistPatientIfMissing(): int
    {
        $client = $this->requireClient();
        if ($this->persistPatientExists()) {
            // Existence check leaves us on the patient's dashboard —
            // extract pid from the pat iframe URL and return.
            return $this->currentPatientPidFromPatIframe();
        }
        $this->addPatientViaUiWithIdentity(
            self::PERSIST_PATIENT_FNAME,
            self::PERSIST_PATIENT_LNAME,
            self::PERSIST_PATIENT_DOB,
            self::PERSIST_PATIENT_SEX,
        );
        return $this->currentPatientPidFromPatIframe();
    }

    /**
     * Search for the persist patient via the shell's anySearchBox.
     * If the finder result appears within a short window, click it
     * (opens the patient's dashboard) and return true. Otherwise
     * return false — caller falls through to create.
     *
     * This mirrors openPatientViaUi's search + click shape but with
     * a short timeout on the finder-result wait, so a missing patient
     * costs ~5s instead of ~30s.
     */
    private function persistPatientExists(): bool
    {
        $client = $this->requireClient();
        $this->muzzleBrowserPrompts();
        $client->switchTo()->defaultContent();
        $this->dismissAnyOpenModals();

        $anySearchBoxXpath = "//form[@name='frm_search_globals']//input[@name='anySearchBox']";
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath($anySearchBoxXpath),
            ),
        );
        $anySearchBox = $client->findElement(WebDriverBy::xpath($anySearchBoxXpath));
        $anySearchBox->clear();
        $anySearchBox->sendKeys(self::PERSIST_PATIENT_LNAME);
        $this->waitAndClick(
            WebDriverBy::xpath("//button[@id='search_globals']"),
            'Global patient-search submit button (persist-check existence probe)',
        );

        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="fin"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="fin"]');
        $resultXpath = '//a[text()=' . self::xpathLiteral(
            self::PERSIST_PATIENT_LNAME . ', ' . self::PERSIST_PATIENT_FNAME,
        ) . ']';
        try {
            $client->waitFor($resultXpath, 5);
        } catch (\Facebook\WebDriver\Exception\TimeoutException) {
            // Not found — return false, caller will create.
            $client->switchTo()->defaultContent();
            return false;
        }
        $client->findElement(WebDriverBy::xpath($resultXpath))->click();
        // Wait for the dashboard to render so caller can extract pid.
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $client->waitFor(
            '//*[text()=' . self::xpathLiteral(
                'Medical Record Dashboard - ' . self::PERSIST_PATIENT_FNAME . ' ' . self::PERSIST_PATIENT_LNAME,
            ) . ']',
            30,
        );
        return true;
    }

    /**
     * Extract the patient's pid from the pat iframe's current URL.
     * Self-sufficient: switches into the pat iframe explicitly (some
     * callers reach here after the trait's terminal switchTo(
     * defaultContent) call) so no assumption about current frame
     * context.
     */
    private function currentPatientPidFromPatIframe(): int
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $url = $client->executeScript('return window.location.href;');
        if (!is_string($url)) {
            self::fail('Could not read pat iframe URL to extract pid');
        }
        // URL may carry pid as either `?pid=N` (dashboard nav) or
        // `?set_pid=N` (session-setter used by the finder click).
        if (preg_match('/[?&](?:set_)?pid=(\d+)/', (string) $url, $m) !== 1) {
            self::fail("pat iframe URL has no pid/set_pid param: {$url}");
        }
        return (int) $m[1];
    }

    /**
     * Idempotent: if the persist appointment already exists (identified
     * by BOTH title + patient lastname in a single Flow Board row —
     * distinguishes it from any unrelated fixture on the same date),
     * skip. Otherwise create the In Office slot + the appointment
     * inside its window. Single existence oracle for both fixtures
     * because they're coupled: the InOffice slot only matters as a
     * prerequisite for creating an appointment without hitting the
     * outside-hours prompt — no InOffice, no way to have created the
     * appointment. So "appointment exists" implies "InOffice exists".
     */
    protected function seedPersistAppointmentIfMissing(int $patientPid): void
    {
        if ($this->persistAppointmentExistsOnFlowBoard()) {
            return;
        }
        // Just create the Office Visit appointment. We do NOT
        // pre-seed an In Office slot because the standard newEvt()-
        // opened modal only offers categories with pc_cattype=0
        // (regular event categories); In Office (pc_catid=2) has
        // pc_cattype=1 and is only offered when the modal is opened
        // with the ?prov=true param (a separate provider-availability
        // flow). Setting form_category to a value not in the
        // dropdown's options renders as blank + fails save.
        //
        // The outside-hours prompt that would otherwise fire on
        // save (find_appt_popup.php:479 "Provider not available,
        // use it anyway?") is a native browser confirm(), which
        // muzzleBrowserPrompts() overrides to return true — so the
        // save proceeds regardless of provider availability.
        $this->createCalendarEvent(
            date: self::PERSIST_APPT_DATE,
            categoryId: self::PERSIST_APPT_CATEGORY_ID,
            title: self::PERSIST_APPT_TITLE,
            hour: self::PERSIST_APPT_HOUR,
            minute: self::PERSIST_APPT_MINUTE,
            durationMin: self::PERSIST_APPT_DURATION_MIN,
            patientPid: $patientPid,
        );
    }

    /**
     * True iff Flow Board shows a row for the persist appointment
     * on the target date. Discriminator is compound: patient
     * lastname AND the appointment start time. Flow Board does NOT
     * render the appointment title in the row (rows show provider /
     * date / time / patient / category / status), so the title is
     * unavailable as a match key on this surface. Patient+time on
     * the fixed target date is unique because no fresh acceptance
     * DB seeds anything on 2099-06-15, and the persist test only
     * ever creates one appointment for the persist patient at 10:00.
     *
     * Scoped to `table.table` (the results table's Bootstrap
     * class) so the report's own filter-form table doesn't
     * satisfy the check.
     */
    private function persistAppointmentExistsOnFlowBoard(): bool
    {
        $this->openFlowBoardFilteredToPersistDate();
        $client = $this->requireClient();
        // Time format on Flow Board rows is 24h "HH:MM" (e.g.
        // "10:00"). Compose the expected time literal.
        $time = sprintf('%02d:%02d', self::PERSIST_APPT_HOUR, self::PERSIST_APPT_MINUTE);
        $hasRow = $client->executeScript(
            "var rows = document.querySelectorAll('table.table tbody tr');"
            . "for (var i = 0; i < rows.length; i++) {"
            . "  var t = rows[i].textContent;"
            . "  if (t.indexOf(arguments[0]) !== -1 && t.indexOf(arguments[1]) !== -1) return true;"
            . "}"
            . "return false;",
            [$time, self::PERSIST_PATIENT_LNAME],
        );
        return $hasRow === true;
    }

    /**
     * Navigate to Patient Flow Board, set From/To = PERSIST_APPT_DATE,
     * click Submit, wait for the result page to render. Post-
     * condition: browser is at top level with the Flow Board results
     * table loaded.
     */
    private function openFlowBoardFilteredToPersistDate(): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $this->dismissAnyOpenModals();
        // GET the empty report page — filter form renders with an
        // empty results panel. The report reads filter values from
        // $_POST and requires CSRF, so URL params are ignored. Fill
        // the From/To date fields + click Submit to trigger the
        // POST-with-CSRF.
        $client->request('GET', '/interface/reports/patient_flow_board_report.php');
        $client->waitFor("//input[@name='form_from_date']", 30);
        $this->setInputValue("//input[@name='form_from_date']", self::PERSIST_APPT_DATE);
        $this->setInputValue("//input[@name='form_to_date']", self::PERSIST_APPT_DATE);
        // Submit is an <a class='btn btn-save'> whose onclick sets
        // form_refresh=true + calls $("#theform").submit(). Trigger
        // the same behavior directly rather than clicking (the anchor
        // wraps its text in mixed whitespace + PHP xlt output).
        $client->executeScript(
            'document.getElementById("form_refresh").value = "true";'
            . 'document.getElementById("theform").submit();',
        );
        // Wait for the results panel to render — either a data table
        // or the "no matching records" message.
        $client->wait(30)->until(
            fn(JavaScriptExecutor $d): bool => (bool) $d->executeScript(
                "return document.querySelector('table.table') !== null"
                . " || document.body.textContent.indexOf('No matching records') !== -1"
                . " || document.body.textContent.indexOf('no matching records') !== -1;",
            ),
        );
    }

    /**
     * Assert the persist appointment appears on Flow Board for the
     * target date. Fails the test with a diagnostic if the row is
     * missing.
     */
    protected function assertPersistAppointmentOnFlowBoard(): void
    {
        self::assertTrue(
            $this->persistAppointmentExistsOnFlowBoard(),
            sprintf(
                'Persist appointment "%s" for %s %s not found on Flow Board for %s',
                self::PERSIST_APPT_TITLE,
                self::PERSIST_PATIENT_FNAME,
                self::PERSIST_PATIENT_LNAME,
                self::PERSIST_APPT_DATE,
            ),
        );
    }

    /**
     * Create a calendar event via the Calendar iframe's newEvt() JS
     * hook. Opens add_edit_event.php inside #modalframe, fills fields,
     * clicks Save. Idempotent-check is caller's responsibility — this
     * helper always creates.
     */
    private function createCalendarEvent(
        string $date,
        string $categoryId,
        string $title,
        int $hour,
        int $minute,
        int $durationMin,
        ?int $patientPid,
    ): void {
        $client = $this->requireClient();
        $this->muzzleBrowserPrompts();
        $client->switchTo()->defaultContent();

        // Persistence-flow tests reach this helper AFTER a Flow Board
        // existence check that navigated to the standalone report URL
        // (no #mainMenu present). main.php GET without token_main
        // bounces to login, so re-establish shell context via the
        // full performLoginAsAdmin flow — expensive but robust.
        $this->ensureShellContext();
        $this->dismissAnyOpenModals();

        // Open Calendar tab.
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//div[@id="mainMenu"]//div[normalize-space(text())="Calendar"'
                . ' and contains(concat(" ",normalize-space(@class)," ")," menuLabel ")]',
            ),
            'Calendar main-menu item',
        );
        $client->waitFor('//iframe[@name="cal"]', 30);
        $this->switchToIFrame('//iframe[@name="cal"]');
        // newEvt(userid, hour, minute, "YYYYMMDD", ?, ?) opens the
        // add-event modal via dlgopen. userid=1 = Administrator (the
        // shipped default provider). Date is compact YYYYMMDD, not
        // ISO YYYY-MM-DD.
        $compactDate = str_replace('-', '', $date);
        $client->wait(30)->until(
            fn(JavaScriptExecutor $d): bool => (bool) $d->executeScript('return typeof newEvt === "function";'),
        );
        $client->executeScript(
            sprintf('newEvt(1, %d, %d, "%s", 0, 0);', $hour, $minute, $compactDate),
        );

        // Switch to top-level, wait for modalframe, switch in.
        $client->switchTo()->defaultContent();
        $client->waitFor("//iframe[@id='modalframe']", 30);
        $this->switchToIFrame("//iframe[@id='modalframe']");
        $client->waitFor("//select[@name='form_category']", 30);
        $client->wait(15)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//input[@id='form_save']"),
            ),
        );

        // Fill fields. Category triggers form UI rearrangement (in/out
        // office hides patient row) so set it FIRST via change event.
        $this->setSelectValue("//select[@name='form_category']", $categoryId);
        $this->setInputValue("//input[@name='form_title']", $title);
        // form_date is a picker input; setInputValue's input/change
        // events are enough to update the underlying value.
        $this->setInputValue("//input[@name='form_date']", $date);
        $this->setInputValue("//input[@name='form_hour']", (string) $hour);
        $this->setInputValue("//input[@name='form_minute']", (string) $minute);
        $this->setInputValue("//input[@name='form_duration']", (string) $durationMin);
        if ($patientPid !== null) {
            // form_pid is the machine-readable hidden id (server
            // reads this); form_patient is the visible display name
            // (client-side submitform validation checks it isn't
            // empty via the Click-to-select placeholder replacement
            // sel_patient() would do). Set both.
            $this->setInputValue("//input[@name='form_pid']", (string) $patientPid);
            $this->setInputValue(
                "//input[@name='form_patient']",
                self::PERSIST_PATIENT_FNAME . ' ' . self::PERSIST_PATIENT_LNAME,
            );
        }

        // Save. Muzzle handles any confirm() for outside-hours checks.
        $client->findElement(WebDriverBy::xpath("//input[@id='form_save']"))->click();

        // Wait for modal to close (add_edit_event.php POSTs to
        // itself; on success it echoes dlgclose() which fires on
        // the parent frame).
        $client->switchTo()->defaultContent();
        $client->wait(30)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::xpath("//iframe[@id='modalframe']"),
            ),
        );
        $this->dismissAnyOpenModals();
    }

    /**
     * Idempotent: if the persist document already exists in the
     * patient's Medical Record category, skip. Otherwise generate a
     * small in-memory PNG and upload it.
     */
    protected function seedPersistDocumentIfMissing(int $patientPid): void
    {
        if ($this->persistDocumentExists($patientPid)) {
            return;
        }
        $this->uploadPersistDocument($patientPid);
    }

    /**
     * True iff the persist document filename appears in the patient's
     * Medical Record category document listing.
     */
    private function persistDocumentExists(int $patientPid): bool
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $this->dismissAnyOpenModals();
        $client->request(
            'GET',
            "/controller.php?document&upload&patient_id={$patientPid}&parent_id=" . self::PERSIST_DOC_CATEGORY_ID,
        );
        // Wait for the upload widget's file input as the "page
        // scaffold rendered" gate — #source-name is the file input
        // that this same URL is going to interact with for uploads.
        // The prior body-fallback selector resolved instantly and
        // caused the filename scan to race Angular's category-tree
        // fetch (ng-init="getCategories(0)" populates the tree
        // asynchronously), returning false while the tree was still
        // being built.
        $client->waitFor("//input[@id='source-name']", 30);
        // Now bounded-wait for the filename link to appear in the
        // Angular-rendered category tree. If it's absent within the
        // window, the doc genuinely doesn't exist and the caller
        // will fall through to upload. If present, existence
        // confirmed. 5s is generous — the Angular fetch typically
        // completes in <1s locally.
        try {
            $client->wait(5)->until(
                fn(JavaScriptExecutor $d): bool => (bool) $d->executeScript(
                    "var links = document.querySelectorAll('a');"
                    . "for (var i = 0; i < links.length; i++) {"
                    . "  if (links[i].textContent.indexOf(arguments[0]) !== -1) return true;"
                    . "}"
                    . "return false;",
                    [self::PERSIST_DOC_FILENAME],
                ),
            );
            return true;
        } catch (TimeoutException) {
            return false;
        }
    }

    /**
     * Upload the persist document. Generates a small in-memory PNG
     * (1×1 pixel is enough — the assertion checks presence + open,
     * not content), writes it to a per-run tmp path with the fixed
     * PERSIST_DOC_FILENAME, drives the file input, clicks Upload,
     * waits for the file to appear in the tree.
     */
    private function uploadPersistDocument(int $patientPid): void
    {
        $client = $this->requireClient();
        // 1×1 transparent PNG — minimal valid PNG bytes.
        $pngBytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkAAIAAAoAAv/lxKUAAAAASUVORK5CYII=',
        );
        $tmpPath = '/tmp/' . self::PERSIST_DOC_FILENAME;
        file_put_contents($tmpPath, $pngBytes);

        $client->switchTo()->defaultContent();
        $this->dismissAnyOpenModals();
        $client->request(
            'GET',
            "/controller.php?document&upload&patient_id={$patientPid}&parent_id=" . self::PERSIST_DOC_CATEGORY_ID,
        );
        // File input is name="file[]" id="source-name". Attach a
        // LocalFileDetector so sendKeys uploads the local file bytes
        // over the WebDriver protocol to the Selenium node's Chrome
        // (which runs in a separate container and can't see our
        // filesystem path). Only works on RemoteWebElement — the
        // instanceof narrow guards against future driver swaps.
        $client->waitFor("//input[@id='source-name']", 30);
        $fileInput = $client->findElement(WebDriverBy::xpath("//input[@id='source-name']"));
        if ($fileInput instanceof RemoteWebElement) {
            $fileInput->setFileDetector(new LocalFileDetector());
        }
        $fileInput->sendKeys($tmpPath);
        // Click the Upload button (button/input[type=submit] with
        // Upload text in the Source File Path form section).
        $this->waitAndClick(
            WebDriverBy::xpath("//button[normalize-space(text())='Upload' or @value='Upload']"
                . " | //input[@type='submit' and (@value='Upload' or normalize-space(@value)='Upload')]"),
            'Documents Upload button',
        );
        // After upload the page reloads; wait for the file to appear
        // as a link in the tree.
        $client->wait(60)->until(
            fn(JavaScriptExecutor $d): bool => (bool) $d->executeScript(
                "var links = document.querySelectorAll('a');"
                . "for (var i = 0; i < links.length; i++) {"
                . "  if (links[i].textContent.indexOf(arguments[0]) !== -1) return true;"
                . "}"
                . "return false;",
                [self::PERSIST_DOC_FILENAME],
            ),
        );
    }

    /**
     * Open the persist document + wait for the viewer panel to render
     * something (the file's img element for PNG viewing, or any
     * viewer content). Post-condition on return: browser is at top
     * level with the viewer visible.
     */
    protected function assertPersistDocumentOpenable(int $patientPid): void
    {
        $client = $this->requireClient();
        $client->switchTo()->defaultContent();
        $this->dismissAnyOpenModals();
        $client->request(
            'GET',
            "/controller.php?document&upload&patient_id={$patientPid}&parent_id=" . self::PERSIST_DOC_CATEGORY_ID,
        );
        // Find + click the file link in the tree.
        $client->wait(30)->until(
            fn(JavaScriptExecutor $d): bool => (bool) $d->executeScript(
                "var links = document.querySelectorAll('a');"
                . "for (var i = 0; i < links.length; i++) {"
                . "  if (links[i].textContent.indexOf(arguments[0]) !== -1) return true;"
                . "}"
                . "return false;",
                [self::PERSIST_DOC_FILENAME],
            ),
        );
        // Click via JS since the tree link may be inside a
        // conditionally-visible subtree.
        $clicked = $client->executeScript(
            "var links = document.querySelectorAll('a');"
            . "for (var i = 0; i < links.length; i++) {"
            . "  if (links[i].textContent.indexOf(arguments[0]) !== -1) { links[i].click(); return true; }"
            . "}"
            . "return false;",
            [self::PERSIST_DOC_FILENAME],
        );
        self::assertTrue($clicked === true, 'Failed to click persist doc link in tree');
        // Assertion (a) — user-visible render path: wait for the
        // viewer's retrieve <iframe> to be present. OpenEMR's
        // Documents viewer wires an <iframe src="/controller.php?
        // document&retrieve&...&as_file=false"> for the Contents
        // tab, and Chrome renders inline for image/* content-type.
        // Presence of the iframe with the retrieve URL proves the
        // viewer resolved the doc + wired the display element.
        $client->wait(30)->until(
            fn(JavaScriptExecutor $d): bool => (bool) $d->executeScript(
                "return document.querySelector('iframe[src*=\"retrieve\"][src*=\"document_id\"]') !== null;",
            ),
        );

        // Assertion (b) — byte-level: fetch the retrieve URL via
        // browser fetch() (uses the current session cookie) and
        // verify:
        //   1. HTTP 200 — proves the file exists on disk AND apache
        //      can read it. Persistence-through-upgrade signal: the
        //      fsupgrade-N.sh passes must preserve
        //      sites/default/documents/ directory contents + file
        //      permissions across the version bump. A 403 here would
        //      mean the file exists but perms broke; a 404 would
        //      mean the file itself was lost.
        //   2. Content-type is image/png — proves mime-detection
        //      + response-header shape survived.
        //   3. First 8 bytes match the PNG magic signature
        //      (89 50 4e 47 0d 0a 1a 0a) — proves the blob is
        //      byte-identical, not corrupted / replaced with an
        //      error page rendered with a 200 status.
        $viewUrl = $client->getCurrentURL();
        if (preg_match('/[?&]doc_id=(\d+)/', (string) $viewUrl, $m) !== 1) {
            self::fail("Doc view URL has no doc_id param: {$viewUrl}");
        }
        $docId = (int) $m[1];
        $retrieveUrl = "/controller.php?document&retrieve&patient_id={$patientPid}&document_id={$docId}";
        // Async fetch needs a script timeout — default is 0.
        $webDriver = $client->getWebDriver();
        if ($webDriver instanceof RemoteWebDriver) {
            $webDriver->manage()->timeouts()->setScriptTimeout(30);
        }
        $result = $client->executeAsyncScript(
            <<<'JS'
                var cb = arguments[arguments.length - 1];
                var url = arguments[0];
                fetch(url, {credentials: 'include'})
                    .then(function (r) {
                        return r.arrayBuffer().then(function (buf) {
                            var hex = Array.from(new Uint8Array(buf.slice(0, 8)))
                                .map(function (b) { return b.toString(16).padStart(2, '0'); })
                                .join('');
                            cb({
                                status: r.status,
                                type: r.headers.get('content-type') || '',
                                len: buf.byteLength,
                                head: hex,
                            });
                        });
                    })
                    .catch(function (e) { cb({error: e.toString()}); });
            JS,
            [$retrieveUrl],
        );
        self::assertIsArray(
            $result,
            'Retrieve fetch returned non-array (script error): ' . var_export($result, true),
        );
        self::assertArrayNotHasKey(
            'error',
            $result,
            'Retrieve fetch failed with JS error: ' . (is_string($result['error'] ?? null) ? $result['error'] : ''),
        );
        self::assertSame(
            200,
            $result['status'] ?? null,
            sprintf(
                'Retrieve blob fetch returned status %s (expected 200). Most likely means the '
                . 'sites/default/documents/ file is missing (persistence regression: fsupgrade lost '
                . 'the directory), or apache lost read permission on the file.',
                is_scalar($result['status'] ?? null) ? (string) $result['status'] : 'null',
            ),
        );
        self::assertStringContainsString(
            'image/png',
            is_string($result['type'] ?? null) ? $result['type'] : '',
            'Retrieve blob content-type is not image/png',
        );
        self::assertGreaterThan(
            0,
            is_int($result['len'] ?? null) ? $result['len'] : 0,
            'Retrieve blob is zero bytes',
        );
        self::assertSame(
            '89504e470d0a1a0a',
            strtolower(is_string($result['head'] ?? null) ? $result['head'] : ''),
            'Retrieve blob does not start with PNG magic bytes — file may be corrupted or replaced with an error page rendered with a 200 status',
        );
    }
}
