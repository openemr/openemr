<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Console\Command\Encryption;

use Doctrine\DBAL\{
    ArrayParameterType,
    Connection,
};
use Psr\Log\LoggerInterface;
use OpenEMR\BC\Crypto\EncryptionConfig;
use OpenEMR\Services\KeyRotation\{
    AppConfigKeyRotation,
    DatabaseContentKeyRotation,
    DocumentKeyRotation,
};
use OpenEMR\Services\Storage\{ManagerInterface, Location};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * CLI wrapper that exposes the encryption key rotation and state syncing
 * tooling.
 */
#[AsCommand(name: 'encryption:sync', description: 'sync encryption')]
class SyncCommand extends Command
{
    private LoggerInterface $logger;

    public function __construct(
        private AppConfigKeyRotation $appConfigRotation,
        private DatabaseContentKeyRotation $dbRotation,
        private DocumentKeyRotation $docRotation,
        private readonly EncryptionConfig $config,
    ) {
        parent::__construct();
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(description: 'Write changes (default: runs in dry-run mode)')] bool $execute = false,
    ): int {
        $this->logger = new ConsoleLogger(output: $output);
        $dryRun = !$execute;

        $output->writeln('Target DB encryption: ' . ($this->config->databaseEncryption ? 'on' : 'off'));
        $output->writeln('Target Filesystem encryption: ' . ($this->config->filesystemEncryption ? 'on' : 'off'));
        $output->writeln($execute ? 'WRITING CHANGES' : 'Dry-run mode, changes will not be written');

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
