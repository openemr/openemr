<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Event;

use OpenEMR\Common\Auth\Oidc\Event\OidcTokenReceivedEvent;
use OpenEMR\Common\Auth\Oidc\Identity\ClaimMapperInterface;
use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OidcTokenReceivedEvent::class)]
final class OidcTokenReceivedEventTest extends TestCase
{
    public function testExposesTokenAndIssuer(): void
    {
        $event = new OidcTokenReceivedEvent('raw-jwt-string', 'https://accounts.example.com');

        self::assertSame('raw-jwt-string', $event->getRawToken());
        self::assertSame('https://accounts.example.com', $event->getIssuer());
    }

    public function testIsNotRejectedByDefault(): void
    {
        $event = new OidcTokenReceivedEvent('token', 'issuer');

        self::assertFalse($event->isRejected());
        self::assertSame('', $event->getRejectionReason());
    }

    public function testRejectMarksEventAsRejected(): void
    {
        $event = new OidcTokenReceivedEvent('token', 'issuer');

        $event->reject('Unknown tenant');

        self::assertTrue($event->isRejected());
        self::assertSame('Unknown tenant', $event->getRejectionReason());
    }

    public function testNoClaimMapperByDefault(): void
    {
        $event = new OidcTokenReceivedEvent('token', 'issuer');

        self::assertNull($event->getClaimMapper());
    }

    public function testSetClaimMapper(): void
    {
        $event = new OidcTokenReceivedEvent('token', 'issuer');
        $mapper = new class implements ClaimMapperInterface {
            public function map(array $claims): NormalizedIdentity
            {
                return new NormalizedIdentity('sub', 'iss', 'email', false, 'name');
            }

            public function supports(array $claims): bool
            {
                return true;
            }
        };

        $event->setClaimMapper($mapper);

        self::assertSame($mapper, $event->getClaimMapper());
    }

    public function testHasEventNameConstant(): void
    {
        self::assertSame('oidc.token.received', OidcTokenReceivedEvent::EVENT_NAME);
    }
}
