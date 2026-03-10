<?php

/**
 * Tests for AtnaSink
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use DateTimeImmutable;
use OpenEMR\Common\Logging\Audit\Atna\WriterInterface;
use OpenEMR\Common\Logging\Audit\AtnaSink;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Clock\ClockInterface;

class AtnaSinkTest extends TestCase
{
    private ClockInterface&MockObject $clock;

    protected function setUp(): void
    {
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('2026-03-10T12:00:00+00:00'));
    }

    public function testRecordDoesNothingWhenDisabled(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->never())->method('writeMessage');

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: false,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('testuser', 'testgroup', 'login', 0, 1, 'Test login');
    }

    public function testRecordDoesNothingWhenHostIsEmpty(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->never())->method('writeMessage');

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: '',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('testuser', 'testgroup', 'login', 0, 1, 'Test login');
    }

    public function testRecordCallsWriterWhenEnabled(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message): bool {
                self::assertStringContainsString('AuditMessage', $message);
                self::assertStringContainsString('testuser', $message);
                self::assertStringContainsString('Login', $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('testuser', 'testgroup', 'login', 0, 1, 'Test login');
    }

    /**
     * @dataProvider eventActionCodeProvider
     */
    public function testEventActionCodesAreCorrect(string $event, string $expectedCode): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message) use ($expectedCode): bool {
                self::assertStringContainsString("EventActionCode=\"{$expectedCode}\"", $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('user', 'group', $event, 0, 1, 'comment');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function eventActionCodeProvider(): array
    {
        return [
            'create' => ['patient-record-create', 'C'],
            'insert' => ['patient-record-insert', 'C'],
            'select' => ['patient-record-select', 'R'],
            'update' => ['patient-record-update', 'U'],
            'delete' => ['patient-record-delete', 'D'],
            'execute (default)' => ['login', 'E'],
        ];
    }

    /**
     * @dataProvider eventDisplayNameProvider
     */
    public function testEventDisplayNamesAreCorrect(string $event, string $expectedDisplayName): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message) use ($expectedDisplayName): bool {
                self::assertStringContainsString("displayName=\"{$expectedDisplayName}\"", $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('user', 'group', $event, 0, 1, 'comment');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function eventDisplayNameProvider(): array
    {
        return [
            'patient-record' => ['patient-record-select', 'Patient Record'],
            'view' => ['view-encounter', 'Patient Record'],
            'login' => ['login', 'Login'],
            'logout' => ['logout', 'Logout'],
            'scheduling' => ['scheduling-update', 'Patient Care Assignment'],
            'security-administration' => ['security-administration-insert', 'Security Administration'],
            'other (passthrough)' => ['custom-event', 'custom-event'],
        ];
    }

    public function testSuccessOutcomeIs0(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message): bool {
                self::assertStringContainsString('EventOutcomeIndicator="0"', $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('user', 'group', 'login', 0, 1, 'comment');
    }

    public function testFailureOutcomeIs4(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message): bool {
                self::assertStringContainsString('EventOutcomeIndicator="4"', $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('user', 'group', 'login', 0, 0, 'comment');
    }

    public function testPatientIdIncludedForPatientRecordEvents(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message): bool {
                self::assertStringContainsString('ParticipantObjectID="12345"', $message);
                self::assertStringContainsString('Patient Number', $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('user', 'group', 'patient-record-select', 12345, 1, 'comment');
    }

    public function testPatientIdNotIncludedWhenZero(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->once())
            ->method('writeMessage')
            ->with(self::callback(function (string $message): bool {
                self::assertStringNotContainsString('Patient Number', $message);
                return true;
            }));

        $sink = new AtnaSink(
            clock: $this->clock,
            writer: $writer,
            enabled: true,
            host: 'audit.example.com',
            serverName: 'emr.example.com',
            serverAddress: '192.168.1.1',
        );

        $sink->record('user', 'group', 'patient-record-select', 0, 1, 'comment');
    }
}
