<?php

/**
 * Tests for the NullEncryptionStrategy class
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

use OpenEMR\Common\Crypto\NullEncryptionStrategy;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use PHPUnit\Framework\TestCase;

final class NullEncryptionStrategyTest extends TestCase
{
    /**
     * @var NullEncryptionStrategy
     */
    private $nullStrategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nullStrategy = new NullEncryptionStrategy();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(EncryptionStrategyInterface::class, $this->nullStrategy);
    }

    public function testGetId(): void
    {
        $this->assertEquals('null', $this->nullStrategy->getId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('No Encryption', $this->nullStrategy->getName());
    }

    public function testGetDescription(): void
    {
        $description = $this->nullStrategy->getDescription();
        $this->assertIsString($description);
        $this->assertStringContainsString('plain text', $description);
        $this->assertStringContainsString('encryption', $description);
    }

    public function testEncryptStandardWithNull(): void
    {
        $result = $this->nullStrategy->encryptStandard(null);
        $this->assertNull($result);
    }

    public function testEncryptStandardWithEmptyString(): void
    {
        $result = $this->nullStrategy->encryptStandard('');
        $this->assertEquals('', $result);
    }

    public function testEncryptStandardWithValidString(): void
    {
        $testValue = 'Hello, World!';
        $result = $this->nullStrategy->encryptStandard($testValue);
        $this->assertEquals($testValue, $result);
    }

    public function testEncryptStandardWithComplexString(): void
    {
        $testValue = 'Special chars: !@#$%^&*()[]{}|;:,.<>?';
        $result = $this->nullStrategy->encryptStandard($testValue);
        $this->assertEquals($testValue, $result);
    }

    public function testEncryptStandardWithUnicode(): void
    {
        $testValue = 'Unicode: 🔒 安全 תירגום';
        $result = $this->nullStrategy->encryptStandard($testValue);
        $this->assertEquals($testValue, $result);
    }

    public function testEncryptStandardIgnoresCustomPassword(): void
    {
        $testValue = 'test data';
        $customPassword = 'secret_password';

        $result = $this->nullStrategy->encryptStandard($testValue, $customPassword);
        $this->assertEquals($testValue, $result);

        // Should be the same regardless of password
        $resultWithoutPassword = $this->nullStrategy->encryptStandard($testValue);
        $this->assertEquals($result, $resultWithoutPassword);
    }

    public function testEncryptStandardIgnoresKeySource(): void
    {
        $testValue = 'test data';

        $resultDrive = $this->nullStrategy->encryptStandard($testValue, null, 'drive');
        $resultDatabase = $this->nullStrategy->encryptStandard($testValue, null, 'database');

        $this->assertEquals($testValue, $resultDrive);
        $this->assertEquals($testValue, $resultDatabase);
        $this->assertEquals($resultDrive, $resultDatabase);
    }

    public function testDecryptStandardWithNull(): void
    {
        $result = $this->nullStrategy->decryptStandard(null);
        $this->assertNull($result);
    }

    public function testDecryptStandardWithEmptyString(): void
    {
        $result = $this->nullStrategy->decryptStandard('');
        $this->assertEquals('', $result);
    }

    public function testDecryptStandardWithValidString(): void
    {
        $testValue = 'Hello, World!';
        $result = $this->nullStrategy->decryptStandard($testValue);
        $this->assertEquals($testValue, $result);
    }

    public function testDecryptStandardIgnoresCustomPassword(): void
    {
        $testValue = 'test data';
        $customPassword = 'secret_password';

        $result = $this->nullStrategy->decryptStandard($testValue, $customPassword);
        $this->assertEquals($testValue, $result);

        // Should be the same regardless of password
        $resultWithoutPassword = $this->nullStrategy->decryptStandard($testValue);
        $this->assertEquals($result, $resultWithoutPassword);
    }

    public function testDecryptStandardIgnoresKeySource(): void
    {
        $testValue = 'test data';

        $resultDrive = $this->nullStrategy->decryptStandard($testValue, null, 'drive');
        $resultDatabase = $this->nullStrategy->decryptStandard($testValue, null, 'database');

        $this->assertEquals($testValue, $resultDrive);
        $this->assertEquals($testValue, $resultDatabase);
        $this->assertEquals($resultDrive, $resultDatabase);
    }

    public function testDecryptStandardIgnoresMinimumVersion(): void
    {
        $testValue = 'test data';

        $result1 = $this->nullStrategy->decryptStandard($testValue, null, 'drive', 1);
        $result6 = $this->nullStrategy->decryptStandard($testValue, null, 'drive', 6);
        $result999 = $this->nullStrategy->decryptStandard($testValue, null, 'drive', 999);

        $this->assertEquals($testValue, $result1);
        $this->assertEquals($testValue, $result6);
        $this->assertEquals($testValue, $result999);
        $this->assertEquals($result1, $result6);
        $this->assertEquals($result6, $result999);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        $testValues = [
            'simple text',
            '',
            null,
            'Special chars: !@#$%^&*()',
            'Unicode: 🔒 安全 תירגום',
            'Very long text: ' . str_repeat('Lorem ipsum dolor sit amet. ', 100),
            '12345',
            'true',
            'false',
            json_encode(['key' => 'value', 'array' => [1, 2, 3]])
        ];

        foreach ($testValues as $testValue) {
            $encrypted = $this->nullStrategy->encryptStandard($testValue);
            $decrypted = $this->nullStrategy->decryptStandard($encrypted);

            $this->assertEquals($testValue, $encrypted, "Encryption should return original value");
            $this->assertEquals($testValue, $decrypted, "Decryption should return original value");
            $this->assertEquals($encrypted, $decrypted, "Encrypted and decrypted values should be identical");
        }
    }

    public function testCryptCheckStandardAlwaysReturnsTrue(): void
    {
        $testValues = [
            null,
            '',
            'valid data',
            'invalid data',
            '001encrypted_looking_data',
            '999future_version',
            'plain text',
            'special!@#$%^&*()chars',
            'Unicode: 🔒',
            str_repeat('x', 10000), // Very long string
            '0',
            'false',
            'true'
        ];

        foreach ($testValues as $testValue) {
            $result = $this->nullStrategy->cryptCheckStandard($testValue);
            $this->assertTrue($result, "cryptCheckStandard should always return true for value: " . var_export($testValue, true));
        }
    }

    public function testIdentityFunction(): void
    {
        // Test that this strategy truly acts as an identity function
        $testValue = 'identity test';

        // Multiple operations should not change the value
        $step1 = $this->nullStrategy->encryptStandard($testValue);
        $step2 = $this->nullStrategy->decryptStandard($step1);
        $step3 = $this->nullStrategy->encryptStandard($step2, 'password', 'database');
        $step4 = $this->nullStrategy->decryptStandard($step3, 'different_password', 'drive', 999);

        $this->assertEquals($testValue, $step1);
        $this->assertEquals($testValue, $step2);
        $this->assertEquals($testValue, $step3);
        $this->assertEquals($testValue, $step4);
    }

    public function testParameterIgnoring(): void
    {
        $testValue = 'parameter test';

        // All these should return the same result regardless of parameters
        $results = [
            $this->nullStrategy->encryptStandard($testValue),
            $this->nullStrategy->encryptStandard($testValue, null),
            $this->nullStrategy->encryptStandard($testValue, ''),
            $this->nullStrategy->encryptStandard($testValue, 'password'),
            $this->nullStrategy->encryptStandard($testValue, null, 'drive'),
            $this->nullStrategy->encryptStandard($testValue, null, 'database'),
            $this->nullStrategy->encryptStandard($testValue, 'pass', 'drive'),
            $this->nullStrategy->encryptStandard($testValue, 'pass', 'database'),

            $this->nullStrategy->decryptStandard($testValue),
            $this->nullStrategy->decryptStandard($testValue, null),
            $this->nullStrategy->decryptStandard($testValue, ''),
            $this->nullStrategy->decryptStandard($testValue, 'password'),
            $this->nullStrategy->decryptStandard($testValue, null, 'drive'),
            $this->nullStrategy->decryptStandard($testValue, null, 'database'),
            $this->nullStrategy->decryptStandard($testValue, 'pass', 'drive', 1),
            $this->nullStrategy->decryptStandard($testValue, 'pass', 'database', 999)
        ];

        foreach ($results as $result) {
            $this->assertEquals($testValue, $result);
        }

        // All results should be identical
        $this->assertCount(1, array_unique($results));
    }

    public function testReturnTypes(): void
    {
        // Test that return types are preserved correctly

        // String input -> string output
        $stringResult = $this->nullStrategy->encryptStandard('test');
        $this->assertIsString($stringResult);

        // Null input -> null output
        $nullResult = $this->nullStrategy->encryptStandard(null);
        $this->assertNull($nullResult);

        // Empty string input -> empty string output
        $emptyResult = $this->nullStrategy->encryptStandard('');
        $this->assertIsString($emptyResult);
        $this->assertEquals('', $emptyResult);

        // Same for decrypt
        $stringDecrypt = $this->nullStrategy->decryptStandard('test');
        $this->assertIsString($stringDecrypt);

        $nullDecrypt = $this->nullStrategy->decryptStandard(null);
        $this->assertNull($nullDecrypt);

        $emptyDecrypt = $this->nullStrategy->decryptStandard('');
        $this->assertIsString($emptyDecrypt);
        $this->assertEquals('', $emptyDecrypt);

        // cryptCheckStandard always returns boolean true
        $checkResult = $this->nullStrategy->cryptCheckStandard('anything');
        $this->assertIsBool($checkResult);
        $this->assertTrue($checkResult);
    }
}
