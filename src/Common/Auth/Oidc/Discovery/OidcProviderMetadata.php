<?php

/**
 * Immutable value object representing an OIDC provider's discovery metadata.
 *
 * Parsed from the provider's /.well-known/openid-configuration document.
 * Only fields required by the OIDC Core spec or needed by this implementation
 * are modeled as typed properties; the full raw document is preserved for
 * forward-compatibility.
 *
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

final readonly class OidcProviderMetadata
{
    /**
     * @param string               $issuer                          REQUIRED. Issuer identifier URL.
     * @param string               $authorizationEndpoint           REQUIRED. Authorization endpoint URL.
     * @param string               $jwksUri                         REQUIRED. JWKS endpoint URL.
     * @param string               $tokenEndpoint                   Token endpoint URL (required for most flows).
     * @param string|null          $userinfoEndpoint                UserInfo endpoint URL.
     * @param string|null          $endSessionEndpoint              RP-Initiated Logout endpoint URL.
     * @param string|null          $revocationEndpoint              Token revocation endpoint URL.
     * @param list<string>         $responseTypesSupported          Supported response types.
     * @param list<string>         $subjectTypesSupported           Supported subject identifier types.
     * @param list<string>         $idTokenSigningAlgValuesSupported Supported ID token signing algorithms.
     * @param list<string>         $scopesSupported                 Supported scopes.
     * @param list<string>         $claimsSupported                 Supported claims.
     * @param array<string, mixed> $raw                             Full raw discovery document for forward-compat.
     */
    public function __construct(
        public string $issuer,
        public string $authorizationEndpoint,
        public string $jwksUri,
        public string $tokenEndpoint = '',
        public ?string $userinfoEndpoint = null,
        public ?string $endSessionEndpoint = null,
        public ?string $revocationEndpoint = null,
        public array $responseTypesSupported = [],
        public array $subjectTypesSupported = [],
        public array $idTokenSigningAlgValuesSupported = [],
        public array $scopesSupported = [],
        public array $claimsSupported = [],
        public array $raw = [],
    ) {
    }

    /**
     * Parse a decoded discovery document into a typed metadata object.
     *
     * @param array<string, mixed> $document Decoded JSON from the discovery endpoint.
     * @throws OidcDiscoveryException If required fields are missing.
     */
    public static function fromDiscoveryDocument(array $document): self
    {
        $missing = [];
        foreach (['issuer', 'authorization_endpoint', 'jwks_uri'] as $field) {
            if (!isset($document[$field]) || !is_string($document[$field]) || $document[$field] === '') {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            throw new OidcDiscoveryException(
                'Discovery document missing required fields: ' . implode(', ', $missing),
            );
        }

        // Validated as non-empty strings above; assert for PHPStan narrowing.
        assert(is_string($document['issuer']));
        assert(is_string($document['authorization_endpoint']));
        assert(is_string($document['jwks_uri']));

        return new self(
            issuer: $document['issuer'],
            authorizationEndpoint: $document['authorization_endpoint'],
            jwksUri: $document['jwks_uri'],
            tokenEndpoint: self::optionalString($document, 'token_endpoint'),
            userinfoEndpoint: self::optionalNullableString($document, 'userinfo_endpoint'),
            endSessionEndpoint: self::optionalNullableString($document, 'end_session_endpoint'),
            revocationEndpoint: self::optionalNullableString($document, 'revocation_endpoint'),
            responseTypesSupported: self::optionalStringList($document, 'response_types_supported'),
            subjectTypesSupported: self::optionalStringList($document, 'subject_types_supported'),
            idTokenSigningAlgValuesSupported: self::optionalStringList($document, 'id_token_signing_alg_values_supported'),
            scopesSupported: self::optionalStringList($document, 'scopes_supported'),
            claimsSupported: self::optionalStringList($document, 'claims_supported'),
            raw: $document,
        );
    }

    /** @param array<string, mixed> $document */
    private static function optionalString(array $document, string $key): string
    {
        $value = $document[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    /** @param array<string, mixed> $document */
    private static function optionalNullableString(array $document, string $key): ?string
    {
        $value = $document[$key] ?? null;
        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $document
     * @return list<string>
     */
    private static function optionalStringList(array $document, string $key): array
    {
        $value = $document[$key] ?? [];
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }
}
