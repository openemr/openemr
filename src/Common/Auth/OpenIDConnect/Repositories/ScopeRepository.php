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

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($scopeIdentifier)
    {
        // I think we'll hardcode these. Not that many.
        $scopes = [
            'openid' => [
                'description' => 'OpenId Connect',
            ],
            'profile' => [
                'description' => 'Basic details about you',
            ],
            'address' => [
                'description' => 'Current Address',
            ],
            'name' => [
                'description' => 'Registered name.',
            ],
            'phone' => [
                'description' => 'Current phone.',
            ],
            'fhirUser' => [
                'description' => 'Declare FHIR Resources usage.',
            ],
            'api:oemr' => [
                'description' => 'Use Standard Api',
            ],
            'api:fhir' => [
                'description' => 'Use FHIR Api',
            ],
            'api:port' => [
                'description' => 'Use Portal Api',
            ],
            'api:pofh' => [
                'description' => 'Use Portal FHIR Api',
            ],
            'username' => [
                'description' => 'Use Username',
            ],
            'password' => [
                'description' => 'Use Password',
            ],
            'email' => [
                'description' => 'Your email address',
            ],
            'nonce' => [
                'description' => 'Security',
            ],
            'patient/*.read' => [
                'description' => 'Read only access to all information about a patient that currently exists and any information created in the future.',
            ],
            'launch/patient' => [
                'description' => 'Grant an external application the ability to launch and load your patient profile.',
            ]
        ];

        if (array_key_exists($scopeIdentifier, $scopes) === false && stripos($scopeIdentifier, 'site:') === false) {
            return null;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($scopeIdentifier);

        return $scope;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        // If a nonce is passed in, add a nonce scope for id token nonce claim
        if (!empty($_SESSION['nonce'])) {
            $scope = new ScopeEntity();
            $scope->setIdentifier('nonce');
            $scopes[] = $scope;
        }
        // Need a site id for our apis
        if ($_SESSION['site_id']) {
            $siteScope = "site:" . $_SESSION['site_id'];
        } else {
            $siteScope = "site:default";
        }
        $scope = new ScopeEntity();
        $scope->setIdentifier($siteScope);
        $scopes[] = $scope;

        return $scopes;
    }
}
