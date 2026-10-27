<?php

/**
 * Isolated tests for the event a statement run fires for each encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events\Billing;

use OpenEMR\Events\Billing\StatementEncounterEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class StatementEncounterEventTest extends TestCase
{
    public function testCarriesTheEncounterTheLinesAndTheRecipient(): void
    {
        $line = ['dos' => '2026-08-01', 'desc' => 'Procedure 99212', 'amount' => '100.00'];
        $event = new StatementEncounterEvent(7, 12, '2026-08-01', 'high', 2, [$line], '100.00', ['Pat Parent', 'Barre, VT 05641'], false, ['pid' => 7]);

        $this->assertSame(7, $event->getPid());
        $this->assertSame(12, $event->getEncounter());
        $this->assertSame('2026-08-01', $event->getServiceDate());
        $this->assertSame('high', $event->getSensitivity());
        $this->assertSame(2, $event->getNotice());
        $this->assertSame([$line], $event->getLines());
        $this->assertSame('100.00', $event->getBalance());
        $this->assertSame(['Pat Parent', 'Barre, VT 05641'], $event->getRecipientLines());
        $this->assertFalse($event->isAddressedToPatient());
        $this->assertSame(['pid' => 7], $event->getStatement());
        $this->assertFalse($event->isExcluded());
    }

    public function testAListenerCanLeaveTheEncounterOff(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(StatementEncounterEvent::EVENT_NAME, function (StatementEncounterEvent $event): void {
            if (!$event->isAddressedToPatient()) {
                $event->exclude();
            }
        });

        $toGuardian = new StatementEncounterEvent(7, 12, '2026-08-01', '', 1, [], '0.00', ['Pat Parent'], false, []);
        $toPatient = new StatementEncounterEvent(7, 12, '2026-08-01', '', 1, [], '0.00', ['Ann Patient'], true, []);
        $dispatcher->dispatch($toGuardian, StatementEncounterEvent::EVENT_NAME);
        $dispatcher->dispatch($toPatient, StatementEncounterEvent::EVENT_NAME);

        $this->assertTrue($toGuardian->isExcluded());
        $this->assertFalse($toPatient->isExcluded());
    }
}
