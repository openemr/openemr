<?php

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use Installer;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'install')]
class InstallCommand extends Command
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option] string $dbHost = '127.0.0.1',
        #[Option] int $dbPort = 3306,
        #[Option] string $dbUser = '',
        #[Option] string $dbPassword = '',
        #[Option] string $dbName = 'openemr',
        #[Option] string $dbRootUser = 'root',
        #[Option] string $dbRootPassword = '',
        #[Option(description: 'OpenEMR admin display name')] string $oeAdminName = 'Administrator',
        #[Option(description: 'OpenEMR admin username')] string $oeAdminUsername = 'admin',
        #[Option(description: 'OpenEMR admin password')] string $oeAdminPassword = '',
    ): int {
        $io = new SymfonyStyle($input, $output);

        if ($input->isInteractive() && !$io->confirm('This command is experimental. Continue? (--no-interaction will auto-apply "yes")', default: false)) {
            return Command::FAILURE;
        }

        $params = [
            // DB root
            'root' => $dbRootUser,
            'rootpass' => $dbRootPassword,

            // Runtime DB info
            'server' => $dbHost,
            'port' => $dbPort,
            'login' => $dbUser,
            'pass' => $dbPassword,
            'dbname' => $dbName, // NEEDS VALIDATION
            // Future:
            // - sockets instead of host/port
            // - SSL

            'loginhost' => '%', // FIXME: webserver for db user

            // Initial admin user seed
            'iuser' => $oeAdminUsername,
            'iuname' => $oeAdminName,
            'iuserpass' => $oeAdminPassword,

            // == Not user configurable ==
            'site' => 'default', // Only default site supported.
        ];
        $logger = new ConsoleLogger($output);
        $installer = new Installer(
            $params,
            $logger,
        );
        $success = $installer->quick_install();
        if (!$success) {
            $output->writeln('Installation failed:');
            $output->writeln($installer->error_message);
            return Command::FAILURE;
        }
        $output->writeln('OpenEMR has been installed!');
        return Command::SUCCESS;
    }
}
