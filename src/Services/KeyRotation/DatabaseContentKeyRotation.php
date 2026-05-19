<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\KeyRotation;

use Doctrine\DBAL\Connection;
use OpenEMR\Common\Crypto\CryptoInterface;
use Psr\Log\LoggerInterface;

/**
 * Puts encrypted columns in the database into the current target encryption
 * state and/or key version.
 */
class DatabaseContentKeyRotation
{
    public function __construct(
        // private readonly EncryptionConfig $config,
        readonly private Connection $conn,
        private LoggerInterface $logger,
        readonly private CryptoInterface $crypto,
    ) {
    }

    private array $encryptedDatabaseColumns = [
        'api_log' => ['request_url', 'request_body', 'response'],
        'comlink_telehealth_auth' => ['auth_token'],
        // Keys needs special handling! Encrypting the encryption keys will
        // destroy the installation; only the OAuth keys need touching.
        // 'keys' => ['value'],
        // Logs also needs special handling - comments is either encrypted or
        // base64'd. See #12118 + #12122
        // 'log' => ['comments'],
        'login_mfa_registrations' => ['var1'], // pk=(user_id,name)
        'module_faxsms_credentials' => ['credentials'],
        'oauth_clients' => ['client_secret'], // pk=client_id
        'onsite_portal_activity' => ['checksum'],
        'payment_processing_audit' => ['audit_data'], // pk=uuid
        'x12_partners' => ['x12_sftp_pass'],
    ];

    public function rotateDatabaseContent(): void
    {
        foreach ($this->encryptedDatabaseColumns as $table => $columns) {
            foreach ($columns as $column) {
                // $this->syncDataInColumn($table, $column); // need to read+pass PK
            }
        }

    }


    private function syncDataInColumn(string $table, string $column, string $pkColumn): void
    {
        // Depending on installation size, this may need to be done in chunks.
        // This also needs to check if the table even exists, some are for
        // dormant modules.

        // one table as a multi-column PK. It's probably best to just fix that
        // at the source rather than trying to patch around it here.
    }

}
