<?php

/**
 * Isolated tests for JWT parsing helpers in the External IdP authentication service.
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ExternalIdp;

require_once __DIR__ . '/bootstrap.php';

use OpenEMR\Modules\ExternalIdp\Service\OidcAuthenticationService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class OidcAuthenticationServiceTest extends TestCase
{
    public function testDecodeJwtHeaderParsesAValidHeader(): void
    {
        $service = new OidcAuthenticationService();
        $header = $this->invokePrivate($service, 'decodeJwtHeader', [
            $this->buildJwtHeader(['alg' => 'RS256', 'typ' => 'JWT']),
        ]);

        self::assertSame('RS256', $header['alg']);
        self::assertSame('JWT', $header['typ']);
    }

    public function testDecodeJwtHeaderRejectsMalformedToken(): void
    {
        $service = new OidcAuthenticationService();

        $this->expectException(\RuntimeException::class);
        $this->invokePrivate($service, 'decodeJwtHeader', ['not-a-jwt']);
    }

    private function buildJwtHeader(array $header): string
    {
        return rtrim(strtr(base64_encode(json_encode($header, JSON_THROW_ON_ERROR)), '+/', '-_'), '=') . '.payload.signature';
    }

    /**
     * @param array<int, mixed> $args
     */
    private function invokePrivate(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $methodReflection = $reflection->getMethod($method);

        return $methodReflection->invokeArgs($object, $args);
    }
}
