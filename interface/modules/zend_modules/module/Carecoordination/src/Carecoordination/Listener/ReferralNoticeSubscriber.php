<?php

/**
 * Sends a referral's recipient its Referral C-CDA by Direct when core asks (#15137): the notice that a
 * referral Task was created for them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Carecoordination\Listener;

use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\CcdaServiceConnectionException;
use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\PhiMailAttachmentType;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingAttachment;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingMessage;
use OpenEMR\Common\DirectMessaging\PhiMailRequester;
use OpenEMR\Common\DirectMessaging\PhiMailSender;
use OpenEMR\Common\DirectMessaging\PhiMailSendResult;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Referral\ReferralDirectNoticeEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ReferralNoticeSubscriber implements EventSubscriberInterface
{
    /**
     * The Referral Note's required sections, not the whole chart: allergies, medications, problems,
     * plan of care and the reason for referral.
     */
    public const COMPONENTS = 'allergies|medications|problems|plan_of_care|referral';

    /** The body built from COMPONENTS alone, without the other document templates. */
    public const SECTIONS = 'continuity_care_document';

    public const DOCUMENT_TYPE = 'referral';

    /**
     * @param ?\Closure(PhiMailOutgoingMessage, PhiMailRequester): PhiMailSendResult $deliver sends the message;
     *     PhiMailSender::fromGlobals()->deliver() when null
     * @param ?\Closure(): PhiMailRequester $requester who the send is audited as; the signed-in user when null
     * @param ?\Closure(int): string $patientName the patient's name for the message's text, by pid;
     *     patient_data's first and last name when null
     */
    public function __construct(
        private CcdaGenerator $generator,
        private ?\Closure $deliver = null,
        private ?\Closure $requester = null,
        private ?OEGlobalsBag $globals = null,
        private ?LoggerInterface $logger = null,
        private ?\Closure $patientName = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ReferralDirectNoticeEvent::EVENT_NAME => 'onReferralDirectNotice'];
    }

    /**
     * Makes the referral's C-CDA and sends it to the recipient's Direct address. With Direct messaging
     * turned off the event is left unhandled, which tells staff Direct isn't available.
     */
    public function onReferralDirectNotice(ReferralDirectNoticeEvent $event): void
    {
        $globals = $this->globals ?? OEGlobalsBag::getInstance();
        if (!$globals->getBoolean('phimail_enable')) {
            return;
        }
        try {
            $ccda = $this->generator->generate(
                $event->patientId,
                null,
                '',
                false,
                false,
                false,
                self::COMPONENTS,
                self::SECTIONS,
                '',
                [],
                self::DOCUMENT_TYPE,
                '',
                [],
                $event->referralId
            );
        } catch (\RuntimeException | \LogicException | DbalException | CcdaServiceConnectionException $e) {
            $this->logger()->error('The Referral C-CDA for a referral\'s Direct notice could not be made', [
                'transaction_id' => $event->referralId,
                'exception' => $e,
            ]);
            $event->failed(xl('The Referral C-CDA could not be made. See the log.'));
            return;
        }
        $content = $ccda->getContent();
        if ($content === '' || str_starts_with($content, 'ERROR:')) {
            $this->logger()->error('The Referral C-CDA for a referral\'s Direct notice came back empty or as an error', [
                'transaction_id' => $event->referralId,
            ]);
            $event->failed(xl('The Referral C-CDA could not be made. See the log.'));
            return;
        }

        $requester = $this->requester === null
            ? PhiMailRequester::fromSession(SessionWrapperFactory::getInstance()->getActiveSession())
            : ($this->requester)();
        $deliver = $this->deliver ?? static fn(PhiMailOutgoingMessage $message, PhiMailRequester $requester): PhiMailSendResult
            => PhiMailSender::fromGlobals($globals, ServiceContainer::getCrypto(), ServiceContainer::getLogger(), EventAuditLogger::getInstance())
                ->deliver($message, $requester, failureEvent: 'transmit-ccd', successEvent: 'transmit-xml');
        // Built before the send, so a database error here is a notice not sent, not one sent but unlogged.
        try {
            $message = new PhiMailOutgoingMessage(
                recipient: $event->directAddress,
                text: $this->messageText($event->patientId),
                attachment: new PhiMailOutgoingAttachment(PhiMailAttachmentType::Cda, self::fileName($ccda->getFilename()), $content),
                verifyFinalDelivery: $globals->getBoolean('phimail_verifyrecipientreceived_enable'),
                patientId: $event->patientId,
                referralId: $event->referralId,
            );
        } catch (SqlQueryException $e) {
            $this->logger()->error("A referral's Direct notice could not be prepared", ['transaction_id' => $event->referralId, 'exception' => $e]);
            $event->failed(xl('The Direct notice could not be prepared. See the log.'));
            return;
        }
        $result = $deliver($message, $requester);
        if ($result->messageId !== null && !$result->logged) {
            // The server queued it, but it couldn't be audited or logged: it went out, and must not be sent again.
            $event->sentUnlogged();
            return;
        }
        $messageId = $result->messageId;
        if ($messageId !== null) {
            $event->sent($messageId);
        } else {
            $event->failed($result->message);
        }
    }

    /**
     * The message's text, which names the patient as a C-CDA sent from the send form does: Direct is
     * the secure channel the C-CDA travels on anyway.
     */
    private function messageText(int $patientId): string
    {
        $name = trim(($this->patientName ?? self::patientName(...))($patientId));
        return xl('A referral is attached') . ($name === '' ? '.' : ' ' . xl('for patient') . ' ' . $name . '.');
    }

    /**
     * The patient's first and last name, or '' when there's no such patient.
     */
    private static function patientName(int $patientId): string
    {
        $row = QueryUtils::querySingleRow('SELECT fname, lname FROM patient_data WHERE pid = ?', [$patientId]);
        return is_array($row) ? (is_string($row['fname'] ?? null) ? $row['fname'] : '') . ' ' . (is_string($row['lname'] ?? null) ? $row['lname'] : '') : '';
    }

    /**
     * The attachment's name: phiMail commands are split on spaces, so it can't have any.
     */
    private static function fileName(string $name): string
    {
        $name = str_replace(' ', '_', trim($name));
        return $name === '' ? 'referral.xml' : $name;
    }

    /**
     * The logger, the container's unless one was given.
     */
    private function logger(): LoggerInterface
    {
        return $this->logger ?? ServiceContainer::getLogger();
    }
}
