<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Log\LoggerInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->logger = SystemLogger::instance();
    }

    /**
     * @return ClientEntity[]
     */
    public function listClientEntities(): array
    {
        $clients = sqlStatementNoLog("Select * From oauth_clients");
        $list = [];
        if (!empty($clients)) {
            while ($client = $clients->FetchRow()) {
                $list[] = $this->hydrateClientEntityFromArray($client);
            }
        }
        return $list;
    }

    public function getClientEntity($clientIdentifier)
    {
        $clients = sqlQueryNoLog("Select * From oauth_clients Where client_id=?", array($clientIdentifier));

        // Check if client is registered
        if ($clients === false) {
            $this->logger->error(
                "ClientRepository->getClientEntity() no client found for identifier ",
                ["client" => $clientIdentifier]
            );
            return false;
        }

        $this->logger->debug(
            "ClientRepository->getClientEntity() client found",
            [
                "client" => [
                    "client_name" => $clients['client_name'],
                    "redirect_uri" => $clients['redirect_uri'],
                    "is_confidential" => $clients['is_confidential']
                ]
            ]
        );
        return $this->hydrateClientEntityFromArray($clients);
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        if ($grantType == 'authorization_code') {
            $client = sqlQueryNoLog("SELECT `client_secret`, `is_confidential` FROM `oauth_clients` WHERE `client_id` = ?", [$clientIdentifier]);

            // Check if client is registered
            if ($client === false) {
                $this->logger->error(
                    "ClientRepository->validateClient() no client found for identifier ",
                    ["client" => $clientIdentifier]
                );
                return false;
            }

            // Validate client if is_confidential
            if (!empty($clientSecret) && !empty($client['is_confidential'])) {
                $secret = (new CryptoGen())->decryptStandard($client['client_secret']);
                if (empty($secret)) {
                    return false;
                }
                return hash_equals($clientSecret, $secret);
            }

            return true;
        } else {
            // password and refresh grant
            return true;
        }
    }

    /**
     * @param $client_record
     * @return ClientEntity
     */
    private function hydrateClientEntityFromArray($client_record): ClientEntity
    {
        $client = new ClientEntity();
        $client->setIdentifier($client_record['client_id']);
        $client->setName($client_record['client_name']);
        $client->setRedirectUri($client_record['redirect_uri']);
        $client->setIsConfidential($client_record['is_confidential']);
        $client->setScopes($client_record['scope']);
        $client->setClientRole($client_record['client_role']);
        // launch uri is the same as the initiate_login_uri SMART uses launchUri
        // so we will refer to it that way.
        $client->setLaunchUri($client_record['initiate_login_uri']);
        return $client;
    }
}
