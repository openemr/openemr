<?php

/**
 * Front Controller Security Tests.
 *
 * Tests .inc.php blocking, path traversal prevention,
 * file validation, and CLI detection.
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

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    private static $baseUrl;
    private static $vulnerable_inc_files = [];

    public static function setUpBeforeClass(): void
    {
        self::$baseUrl = getenv('OPENEMR_TEST_URL') ?: 'http://localhost/openemr';
        self::loadVulnerableIncFiles();
    }

    private static function loadVulnerableIncFiles(): void
    {
        $basePath = dirname(dirname(__DIR__));
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.inc\.php$/i', $file->getFilename())) {
                $relativePath = str_replace($basePath . '/', '', $file->getPathname());
                self::$vulnerable_inc_files[] = $relativePath;
            }
        }
    }
    public function testIncPhpFilesBlocked(): void
    {
        // Test a sample of vulnerable .inc.php files
        $sampleFiles = array_slice(self::$vulnerable_inc_files, 0, 10);

        foreach ($sampleFiles as $file) {
            $url = self::$baseUrl . '/' . $file;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->assertEquals(
                403,
                $httpCode,
                "File {$file} should be blocked with 403 Forbidden, got {$httpCode}"
            );
        }
    }

    /**
     * Test specific vulnerable file from security log
     */
    public function testHistoryIncPhpBlocked(): void
    {
        // This is the file from the security log that caused xl() undefined error
        $url = self::$baseUrl . '/interface/patient_file/history/history.inc.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(
            403,
            $httpCode,
            "history.inc.php should be blocked with 403 Forbidden"
        );
    }

    /**
     * Test path traversal attack prevention
     */
    public function testPathTraversalBlocked(): void
    {
        $attackVectors = [
            '../../../etc/passwd',
            '....//....//....//etc/passwd',
            '..%2F..%2F..%2Fetc%2Fpasswd',
            '..%5c..%5c..%5cetc%5cpasswd',
            '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd',
        ];

        foreach ($attackVectors as $vector) {
            $url = self::$baseUrl . '/home.php?_ROUTE=' . urlencode($vector);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->assertEquals(
                404,
                $httpCode,
                "Path traversal attack '{$vector}' should return 404, got {$httpCode}"
            );
        }
    }

    /**
     * Test that non-PHP files are blocked
     */
    public function testNonPhpFilesBlocked(): void
    {
        $nonPhpFiles = [
            '.htaccess',
            'composer.json',
            'README.md',
            '.env',
            'config.yaml',
        ];

        foreach ($nonPhpFiles as $file) {
            $url = self::$baseUrl . '/home.php?_ROUTE=' . urlencode($file);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->assertEquals(
                404,
                $httpCode,
                "Non-PHP file '{$file}' should return 404, got {$httpCode}"
            );
        }
    }

    /**
     * Test that non-existent files return 404
     */
    public function testNonExistentFilesReturn404(): void
    {
        $nonExistentFiles = [
            'nonexistent.php',
            'interface/nonexistent/file.php',
            'fake/path/to/file.php',
        ];

        foreach ($nonExistentFiles as $file) {
            $url = self::$baseUrl . '/home.php?_ROUTE=' . urlencode($file);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->assertEquals(
                404,
                $httpCode,
                "Non-existent file '{$file}' should return 404, got {$httpCode}"
            );
        }
    }

    /**
     * Test that legitimate PHP files are accessible
     */
    public function testLegitimatePhpFilesAccessible(): void
    {
        $legitimateFiles = [
            'index.php',
            'interface/login/login.php',
        ];

        foreach ($legitimateFiles as $file) {
            $url = self::$baseUrl . '/' . $file;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->assertContains(
                $httpCode,
                [200, 302],
                "Legitimate file '{$file}' should be accessible, got {$httpCode}"
            );
        }
    }

    /**
     * Test that front controller can be disabled via feature flag
     */
    public function testFrontControllerCanBeDisabled(): void
    {
        // This test requires the ability to control the environment variable
        // In a real test environment, you would:
        // 1. Disable OPENEMR_ENABLE_FRONT_CONTROLLER
        // 2. Make a request
        // 3. Verify it returns 404 (front controller disabled)
        // 4. Re-enable for other tests

        $this->markTestSkipped(
            'This test requires environment variable control - manual testing required'
        );
    }

    /**
     * Test security headers are present
     */
    public function testSecurityHeadersPresent(): void
    {
        $url = self::$baseUrl . '/index.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->assertStringContainsString(
            'X-Content-Type-Options: nosniff',
            $response,
            'Security header X-Content-Type-Options should be present'
        );

        $this->assertStringContainsString(
            'X-XSS-Protection:',
            $response,
            'Security header X-XSS-Protection should be present'
        );

        $this->assertStringContainsString(
            'X-Frame-Options:',
            $response,
            'Security header X-Frame-Options should be present'
        );
    }

    /**
     * Generate security test report
     */
    public function testGenerateSecurityReport(): void
    {
        $report = [
            'test_date' => date('Y-m-d H:i:s'),
            'total_inc_files' => count(self::$vulnerable_inc_files),
            'sample_tested' => 10,
            'vulnerable_files' => self::$vulnerable_inc_files,
        ];

        $reportPath = __DIR__ . '/../../reports/security-test-report.json';
        @mkdir(dirname($reportPath), 0755, true);
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        $this->assertFileExists($reportPath, 'Security report should be generated');
    }
}
