<?php

/**
 * Example test showing how to use video annotations
 *
 * This example demonstrates how to add visual annotations to E2E test videos
 * to make them easier to navigate and understand.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEditGlobals;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;
use Facebook\WebDriver\WebDriverBy;

/**
 * Example test with video annotations.
 *
 * Copy the annotateVideo() calls to your own tests to make videos easier to navigate.
 */
class ExampleAnnotatedTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private Client $client;
    private $crawler;

    /**
     * Example test showing how to annotate key steps in the video.
     */
    #[Test]
    public function testAnnotatedEditGlobals(): void
    {
        $this->base();
        try {
            // Annotate: Starting test
            $this->annotateVideo('TEST START: Edit Globals', 2000, '#4CAF50');

            $this->login(LoginTestData::username, LoginTestData::password);

            // Annotate: Navigating to page
            $this->annotateVideo('STEP 1: Navigate to Administration > Globals', 2000);

            $this->goToMainMenuLink('Administration||Globals');
            $this->assertActiveTab("Configuration");

            // Annotate: Page loaded
            $this->annotateVideo('STEP 2: Configuration page loaded', 1500, '#2196F3');

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_IFRAME);
            $this->switchToIFrame(XpathsConstantsEditGlobals::GLOBALS_IFRAME);

            $this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);
            $this->crawler = $this->client->refreshCrawler();

            // Annotate: Testing search
            $this->annotateVideo('STEP 3: Testing search functionality', 2000, '#9C27B0');

            $searchElement = $this->client->findElement(WebDriverBy::xpath(XpathsConstantsEditGlobals::SEARCH_INPUT));
            $searchElement->clear();
            $searchElement->sendKeys('Language');
            $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_BUTTON)->click();

            sleep(2);

            // Annotate: Verifying results
            $this->annotateVideo('STEP 4: Verifying search results', 1500, '#FF9800');

            $highlights = $this->crawler->filterXPath(XpathsConstantsEditGlobals::SEARCH_HIGHLIGHT);
            $this->assertGreaterThan(0, count($highlights), 'Search did not highlight any results');

            // Annotate: Test complete
            $this->annotateVideo('TEST COMPLETE: All checks passed', 2000, '#4CAF50');
        } catch (\Throwable $e) {
            // Annotate failures too
            $this->annotateVideo('TEST FAILED: ' . $e->getMessage(), 3000, '#F44336');
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
    }
}
