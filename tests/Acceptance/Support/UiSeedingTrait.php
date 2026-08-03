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

use Facebook\WebDriver\Remote\RemoteWebDriver;
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
        return $this->seedPatientSuffix ??= bin2hex(random_bytes(3));
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
     * to a no-op so no popup fires. The grid path in BrowserSession
     * also sets unhandledPromptBehavior=accept as an independent
     * safety net for any prompt the JS override doesn't cover.
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
        $this->setInputValue("//input[@type='text' and @name='form_fname']", $this->seedPatientFname());
        $this->setInputValue("//input[@type='text' and @name='form_lname']", $this->seedPatientLname());
        $this->setInputValue("//input[@name='form_DOB']", self::SEED_PATIENT_DOB);
        $this->setSelectValue("//select[@name='form_sex']", self::SEED_PATIENT_SEX);
        $this->setSelectValue("//select[@name='form_sex_identified']", self::SEED_PATIENT_SEX);

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
        // its JS finishes wiring the confirm handler. Same 5s wait the
        // source-side PatientAddTrait uses.
        sleep(5);
        $client->findElement(WebDriverBy::xpath("//*[@id='confirmCreate']"))->click();

        // No explicit alert-wait: after form submit the browser
        // navigates to the new patient's Medical Record Dashboard,
        // where a "New Due Clinical Reminders" alert(...) would fire
        // from library/clinical_rules.php via <img onload>. The
        // muzzleBrowserPrompts() call at the top of this method has
        // already overridden window.alert to a no-op via CDP, so
        // the emitter fires but produces no popup. Prior versions
        // tried to explicitly wait for the alert (5s → 15s → 60s)
        // and misread it as a dup-check alert, accumulating layers
        // of wrong-shape mitigation (#13348) — see this file's git log.
        // Waiting for the real post-condition (the dashboard header)
        // is deterministic.
        //
        // Switch back to defaults, into the patient iframe, wait for
        // the Medical Record Dashboard header — proof the create
        // succeeded and the browser landed on the summary.
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        // 60s (not the Panther-default 30s) — the post-submit
        // dashboard render includes the reminders-widget compute
        // which can slip past 30s on slow ARM CI runners. Matches
        // the ARM-tolerance ceiling established for the alert wait
        // in #13348.
        $client->waitFor(
            '//*[text()="Medical Record Dashboard - ' . $this->seedPatientFname() . ' ' . $this->seedPatientLname() . '"]',
            60,
        );
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
        // 60s tolerance for the ARM-CI-runner-slow dashboard render —
        // matches the addPatientViaUi() pattern above.
        $client->waitFor(
            '//*[text()=' . self::xpathLiteral('Medical Record Dashboard - ' . $firstname . ' ' . $lastname) . ']',
            60,
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
        // 60s tolerance for ARM-CI-runner-slow encounter render —
        // same class of post-navigation content-render wait as the
        // dashboard-header waits above.
        $client->waitFor(
            '//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for '
                . $this->seedPatientFname() . ' ' . $this->seedPatientLname() . '")]',
            60,
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

}
