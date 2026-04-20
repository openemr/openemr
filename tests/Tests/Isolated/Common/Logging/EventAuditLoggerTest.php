<?php

/**
 * Isolated tests for EventAuditLogger sink dispatch
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\SinkInterface;
use OpenEMR\Common\Logging\AuditConfig;
use OpenEMR\Common\Logging\BreakglassCheckerInterface;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Encryption\CipherSuiteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EventAuditLoggerTest extends TestCase
{
    private CryptoInterface&MockObject $crypto;
    private SessionInterface&MockObject $session;
    private AuditConfig $config;
    private BreakglassCheckerInterface&MockObject $breakglassChecker;
    private ClockInterface $clock;

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
        $this->clock = new FrozenClock(new \DateTimeImmutable('2026-01-15 10:30:00'));
    }

    public function testRecordLogItemDispatchesToAllSinks(): void
    {
        $sink1 = $this->createMock(SinkInterface::class);
        $sink1->expects($this->once())
            ->method('record')
            ->with(self::isInstanceOf(Event::class))
            ->willReturn(true);

        $sink2 = $this->createMock(SinkInterface::class);
        $sink2->expects($this->once())
            ->method('record')
            ->with(self::isInstanceOf(Event::class))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink1, $sink2],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'test-event',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comments',
        );
    }

    public function testRecordLogItemPassesCorrectEventData(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('patient-record-select', $event->event);
                self::assertSame('drsmith', $event->user);
                self::assertSame('physicians', $event->group);
                self::assertSame(12345, $event->patientId);
                self::assertSame(1, $event->success);
                self::assertSame('patient-record', $event->category);
                return true;
            }))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'patient-record-select',
            user: 'drsmith',
            group: 'physicians',
            comments: 'Viewed patient record',
            patientId: 12345,
            category: 'patient-record',
        );
    }

    public function testRecordLogItemEncryptsCommentsWhenEnabled(): void
    {
        $this->crypto->expects($this->once())
            ->method('encryptStandard')
            ->with('Sensitive data')
            ->willReturn('encrypted:Sensitive data');

        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('encrypted:Sensitive data', $event->comments);
                return true;
            }))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink],
            crypto: $this->crypto,
            shouldEncrypt: true,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'login',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Sensitive data',
        );
    }

    public function testRecordLogItemBase64EncodesCommentsWhenNotEncrypted(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame(base64_encode('Plain text'), $event->comments);
                return true;
            }))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'login',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Plain text',
        );
    }

    public function testRecordLogItemWithNoSinksDoesNotError(): void
    {
        $logger = new EventAuditLogger(
            sinks: [],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'test-event',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comments',
        );

        // Test passes if no exception is thrown
        $this->addToAssertionCount(1);
    }

    public function testRecordLogItemContinuesIfSinkFails(): void
    {
        $failingSink = $this->createMock(SinkInterface::class);
        $failingSink->expects($this->once())
            ->method('record')
            ->willReturn(false);

        $successSink = $this->createMock(SinkInterface::class);
        $successSink->expects($this->once())
            ->method('record')
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$failingSink, $successSink],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'test-event',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comments',
        );
    }

    public function testRecordLogItemEncryptsApiDataWhenEnabled(): void
    {
        $this->crypto->expects($this->exactly(4))
            ->method('encryptStandard')
            ->willReturnCallback(fn(string $value): string => 'encrypted:' . $value);

        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertNotNull($event->api);
                self::assertSame('encrypted:https://api.example.com/patient', $event->api['request_url']);
                self::assertSame('encrypted:{"id":123}', $event->api['request_body']);
                self::assertSame('encrypted:{"status":"ok"}', $event->api['response']);
                return true;
            }))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink],
            crypto: $this->crypto,
            shouldEncrypt: true,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'api-create',
            user: 'apiuser',
            group: 'api',
            comments: 'API call',
            api: [
                'user_id' => 1,
                'patient_id' => 123,
                'method' => 'POST',
                'request' => 'create patient',
                'request_url' => 'https://api.example.com/patient',
                'request_body' => '{"id":123}',
                'response' => '{"status":"ok"}',
            ],
        );
    }

    public function testRecordLogItemConvertsNullPatientIdString(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertNull($event->patientId);
                return true;
            }))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink],
            crypto: $this->crypto,
            shouldEncrypt: false,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        // Legacy code sometimes passes "NULL" as a string
        $logger->recordLogItem(
            success: 1,
            event: 'test-event',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comments',
            patientId: 'NULL',
        );
    }

    public function testRecordLogItemEncryptsCommentsViaCipherSuite(): void
    {
        $cipherSuite = $this->createMock(CipherSuiteInterface::class);
        $cipherSuite->expects($this->once())
            ->method('encrypt')
            ->with('Sensitive data')
            ->willReturn('ciphersuite-encrypted:Sensitive data');

        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('ciphersuite-encrypted:Sensitive data', $event->comments);
                return true;
            }))
            ->willReturn(true);

        $logger = new EventAuditLogger(
            sinks: [$sink],
            crypto: $cipherSuite,
            shouldEncrypt: true,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'login',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Sensitive data',
        );
    }
}
