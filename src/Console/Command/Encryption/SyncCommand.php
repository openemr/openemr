<?php

declare(strict_types=1);

namespace OpenEMR\Console\Command\Encryption;

use Doctrine\DBAL\{
    ArrayParameterType,
    Connection,
};
use OpenEMR\Common\Crypto\CryptoInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'encryption:sync', description: 'sync encryption')]
class SyncCommand extends Command
{
    private array $database = [
        'login_mfa_registrations' => ['var1'],
        'log' => ['comments'],
        'api_log' => ['request_url', 'request_body', 'response'],
        'x12_partners' => ['x12_sftp_pass'],
        'onsite_portal_activity' => ['checksum'],
        'oauth_clients' => ['client_secret'],
        'keys' => ['value'],
        'payment_processing_audit' => ['audit_data'],
        'module_faxsms_credentials' => ['credentials'],
        'comlink_telehealth_auth' => ['auth_token'],
    ];

    // TODO: can this be pulled straight from GLOBALS_METADATA?
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
        private Connection $conn,
        private CryptoInterface $crypto,
    ) {
        parent::__construct();
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(description: 'Do not write changes')] bool $dryRun = false,
    ): int {
        // iterate over table, figure out target data based on config state and
        // if needed key version
        //
        // loop over data not in target state
        // - decryptFromDatabase
        // - encryptForDatabase
        // - UPDATE
        //
        //

        // print_r($this->crypto);

        // Handle globals
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

            // TODO: this will _always_ cause a change even if it's at the
            // preferred format. CryptoInterface needs a specific "is this at
            // the preferred format" check.
            $plain = $this->crypto->decryptFromDatabase($row['gl_value']);
            $updated = $this->crypto->encryptForDatabase($plain);

            if ($dryRun) {
                $output->writeln("Dry-run: would update $key");
            } else {
                $output->writeln("Updating $key");
                $this->conn->update(table: 'globals', data: ['gl_value' => $updated] , criteria: ['gl_name' => $key]);
            }
        }


        // files: again, its own thing

        return 0;
    }
}
