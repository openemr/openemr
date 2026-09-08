<?php

/**
 * Tests for MultiSink
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\MultiSink;
use OpenEMR\Common\Logging\Audit\SinkInterface;
use PHPUnit\Framework\TestCase;

class MultiSinkTest extends TestCase
{
    private function createEvent(): Event
    {
        return new Event(
            current_datetime: '2026-06-26 12:00:00',
            event: 'test-event',
            category: 'test-category',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comment',
            user_notes: '',
            patientId: 123,
            success: 1,
            logFrom: 'open-emr',
            menuItemId: null,
            ccdaDocId: null,
            api: null,
        );
    }

    public function testRecordWithSingleSinkDispatchesToIt(): void
    {
        $event = $this->createEvent();

        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with($event);

        $multiSink = new MultiSink([$sink]);
        $multiSink->record($event);
    }

    public function testRecordDispatchesToAllSinks(): void
    {
        $event = $this->createEvent();

        $sink1 = $this->createMock(SinkInterface::class);
        $sink1->expects($this->once())
            ->method('record')
            ->with($event);

        $sink2 = $this->createMock(SinkInterface::class);
        $sink2->expects($this->once())
            ->method('record')
            ->with($event);

        $sink3 = $this->createMock(SinkInterface::class);
        $sink3->expects($this->once())
            ->method('record')
            ->with($event);

        $multiSink = new MultiSink([$sink1, $sink2, $sink3]);
        $multiSink->record($event);
    }

    public function testRecordWithEmptySinksArrayDoesNotError(): void
    {
        // This test is implicitly checking no exceptions are thrown
        $this->expectNotToPerformAssertions();

        $multiSink = new MultiSink([]);
        $multiSink->record($this->createEvent());
    }

    public function testRecordPropagatesExceptionsFromSink(): void
    {
        // Note: we _may_ want to change this behavior to try dispatching to
        // all, log all errors, and throw the last one at the end. This was the
        // previous behavior.
        $event = $this->createEvent();

        $throwingSink = $this->createMock(SinkInterface::class);
        $throwingSink->expects($this->once())
            ->method('record')
            ->willThrowException(new \RuntimeException('Sink failed'));

        $neverCalledSink = $this->createMock(SinkInterface::class);
        $neverCalledSink->expects($this->never())
            ->method('record');

        $multiSink = new MultiSink([$throwingSink, $neverCalledSink]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sink failed');
        $multiSink->record($event);
    }

    public function testRecordPassesSameEventToAllSinks(): void
    {
        $event = $this->createEvent();

        $sink1 = $this->createMock(SinkInterface::class);
        $sink1->expects($this->once())
            ->method('record')
            ->willReturnCallback(function (Event $e) use ($event): void {
                self::assertSame($event, $e, 'Expects original instance');
            });

        $sink2 = $this->createMock(SinkInterface::class);
        $sink2->expects($this->once())
            ->method('record')
            ->willReturnCallback(function (Event $e) use ($event): void {
                self::assertSame($event, $e, 'Expects original instance');
            });

        $multiSink = new MultiSink([$sink1, $sink2]);
        $multiSink->record($event);
    }
}
