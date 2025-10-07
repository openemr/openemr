<?php

/**
 * RegisterApiTestClientCommand.php - Utility class to help test api clients by registering a test client with the OpenEMR API
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
//use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;

class RegisterApiTestClientCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr-dev:register-api-test-client')
            ->setDescription("Utility class to help test api clients by registering a test client with the OpenEMR API")
            ->addUsage('--site=default')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                    new InputOption('redirect-uri', 'u', InputOption::VALUE_REQUIRED, 'The redirect uri to use for the test client', 'https://example.com/callback'),
                ])
            );
    }
    /**
     * Execute the command and spit any output to STDOUT and errors to STDERR
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // going to hit the github api endpoint for the milestone given in the api
        $site = $input->getOption('site');
        $redirectUri = $input->getOption('redirect-uri');
        $symfonyStyler = new SymfonyStyle($input, $output);

        try {
            $clientRepository = new ClientRepository();
            $clientId = $clientRepository->generateClientId();
            $scopeRepository = new ScopeRepository();
            $scopeList = new ServerScopeListEntity();
            $scopes = array_unique(array_merge($scopeList->getAllSupportedScopesList()));
            $info = [
                'client_role' => 'user',
                'client_name' => 'OpenEMR API Test Client ' . date("Y-m-d H:i:s"),
                'client_secret' => $clientRepository->generateClientSecret(),
                'registration_access_token' => $clientRepository->generateRegistrationAccessToken(),
                'registration_client_uri_path' => $clientRepository->generateRegistrationClientUriPath(),
                'contacts' => 'example@open-emr.org',
                'redirect_uris' => [$redirectUri],
                'grant_types' => 'authorization_code|password|client_credentials',
                'scope' => implode(" ", $scopes),
                'dsi_type' => ClientEntity::DSI_TYPE_NONE
            ];

            $saved = $clientRepository->insertNewClient($clientId, $info, $site);
            if (!$saved) {
                $symfonyStyler->error("Error registering test client. Failed to insert client into database. Please check the logs for more details.");
                return Command::FAILURE;
            } else {
                $client = $clientRepository->getClientEntity($clientId);
                if (empty($client)) {
                    $symfonyStyler->error("Error registering test client. Please check the logs for more details.");
                    return Command::FAILURE;
                }
                $clientRepository->saveIsEnabled($client, true);
                $symfonyStyler->success("Client registered successfully.");
                $symfonyStyler->table([
                    'Client ID',
                    'Client Secret',
                    'Registration Access Token',
                    'Registration Client URI Path',
                    'Redirect URIs',
                    'Grant Types',
                    'DSI Type'
                ], [
                    [
                        $clientId,
                        $info['client_secret'],
                        $info['registration_access_token'],
                        $info['registration_client_uri_path'],
                        implode(", ", $info['redirect_uris']),
                        $info['grant_types'],
                        $info['dsi_type']
                    ]
                ]);
                $symfonyStyler->info("Scopes: " . $info['scope']);
            }
            return Command::SUCCESS;
        } catch (Exception $e) {
            $symfonyStyler->error("Error creating : " . $e->getMessage());
            $symfonyStyler->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
