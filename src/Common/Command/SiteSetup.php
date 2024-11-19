<?php

namespace OpenEMR\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use OpenEMR\Common\Compatibility\Checker;

class SiteSetup extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:site:setup')
            ->setDescription('Setup a site')
            ->addUsage('--site=default')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                    new InputOption('database_host', 'dbh', InputOption::VALUE_REQUIRED, 'Database host', 'localhost'),
                    new InputOption('database_port', 'dbhp', InputOption::VALUE_REQUIRED, 'Database host', '3306'),
                    new InputOption('database_root_user', 'dbru', InputOption::VALUE_REQUIRED, 'Database root user', 'root'),
                    new InputOption('database_root_pass', 'dbrp', InputOption::VALUE_REQUIRED, 'Database root password', ''),
                    new InputOption('database_user', 'dbu', InputOption::VALUE_REQUIRED, 'Database root user', 'openemr'),
                    new InputOption('database_pass', 'dbp', InputOption::VALUE_REQUIRED, 'Database root password', 'openemr'),
                    new InputOption('database_name', 'dbn', InputOption::VALUE_REQUIRED, 'Database root password', 'openemr'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $check = Checker::checkPhpVersion();
        if ($check !== true) {
            $output->writeln($check);
            return 2;
        }

        $output->writeln('Starting site setup...');
        $defaults = [
            'iuser'                    => 'admin',
            'iuname'                   => 'Administrator',
            'iuserpass'                => 'pass',
            'igroup'                   => 'Default',
            'server'                   => $input->getOption('database_host'), // mysql server
            'loginhost'                => 'localhost', // php/apache server
            'port'                     => $input->getOption('database_port'),
            'root'                     => $input->getOption('database_root_user'),
            'rootpass'                 => $input->getOption('database_root_pass'),
            'login'                    => $input->getOption('database_user'),
            'pass'                     => $input->getOption('database_pass'),
            'dbname'                   => $input->getOption('database_name'),
            'collate'                  => 'utf8mb4_general_ci',
            'site'                     => $input->getOption('site'),
            'source_site_id'           => '',
            'clone_database'           => '',
            'no_root_db_access'        => '',
            'development_translations' => '',
        ];

        // Set the maximum excution time and time limit to unlimited.
        ini_set('max_execution_time', 0);
        ini_set('display_errors', 0);
        set_time_limit(0);

        require_once(__DIR__ . '/../../../library/classes/Installer.class.php');
        $installer = new \Installer($defaults);

        $output->writeln('Setting up site...');

        if (! $installer->quick_install() ) {
            $output->writeln($installer->error_message);
            return 1;
        }

        $output->writeln($installer->debug_message);

        $output->writeln('Site Setup');
        return 0;
    }
}
