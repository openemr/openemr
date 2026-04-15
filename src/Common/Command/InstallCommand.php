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
    ): int {
        $io = new SymfonyStyle($input, $output);

        if ($input->isInteractive() && !$io->confirm('This command is experimental. Continue? (--no-interaction will auto-apply "yes")', default: false)) {
            return Command::FAILURE;
        }

        // login -> dbuser
        // pass -> dbPassowrd
        // dbname => dbName
        // collate = 'utf8mb4_general_ci'
        // pre-validate things are nonempty??
        $params = [
            // DB root
            'root' => $dbRootUser,
            'rootpass' => $dbRootPassword,

            'server' => $dbHost,
            'port' => $dbPort,
            'login' => $dbUser,
            'pass' => $dbPassword,
            'dbname' => $dbName, // NEEDS VALIDATION
            'loginhost' => '%', // FIXME: webserver for db user

            'iuserpass' => 'changeme',
            'site' => 'FAKESITE', // FIXME: remove this.
        ];
        $logger = new ConsoleLogger($output);
        $installer = new Installer(
            $params,
            $logger,
        );
        /* return 2; */
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
