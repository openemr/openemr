<?php

/**
 * Isolated tests for trusted-user scope generation in the External IdP
 * provisioning service.
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ExternalIdp;

require_once __DIR__ . '/bootstrap.php';

use OpenEMR\Modules\ExternalIdp\Service\OidcProvisioningService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class OidcProvisioningServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'http://example.test';
    }

    public function testBuildTrustedUserScopesIncludesApiAndUserWriteScopes(): void
    {
        $service = new OidcProvisioningService();

        $scopes = $this->invokePrivate($service, 'buildTrustedUserScopes');

        self::assertContains('openid', $scopes);
        self::assertContains('fhirUser', $scopes);
        self::assertContains('api:oemr', $scopes);
        self::assertContains('api:fhir', $scopes);
        self::assertContains('user/patient.write', $scopes);
        self::assertContains('user/patient.crus', $scopes);
    }

    private function invokePrivate(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $methodReflection = $reflection->getMethod($method);

        return $methodReflection->invokeArgs($object, $args);
    }
}
