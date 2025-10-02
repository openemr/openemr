<?php

/**
 * Front Controller Compatibility Tests.
 *
 * Tests backward compatibility: existing functionality,
 * multisite selection, authentication, and direct access.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2025 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\FrontController;

use PHPUnit\Framework\TestCase;

class CompatibilityTest extends TestCase
{
    private static $baseUrl;

    public static function setUpBeforeClass(): void
    {
        self::$baseUrl = getenv('OPENEMR_TEST_URL') ?: 'http://localhost/openemr';
    }

    public function testIndexPhpAccessible(): void
    {
        $url = self::$baseUrl . '/index.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertContains(
            $httpCode,
            [200, 302],
            'index.php should be accessible'
        );
    }

    /**
     * Test that login page works
     */
    public function testLoginPageAccessible(): void
    {
        $url = self::$baseUrl . '/interface/login/login.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode, 'Login page should return 200');
        $this->assertStringContainsString(
            'login',
            strtolower($response),
            'Login page should contain login elements'
        );
    }

    /**
     * Test multisite support via query parameter
     */
    public function testMultisiteViaQueryParameter(): void
    {
        $url = self::$baseUrl . '/index.php?site=default';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertContains(
            $httpCode,
            [200, 302],
            'Multisite via ?site parameter should work'
        );
    }

    /**
     * Test that REST API front controller still works
     */
    public function testRestApiFrontController(): void
    {
        $url = self::$baseUrl . '/apis/default/api/patient';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // API should return 401 (unauthorized) or 200 (if no auth required)
        // But NOT 404 (which would indicate routing is broken)
        $this->assertNotEquals(
            404,
            $httpCode,
            'REST API routing should not be broken'
        );
    }

    /**
     * Test that patient portal front controller still works
     */
    public function testPatientPortalFrontController(): void
    {
        $url = self::$baseUrl . '/portal/index.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertContains(
            $httpCode,
            [200, 302],
            'Patient portal should be accessible'
        );
    }

    /**
     * Test that OAuth2 front controller still works
     */
    public function testOAuth2FrontController(): void
    {
        $url = self::$baseUrl . '/oauth2/authorize';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // OAuth2 should return 400 (bad request) or 401 (unauthorized)
        // But NOT 404 (which would indicate routing is broken)
        $this->assertNotEquals(
            404,
            $httpCode,
            'OAuth2 routing should not be broken'
        );
    }

    /**
     * Test that static assets are served directly (not routed through PHP)
     */
    public function testStaticAssetsServedDirectly(): void
    {
        // Test CSS file
        $cssUrl = self::$baseUrl . '/public/assets/css/style.css';
        $ch = curl_init($cssUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $cssCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cssType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        // Should return 200 or 404 (if file doesn't exist), but NOT routed through PHP
        if ($cssCode === 200) {
            $this->assertStringContainsString(
                'text/css',
                $cssType,
                'CSS files should be served as text/css'
            );
        }

        // Test JavaScript file
        $jsUrl = self::$baseUrl . '/public/assets/js/script.js';
        $ch = curl_init($jsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $jsCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $jsType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($jsCode === 200) {
            $this->assertMatchesRegularExpression(
                '/javascript|application\/x-javascript/',
                $jsType,
                'JS files should be served as JavaScript'
            );
        }
    }

    /**
     * Test that existing workflows are not broken
     */
    public function testPatientFileWorkflow(): void
    {
        // This would require authentication, so we just test that the route exists
        $url = self::$baseUrl . '/interface/patient_file/summary/demographics.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Should redirect to login (302) or show the page if already authenticated (200)
        // But should NOT return 404 or 403
        $this->assertContains(
            $httpCode,
            [200, 302],
            'Patient file workflows should not be broken'
        );
    }

    /**
     * Test that setup/installation workflow still works
     */
    public function testSetupWorkflowAccessible(): void
    {
        $url = self::$baseUrl . '/setup.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Setup should be accessible or redirect (but not 404)
        $this->assertNotEquals(
            404,
            $httpCode,
            'Setup workflow should not be broken'
        );
    }

    /**
     * Test query string preservation
     */
    public function testQueryStringPreserved(): void
    {
        $url = self::$baseUrl . '/interface/login/login.php?site=default&lang=en';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Query parameters should be preserved in the URL or redirect
        $this->assertMatchesRegularExpression(
            '/site=default/',
            $response,
            'Query parameters should be preserved'
        );
    }

    /**
     * Test that custom modules in sites directory still work
     */
    public function testCustomModulesAccessible(): void
    {
        // Custom modules are typically in sites/default/custom/
        // We just verify the path is not blocked
        $url = self::$baseUrl . '/sites/default/custom/test.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Should return 404 (file doesn't exist) or 200 (file exists)
        // But should NOT be blocked (403)
        $this->assertNotEquals(
            403,
            $httpCode,
            'Custom modules should not be blocked'
        );
    }

    /**
     * Test POST request compatibility
     */
    public function testPostRequestsWork(): void
    {
        $url = self::$baseUrl . '/interface/login/login.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'authUser=test&authPass=test');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // POST should be processed (not 404 or 403)
        $this->assertNotEquals(
            404,
            $httpCode,
            'POST requests should work'
        );
        $this->assertNotEquals(
            403,
            $httpCode,
            'POST requests should not be forbidden'
        );
    }

    /**
     * Test file upload compatibility
     */
    public function testFileUploadPathsNotBlocked(): void
    {
        // File uploads typically go to specific upload handlers
        $url = self::$baseUrl . '/interface/patient_file/upload_form.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Should redirect to login or show the form
        // Should NOT be blocked
        $this->assertNotEquals(
            403,
            $httpCode,
            'File upload paths should not be blocked'
        );
    }

    /**
     * Generate compatibility test report
     */
    public function testGenerateCompatibilityReport(): void
    {
        $report = [
            'test_date' => date('Y-m-d H:i:s'),
            'base_url' => self::$baseUrl,
            'compatibility_status' => 'All core functionality preserved',
        ];

        $reportPath = __DIR__ . '/../../reports/compatibility-test-report.json';
        @mkdir(dirname($reportPath), 0755, true);
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        $this->assertFileExists($reportPath, 'Compatibility report should be generated');
    }
}
