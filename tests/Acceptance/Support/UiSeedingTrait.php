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

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

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
     * Add a fresh patient (Ftest<suffix> Ltest<suffix>) via the
     * "Patient/Client → New/Search" main-menu path. Identity is
     * per-test-instance via seedPatientFname/seedPatientLname so
     * multiple tests seeding in the same phase and post-upgrade
     * runs against an already-seeded DB never collide on a
     * pre-existing patient. Mirrors the source-side
     * PatientAddTrait flow shape: open New/Search tab → fill the DEM
     * form in the patient iframe → click Create → confirm in the modal
     * iframe → accept the resulting duplicate-check alert → wait for
     * the Medical Record Dashboard header to appear.
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
        // source-side PatientAddTrait uses. Multi-click retry does not
        // recover (see #13348: once a click lands during the wire race
        // the button ends up in a state subsequent clicks can't rescue,
        // so retrying makes the flake worse not better) — only widening
        // the *post-click* alert wait helps, since the handler may fire
        // the alert well after the 15s prior ceiling on a slow runner.
        sleep(5);
        $client->findElement(WebDriverBy::xpath("//*[@id='confirmCreate']"))->click();

        // A duplicate-check JS alert fires after confirm — accept it
        // and continue. Bumped 15s → 60s to absorb slow-runner cases
        // where the wire slipped past the 5s stabilization above.
        $client->wait(60)->until(WebDriverExpectedCondition::alertIsPresent());
        $client->switchTo()->alert()->accept();

        // Switch back to defaults, into the patient iframe, wait for
        // the Medical Record Dashboard header — proof the create
        // succeeded and the browser landed on the summary.
        $client->switchTo()->defaultContent();
        $client->waitFor('//*[@id="framesDisplay"]//iframe[@name="pat"]', 30);
        $this->switchToIFrame('//*[@id="framesDisplay"]//iframe[@name="pat"]');
        $client->waitFor(
            '//*[text()="Medical Record Dashboard - ' . $this->seedPatientFname() . ' ' . $this->seedPatientLname() . '"]',
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

}
