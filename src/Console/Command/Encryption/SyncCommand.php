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
    private bool $databaseEncryption;
    private bool $filesystemEncryption;

    private array $encryptedDatabaseColumns = [
        'api_log' => ['request_url', 'request_body', 'response'],
        'comlink_telehealth_auth' => ['auth_token'],
        // Keys needs special handling! Encrypting the encryption keys will
        // destroy the installation; only the OAuth keys need touching.
        // 'keys' => ['value'],
        // Logs also needs special handling - comments is either encrypted or
        // base64'd
        // 'log' => ['comments'],
        'login_mfa_registrations' => ['var1'], // pk=(user_id,name)
        'module_faxsms_credentials' => ['credentials'],
        'oauth_clients' => ['client_secret'], // pk=client_id
        'onsite_portal_activity' => ['checksum'],
        'payment_processing_audit' => ['audit_data'], // pk=uuid
        'x12_partners' => ['x12_sftp_pass'],
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
        $this->readConfig();
        var_dump($this->filesystemEncryption);
        var_dump($this->databaseEncryption);

        foreach ($this->encryptedDatabaseColumns as $table => $columns) {
            foreach ($columns as $column) {
                // $this->syncDataInColumn($table, $column); // need to read+pass PK
            }
        }


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
        // $this->syncGlobalsTable($output, $dryRun);
        $this->syncDocuments($output, $dryRun);

        // files: again, its own thing

        return 0;
    }

    private function syncGlobalsTable(OutputInterface $output, bool $dryRun): void
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

            if ($this->crypto->isDatabaseValueLatest($value)) {
                $output->writeln("Globals: $key is current");
                continue;
            }

            // Rebuild into current target state
            $updated = $this->crypto->encryptForDatabase(
                $this->crypto->decryptFromDatabase($value)
            );

            if ($dryRun) {
                $output->writeln("Dry-run: would update $key");
            } else {
                $output->writeln("Updating $key");
                $this->conn->update(table: 'globals', data: ['gl_value' => $updated] , criteria: ['gl_name' => $key]);
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

    private function syncDocuments(OutputInterface $output, bool $dryRun): void
    {
        // This takes a naive approach for paging and resource management: go
        // as far as possible and if it crashes from resource use, well, run
        // the script again.
        $data = $this->conn->createQueryBuilder()
            ->select('id', 'type', 'url', 'thumb_url') // what else?
            ->from('documents')
            ->where('encrypted = :encrypted')
            ->setParameter('encrypted', $this->filesystemEncryption ? 0 : 1) // Inverse of current state
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($data as $row) {
            print_r($row);
        }


    }

    /**
     * Reimplements a minimal set of application bootstrapping out of the
     * globals table to read the necessary config but still be DI-friendly.
     */
    private function readConfig(): void
    {
        $configs = $this->conn->createQueryBuilder()
            ->select('gl_name', 'gl_value')
            ->from('globals')
            ->where('gl_name IN (:keys)')
            ->setParameter('keys', ['drive_encryption', 'database_encryption'], ArrayParameterType::STRING)
            ->executeQuery()
            ->fetchAllKeyValue();

        // Note: this replicates the logic from library/globals.php :/
        $this->databaseEncryption = ($configs['database_encryption'] ?? '1') === '1';
        $this->filesystemEncryption = ($configs['drive_encryption'] ?? '1') === '1';
    }
}
