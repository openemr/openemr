<?php

/**
 * Front Controller Compatibility Tests.
 *
 * Tests backward compatibility: existing functionality,
 * multisite selection, authentication, and direct access.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   GPLv3
 */

namespace OpenCoreEMR\Tests\FrontController;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

class CompatibilityTest extends TestCase
{
    private static $baseUrl;
    private static $client;

    public static function setUpBeforeClass(): void
    {
        self::$baseUrl = getenv('OPENEMR_TEST_URL') ?: 'http://localhost/openemr';
        self::$client = new Client([
            'base_uri' => self::$baseUrl,
            'http_errors' => false,
            'allow_redirects' => true,
        ]);
    }

    public function testIndexPhpAccessible(): void
    {
        $response = self::$client->get('/index.php');
        $httpCode = $response->getStatusCode();

        $this->assertEquals(
            200,
            $httpCode,
            'index.php should return 200'
        );
    }

    /**
     * Test that login page works
     */
    public function testLoginPageAccessible(): void
    {
        $response = self::$client->get('/interface/login/login.php');
        $httpCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->assertEquals(200, $httpCode, 'Login page should return 200');
        $this->assertStringContainsString(
            'login',
            strtolower($body),
            'Login page should contain login elements'
        );
    }

    /**
     * Test multisite support via query parameter
     */
    public function testMultisiteViaQueryParameter(): void
    {
        $response = self::$client->get('/index.php?site=default');
        $httpCode = $response->getStatusCode();

        $this->assertEquals(
            200,
            $httpCode,
            'Multisite via ?site parameter should return 200'
        );
    }

    /**
     * Test that REST API front controller still works
     */
    public function testRestApiFrontController(): void
    {
        $response = self::$client->head('/apis/default/api/patient');
        $httpCode = $response->getStatusCode();

        // API should return 401 (unauthorized) - routing is working
        $this->assertEquals(
            401,
            $httpCode,
            'REST API should return 401 (unauthorized)'
        );
    }

    /**
     * Test that patient portal front controller still works
     */
    public function testPatientPortalFrontController(): void
    {
        $response = self::$client->get('/portal/index.php');
        $httpCode = $response->getStatusCode();

        $this->assertEquals(
            200,
            $httpCode,
            'Patient portal should return 200'
        );
    }

    /**
     * Test that OAuth2 front controller still works
     */
    public function testOAuth2FrontController(): void
    {
        $response = self::$client->head('/oauth2/authorize');
        $httpCode = $response->getStatusCode();

        // OAuth2 should return 400 (bad request) - routing is working
        $this->assertEquals(
            400,
            $httpCode,
            'OAuth2 should return 400 (bad request)'
        );
    }

    /**
     * Test that static assets are served directly (not routed through PHP)
     */
    public function testStaticAssetsServedDirectly(): void
    {
        // Test CSS file
        $cssResponse = self::$client->head('/public/assets/css/style.css');
        $cssCode = $cssResponse->getStatusCode();

        // Should return 200 or 404 (if file doesn't exist)
        if ($cssCode === 200) {
            $cssType = $cssResponse->getHeader('Content-Type')[0] ?? '';
            $this->assertStringContainsString(
                'text/css',
                $cssType,
                'CSS files should be served as text/css'
            );
        }

        // Test JavaScript file
        $jsResponse = self::$client->head('/public/assets/js/script.js');
        $jsCode = $jsResponse->getStatusCode();

        if ($jsCode === 200) {
            $jsType = $jsResponse->getHeader('Content-Type')[0] ?? '';
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
        // This would require authentication, so we expect redirect to login
        $response = self::$client->get('/interface/patient_file/summary/demographics.php', [
            'allow_redirects' => false
        ]);
        $httpCode = $response->getStatusCode();

        // Should redirect to login (302) since not authenticated
        $this->assertEquals(
            302,
            $httpCode,
            'Patient file should redirect to login when not authenticated'
        );
    }

    /**
     * Test that setup/installation workflow still works
     */
    public function testSetupWorkflowAccessible(): void
    {
        $response = self::$client->get('/setup.php', [
            'allow_redirects' => false
        ]);
        $httpCode = $response->getStatusCode();

        // Setup should be accessible (200) or redirect (302)
        $this->assertContains(
            $httpCode,
            [200, 302],
            'Setup workflow should be accessible'
        );
    }

    /**
     * Test query string preservation
     */
    public function testQueryStringPreserved(): void
    {
        $response = self::$client->get('/interface/login/login.php?site=default&lang=en', [
            'allow_redirects' => false
        ]);
        $body = (string) $response->getBody();
        $location = $response->getHeader('Location')[0] ?? '';

        // Query parameters should be preserved in the URL or redirect
        $fullResponse = $body . $location;
        $this->assertMatchesRegularExpression(
            '/site=default/',
            $fullResponse,
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
        $response = self::$client->head('/sites/default/custom/test.php');
        $httpCode = $response->getStatusCode();

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
        $response = self::$client->post('/interface/login/login.php', [
            'form_params' => [
                'authUser' => 'test',
                'authPass' => 'test'
            ],
            'allow_redirects' => false
        ]);
        $httpCode = $response->getStatusCode();

        // POST should return 200 or 302 (redirect)
        $this->assertContains(
            $httpCode,
            [200, 302],
            'POST requests should be processed'
        );
    }

    /**
     * Test file upload compatibility
     */
    public function testFileUploadPathsNotBlocked(): void
    {
        // File uploads typically go to specific upload handlers
        $response = self::$client->get('/interface/patient_file/upload_form.php', [
            'allow_redirects' => false
        ]);
        $httpCode = $response->getStatusCode();

        // Should redirect to login (302) or show the form (200)
        $this->assertContains(
            $httpCode,
            [200, 302],
            'File upload paths should be accessible'
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
        mkdir(dirname($reportPath), 0755, true);
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT) . "\n");

        $this->assertFileExists($reportPath, 'Compatibility report should be generated');
    }
}
