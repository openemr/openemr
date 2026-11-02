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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;

final readonly class StatementEmail
{
    public function __construct(private LoggerInterface $logger)
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
            throw new \InvalidArgumentException('Statement message has no text content');
        }

        $patient = QueryUtils::querySingleRow('SELECT * FROM `patient_data` WHERE `pid` = ?', [$pid]);
        $patient = is_array($patient) ? $patient : [];
        $email = is_string($patient['email'] ?? null) ? $patient['email'] : '';
        $sender = OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email');
        if (($patient['hipaa_allowemail'] ?? '') !== 'YES' || $email === '' || $sender === '') {
            throw new \RuntimeException(xl('Email is not allowed or not configured for this patient'));
        }
        if (!ValidationUtils::isValidEmail($email)) {
            throw new \RuntimeException(xl('Patient email address is invalid'));
        }
        if (!ValidationUtils::isValidEmail($sender)) {
            throw new \RuntimeException(xl('Sender email address is not configured or invalid'));
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $facilityId = $session->get('pc_facility');
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
     * The statement list's Email column: the patient allows email and the portal,
     * has the HIPAA notice and a valid address.
     */
    public function listsAsEmailable(int $pid): bool
    {
        $patient = QueryUtils::querySingleRow('SELECT * FROM `patient_data` WHERE `pid` = ?', [$pid]);
        if (!is_array($patient)) {
            return false;
        }
        return ($patient['hipaa_allowemail'] ?? '') === 'YES'
            && ($patient['allow_patient_portal'] ?? '') === 'YES'
            && ($patient['hipaa_notice'] ?? '') === 'YES'
            && ValidationUtils::isValidEmail(self::text($patient, 'email'));
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
