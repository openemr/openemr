<?php

/**
 * Isolated tests for EventAuditLogger::logAuthFailure().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aanand Sreekumaran Nair Jayakumari
 * @copyright Copyright (c) 2026 Aanand Sreekumaran Nair Jayakumari
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Auth\AuthEvent;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\SinkInterface;
use OpenEMR\Common\Logging\AuditConfig;
use OpenEMR\Common\Logging\BreakglassCheckerInterface;
use OpenEMR\Common\Logging\EventAuditLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EventAuditLoggerAuthFailureTest extends TestCase
{
    private CryptoInterface&MockObject $crypto;
    private SessionInterface&MockObject $session;
    private AuditConfig $config;
    private BreakglassCheckerInterface&MockObject $breakglassChecker;

    protected function setUp(): void
    {
        $this->crypto = $this->createMock(CryptoInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->config = new AuditConfig(
            enabled: true,
            forceBreakglass: false,
            queryEvents: true,
            httpRequestEvents: true,
            eventTypeFlags: [],
        );
        $this->breakglassChecker = $this->createMock(BreakglassCheckerInterface::class);

        // Set a known REMOTE_ADDR so comment assertions are deterministic.
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    private function makeLogger(SinkInterface $sink): EventAuditLogger
    {
        return new EventAuditLogger(
            sinks: [$sink],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: new FrozenClock(new \DateTimeImmutable('2026-01-15 10:30:00')),
        );
    }

    public function testLogAuthFailurePassesEventValueToSink(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('mfa', $event->event);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'testuser',
            'Administrators',
            'TOTP code incorrect',
        );
    }

    public function testLogAuthFailureAlwaysRecordsSuccess0(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame(0, $event->success);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'testuser',
            'Administrators',
            'TOTP code incorrect',
        );
    }

    public function testLogAuthFailureCommentsContainIpAndReason(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                // Comments are base64-encoded by recordLogItem when encryption is off.
                $decoded = base64_decode($event->comments);
                self::assertStringContainsString('127.0.0.1', $decoded);
                self::assertStringContainsString('TOTP code incorrect', $decoded);
                self::assertStringStartsWith('failure:', $decoded);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'testuser',
            'Administrators',
            'TOTP code incorrect',
        );
    }

    public function testLogAuthFailurePassesUsernameAndGroup(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('drsmith', $event->user);
                self::assertSame('physicians', $event->group);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'drsmith',
            'physicians',
            'U2F authentication error',
        );
    }

    public function testLogAuthFailureNullUsernameBecomesEmptyString(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('', $event->user);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            null,
            '',
            'OAuth2 MFA (TOTP) code incorrect',
        );
    }

    public function testLogAuthFailurePatientIdIsNullByDefault(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertNull($event->patientId);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'testuser',
            'Administrators',
            'TOTP code incorrect',
        );
    }

    public function testLogAuthFailurePatientIdForwardedToSink(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame(42, $event->patientId);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'portaluser',
            'portal',
            'Portal MFA failure',
            42,
        );
    }

    public function testLogAuthFailureCommentsIncludeForwardedIp(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';

        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                $decoded = base64_decode($event->comments);
                self::assertStringContainsString('10.0.0.1', $decoded);
                return true;
            }))
            ->willReturn(true);

        $this->makeLogger($sink)->logAuthFailure(
            AuthEvent::mfa(),
            'testuser',
            'Administrators',
            'TOTP code incorrect',
        );
    }
}
