<?php

declare(strict_types=1);

namespace OpenEMR\Console\Command\Encryption;

use Doctrine\DBAL\{
    ArrayParameterType,
    Connection,
};
use Psr\Log\LoggerInterface;
use OpenEMR\Services\Storage\{ManagerInterface, Location};
use OpenEMR\Services\KeyRotation\{
    AppConfigKeyRotation,
    DatabaseContentKeyRotation,
    DocumentKeyRotation,
};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

#[AsCommand(name: 'encryption:sync', description: 'sync encryption')]
class SyncCommand extends Command
{
    private LoggerInterface $logger;

    public function __construct(
        private AppConfigKeyRotation $appConfigRotation,
        private DatabaseContentKeyRotation $dbRotation,
        private DocumentKeyRotation $docRotation,
    ) {
        parent::__construct();
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(description: 'Write changes (default: runs in dry-run mode)')] bool $execute = false,
    ): int {
        $this->logger = new ConsoleLogger(output: $output);
        $dryRun = !$execute;

        // Globals table (app config)
        $this->logger->notice('Beginning app config');
        $this->appConfigRotation->setDryRun($dryRun);
        $this->appConfigRotation->setLogger($this->logger);
        $this->appConfigRotation->rotateConfigs();
        $this->logger->notice('App config complete');

        // TODO: DatabaseContentKeyRotation

        // Documents
        $this->logger->notice('Beginning documents');
        $this->docRotation->setDryRun($dryRun);
        $this->docRotation->setLogger($this->logger);
        $this->docRotation->rotateAllDocuments();
        $this->logger->notice('Documents complete');

        // TODO: other non-document files

        return 0;
    }
}
