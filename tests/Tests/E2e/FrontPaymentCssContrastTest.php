<?php

/**
 * E2e test for front payment receipt CSS contrast.
 *
 * Verifies that the receipt page CSS rules for .bg-color, .bg-color-w,
 * and table header elements include explicit text color properties to
 * ensure readability across all themes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @see       https://github.com/openemr/openemr/issues/10842
 * @author    Josh Baiad <josh@jbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@jbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class FrontPaymentCssContrastTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private ?Crawler $crawler = null;

    /**
     * Verify that the front payment receipt CSS includes explicit text
     * color on elements that set a background-color, ensuring readable
     * contrast across all OpenEMR themes.
     *
     * The light and solar themes map --secondary to a dark color, which
     * causes text to be invisible when no explicit color property is set.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReceiptCssHasExplicitTextColor(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Navigate to front_payment.php receipt view. The receipt=1
            // parameter triggers the receipt rendering path which includes
            // the CSS rules we need to test. We use pre_payment type with
            // patient=1 so the page renders without requiring a real
            // encounter.
            $this->crawler = $this->client->request(
                'GET',
                '/interface/patient_file/front_payment.php?receipt=1&patient=1&time=2026-01-01+12:00:00&radio_type_of_payment=pre_payment'
            );

            // Use JavaScript to inspect the CSS rules in the page's
            // stylesheets and check whether .bg-color, .bg-color-w, and
            // the mini_table th rules have an explicit 'color' property.
            /** @var string $result */
            $result = $this->client->executeScript(<<<'JS_WRAP'
                var results = {bgColor: false, bgColorW: false, miniTableTh: false};
                var sheets = document.styleSheets;
                for (var i = 0; i < sheets.length; i++) {
                    try {
                        var rules = sheets[i].cssRules || sheets[i].rules;
                        if (!rules) continue;
                        for (var j = 0; j < rules.length; j++) {
                            var rule = rules[j];
                            if (!rule.selectorText) continue;
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
                        // Cross-origin stylesheet, skip
                    }
                }
                return JSON.stringify(results);
            JS_WRAP);

            /** @var array{bgColor: bool, bgColorW: bool, miniTableTh: bool} $cssResults */
            $cssResults = json_decode($result, true);

            $this->assertTrue(
                $cssResults['bgColor'],
                '.bg-color CSS rule must include an explicit color property for text contrast'
            );
            $this->assertTrue(
                $cssResults['bgColorW'],
                '.bg-color-w CSS rule must include an explicit color property for text contrast'
            );
            $this->assertTrue(
                $cssResults['miniTableTh'],
                'table.mini_table th CSS rule must include an explicit color property for text contrast'
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }
}
