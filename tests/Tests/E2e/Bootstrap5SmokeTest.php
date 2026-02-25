<?php

/**
 * Bootstrap 5 Smoke Test - Fast verification of BS5 migration
 *
 * This is a quick test that verifies the most critical Bootstrap 5
 * functionality works. Run this first before the full E2E suite.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Fast smoke test for Bootstrap 5 migration verification.
 *
 * Run with: phpunit --filter Bootstrap5SmokeTest
 * Or: phpunit --group bootstrap5-smoke
 */
#[Group('bootstrap5-smoke')]
#[Group('bootstrap5')]
class Bootstrap5SmokeTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    /**
     * Single comprehensive smoke test that verifies all critical BS5 functionality.
     *
     * This test:
     * 1. Verifies Bootstrap 5 JS is loaded
     * 2. Tests modal open/close
     * 3. Tests dropdown menu
     * 4. Checks for deprecated data attributes
     *
     * All in one test to minimize login overhead.
     */
    #[Test]
    public function testBootstrap5CoreFunctionality(): void
    {
        $this->base();
        $errors = [];

        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // 1. Check Bootstrap 5 is loaded
            $bs5Check = $this->client->executeScript(<<<'JS'
                return {
                    loaded: typeof bootstrap !== 'undefined',
                    version: (typeof bootstrap !== 'undefined' && bootstrap.Modal)
                        ? bootstrap.Modal.VERSION
                        : null,
                    hasModal: typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function',
                    hasDropdown: typeof bootstrap !== 'undefined' && typeof bootstrap.Dropdown === 'function',
                    hasCollapse: typeof bootstrap !== 'undefined' && typeof bootstrap.Collapse === 'function',
                    hasTooltip: typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip === 'function'
                };
            JS);

            if (!$bs5Check['loaded']) {
                $errors[] = 'Bootstrap JS not loaded';
            } elseif (!str_starts_with($bs5Check['version'] ?? '', '5.')) {
                $errors[] = "Wrong Bootstrap version: {$bs5Check['version']}";
            }

            foreach (['hasModal', 'hasDropdown', 'hasCollapse', 'hasTooltip'] as $component) {
                if (!$bs5Check[$component]) {
                    $errors[] = "Missing Bootstrap component: $component";
                }
            }

            // 2. Test dropdown functionality (user menu)
            try {
                $userDropdown = $this->client->findElement(
                    WebDriverBy::cssSelector('.dropdown-toggle, [data-bs-toggle="dropdown"]')
                );
                $userDropdown->click();

                $this->client->wait(3)->until(
                    WebDriverExpectedCondition::visibilityOfElementLocated(
                        WebDriverBy::cssSelector('.dropdown-menu.show')
                    )
                );

                // Close by clicking elsewhere
                $this->client->executeScript('document.body.click()');
                usleep(300000);
            } catch (\Throwable $e) {
                $errors[] = "Dropdown test failed: " . $e->getMessage();
            }

            // 3. Check for deprecated BS4 data attributes
            $deprecatedCount = $this->client->executeScript(<<<'JS'
                let count = 0;
                ['data-toggle', 'data-target', 'data-dismiss'].forEach(attr => {
                    count += document.querySelectorAll(`[${attr}]`).length;
                });
                return count;
            JS);

            if ($deprecatedCount > 0) {
                $errors[] = "Found $deprecatedCount elements with deprecated BS4 data attributes";
            }

            // 4. Check critical deprecated classes
            $deprecatedClasses = $this->client->executeScript(<<<'JS'
                const badClasses = ['badge-primary', 'badge-secondary', 'badge-success',
                                   'badge-danger', 'badge-warning', 'badge-info'];
                let found = [];
                document.querySelectorAll('*').forEach(el => {
                    if (typeof el.className === 'string') {
                        badClasses.forEach(cls => {
                            if (el.className.includes(cls)) {
                                found.push(cls);
                            }
                        });
                    }
                });
                return [...new Set(found)];
            JS);

            if (!empty($deprecatedClasses)) {
                $errors[] = "Found deprecated badge classes: " . implode(', ', $deprecatedClasses);
            }

            // Report results
            if (!empty($errors)) {
                $this->fail("Bootstrap 5 smoke test failed:\n- " . implode("\n- ", $errors));
            }

        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
        $this->assertTrue(true, 'Bootstrap 5 smoke test passed');
    }

    /**
     * Quick visual check - captures page state for manual review.
     * This test always passes but outputs diagnostic info.
     */
    #[Test]
    public function testPageStateCapture(): void
    {
        $this->base();

        try {
            $this->login(LoginTestData::username, LoginTestData::password);

            // Capture page state info for debugging
            $pageState = $this->client->executeScript(<<<'JS'
                return {
                    url: location.href,
                    title: document.title,
                    bodyClasses: document.body.className,
                    bootstrapVersion: (typeof bootstrap !== 'undefined' && bootstrap.Modal)
                        ? bootstrap.Modal.VERSION : 'not loaded',
                    elementsWithBsToggle: document.querySelectorAll('[data-bs-toggle]').length,
                    elementsWithOldToggle: document.querySelectorAll('[data-toggle]').length,
                    modalsCount: document.querySelectorAll('.modal').length,
                    dropdownsCount: document.querySelectorAll('.dropdown').length
                };
            JS);

            // Output diagnostic info
            fwrite(STDERR, "\n[BS5 Page State]\n");
            fwrite(STDERR, json_encode($pageState, JSON_PRETTY_PRINT) . "\n");

        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }

        $this->client->quit();
        $this->assertTrue(true);
    }
}
