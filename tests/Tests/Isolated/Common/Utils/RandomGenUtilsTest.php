<?php

/**
 * Isolated RandomGenUtils Test
 *
 * Tests random generation utilities.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\RandomGenUtils;
use PHPUnit\Framework\TestCase;

class RandomGenUtilsTest extends TestCase
{
    public function testProduceRandomBytesReturnsCorrectLength(): void
    {
        $bytes = RandomGenUtils::produceRandomBytes(16);
        $this->assertSame(16, strlen($bytes));
    }

    public function testProduceRandomBytesReturnsRandomData(): void
    {
        $bytes1 = RandomGenUtils::produceRandomBytes(32);
        $bytes2 = RandomGenUtils::produceRandomBytes(32);
        $this->assertNotSame($bytes1, $bytes2);
    }

    public function testProduceRandomBytesZeroLength(): void
    {
        $bytes = RandomGenUtils::produceRandomBytes(0);
        $this->assertSame(0, strlen($bytes));
    }

    public function testProduceRandomStringDefaultLength(): void
    {
        $str = RandomGenUtils::produceRandomString();
        $this->assertSame(26, strlen($str));
    }

    public function testProduceRandomStringCustomLength(): void
    {
        $str = RandomGenUtils::produceRandomString(10);
        $this->assertSame(10, strlen($str));
    }

    public function testProduceRandomStringUsesDefaultAlphabet(): void
    {
        $str = RandomGenUtils::produceRandomString(100);
        // Default alphabet is lowercase + 234567
        $this->assertMatchesRegularExpression('/^[a-z2-7]+$/', $str);
    }

    public function testProduceRandomStringCustomAlphabet(): void
    {
        $str = RandomGenUtils::produceRandomString(50, 'ABC');
        $this->assertMatchesRegularExpression('/^[ABC]+$/', $str);
    }

    public function testProduceRandomStringIsRandom(): void
    {
        $str1 = RandomGenUtils::produceRandomString(20);
        $str2 = RandomGenUtils::produceRandomString(20);
        // With 32 character alphabet and 20 chars, collision is extremely unlikely
        $this->assertNotSame($str1, $str2);
    }

    public function testCreateUniqueTokenDefaultLength(): void
    {
        $token = RandomGenUtils::createUniqueToken();
        $this->assertSame(40, strlen($token));
    }

    public function testCreateUniqueTokenCustomLength(): void
    {
        $token = RandomGenUtils::createUniqueToken(20);
        $this->assertSame(20, strlen($token));
    }

    public function testCreateUniqueTokenUsesAlphanumeric(): void
    {
        $token = RandomGenUtils::createUniqueToken(100);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', $token);
    }

    public function testCreateUniqueTokenIsUnique(): void
    {
        $token1 = RandomGenUtils::createUniqueToken();
        $token2 = RandomGenUtils::createUniqueToken();
        $this->assertNotSame($token1, $token2);
    }

    public function testGeneratePortalPasswordLength(): void
    {
        $password = RandomGenUtils::generatePortalPassword();
        $this->assertSame(12, strlen($password));
    }

    public function testGeneratePortalPasswordContainsUppercase(): void
    {
        $password = RandomGenUtils::generatePortalPassword();
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
    }

    public function testGeneratePortalPasswordContainsLowercase(): void
    {
        $password = RandomGenUtils::generatePortalPassword();
        $this->assertMatchesRegularExpression('/[a-z]/', $password);
    }

    public function testGeneratePortalPasswordContainsNumber(): void
    {
        $password = RandomGenUtils::generatePortalPassword();
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
    }

    public function testGeneratePortalPasswordContainsSpecialChar(): void
    {
        $password = RandomGenUtils::generatePortalPassword();
        $this->assertMatchesRegularExpression('/[@#$%]/', $password);
    }

    public function testGeneratePortalPasswordIsRandom(): void
    {
        $password1 = RandomGenUtils::generatePortalPassword();
        $password2 = RandomGenUtils::generatePortalPassword();
        $this->assertNotSame($password1, $password2);
    }

    public function testGeneratePortalPasswordOnlyContainsValidChars(): void
    {
        $password = RandomGenUtils::generatePortalPassword();
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9@#$%]+$/', $password);
    }
}
