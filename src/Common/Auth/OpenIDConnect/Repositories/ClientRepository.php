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

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true)
    {
        $clients = sqlQuery("Select * From oauth_clients Where client_id=?", array($clientIdentifier));

        // Check if client is registered
        if ($clients === false) {
            return false;
        }

        if ($clientSecret) {
            if (
                $mustValidateSecret === true
                && $clients['is_confidential'] === true
                && password_verify($clientSecret, $clients['secret']) === false
            ) {
                return false;
            }
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
        // password and refresh grant
        return true;
    }
}
