<?php

/**
 * Tests for the CryptoGen class - Public Interface Only
 *
 * @category  Test
 * @package   OpenEMR\Tests\Unit\Common\Crypto
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright 2025 OpenCoreEMR
 * @license   GNU General Public License 3
 * @link      http://www.open-emr.org
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Common\Crypto;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Crypto\CryptoGenException;
use PHPUnit\Framework\TestCase;

final class CryptoGenTest extends TestCase
{
    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    /**
     * @var array<string, mixed> Original $GLOBALS backup
     */
    private array $originalGlobals;

    /**
     * @var string Test site directory for file operations
     */
    private string $testSiteDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Backup original GLOBALS
        $globalsCopy = $GLOBALS;
        $this->originalGlobals = $globalsCopy;

        // Create test site directory
        $this->testSiteDir = sys_get_temp_dir() . '/openemr_test_' . uniqid();
        mkdir($this->testSiteDir . '/documents/logs_and_misc/methods', 0755, true);

        // Mock GLOBALS for testing
        $GLOBALS['OE_SITE_DIR'] = $this->testSiteDir;

        // Mock required global functions
        if (!function_exists('sqlQueryNoLog')) {
            eval(
                'function sqlQueryNoLog($query, $binds = []) {
                global $testSQLResponses;
                return $testSQLResponses[md5($query . serialize($binds))] ?? [];
            }'
            );
        }

        if (!function_exists('sqlStatementNoLog')) {
            eval(
                'function sqlStatementNoLog($query, $binds = []) {
                global $testSQLStatements;
                $testSQLStatements[] = ["query" => $query, "binds" => $binds];
                return true;
            }'
            );
        }

        if (!function_exists('errorLogEscape')) {
            eval(
                'function errorLogEscape($string) {
                return addslashes($string);
            }'
            );
        }

        $this->cryptoGen = new CryptoGen();
    }

    protected function tearDown(): void
    {
        // Restore original GLOBALS
        foreach ($this->originalGlobals as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        // Clean up test directory
        if (is_dir($this->testSiteDir)) {
            $this->removeDirectory($this->testSiteDir);
        }

        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testConstructor(): void
    {
        $cryptoGen = new CryptoGen();

        // Test that CryptoGen can be constructed successfully
        $this->assertInstanceOf(CryptoGen::class, $cryptoGen);

        // Test that the ready() method works
        $this->assertTrue(CryptoGen::ready());
    }

    public function testEncryptStandardWithEmptyValue(): void
    {
        $result = $this->cryptoGen->encryptStandard('');
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
    }

    public function testEncryptStandardWithNullValue(): void
    {
        $result = $this->cryptoGen->encryptStandard(null);
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
    }

    public function testEncryptStandardWithValidValue(): void
    {
        $testValue = 'test data for encryption';
        $result = $this->cryptoGen->encryptStandard($testValue);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
        $this->assertNotEquals($testValue, $result);
    }

    public function testEncryptStandardWithCustomPassword(): void
    {
        $testValue = 'test data';
        $customPassword = 'my_custom_password';

        $result = $this->cryptoGen->encryptStandard($testValue, $customPassword);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
    }

    public function testEncryptStandardWithDatabaseKeySource(): void
    {
        $testValue = 'test data';

        $result = $this->cryptoGen->encryptStandard($testValue, null, 'database');

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
    }

    public function testDecryptStandardWithEmptyValue(): void
    {
        $result = $this->cryptoGen->decryptStandard('');
        $this->assertEquals('', $result);
    }

    public function testDecryptStandardWithNullValue(): void
    {
        $result = $this->cryptoGen->decryptStandard(null);
        $this->assertEquals('', $result);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        $testValue = 'Hello, World! This is a test of encryption and decryption.';

        $encrypted = $this->cryptoGen->encryptStandard($testValue);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);

        $this->assertEquals($testValue, $decrypted);
    }

    public function testEncryptDecryptRoundTripWithCustomPassword(): void
    {
        $testValue = 'Test data with custom password';
        $customPassword = 'my_secret_password_123';

        $encrypted = $this->cryptoGen->encryptStandard($testValue, $customPassword);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted, $customPassword);

        $this->assertEquals($testValue, $decrypted);
    }

    public function testDecryptStandardWithMinimumVersion(): void
    {
        $testValue = 'test data';
        $encrypted = $this->cryptoGen->encryptStandard($testValue);

        // Should work with minimum version 6 (current version)
        $result = $this->cryptoGen->decryptStandard($encrypted, null, 'drive', 6);
        $this->assertEquals($testValue, $result);

        // Should fail with minimum version higher than current
        $result = $this->cryptoGen->decryptStandard($encrypted, null, 'drive', 7);
        $this->assertFalse($result);
    }

    public function testCryptCheckStandardWithValidValues(): void
    {
        $testValue = 'test data';
        $encrypted = $this->cryptoGen->encryptStandard($testValue);

        $this->assertTrue($this->cryptoGen->cryptCheckStandard($encrypted));

        // Test with empty encrypted value
        $this->assertFalse($this->cryptoGen->cryptCheckStandard(''));

        // Test with null
        $this->assertFalse($this->cryptoGen->cryptCheckStandard(null));
    }

    public function testCryptCheckStandardWithInvalidValues(): void
    {
        $invalidValues = [
            'invalid_data' => 'plain text without version prefix',
            '999invalid' => 'future version 999 format',
            'not_encrypted_at_all' => 'completely unencrypted text',
            '006' => 'version prefix only with no data',
        ];

        foreach ($invalidValues as $value => $description) {
            $this->assertFalse(
                $this->cryptoGen->cryptCheckStandard($value),
                "Expected cryptCheckStandard to return false for: {$description} (value: '{$value}')"
            );
        }
    }

    public function testReadyStaticMethod(): void
    {
        // Test that ready() returns boolean
        $this->assertIsBool(CryptoGen::ready());

        // In normal test environment, should return true
        $this->assertTrue(CryptoGen::ready());
    }
}
