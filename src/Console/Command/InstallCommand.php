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

namespace OpenEMR\Console\Command;

use OpenEMR\Common\Installer\InstallerInterface;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
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
        #[Option(description: 'OpenEMR practice group name')] string $oeAdminGroup = 'Default',
        #[Option(description: 'Enable REST API')] bool $enableRestApi = false,
        #[Option(description: 'Enable FHIR API')] bool $enableFhirApi = false,
        #[Option(description: 'Enable Portal API')] bool $enablePortalApi = false,
        #[Option(description: 'Enable OAuth2 password grant (insecure, for testing)')] bool $enablePasswordGrant = false,
        #[Option(description: 'Enable FHIR system scopes')] bool $enableSystemScopes = false,
        #[Option(description: 'Enable C-CDA service')] bool $enableCcda = false,
        #[Option(description: 'Site address for OAuth/FHIR callbacks (e.g. https://localhost:9300)')] string $siteAddress = '',
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
            'igroup' => $oeAdminGroup,

            // == Not user configurable ==
            'site' => 'default', // Only default site supported.
        ];

        $customGlobals = $this->buildCustomGlobals(
            enableRestApi: $enableRestApi,
            enableFhirApi: $enableFhirApi,
            enablePortalApi: $enablePortalApi,
            enablePasswordGrant: $enablePasswordGrant,
            enableSystemScopes: $enableSystemScopes,
            enableCcda: $enableCcda,
            siteAddress: $siteAddress,
        );
        if ($customGlobals !== []) {
            $params['custom_globals'] = json_encode($customGlobals, JSON_THROW_ON_ERROR);
        }

        $this->installer->setLogger(new ConsoleLogger($output));
        $success = $this->installer->install($params);
        if (!$success) {
            $io->error(['Installation failed:', $this->installer->getErrorMessage()]);
            return Command::FAILURE;
        }
        $io->success('OpenEMR has been installed!');
        return Command::SUCCESS;
    }

    /**
     * @return array<string, array{value: string}>
     */
    private function buildCustomGlobals(
        bool $enableRestApi,
        bool $enableFhirApi,
        bool $enablePortalApi,
        bool $enablePasswordGrant,
        bool $enableSystemScopes,
        bool $enableCcda,
        string $siteAddress,
    ): array {
        // Magic values: '3' means "enable for both contexts". See #11863 for enum refactor.
        $flags = array_filter([
            GlobalConnectorsEnum::REST_API->value => $enableRestApi ? '1' : null,
            GlobalConnectorsEnum::REST_FHIR_API->value => $enableFhirApi ? '1' : null,
            GlobalConnectorsEnum::REST_PORTAL_API->value => $enablePortalApi ? '1' : null,
            GlobalConnectorsEnum::OAUTH_PASSWORD_GRANT->value => $enablePasswordGrant ? '3' : null, // Users + Patients
            GlobalConnectorsEnum::REST_SYSTEM_SCOPES_API->value => $enableSystemScopes ? '1' : null,
            GlobalConnectorsEnum::SITE_ADDRESS_OAUTH->value => $siteAddress !== '' ? $siteAddress : null,
            GlobalConnectorsEnum::CCDA_ALT_SERVICE_ENABLE->value => $enableCcda ? '3' : null, // Care Coordination + Portal
        ]);

        return array_map(fn($v) => ['value' => $v], $flags);
    }
}
