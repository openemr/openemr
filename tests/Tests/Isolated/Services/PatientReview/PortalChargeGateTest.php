<?php

/**
 * The portal charge gate: a second gateway charge is refused, under the patient's payment
 * lock, while a charged payment is still open.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use DateTimeImmutable;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientReview\Portal\ChargeRefusal;
use OpenEMR\Services\PatientReview\Portal\PortalChargeGate;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;

final class PortalChargeGateTest extends TestCase
{
    private const PID = 7;

    private PortalReviewWorkflows $workflows;
    private PortalChargeGate $gate;

    protected function setUp(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('2026-10-01 09:30:00');
            }
        };
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->method('encryptForDatabase')->willReturnCallback(static fn(?string $value): string => (string) $value);
        $queue = new ReviewQueueService(new InMemoryReviewRequestStore(), new RecordingReviewAuditTrail(), $clock, new UuidFactory(), $crypto, new NullLogger());
        $this->workflows = new PortalReviewWorkflows($queue, new NullLogger());
        $this->gate = new PortalChargeGate($this->workflows);
    }

    private function lock(bool $busy = false): FakePaymentLock
    {
        return new FakePaymentLock($busy);
    }

    public function testAFirstChargeGoesAheadWithTheLockHeld(): void
    {
        $lock = $this->lock();

        $this->assertNull($this->gate->open((string) self::PID, $lock));
        $this->assertSame(['acquire 7'], $lock->calls, 'held for the charge, released when the request ends');
    }

    public function testASecondChargeIsRefusedWhileAChargedPaymentIsOpen(): void
    {
        $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', '{"transId":"T1"}', true, ReviewActor::patient(self::PID, 'ann'));
        $lock = $this->lock();

        $this->assertSame(ChargeRefusal::ChargedPaymentOpen, $this->gate->open(self::PID, $lock));
        $this->assertSame(['acquire 7', 'release'], $lock->calls, 'checked under the lock, then let go');
    }

    public function testAnUnchargedPendingPaymentDoesNotBlockACharge(): void
    {
        $card = '{"cardHolderName":"Ann Lee","cardNumber":"4111111111111111","month":"12","year":"2030","cardCode":"123"}';
        $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', $card, false, ReviewActor::patient(self::PID, 'ann'));

        $this->assertNull($this->gate->open(self::PID, $this->lock()));
    }

    public function testABusyLockRefusesTheCharge(): void
    {
        $this->assertSame(ChargeRefusal::PaymentInProgress, $this->gate->open(self::PID, $this->lock(busy: true)));
    }

    /**
     * A missing or invalid patient id is refused before the lock is taken.
     */
    public function testNoPatientNoCharge(): void
    {
        $lock = $this->lock();

        foreach (['', '0', 'abc', null, -3] as $pid) {
            $this->assertSame(ChargeRefusal::NoPatient, $this->gate->open($pid, $lock));
        }
        $this->assertSame([], $lock->calls, 'the lock is not touched');
    }

    /**
     * Every refusal tells the patient something.
     */
    public function testEachRefusalHasAMessage(): void
    {
        foreach (ChargeRefusal::cases() as $refusal) {
            $this->assertNotSame('', $refusal->message());
        }
    }
}
