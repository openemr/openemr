<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events\Core;

use OpenEMR\Events\Core\ExternalAuthenticationProviderEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExternalAuthenticationProviderEventTest extends TestCase
{
    #[Test]
    public function it_collects_unique_login_providers(): void
    {
        $event = new ExternalAuthenticationProviderEvent();
        $event->addProvider('keycloak', 'Sign in with Keycloak', '/external-idp/start');

        self::assertSame([
            ['id' => 'keycloak', 'label' => 'Sign in with Keycloak', 'loginUrl' => '/external-idp/start'],
        ], $event->getProviders());
    }

    #[Test]
    public function it_rejects_duplicate_provider_ids(): void
    {
        $event = new ExternalAuthenticationProviderEvent();
        $event->addProvider('keycloak', 'Sign in with Keycloak', '/external-idp/start');

        $this->expectException(\InvalidArgumentException::class);
        $event->addProvider('keycloak', 'Another Keycloak', '/another-start');
    }
}
