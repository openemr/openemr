<?php

/**
 * CLI installer command for OpenEMR.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Installer\InstallerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'install', description: 'Install OpenEMR (experimental)')]
class InstallCommand extends Command
{
    public function __construct(private readonly InstallerInterface $installer)
    {
        parent::__construct();
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option(description: 'Database server hostname')] string $dbHost = '127.0.0.1',
        #[Option(description: 'Database server port')] int $dbPort = 3306,
        #[Option(description: 'Database username for OpenEMR')] string $dbUser = '',
        #[Option(description: 'Database password for OpenEMR')] string $dbPassword = '',
        #[Option(description: 'Database name')] string $dbName = 'openemr',
        #[Option(description: 'Database root username')] string $dbRootUser = 'root',
        #[Option(description: 'Database root password')] string $dbRootPassword = '',
        #[Option(description: 'OpenEMR admin display name')] string $oeAdminName = 'Administrator',
        #[Option(description: 'OpenEMR admin username')] string $oeAdminUsername = 'admin',
        #[Option(description: 'OpenEMR admin password')] string $oeAdminPassword = '',
    ): int {
        $io = new SymfonyStyle($input, $output);

        if ($input->isInteractive() && !$io->confirm('This command is experimental. Continue?', default: false)) {
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
        $this->installer->setLogger(new ConsoleLogger($output));
        $success = $this->installer->install($params);
        if (!$success) {
            $io->error(['Installation failed:', $this->installer->getErrorMessage()]);
            return Command::FAILURE;
        }
        $io->success('OpenEMR has been installed!');
        return Command::SUCCESS;
    }
}
