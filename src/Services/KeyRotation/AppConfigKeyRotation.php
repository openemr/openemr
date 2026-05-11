<?php

declare(strict_types=1);

namespace OpenEMR\Services\KeyRotation;

use Doctrine\DBAL\{ArrayParameterType, Connection};
use Psr\Log\LoggerInterface;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\Storage\{ManagerInterface, Location};

class AppConfigKeyRotation
{
    private bool $dryRun = true;

    // TODO: can this be pulled straight from GLOBALS_METADATA?
    /**
     * @var string[]
     */
    private array $encryptedGlobals = [
        // Payment Gateways
        'gateway_api_key',
        'gateway_public_key',
        'gateway_transaction_key',
        'rainforest_api_key',
        'rainforest_webhook_secret',

        // Sphere Payment Processing
        'sphere_patientfront_trxcustid',
        'sphere_patientfront_trxcustid_licensekey',
        'sphere_clinicfront_trxcustid',
        'sphere_clinicfront_trxcustid_licensekey',
        'sphere_clinicfront_retail_trxcustid',
        'sphere_clinicfront_retail_trxcustid_licensekey',
        'sphere_ecomm_tc_link_pass',
        'sphere_retail_tc_link_pass',
        'sphere_moto_tc_link_pass',

        // Email / Messaging
        'SMTP_PASS',
        'phimail_password',
        'phone_gateway_password',

        // External Services
        'couchdb_pass',
        'google_recaptcha_secret_key',
        'erx_account_password',
        'easipro_pass',
        'usps_apiv3_client_id',
        'usps_apiv3_client_secret',

        // Weno eRx Module
        'weno_admin_password',
        'weno_encryption_key',
        'weno_provider_password',

        // Comlink Telehealth Module
        'comlink_telehealth_user_password',

        // DORN Module
        'oe_dorn_config_clientsecret',

        // ClaimRev Module
        'oe_claimrev_config_clientsecret',
    ];

    public function __construct(
        readonly private Connection $conn,
        private LoggerInterface $logger,
        readonly private CryptoInterface $crypto,
    ) {
    }

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function rotateConfigs(): void
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->select('gl_name', 'gl_value')
            ->from('globals')
            ->where('gl_name IN (:names)')
            ->setParameter('names', $this->encryptedGlobals, ArrayParameterType::STRING);
        $result = $qb->executeQuery();

        foreach ($result->fetchAllAssociative() as $row) {
            if ($row['gl_value'] === '') {
                continue;
            }
            $key = $row['gl_name'];
            $value = $row['gl_value'];
            assert(is_string($key));
            assert(is_string($value));
            $logContext = ['key' => $key];

            if ($this->crypto->isDatabaseValueLatest($value)) {
                $this->logger->debug('Globals: {key} is current', $logContext);
                continue;
            }

            // Rebuild into current target state
            $updated = $this->crypto->encryptForDatabase(
                $this->crypto->decryptFromDatabase($value)
            );

            if ($this->dryRun) {
                $this->logger->debug('Globals: would update {key} (dry run)', $logContext);
                continue;
            }

            $this->logger->info('Globals: updating {key}', $logContext);
            $this->conn->update(
                table: 'globals',
                data: ['gl_value' => $updated],
                criteria: ['gl_name' => $key],
            );
        }
    }
}
