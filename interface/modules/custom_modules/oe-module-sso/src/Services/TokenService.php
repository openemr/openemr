<?php

/**
 * Token Service - JWT validation and JWKS handling
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Services;

use OpenEMR\Common\Logging\SystemLogger;

class TokenService
{
    private SystemLogger $logger;
    private array $jwksCache = [];

    public function __construct()
    {
        $this->logger = new SystemLogger();
    }

    /**
     * Validate a JWT token against JWKS
     *
     * @param string $token The JWT to validate
     * @param string $jwksUri URI to fetch JWKS from
     * @param string $expectedAudience Expected audience (client_id)
     * @param string $expectedIssuer Expected issuer
     * @return array Decoded claims
     * @throws \RuntimeException If validation fails
     */
    public function validateToken(
        string $token,
        string $jwksUri,
        string $expectedAudience,
        string $expectedIssuer
    ): array {
        // Split the token
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Invalid JWT format');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // Decode header
        $header = json_decode($this->base64UrlDecode($headerB64), true);
        if (!$header || empty($header['alg']) || empty($header['kid'])) {
            throw new \RuntimeException('Invalid JWT header');
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!$payload) {
            throw new \RuntimeException('Invalid JWT payload');
        }

        // Validate expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \RuntimeException('Token has expired');
        }

        // Validate not before
        if (isset($payload['nbf']) && $payload['nbf'] > time() + 60) {
            throw new \RuntimeException('Token is not yet valid');
        }

        // Validate issuer
        if (!empty($expectedIssuer) && ($payload['iss'] ?? '') !== $expectedIssuer) {
            throw new \RuntimeException('Invalid token issuer');
        }

        // Validate audience
        $aud = $payload['aud'] ?? '';
        if (is_array($aud)) {
            if (!in_array($expectedAudience, $aud)) {
                throw new \RuntimeException('Invalid token audience');
            }
        } elseif ($aud !== $expectedAudience) {
            throw new \RuntimeException('Invalid token audience');
        }

        // Get JWKS and find the matching key
        $jwks = $this->getJwks($jwksUri);
        $key = $this->findKey($jwks, $header['kid']);
        if (!$key) {
            throw new \RuntimeException('Unable to find matching key in JWKS');
        }

        // Verify signature
        if (!$this->verifySignature($headerB64, $payloadB64, $signatureB64, $key, $header['alg'])) {
            throw new \RuntimeException('Invalid token signature');
        }

        return $payload;
    }

    /**
     * Fetch JWKS from URI with caching
     */
    private function getJwks(string $jwksUri): array
    {
        $cacheKey = md5($jwksUri);

        // Check memory cache
        if (isset($this->jwksCache[$cacheKey])) {
            return $this->jwksCache[$cacheKey];
        }

        // Check file cache
        $cacheFile = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/sso_jwks_' . $cacheKey . '.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            $cached = file_get_contents($cacheFile);
            if ($cached !== false) {
                $jwks = json_decode($cached, true);
                if ($jwks !== null) {
                    $this->jwksCache[$cacheKey] = $jwks;
                    return $jwks;
                }
            }
        }

        // Fetch JWKS
        $ch = curl_init($jwksUri);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            throw new \RuntimeException("Failed to fetch JWKS from $jwksUri");
        }

        $jwks = json_decode($response, true);
        if (!$jwks || !isset($jwks['keys'])) {
            throw new \RuntimeException('Invalid JWKS format');
        }

        // Cache the JWKS
        file_put_contents($cacheFile, $response);
        $this->jwksCache[$cacheKey] = $jwks;

        return $jwks;
    }

    /**
     * Find a key in JWKS by kid
     */
    private function findKey(array $jwks, string $kid): ?array
    {
        foreach ($jwks['keys'] as $key) {
            if (($key['kid'] ?? '') === $kid) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Verify JWT signature using RSA
     */
    private function verifySignature(
        string $headerB64,
        string $payloadB64,
        string $signatureB64,
        array $jwk,
        string $algorithm
    ): bool {
        $supportedAlgorithms = [
            'RS256' => OPENSSL_ALGO_SHA256,
            'RS384' => OPENSSL_ALGO_SHA384,
            'RS512' => OPENSSL_ALGO_SHA512,
        ];

        if (!isset($supportedAlgorithms[$algorithm])) {
            throw new \RuntimeException("Unsupported algorithm: $algorithm");
        }

        // Build public key from JWK
        $publicKey = $this->jwkToPublicKey($jwk);
        if (!$publicKey) {
            throw new \RuntimeException('Failed to construct public key from JWK');
        }

        $data = $headerB64 . '.' . $payloadB64;
        $signature = $this->base64UrlDecode($signatureB64);

        $result = openssl_verify($data, $signature, $publicKey, $supportedAlgorithms[$algorithm]);

        return $result === 1;
    }

    /**
     * Convert JWK to OpenSSL public key
     */
    private function jwkToPublicKey(array $jwk): mixed
    {
        if (($jwk['kty'] ?? '') !== 'RSA') {
            throw new \RuntimeException('Only RSA keys are supported');
        }

        $n = $this->base64UrlDecode($jwk['n']);
        $e = $this->base64UrlDecode($jwk['e']);

        // Build RSA public key in DER format
        $modulus = $this->encodeInteger($n);
        $exponent = $this->encodeInteger($e);

        $rsaPublicKey = $this->encodeDerSequence($modulus . $exponent);

        // Wrap in SubjectPublicKeyInfo structure
        $algorithmIdentifier = $this->encodeDerSequence(
            $this->encodeDerOid('1.2.840.113549.1.1.1') . // rsaEncryption OID
            "\x05\x00" // NULL parameters
        );

        $subjectPublicKeyInfo = $this->encodeDerSequence(
            $algorithmIdentifier .
            $this->encodeDerBitString($rsaPublicKey)
        );

        $pem = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n") .
            "-----END PUBLIC KEY-----";

        return openssl_pkey_get_public($pem);
    }

    private function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;
        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function encodeInteger(string $bytes): string
    {
        // Ensure positive integer (add leading 0x00 if high bit set)
        if (ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes;
        }
        return "\x02" . $this->encodeLength(strlen($bytes)) . $bytes;
    }

    private function encodeDerSequence(string $contents): string
    {
        return "\x30" . $this->encodeLength(strlen($contents)) . $contents;
    }

    private function encodeDerBitString(string $contents): string
    {
        return "\x03" . $this->encodeLength(strlen($contents) + 1) . "\x00" . $contents;
    }

    private function encodeDerOid(string $oid): string
    {
        $parts = array_map('intval', explode('.', $oid));
        $encoded = chr($parts[0] * 40 + $parts[1]);

        for ($i = 2; $i < count($parts); $i++) {
            $value = $parts[$i];
            if ($value < 128) {
                $encoded .= chr($value);
            } else {
                $bytes = '';
                while ($value > 0) {
                    $bytes = chr(($value & 0x7f) | ($bytes === '' ? 0 : 0x80)) . $bytes;
                    $value >>= 7;
                }
                $encoded .= $bytes;
            }
        }

        return "\x06" . $this->encodeLength(strlen($encoded)) . $encoded;
    }

    private function encodeLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';
        while ($length > 0) {
            $bytes = chr($length & 0xff) . $bytes;
            $length >>= 8;
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    /**
     * Generate PKCE code verifier
     */
    public function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generate PKCE code challenge from verifier
     */
    public function generateCodeChallenge(string $verifier): string
    {
        $hash = hash('sha256', $verifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    /**
     * Generate a random state parameter
     */
    public function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Generate a random nonce
     */
    public function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }
}
