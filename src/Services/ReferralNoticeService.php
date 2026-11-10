<?php

/**
 * Tells a referral's recipient that a Task was created for them (#15137), when the
 * fhir_referral_notify_recipient global is on, and records how it went for staff to see.
 *
 * The channel follows what the recipient's address book entry holds, with no setting of its own: its
 * Direct address if it has one, which gets the Referral C-CDA through ReferralDirectNoticeEvent;
 * otherwise its email address, which gets a plain email with no patient details; otherwise nothing,
 * and the referral shows staff that the recipient couldn't be told.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use Doctrine\DBAL\Exception as DbalException;
use MyMailer;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Referral\ReferralDirectNoticeEvent;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ReferralNoticeService
{
    /** The global with the address the practice's notification emails come from. */
    public const SENDER_GLOBAL = 'patient_reminder_sender_email';

    /**
     * Collaborators left null are made the first time they're needed.
     *
     * @param ?\Closure(string, string, string, string): bool $queueEmail queues an email (sender, recipient,
     *     subject, body); MyMailer::emailServiceQueue() when null
     * @param ?\Closure(): array{name: string, phone: string} $practice the practice's name and phone, as an
     *     email gives them; the primary business entity's when null
     */
    public function __construct(
        private ?ReferralNoticeRepository $notices = null,
        private ?EventDispatcherInterface $dispatcher = null,
        private readonly ?\Closure $queueEmail = null,
        private readonly ?\Closure $practice = null,
        private ?OEGlobalsBag $globals = null,
        private ?ClockInterface $clock = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Whether recipients are told about their Tasks: both globals are on.
     */
    public function isEnabled(): bool
    {
        $globals = $this->globals();
        return $globals->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)
            && $globals->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_NOTIFY_RECIPIENT->value);
    }

    /**
     * Tells the referral's recipient about their new Task, and records how it went.
     *
     * @param string $taskUuid the Task just created for them
     * @param ?int $userId who saved the referral
     * @return ?ReferralNoticeOutcome how it went, or null when recipients aren't told or the referral names no one
     */
    public function notify(int $referralId, string $taskUuid, ?int $userId): ?ReferralNoticeOutcome
    {
        if (!$this->isEnabled()) {
            return null;
        }
        $recipient = $this->notices()->recipientAddresses($referralId);
        if ($recipient === null) {
            return null;
        }
        [$outcome, $address, $messageId, $detail] = match (true) {
            $recipient['direct'] !== '' => $this->byDirect($referralId, $recipient['patient_id'], $recipient['direct'], $userId),
            $recipient['email'] !== '' => $this->byEmail($recipient['email']),
            default => [ReferralNoticeOutcome::NoAddress, null, null, null],
        };
        $this->notices()->record($referralId, $taskUuid, $outcome, $address, $messageId, $detail, $userId, $this->clock()->now());
        if ($outcome->needsStaff()) {
            $this->logger()->warning('A referral\'s recipient was not told about their Task', [
                'transaction_id' => $referralId,
                'task_uuid' => $taskUuid,
                'outcome' => $outcome->value,
            ]);
        }
        return $outcome;
    }

    /**
     * @return array{ReferralNoticeOutcome, ?string, ?string, ?string} outcome, address, message id, detail
     */
    private function byDirect(int $referralId, int $patientId, string $address, ?int $userId): array
    {
        $event = new ReferralDirectNoticeEvent($referralId, $patientId, $address, $userId);
        $dispatcher = $this->dispatcher();
        if ($dispatcher === null) {
            return [ReferralNoticeOutcome::DirectUnavailable, $address, null, null];
        }
        try {
            $dispatcher->dispatch($event, ReferralDirectNoticeEvent::EVENT_NAME);
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            // The referral and its Task are saved; the notice is what failed, and staff are shown that.
            $this->logger()->error('A referral\'s Direct notice failed', ['transaction_id' => $referralId, 'exception' => $e]);
            return [ReferralNoticeOutcome::DirectFailed, $address, null, xl('The Direct notice could not be sent. See the log.')];
        }
        $messageId = $event->messageId();
        return match (true) {
            $messageId !== null => [ReferralNoticeOutcome::DirectSent, $address, $messageId, null],
            $event->wasSentUnlogged() => [ReferralNoticeOutcome::DirectSentUnlogged, $address, null, null],
            $event->failure() !== null => [ReferralNoticeOutcome::DirectFailed, $address, null, $event->failure()],
            default => [ReferralNoticeOutcome::DirectUnavailable, $address, null, null],
        };
    }

    /**
     * A plain email with no patient details: only that the practice sent a referral and how to reach it.
     *
     * @return array{ReferralNoticeOutcome, ?string, ?string, ?string} outcome, address, message id, detail
     */
    private function byEmail(string $address): array
    {
        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            return [ReferralNoticeOutcome::EmailFailed, $address, null, xl('The email address in the address book entry is not valid.')];
        }
        $sender = trim($this->globals()->getString(self::SENDER_GLOBAL));
        if ($sender === '') {
            return [ReferralNoticeOutcome::EmailFailed, $address, null, xl('No sender email address is set for notifications (Notification Email Address).')];
        }
        $practice = $this->practice();
        $subject = xl('Referral from') . ' ' . $practice['name'];
        $body = $practice['name'] . ' ' . xl('has sent you a referral.') . ' '
            . xl('This email does not include patient details.') . ' '
            . ($practice['phone'] === ''
                ? xl('Please contact the practice to receive it.')
                : xl('Please contact the practice to receive it at') . ' ' . $practice['phone'] . '.');
        $queued = ($this->queueEmail ?? MyMailer::emailServiceQueue(...))($sender, $address, $subject, $body);
        return $queued
            ? [ReferralNoticeOutcome::EmailQueued, $address, null, null]
            : [ReferralNoticeOutcome::EmailFailed, $address, null, xl('The email could not be queued.')];
    }

    /**
     * @return array{name: string, phone: string}
     */
    private function practice(): array
    {
        if ($this->practice !== null) {
            return ($this->practice)();
        }
        $entity = (new FacilityService())->getPrimaryBusinessEntity();
        $name = is_array($entity) && is_string($entity['name'] ?? null) ? trim($entity['name']) : '';
        $phone = is_array($entity) && is_string($entity['phone'] ?? null) ? trim($entity['phone']) : '';
        return ['name' => $name === '' ? xl('Your referring practice') : $name, 'phone' => $phone];
    }

    /**
     * The notice records, made the first time they're needed.
     */
    private function notices(): ReferralNoticeRepository
    {
        return $this->notices ??= new ReferralNoticeRepository();
    }

    /**
     * The event dispatcher: the given one, else the kernel's, else none.
     */
    private function dispatcher(): ?EventDispatcherInterface
    {
        if ($this->dispatcher === null && $this->globals()->hasKernel()) {
            $this->dispatcher = $this->globals()->getKernel()->getEventDispatcher();
        }
        return $this->dispatcher;
    }

    /**
     * The globals, the given ones or the running site's.
     */
    private function globals(): OEGlobalsBag
    {
        return $this->globals ??= OEGlobalsBag::getInstance();
    }

    /**
     * The clock, the container's unless one was given.
     */
    private function clock(): ClockInterface
    {
        return $this->clock ??= ServiceContainer::getClock();
    }

    /**
     * The logger, the container's unless one was given.
     */
    private function logger(): LoggerInterface
    {
        return $this->logger ??= ServiceContainer::getLogger();
    }
}
