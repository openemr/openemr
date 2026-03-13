<?php

/**
 * Writes audit records to the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ParameterType;
use OpenEMR\Common\Crypto\CryptoGen;

/**
 * Writes audit records directly to the log tables.
 *
 * This replaces the delegation to EventAuditLogger::recordLogItem(),
 * using the DBAL connection directly.
 */
final class AuditRecordWriter implements AuditRecordWriterInterface
{
    private const LOG_COMMENT_ENCRYPT_VERSION = '4';

    public function __construct(
        private readonly AuditSettingsInterface $settings,
        private readonly CryptoGen $cryptoGen,
    ) {
    }

    public function write(
        Connection $connection,
        int $success,
        string $event,
        string $user,
        string $group,
        string $comments,
        ?int $patientId,
        ?string $category,
        string $logFrom = 'open-emr',
    ): void {
        // Encrypt or encode comments
        if ($this->settings->isEncryptionEnabled()) {
            $comments = $this->cryptoGen->encryptStandard($comments);
            $encrypt = 'Yes';
        } else {
            // Base64 encode for binary safety
            $comments = base64_encode($comments);
            $encrypt = 'No';
        }

        $currentDatetime = date('Y-m-d H:i:s');
        $clientCertCn = $_SERVER['SSL_CLIENT_S_DN_CN'] ?? '';

        // Insert into log table
        $logSql = <<<'SQL'
            INSERT INTO `log` (
                `date`, `event`, `category`, `user`, `groupname`,
                `comments`, `user_notes`, `patient_id`, `success`,
                `crt_user`, `log_from`, `menu_item_id`, `ccda_doc_id`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            SQL;

        $logEntry = [
            $currentDatetime,
            $event,
            $category,
            $user,
            $group,
            $comments,
            '', // user_notes
            $patientId,
            $success,
            $clientCertCn,
            $logFrom,
            null, // menu_item_id
            null, // ccda_doc_id
        ];

        $stmt = $connection->prepare($logSql);
        foreach ($logEntry as $i => $value) {
            $stmt->bindValue($i + 1, $value, $this->getParameterType($value));
        }
        $stmt->execute();

        // Get the last insert ID
        $lastLogId = $connection->lastInsertId();

        // Generate checksum
        $checksum = hash('sha3-512', implode('', array_map(
            fn ($v) => (string) ($v ?? ''),
            $logEntry
        )));

        // Insert into log_comment_encrypt
        $encryptSql = <<<'SQL'
            INSERT INTO `log_comment_encrypt`
                (`log_id`, `encrypt`, `checksum`, `checksum_api`, `version`)
            VALUES (?, ?, ?, ?, ?)
            SQL;

        $stmt = $connection->prepare($encryptSql);
        $stmt->bindValue(1, $lastLogId, ParameterType::INTEGER);
        $stmt->bindValue(2, $encrypt, ParameterType::STRING);
        $stmt->bindValue(3, $checksum, ParameterType::STRING);
        $stmt->bindValue(4, '', ParameterType::STRING); // checksum_api (not used for SQL audits)
        $stmt->bindValue(5, self::LOG_COMMENT_ENCRYPT_VERSION, ParameterType::STRING);
        $stmt->execute();
    }

    private function getParameterType(mixed $value): ParameterType
    {
        return match (true) {
            $value === null => ParameterType::NULL,
            is_int($value) => ParameterType::INTEGER,
            default => ParameterType::STRING,
        };
    }
}
