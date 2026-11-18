<?php

/**
 * GCIP Authentication Service
 * 
 * <!-- AI-Generated Content Start -->
 * This service handles the core GCIP authentication functionality including
 * OAuth2 flow management, token validation, user authentication, and
 * integration with OpenEMR's user management system.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth\Services
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth\Services;

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\UserService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWK;

/**
 * Service for handling GCIP authentication operations
 */
class GcipAuthService
{
    /**
     * @var GcipConfigService
     */
    private $configService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    /**
     * Google's OAuth2 endpoints - AI-Generated
     */
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const GOOGLE_JWKS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    /**
     * OAuth2 scopes for GCIP authentication - AI-Generated
     */
    private const OAUTH_SCOPES = [
        'openid',
        'email',
        'profile'
    ];

    /**
     * GcipAuthService constructor
     * 
     * <!-- AI-Generated Content Start -->
     * Initializes the authentication service with configuration, user
     * service, and encryption utilities for GCIP authentication processing.
     * <!-- AI-Generated Content End -->
     *
     * @param GcipConfigService $configService
     */
    public function __construct(GcipConfigService $configService)
    {
        $this->configService = $configService;
        $this->userService = new UserService();
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * Generate OAuth2 authorization URL
     * 
     * <!-- AI-Generated Content Start -->
     * Creates the OAuth2 authorization URL that users will be redirected to
     * for GCIP authentication, including all necessary parameters and state.
     * <!-- AI-Generated Content End -->
     *
     * @param string $state Random state parameter for CSRF protection
     * @return string|null Authorization URL or null if not configured
     */
    public function getAuthorizationUrl(string $state): ?string
    {
        if (!$this->configService->isGcipEnabled()) {
            return null;
        }

        $clientId = $this->configService->getClientId();
        $redirectUri = $this->configService->getRedirectUri();
        
        if (!$clientId || !$redirectUri) {
            return null;
        }

        // Build OAuth2 authorization URL - AI-Generated
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => implode(' ', self::OAUTH_SCOPES),
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        // Add tenant-specific parameters if configured - AI-Generated
        $tenantId = $this->configService->getTenantId();
        if ($tenantId) {
            $params['hd'] = $tenantId; // Hosted domain restriction
        }

        return self::GOOGLE_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     * 
     * <!-- AI-Generated Content Start -->
     * Exchanges the OAuth2 authorization code received from Google for
     * access and ID tokens, which are used for user authentication.
     * <!-- AI-Generated Content End -->
     *
     * @param string $code Authorization code from OAuth2 callback
     * @return array|null Token response or null on failure
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        $clientId = $this->configService->getClientId();
        $clientSecret = $this->configService->getClientSecret();
        $redirectUri = $this->configService->getRedirectUri();

        if (!$clientId || !$clientSecret || !$redirectUri) {
            return null;
        }

        // Prepare token exchange request - AI-Generated
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri
        ];

        // Make token exchange request - AI-Generated
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($postData)
            ]
        ]);

        $response = file_get_contents(self::GOOGLE_TOKEN_URL, false, $context);
        
        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Validate and decode ID token
     * 
     * <!-- AI-Generated Content Start -->
     * Validates the JWT ID token received from Google and extracts user
     * information including email, name, and other profile data.
     * <!-- AI-Generated Content End -->
     *
     * @param string $idToken JWT ID token from Google
     * @return array|null Decoded token payload or null on failure
     */
    public function validateIdToken(string $idToken): ?array
    {
        try {
            // Get Google's public keys for JWT verification - AI-Generated
            $jwksData = file_get_contents(self::GOOGLE_JWKS_URL);
            if (!$jwksData) {
                return null;
            }

            $jwks = json_decode($jwksData, true);
            if (!$jwks) {
                return null;
            }

            // Parse and validate the JWT - AI-Generated
            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($idToken, $keys);

            // Verify the token claims - AI-Generated
            $payload = (array) $decoded;
            
            // Verify issuer - AI-Generated
            if (!in_array($payload['iss'] ?? '', ['https://accounts.google.com', 'accounts.google.com'])) {
                return null;
            }

            // Verify audience (client ID) - AI-Generated
            if (($payload['aud'] ?? '') !== $this->configService->getClientId()) {
                return null;
            }

            // Verify expiration - AI-Generated
            if (($payload['exp'] ?? 0) < time()) {
                return null;
            }

            return $payload;
            
        } catch (\Exception $e) {
            error_log("GCIP ID Token validation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate user with GCIP token
     * 
     * <!-- AI-Generated Content Start -->
     * Processes GCIP authentication by validating the user's token and
     * either logging in an existing user or creating a new one if configured.
     * <!-- AI-Generated Content End -->
     *
     * @param array $tokenData Token data from GCIP authentication
     * @return array Authentication result with user data or error
     */
    public function authenticateUser(array $tokenData): array
    {
        $idToken = $tokenData['id_token'] ?? null;
        if (!$idToken) {
            return ['success' => false, 'error' => 'Missing ID token'];
        }

        // Validate the ID token - AI-Generated
        $payload = $this->validateIdToken($idToken);
        if (!$payload) {
            return ['success' => false, 'error' => 'Invalid ID token'];
        }

        $email = $payload['email'] ?? null;
        if (!$email) {
            return ['success' => false, 'error' => 'Email not provided in token'];
        }

        // Check domain restriction if configured - AI-Generated
        $domainRestriction = $this->configService->getConfigValue('gcip_domain_restriction');
        if ($domainRestriction && !$this->isEmailInAllowedDomain($email, $domainRestriction)) {
            return ['success' => false, 'error' => 'Email domain not allowed'];
        }

        // Find or create user - AI-Generated
        $user = $this->findUserByEmail($email);
        if (!$user && $this->configService->getConfigValue('gcip_auto_user_creation', false)) {
            $user = $this->createUserFromToken($payload);
        }

        if (!$user) {
            return ['success' => false, 'error' => 'User not found and auto-creation disabled'];
        }

        // Store encrypted tokens for user session - AI-Generated
        $this->storeUserTokens($user['id'], $tokenData);

        return [
            'success' => true,
            'user' => $user,
            'token_data' => $payload
        ];
    }

    /**
     * Find user by email address
     * 
     * <!-- AI-Generated Content Start -->
     * Searches for an existing OpenEMR user account using the email address
     * provided in the GCIP authentication token.
     * <!-- AI-Generated Content End -->
     *
     * @param string $email User email address
     * @return array|null User data or null if not found
     */
    private function findUserByEmail(string $email): ?array
    {
        // Use OpenEMR's user service to find user by email - AI-Generated
        return $this->userService->getUserByEmail($email);
    }

    /**
     * Create new user from GCIP token
     * 
     * <!-- AI-Generated Content Start -->
     * Creates a new OpenEMR user account using information from the GCIP
     * authentication token when auto-user creation is enabled.
     * <!-- AI-Generated Content End -->
     *
     * @param array $tokenPayload Decoded ID token payload
     * @return array|null Created user data or null on failure
     */
    private function createUserFromToken(array $tokenPayload): ?array
    {
        $email = $tokenPayload['email'] ?? null;
        $firstName = $tokenPayload['given_name'] ?? '';
        $lastName = $tokenPayload['family_name'] ?? '';
        $name = $tokenPayload['name'] ?? ($firstName . ' ' . $lastName);

        if (!$email) {
            return null;
        }

        // Generate username from email - AI-Generated
        $username = strtolower(explode('@', $email)[0]);
        $username = preg_replace('/[^a-z0-9]/', '', $username);

        // Ensure username uniqueness - AI-Generated
        $originalUsername = $username;
        $counter = 1;
        while ($this->userService->getUserByUsername($username)) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        // Create user data - AI-Generated
        $userData = [
            'username' => $username,
            'password' => $this->generateRandomPassword(),
            'fname' => $firstName,
            'lname' => $lastName,
            'email' => $email,
            'authorized' => 1,
            'active' => 1,
            'see_auth' => 1,
            'default_warehouse' => '',
            'irnpool' => '',
            'taxonomy' => '',
            'federaltaxid' => '',
            'notes' => 'Account created via GCIP authentication on ' . date('Y-m-d H:i:s')
        ];

        // Set default role if configured - AI-Generated
        $defaultRole = $this->configService->getConfigValue('gcip_default_role', 'Clinician');
        if ($defaultRole) {
            $userData['role'] = $defaultRole;
        }

        try {
            return $this->userService->createUser($userData);
        } catch (\Exception $e) {
            error_log("Failed to create GCIP user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if email is in allowed domain
     * 
     * <!-- AI-Generated Content Start -->
     * Validates whether the user's email domain is in the list of allowed
     * domains for GCIP authentication when domain restriction is enabled.
     * <!-- AI-Generated Content End -->
     *
     * @param string $email User email address
     * @param string $allowedDomains Comma-separated list of allowed domains
     * @return bool Whether email domain is allowed
     */
    private function isEmailInAllowedDomain(string $email, string $allowedDomains): bool
    {
        $emailDomain = strtolower(explode('@', $email)[1] ?? '');
        $allowedDomainList = array_map('trim', array_map('strtolower', explode(',', $allowedDomains)));
        
        return in_array($emailDomain, $allowedDomainList);
    }

    /**
     * Store encrypted user tokens
     * 
     * <!-- AI-Generated Content Start -->
     * Securely stores the user's GCIP authentication tokens in encrypted
     * format for session management and token refresh operations.
     * <!-- AI-Generated Content End -->
     *
     * @param int $userId User ID
     * @param array $tokenData Token data to store
     */
    private function storeUserTokens(int $userId, array $tokenData): void
    {
        $encryptedTokens = $this->cryptoGen->encryptStandard(json_encode($tokenData));
        
        // Store in user settings or session - AI-Generated
        sqlStatementNoLog(
            "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$userId, 'gcip_tokens', $encryptedTokens]
        );
    }

    /**
     * Clean up user session data
     * 
     * <!-- AI-Generated Content Start -->
     * Removes stored GCIP authentication tokens and session data for a user,
     * typically called during logout to ensure proper cleanup.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Username to clean up
     */
    public function cleanupUserSession(string $username): void
    {
        $user = $this->userService->getUserByUsername($username);
        if (!$user) {
            return;
        }

        // Remove stored tokens - AI-Generated
        sqlStatementNoLog(
            "DELETE FROM user_settings WHERE setting_user = ? AND setting_label = ?",
            [$user['id'], 'gcip_tokens']
        );
    }

    /**
     * Generate random password for auto-created users
     * 
     * <!-- AI-Generated Content Start -->
     * Generates a secure random password for users created automatically
     * through GCIP authentication when they don't have existing accounts.
     * <!-- AI-Generated Content End -->
     *
     * @return string Random password
     */
    private function generateRandomPassword(): string
    {
        return bin2hex(random_bytes(16));
    }
}