<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Service;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Persistence for module credentials in `module_faxsms_credentials`.
 *
 * Extracted from AppDispatch so the controller no longer talks to SQL/crypto
 * directly. Behavior is preserved exactly; the controller still resolves the
 * owning auth_user and the module vendor and passes them in. The crypto service
 * is resolved from the same container source AppDispatch uses, so this class
 * does not depend on a client's construction timing.
 */
class CredentialsRepository
{
    /**
     * Default appointment-reminder body used when no custom SMS/email message
     * has been saved.
     */
    private const DEFAULT_REMINDER_TEMPLATE = "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.";

    private readonly CryptoInterface $crypto;

    public function __construct(?CryptoInterface $crypto = null)
    {
        $this->crypto = $crypto ?? ServiceContainer::getCrypto();
    }

    /**
     * Load a service vendor's credentials for the given owner. Returns the
     * decrypted credential array, or the empty-default template when no row
     * exists.
     *
     * @param string|null $vendor
     * @param int         $authUser
     * @return array<mixed>
     */
    public function loadSetup(?string $vendor, int $authUser): array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?",
            [$authUser, $vendor]
        );

        if (!is_array($row) || $row === []) {
            return [
                'username' => '',
                'extension' => '',
                'password' => '',
                'account' => '',
                'phone' => '',
                'appKey' => '',
                'appSecret' => '',
                'server' => '',
                'portal' => '',
                'smsNumber' => '',
                'production' => '',
                'redirect_url' => '',
                'smsHours' => "50",
                'smsMessage' => self::DEFAULT_REMINDER_TEMPLATE,
                'jwt' => '',
                // SignalWire fields
                'space_url' => '',
                'project_id' => '',
                'api_token' => '',
                'fax_number' => ''
            ];
        }

        $stored = $row['credentials'] ?? null;
        $decrypt = $this->crypto->decryptFromDatabase(is_string($stored) ? $stored : null);
        $decode = json_decode($decrypt, true);
        $decode = is_array($decode) ? $decode : [];
        if (($decode['smsMessage'] ?? '') === '') {
            $decode['smsMessage'] = self::DEFAULT_REMINDER_TEMPLATE;
        }
        return $decode;
    }

    /**
     * Persist a service vendor's credentials for the given owner.
     *
     * @param string|null          $vendor
     * @param int                  $authUser
     * @param array<mixed> $setup
     * @return string Human-readable status message (translated).
     */
    public function storeSetup(?string $vendor, int $authUser, array $setup): string
    {
        // encrypt for safety.
        $jsonSetup = json_encode($setup);
        $content = $this->crypto->encryptForDatabase($jsonSetup !== false ? $jsonSetup : null);
        if (($vendor === null || $vendor === '') || $setup === []) {
            return xlt('Error: Missing vendor, user or credential items');
        }
        $sql = "INSERT INTO `module_faxsms_credentials` (`id`, `auth_user`, `vendor`, `credentials`)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `auth_user`= ?, `vendor` = ?, `credentials`= ?, `updated` = NOW()";
        QueryUtils::sqlStatementThrowException($sql, ['', $authUser, $vendor, $content, $authUser, $vendor, $content]);

        return xlt('Save Success');
    }

    /**
     * Load the email-notification credentials for the given owner. Returns the
     * decrypted array, or globals-derived defaults when no row exists.
     *
     * @param int $authUser
     * @return array<mixed>
     */
    public function loadEmailSetup(int $authUser): array
    {
        $vendor = '_email';
        $row = QueryUtils::querySingleRow(
            "SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?",
            [$authUser, $vendor]
        );

        if (!is_array($row) || $row === []) {
            $defaults = [
                'sender_name' => OEGlobalsBag::getInstance()->getString('patient_reminder_sender_name'),
                'sender_email' => OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email'),
                'notification_email' => OEGlobalsBag::getInstance()->getString('practice_return_email_path'),
                'email_transport' => OEGlobalsBag::getInstance()->get('EMAIL_METHOD'),
                'smtp_host' => OEGlobalsBag::getInstance()->getString('SMTP_HOST'),
                'smtp_port' => OEGlobalsBag::getInstance()->getInt('SMTP_PORT'),
                'smtp_user' => OEGlobalsBag::getInstance()->getString('SMTP_USER'),
                'smtp_password' => OEGlobalsBag::getInstance()->getString('SMTP_PASS'),
                'smtp_security' => OEGlobalsBag::getInstance()->get('SMTP_SECURE'),
                'notification_hours' => OEGlobalsBag::getInstance()->getInt('EMAIL_NOTIFICATION_HOUR'),
                'email_message' => OEGlobalsBag::getInstance()->getString('EMAIL_MESSAGE'),
            ];
            if ($defaults['email_message'] === '') {
                $defaults['email_message'] = self::DEFAULT_REMINDER_TEMPLATE;
            }
            return $defaults;
        }

        $stored = $row['credentials'] ?? null;
        $decrypt = $this->crypto->decryptFromDatabase(is_string($stored) ? $stored : null);
        $decoded = json_decode($decrypt, true);
        $decoded = is_array($decoded) ? $decoded : [];
        if (($decoded['email_message'] ?? '') === '') {
            $decoded['email_message'] = self::DEFAULT_REMINDER_TEMPLATE;
        }
        return $decoded;
    }

    /**
     * Persist the email-notification credentials for the given owner.
     *
     * @param int   $authUser
     * @param mixed $credentials
     * @return void
     */
    public function storeEmailSetup(int $authUser, $credentials): void
    {
        $vendor = '_email';
        $encoded = json_encode($credentials);
        $encrypted = $this->crypto->encryptForDatabase($encoded !== false ? $encoded : null);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `module_faxsms_credentials` (auth_user, vendor, credentials, updated) VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE credentials = VALUES(credentials), updated = VALUES(updated)",
            [$authUser, $vendor, $encrypted]
        );
    }
}
