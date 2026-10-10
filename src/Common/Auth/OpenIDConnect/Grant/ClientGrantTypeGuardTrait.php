<?php

/**
 * Rejects a token or authorization request when the client is not registered for the grant.
 *
 * Call it only after the client has authenticated, so an unauthenticated caller still gets
 * invalid_client and cannot probe which grants a client is registered for (RFC 6749 5.2).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect\Grant;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\ClientGrantTypePolicy;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait ClientGrantTypeGuardTrait
{
    /**
     * @throws OAuthServerException unauthorized_client
     */
    protected function assertClientMayUseGrant(ClientEntity $client, string $grantType, ?LoggerInterface $logger, ?string $redirectUri = null): void
    {
        $policy = new ClientGrantTypePolicy();
        if ($policy->isGrantAllowed($client->getGrantTypes(), $grantType)) {
            return;
        }
        ($logger ?? new NullLogger())->error('OAuth2 client used a grant type it is not registered for', [
            'client' => $client->getIdentifier(),
            'grant_type' => $grantType,
            'registered_grant_types' => $client->getGrantTypes(),
        ]);
        throw $policy->unauthorizedClient($grantType, $redirectUri);
    }
}
