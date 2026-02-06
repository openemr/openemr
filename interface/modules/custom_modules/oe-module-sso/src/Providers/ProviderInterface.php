<?php

/**
 * SSO Provider Interface - Contract all providers must implement
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Providers;

interface ProviderInterface
{
    /**
     * Get the unique provider identifier (e.g., 'entra', 'google', 'okta')
     */
    public function getId(): string;

    /**
     * Get the display name for this provider
     */
    public function getName(): string;

    /**
     * Get the icon HTML/SVG for the login button
     */
    public function getIcon(): string;

    /**
     * Check if this provider is fully configured
     */
    public function isConfigured(): bool;

    /**
     * Check if this provider is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get the OIDC discovery document
     *
     * @return array Discovery document contents
     */
    public function getDiscoveryDocument(): array;

    /**
     * Build the authorization URL for initiating the auth flow
     *
     * @param string $state CSRF protection state
     * @param string $nonce Replay protection nonce
     * @param string $codeChallenge PKCE code challenge
     * @return string Authorization URL
     */
    public function buildAuthorizationUrl(string $state, string $nonce, string $codeChallenge): string;

    /**
     * Exchange authorization code for tokens
     *
     * @param string $code Authorization code from callback
     * @param string $codeVerifier PKCE code verifier
     * @return array Token response (access_token, id_token, refresh_token, etc.)
     */
    public function exchangeCodeForTokens(string $code, string $codeVerifier): array;

    /**
     * Validate the ID token and extract claims
     *
     * @param string $idToken The ID token to validate
     * @param string $nonce Expected nonce value
     * @return array Validated claims from the token
     * @throws \Exception If token validation fails
     */
    public function validateIdToken(string $idToken, string $nonce): array;

    /**
     * Extract standardized user info from token claims
     *
     * @param array $claims Token claims
     * @return array Standardized user info with keys: sub, email, name, given_name, family_name, groups
     */
    public function extractUserInfo(array $claims): array;

    /**
     * Build the logout URL for single logout
     *
     * @param string|null $postLogoutRedirect URL to redirect after logout
     * @return string Logout URL
     */
    public function buildLogoutUrl(?string $postLogoutRedirect = null): string;

    /**
     * Get configuration fields required for admin UI
     *
     * @return array Field definitions with type, label, required, description
     */
    public function getConfigFields(): array;

    /**
     * Get default OAuth scopes for this provider
     *
     * @return array Scopes to request
     */
    public function getDefaultScopes(): array;

    /**
     * Check if this provider supports group claims
     */
    public function supportsGroupClaims(): bool;

    /**
     * Set the provider configuration
     *
     * @param array $config Configuration values
     */
    public function setConfig(array $config): void;

    /**
     * Get the redirect URI for OAuth callbacks
     */
    public function getRedirectUri(): string;
}
