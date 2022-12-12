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
        $this->logger = new SystemLogger();
    }

    /**
     * @return ClientEntity[]
     */
    public function listClientEntities(): array
    {
        $clients = sqlStatementNoLog("Select * From oauth_clients ORDER BY is_enabled DESC, register_date DESC");
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
        $this->logger->debug(
            "ClientRepository->validateClient() checking client validation",
            ["client" => $clientIdentifier, "grantType" => $grantType]
        );
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
                $secretMatches = hash_equals($clientSecret, $secret);
                if (!$secretMatches) {
                    $this->logger->error(
                        "ClientRepository->validateClient() Confidential client sent invalid client secret.  Validation failed",
                        ["client" => $clientIdentifier, "grantType" => $grantType]
                    );
                }
                return $secretMatches;
            }

            return true;
        } else {
            // password and refresh grant
            return true;
        }
    }

    /**
     * Set a client in the database to be enabled if $isEnabled is true or disabled if $isEnabled is false.
     * @param ClientEntity $client
     * @param $isEnabled
     * @return bool True if it succeeded
     * @throws \RuntimeException If there is a database error in saving.
     */
    public function saveIsEnabled(ClientEntity $client, $isEnabled)
    {
        // TODO: adunsulag do we want to eventually just have a save() method.. it would be very handy but not sure
        // we want any oauth2 values being overwritten.
        $isEnabledSaveValue = $isEnabled === true ? 1 : 0;
        $clientId = $client->getIdentifier();
        $params = [$isEnabledSaveValue, $clientId];
        $res = sqlStatement("UPDATE oauth_clients SET is_enabled=? WHERE client_id = ?", $params);
        if ($res === false) {
            // TODO: adunsulag is there a better exception to throw here in OpenEMR than runtime?
            throw new \RuntimeException("Failed to save oauth_clients is_enabled flag.  Check logs for sql error");
        }
        return true;
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
        $client->setIsEnabled($client_record['is_enabled'] == "1");
        $client->setJwks($client_record['jwks']);
        $client->setJwksUri($client_record['jwks_uri']);
        $client->setLogoutRedirectUris($client_record['logout_redirect_uris']);
        $client->setContacts($client_record['contacts']);
        $client->setRegistrationDate($client_record['register_date']);
        return $client;
    }
}
