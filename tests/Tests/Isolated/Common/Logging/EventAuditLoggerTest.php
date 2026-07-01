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
use PHPUnit\Framework\Attributes\DataProvider;
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
            }));

        $logger = new EventAuditLogger(
            sink: $sink,
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
            }));

        $logger = new EventAuditLogger(
            sink: $sink,
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

    public function testRecordLogItemConvertsNullPatientIdString(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertNull($event->patientId);
                return true;
            }));

        $logger = new EventAuditLogger(
            sink: $sink,
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

    /**
     * @return array<string, array{
     *   sql: string,
     *   enabled: bool,
     *   forceBreakglass: bool,
     *   isBreakglassUser: bool,
     *   expectLog: bool,
     * }>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function breakglassOverrideProvider(): array
    {
        return [
            'breakglass active: logs despite disabled config' => [
                'sql' => 'SELECT * FROM patient_data WHERE pid = 1',
                'enabled' => false,
                'forceBreakglass' => true,
                'isBreakglassUser' => true,
                'expectLog' => true,
            ],
            'breakglass config off: skips even if user in group' => [
                'sql' => 'SELECT * FROM patient_data WHERE pid = 1',
                'enabled' => false,
                'forceBreakglass' => false,
                'isBreakglassUser' => true,
                'expectLog' => false,
            ],
            'user not in breakglass group: skips even if config on' => [
                'sql' => 'SELECT * FROM patient_data WHERE pid = 1',
                'enabled' => false,
                'forceBreakglass' => true,
                'isBreakglassUser' => false,
                'expectLog' => false,
            ],
            'breakglass does not override select from unknown table' => [
                'sql' => 'SELECT * FROM some_unknown_table',
                'enabled' => false,
                'forceBreakglass' => true,
                'isBreakglassUser' => true,
                'expectLog' => false,
            ],
        ];
    }

    #[DataProvider('breakglassOverrideProvider')]
    public function testAuditSQLEventBreakglassOverride(
        string $sql,
        bool $enabled,
        bool $forceBreakglass,
        bool $isBreakglassUser,
        bool $expectLog,
    ): void {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($expectLog ? $this->once() : $this->never())
            ->method('record');

        $this->session->method('get')
            ->willReturnCallback(fn (string $key) => match ($key) {
                'authUser' => 'testuser',
                'authProvider' => 'default',
                default => null,
            });

        $this->breakglassChecker->method('isBreakglassUser')
            ->willReturn($isBreakglassUser);

        $config = new AuditConfig(
            enabled: $enabled,
            forceBreakglass: $forceBreakglass,
            queryEvents: true,
            httpRequestEvents: false,
            eventTypeFlags: ['patient-record' => true, 'other' => true],
        );

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->auditSQLEvent($sql, true);
    }
}
