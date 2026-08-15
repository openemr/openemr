<?php

/**
 * KkEditGlobalsTest class
 *
 * End-to-end tests for the edit_globals.php configuration page.
 * These tests validate the UI interactions and database persistence
 * of the global configuration settings form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEditGlobals;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

/**
 * E2E test suite for the global configuration editor.
 *
 * Tests cover:
 * - Page loading and form elements
 * - Tab navigation between configuration sections
 * - Search functionality for finding settings
 * - Saving different field types (text, checkbox, select)
 * - Transaction handling for multiple setting changes
 */
class KkEditGlobalsTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    /**
     * Labels of the settings these tests drive.
     *
     * edit_globals.php names every control positionally (form_0, form_1, ...),
     * so the index shifts whenever a setting is added or removed. The visible
     * label is the only stable handle on a particular setting. All three live
     * on the tab that is active on load, so no tab switching is needed --
     * controls on an inactive tab are present but not interactable.
     */
    private const LABEL_NUM_MESSAGES = 'Number of Messages Displayed in Patient Summary';
    private const LABEL_WINDOW_TITLE = 'Add Patient Name To Window Title';
    private const LABEL_ENCOUNTER_PAGE_SIZE = 'Encounter Page Size';

    private Crawler $crawler;

    /**
     * Test that the edit globals configuration page loads successfully.
     *
     * Verifies that:
     * - The Configuration tab becomes active
     * - The globals form iframe loads
     * - The main form element exists
     * - Save button and search field are present
     *
     * @return void
     */
    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testEditGlobalsPageLoads(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Navigate to Administration > Globals
            $this->goToMainMenuLink('Admin||Config');

            // Wait for the page to load
            $this->assertActiveTab("Configuration");

            // Switch to the iframe containing the globals form
            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            // Verify the form exists
            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();
            $form = $this->crawler->filterXPath(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->assertCount(1, $form, 'Edit globals form not found');

            // Verify Save button exists
            $saveButton = $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON);
            $this->assertGreaterThan(0, count($saveButton), 'Save button not found');

            // Verify Search field exists
            $searchField = $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_INPUT);
            $this->assertCount(1, $searchField, 'Search field not found');
        } finally {
            $this->client->quit();
        }
    }

    /**
     * Test navigation between different configuration tabs.
     *
     * Verifies that clicking on each tab:
     * - Makes the tab visible and accessible
     * - Activates the tab (adds 'current' class)
     * - Shows the appropriate configuration section
     *
     * Tests tabs: Locale, Appearance, Security, Connectors
     *
     * @return void
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testTabNavigation(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->goToMainMenuLink('Admin||Config');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();

            // Test clicking on different tabs
            $tabs = [
                'Locale' => XpathsConstantsEditGlobals::TAB_LOCALE,
                'Appearance' => XpathsConstantsEditGlobals::TAB_APPEARANCE,
                'Security' => XpathsConstantsEditGlobals::TAB_SECURITY,
                'Connectors' => XpathsConstantsEditGlobals::TAB_CONNECTORS,
            ];

            foreach ($tabs as $tabName => $tabXpath) {
                $this->crawler = $this->client->refreshCrawler();
                $tab = $this->crawler->filterXPath($tabXpath);
                $this->assertGreaterThan(0, count($tab), "Tab '$tabName' not found");

                // Click the tab
                $tab->click();
                sleep(1); // Wait for tab content to load

                // Verify the tab is now active (has 'current' class)
                $this->crawler = $this->client->refreshCrawler();
                $activeTab = $this->crawler->filterXPath($tabXpath . '/parent::li[contains(@class, "current")]');
                $this->assertGreaterThan(0, count($activeTab), "Tab '$tabName' did not become active after clicking");
            }
        } finally {
            $this->client->quit();
        }
    }

    /**
     * Test the search functionality for finding configuration settings.
     *
     * Verifies that:
     * - Search input accepts text
     * - Search button triggers search
     * - Matching settings are highlighted with <mark> tags
     * - Matching rows receive 'srch' class
     *
     * Uses "Language" as the search term for testing.
     *
     * @return void
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testSearchFunctionality(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->goToMainMenuLink('Admin||Config');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            // Wait for the search field
            $this->client->waitFor(XpathsConstantsEditGlobals::SEARCH_INPUT);
            $this->crawler = $this->client->refreshCrawler();

            // Enter search term
            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Language');

            // Click search button
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();

            // Wait for search results to load
            sleep(2);

            // Verify search highlights appear
            $this->crawler = $this->client->refreshCrawler();
            $highlights = $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_HIGHLIGHT);
            $this->assertGreaterThan(0, count($highlights), 'Search did not highlight any results');

            // Verify search results rows are marked
            $searchResults = $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_RESULT_ROW);
            $this->assertGreaterThan(0, count($searchResults), 'No search result rows found');
        } finally {
            $this->client->quit();
        }
    }

    /**
     * Test saving a text-type global configuration setting.
     *
     * Validates the complete flow:
     * - Changes 'num_of_messages_displayed' to a new value
     * - Clicks Save button
     * - Verifies value is persisted to database
     * - Restores original value
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testCanSaveTextGlobalSetting(): void
    {
        $testSetting = 'num_of_messages_displayed';
        $originalValue = $this->getGlobalValue($testSetting);
        // Pick a value that differs from whatever is stored, so the save is
        // observable regardless of the starting state.
        $testValue = $originalValue === '7' ? '8' : '7';

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->goToMainMenuLink('Admin||Config');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);

            $fieldXpath = self::controlXpathForLabel(self::LABEL_NUM_MESSAGES, '//input[@type="text"]');
            $element = $this->client->findElement(WebDriverBy::xpath($fieldXpath));
            $element->clear();
            $element->sendKeys($testValue);

            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            $this->waitForGlobalValue($testSetting, $testValue);
            $this->assertSame($testValue, $this->getGlobalValue($testSetting), 'Global setting was not saved correctly');
        } finally {
            // Restore in finally: a failed save, poll, or assertion must not
            // leave the globals table mutated for the tests that follow.
            $this->setGlobalValue($testSetting, $originalValue);
            $this->client->quit();
        }
    }

    /**
     * Test saving a checkbox-type global configuration setting.
     *
     * Validates the complete flow:
     * - Toggles 'window_title_add_patient_name'
     * - Clicks Save button
     * - Verifies the toggled value is persisted to database
     * - Restores original value
     *
     * An unchecked box is not submitted at all, so edit_globals.php stores an
     * empty string rather than '0' -- the expected value differs by direction.
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testCanSaveCheckboxGlobalSetting(): void
    {
        $testSetting = 'window_title_add_patient_name';
        $originalValue = $this->getGlobalValue($testSetting);
        $checkIt = $originalValue !== '1';
        $expectedValue = $checkIt ? '1' : '';

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->goToMainMenuLink('Admin||Config');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);

            $checkboxXpath = self::controlXpathForLabel(self::LABEL_WINDOW_TITLE, '//input[@type="checkbox"]');
            $checkbox = $this->client->findElement(WebDriverBy::xpath($checkboxXpath));

            if ($checkbox->isSelected() !== $checkIt) {
                $checkbox->click();
            }

            $this->assertSame($checkIt, $checkbox->isSelected(), 'Checkbox did not reach the intended state');

            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            $this->waitForGlobalValue($testSetting, $expectedValue);
            $this->assertSame(
                $expectedValue,
                $this->getGlobalValue($testSetting),
                'Checkbox global setting was not saved correctly'
            );
        } finally {
            $this->setGlobalValue($testSetting, $originalValue);
            $this->client->quit();
        }
    }

    /**
     * Test saving a select dropdown-type global configuration setting.
     *
     * Validates the complete flow:
     * - Selects a different option for 'encounter_page_size'
     * - Clicks Save button
     * - Verifies the selected value is persisted to database
     * - Restores original value
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testCanSaveSelectGlobalSetting(): void
    {
        $testSetting = 'encounter_page_size';
        $originalValue = $this->getGlobalValue($testSetting);

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->goToMainMenuLink('Admin||Config');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);

            $selectXpath = self::controlXpathForLabel(self::LABEL_ENCOUNTER_PAGE_SIZE, '//select');
            $select = new WebDriverSelect($this->client->findElement(WebDriverBy::xpath($selectXpath)));

            $currentValue = $select->getFirstSelectedOption()->getAttribute('value');
            $candidates = [];
            foreach ($select->getOptions() as $option) {
                $value = $option->getAttribute('value');
                if ($value !== null && $value !== $currentValue) {
                    $candidates[] = $value;
                }
            }

            $this->assertNotSame([], $candidates, 'No alternative option available to select');

            $testValue = $candidates[0];
            $select->selectByValue($testValue);

            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            $this->waitForGlobalValue($testSetting, $testValue);
            $this->assertSame(
                $testValue,
                $this->getGlobalValue($testSetting),
                'Select global setting was not saved correctly'
            );
        } finally {
            $this->setGlobalValue($testSetting, $originalValue);
            $this->client->quit();
        }
    }

    /**
     * Test saving multiple global settings in a single transaction.
     *
     * Both fields live on the same (default) tab, so they can be edited before
     * a single Save. Searching between edits would not work: the search posts
     * the form back and re-renders the page, discarding any pending edit.
     *
     * This exercises the transaction logic in edit_globals.php -- START
     * TRANSACTION, several UPDATEs, COMMIT -- so either all changes land or
     * none do.
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testMultipleGlobalsCanBeSavedInOneTransaction(): void
    {
        $textSetting = 'num_of_messages_displayed';
        $boolSetting = 'window_title_add_patient_name';

        $originalText = $this->getGlobalValue($textSetting);
        $originalBool = $this->getGlobalValue($boolSetting);

        $expectedText = $originalText === '9' ? '6' : '9';
        $checkIt = $originalBool !== '1';
        $expectedBool = $checkIt ? '1' : '';

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->goToMainMenuLink('Admin||Config');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);

            $textField = $this->client->findElement(
                WebDriverBy::xpath(self::controlXpathForLabel(self::LABEL_NUM_MESSAGES, '//input[@type="text"]'))
            );
            $textField->clear();
            $textField->sendKeys($expectedText);

            $checkbox = $this->client->findElement(
                WebDriverBy::xpath(self::controlXpathForLabel(self::LABEL_WINDOW_TITLE, '//input[@type="checkbox"]'))
            );
            if ($checkbox->isSelected() !== $checkIt) {
                $checkbox->click();
            }

            // One Save for both edits.
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            $this->waitForGlobalValue($textSetting, $expectedText);
            $this->waitForGlobalValue($boolSetting, $expectedBool);

            $this->assertSame(
                $expectedText,
                $this->getGlobalValue($textSetting),
                "Setting '$textSetting' was not saved correctly in transaction"
            );
            $this->assertSame(
                $expectedBool,
                $this->getGlobalValue($boolSetting),
                "Setting '$boolSetting' was not saved correctly in transaction"
            );
        } finally {
            $this->setGlobalValue($textSetting, $originalText);
            $this->setGlobalValue($boolSetting, $originalBool);
            $this->client->quit();
        }
    }

    // Helper methods

    /**
     * Build an XPath for a form control in the settings row carrying $label.
     *
     * @param string $label   The visible setting label
     * @param string $control Relative XPath of the control, e.g. '//select'
     */
    private static function controlXpathForLabel(string $label, string $control): string
    {
        return '//div[contains(@class, "form-group")][div[normalize-space() = "' . $label . '"]]' . $control;
    }

    /**
     * Poll until a global holds the expected value, or the timeout expires.
     *
     * The Save button posts the form and the page re-renders; polling the
     * database beats a fixed sleep, which is either slower than it needs to be
     * or too short on a loaded CI runner.
     */
    private function waitForGlobalValue(string $name, string $expected, int $timeout = 15): void
    {
        $deadline = time() + $timeout;
        while (time() < $deadline) {
            if ($this->getGlobalValue($name) === $expected) {
                return;
            }
            usleep(250_000);
        }
    }

    /**
     * Retrieve a global configuration value from the database.
     *
     * @param string $name The global setting name (gl_name)
     * @return string The global setting value, or empty string if not found
     */
    private function getGlobalValue(string $name): string
    {
        $value = QueryUtils::fetchSingleValue(
            'SELECT gl_value FROM globals WHERE gl_name = ?',
            'gl_value',
            [$name]
        );

        return is_string($value) ? $value : '';
    }

    /**
     * Set a global configuration value in the database.
     *
     * Used for test cleanup to restore original values after test execution.
     *
     * @param string $name The global setting name (gl_name)
     * @param string $value The value to set
     */
    private function setGlobalValue(string $name, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE globals SET gl_value = ? WHERE gl_name = ?',
            [$value, $name]
        );
    }
}
