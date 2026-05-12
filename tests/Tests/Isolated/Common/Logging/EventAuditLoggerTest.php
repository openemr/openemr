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
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\SinkInterface;
use OpenEMR\Common\Logging\AuditConfig;
use OpenEMR\Common\Logging\BreakglassCheckerInterface;
use OpenEMR\Common\Logging\EventAuditLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EventAuditLoggerTest extends TestCase
{
    private SessionInterface&MockObject $session;
    private AuditConfig $config;
    private BreakglassCheckerInterface&MockObject $breakglassChecker;
    private ClockInterface $clock;

    protected function setUp(): void
    {
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

    public function testRecordLogItemBase64EncodesComments(): void
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
}
