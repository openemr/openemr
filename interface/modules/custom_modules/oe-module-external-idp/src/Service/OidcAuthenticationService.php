<?php

/**
 * Handles the secure OIDC login flow for the External IdP module.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Auth\ExternalAuthenticationResult;
use OpenEMR\Common\Auth\ExternalAuthenticationService;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ExternalIdp\Repository\IdentityRepository;
use OpenEMR\Modules\ExternalIdp\Repository\ProviderRepository;

final class OidcAuthenticationService
{
    private const ALLOWED_SIGNING_ALGORITHMS = ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'];

    public function __construct(
        private readonly ProviderRepository $providerRepository = new ProviderRepository(),
        private readonly IdentityRepository $identityRepository = new IdentityRepository(),
        private readonly OidcStateService $stateService = new OidcStateService(),
    ) {
    }

    /**
     * @param array<string, scalar|null> $request
     */
    public function start(array $request): string
    {
        $siteId = OidcStateService::normalizeSiteId($request['site'] ?? SessionWrapperFactory::getInstance()->getActiveSession()->get('site_id') ?? 'default');
        SessionWrapperFactory::getInstance()->getActiveSession()->set('site_id', $siteId);

        $providerId = $this->resolveProviderId($request, $siteId);
        try {
            $provider = $this->loadProvider($providerId, $siteId, true);

            $state = $this->stateService->store((string) $provider['id'], $siteId, $request);
            $authorizationUrl = $this->buildAuthorizationUrl($provider, $state);
            $this->providerRepository->markStart((int) $provider['id']);

            EventAuditLogger::getInstance()->newEvent('external_login_start', '', (string) $provider['id'], 1, 'authorization request created');
            return $authorizationUrl;
        } catch (\Throwable $exception) {
            $this->recordFailure($providerId, 'authorization request failed', $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param array<string, scalar|null> $request
     */
    public function finish(array $request): string
    {
        $state = $this->requestString($request, 'state');
        if ($state === '') {
            throw new \RuntimeException('Missing OIDC state.');
        }

        $pending = $this->stateService->consume($state);
        $providerId = (int) $pending['provider_id'];

        try {
            $provider = $this->loadProvider($providerId, (string) $pending['site_id'], true);
            SessionWrapperFactory::getInstance()->getActiveSession()->set('site_id', (string) $pending['site_id']);

            if ($this->requestString($request, 'error') !== '') {
                $this->auditFailure((string) $provider['id'], 'authorization response returned an error');
                throw new \RuntimeException('External sign-in was rejected by the identity provider.');
            }

            $code = $this->requestString($request, 'code');
            if ($code === '') {
                $this->auditFailure((string) $provider['id'], 'authorization code was missing');
                throw new \RuntimeException('Missing OIDC authorization code.');
            }

            $tokenResponse = $this->exchangeCodeForTokens($provider, $pending, $code);
            $claims = $this->validateIdToken($provider, $pending, $tokenResponse['id_token'] ?? '');
            $userId = $this->resolveUserId((int) $provider['id'], (string) $claims->sub);
            if ($userId === null) {
                $this->auditFailure((string) $provider['id'], 'no OpenEMR binding exists for the external subject');
                throw new \RuntimeException('No local OpenEMR binding exists for this external identity.');
            }

            $result = new ExternalAuthenticationResult($userId, (string) $provider['id']);
            $completed = (new ExternalAuthenticationService())->complete($result, $pending['login_options']);
            if (!$completed) {
                $this->auditFailure((string) $provider['id'], 'local user validation failed');
                throw new \RuntimeException('The mapped OpenEMR user could not be validated.');
            }

            $this->providerRepository->markSuccess((int) $provider['id'], $userId);
            return (string) $pending['return_target'];
        } catch (\Throwable $exception) {
            $this->recordFailure((int) $provider['id'], 'authorization callback failed', $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $provider
     * @param array<string, mixed> $pending
     */
    private function buildAuthorizationUrl(array $provider, array $pending): string
    {
        $metadata = $this->getDiscoveryMetadata($provider);
        $authorizationEndpoint = (string) $metadata['authorization_endpoint'];
        $query = [
            'response_type' => 'code',
            'client_id' => (string) $provider['client_id'],
            'redirect_uri' => $this->getCallbackUrl(),
            'scope' => (string) $provider['scopes'],
            'state' => (string) $pending['state'],
            'nonce' => (string) $pending['nonce'],
            'code_challenge' => (string) $pending['code_challenge'],
            'code_challenge_method' => 'S256',
        ];

        return $authorizationEndpoint . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param array<string, mixed> $provider
     * @return array<string, mixed>
     */
    private function getDiscoveryMetadata(array $provider): array
    {
        $metadata = json_decode((string) ($provider['discovery_document'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($metadata)) {
            throw new \RuntimeException('OIDC discovery metadata is invalid.');
        }
        return $metadata;
    }

    /**
     * @param array<string, mixed> $provider
     * @param array<string, mixed> $pending
     * @return array<string, mixed>
     */
    private function exchangeCodeForTokens(array $provider, array $pending, string $code): array
    {
        $metadata = $this->getDiscoveryMetadata($provider);
        $tokenEndpoint = (string) ($metadata['token_endpoint'] ?? '');
        if ($tokenEndpoint === '') {
            throw new \RuntimeException('OIDC token endpoint is missing.');
        }

        $client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'allow_redirects' => false,
            'verify' => true,
            'http_errors' => false,
        ]);

        $body = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getCallbackUrl(),
            'client_id' => (string) $provider['client_id'],
            'code_verifier' => (string) $pending['pkce_verifier'],
        ];
        $requestOptions = [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $body,
        ];
        $clientSecret = trim((string) ($provider['client_secret'] ?? ''));
        if ($clientSecret !== '') {
            $requestOptions['auth'] = [(string) $provider['client_id'], $clientSecret, 'basic'];
        }

        try {
            $response = $client->post($tokenEndpoint, $requestOptions);
        } catch (GuzzleException $exception) {
            $this->auditFailure((string) $provider['id'], 'token exchange request failed');
            throw new \RuntimeException('The identity provider token exchange failed.', 0, $exception);
        }

        if ($response->getStatusCode() !== 200) {
            $this->auditFailure((string) $provider['id'], 'token exchange returned a non-success status');
            throw new \RuntimeException('The identity provider token exchange did not return HTTP 200.');
        }

        try {
            $tokenResponse = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->auditFailure((string) $provider['id'], 'token exchange returned invalid JSON');
            throw new \RuntimeException('The identity provider token exchange did not return valid JSON.', 0, $exception);
        }

        if (!is_array($tokenResponse) || empty($tokenResponse['id_token']) || !is_string($tokenResponse['id_token'])) {
            $this->auditFailure((string) $provider['id'], 'token exchange response was missing an id_token');
            throw new \RuntimeException('The identity provider token exchange response was incomplete.');
        }

        return $tokenResponse;
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function validateIdToken(array $provider, array $pending, string $idToken): object
    {
        if ($idToken === '') {
            throw new \RuntimeException('OIDC id_token is missing.');
        }

        $metadata = $this->getDiscoveryMetadata($provider);
        $issuer = (string) ($metadata['issuer'] ?? '');
        if ($issuer === '') {
            throw new \RuntimeException('OIDC issuer metadata is missing.');
        }

        $header = $this->decodeJwtHeader($idToken);
        $alg = (string) ($header['alg'] ?? '');
        if ($alg === '' || !in_array($alg, self::ALLOWED_SIGNING_ALGORITHMS, true)) {
            throw new \RuntimeException('OIDC id_token uses an unsupported signing algorithm.');
        }

        $supportedAlgorithms = array_values(array_intersect(
            self::ALLOWED_SIGNING_ALGORITHMS,
            is_array($metadata['id_token_signing_alg_values_supported'] ?? null) ? $metadata['id_token_signing_alg_values_supported'] : []
        ));
        if (!in_array($alg, $supportedAlgorithms, true)) {
            throw new \RuntimeException('OIDC id_token algorithm is not supported by the provider discovery metadata.');
        }

        $jwksUri = (string) ($metadata['jwks_uri'] ?? '');
        if ($jwksUri === '') {
            throw new \RuntimeException('OIDC JWKS URI is missing.');
        }

        $jwks = $this->fetchJwks($jwksUri);
        $keys = JWK::parseKeySet($jwks);

        $previousLeeway = JWT::$leeway;
        JWT::$leeway = 300;
        try {
            $claims = JWT::decode($idToken, $keys);
        } catch (\Throwable $exception) {
            $this->auditFailure((string) $provider['id'], 'id_token signature or claims validation failed');
            throw new \RuntimeException('The identity provider id_token could not be validated.', 0, $exception);
        } finally {
            JWT::$leeway = $previousLeeway;
        }

        if (!isset($claims->iss) || !is_string($claims->iss) || rtrim($claims->iss, '/') !== rtrim($issuer, '/')) {
            throw new \RuntimeException('OIDC id_token issuer does not match the configured issuer.');
        }

        $audience = $claims->aud ?? null;
        $clientId = (string) $provider['client_id'];
        if (is_string($audience)) {
            if (!hash_equals($clientId, $audience)) {
                throw new \RuntimeException('OIDC id_token audience does not match the configured client ID.');
            }
        } elseif (is_array($audience)) {
            if (!in_array($clientId, $audience, true)) {
                throw new \RuntimeException('OIDC id_token audience does not include the configured client ID.');
            }
            if (count($audience) > 1 && (!isset($claims->azp) || !is_string($claims->azp) || !hash_equals($clientId, $claims->azp))) {
                throw new \RuntimeException('OIDC id_token azp does not match the configured client ID.');
            }
        } else {
            throw new \RuntimeException('OIDC id_token audience is invalid.');
        }

        if (!isset($claims->sub) || !is_string($claims->sub) || trim($claims->sub) === '') {
            throw new \RuntimeException('OIDC id_token subject is missing.');
        }
        if (!isset($claims->nonce) || !is_string($claims->nonce) || !hash_equals((string) $pending['nonce'], $claims->nonce)) {
            throw new \RuntimeException('OIDC id_token nonce did not match.');
        }
        $exp = $this->toTimestamp($claims->exp ?? null);
        if ($exp === null || $exp < time()) {
            throw new \RuntimeException('OIDC id_token is expired.');
        }
        $iat = $this->toTimestamp($claims->iat ?? null);
        if ($iat === null || $iat > (time() + 300)) {
            throw new \RuntimeException('OIDC id_token issue time is invalid.');
        }

        return $claims;
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
            throw new \RuntimeException('Unable to retrieve OIDC JWKS.', 0, $exception);
        }

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('OIDC JWKS endpoint did not return HTTP 200.');
        }

        try {
            $jwks = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('OIDC JWKS endpoint did not return valid JSON.', 0, $exception);
        }

        if (!is_array($jwks) || empty($jwks['keys']) || !is_array($jwks['keys'])) {
            throw new \RuntimeException('OIDC JWKS payload is invalid.');
        }

        return $jwks;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtHeader(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \RuntimeException('OIDC id_token is malformed.');
        }

        try {
            $decoded = json_decode($this->base64UrlDecode($parts[0]), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('OIDC id_token header is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('OIDC id_token header is invalid.');
        }

        return $decoded;
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }

    private function resolveProviderId(array $request, string $siteId): int
    {
        $providerIdValue = $this->requestString($request, 'provider_id');
        if ($providerIdValue !== '' && ctype_digit($providerIdValue)) {
            $providerId = (int) $providerIdValue;
            return $providerId;
        }

        $provider = $this->providerRepository->getEnabledForSite($siteId);
        if (empty($provider['id'])) {
            throw new \RuntimeException('No enabled External IdP provider exists for this site.');
        }

        return (int) $provider['id'];
    }

    private function loadProvider(int $providerId, string $siteId, bool $enabledOnly): array
    {
        if ($providerId < 1) {
            throw new \RuntimeException('OIDC provider ID is invalid.');
        }

        $provider = $this->providerRepository->getById($providerId);
        if (empty($provider) || (string) ($provider['site_id'] ?? '') !== $siteId) {
            throw new \RuntimeException('OIDC provider was not found for the current site.');
        }

        if ($enabledOnly && (int) ($provider['enabled'] ?? 0) !== 1) {
            throw new \RuntimeException('OIDC provider is disabled.');
        }

        return $provider;
    }

    private function resolveUserId(int $providerId, string $subject): ?int
    {
        return $this->identityRepository->findUserId($providerId, $subject);
    }

    private function getCallbackUrl(): string
    {
        $globals = OEGlobalsBag::getInstance();
        $callbackPath = $globals->getWebRoot() . '/interface/modules/custom_modules/oe-module-external-idp/callback.php';
        $forwardedProto = trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $scheme = ($forwardedProto !== '')
            ? strtolower(explode(',', $forwardedProto)[0])
            : ((!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http');
        $host = trim((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));
        if (str_contains($host, ',')) {
            $host = trim(explode(',', $host)[0]);
        }

        if ($host !== '') {
            return $scheme . '://' . $host . $callbackPath;
        }

        $siteBaseUrl = rtrim((string) $globals->get('site_addr_oath'), '/');
        if ($siteBaseUrl !== '') {
            return $siteBaseUrl . $callbackPath;
        }

        return 'http://localhost' . $callbackPath;
    }

    private function auditFailure(string $providerId, string $message): void
    {
        EventAuditLogger::getInstance()->newEvent('external_login_failure', '', $providerId, 0, $message);
    }

    private function recordFailure(int $providerId, string $auditMessage, string $message): void
    {
        if ($providerId < 1) {
            return;
        }

        try {
            $this->providerRepository->markFailure($providerId, $this->normalizeFailureMessage($message));
            $this->auditFailure((string) $providerId, $auditMessage);
        } catch (\Throwable $ignored) {
            // Ignore secondary logging failures; the original exception is what matters.
        }
    }

    private function normalizeFailureMessage(string $message): string
    {
        $message = trim(preg_replace('/[\r\n\t]+/', ' ', $message) ?? '');
        if ($message === '') {
            return 'external authentication failed';
        }

        return substr($message, 0, 1024);
    }

    private function toTimestamp(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @param array<string, scalar|null> $request
     */
    private function requestString(array $request, string $key): string
    {
        $value = $request[$key] ?? null;
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
