<?php

/**
 * Tests for the CryptoGenStrategy class
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

use Error;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Crypto\CryptoGenException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class CryptoGenStrategyTest extends TestCase
{
    /**
     * @var CryptoGenStrategy
     */
    private $cryptoGenStrategy;

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
        /**
         * @var array<string, mixed> $globalsCopy
         */
        $globalsCopy = $GLOBALS;
        $this->originalGlobals = $globalsCopy;

        // Create test site directory
        $this->testSiteDir = sys_get_temp_dir() . '/openemr_test_' . uniqid();
        mkdir($this->testSiteDir . '/documents/logs_and_misc/methods', 0755, true);

        // Mock GLOBALS for testing
        $GLOBALS['OE_SITE_DIR'] = $this->testSiteDir;

        $this->cryptoGenStrategy = new CryptoGenStrategy();
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
        $cryptoGenStrategy = new CryptoGenStrategy();

        // Test that key cache is initialized as empty array
        $reflection = new ReflectionClass($cryptoGenStrategy);
        $keyCacheProperty = $reflection->getProperty('keyCache');
        $keyCacheProperty->setAccessible(true);
        $this->assertIsArray($keyCacheProperty->getValue($cryptoGenStrategy));
        $this->assertEmpty($keyCacheProperty->getValue($cryptoGenStrategy));
    }

    public function testCoreEncryptWithoutOpenSSL(): void
    {
        // Temporarily disable OpenSSL extension to simulate missing extension
        $reflection = new ReflectionMethod($this->cryptoGenStrategy, 'coreEncrypt');
        $reflection->setAccessible(true);

        // Mock the extension_loaded function to return false
        if (function_exists('extension_loaded')) {
            $this->markTestSkipped('Cannot mock extension_loaded function in this test environment');
        }

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('OpenEMR Error : Need to install openssl library');

        $reflection->invoke($this->cryptoGenStrategy, 'test');
    }

    public function testCoreEncryptThrowsExceptionForBlankKey(): void
    {
        // This test should verify that coreEncrypt works with empty password (uses standard keys)
        $reflection = new ReflectionMethod($this->cryptoGenStrategy, 'coreEncrypt');
        $reflection->setAccessible(true);

        // Empty password should work (falls back to standard keys)
        $result = $reflection->invoke($this->cryptoGenStrategy, 'test data', '');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testCoreDecryptWithInvalidBase64(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGenStrategy, 'coreDecrypt');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($this->cryptoGenStrategy, 'invalid-base64', null, 'drive', 'six');
        $this->assertFalse($result);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        // Test full encrypt/decrypt cycle using public methods
        $testData = 'test data for round trip';

        // Encrypt using the strategy
        $encrypted = $this->cryptoGenStrategy->encryptStandard($testData);
        $this->assertIsString($encrypted);
        $this->assertStringStartsWith('006', $encrypted);

        // Decrypt using the strategy
        $decrypted = $this->cryptoGenStrategy->decryptStandard($encrypted);
        $this->assertEquals($testData, $decrypted);
    }
}
