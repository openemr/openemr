<?php

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use Installer;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'install')]
class InstallCommand extends Command
{
    public function __invoke(
        OutputInterface $output,
        #[Option] string $dbServer = '127.0.0.1',
        #[Option] int $dbPort = 3306,
        #[Option] string $dbUser = '',
        #[Option] string $dbPassword = '',
        #[Option] string $dbName = 'openemr',
        #[Option] string $dbRootUser = 'root',
        #[Option] string $dbRootPassword = '',
    ): int {
        $logger = new ConsoleLogger($output);

        // login -> dbuser
        // pass -> dbPassowrd
        // dbname => dbName
        // collate = 'utf8mb4_general_ci'
        return -2;
    }
}
