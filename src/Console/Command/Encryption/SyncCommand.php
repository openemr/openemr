<?php

declare(strict_types=1);

namespace OpenEMR\Console\Command\Encryption;

use OpenEMR\Common\Installer\InstallerInterface;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        return 0;
    }
}
