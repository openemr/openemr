<?php

/**
 * Immutable value object representing a single JSON Web Key (JWK).
 *
 * Wraps the raw JWK data from a JWKS endpoint and exposes typed accessors
 * for standard fields. Supports RSA and EC key types.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7517
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

final readonly class JsonWebKey
{
    /**
     * @param string               $kty Key type (e.g. "RSA", "EC").
     * @param string               $kid Key ID.
     * @param string|null          $alg Algorithm (e.g. "RS256", "ES256").
     * @param string|null          $use Key use ("sig" or "enc").
     * @param array<string, mixed> $parameters All raw JWK parameters.
     */
    public function __construct(
        public string $kty,
        public string $kid,
        public ?string $alg = null,
        public ?string $use = null,
        public array $parameters = [],
    ) {
    }

    /**
     * Parse a single JWK from its array representation.
     *
     * @param array<string, mixed> $data Decoded JWK entry from the JWKS "keys" array.
     * @throws JwksException If required fields are missing.
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['kty']) || !is_string($data['kty']) || $data['kty'] === '') {
            throw new JwksException('JWK missing required "kty" field');
        }

        if (!isset($data['kid']) || !is_string($data['kid']) || $data['kid'] === '') {
            throw new JwksException('JWK missing required "kid" field');
        }

        return new self(
            kty: $data['kty'],
            kid: $data['kid'],
            alg: isset($data['alg']) && is_string($data['alg']) ? $data['alg'] : null,
            use: isset($data['use']) && is_string($data['use']) ? $data['use'] : null,
            parameters: $data,
        );
    }

    /**
     * Get a raw JWK parameter by name.
     */
    public function getParameter(string $name): mixed
    {
        return $this->parameters[$name] ?? null;
    }

    public function isSigningKey(): bool
    {
        return $this->use === null || $this->use === 'sig';
    }
}
