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

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEditGlobals;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

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
     * @var Client The Panther browser client instance
     */
    private Client $client;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler The DOM crawler for element selection
     */
    private $crawler;

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
            $this->goToMainMenuLink('Administration||Globals');

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
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
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

            $this->goToMainMenuLink('Administration||Globals');
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
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
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

            $this->goToMainMenuLink('Administration||Globals');
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
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
    }

    /**
     * Test saving a text-type global configuration setting.
     *
     * Validates the complete flow:
     * - Searches for the 'site_id' setting
     * - Changes the value to a unique test value
     * - Clicks Save button
     * - Verifies value is persisted to database
     * - Restores original value
     *
     * This tests the text field handling in edit_globals.php lines 256-257.
     *
     * @return void
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testCanSaveTextGlobalSetting(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Use 'site_id' as a test setting (it's a text field in the Locale tab)
            $testSetting = 'site_id';
            $originalValue = $this->getGlobalValue($testSetting);
            $testValue = 'test-' . time(); // Unique value to ensure change detection

            $this->goToMainMenuLink('Administration||Globals');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();

            // Make sure we're on the Locale tab (default tab)
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::TAB_LOCALE)->click();
            sleep(1);

            // Find the site_id field using search to make it easier
            $this->crawler = $this->client->refreshCrawler();
            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Site ID');
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();
            sleep(1);

            // Find the input field for site_id
            // The field should be in a highlighted row after search
            $this->crawler = $this->client->refreshCrawler();
            $fieldXpath = '//div[contains(@class, "srch")]//input[@type="text"]';
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath($fieldXpath)
                )
            );

            // Fill in the field
            $element = $this->client->findElement(WebDriverBy::xpath($fieldXpath));
            $element->clear();
            $element->sendKeys($testValue);

            // Click the Save button
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            // Wait for page reload
            sleep(3);

            // Verify the value was saved in the database
            $savedValue = $this->getGlobalValue($testSetting);
            $this->assertSame($testValue, $savedValue, 'Global setting was not saved correctly');

            // Restore original value
            $this->setGlobalValue($testSetting, $originalValue);
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
    }

    /**
     * Test saving a checkbox-type global configuration setting.
     *
     * Validates the complete flow:
     * - Searches for the 'disable_utf8_flag' setting
     * - Toggles the checkbox value
     * - Clicks Save button
     * - Verifies the toggled value is persisted to database
     * - Restores original value
     *
     * This tests the checkbox handling in edit_globals.php lines 527-531.
     *
     * @return void
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testCanSaveCheckboxGlobalSetting(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Use 'disable_utf8_flag' as a test setting (it's a checkbox)
            $testSetting = 'disable_utf8_flag';
            $originalValue = $this->getGlobalValue($testSetting);

            // Toggle the value
            $testValue = ($originalValue === '1') ? '0' : '1';

            $this->goToMainMenuLink('Administration||Globals');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();

            // Search for the setting
            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Disable UTF8');
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();
            sleep(1);

            // Find the checkbox
            $this->crawler = $this->client->refreshCrawler();
            $checkboxXpath = '//div[contains(@class, "srch")]//input[@type="checkbox"]';
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath($checkboxXpath)
                )
            );

            $checkbox = $this->client->findElement(WebDriverBy::xpath($checkboxXpath));

            // Toggle the checkbox to the test value
            $isChecked = $checkbox->isSelected();
            if (($testValue === '1' && !$isChecked) || ($testValue === '0' && $isChecked)) {
                $checkbox->click();
            }

            // Click the Save button
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            // Wait for page reload
            sleep(3);

            // Verify the value was saved
            $savedValue = $this->getGlobalValue($testSetting);
            $this->assertSame($testValue, $savedValue, 'Checkbox global setting was not saved correctly');

            // Restore original value
            $this->setGlobalValue($testSetting, $originalValue);
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
    }

    /**
     * Test saving a select dropdown-type global configuration setting.
     *
     * Validates the complete flow:
     * - Searches for the 'language_default' setting
     * - Selects a different option from the dropdown
     * - Clicks Save button
     * - Verifies the selected value is persisted to database
     * - Restores original value
     *
     * This tests the select dropdown handling in edit_globals.php lines 502-522.
     *
     * @return void
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testCanSaveSelectGlobalSetting(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Use 'language_default' as a test setting (it's a select dropdown)
            $testSetting = 'language_default';
            $originalValue = $this->getGlobalValue($testSetting);

            $this->goToMainMenuLink('Administration||Globals');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();

            // Search for the setting
            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Default Language');
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();
            sleep(1);

            // Find the select dropdown
            $this->crawler = $this->client->refreshCrawler();
            $selectXpath = '//div[contains(@class, "srch")]//select';
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath($selectXpath)
                )
            );

            $select = $this->client->findElement(WebDriverBy::xpath($selectXpath));

            // Get the current selected value and select a different one
            $options = $select->findElements(WebDriverBy::tagName('option'));
            $this->assertGreaterThan(0, count($options), 'No options found in language dropdown');

            // Select the first available option (if different from current)
            foreach ($options as $option) {
                if (!$option->isSelected()) {
                    $testValue = $option->getAttribute('value');
                    $option->click();
                    break;
                }
            }

            // Click the Save button
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();

            // Wait for page reload
            sleep(3);

            // Verify the value was saved
            $savedValue = $this->getGlobalValue($testSetting);
            $this->assertSame($testValue, $savedValue, 'Select global setting was not saved correctly');

            // Restore original value
            $this->setGlobalValue($testSetting, $originalValue);
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
    }

    /**
     * Test saving multiple global settings in a single transaction.
     *
     * Validates the transaction handling:
     * - Modifies both 'site_id' and 'phone_country_code' settings
     * - Clicks Save button once
     * - Verifies both values are persisted to database atomically
     * - Restores original values
     *
     * This tests the critical transaction logic in edit_globals.php:
     * - START TRANSACTION (line 236)
     * - Multiple UPDATE operations
     * - COMMIT (line 299)
     * - Ensures atomicity: either all changes save or none do
     *
     * @return void
     */
    #[Depends('testEditGlobalsPageLoads')]
    #[Test]
    public function testMultipleGlobalsCanBeSavedInOneTransaction(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Test multiple settings at once
            $settings = [
                'site_id' => [
                    'original' => $this->getGlobalValue('site_id'),
                    'test' => 'test-multi-' . time()
                ],
                'phone_country_code' => [
                    'original' => $this->getGlobalValue('phone_country_code'),
                    'test' => '1'
                ]
            ];

            $this->goToMainMenuLink('Administration||Globals');
            $this->assertActiveTab("Configuration");

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();

            // Ensure we're on the Locale tab
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::TAB_LOCALE)->click();
            sleep(1);

            // Search for and modify site_id
            $this->crawler = $this->client->refreshCrawler();
            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Site ID');
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();
            sleep(1);

            $this->crawler = $this->client->refreshCrawler();
            $fieldXpath = '//div[contains(@class, "srch")]//input[@type="text"]';
            $element = $this->client->findElement(WebDriverBy::xpath($fieldXpath));
            $element->clear();
            $element->sendKeys($settings['site_id']['test']);

            // Clear search and search for phone_country_code
            $this->crawler = $this->client->refreshCrawler();
            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Phone Country Code');
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();
            sleep(1);

            $this->crawler = $this->client->refreshCrawler();
            $fieldXpath2 = '//div[contains(@class, "srch")]//input[@type="text"]';
            $element2 = $this->client->findElement(WebDriverBy::xpath($fieldXpath2));
            $element2->clear();
            $element2->sendKeys($settings['phone_country_code']['test']);

            // Save both changes at once
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SAVE_BUTTON)->click();
            sleep(3);

            // Verify both values were saved
            foreach ($settings as $settingName => $values) {
                $savedValue = $this->getGlobalValue($settingName);
                $this->assertSame($values['test'], $savedValue, "Setting '$settingName' was not saved correctly in transaction");
            }

            // Restore original values
            foreach ($settings as $settingName => $values) {
                $this->setGlobalValue($settingName, $values['original']);
            }
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
    }

    // Helper methods

    /**
     * Retrieve a global configuration value from the database.
     *
     * @param string $name The global setting name (gl_name)
     * @return string The global setting value, or empty string if not found
     */
    private function getGlobalValue(string $name): string
    {
        $result = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = ?", [$name]);
        return $result['gl_value'] ?? '';
    }

    /**
     * Set a global configuration value in the database.
     *
     * Used for test cleanup to restore original values after test execution.
     *
     * @param string $name The global setting name (gl_name)
     * @param string $value The value to set
     * @return void
     */
    private function setGlobalValue(string $name, string $value): void
    {
        sqlStatement(
            "UPDATE globals SET gl_value = ? WHERE gl_name = ?",
            [$value, $name]
        );
    }

    /**
     * Retrieve a background service configuration from the database.
     *
     * @param string $name The background service name
     * @return array<string, mixed> The service configuration row, or empty array if not found
     */
    private function getBackgroundService(string $name): array
    {
        return sqlQuery(
            "SELECT * FROM background_services WHERE name = ?",
            [$name]
        ) ?? [];
    }
}
