<?php

/**
 * Emailing a patient statement, and whether the statement list shows a patient as emailable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\ValidationUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class StatementEmail
{
    /**
     * @param string $sender the patient_reminder_sender_email global
     */
    public function __construct(private LoggerInterface $logger, private SessionInterface $session, private string $sender)
    {
    }

    /**
     * Email the statement to the patient.
     *
     * @throws \InvalidArgumentException if the statement has no text
     * @throws \RuntimeException if it can't be sent; the message says why, for the biller
     */
    public function send(int $pid, string $statement): void
    {
        if (trim(strip_tags($statement)) === '') {
            throw new \InvalidArgumentException(xl('The statement has no text'));
        }

        $patient = self::patient($pid);
        $reason = $this->whyNotEmailable($patient);
        if ($reason !== null) {
            throw new \RuntimeException($reason);
        }
        $email = self::text($patient, 'email');
        $sender = $this->sender;

        $facilityId = $this->session->get('pc_facility');
        $facility = $facilityId
            ? QueryUtils::querySingleRow('SELECT * FROM facility WHERE id = ?', [$facilityId])
            : QueryUtils::querySingleRow('SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1');
        $facilityName = is_array($facility) && is_string($facility['name'] ?? null) ? $facility['name'] : '';

        $mail = new \MyMailer();
        $name = self::text($patient, 'fname') . ' ' . self::text($patient, 'lname');
        $mail->addReplyTo($sender, $sender);
        $mail->setFrom($sender, $sender);
        $mail->addAddress($email, $name);
        $mail->Subject = $facilityName . ' ' . xl('Patient Statement Bill');
        $mail->msgHTML("<html><body><div class='wrapper'>" . $statement . '</div></body></html>');
        $mail->isHTML(true);
        $mail->AltBody = $statement;

        if ($mail->send()) {
            return;
        }
        $this->logger->error('Statement email failed', ['pid' => $pid, 'error' => $mail->ErrorInfo]);
        throw new \RuntimeException(xl('Failed to send statement email'));
    }

    /**
     * The statement list's Email column: send() would email this patient. It uses the
     * same check, so the column and an Email run agree.
     */
    public function listsAsEmailable(int $pid): bool
    {
        return $this->whyNotEmailable(self::patient($pid)) === null;
    }

    /**
     * Why the patient can't be emailed, for the biller, or null when they can: they
     * allow email (HIPAA), have a valid address, and the site has a valid sender.
     *
     * @param array<mixed> $patient
     */
    private function whyNotEmailable(array $patient): ?string
    {
        $email = self::text($patient, 'email');
        if (($patient['hipaa_allowemail'] ?? '') !== 'YES' || $email === '' || $this->sender === '') {
            return xl('Email is not allowed or not configured for this patient');
        }
        if (!ValidationUtils::isValidEmail($email)) {
            return xl('Patient email address is invalid');
        }
        if (!ValidationUtils::isValidEmail($this->sender)) {
            return xl('Sender email address is not configured or invalid');
        }
        return null;
    }

    /**
     * @return array<mixed>
     */
    private static function patient(int $pid): array
    {
        $patient = QueryUtils::querySingleRow('SELECT * FROM `patient_data` WHERE `pid` = ?', [$pid]);
        return is_array($patient) ? $patient : [];
    }

    /**
     * @param array<mixed> $row
     */
    private static function text(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        return is_string($value) ? $value : '';
    }
}
