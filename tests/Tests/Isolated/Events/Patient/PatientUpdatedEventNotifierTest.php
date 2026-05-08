<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events\Patient;

use OpenEMR\Events\Patient\PatientUpdatedEvent;
use OpenEMR\Events\Patient\PatientUpdatedEventNotifier;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PatientUpdatedEventNotifierTest extends TestCase
{
    public function testDispatchesPatientUpdatedEventWithBeforeAndAfter(): void
    {
        $before = ['pid' => 42, 'fname' => 'Test', 'hipaa_allowsms' => 'NO'];
        $after = ['pid' => 42, 'fname' => 'Test', 'hipaa_allowsms' => 'YES'];

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(static fn($event): bool => $event instanceof PatientUpdatedEvent
                    && $event->getDataBeforeUpdate() === $before
                    && $event->getNewPatientData() === $after),
                PatientUpdatedEvent::EVENT_HANDLE,
            );
        $logger->expects($this->never())->method('error');

        (new PatientUpdatedEventNotifier($dispatcher, $logger))->notify(42, $before, $after);
    }

    public function testLogsErrorAndSkipsDispatchWhenPreUpdateRowMissing(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $dispatcher->expects($this->never())->method('dispatch');
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('PatientUpdatedEvent skipped'),
                ['pid' => 99],
            );

        (new PatientUpdatedEventNotifier($dispatcher, $logger))->notify(99, false, ['pid' => 99]);
    }
}
