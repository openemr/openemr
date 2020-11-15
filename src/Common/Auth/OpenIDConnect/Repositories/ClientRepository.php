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

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier)
    {
        $clients = sqlQueryNoLog("Select * From oauth_clients Where client_id=?", array($clientIdentifier));

        // Check if client is registered
        if ($clients === false) {
            return false;
        }

        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName($clients['client_name']);
        $client->setRedirectUri($clients['redirect_uri']);
        $client->setIsConfidential($clients['is_confidential']);

        return $client;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        if ($grantType == 'authorization_code') {
            $client = sqlQueryNoLog("SELECT `client_secret`, `is_confidential` FROM `oauth_clients` WHERE `client_id` = ?", [$clientIdentifier]);

            // Check if client is registered
            if ($client === false) {
                return false;
            }

            // Validate client if is_confidential
            if (!empty($clientSecret) && !empty($client['is_confidential'])) {
                $secret = (new CryptoGen)->decryptStandard($client['client_secret']);
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
}
