<?php

/**
 * OpenID Provider metadata from discovery.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

final readonly class OidcProviderMetadata
{
    public function __construct(
        public string $issuer,
        public string $authorizationEndpoint,
        public string $tokenEndpoint,
        public string $jwksUri,
        public string $endSessionEndpoint,
    ) {
        if ($this->issuer === '' || $this->authorizationEndpoint === '' || $this->tokenEndpoint === '' || $this->jwksUri === '') {
            throw new OidcRpException('OIDC discovery document is missing required endpoints');
        }
    }

    /**
     * @param array<string, mixed> $document
     */
    public static function fromDiscoveryDocument(array $document, string $expectedIssuer): self
    {
        $issuer = self::stringClaim($document, 'issuer');
        if ($issuer === '' || $issuer !== $expectedIssuer) {
            throw new OidcRpException('OIDC discovery issuer does not match the configured issuer');
        }

        return new self(
            issuer: $issuer,
            authorizationEndpoint: self::stringClaim($document, 'authorization_endpoint'),
            tokenEndpoint: self::stringClaim($document, 'token_endpoint'),
            jwksUri: self::stringClaim($document, 'jwks_uri'),
            endSessionEndpoint: self::stringClaim($document, 'end_session_endpoint'),
        );
    }

    /**
     * @param array<string, mixed> $document
     */
    private static function stringClaim(array $document, string $key): string
    {
        $value = $document[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }
}
