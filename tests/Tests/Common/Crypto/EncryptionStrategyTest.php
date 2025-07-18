<?php

namespace OpenEMR\Tests\Common\Crypto;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Crypto\NullEncryptionStrategy;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

/**
 * Encryption Strategy Tests
 * 
 * Tests encryption strategies to ensure they can encrypt and decrypt data
 * returning the original value for round-trip operations.
 * 
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class EncryptionStrategyTest extends TestCase
{
    /**
     * Generate a random string for testing.
     *
     * @param int $length Length of the random string
     * @return string Random string
     */
    private function generateRandomString(int $length = 32): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    /**
     * Test that CryptoGenStrategy can encrypt and decrypt random strings.
     */
    public function testCryptoGenStrategyRoundTrip(): void
    {
        $strategy = new CryptoGenStrategy();
        $this->assertInstanceOf(EncryptionStrategyInterface::class, $strategy);
        
        // Test with random strings of various lengths
        $testStrings = [
            $this->generateRandomString(10),
            $this->generateRandomString(50),
            $this->generateRandomString(100),
            $this->generateRandomString(1000)
        ];
        
        foreach ($testStrings as $originalString) {
            // Encrypt the string
            $encrypted = $strategy->encryptStandard($originalString);
            $this->assertNotNull($encrypted, 'Encryption should not return null');
            $this->assertNotEquals($originalString, $encrypted, 'Encrypted value should be different from original');
            
            // Decrypt the encrypted string
            $decrypted = $strategy->decryptStandard($encrypted);
            $this->assertNotFalse($decrypted, 'Decryption should not fail');
            $this->assertEquals($originalString, $decrypted, 'Decrypted value should match original');
        }
    }

    /**
     * Test that CryptoGenStrategy can encrypt and decrypt with custom passwords.
     */
    public function testCryptoGenStrategyRoundTripWithCustomPassword(): void
    {
        $strategy = new CryptoGenStrategy();
        $originalString = $this->generateRandomString(50);
        $customPassword = $this->generateRandomString(20);
        
        // Encrypt with custom password
        $encrypted = $strategy->encryptStandard($originalString, $customPassword);
        $this->assertNotNull($encrypted, 'Encryption with custom password should not return null');
        $this->assertNotEquals($originalString, $encrypted, 'Encrypted value should be different from original');
        
        // Decrypt with same custom password
        $decrypted = $strategy->decryptStandard($encrypted, $customPassword);
        $this->assertNotFalse($decrypted, 'Decryption with custom password should not fail');
        $this->assertEquals($originalString, $decrypted, 'Decrypted value should match original');
        
        // Decrypt with wrong password should fail
        $wrongPassword = $this->generateRandomString(20);
        $failedDecryption = $strategy->decryptStandard($encrypted, $wrongPassword);
        $this->assertFalse($failedDecryption, 'Decryption with wrong password should fail');
    }

    /**
     * Test that NullEncryptionStrategy returns values unchanged.
     */
    public function testNullEncryptionStrategyRoundTrip(): void
    {
        $strategy = new NullEncryptionStrategy();
        $this->assertInstanceOf(EncryptionStrategyInterface::class, $strategy);
        
        // Test with random strings of various lengths
        $testStrings = [
            $this->generateRandomString(10),
            $this->generateRandomString(50),
            $this->generateRandomString(100),
            $this->generateRandomString(1000)
        ];
        
        foreach ($testStrings as $originalString) {
            // "Encrypt" the string (should return unchanged)
            $encrypted = $strategy->encryptStandard($originalString);
            $this->assertEquals($originalString, $encrypted, 'Null encryption should return original value');
            
            // "Decrypt" the string (should return unchanged)
            $decrypted = $strategy->decryptStandard($encrypted);
            $this->assertEquals($originalString, $decrypted, 'Null decryption should return original value');
        }
    }

    /**
     * Test that NullEncryptionStrategy encryption alone (without decryption) returns original value.
     * 
     * This is the key behavior of null encryption - the "encrypted" value IS the original value,
     * so no decryption step is needed to access the data.
     */
    public function testNullEncryptionStrategyEncryptionIsOriginal(): void
    {
        $strategy = new NullEncryptionStrategy();
        
        // Test with various random strings
        $testStrings = [
            $this->generateRandomString(25),
            $this->generateRandomString(75),
            'Special chars: !@#$%^&*()_+-=[]{}|;:,.<>?',
            'Unicode: 你好世界 🌍 ñáéíóú',
            '123456789',
            ''
        ];
        
        foreach ($testStrings as $originalString) {
            $encrypted = $strategy->encryptStandard($originalString);
            
            // The critical test: encrypted value should BE the original value
            $this->assertSame($originalString, $encrypted, 
                'Null encryption should return the exact same value - no decryption needed');
            
            // This means you can directly use the "encrypted" value as if it were decrypted
            $this->assertEquals($originalString, $encrypted, 
                'Encrypted value can be used directly without decryption');
        }
    }

    /**
     * Test edge cases with empty strings and null values.
     */
    public function testEdgeCases(): void
    {
        $strategies = [
            new CryptoGenStrategy(),
            new NullEncryptionStrategy()
        ];
        
        foreach ($strategies as $strategy) {
            $strategyName = get_class($strategy);
            
            // Test with null
            $encrypted = $strategy->encryptStandard(null);
            $this->assertNull($encrypted, "{$strategyName}: Encrypting null should return null");
            
            $decrypted = $strategy->decryptStandard(null);
            if ($strategy instanceof NullEncryptionStrategy) {
                $this->assertNull($decrypted, "{$strategyName}: Decrypting null should return null");
            } else {
                // CryptoGenStrategy returns empty string for null/empty input
                $this->assertEquals("", $decrypted, "{$strategyName}: Decrypting null should return empty string");
            }
            
            // Test with empty string
            $encrypted = $strategy->encryptStandard("");
            $decrypted = $strategy->decryptStandard($encrypted);
            $this->assertEquals("", $decrypted, "{$strategyName}: Empty string round trip should work");
        }
    }

    /**
     * Test that strategies report correct metadata.
     */
    public function testStrategyMetadata(): void
    {
        $cryptoGenStrategy = new CryptoGenStrategy();
        $this->assertEquals('cryptogen', $cryptoGenStrategy->getId());
        $this->assertEquals('Standard Encryption (AES-256-CBC)', $cryptoGenStrategy->getName());
        $this->assertStringContainsString('AES-256-CBC', $cryptoGenStrategy->getDescription());
        
        $nullStrategy = new NullEncryptionStrategy();
        $this->assertEquals('null', $nullStrategy->getId());
        $this->assertEquals('No Encryption', $nullStrategy->getName());
        $this->assertStringContainsString('plain text', $nullStrategy->getDescription());
    }

    /**
     * Test cryptCheckStandard validation.
     */
    public function testCryptCheckStandard(): void
    {
        $cryptoGenStrategy = new CryptoGenStrategy();
        $nullStrategy = new NullEncryptionStrategy();
        
        $testString = $this->generateRandomString(50);
        
        // Test CryptoGenStrategy validation
        $encrypted = $cryptoGenStrategy->encryptStandard($testString);
        $this->assertTrue($cryptoGenStrategy->cryptCheckStandard($encrypted), 'CryptoGen should validate its own encrypted data');
        $this->assertFalse($cryptoGenStrategy->cryptCheckStandard($testString), 'CryptoGen should not validate plain text');
        
        // Test NullEncryptionStrategy validation (always returns true)
        $this->assertTrue($nullStrategy->cryptCheckStandard($testString), 'Null strategy should validate any string');
        $this->assertTrue($nullStrategy->cryptCheckStandard($encrypted), 'Null strategy should validate encrypted data');
        $this->assertTrue($nullStrategy->cryptCheckStandard(""), 'Null strategy should validate empty string');
    }

    /**
     * Test that different random strings produce different encrypted outputs.
     */
    public function testEncryptionUniqueness(): void
    {
        $strategy = new CryptoGenStrategy();
        
        $string1 = $this->generateRandomString(50);
        $string2 = $this->generateRandomString(50);
        
        // Ensure we have different input strings
        $this->assertNotEquals($string1, $string2, 'Test strings should be different');
        
        $encrypted1 = $strategy->encryptStandard($string1);
        $encrypted2 = $strategy->encryptStandard($string2);
        
        // Different inputs should produce different encrypted outputs
        $this->assertNotEquals($encrypted1, $encrypted2, 'Different inputs should produce different encrypted outputs');
        
        // Even encrypting the same string twice should produce different outputs (due to random IV)
        $encrypted1Again = $strategy->encryptStandard($string1);
        $this->assertNotEquals($encrypted1, $encrypted1Again, 'Same input encrypted twice should produce different outputs');
        
        // But both should decrypt to the same original value
        $decrypted1 = $strategy->decryptStandard($encrypted1);
        $decrypted1Again = $strategy->decryptStandard($encrypted1Again);
        $this->assertEquals($string1, $decrypted1, 'First encryption should decrypt correctly');
        $this->assertEquals($string1, $decrypted1Again, 'Second encryption should decrypt correctly');
    }
}