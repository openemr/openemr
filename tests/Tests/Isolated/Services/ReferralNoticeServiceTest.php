<?php

/**
 * Isolated tests for how a referral's recipient is told about their Task (#15137): the channel their
 * address book entry allows, and what's recorded for staff.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Referral\ReferralDirectNoticeEvent;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\ReferralNoticeOutcome;
use OpenEMR\Services\ReferralNoticeRepository;
use OpenEMR\Services\ReferralNoticeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ReferralNoticeServiceTest extends TestCase
{
    private const TASK = '5f4e3d2c-1b0a-4987-a6b5-c4d3e2f1a0b9';

    /** @var list<array{int, string, ReferralNoticeOutcome, ?string, ?string, ?string, ?int, string}> */
    private array $recorded = [];

    /** @var list<array{string, string, string, string}> */
    private array $emails = [];

    private bool $queueSucceeds = true;

    /** @var ?array{patient_id: int, direct: string, email: string} */
    private ?array $recipient = ['patient_id' => 42, 'direct' => '', 'email' => ''];

    private EventDispatcher $dispatcher;

    private bool $translationBefore = false;

    /**
     * Sets up the collaborators each test uses.
     */
    protected function setUp(): void
    {
        // The notice's texts are translated, and xl() reaches for the translation tables unless this is set.
        $this->translationBefore = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * Puts the translation setting back as the test found it.
     */
    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->translationBefore);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function offProvider(): array
    {
        return ['tracking off' => ['0', '1'], 'notices off' => ['1', '0'], 'both off' => ['0', '0']];
    }

    /**
     * Nobody is told unless both globals are on.
     */
    #[DataProvider('offProvider')]
    public function testNobodyIsToldUnlessBothGlobalsAreOn(string $tracking, string $notify): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => '', 'email' => 'intake@clinic.example'];

        self::assertNull($this->service(tracking: $tracking, notify: $notify)->notify(7, self::TASK, 3));
        self::assertSame([], $this->recorded);
        self::assertSame([], $this->emails);
    }

    /**
     * A referral that names no one tells no one.
     */
    public function testAReferralThatNamesNoOneTellsNoOne(): void
    {
        $this->recipient = null;

        self::assertNull($this->service()->notify(7, self::TASK, 3));
        self::assertSame([], $this->recorded);
    }

    /**
     * A Direct address gets the referral by Direct.
     */
    public function testADirectAddressGetsTheReferralByDirect(): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => 'consult@direct.example', 'email' => 'intake@clinic.example'];
        $seen = [];
        $this->dispatcher->addListener(ReferralDirectNoticeEvent::EVENT_NAME, static function (ReferralDirectNoticeEvent $event) use (&$seen): void {
            $seen[] = [$event->referralId, $event->patientId, $event->directAddress, $event->userId];
            $event->sent('msg-1');
        });

        self::assertSame(ReferralNoticeOutcome::DirectSent, $this->service()->notify(7, self::TASK, 3));

        self::assertSame([[7, 42, 'consult@direct.example', 3]], $seen);
        self::assertSame([[7, self::TASK, ReferralNoticeOutcome::DirectSent, 'consult@direct.example', 'msg-1', null, 3, '2026-11-09 10:00:00']], $this->recorded);
        // Direct comes first: no email as well.
        self::assertSame([], $this->emails);
    }

    /**
     * A Direct notice that could not be sent says why.
     */
    public function testADirectNoticeThatCouldNotBeSentSaysWhy(): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => 'consult@direct.example', 'email' => ''];
        $this->dispatcher->addListener(ReferralDirectNoticeEvent::EVENT_NAME, static function (ReferralDirectNoticeEvent $event): void {
            $event->failed('Recipient not allowed');
        });

        self::assertSame(ReferralNoticeOutcome::DirectFailed, $this->service()->notify(7, self::TASK, 3));
        self::assertSame('Recipient not allowed', $this->recorded[0][5]);
        self::assertNull($this->recorded[0][4]);
    }

    /**
     * A Direct notice queued but not logged is not for staff to resend.
     */
    public function testADirectNoticeQueuedButNotLoggedIsNotForStaffToResend(): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => 'consult@direct.example', 'email' => ''];
        $this->dispatcher->addListener(ReferralDirectNoticeEvent::EVENT_NAME, static function (ReferralDirectNoticeEvent $event): void {
            $event->sentUnlogged();
        });

        self::assertSame(ReferralNoticeOutcome::DirectSentUnlogged, $this->service()->notify(7, self::TASK, 3));
        self::assertFalse(ReferralNoticeOutcome::DirectSentUnlogged->needsStaff());
        self::assertSame([null, null], [$this->recorded[0][4], $this->recorded[0][5]]);
    }

    /**
     * A Direct listener that throws is a failed notice not a failed save.
     */
    public function testADirectListenerThatThrowsIsAFailedNoticeNotAFailedSave(): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => 'consult@direct.example', 'email' => ''];
        $this->dispatcher->addListener(ReferralDirectNoticeEvent::EVENT_NAME, static function (): void {
            throw new \RuntimeException('C-CDA service down');
        });

        self::assertSame(ReferralNoticeOutcome::DirectFailed, $this->service()->notify(7, self::TASK, 3));
        self::assertSame('The Direct notice could not be sent. See the log.', $this->recorded[0][5]);
    }

    /**
     * With nothing to send Direct the recipient is shown as not told.
     */
    public function testWithNothingToSendDirectTheRecipientIsShownAsNotTold(): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => 'consult@direct.example', 'email' => 'intake@clinic.example'];

        self::assertSame(ReferralNoticeOutcome::DirectUnavailable, $this->service()->notify(7, self::TASK, 3));
        self::assertSame(ReferralNoticeOutcome::DirectUnavailable, $this->service(withDispatcher: false)->notify(7, self::TASK, 3));
        self::assertSame([], $this->emails);
        self::assertTrue(ReferralNoticeOutcome::DirectUnavailable->needsStaff());
    }

    /**
     * An email address gets a plain email with no patient details.
     */
    public function testAnEmailAddressGetsAPlainEmailWithNoPatientDetails(): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => '', 'email' => 'intake@clinic.example'];

        self::assertSame(ReferralNoticeOutcome::EmailQueued, $this->service()->notify(7, self::TASK, 3));

        self::assertSame(
            [[
                'front@practice.example',
                'intake@clinic.example',
                'Referral from Zq Family Practice',
                'Zq Family Practice has sent you a referral. This email does not include patient details. Please contact the practice to receive it at 555-0100.',
            ]],
            $this->emails,
        );
        self::assertSame([[7, self::TASK, ReferralNoticeOutcome::EmailQueued, 'intake@clinic.example', null, null, 3, '2026-11-09 10:00:00']], $this->recorded);
    }

    /**
     * @return array<string, array{string, string, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emailFailureProvider(): array
    {
        return [
            'not an address' => ['intake at clinic', 'front@practice.example', true, 'The email address in the address book entry is not valid.'],
            'no sender' => ['intake@clinic.example', ' ', true, 'No sender email address is set for notifications (Notification Email Address).'],
            'not queued' => ['intake@clinic.example', 'front@practice.example', false, 'The email could not be queued.'],
        ];
    }

    /**
     * An email that could not be queued says why.
     */
    #[DataProvider('emailFailureProvider')]
    public function testAnEmailThatCouldNotBeQueuedSaysWhy(string $address, string $sender, bool $queues, string $why): void
    {
        $this->recipient = ['patient_id' => 42, 'direct' => '', 'email' => $address];
        $this->queueSucceeds = $queues;

        self::assertSame(ReferralNoticeOutcome::EmailFailed, $this->service(sender: $sender)->notify(7, self::TASK, 3));
        self::assertSame($why, $this->recorded[0][5]);
    }

    /**
     * No address is recorded for staff.
     */
    public function testNoAddressIsRecordedForStaff(): void
    {
        self::assertSame(ReferralNoticeOutcome::NoAddress, $this->service()->notify(7, self::TASK, null));
        self::assertSame([[7, self::TASK, ReferralNoticeOutcome::NoAddress, null, null, null, null, '2026-11-09 10:00:00']], $this->recorded);
        self::assertSame([], $this->emails);
    }

    /**
     * The service under test, with the recorded collaborators and these globals.
     */
    private function service(string $tracking = '1', string $notify = '1', string $sender = 'front@practice.example', bool $withDispatcher = true): ReferralNoticeService
    {
        $test = $this;
        $notices = new class ($test) extends ReferralNoticeRepository {
            /**
             * Keeps the test, which records what the repository is given.
             */
            public function __construct(private readonly ReferralNoticeServiceTest $test)
            {
            }

            /**
             * Records the notice on the test instead of in the database.
             */
            public function record(
                int $referralId,
                string $taskUuid,
                ReferralNoticeOutcome $outcome,
                ?string $address,
                ?string $messageId,
                ?string $detail,
                ?int $userId,
                \DateTimeImmutable $at,
            ): void {
                $this->test->recordNotice([$referralId, $taskUuid, $outcome, $address, $messageId, $detail, $userId, $at->format('Y-m-d H:i:s')]);
            }

            /**
             * The recipient the test set.
             */
            public function recipientAddresses(int $referralId): ?array
            {
                return $this->test->recipient();
            }
        };
        return new ReferralNoticeService(
            notices: $notices,
            dispatcher: $withDispatcher ? $this->dispatcher : null,
            queueEmail: function (string $from, string $to, string $subject, string $body): bool {
                $this->emails[] = [$from, $to, $subject, $body];
                return $this->queueSucceeds;
            },
            practice: static fn(): array => ['name' => 'Zq Family Practice', 'phone' => '555-0100'],
            globals: new OEGlobalsBag([
                GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value => $tracking,
                GlobalConnectorsEnum::FHIR_REFERRAL_NOTIFY_RECIPIENT->value => $notify,
                ReferralNoticeService::SENDER_GLOBAL => $sender,
            ]),
            clock: new FrozenClock(new \DateTimeImmutable('2026-11-09 10:00:00')),
            logger: new NullLogger(),
        );
    }

    /**
     * @param array{int, string, ReferralNoticeOutcome, ?string, ?string, ?string, ?int, string} $row
     */
    public function recordNotice(array $row): void
    {
        $this->recorded[] = $row;
    }

    /**
     * @return ?array{patient_id: int, direct: string, email: string}
     */
    public function recipient(): ?array
    {
        return $this->recipient;
    }
}
