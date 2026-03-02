<?php

/**
 * TokenSearchValue Isolated Test
 *
 * Tests non-UUID token parsing and formatting. UUID-specific behavior
 * requires UuidRegistry and is tested in integration tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\TokenSearchValue;
use PHPUnit\Framework\TestCase;

class TokenSearchValueTest extends TestCase
{
    // =========================================================================
    // Constructor
    // =========================================================================

    public function testConstructorWithCodeOnly(): void
    {
        $token = new TokenSearchValue('active');
        $this->assertSame('active', $token->getCode());
        $this->assertNull($token->getSystem());
    }

    public function testConstructorWithCodeAndSystem(): void
    {
        $token = new TokenSearchValue('M', 'http://hl7.org/fhir/administrative-gender');
        $this->assertSame('M', $token->getCode());
        $this->assertSame('http://hl7.org/fhir/administrative-gender', $token->getSystem());
    }

    public function testConstructorWithNumericCode(): void
    {
        $token = new TokenSearchValue(42);
        $this->assertSame(42, $token->getCode());
    }

    // =========================================================================
    // buildFromFHIRString
    // =========================================================================

    public function testBuildFromFHIRStringWithCodeOnly(): void
    {
        $token = TokenSearchValue::buildFromFHIRString('active');
        $this->assertSame('active', $token->getCode());
        $this->assertNull($token->getSystem());
    }

    public function testBuildFromFHIRStringWithSystemAndCode(): void
    {
        $token = TokenSearchValue::buildFromFHIRString('http://hl7.org/fhir/administrative-gender|M');
        $this->assertSame('M', $token->getCode());
        $this->assertSame('http://hl7.org/fhir/administrative-gender', $token->getSystem());
    }

    public function testBuildFromFHIRStringWithEmptySystem(): void
    {
        $token = TokenSearchValue::buildFromFHIRString('|active');
        $this->assertSame('active', $token->getCode());
        $this->assertSame('', $token->getSystem());
    }

    public function testBuildFromFHIRStringWithMultiplePipes(): void
    {
        // When there are multiple pipes, system is first part, code is last part
        $token = TokenSearchValue::buildFromFHIRString('system|subsystem|code');
        $this->assertSame('code', $token->getCode());
        $this->assertSame('system', $token->getSystem());
    }

    // =========================================================================
    // setCode / setSystem
    // =========================================================================

    public function testSetCodeUpdatesCode(): void
    {
        $token = new TokenSearchValue('old');
        $token->setCode('new');
        $this->assertSame('new', $token->getCode());
    }

    public function testSetSystemUpdatesSystem(): void
    {
        $token = new TokenSearchValue('code');
        $token->setSystem('http://example.org');
        $this->assertSame('http://example.org', $token->getSystem());
    }

    // =========================================================================
    // __toString
    // =========================================================================

    public function testToStringWithCodeAndSystem(): void
    {
        $token = new TokenSearchValue('M', 'http://hl7.org/fhir/gender');
        $this->assertSame('M|http://hl7.org/fhir/gender', (string) $token);
    }

    public function testToStringWithCodeOnly(): void
    {
        $token = new TokenSearchValue('active');
        $this->assertSame('active|', (string) $token);
    }

    // =========================================================================
    // getHumanReadableCode (non-UUID)
    // =========================================================================

    public function testGetHumanReadableCodeReturnsCodeDirectlyForNonUuid(): void
    {
        $token = new TokenSearchValue('active');
        $this->assertSame('active', $token->getHumanReadableCode());
    }
}
