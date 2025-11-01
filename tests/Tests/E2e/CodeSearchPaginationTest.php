<?php

/**
 * CodeSearchPaginationTest class
 *
 * Tests for code search pagination bug where Previous button goes forward instead of backward
 * See: https://community.open-emr.org/t/cannot-reverse-code-search/26302
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2025 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class CodeSearchPaginationTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $client;
    private $crawler;

    /**
     * Test that the Next pagination button works correctly
     * This test should PASS (Next button is working)
     */
    public function testCodeSearchPaginationNextButton(): void
    {
        $this->base();
        try {
            $this->login('admin', 'pass');

            // Navigate to code search page
            $this->crawler = $this->client->request(
                'GET',
                '/interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10'
            );

            // Wait for DataTables to initialize
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('my_data_table')
                )
            );

            // Enter a broad search term to ensure we have multiple pages
            $searchInput = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_filter input')
            );
            $searchInput->clear();
            $searchInput->sendKeys('disease');

            // Click search button (if it exists) or trigger search
            $this->client->wait(2);
            $this->client->executeScript(
                "oTable.api().search('disease').draw();"
            );

            // Wait for results to load
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.dataTables_info')
                )
            );

            // Get the initial page info
            $initialInfo = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_info')
            )->getText();

            // Extract start number from "Showing 1 to 50 of 500" format
            preg_match('/Showing (\d+) to (\d+)/', $initialInfo, $matches);
            $initialStart = (int)($matches[1] ?? 0);
            $initialEnd = (int)($matches[2] ?? 0);

            $this->assertGreaterThan(0, $initialStart, 'Should have results on first page');

            // Click Next button
            $nextButton = $this->client->findElement(
                WebDriverBy::linkText('Next')
            );
            $nextButton->click();

            // Wait for page to change
            $this->client->wait(5);

            // Get new page info
            $newInfo = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_info')
            )->getText();

            preg_match('/Showing (\d+) to (\d+)/', $newInfo, $newMatches);
            $newStart = (int)($newMatches[1] ?? 0);
            $newEnd = (int)($newMatches[2] ?? 0);

            // Assert that Next button moved forward
            $this->assertGreaterThan(
                $initialStart,
                $newStart,
                'Next button should advance to higher page numbers'
            );

            $this->assertGreaterThan(
                $initialEnd,
                $newEnd,
                'Next button should show later records'
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Test that the Previous pagination button works correctly
     * This test should FAIL (demonstrating the bug)
     *
     * BUG: Previous button advances forward instead of going backward
     */
    public function testCodeSearchPaginationPreviousButton(): void
    {
        $this->base();
        try {
            $this->login('admin', 'pass');

            // Navigate to code search page
            $this->crawler = $this->client->request(
                'GET',
                '/interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10'
            );

            // Wait for DataTables to initialize
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('my_data_table')
                )
            );

            // Enter a broad search term
            $searchInput = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_filter input')
            );
            $searchInput->clear();
            $searchInput->sendKeys('disease');

            // Trigger search
            $this->client->wait(2);
            $this->client->executeScript(
                "oTable.api().search('disease').draw();"
            );

            // Wait for results
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.dataTables_info')
                )
            );

            // Click Next to go to page 2
            $nextButton = $this->client->findElement(
                WebDriverBy::linkText('Next')
            );
            $nextButton->click();
            $this->client->wait(3);

            // Get page 2 info
            $page2Info = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_info')
            )->getText();

            preg_match('/Showing (\d+) to (\d+)/', $page2Info, $page2Matches);
            $page2Start = (int)($page2Matches[1] ?? 0);
            $page2End = (int)($page2Matches[2] ?? 0);

            // Now click Previous - should go back to page 1
            $previousButton = $this->client->findElement(
                WebDriverBy::linkText('Previous')
            );
            $previousButton->click();
            $this->client->wait(3);

            // Get the new page info after clicking Previous
            $afterPreviousInfo = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_info')
            )->getText();

            preg_match('/Showing (\d+) to (\d+)/', $afterPreviousInfo, $afterMatches);
            $afterStart = (int)($afterMatches[1] ?? 0);
            $afterEnd = (int)($afterMatches[2] ?? 0);

            // THIS ASSERTION WILL FAIL due to the bug
            // Previous button should go to LOWER page numbers (backward)
            $this->assertLessThan(
                $page2Start,
                $afterStart,
                'BUG: Previous button should go backward to lower page numbers, but it goes forward! ' .
                "Expected start < {$page2Start}, got {$afterStart}"
            );

            $this->assertLessThan(
                $page2End,
                $afterEnd,
                'BUG: Previous button should show earlier records, but it shows later ones! ' .
                "Expected end < {$page2End}, got {$afterEnd}"
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Test the complete pagination flow: First -> Next -> Next -> Previous -> Previous -> Last
     * This comprehensively tests all pagination buttons
     */
    public function testCodeSearchPaginationCompleteFlow(): void
    {
        $this->base();
        try {
            $this->login('admin', 'pass');

            // Navigate to code search page
            $this->crawler = $this->client->request(
                'GET',
                '/interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10'
            );

            // Wait for DataTables
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('my_data_table')
                )
            );

            // Search
            $searchInput = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_filter input')
            );
            $searchInput->clear();
            $searchInput->sendKeys('disease');
            $this->client->wait(2);
            $this->client->executeScript("oTable.api().search('disease').draw();");
            $this->client->wait(5);

            // Helper function to get current page start
            $getPageStart = function () {
                $info = $this->client->findElement(
                    WebDriverBy::cssSelector('.dataTables_info')
                )->getText();
                preg_match('/Showing (\d+) to/', $info, $matches);
                return (int)($matches[1] ?? 0);
            };

            $pageHistory = [];

            // Record page 1
            $pageHistory[] = ['button' => 'Initial', 'start' => $getPageStart()];

            // Click Next (should go forward)
            $this->client->findElement(WebDriverBy::linkText('Next'))->click();
            $this->client->wait(3);
            $pageHistory[] = ['button' => 'Next', 'start' => $getPageStart()];

            // Click Next again (should go forward again)
            $this->client->findElement(WebDriverBy::linkText('Next'))->click();
            $this->client->wait(3);
            $pageHistory[] = ['button' => 'Next', 'start' => $getPageStart()];

            // Click Previous (SHOULD go backward, but BUG causes it to go forward)
            $this->client->findElement(WebDriverBy::linkText('Previous'))->click();
            $this->client->wait(3);
            $pageHistory[] = ['button' => 'Previous', 'start' => $getPageStart()];

            // Verify the flow
            // Initial -> Next should increase
            $this->assertLessThan(
                $pageHistory[1]['start'],
                $pageHistory[0]['start'],
                'First Next should move forward'
            );

            // Next -> Next should increase
            $this->assertLessThan(
                $pageHistory[2]['start'],
                $pageHistory[1]['start'],
                'Second Next should move forward'
            );

            // After Next -> Previous should DECREASE (this will fail due to bug)
            $this->assertGreaterThan(
                $pageHistory[3]['start'],
                $pageHistory[2]['start'],
                'Previous after Next should move backward, but BUG causes it to move forward! ' .
                'Page history: ' . json_encode($pageHistory)
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Test to verify what action the Previous button is actually calling
     * This helps diagnose whether the bug is in event binding or rendering
     */
    public function testCodeSearchPaginationPreviousButtonAction(): void
    {
        $this->base();
        try {
            $this->login('admin', 'pass');

            // Navigate to code search page
            $this->crawler = $this->client->request(
                'GET',
                '/interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10'
            );

            // Wait for DataTables
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('my_data_table')
                )
            );

            // Search
            $searchInput = $this->client->findElement(
                WebDriverBy::cssSelector('.dataTables_filter input')
            );
            $searchInput->sendKeys('disease');
            $this->client->wait(2);
            $this->client->executeScript("oTable.api().search('disease').draw();");
            $this->client->wait(5);

            // Go to page 2
            $this->client->findElement(WebDriverBy::linkText('Next'))->click();
            $this->client->wait(3);

            // Inject monitoring code to capture what action Previous button calls
            $actionCalled = $this->client->executeScript("
                return new Promise((resolve) => {
                    var originalPage = $.fn.dataTable.Api.prototype.page;
                    $.fn.dataTable.Api.prototype.page = function(action) {
                        if (typeof action === 'string') {
                            // Restore original immediately
                            $.fn.dataTable.Api.prototype.page = originalPage;
                            // Resolve with the action
                            resolve(action);
                            // Still call the original
                            return originalPage.apply(this, arguments);
                        }
                        return originalPage.apply(this, arguments);
                    };

                    // Click the Previous button
                    $('.dataTables_paginate a:contains(\"Previous\")').click();
                });
            ");

            // Verify the action called
            $this->assertSame(
                'previous',
                $actionCalled,
                "Previous button should call page('previous'), but called page('{$actionCalled}') instead"
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }
}
