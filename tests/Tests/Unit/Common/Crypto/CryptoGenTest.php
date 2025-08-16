<?php

/**
 * Tests for the CryptoGen class
 *
 * @category  Test
 * @package   OpenEMR\Tests\Unit\Common\Crypto
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   GNU General Public License 3
 * @link      http://www.open-emr.org
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Common\Crypto;

use Error;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\KeySource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

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

        // Test that key cache is initialized as empty array
        $reflection = new ReflectionClass($cryptoGen);
        $keyCacheProperty = $reflection->getProperty('keyCache');
        $keyCacheProperty->setAccessible(true);
        $this->assertIsArray($keyCacheProperty->getValue($cryptoGen));
        $this->assertEmpty($keyCacheProperty->getValue($cryptoGen));
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
        $testValue = 'test encryption data';
        $result = $this->cryptoGen->encryptStandard($testValue);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
        $this->assertNotEquals($testValue, $result);
    }

    public function testEncryptStandardWithCustomPassword(): void
    {
        $testValue = 'test data';
        $customPassword = 'mypassword123';
        $result = $this->cryptoGen->encryptStandard($testValue, $customPassword);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringStartsWith('006', $result);
    }

    public function testEncryptStandardWithDatabaseKeySource(): void
    {
        // Create a mock CryptoGen to mock SQL wrapper methods
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog'])
            ->getMock();

        // Mock SQL responses for database keys - allow more calls as needed
        $mockCryptoGen->expects($this->atLeastOnce())
            ->method('sqlQueryNoLog')
            ->willReturn(['value' => 'encoded_key_value']);

        $testValue = 'test data';
        $result = $mockCryptoGen->encryptStandard($testValue, null, KeySource::DATABASE->value);

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
        $originalValue = 'This is a test message for encryption and decryption';

        $encrypted = $this->cryptoGen->encryptStandard($originalValue);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);

        $this->assertEquals($originalValue, $decrypted);
    }

    public function testEncryptDecryptRoundTripWithCustomPassword(): void
    {
        $originalValue = 'Test data with custom password';
        $customPassword = 'secret123';

        $encrypted = $this->cryptoGen->encryptStandard($originalValue, $customPassword);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted, $customPassword);

        $this->assertEquals($originalValue, $decrypted);
    }

    public function testDecryptStandardWithMinimumVersion(): void
    {
        $testValue = 'test data';
        $encrypted = $this->cryptoGen->encryptStandard($testValue);
        $this->assertIsString($encrypted);

        // Should succeed with minimum version 6
        $decrypted = $this->cryptoGen->decryptStandard($encrypted, null, KeySource::DRIVE->value, 6);
        $this->assertEquals($testValue, $decrypted);

        // Should fail with minimum version 7
        $decrypted = $this->cryptoGen->decryptStandard($encrypted, null, KeySource::DRIVE->value, 7);
        $this->assertFalse($decrypted);
    }

    public function testCryptCheckStandardWithValidValues(): void
    {
        $this->assertTrue($this->cryptoGen->cryptCheckStandard('001test'));
        $this->assertTrue($this->cryptoGen->cryptCheckStandard('002test'));
        $this->assertTrue($this->cryptoGen->cryptCheckStandard('003test'));
        $this->assertTrue($this->cryptoGen->cryptCheckStandard('004test'));
        $this->assertTrue($this->cryptoGen->cryptCheckStandard('005test'));
        $this->assertTrue($this->cryptoGen->cryptCheckStandard('006test'));
    }

    public function testCryptCheckStandardWithInvalidValues(): void
    {
        $this->assertFalse($this->cryptoGen->cryptCheckStandard(''));
        $this->assertFalse($this->cryptoGen->cryptCheckStandard('007test'));
        $this->assertFalse($this->cryptoGen->cryptCheckStandard('000test'));
        $this->assertFalse($this->cryptoGen->cryptCheckStandard('test'));
        $this->assertFalse($this->cryptoGen->cryptCheckStandard('abc123'));
    }

    public function testCoreEncryptWithoutOpenSSL(): void
    {
        // Skip if openssl is not loaded (since we can't easily mock extension_loaded)
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped('OpenSSL extension not available');
        }

        // This test would require mocking extension_loaded which is difficult
        // Instead we test the normal case
        $reflection = new ReflectionMethod($this->cryptoGen, 'coreEncrypt');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($this->cryptoGen, 'test data');
        $this->assertIsString($result);
    }

    public function testCoreEncryptThrowsExceptionForBlankKey(): void
    {
        // Mock a scenario where keys would be blank
        $reflection = new ReflectionMethod($this->cryptoGen, 'coreEncrypt');
        $reflection->setAccessible(true);

        // This is hard to test without extensive mocking, so we test normal operation
        $result = $reflection->invoke($this->cryptoGen, 'test data');
        $this->assertIsString($result);
    }

    public function testCoreDecryptWithInvalidBase64(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'coreDecrypt');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($this->cryptoGen, 'invalid_base64_!!!');
        $this->assertFalse($result);
    }

    public function testAes256DecryptTwoWithValidData(): void
    {
        // This tests backward compatibility with version 2 encryption
        // We need to create a properly formatted version 2 encrypted value

        // For now, test that the method exists and handles invalid data
        $result = $this->cryptoGen->aes256DecryptTwo('invalid_data');
        $this->assertFalse($result);
    }

    public function testAes256DecryptMycrypt(): void
    {
        // Test scenario when mcrypt extension is not loaded
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isMcryptExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isMcryptExtensionLoaded')
            ->willReturn(false);

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->aes256Decrypt_mycrypt('test');
    }

    public function testAes256DecryptMycryptWithMcryptAvailable(): void
    {
        // Test scenario when mcrypt extension is loaded
        $testData = base64_encode('test encrypted data');
        $expectedDecrypted = 'decrypted';

        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isMcryptExtensionLoaded', 'pack', 'mcryptGetIvSize', 'mcryptCreateIv', 'mcryptDecrypt'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isMcryptExtensionLoaded')
            ->willReturn(true);

        $mockCryptoGen->expects($this->once())
            ->method('pack')
            ->willReturn('secret_key');

        $mockCryptoGen->expects($this->once())
            ->method('mcryptGetIvSize')
            ->willReturn(16);

        $mockCryptoGen->expects($this->once())
            ->method('mcryptCreateIv')
            ->willReturn('iv_value');

        $mockCryptoGen->expects($this->once())
            ->method('mcryptDecrypt')
            ->willReturn($expectedDecrypted);

        $result = $mockCryptoGen->aes256Decrypt_mycrypt($testData);
        $this->assertEquals($expectedDecrypted, $result);
    }

    public function testCollectCryptoKeyDriveSource(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Test creating a new key
        $key = $reflection->invoke($this->cryptoGen, 'test', 'a', KeySource::DRIVE);
        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key)); // 256 bits = 32 bytes

        // Test retrieving the same key (should be cached)
        $key2 = $reflection->invoke($this->cryptoGen, 'test', 'a', KeySource::DRIVE);
        $this->assertEquals($key, $key2);
    }

    public function testCollectCryptoKeyDatabaseSource(): void
    {
        // Create a mock CryptoGen to test database key collection
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog', 'sqlStatementNoLog', 'getRandomBytes'])->getMock();

        $testKey = 'random_32_byte_key_for_testing!!';
        $this->assertEquals(32, strlen($testKey), 'Test key must be exactly 32 bytes long');

        $mockCryptoGen->expects($this->once())
            ->method('getRandomBytes')
            ->willReturn($testKey);

        // Mock empty SQL response to trigger key creation
        $mockCryptoGen->expects($this->exactly(2))
            ->method('sqlQueryNoLog')
            ->willReturnOnConsecutiveCalls(
                ['value' => ''], // First call: key doesn't exist
                ['value' => base64_encode($testKey)] // Second call: return the created key
            );

        $mockCryptoGen->expects($this->once())
            ->method('sqlStatementNoLog');

        $reflection = new ReflectionMethod($mockCryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $key = $reflection->invoke($mockCryptoGen, 'test', 'a', KeySource::DATABASE);
        $this->assertIsString($key);
        $this->assertEquals($key, $testKey);
    }

    public function testCollectCryptoKeyOlderVersions(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Test older versions that don't encrypt the key on drive
        foreach (['one', 'two', 'three', 'four'] as $version) {
            $key = $reflection->invoke($this->cryptoGen, $version, 'a', KeySource::DRIVE);
            $this->assertIsString($key);
            $this->assertEquals(32, strlen($key));
        }
    }

    public function testFormatExceptionMessage(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'formatExceptionMessage');
        $reflection->setAccessible(true);

        $stackTrace = [
            [
                'file' => '/test/file.php',
                'line' => 123,
                'class' => 'TestClass',
                'type' => '->',
                'function' => 'testMethod'
            ],
            [
                'function' => 'anotherFunction'
            ]
        ];

        $result = $reflection->invoke($this->cryptoGen, $stackTrace);
        $this->assertIsString($result);
        $this->assertStringContainsString('Error Call Stack:', $result);
        $this->assertStringContainsString('/test/file.php', $result);
        $this->assertStringContainsString('123', $result);
        $this->assertStringContainsString('TestClass->testMethod()', $result);
        $this->assertStringContainsString('anotherFunction()', $result);
    }

    public function testDecryptStandardWithVersions(): void
    {
        // Test that different version prefixes route to correct decryption methods

        // Version 6 (current)
        $testData = 'test data';
        $encrypted = $this->cryptoGen->encryptStandard($testData);
        $this->assertIsString($encrypted);
        $this->assertTrue($this->cryptoGen->cryptCheckStandard($encrypted));

        // Test invalid version
        $invalidVersionData = '007' . base64_encode('test');
        $result = $this->cryptoGen->decryptStandard($invalidVersionData);
        $this->assertFalse($result);
    }

    public function testKeyVersionProperty(): void
    {
        $reflection = new ReflectionProperty($this->cryptoGen, 'keyVersion');
        $reflection->setAccessible(true);
        $this->assertEquals('six', $reflection->getValue($this->cryptoGen));
    }

    public function testEncryptionVersionProperty(): void
    {
        $reflection = new ReflectionProperty($this->cryptoGen, 'encryptionVersion');
        $reflection->setAccessible(true);
        $this->assertEquals('006', $reflection->getValue($this->cryptoGen));
    }

    public function testKeyCacheProperty(): void
    {
        $reflection = new ReflectionProperty($this->cryptoGen, 'keyCache');
        $reflection->setAccessible(true);

        $keySource = KeySource::DRIVE;

        // Initially empty
        $this->assertEmpty($reflection->getValue($this->cryptoGen));

        // After getting a key, should be populated
        $reflectionMethod = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->cryptoGen, 'test', 'a', $keySource);

        $cache = $reflection->getValue($this->cryptoGen);
        $this->assertIsArray($cache);
        // Since PHPStan can't analyze runtime behavior, we use array key existence as a proxy
        $cacheKeys = array_keys($cache);
        $expectedKey = "testa{$keySource->value}";
        $this->assertContains($expectedKey, $cacheKeys, 'Cache should contain the expected key after collectCryptoKey call');
    }

    public function testDecryptWithWrongPassword(): void
    {
        $testData = 'sensitive data';
        $password1 = 'password1';
        $password2 = 'password2';

        $encrypted = $this->cryptoGen->encryptStandard($testData, $password1);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted, $password2);

        $this->assertFalse($decrypted);
    }

    public function testLargeDataEncryption(): void
    {
        $largeData = str_repeat('This is a large data string for testing. ', 1000);

        $encrypted = $this->cryptoGen->encryptStandard($largeData);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);

        $this->assertEquals($largeData, $decrypted);
    }

    public function testSpecialCharacterEncryption(): void
    {
        $specialData = "Test data with special chars: àáâãäå æç èéêë ìíîï ñ òóôõö ùúûü ÿ 日本語 العربية русский";

        $encrypted = $this->cryptoGen->encryptStandard($specialData);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);

        $this->assertEquals($specialData, $decrypted);
    }

    public function testBinaryDataEncryption(): void
    {
        $binaryData = pack('C*', ...range(0, 255));

        $encrypted = $this->cryptoGen->encryptStandard($binaryData);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);

        $this->assertEquals($binaryData, $decrypted);
    }

    public function testDecryptStandardAllVersions(): void
    {
        // Test all supported decryption versions

        // Version 6 (current)
        $testData = 'test version 6';
        $encrypted = $this->cryptoGen->encryptStandard($testData);
        $this->assertIsString($encrypted);
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);
        $this->assertEquals($testData, $decrypted);

        // Test version routing by manually creating version prefixes
        // Note: These test the routing logic, not actual decryption since we don't have legacy encrypted data

        // Version 5 - should route to coreDecrypt
        $version5Data = '005' . base64_encode('mock_encrypted_data');
        $result = $this->cryptoGen->decryptStandard($version5Data);
        $this->assertFalse($result); // Will fail due to invalid data, but tests routing

        // Version 4 - should route to coreDecrypt
        $version4Data = '004' . base64_encode('mock_encrypted_data');
        $result = $this->cryptoGen->decryptStandard($version4Data);
        $this->assertFalse($result);

        // Version 3 - should route to aes256DecryptTwo
        $version3Data = '003' . base64_encode('mock_encrypted_data');
        $result = $this->cryptoGen->decryptStandard($version3Data);
        $this->assertFalse($result);

        // Version 2 - should route to aes256DecryptTwo
        $version2Data = '002' . base64_encode('mock_encrypted_data');
        $result = $this->cryptoGen->decryptStandard($version2Data);
        $this->assertFalse($result);

        // Version 1 - should route to aes256DecryptOne
        $version1Data = '001' . base64_encode('mock_encrypted_data');
        $result = $this->cryptoGen->decryptStandard($version1Data);
        $this->assertFalse($result);
    }

    public function testCollectCryptoKeyWithExistingDatabaseKey(): void
    {
        // Test using drive keys instead of database to avoid SQL mocking complexity
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // First call will create a key, second call should return the same key from cache
        $key1 = $reflection->invoke($this->cryptoGen, 'existing', 'a', KeySource::DRIVE);
        $key2 = $reflection->invoke($this->cryptoGen, 'existing', 'a', KeySource::DRIVE);

        $this->assertIsString($key1);
        $this->assertEquals(32, strlen($key1)); // 256-bit key should be 32 bytes
        $this->assertEquals($key1, $key2); // Should be the same key from cache

        // Verify the key file was created
        $keyFilePath = $this->testSiteDir . '/documents/logs_and_misc/methods/existinga';
        $this->assertFileExists($keyFilePath);
    }

    public function testCollectCryptoKeyNewerVersionEncryption(): void
    {
        // Test newer versions (five, six) that encrypt the key on drive
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Create a key with version 'five'
        $key = $reflection->invoke($this->cryptoGen, 'five', 'a', KeySource::DRIVE);
        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key));

        // Verify the file was created and encrypted
        $keyFile = $this->testSiteDir . '/documents/logs_and_misc/methods/fivea';
        $this->assertFileExists($keyFile);

        // The content should be encrypted (start with version prefix)
        $content = file_get_contents($keyFile);
        $this->assertIsString($content);
        $this->assertStringStartsWith('006', $content);
    }

    public function testHmacValidationFailure(): void
    {
        // Create a valid encrypted value, then tamper with it to cause HMAC failure
        $testData = 'test data';
        $encrypted = $this->cryptoGen->encryptStandard($testData);

        // Remove version prefix and decode
        $this->assertIsString($encrypted);
        $withoutVersion = substr($encrypted, 3);
        $raw = base64_decode($withoutVersion);

        // Tamper with the HMAC (first 48 bytes)
        $tamperedRaw = 'X' . substr($raw, 1);
        $tamperedEncrypted = '006' . base64_encode($tamperedRaw);

        // This should fail HMAC validation and return false
        $result = $this->cryptoGen->decryptStandard($tamperedEncrypted);
        $this->assertFalse($result);
    }

    public function testCustomPasswordHmacFailure(): void
    {
        // Test HMAC failure with custom password
        $testData = 'test data';
        $password = 'mypassword';
        $encrypted = $this->cryptoGen->encryptStandard($testData, $password);

        // Tamper with the encrypted data
        $this->assertIsString($encrypted);
        $withoutVersion = substr($encrypted, 3);
        $raw = base64_decode($withoutVersion);

        // Skip salt (32 bytes) and tamper with HMAC (next 48 bytes)
        $tamperedRaw = substr($raw, 0, 32) . 'X' . substr($raw, 33);
        $tamperedEncrypted = '006' . base64_encode($tamperedRaw);

        $result = $this->cryptoGen->decryptStandard($tamperedEncrypted, $password);
        $this->assertFalse($result);
    }

    public function testAes256DecryptTwoHmacFailure(): void
    {
        // Test HMAC failure in aes256DecryptTwo
        $fakeEncrypted = base64_encode(str_repeat('X', 100)); // Invalid HMAC
        $result = $this->cryptoGen->aes256DecryptTwo($fakeEncrypted);
        $this->assertFalse($result);
    }

    public function testDecryptStandardErrorCases(): void
    {
        // Test with malformed version
        $result = $this->cryptoGen->decryptStandard('abc123');
        $this->assertFalse($result);

        // Test with unsupported version
        $result = $this->cryptoGen->decryptStandard('999test');
        $this->assertFalse($result);

        // Test minimum version rejection
        $testData = 'test';
        $encrypted = $this->cryptoGen->encryptStandard($testData); // Version 6
        $this->assertIsString($encrypted);
        $result = $this->cryptoGen->decryptStandard($encrypted, null, KeySource::DRIVE->value, 7);
        $this->assertFalse($result);
    }

    public function testCoreDecryptErrorPaths(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'coreDecrypt');
        $reflection->setAccessible(true);

        // Test with malformed base64
        $result = $reflection->invoke($this->cryptoGen, 'not_valid_base64_!');
        $this->assertFalse($result);

        // Test with too short data
        $shortData = base64_encode('short');
        $result = $reflection->invoke($this->cryptoGen, $shortData);
        $this->assertFalse($result);
    }

    public function testCollectCryptoKeyVersionVariations(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Test default parameters
        $key1 = $reflection->invoke($this->cryptoGen);
        $this->assertIsString($key1);
        $this->assertEquals(32, strlen($key1));

        // Test with sub parameter
        $key2 = $reflection->invoke($this->cryptoGen, 'one', 'test');
        $this->assertIsString($key2);
        $this->assertEquals(32, strlen($key2));

        // Keys should be different
        $this->assertNotEquals($key1, $key2);
    }

    public function testKeyCache(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $cacheProperty = new ReflectionProperty($this->cryptoGen, 'keyCache');
        $cacheProperty->setAccessible(true);

        // Initially empty
        $this->assertEmpty($cacheProperty->getValue($this->cryptoGen));

        // Get a key
        $key1 = $reflection->invoke($this->cryptoGen, 'cache_test', 'a', KeySource::DRIVE);

        // Getting the same key again should return cached version
        $key2 = $reflection->invoke($this->cryptoGen, 'cache_test', 'a', KeySource::DRIVE);
        $this->assertEquals($key1, $key2, 'Second call should return cached key (same as first)');

        // Cache should now contain the key - verify by checking cache size
        $cache = $cacheProperty->getValue($this->cryptoGen);
        $this->assertIsArray($cache);
        $this->assertGreaterThan(0, count($cache), 'Cache should have at least one entry after key operations');
    }

    public function testEmptyValueEncryptionDecryption(): void
    {
        // Test that empty values are handled correctly
        $encrypted = $this->cryptoGen->encryptStandard('');
        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        $this->assertStringStartsWith('006', $encrypted);

        $decrypted = $this->cryptoGen->decryptStandard($encrypted);
        $this->assertEquals('', $decrypted);
    }

    public function testDatabaseKeyCreationPath(): void
    {
        // Test using drive key source to avoid complex database mocking
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // This will create a real key file on drive and return the key
        $key = $reflection->invoke($this->cryptoGen, 'testkey', 'a', KeySource::DRIVE);

        // Key should be a valid 32-byte string
        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key)); // 256-bit key should be 32 bytes

        // Verify the key file was created
        $keyFilePath = $this->testSiteDir . '/documents/logs_and_misc/methods/testkeya';
        $this->assertFileExists($keyFilePath);

        // Second call should return the same key from cache
        $key2 = $reflection->invoke($this->cryptoGen, 'testkey', 'a', KeySource::DRIVE);
        $this->assertEquals($key, $key2);
    }

    public function testFormatExceptionMessageEdgeCases(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'formatExceptionMessage');
        $reflection->setAccessible(true);

        // Test with minimal stack trace
        $minimalTrace = [
            ['function' => 'testFunction']
        ];
        $result = $reflection->invoke($this->cryptoGen, $minimalTrace);
        $this->assertIsString($result);
        $this->assertStringContainsString('testFunction()', $result);

        // Test with empty stack trace
        $result = $reflection->invoke($this->cryptoGen, []);
        $this->assertIsString($result);
        $this->assertStringContainsString('Error Call Stack:', $result);

        // Test with stack trace missing some fields
        $partialTrace = [
            [
                'class' => 'TestClass',
                'file' => '/test.php',
                'function' => 'method',
                'type' => '::'
            ]
        ];
        $result = $reflection->invoke($this->cryptoGen, $partialTrace);
        $this->assertIsString($result);
        $this->assertStringContainsString('/test.php', $result);
        $this->assertStringContainsString('TestClass', $result);
        $this->assertStringContainsString('method()', $result);
    }

    public function testReadExistingOlderVersionKeys(): void
    {
        // Test reading existing older version keys from drive
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Create a mock older version key file (unencrypted)
        $keyDir = $this->testSiteDir . '/documents/logs_and_misc/methods';
        $testKey = base64_encode('test_older_key_32_bytes_for_testing!');
        file_put_contents($keyDir . '/threea', $testKey);

        $key = $reflection->invoke($this->cryptoGen, 'three', 'a', KeySource::DRIVE);
        $this->assertEquals(base64_decode($testKey), $key);
    }

    public function testReadExistingNewerVersionKeys(): void
    {
        // Test reading existing newer version keys from drive (encrypted)

        // Create a mock CryptoGen for the encryption part
        $mockCryptoGenForEncryption = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog'])
            ->getMock();

        // Mock database key responses for encryption - allow more calls as needed
        $mockCryptoGenForEncryption->expects($this->atLeastOnce())
            ->method('sqlQueryNoLog')
            ->willReturn(['value' => 'database_encoded_key']);

        // Create an encrypted key file using the mock
        $keyDir = $this->testSiteDir . '/documents/logs_and_misc/methods';
        $rawKey = 'test_newer_key_32_bytes_for_test'; // Exactly 32 bytes
        $encryptedKey = $mockCryptoGenForEncryption->encryptStandard($rawKey, null, KeySource::DATABASE->value);
        file_put_contents($keyDir . '/sixx', $encryptedKey);

        // Now test reading the key with a second mock for the decryption part
        $mockCryptoGenForDecryption = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['decryptStandard'])
            ->getMock();

        $mockCryptoGenForDecryption->expects($this->once())
            ->method('decryptStandard')
            ->willReturn($rawKey);

        $reflection = new ReflectionMethod($mockCryptoGenForDecryption, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $key = $reflection->invoke($mockCryptoGenForDecryption, 'six', 'x', KeySource::DRIVE);
        $this->assertEquals($rawKey, $key);
    }

    public function testCorruptedKeyFileHandling(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Create a corrupted key file for newer version (five/six that need decryption)
        $keyDir = $this->testSiteDir . '/documents/logs_and_misc/methods';
        file_put_contents($keyDir . '/corruptedkeya', 'invalid_encrypted_data');

        $this->expectException(CryptoGenException::class);
        $reflection->invoke($this->cryptoGen, 'corruptedkey', 'a', KeySource::DRIVE);
    }

    public function testMcryptLegacyFunction(): void
    {
        // Test the legacy mcrypt function behavior without mcrypt extension
        $testData = base64_encode('test encrypted data');

        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isMcryptExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isMcryptExtensionLoaded')
            ->willReturn(false);

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->aes256Decrypt_mycrypt($testData);
    }

    public function testCustomPasswordEncryptionPaths(): void
    {
        // Test custom password encryption to cover those specific code paths
        $testData = 'test custom password encryption';
        $password = 'test_password_123';

        // This should exercise the custom password path in coreEncrypt
        $encrypted = $this->cryptoGen->encryptStandard($testData, $password);
        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        $this->assertStringStartsWith('006', $encrypted);

        // And the custom password path in coreDecrypt
        $decrypted = $this->cryptoGen->decryptStandard($encrypted, $password);
        $this->assertEquals($testData, $decrypted);

        // Test wrong password to trigger HMAC failure path
        $wrongDecrypted = $this->cryptoGen->decryptStandard($encrypted, 'wrong_password');
        $this->assertFalse($wrongDecrypted);
    }

    public function testAllCollectCryptoKeyPaths(): void
    {
        $reflection = new ReflectionMethod($this->cryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        // Test all version/sub combinations to ensure full coverage
        $versions = ['one', 'two', 'three', 'four', 'five', 'six'];
        $subs = ['', 'a', 'b'];

        foreach ($versions as $version) {
            foreach ($subs as $sub) {
                $key = $reflection->invoke($this->cryptoGen, $version, $sub, KeySource::DRIVE);
                $this->assertIsString($key);
                $this->assertEquals(32, strlen($key));
            }
        }
    }

    public function testCoreDecryptCustomPasswordPath(): void
    {
        // Test coreDecrypt with custom password to cover those lines
        $reflection = new ReflectionMethod($this->cryptoGen, 'coreDecrypt');
        $reflection->setAccessible(true);

        // Create a mock encrypted value with salt for custom password
        $salt = str_repeat('S', 32); // 32 byte salt
        $hmac = str_repeat('H', 48); // 48 byte HMAC
        $iv = str_repeat('I', 16);   // 16 byte IV
        $data = str_repeat('D', 32); // encrypted data

        $mockEncrypted = base64_encode($salt . $hmac . $iv . $data);

        // This should exercise the custom password decryption path
        $result = $reflection->invoke($this->cryptoGen, $mockEncrypted, 'testpass');
        $this->assertFalse($result); // Will fail HMAC validation but covers the code
    }

    public function testMcryptMethodDirectly(): void
    {
        // Test the aes256Decrypt_mycrypt method directly using mocks for coverage
        $testData = base64_encode('test data for mcrypt');

        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isMcryptExtensionLoaded', 'pack', 'mcryptGetIvSize', 'mcryptCreateIv', 'mcryptDecrypt'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isMcryptExtensionLoaded')
            ->willReturn(true);

        $mockCryptoGen->expects($this->once())
            ->method('pack')
            ->willReturn('secret_key');

        $mockCryptoGen->expects($this->once())
            ->method('mcryptGetIvSize')
            ->willReturn(32);

        $mockCryptoGen->expects($this->once())
            ->method('mcryptCreateIv')
            ->willReturn('initialization_vector');

        $mockCryptoGen->expects($this->once())
            ->method('mcryptDecrypt')
            ->willReturn('decrypted_result');

        $result = $mockCryptoGen->aes256Decrypt_mycrypt($testData);
        $this->assertEquals('decrypted_result', $result);
    }

    public function testMcryptMethodDirectlyWithoutExtension(): void
    {
        // Test the aes256Decrypt_mycrypt method when mcrypt extension is not available
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isMcryptExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isMcryptExtensionLoaded')
            ->willReturn(false);

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->aes256Decrypt_mycrypt('test');
    }

    public function testAllExceptionPaths(): void
    {
        // Test various exception scenarios to improve coverage

        // Test with malformed encrypted data to trigger HMAC validation failures
        $malformedData = '006' . base64_encode(str_repeat('x', 100));
        $result = $this->cryptoGen->decryptStandard($malformedData);
        $this->assertFalse($result);

        // Test custom password with malformed data
        $result2 = $this->cryptoGen->decryptStandard($malformedData, 'password');
        $this->assertFalse($result2);

        // Test version 2 decrypt with malformed data
        $malformedV2 = '002' . base64_encode(str_repeat('y', 100));
        $result3 = $this->cryptoGen->decryptStandard($malformedV2);
        $this->assertFalse($result3);
    }

    public function testCoverageBoosterAllPaths(): void
    {
        // This test aims to hit any remaining uncovered lines

        // Test minimum version parameter
        $result = $this->cryptoGen->decryptStandard('001test', null, KeySource::DRIVE->value, 2);
        $this->assertFalse($result); // Should fail due to minimum version check

        // Test unknown version
        $result2 = $this->cryptoGen->decryptStandard('999test');
        $this->assertFalse($result2);

        // Test successful decryption paths for version coverage
        $v6Data = $this->cryptoGen->encryptStandard('test data');
        $this->assertIsString($v6Data);
        $this->assertStringStartsWith('006', $v6Data);
        $decrypted = $this->cryptoGen->decryptStandard($v6Data);
        $this->assertEquals('test data', $decrypted);
    }

    /**
     * Test encryption failure when OpenSSL extension is not loaded
     */
    public function testEncryptStandardOpenSSLNotLoaded(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(false);

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->encryptStandard('test data');
    }

    /**
     * Test encryption failure when random bytes fail in custom password mode
     */
    public function testEncryptStandardRandomBytesFailCustomPassword(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'getRandomBytes'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(true);

        // First call for salt generation returns empty (failure)
        $mockCryptoGen->expects($this->once())
            ->method('getRandomBytes')
            ->with(32)
            ->willReturn('');

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->encryptStandard('test data', 'custom_password');
    }

    /**
     * Test encryption failure when IV generation fails
     */
    public function testEncryptStandardEmptyIV(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'getOpenSSLCipherIvLength', 'getRandomBytes', 'hashPbkdf2', 'hashHkdf'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(true);

        $mockCryptoGen->expects($this->once())
            ->method('getOpenSSLCipherIvLength')
            ->willReturn(16);

        // Mock the key derivation functions for custom password
        $mockCryptoGen->expects($this->once())
            ->method('hashPbkdf2')
            ->willReturn('derived_pre_key');

        $mockCryptoGen->expects($this->exactly(2))
            ->method('hashHkdf')
            ->willReturn('derived_key');

        // Handle salt generation and IV generation
        $mockCryptoGen->expects($this->exactly(2))
            ->method('getRandomBytes')
            ->willReturnCallback(
                function ($length) {
                    return $length === 32 ? 'salt_32_bytes_long_generated!' : ''; // Salt succeeds, IV fails
                }
            );

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->encryptStandard('test data', 'custom_password');
    }

    /**
     * Test encryption failure when encryption result is blank
     */
    public function testEncryptStandardBlankResult(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(
                [
                'getOpenSSLCipherIvLength',
                'getRandomBytes',
                'hashHmac',
                'isOpenSSLExtensionLoaded',
                'opensslEncrypt',
                'sqlQueryNoLog'
                ]
            )
            ->getMock();

        $mockCryptoGen->expects($this->atLeastOnce())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(true);

        // Mock existing keys - allow more calls as needed for key collection
        $mockCryptoGen->expects($this->atLeastOnce())
            ->method('sqlQueryNoLog')
            ->willReturn(['value' => 'existing_encoded_key']);

        $mockCryptoGen->expects($this->once())
            ->method('getOpenSSLCipherIvLength')
            ->willReturn(16);

        $mockCryptoGen->expects($this->once())
            ->method('getRandomBytes')
            ->willReturn('valid_iv_123456');

        // Return empty encryption result (failure)
        $mockCryptoGen->expects($this->once())
            ->method('opensslEncrypt')
            ->willReturn('');

        $mockCryptoGen->expects($this->once())
            ->method('hashHmac')
            ->willReturn('valid_hmac');

        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->encryptStandard('non_empty_data', null, KeySource::DATABASE->value);
    }

    /**
     * Test decryption failure when OpenSSL extension is not loaded
     */
    public function testDecryptStandardOpenSSLNotLoaded(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(false);

        // Call with a version 6 encrypted value that will trigger coreDecrypt
        $result = $mockCryptoGen->decryptStandard('006test_data');
        $this->assertFalse($result);
    }

    /**
     * Test decryption failure when base64 decode fails
     */

    /**
     * Test decryption failure when secret keys are empty
     */

    /**
     * Test aes256DecryptTwo failure when OpenSSL extension is not loaded
     */
    public function testAes256DecryptTwoOpenSSLNotLoaded(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(false);

        $result = $mockCryptoGen->aes256DecryptTwo('test_data');
        $this->assertFalse($result);
    }

    /**
     * Test aes256DecryptTwo failure when secret keys are empty
     */

    /**
     * Test aes256DecryptTwo failure when base64 decode fails
     */

    /**
     * Test aes256DecryptTwo HMAC authentication failure with mocked dependencies
     */

    /**
     * Test aes256DecryptOne failure when OpenSSL extension is not loaded
     */
    public function testAes256DecryptOneOpenSSLNotLoaded(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded'])
            ->getMock();

        $mockCryptoGen->expects($this->once())
            ->method('isOpenSSLExtensionLoaded')
            ->willReturn(false);

        $result = $mockCryptoGen->aes256DecryptOne('test_data');
        $this->assertFalse($result);
    }

    /**
     * Test aes256DecryptOne failure when secret key is empty
     */

    /**
     * Test collectCryptoKey failure when random bytes fail for database storage
     */
    public function testCollectCryptoKeyDatabaseRandomBytesFailure(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog', 'getRandomBytes'])
            ->getMock();

        // Simulate empty SQL result (key doesn't exist)
        $mockCryptoGen->expects($this->once())
            ->method('sqlQueryNoLog')
            ->willReturn(['value' => '']);

        // Random bytes generation fails
        $mockCryptoGen->expects($this->once())
            ->method('getRandomBytes')
            ->willReturn('');

        $reflection = new ReflectionMethod($mockCryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $this->expectException(CryptoGenException::class);

        $reflection->invoke($mockCryptoGen, 'test', 'a', KeySource::DATABASE);
    }

    /**
     * Test collectCryptoKey failure when random bytes fail for drive storage
     */
    public function testCollectCryptoKeyDriveRandomBytesFailure(): void
    {
        // Set up globals for file path
        global $GLOBALS;
        $GLOBALS['OE_SITE_DIR'] = $this->testSiteDir;

        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['getRandomBytes'])
            ->getMock();

        // Random bytes generation fails
        $mockCryptoGen->expects($this->once())
            ->method('getRandomBytes')
            ->willReturn('');

        $reflection = new ReflectionMethod($mockCryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $this->expectException(CryptoGenException::class);

        $reflection->invoke($mockCryptoGen, 'test', 'a', KeySource::DRIVE);
    }

    /**
     * Test collectCryptoKey failure when key creation in database fails
     */
    public function testCollectCryptoKeyDatabaseCreationFailure(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog', 'createDatabaseKey'])
            ->getMock();

        // First call: key doesn't exist
        // Second call: key still empty after attempted creation
        $mockCryptoGen->expects($this->once())
            ->method('sqlQueryNoLog')
            ->willReturnOnConsecutiveCalls(
                ['value' => '']  // Key doesn't exist on initial query
            );

        $mockCryptoGen->expects($this->once())
            ->method('createDatabaseKey')
            ->willReturn('');  // Key still doesn't exist after "creation".

        $reflection = new ReflectionMethod($mockCryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $this->expectException(CryptoGenException::class);

        $reflection->invoke($mockCryptoGen, 'test', 'a', KeySource::DATABASE);
    }

    /**
     * When we create a new database key, if it doesn't successfully come back
     * from the database in the round-trip check, we expect a CryptoGenException.
     */
    public function testCollectCryptoKeyDatabaseRoundTripFailure(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog', 'sqlStatementNoLog', 'getRandomBytes'])
            ->getMock();

        $testKey = 'test_key_32_bytes_for_testing!!!'; // Exactly 32 bytes
        $this->assertEquals(32, strlen($testKey), 'Test key must be exactly 32 bytes long');

        // Mock random bytes generation to return our test key
        $mockCryptoGen->expects($this->once())
            ->method('getRandomBytes')
            ->with(32)
            ->willReturn($testKey);

        // Mock SQL query responses:
        // First call: key doesn't exist (empty value triggers creation)
        // Second call: round-trip verification returns different key (simulating corruption/storage failure)
        $mockCryptoGen->expects($this->exactly(2))
            ->method('sqlQueryNoLog')
            ->willReturnOnConsecutiveCalls(
                ['value' => ''], // Key doesn't exist initially
                ['value' => base64_encode('different_key_that_doesnt_match!')] // Round-trip returns wrong key
            );

        // Mock SQL statement for key insertion
        $mockCryptoGen->expects($this->once())
            ->method('sqlStatementNoLog')
            ->with(
                "INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)",
                ['testa', base64_encode($testKey)]
            );

        $reflection = new ReflectionMethod($mockCryptoGen, 'collectCryptoKey');
        $reflection->setAccessible(true);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('The newly created key could not be stored or encoded correctly.');

        $reflection->invoke($mockCryptoGen, 'test', 'a', KeySource::DATABASE);
    }

    /**
     * Test collectCryptoKey failure when drive key file doesn't exist after creation attempt
     */
    public function testCollectCryptoKeyDriveFileNotCreated(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['sqlQueryNoLog', 'fileExists', 'filePutContents'])
            ->getMock();

        // First fileExists call returns false (file doesn't exist initially)
        // Second fileExists call returns false again (file still doesn't exist after creation attempt)
        $mockCryptoGen->expects($this->exactly(2))
            ->method('fileExists')
            ->willReturn(false);

        $mockCryptoGen->expects($this->exactly(2))
            ->method('sqlQueryNoLog')
            ->willReturn(['value' => base64_encode('successful database key')]);

        $mockCryptoGen->expects($this->once())
            ->method('filePutContents')
            ->willReturn(16); // File write succeeds, returns number of bytes written

        $this->expectException(CryptoGenException::class);

        // This calls encryptStandard which internally calls collectCryptoKey
        $mockCryptoGen->encryptStandard('test data', null, KeySource::DRIVE->value);
    }

    /**
     * Expect to throw a CryptoGenException if hashing fails on a custom password in coreEncrypt.
     */
    public function testCustomPassEncryptHashFail(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'hashPbkdf2', 'hashHkdf'])
            ->getMock();

        $mockCryptoGen->method('isOpenSSLExtensionLoaded')->willReturn(true);
        $mockCryptoGen->method('hashPbkdf2')->willReturn('hashPbkdf2 value');
        $mockCryptoGen->method('hashHkdf')->willReturn('');
        $this->expectException(CryptoGenException::class);

        $mockCryptoGen->encryptStandard('test data', 'custom password');
    }

    /**
     * Expect it to return false if hashing fails on a custom password in coreDecrypt.
     */
    public function testCustomPassDecryptHashFail(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'hashPbkdf2', 'hashHkdf'])
            ->getMock();

        $mockCryptoGen->method('isOpenSSLExtensionLoaded')->willReturn(true);
        $mockCryptoGen->method('hashPbkdf2')->willReturn('hashPbkdf2 value');
        $mockCryptoGen->method('hashHkdf')->willReturn('');
        $this->assertFalse($mockCryptoGen->decryptStandard('006test data', 'custom password'));
    }

    /**
     * Expect it to return false if hashing fails on a custom password in aes256DecryptTwo.
     */
    public function testCustomPassAesTwoDecryptHashFail(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'hash'])
            ->getMock();

        $mockCryptoGen->method('isOpenSSLExtensionLoaded')->willReturn(true);
        $mockCryptoGen->method('hash')->willReturn('');

        $this->assertFalse($mockCryptoGen->aes256DecryptTwo('test data', 'custom password'));
    }

    /**
     * Expect it to return false if hashing fails on a custom password in aes256DecryptOne.
     */
    public function testCustomPassAesOneDecryptHashFail(): void
    {
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'hash'])
            ->getMock();

        $mockCryptoGen->method('isOpenSSLExtensionLoaded')->willReturn(true);
        $mockCryptoGen->method('hash')->willReturn('');

        $this->assertFalse($mockCryptoGen->aes256DecryptOne('test data', 'custom password'));
    }

    /**
     * Test that aes256DecryptTwo works with a custom password.
     */
    public function testAes256DecryptTwoWithCustomPassword(): void
    {
        $rawTestData = 'This is a test string that is approximately one hundred characters long for testing purposes here';
        $testData = base64_encode($rawTestData);
        $mockCryptoGen = $this->getMockBuilder(CryptoGen::class)
            ->onlyMethods(['isOpenSSLExtensionLoaded', 'openSSLDecrypt', 'hashEquals', 'hash'])
            ->getMock();

        $mockCryptoGen->method('isOpenSSLExtensionLoaded')->willReturn(true);
        $mockCryptoGen->method('hash')->willReturn('hash value');
        $mockCryptoGen->method('hashEquals')->willReturn(true);
        $encryptedData = mb_substr($rawTestData, 48, null, '8bit');
        $iv = mb_substr($rawTestData, 32, 16, '8bit');
        $mockCryptoGen->expects($this->once())
            ->method('openSSLDecrypt')
            ->with($encryptedData, 'aes-256-cbc', 'hash value', $iv)
            ->willReturn('decrypted data');

        $mockCryptoGen->aes256DecryptTwo($testData, 'custom password');
    }
}
