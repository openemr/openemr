<?php

/**
 * The Carecoordination module sends a referral's recipient its Referral C-CDA by Direct when core
 * asks (#15137), and says how it went.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\Carecoordination\Listener;

use Carecoordination\Listener\ReferralNoticeSubscriber;
use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\CcdaServiceConnectionException;
use Carecoordination\Model\GeneratedCcdaResult;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\PhiMailAttachmentType;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingMessage;
use OpenEMR\Common\DirectMessaging\PhiMailRequester;
use OpenEMR\Common\DirectMessaging\PhiMailSendResult;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Referral\ReferralDirectNoticeEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ReferralNoticeSubscriberTest extends TestCase
{
    /** No patient has this id, so the message names none. */
    private const PATIENT = 990081;

    private CcdaGenerator&MockObject $generator;

    /** @var list<PhiMailOutgoingMessage> */
    private array $sent = [];

    private PhiMailSendResult $result;

    private ?SqlQueryException $nameFailure = null;

    /**
     * Sets up the collaborators each test uses.
     */
    protected function setUp(): void
    {
        $this->generator = $this->createMock(CcdaGenerator::class);
        $this->result = PhiMailSendResult::sent('msg-1');
    }

    /**
     * Listens for the notice.
     */
    public function testListensForTheNotice(): void
    {
        self::assertSame([ReferralDirectNoticeEvent::EVENT_NAME => 'onReferralDirectNotice'], ReferralNoticeSubscriber::getSubscribedEvents());
    }

    /**
     * Sends the referral's own C-CDA with the referral sections only.
     */
    public function testSendsTheReferralsOwnCcdaWithTheReferralSectionsOnly(): void
    {
        $this->generator->expects(self::once())->method('generate')
            ->with(
                self::PATIENT,
                null,
                '',
                false,
                false,
                false,
                'allergies|medications|problems|plan_of_care|referral',
                'continuity_care_document',
                '',
                [],
                'referral',
                '',
                [],
                7,
            )
            ->willReturn(new GeneratedCcdaResult(1, 'uuid', 'Referral Doe Jane.xml', '<ClinicalDocument/>'));
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        self::assertSame('msg-1', $event->messageId());
        self::assertCount(1, $this->sent);
        $message = $this->sent[0];
        self::assertSame(['consult@direct.example', self::PATIENT, 7, false], [$message->recipient, $message->patientId, $message->referralId, $message->verifyFinalDelivery]);
        self::assertSame('A referral is attached.', $message->text);
        self::assertNotNull($message->attachment);
        self::assertSame(
            [PhiMailAttachmentType::Cda, 'Referral_Doe_Jane.xml', '<ClinicalDocument/>'],
            [$message->attachment->type, $message->attachment->fileName, $message->attachment->content],
        );
    }

    /**
     * A send that fails says why.
     */
    public function testASendThatFailsSaysWhy(): void
    {
        $this->generator->method('generate')->willReturn(new GeneratedCcdaResult(1, 'uuid', 'referral.xml', '<ClinicalDocument/>'));
        $this->result = PhiMailSendResult::notSent('Recipient not allowed');
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        self::assertNull($event->messageId());
        self::assertSame('Recipient not allowed', $event->failure());
    }

    /**
     * A send queued but not logged is reported as sent.
     */
    public function testASendQueuedButNotLoggedIsReportedAsSent(): void
    {
        $this->generator->method('generate')->willReturn(new GeneratedCcdaResult(1, 'uuid', 'referral.xml', '<ClinicalDocument/>'));
        $this->result = PhiMailSendResult::sentUnlogged('msg-1');
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        // Reported unlogged only because the send itself ran, and it was the log that failed afterwards.
        self::assertCount(1, $this->sent);
        self::assertTrue($event->wasSentUnlogged());
        self::assertNull($event->failure());
        self::assertTrue($event->wasHandled());
    }

    /**
     * A database error while the message is put together, before anything is sent, is a notice not
     * sent, not one sent but unlogged.
     */
    public function testAFailureBeforeTheSendIsANoticeNotSent(): void
    {
        $this->generator->method('generate')->willReturn(new GeneratedCcdaResult(1, 'uuid', 'referral.xml', '<ClinicalDocument/>'));
        $this->nameFailure = new SqlQueryException(sqlStatement: 'SELECT fname, lname FROM patient_data', message: 'database is down');
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        self::assertSame([], $this->sent);
        self::assertFalse($event->wasSentUnlogged());
        self::assertSame('The Direct notice could not be prepared. See the log.', $event->failure());
    }

    /**
     * A C-CDA that can not be made is not sent.
     */
    public function testACcdaThatCanNotBeMadeIsNotSent(): void
    {
        $this->generator->method('generate')->willThrowException(new \RuntimeException('no template'));
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        self::assertSame([], $this->sent);
        self::assertSame('The Referral C-CDA could not be made. See the log.', $event->failure());
    }

    /**
     * A C-CDA service that is turned off or can not be reached fails the notice, the same way.
     */
    public function testACcdaServiceThatCanNotBeReachedIsANoticeNotSent(): void
    {
        $this->generator->method('generate')->willThrowException(new CcdaServiceConnectionException('service is down'));
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        self::assertSame([], $this->sent);
        self::assertSame('The Referral C-CDA could not be made. See the log.', $event->failure());
    }

    /**
     * An error in place of the C-CDA is not sent.
     */
    public function testAnErrorInPlaceOfTheCcdaIsNotSent(): void
    {
        $this->generator->method('generate')->willReturn(new GeneratedCcdaResult(1, 'uuid', 'referral.xml', 'ERROR: no data'));
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber()->onReferralDirectNotice($event);

        self::assertSame([], $this->sent);
        self::assertNotNull($event->failure());
    }

    /**
     * With Direct off the notice is left for core to report.
     */
    public function testWithDirectOffTheNoticeIsLeftForCoreToReport(): void
    {
        $this->generator->expects(self::never())->method('generate');
        $event = new ReferralDirectNoticeEvent(7, self::PATIENT, 'consult@direct.example', 3);

        $this->subscriber(directEnabled: false)->onReferralDirectNotice($event);

        self::assertFalse($event->wasHandled());
    }

    /**
     * The listener under test, with a recording sender.
     */
    private function subscriber(bool $directEnabled = true): ReferralNoticeSubscriber
    {
        return new ReferralNoticeSubscriber(
            generator: $this->generator,
            deliver: function (PhiMailOutgoingMessage $message, PhiMailRequester $requester): PhiMailSendResult {
                $this->sent[] = $message;
                return $this->result;
            },
            requester: static fn(): PhiMailRequester => new PhiMailRequester('admin', 'Default', 3),
            globals: new OEGlobalsBag(['phimail_enable' => $directEnabled ? '1' : '0', 'phimail_verifyrecipientreceived_enable' => '0']),
            logger: new NullLogger(),
            patientName: function (int $patientId): string {
                if ($this->nameFailure !== null) {
                    throw $this->nameFailure;
                }
                return '';
            },
        );
    }
}
