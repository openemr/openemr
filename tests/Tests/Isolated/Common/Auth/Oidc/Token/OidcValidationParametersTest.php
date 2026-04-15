<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Token;

use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;
use PHPUnit\Framework\TestCase;

final class OidcValidationParametersTest extends TestCase
{
    public function testConstructionWithRequiredFieldsOnly(): void
    {
        $params = new OidcValidationParameters(
            expectedIssuer: 'https://accounts.google.com',
            expectedAudience: 'my-client-id',
        );

        self::assertSame('https://accounts.google.com', $params->expectedIssuer);
        self::assertSame('my-client-id', $params->expectedAudience);
        self::assertSame(30, $params->clockSkewSeconds);
        self::assertSame(86400, $params->maxTokenAgeSeconds);
        self::assertSame(['RS256'], $params->allowedAlgorithms);
    }

    public function testConstructionWithAllCustomValues(): void
    {
        $params = new OidcValidationParameters(
            expectedIssuer: 'https://login.microsoftonline.com/tid/v2.0',
            expectedAudience: 'azure-client-id',
            clockSkewSeconds: 60,
            maxTokenAgeSeconds: 3600,
            allowedAlgorithms: ['RS256', 'RS384'],
        );

        self::assertSame('https://login.microsoftonline.com/tid/v2.0', $params->expectedIssuer);
        self::assertSame('azure-client-id', $params->expectedAudience);
        self::assertSame(60, $params->clockSkewSeconds);
        self::assertSame(3600, $params->maxTokenAgeSeconds);
        self::assertSame(['RS256', 'RS384'], $params->allowedAlgorithms);
    }

    public function testIsImmutable(): void
    {
        $reflection = new \ReflectionClass(OidcValidationParameters::class);
        self::assertTrue($reflection->isReadOnly());
        self::assertTrue($reflection->isFinal());
    }
}
