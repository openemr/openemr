<?php

/**
 * Verifies RSA signatures on ID tokens using a JWKS.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use InvalidArgumentException;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JWKValidatorException;
use OpenEMR\Common\Utils\HttpUtils;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PublicKey as RsaPublicKey;

final class OidcJwkSigner implements Signer
{
    /**
     * @var list<string>
     */
    public const SUPPORTED_ALGORITHMS = ['RS256', 'RS384', 'RS512'];

    /**
     * @var array<string, mixed>
     */
    private array $headers = [];

    public function __construct(private readonly string $algorithmId)
    {
        if (!in_array($this->algorithmId, self::SUPPORTED_ALGORITHMS, true)) {
            throw new OidcRpException('Unsupported ID token signing algorithm');
        }
    }

    public function algorithmId(): string
    {
        return $this->algorithmId;
    }

    /**
     * @param array<string, mixed> $headers
     */
    public function modifyHeader(array &$headers): void
    {
        $headers['alg'] = $this->algorithmId();
        $this->headers = $headers;
    }

    public function sign(string $payload, Key $key): string
    {
        throw new \BadMethodCallException('OIDC relying-party signer only verifies signatures');
    }

    public function verify(string $expected, string $payload, Key $key): bool
    {
        if (!$key instanceof JsonWebKeySet) {
            throw new InvalidArgumentException('ID token verification requires a JSON Web Key Set');
        }

        $kid = $this->headers['kid'] ?? null;
        $kid = is_string($kid) ? $kid : null;
        [$modulus, $exponent] = $this->rsaFactors($key->getJSONWebKey($kid, $this->algorithmId()));
        $publicKeyXml = <<<XML
        <RSAKeyValue>
          <Modulus>{$modulus}</Modulus>
          <Exponent>{$exponent}</Exponent>
        </RSAKeyValue>
        XML;

        $hash = match ($this->algorithmId) {
            'RS384' => 'sha384',
            'RS512' => 'sha512',
            default => 'sha256',
        };
        $loaded = PublicKeyLoader::load($publicKeyXml);
        if (!$loaded instanceof RsaPublicKey) {
            throw new JWKValidatorException('Malformed key object');
        }
        $padded = $loaded->withPadding(RSA::SIGNATURE_PKCS1);
        if (!$padded instanceof RsaPublicKey) {
            throw new JWKValidatorException('Malformed key object');
        }
        $rsa = $padded->withHash($hash);
        if (!$rsa instanceof RsaPublicKey) {
            throw new JWKValidatorException('Malformed key object');
        }

        return $rsa->verify($payload, $expected);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function rsaFactors(mixed $jwk): array
    {
        if (
            !is_object($jwk)
            || !property_exists($jwk, 'n')
            || !property_exists($jwk, 'e')
            || !is_string($jwk->n)
            || !is_string($jwk->e)
        ) {
            throw new JWKValidatorException('Malformed key object');
        }

        return [
            base64_encode(HttpUtils::base64url_decode($jwk->n)),
            base64_encode(HttpUtils::base64url_decode($jwk->e)),
        ];
    }
}
