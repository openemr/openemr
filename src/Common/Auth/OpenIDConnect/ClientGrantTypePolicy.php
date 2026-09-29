<?php

/**
 * Which OAuth2 grant types a registered client may use.
 *
 * `oauth_clients.grant_types` was stored at registration but never enforced, so a client
 * registered for one grant could use any grant the server had enabled -- most importantly,
 * an authorization-code client could use the password grant and skip the consent screen.
 * This class is the single source of truth for both halves:
 *
 *  - registration: which grant types a client may register for (resolveRegistrationGrantTypes)
 *  - token issuance: whether an authenticated client may use a grant (isGrantAllowed)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect;

use League\OAuth2\Server\Exception\OAuthServerException;

final class ClientGrantTypePolicy
{
    public const AUTHORIZATION_CODE = 'authorization_code';
    public const REFRESH_TOKEN = 'refresh_token';
    public const CLIENT_CREDENTIALS = 'client_credentials';
    public const PASSWORD = 'password';

    /**
     * Grant types a client may register for. Whether the server currently has a grant enabled
     * (e.g. the oauth_password_grant global) is checked separately when the token is issued.
     */
    public const SUPPORTED = [
        self::AUTHORIZATION_CODE,
        self::REFRESH_TOKEN,
        self::CLIENT_CREDENTIALS,
        self::PASSWORD,
    ];

    /**
     * Grants that issue refresh tokens. A client registered for one of them may use the
     * refresh_token grant without listing it, since most clients omit it from registration.
     */
    private const REFRESH_ISSUING = [self::AUTHORIZATION_CODE, self::PASSWORD];

    /**
     * RFC 7591 section 2: when grant_types is omitted the client uses authorization_code only.
     */
    private const DEFAULT_GRANT_TYPES = [self::AUTHORIZATION_CODE];

    /**
     * @param list<string> $registeredGrantTypes the client's stored grant types
     */
    public function isGrantAllowed(array $registeredGrantTypes, string $grantType): bool
    {
        $registered = $this->effectiveGrantTypes($registeredGrantTypes);
        if (in_array($grantType, $registered, true)) {
            return true;
        }
        return $grantType === self::REFRESH_TOKEN
            && array_intersect(self::REFRESH_ISSUING, $registered) !== [];
    }

    /**
     * Stored grant types with the RFC 7591 default applied to a client that has none recorded.
     *
     * @param list<string> $registeredGrantTypes
     * @return list<string>
     */
    public function effectiveGrantTypes(array $registeredGrantTypes): array
    {
        $known = array_values(array_intersect($registeredGrantTypes, self::SUPPORTED));
        return $known === [] ? self::DEFAULT_GRANT_TYPES : $known;
    }

    /**
     * Validate the grant_types a client asks for at registration, or derive them when omitted.
     *
     * Omitted: authorization_code, plus client_credentials for a confidential client that
     * registers system/ scopes with a JWKS -- the shape of a backend-services (bulk export)
     * client, which the registration UI has never sent grant_types for.
     *
     * @param mixed $requested the raw grant_types value from the registration request, null when omitted
     * @return list<string>
     * @throws OAuthServerException invalid_client_metadata
     */
    public function resolveRegistrationGrantTypes(mixed $requested, bool $isConfidential, bool $hasSystemScopes, bool $hasJwks): array
    {
        if ($requested === null) {
            $grantTypes = self::DEFAULT_GRANT_TYPES;
            if ($isConfidential && $hasSystemScopes && $hasJwks) {
                $grantTypes[] = self::CLIENT_CREDENTIALS;
            }
            return $grantTypes;
        }

        if (!is_array($requested) || $requested === []) {
            throw $this->invalidMetadata('grant_types must be a non-empty array');
        }
        $grantTypes = [];
        foreach ($requested as $grantType) {
            if (!is_string($grantType) || !in_array($grantType, self::SUPPORTED, true)) {
                throw $this->invalidMetadata('Unsupported grant_types value');
            }
            if (!in_array($grantType, $grantTypes, true)) {
                $grantTypes[] = $grantType;
            }
        }
        if (in_array(self::CLIENT_CREDENTIALS, $grantTypes, true) && !($isConfidential && $hasJwks)) {
            // client_credentials authenticates only with a private_key_jwt assertion
            throw $this->invalidMetadata('client_credentials requires a confidential client with jwks or jwks_uri');
        }
        if ($grantTypes === [self::REFRESH_TOKEN]) {
            throw $this->invalidMetadata('refresh_token requires a grant that issues refresh tokens');
        }
        return $grantTypes;
    }

    /**
     * RFC 6749 section 5.2: the authenticated client is not authorized to use this grant type.
     */
    public function unauthorizedClient(string $grantType, ?string $redirectUri = null): OAuthServerException
    {
        return new OAuthServerException(
            'The authenticated client is not authorized to use this authorization grant type.',
            0,
            'unauthorized_client',
            400,
            'Grant type ' . $grantType . ' is not registered for this client.',
            $redirectUri
        );
    }

    private function invalidMetadata(string $message): OAuthServerException
    {
        return new OAuthServerException($message, 0, 'invalid_client_metadata');
    }
}
