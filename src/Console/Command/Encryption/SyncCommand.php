<?php

declare(strict_types=1);

namespace OpenEMR\Console\Command\Encryption;

use Doctrine\DBAL\{
    ArrayParameterType,
    Connection,
};
use Psr\Log\LoggerInterface;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\Storage\{ManagerInterface, Location};
use OpenEMR\Services\KeyRotation\{
    AppConfigKeyRotation,
    DocumentKeyRotation,
};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
// use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'encryption:sync', description: 'sync encryption')]
class SyncCommand extends Command
{
    private LoggerInterface $logger;

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

    public function __construct(
        private AppConfigKeyRotation $appConfigRotation,
        private DocumentKeyRotation $docRotation,
    ) {
        parent::__construct();
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(description: 'Do not write changes')] bool $dryRun = false,
    ): int {
        $this->logger = new ConsoleLogger(output: $output);

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

        // Globals table (app config)
        $this->logger->notice('Beginning app config');
        $this->appConfigRotation->setDryRun($dryRun);
        $this->appConfigRotation->setLogger($this->logger);
        $this->appConfigRotation->rotateConfigs();
        $this->logger->notice('App config complete');

        // Documents
        $this->logger->notice('Beginning documents');
        $this->docRotation->setDryRun($dryRun);
        $this->docRotation->setLogger($this->logger);
        $this->docRotation->rotateAllDocuments();
        $this->logger->notice('Documents complete');

        // TODO: other non-document files

        return 0;
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
