<?php

namespace OpenEMR\RestControllers\Authorization;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use Psr\Log\LoggerInterface;

class ExternalBearerTokenValidator implements ExternalBearerTokenValidatorInterface
{
    private const ALLOWED_SIGNING_ALGORITHMS = ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'];

    public function __construct(private ?LoggerInterface $logger = null)
    {
        $this->logger ??= ServiceContainer::getLogger();
    }

    public function validateBearerToken(string $rawToken, string $siteId): ?array
    {
        $rawToken = trim($rawToken);
        if ($rawToken === '') {
            $this->logger->debug('External bearer token validation skipped because token was empty', [
                'site_id' => $siteId,
            ]);
            return null;
        }

        try {
            $provider = $this->loadProvider($siteId);
            if ($provider === null) {
                $this->logger->info('External bearer token validation skipped because no enabled external IdP provider was found', [
                    'site_id' => $siteId,
                ]);
                return null;
            }

            $this->logger->debug('External bearer token validation started', [
                'site_id' => $siteId,
                'provider_id' => (int) $provider['id'],
                'issuer_url' => (string) ($provider['issuer_url'] ?? ''),
                'client_id' => (string) ($provider['client_id'] ?? ''),
            ]);
            $claims = $this->validateJwtAgainstProvider($rawToken, $provider);
            $user = $this->loadMappedUser((int) $provider['id'], (string) $claims->sub);
            if ($user === null) {
                $this->logger->warning('External bearer token subject is not mapped to an active OpenEMR user', [
                    'provider_id' => (int) $provider['id'],
                    'subject' => (string) $claims->sub,
                ]);
                return null;
            }

            $scopes = $this->extractScopes($claims);
            $this->logger->info('External bearer token validated successfully', [
                'site_id' => $siteId,
                'provider_id' => (int) $provider['id'],
                'subject' => (string) $claims->sub,
                'user_uuid' => (string) $user['uuid'],
                'scope_count' => count($scopes),
            ]);
            return [
                'oauth_user_id' => (string) $user['uuid'],
                'oauth_client_id' => (string) $provider['client_id'],
                'oauth_access_token_id' => $this->extractTokenIdentifier($rawToken, $claims),
                'oauth_scopes' => $scopes,
                'oauth_is_external' => true,
                'oauth_external_provider_id' => (int) $provider['id'],
                'oauth_external_subject' => (string) $claims->sub,
                'oauth_external_patient' => $this->extractOptionalStringClaim($claims, 'patient'),
            ];
        } catch (\Throwable $exception) {
            $this->logger->warning('External bearer token validation failed', [
                'site_id' => $siteId,
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadProvider(string $siteId): ?array
    {
        try {
            $provider = sqlQuery(
                'SELECT `id`, `issuer_url`, `client_id`, `bearer_audiences`, `discovery_document`, `enabled`
                 FROM `module_external_idp_provider`
                 WHERE `site_id` = ? AND `enabled` = 1',
                [$siteId]
            );
        } catch (\Throwable $exception) {
            $this->logger->debug('External IdP provider table unavailable during bearer token validation', [
                'site_id' => $siteId,
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            return null;
        }

        return is_array($provider) && !empty($provider['id']) ? $provider : null;
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function validateJwtAgainstProvider(string $rawToken, array $provider): object
    {
        $metadata = $this->decodeDiscoveryDocument((string) ($provider['discovery_document'] ?? ''));
        $issuer = rtrim((string) ($metadata['issuer'] ?? $provider['issuer_url'] ?? ''), '/');
        if ($issuer === '') {
            throw new \RuntimeException('External provider issuer is missing.');
        }

        $jwksUri = (string) ($metadata['jwks_uri'] ?? '');
        if ($jwksUri === '') {
            throw new \RuntimeException('External provider JWKS URI is missing.');
        }

        $header = $this->decodeJwtHeader($rawToken);
        $alg = (string) ($header['alg'] ?? '');
        if ($alg === '' || !in_array($alg, self::ALLOWED_SIGNING_ALGORITHMS, true)) {
            throw new \RuntimeException('External bearer token algorithm is not allowed.');
        }
        $this->logger->debug('External bearer token header parsed', [
            'provider_id' => (int) $provider['id'],
            'alg' => $alg,
            'kid' => (string) ($header['kid'] ?? ''),
        ]);

        $advertisedAlgorithms = $metadata['id_token_signing_alg_values_supported'] ?? $metadata['token_endpoint_auth_signing_alg_values_supported'] ?? [];
        if (is_array($advertisedAlgorithms) && $advertisedAlgorithms !== [] && !in_array($alg, $advertisedAlgorithms, true)) {
            throw new \RuntimeException('External bearer token algorithm is not advertised by the provider.');
        }

        $keys = JWK::parseKeySet($this->fetchJwks($jwksUri));
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = 60;
        try {
            $claims = JWT::decode($rawToken, $keys);
        } finally {
            JWT::$leeway = $previousLeeway;
        }

        $tokenIssuer = rtrim($this->extractRequiredStringClaim($claims, 'iss'), '/');
        if (!hash_equals($issuer, $tokenIssuer)) {
            throw new \RuntimeException('External bearer token issuer does not match the configured provider.');
        }

        $this->validateAudience($claims->aud ?? null, $this->buildAcceptedAudiences($provider), $claims->azp ?? null);
        $this->validateTimeClaims($claims);
        $this->extractRequiredStringClaim($claims, 'sub');
        $this->logger->debug('External bearer token claims validated', [
            'provider_id' => (int) $provider['id'],
            'issuer' => $tokenIssuer,
            'subject' => (string) $claims->sub,
        ]);

        return $claims;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeDiscoveryDocument(string $document): array
    {
        if ($document === '') {
            throw new \RuntimeException('External provider discovery document is missing.');
        }

        $metadata = json_decode($document, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($metadata)) {
            throw new \RuntimeException('External provider discovery document is invalid.');
        }

        return $metadata;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtHeader(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \RuntimeException('External bearer token is malformed.');
        }

        $decoded = JWT::urlsafeB64Decode($parts[0]);
        $header = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($header)) {
            throw new \RuntimeException('External bearer token header is invalid.');
        }

        return $header;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchJwks(string $jwksUri): array
    {
        $client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'allow_redirects' => false,
            'verify' => true,
            'http_errors' => false,
        ]);

        try {
            $response = $client->get($jwksUri, ['headers' => ['Accept' => 'application/json']]);
        } catch (GuzzleException $exception) {
            throw new \RuntimeException('Unable to retrieve external provider JWKS.', 0, $exception);
        }

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('External provider JWKS endpoint did not return HTTP 200.');
        }

        $jwks = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($jwks) || !is_array($jwks['keys'] ?? null) || $jwks['keys'] === []) {
            throw new \RuntimeException('External provider JWKS payload is invalid.');
        }

        return $jwks;
    }

    /**
     * @param list<string> $acceptedAudiences
     */
    private function validateAudience(mixed $audience, array $acceptedAudiences, mixed $authorizedParty): void
    {
        if ($acceptedAudiences === []) {
            throw new \RuntimeException('External bearer token validation has no accepted audiences configured.');
        }

        if (is_string($audience)) {
            if (!$this->matchesAcceptedAudience($audience, $acceptedAudiences)) {
                throw new \RuntimeException('External bearer token audience does not match any accepted audience.');
            }
            return;
        }

        if (is_array($audience)) {
            $hasAcceptedAudience = false;
            foreach ($audience as $audienceValue) {
                if (is_string($audienceValue) && $this->matchesAcceptedAudience($audienceValue, $acceptedAudiences)) {
                    $hasAcceptedAudience = true;
                    break;
                }
            }
            if (!$hasAcceptedAudience) {
                throw new \RuntimeException('External bearer token audience does not include any accepted audience.');
            }
            if (count($audience) > 1 && (!is_string($authorizedParty) || !$this->matchesAcceptedAudience($authorizedParty, $acceptedAudiences))) {
                throw new \RuntimeException('External bearer token azp does not match any accepted audience.');
            }
            return;
        }

        throw new \RuntimeException('External bearer token audience is invalid.');
    }

    /**
     * @param array<string, mixed> $provider
     * @return list<string>
     */
    private function buildAcceptedAudiences(array $provider): array
    {
        $acceptedAudiences = [];
        foreach ([
            (string) ($provider['client_id'] ?? ''),
            (string) ($provider['bearer_audiences'] ?? ''),
        ] as $value) {
            if ($value === '') {
                continue;
            }

            $parts = preg_split('/[\s,]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part !== '') {
                    $acceptedAudiences[$part] = $part;
                }
            }
        }

        return array_values($acceptedAudiences);
    }

    /**
     * @param list<string> $acceptedAudiences
     */
    private function matchesAcceptedAudience(string $audience, array $acceptedAudiences): bool
    {
        $audience = trim($audience);
        foreach ($acceptedAudiences as $acceptedAudience) {
            if (hash_equals($acceptedAudience, $audience)) {
                return true;
            }
        }

        return false;
    }

    private function validateTimeClaims(object $claims): void
    {
        $now = time();
        $exp = $this->extractTimestampClaim($claims, 'exp');
        if ($exp === null || $exp < $now) {
            throw new \RuntimeException('External bearer token is expired.');
        }

        $nbf = $this->extractTimestampClaim($claims, 'nbf');
        if ($nbf !== null && $nbf > ($now + 60)) {
            throw new \RuntimeException('External bearer token is not valid yet.');
        }

        $iat = $this->extractTimestampClaim($claims, 'iat');
        if ($iat !== null && $iat > ($now + 60)) {
            throw new \RuntimeException('External bearer token issue time is invalid.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadMappedUser(int $providerId, string $subject): ?array
    {
        $user = sqlQuery(
            'SELECT u.`uuid`, u.`id`
             FROM `module_external_idp_identity` AS i
             INNER JOIN `users` AS u ON u.`id` = i.`user_id`
             WHERE i.`provider_id` = ? AND i.`subject` = ? AND u.`active` = 1
             LIMIT 1',
            [$providerId, $subject]
        );

        if (!is_array($user) || empty($user['uuid'])) {
            return null;
        }

        $user['uuid'] = UuidRegistry::uuidToString($user['uuid']);

        return $user;
    }

    /**
     * @return list<string>
     */
    private function extractScopes(object $claims): array
    {
        $scopes = [];
        $scopeClaim = $claims->scope ?? null;
        if (is_string($scopeClaim) && trim($scopeClaim) !== '') {
            $scopes = preg_split('/\s+/', trim($scopeClaim)) ?: [];
        } elseif (is_array($scopeClaim)) {
            $scopes = array_values(array_filter($scopeClaim, 'is_string'));
        }

        $scpClaim = $claims->scp ?? null;
        if (is_string($scpClaim) && trim($scpClaim) !== '') {
            $scopes = array_merge($scopes, preg_split('/\s+/', trim($scpClaim)) ?: []);
        } elseif (is_array($scpClaim)) {
            $scopes = array_merge($scopes, array_values(array_filter($scpClaim, 'is_string')));
        }

        $scopes = array_values(array_unique(array_filter(array_map(
            static fn($scope): string => trim((string) $scope),
            $scopes
        ))));

        return $scopes;
    }

    private function extractTokenIdentifier(string $rawToken, object $claims): string
    {
        $jti = $this->extractOptionalStringClaim($claims, 'jti');
        return $jti !== null ? $jti : hash('sha256', $rawToken);
    }

    private function extractRequiredStringClaim(object $claims, string $claim): string
    {
        $value = $this->extractOptionalStringClaim($claims, $claim);
        if ($value === null) {
            throw new \RuntimeException('External bearer token claim ' . $claim . ' is missing.');
        }
        return $value;
    }

    private function extractOptionalStringClaim(object $claims, string $claim): ?string
    {
        $value = $claims->{$claim} ?? null;
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
        if (is_numeric($value)) {
            return trim((string) $value);
        }
        return null;
    }

    private function extractTimestampClaim(object $claims, string $claim): ?int
    {
        $value = $claims->{$claim} ?? null;
        if (is_numeric($value)) {
            return (int) $value;
        }
        return null;
    }
}
