<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth;

use OpenEMR\Common\Auth\ExternalAuthenticationResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExternalAuthenticationResultTest extends TestCase
{
    #[Test]
    public function it_accepts_a_positive_user_id_and_safe_provider_id(): void
    {
        $result = new ExternalAuthenticationResult(42, 'oidc-keycloak_1');

        self::assertSame(42, $result->userId);
        self::assertSame('oidc-keycloak_1', $result->providerId);
    }

    #[Test]
    public function it_rejects_invalid_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ExternalAuthenticationResult(0, 'provider');
    }
}
