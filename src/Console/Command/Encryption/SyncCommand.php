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
    public function __invoke(
        OutputInterface $output,
    ): int {
        return 0;
    }
}
