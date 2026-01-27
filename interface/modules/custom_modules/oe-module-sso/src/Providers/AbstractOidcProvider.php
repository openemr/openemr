<?php

/**
 * Abstract OIDC Provider - Base implementation for OIDC providers
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Providers;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\SSO\Services\TokenService;

abstract class AbstractOidcProvider implements ProviderInterface
{
    protected array $config = [];
    protected ?array $discoveryDocument = null;
    protected SystemLogger $logger;
    protected TokenService $tokenService;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->logger = new SystemLogger();
        $this->tokenService = new TokenService();
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->discoveryDocument = null; // Reset cached discovery
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['client_id']) && !empty($this->config['client_secret']);
    }

    public function isEnabled(): bool
    {
        return !empty($this->config['enabled']) && $this->isConfigured();
    }

    public function getRedirectUri(): string
    {
        return $GLOBALS['site_addr_oath'] . $GLOBALS['webroot']
            . '/interface/modules/custom_modules/oe-module-sso/public/callback.php';
    }

    /**
     * Get the OIDC discovery URL for this provider
     */
    abstract protected function getDiscoveryUrl(): string;

    public function getDiscoveryDocument(): array
    {
        if ($this->discoveryDocument !== null) {
            return $this->discoveryDocument;
        }

        $url = $this->getDiscoveryUrl();
        $cacheKey = 'sso_discovery_' . md5($url);

        // Try cache first (simple file-based cache)
        $cacheFile = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/' . $cacheKey . '.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            $cached = file_get_contents($cacheFile);
            if ($cached !== false) {
                $this->discoveryDocument = json_decode($cached, true);
                if ($this->discoveryDocument !== null) {
                    return $this->discoveryDocument;
                }
            }
        }

        // Fetch discovery document
        $ch = curl_init($url);
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
            throw new \RuntimeException("Failed to fetch OIDC discovery document from $url");
        }

        $this->discoveryDocument = json_decode($response, true);
        if ($this->discoveryDocument === null) {
            throw new \RuntimeException("Invalid JSON in OIDC discovery document");
        }

        // Cache the discovery document
        file_put_contents($cacheFile, $response);

        return $this->discoveryDocument;
    }

    public function buildAuthorizationUrl(string $state, string $nonce, string $codeChallenge): string
    {
        $discovery = $this->getDiscoveryDocument();
        $authEndpoint = $discovery['authorization_endpoint'] ?? null;

        if (empty($authEndpoint)) {
            throw new \RuntimeException("Authorization endpoint not found in discovery document");
        }

        $params = [
            'client_id' => $this->config['client_id'],
            'response_type' => 'code',
            'redirect_uri' => $this->getRedirectUri(),
            'scope' => implode(' ', $this->getDefaultScopes()),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'response_mode' => 'query',
        ];

        // Add provider-specific parameters
        $params = array_merge($params, $this->getAdditionalAuthParams());

        return $authEndpoint . '?' . http_build_query($params);
    }

    /**
     * Override in subclasses to add provider-specific auth parameters
     */
    protected function getAdditionalAuthParams(): array
    {
        return [];
    }

    public function exchangeCodeForTokens(string $code, string $codeVerifier): array
    {
        $discovery = $this->getDiscoveryDocument();
        $tokenEndpoint = $discovery['token_endpoint'] ?? null;

        if (empty($tokenEndpoint)) {
            throw new \RuntimeException("Token endpoint not found in discovery document");
        }

        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
            'code_verifier' => $codeVerifier,
        ];

        $ch = curl_init($tokenEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("Token request failed: $error");
        }

        $tokens = json_decode($response, true);
        if ($httpCode !== 200 || isset($tokens['error'])) {
            $errorDesc = $tokens['error_description'] ?? $tokens['error'] ?? 'Unknown error';
            throw new \RuntimeException("Token exchange failed: $errorDesc");
        }

        return $tokens;
    }

    public function validateIdToken(string $idToken, string $nonce): array
    {
        $discovery = $this->getDiscoveryDocument();
        $jwksUri = $discovery['jwks_uri'] ?? null;

        if (empty($jwksUri)) {
            throw new \RuntimeException("JWKS URI not found in discovery document");
        }

        // Validate and decode the token
        $claims = $this->tokenService->validateToken(
            $idToken,
            $jwksUri,
            $this->config['client_id'],
            $discovery['issuer'] ?? ''
        );

        // Verify nonce
        if (empty($claims['nonce']) || $claims['nonce'] !== $nonce) {
            throw new \RuntimeException("Nonce mismatch - possible replay attack");
        }

        return $claims;
    }

    public function extractUserInfo(array $claims): array
    {
        return [
            'sub' => $claims['sub'] ?? '',
            'email' => $claims['email'] ?? $claims['preferred_username'] ?? '',
            'name' => $claims['name'] ?? '',
            'given_name' => $claims['given_name'] ?? '',
            'family_name' => $claims['family_name'] ?? '',
            'groups' => $claims['groups'] ?? [],
        ];
    }

    public function buildLogoutUrl(?string $postLogoutRedirect = null): string
    {
        $discovery = $this->getDiscoveryDocument();
        $logoutEndpoint = $discovery['end_session_endpoint'] ?? null;

        if (empty($logoutEndpoint)) {
            // Fallback to just clearing local session
            return $GLOBALS['webroot'] . '/interface/logout.php';
        }

        $params = [];
        if ($postLogoutRedirect !== null) {
            $params['post_logout_redirect_uri'] = $postLogoutRedirect;
        }

        if (!empty($params)) {
            return $logoutEndpoint . '?' . http_build_query($params);
        }

        return $logoutEndpoint;
    }

    public function getDefaultScopes(): array
    {
        return ['openid', 'email', 'profile'];
    }

    public function supportsGroupClaims(): bool
    {
        return false;
    }

    public function getConfigFields(): array
    {
        return [
            'client_id' => [
                'type' => 'text',
                'label' => 'Client ID',
                'required' => true,
                'description' => 'OAuth 2.0 Client ID from your identity provider',
            ],
            'client_secret' => [
                'type' => 'password',
                'label' => 'Client Secret',
                'required' => true,
                'description' => 'OAuth 2.0 Client Secret',
            ],
        ];
    }
}
