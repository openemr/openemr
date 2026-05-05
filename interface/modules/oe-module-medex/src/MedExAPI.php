<?php

/**
 * MedEx API Connection Manager
 *
 * Unified authentication and communication with MedEx servers
 * Uses QueryUtils for database access and includes full type declarations
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx;

require_once __DIR__ . '/MedExConfig.php';

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Http\oeHttp;

class MedExAPI
{
    private ?string $baseUrl = null;
    private ?string $apiKey = null;
    private ?string $practiceId = null;
    private ?string $sessionToken = null;
    private ?int $sessionExpiry = null;
    private ?string $lastError = null;
    private ?array $sessionData = null;

    // Session token validity period (1 hour)
    private const SESSION_TOKEN_LIFETIME = 3600;

    // Services/subscriptions cache TTL (5 minutes - SESSION level inner cache)
    private const SERVICES_CACHE_TTL = 300;

    // DB-level services cache TTL (6 hours) — throttles login calls for idle/unsubscribed practices
    private const SERVICES_DB_CACHE_TTL = 21600;

    // Pricing DB cache TTL (7 days) — pricing changes rarely; cached on initial connect
    private const PRICING_CACHE_TTL = 604800;

    /**
     * Constructor
     * Loads configuration from globals or database
     */
    public function __construct()
    {
        // Always load from medex_prefs table first (most reliable source)
        $this->loadFromPrefs();

        // If still empty, fall back to OpenEMR globals (set via Module Manager settings)
        if (empty($this->apiKey) || empty($this->practiceId)) {
            $this->apiKey = $this->apiKey ?? ($GLOBALS['medex_api_key'] ?? null);
            $this->practiceId = $this->practiceId ?? ($GLOBALS['medex_practice_id'] ?? null);
        }

        // Resolve base URL from single source of truth
        $this->baseUrl = MedExConfig::baseUrl();
    }

    /**
     * Load credentials from legacy medex_prefs table
     */
    private function loadFromPrefs(): void
    {
        try {
            $records = QueryUtils::fetchRecords(
                "SELECT ME_api_key, MedEx_id FROM medex_prefs
                 WHERE ME_username IS NOT NULL AND ME_api_key IS NOT NULL
                 ORDER BY MedEx_lastupdated DESC LIMIT 1"
            );

            $prefs = $records[0] ?? null;

            // Debug logging
            error_log("MedExAPI loadFromPrefs - Query returned: " . print_r($prefs, true));

            if ($prefs) {
                if (empty($this->apiKey) && !empty($prefs['ME_api_key'])) {
                    $this->apiKey = $prefs['ME_api_key'];
                    error_log("MedExAPI loadFromPrefs - Loaded API key (length: " . strlen($this->apiKey) . ")");
                }
                if (empty($this->practiceId) && !empty($prefs['MedEx_id'])) {
                    $this->practiceId = $prefs['MedEx_id'];
                    error_log("MedExAPI loadFromPrefs - Loaded practice ID: " . $this->practiceId);
                }
            } else {
                error_log("MedExAPI loadFromPrefs - No prefs found");
            }

            error_log("MedExAPI loadFromPrefs - Final apiKey: " . ($this->apiKey ? 'SET' : 'NULL') . ", practiceId: " . ($this->practiceId ? $this->practiceId : 'NULL'));
        } catch (SqlQueryException $e) {
            error_log("MedExAPI loadFromPrefs - Error: " . $e->getMessage());
        }
    }

    /**
     * Check if MedEx is globally enabled
     */
    public function isEnabled(): bool
    {
        try {
            $enabled = QueryUtils::fetchSingleValue(
                "SELECT gl_value FROM globals WHERE gl_name = ?",
                'gl_value',
                ['medex_enable']
            );
            return $enabled === '1';
        } catch (SqlQueryException $e) {
            error_log("MedExAPI isEnabled - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if MedEx is configured (has credentials)
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) &&
               !empty($this->practiceId) &&
               !empty($this->baseUrl);
    }

    /**
     * Get MedEx base URL
     */
    public function getBaseUrl(): string
    {
        return (string)$this->baseUrl;
    }

    /**
     * Check if MedEx is both configured and enabled
     */
    public function isActive(): bool
    {
        if (!$this->isEnabled() || !$this->isConfigured()) {
            return false;
        }

        // Check for "Bad Actor" lock or force_disable (Kill Switch)
        try {
            $records = QueryUtils::fetchRecords(
                "SELECT bad_actor_until, bad_actor_message FROM medex_prefs LIMIT 1"
            );
            $prefs = $records[0] ?? null;

            if ($prefs && !empty($prefs['bad_actor_until'])) {
                $lockUntil = strtotime($prefs['bad_actor_until']);
                if ($lockUntil > time()) {
                    // Only log once per hour to avoid log spam
                    static $lastLog = 0;
                    if (time() - $lastLog > 3600) {
                        $msg = !empty($prefs['bad_actor_message']) ? $prefs['bad_actor_message'] : 'Bad Actor lock';
                        error_log("[MedEx] API Access Locked ({$msg}) until " . date('Y-m-d H:i:s', $lockUntil));
                        $lastLog = time();
                    }
                    return false;
                }
            }
        } catch (\Exception $e) {
            // If table doesn't exist or column missing, assume not locked
        }

        return true;
    }

    /**
     * Get human-readable reason why isActive() returned false, if any.
     * Returns null when active/unlocked.
     *
     * @return string|null
     */
    public function getLockReason(): ?string
    {
        try {
            $records = QueryUtils::fetchRecords(
                "SELECT bad_actor_until, bad_actor_message FROM medex_prefs LIMIT 1"
            );
            $prefs = $records[0] ?? null;
            if ($prefs && !empty($prefs['bad_actor_until'])) {
                $lockUntil = strtotime($prefs['bad_actor_until']);
                if ($lockUntil > time()) {
                    $msg = !empty($prefs['bad_actor_message'])
                        ? $prefs['bad_actor_message']
                        : 'Account locked';
                    $until = date('Y-m-d H:i:s', $lockUntil);
                    // 10-year lock = effectively permanent (force_disable)
                    if (($lockUntil - time()) > 315000000) {
                        return $msg;
                    }
                    return $msg . ' (until ' . $until . ')';
                }
            }
        } catch (\Exception $e) {
            // ignore
        }
        return null;
    }

    /**
     * Login to MedEx server and obtain a session token
     * This is the secure authentication method - uses permanent API key once to get temporary session token
     *
     * @param bool $forceRefresh Force a fresh login even if cached token exists
     * @return array Login data including braintree_token, or throws exception on failure
     * @throws \Exception On connection or auth errors
     */
    public function login(bool $forceRefresh = false): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('MedEx not configured');
        }

        // Check if we have a valid cached session token (unless forcing refresh)
        if (!$forceRefresh && $this->hasValidSessionToken()) {
            return $this->sessionData ?? ['success' => true, 'token' => $this->sessionToken];
        }

        // Try to load cached session token from database (unless forcing refresh)
        if (!$forceRefresh) {
            $this->loadCachedSessionToken();
            if ($this->hasValidSessionToken()) {
                return $this->sessionData ?? ['success' => true, 'token' => $this->sessionToken];
            }
        }

        // Need to login fresh - call api/oemr/login with practice_id + api_key
        $url = $this->baseUrl . '/index.php?route=api/oemr/login';

        try {
            $http = oeHttp::setOptions([
                'timeout' => 5,
                'verify' => false,
                'http_errors' => false
            ]);

            $response = $http->asFormParams()->post($url, [
                'site' => $this->practiceId,
                'token' => $this->apiKey
            ]);

            $httpCode = $response->getStatusCode();
            $body = $response->getBody();

            error_log("[MedEx] Login response code: " . $httpCode);
            error_log("[MedEx] Login response: " . substr($body, 0, 200));

            if ($httpCode !== 200) {
                throw new \Exception("Login failed - HTTP {$httpCode}");
            }

            $data = json_decode($body, true);
            if (!$data || empty($data['success'])) {
                throw new \Exception($data['error'] ?? 'Login failed');
            }

            // Store session token
            $this->sessionToken = $data['token'];
            $this->sessionExpiry = time() + self::SESSION_TOKEN_LIFETIME;

            // Cache session token in database
            $this->cacheSessionToken();

            // Store enabled services for later use
            $this->sessionData = $data;

            error_log("[MedEx] Login successful - got session token");
            error_log("[MedEx] customer_group_id from API: " . ($data['customer_group_id'] ?? 'NOT SET'));
            error_log("[MedEx] sessionData keys: " . implode(', ', array_keys($data)));
            return $data;

        } catch (\Exception $e) {
            error_log("[MedEx] Login error: " . $e->getMessage());

            // For network/DNS errors (e.g. after power outage / container restart),
            // fall back to the stale cached session token rather than throwing and
            // blocking the entire OpenEMR calendar tab.
            $errMsg = strtolower($e->getMessage());
            $isNetworkError = (
                strpos($errMsg, 'curl') !== false ||
                strpos($errMsg, 'connect') !== false ||
                strpos($errMsg, 'resolve') !== false ||
                strpos($errMsg, 'timeout') !== false ||
                strpos($errMsg, 'network') !== false
            );

            if ($isNetworkError) {
                try {
                    $records = QueryUtils::fetchRecords(
                        "SELECT session_token, session_token_expiry FROM medex_prefs
                         WHERE ME_username IS NOT NULL AND session_token IS NOT NULL LIMIT 1"
                    );
                    $stale = $records[0] ?? null;
                    if ($stale && !empty($stale['session_token'])) {
                        error_log("[MedEx] Network error — using stale cached session token as fallback");
                        $this->sessionToken = $stale['session_token'];
                        $this->sessionExpiry = time() + 300; // 5-min grace period
                        return [
                            'success'        => true,
                            'token'          => $this->sessionToken,
                            'stale_fallback' => true,
                        ];
                    }
                } catch (\Exception $dbErr) {
                    // ignore DB error during fallback
                }
            }

            throw $e;
        }
    }

    /**
     * Check if current session token is valid
     */
    private function hasValidSessionToken(): bool
    {
        return !empty($this->sessionToken) &&
               $this->sessionExpiry !== null &&
               time() < $this->sessionExpiry;
    }

    /**
     * Load cached session token from database
     */
    private function loadCachedSessionToken(): void
    {
        try {
            $records = QueryUtils::fetchRecords(
                "SELECT session_token, session_token_expiry FROM medex_prefs
                 WHERE ME_username IS NOT NULL AND session_token IS NOT NULL
                 ORDER BY MedEx_lastupdated DESC LIMIT 1"
            );

            $prefs = $records[0] ?? null;

            if ($prefs && !empty($prefs['session_token']) && !empty($prefs['session_token_expiry'])) {
                $expiry = strtotime($prefs['session_token_expiry']);
                if ($expiry > time()) {
                    $this->sessionToken = $prefs['session_token'];
                    $this->sessionExpiry = $expiry;
                    error_log("[MedEx] Loaded cached session token (expires: " . date('Y-m-d H:i:s', $expiry) . ")");
                }
            }
        } catch (\Exception $e) {
            // Columns may not exist yet - that's okay
            error_log("[MedEx] Could not load cached session token: " . $e->getMessage());
        }
    }

    /**
     * Cache session token to database
     */
    private function cacheSessionToken(): void
    {
        try {
            $expiryStr = date('Y-m-d H:i:s', $this->sessionExpiry);

            // First try to update with new columns
            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET session_token = ?, session_token_expiry = ? WHERE ME_username IS NOT NULL",
                [$this->sessionToken, $expiryStr]
            );
            error_log("[MedEx] Cached session token to database");
        } catch (\Exception $e) {
            // Columns may not exist - try to add them
            try {
                QueryUtils::sqlStatementThrowException(
                    "ALTER TABLE medex_prefs ADD COLUMN session_token VARCHAR(255) NULL, ADD COLUMN session_token_expiry DATETIME NULL"
                );
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_prefs SET session_token = ?, session_token_expiry = ? WHERE ME_username IS NOT NULL",
                    [$this->sessionToken, $expiryStr]
                );
            } catch (\Exception $e2) {
                error_log("[MedEx] Could not cache session token: " . $e2->getMessage());
            }
        }
    }

    /**
     * Get the current session token (login if needed)
     *
     * @return string Session token for API calls
     * @throws \Exception If login fails
     */
    public function getSessionToken(): string
    {
        if (!$this->hasValidSessionToken()) {
            $this->login();
        }
        return $this->sessionToken;
    }

    /**
     * Clear cached session token (force re-login on next request)
     */
    public function clearSessionToken(): void
    {
        $this->sessionToken = null;
        $this->sessionExpiry = null;

        try {
            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET session_token = NULL, session_token_expiry = NULL WHERE ME_username IS NOT NULL"
            );
        } catch (\Exception $e) {
            // Ignore - columns may not exist
        }
    }

    /**
     * Test connection to MedEx server
     *
     * @return array{success: bool, error?: string, disabled?: bool, message?: string, practice_name?: string, practice_id?: string, server?: string, services?: array}
     */
    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'MedEx is disabled. Enable it in Global Configuration.',
                'disabled' => true
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'MedEx is not configured. Please provide API key and Practice ID.'
            ];
        }

        try {
            $response = $this->makeRequest('index.php?route=api/oemr/ping', [
                'site' => $this->practiceId
            ], 'GET');

            if (!empty($response['success'])) {
                return [
                    'success' => true,
                    'message' => 'Connected to MedEx successfully',
                    'practice_name' => $response['practice_name'] ?? 'Unknown',
                    'practice_id' => $this->practiceId,
                    'server' => $this->baseUrl,
                    'services' => $response['services'] ?? []
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Unknown error'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fetch session status and enabled services
     * Caches session for 24 hours (Daily check)
     * NOTE: This is separate from login() - uses an already authenticated session
     *
     * @param bool $force Force fresh fetch even if cached
     * @return array|null Session data or null on failure
     */
    public function fetchSessionStatus(bool $force = false): ?array
    {
        if (!$this->isActive()) {
            return null;
        }

        // Check if we have cached session data in memory from login
        if (!$force && $this->sessionData !== null) {
            return $this->sessionData;
        }

        // Check database cache (24 hour TTL)
        if (!$force) {
            try {
                $records = QueryUtils::fetchRecords(
                    "SELECT status, MedEx_lastupdated FROM medex_prefs LIMIT 1"
                );
                $prefs = $records[0] ?? null;

                if ($prefs && !empty($prefs['status'])) {
                    $lastUpdated = strtotime($prefs['MedEx_lastupdated']);
                    $dayAgo = strtotime('-24 hours');

                    if ($lastUpdated > $dayAgo) {
                        // Use cached data
                        $this->sessionData = json_decode($prefs['status'], true);
                        return $this->sessionData;
                    }
                }
            } catch (SqlQueryException $e) {
                error_log("MedExAPI fetchSessionStatus - Cache check error: " . $e->getMessage());
            }
        }

        // Login will populate sessionData with enabled services
        try {
            $this->clearSessionToken(); // Force fresh login
            $this->login();
            return $this->sessionData;
        } catch (\Exception $e) {
            $this->lastError = 'Session fetch error: ' . $e->getMessage();
            return null;
        }
    }

    /**
     * Register new practice with MedEx
     *
     * @param array $data Registration data (email, password, etc.)
     * @return array{success: bool, error?: string, practice_id?: string, message?: string}
     */
    public function register(array $data): array
    {
        $required = [
            'email' => 'Email',
            'password' => 'Password'
        ];

        foreach ($required as $field => $label) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: {$label}"
                ];
            }
        }

        try {
            error_log("[MedEx] Registration attempt - baseUrl: " . $this->baseUrl);
            error_log("[MedEx] Registration data: " . json_encode($data));

            $response = $this->makeRequest('index.php?route=api/oemr/register', $data, 'POST');

            if (!empty($response['success']) && !empty($response['api_key']) && !empty($response['practice_id'])) {
                // Save credentials
                $this->apiKey = $response['api_key'];
                $this->practiceId = $response['practice_id'];

                // Save to globals - use REPLACE to ensure values are saved
                try {
                    QueryUtils::sqlStatementThrowException(
                        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_api_key', 0, ?)",
                        [$this->apiKey]
                    );
                    QueryUtils::sqlStatementThrowException(
                        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_practice_id', 0, ?)",
                        [$this->practiceId]
                    );
                    QueryUtils::sqlStatementThrowException(
                        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_enable', 0, '1')",
                        []
                    );

                    // Enforce single-row invariant: wipe any existing rows before writing.
                    // medex_prefs must have exactly ONE row representing the connected practice.
                    // Using DELETE + INSERT (not REPLACE) ensures a different email/account
                    // re-registering doesn't leave a stale second row behind.
                    QueryUtils::sqlStatementThrowException("DELETE FROM medex_prefs", []);
                    QueryUtils::sqlStatementThrowException(
                        "INSERT INTO medex_prefs (ME_username, ME_api_key, MedEx_id) VALUES (?, ?, ?)",
                        [$data['email'], $this->apiKey, $this->practiceId]
                    );
                } catch (SqlQueryException $e) {
                    error_log("[MedEx] Registration - Database save error: " . $e->getMessage());
                    return [
                        'success' => false,
                        'error' => 'Registration succeeded but failed to save credentials: ' . $e->getMessage()
                    ];
                }

                return [
                    'success' => true,
                    'practice_id' => $this->practiceId,
                    'message' => 'Registration successful'
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Registration failed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Registration error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get enabled services/products for this practice
     *
     * @return array List of enabled services
     */
    public function getEnabledServices(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            $this->bustServicesCache();
        }

        // Return from session cache if fresh enough (avoids a network call on every page load)
        // Key is URL-aware so changing medex_bank_url auto-invalidates the cache
        $urlHash = substr(md5($this->baseUrl ?? ''), 0, 8);
        $cacheKey = 'medex_services_cache_' . $urlHash;
        if (!$forceRefresh && !empty($_SESSION[$cacheKey]['ts']) && (time() - $_SESSION[$cacheKey]['ts']) < self::SERVICES_CACHE_TTL) {
            return $_SESSION[$cacheKey]['data'];
        }

        // DB-level cache (6h TTL): throttles login/network calls for idle/unsubscribed practices.
        // Even an empty result is cached so practices with no subscriptions don't call login() on every session.
        $dbCache   = $this->readStatusCache();
        $dbCheckTs = $dbCache['last_services_check_ts'] ?? 0;
        if (!$forceRefresh && (time() - $dbCheckTs) < self::SERVICES_DB_CACHE_TTL) {
            $cachedServices = $dbCache['last_services_result'] ?? [];
            // Warm the inner session cache from the DB result
            $_SESSION[$cacheKey] = ['data' => $cachedServices, 'ts' => time()];
            return $cachedServices;
        }

        try {
            // forceRefresh=true ensures login() makes a real HTTP call and populates
            // $this->sessionData (including enabled_services). Without force, login() returns
            // early on a valid cached token without setting sessionData, leaving enabled_services empty.
            $this->login(true);
        } catch (\Exception $e) {
            // On connection failure, write a timestamp so we don't hammer the server on every request
            $this->updateStatusCache(['last_services_check_ts' => time(), 'last_services_result' => []]);
            return [];
        }

        // The login() response from the MedEx server is the sole authoritative source for
        // which services are subscribed. enabled_services is populated from medex_subscriptions
        // (WHERE status='active') on the server — it reflects real subscription orders only.
        // Do NOT fall back to pricing/available flags: pricing tells us what's purchasable,
        // not what the practice has actually subscribed to.
        $services = array_values((array)($this->sessionData['enabled_services'] ?? []));

        // Always write DB cache (even empty result) so future calls skip the network for 6h.
        // Also always write enabled_services — the menu function reads this key exclusively and
        // relies on it being up-to-date (including [] when no subscriptions are active).
        // Writing [] here is correct: it means "server confirmed: no active subscriptions."
        $cacheUpdate = [
            'last_services_check_ts' => time(),
            'last_services_result'   => $services,
            'enabled_services'       => $services,  // Always write, even []
        ];
        $this->updateStatusCache($cacheUpdate);
        // Inner session cache only for non-empty (existing behaviour — avoids blank menu flicker).
        if (!empty($services)) {
            $_SESSION[$cacheKey] = ['data' => $services, 'ts' => time()];
        }

        return $services;
    }

    /**
     * Verify entitlement for one service using authoritative server refresh.
     */
    public function hasServiceEntitlement(string $service): bool
    {
        $enabled = $this->getEnabledServices(true);
        if (isset($enabled[$service])) {
            return $enabled[$service] === true || $enabled[$service] === 1;
        }
        return in_array($service, $enabled, true);
    }

    /**
     * Verify at least one service entitlement using authoritative server refresh.
     *
     * @param array<int,string> $services
     */
    public function hasAnyServiceEntitlement(array $services): bool
    {
        $enabled = $this->getEnabledServices(true);
        foreach ($services as $service) {
            if (isset($enabled[$service]) && ($enabled[$service] === true || $enabled[$service] === 1)) {
                return true;
            }
            if (in_array($service, $enabled, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Make HTTP request to MedEx API using oeHttp
     *
     * @param string $endpoint API endpoint path
     * @param array $params Request parameters
     * @param string $method HTTP method (GET or POST)
     * @return array Decoded JSON response
     * @throws \Exception On connection or HTTP errors
     */
    public function makeRequest(string $endpoint, array $params = [], string $method = 'POST'): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        // Determine if this is a public endpoint (no auth required)
        $isPublicEndpoint = strpos($endpoint, 'register') !== false ||
                           strpos($endpoint, 'login') !== false ||
                           strpos($endpoint, 'pricing') !== false || // Pricing is public info
                           strpos($endpoint, 'send_link') !== false ||
                           strpos($endpoint, 'send_secure_chat_link') !== false; // Public: onboarding OTP + external OpenEMR sends

        // Add authentication for non-public endpoints
        // Uses session token (secure) obtained from login, not raw API key
        if (!$isPublicEndpoint) {
            // Login first to get session token
            $sessionToken = $this->getSessionToken();

            // Add session token to request
            // OpenCart routes expect token in URL query string
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $separator . 'token=' . urlencode($sessionToken);
        } else {
            // Public endpoint — try to load any DB-cached session token and attach it so
            // the server can apply customer-group-specific data (e.g. pricing returns
            // 0.00 for DEMO customers only when a valid token is present).
            // loadCachedSessionToken() is a cheap DB-only read; no network call.
            if (empty($this->sessionToken)) {
                $this->loadCachedSessionToken();
            }
            if ($this->hasValidSessionToken() && !empty($this->sessionToken)) {
                $separator = (strpos($url, '?') !== false) ? '&' : '?';
                $url .= $separator . 'token=' . urlencode($this->sessionToken);
            }
        }

        try {
            // Configure HTTP client
            $http = oeHttp::setOptions([
                'timeout' => 5,
                'verify' => false, // Disable SSL verification (match old behavior)
                'http_errors' => false // Don't throw on 4xx/5xx responses
            ]);

            // Debug logging - log params being sent
            error_log("[MedEx] Request endpoint: " . $endpoint);
            error_log("[MedEx] Param count: " . count($params));
            error_log("[MedEx] Param keys: " . implode(', ', array_keys($params)));
            error_log("[MedEx] Final URL: " . preg_replace('/token=[^&]+/', 'token=***', $url));

            // Sanitize params to ensure valid UTF-8 (prevents json_encode errors)
            $params = $this->sanitizeUtf8($params);

            // Make the request
            if ($method === 'GET') {
                $response = $http->get($url, $params);
            } else {
                // OpenCart API expects form-encoded data (not JSON)
                error_log("[MedEx] Using form-encoded POST");
                
                // Use asFormParams() to send data as application/x-www-form-urlencoded
                $response = $http->asFormParams()->post($url, $params);
            }

            $httpCode = $response->getStatusCode();
            $body = $response->getBody();

            // Debug logging
            error_log("[MedEx] HTTP Code: " . $httpCode);
            error_log("[MedEx] Response: " . substr($body, 0, 200));

            if ($httpCode !== 200) {
                throw new \Exception("HTTP {$httpCode} - URL: " . $url);
            }

            $decoded = json_decode($body, true);
            if (!$decoded) {
                throw new \Exception("Invalid JSON response: " . substr($body, 0, 100));
            }

            // Handle "Bad Actor" lock signal from server
            if (isset($decoded['bad_actor_lock']) && is_numeric($decoded['bad_actor_lock'])) {
                $lockSeconds = (int)$decoded['bad_actor_lock'];
                $lockUntil = date('Y-m-d H:i:s', time() + $lockSeconds);
                error_log("[MedEx] Server signaled Bad Actor lock for {$lockSeconds} seconds (until {$lockUntil})");
                try {
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE medex_prefs SET bad_actor_until = ? WHERE 1",
                        [$lockUntil]
                    );
                } catch (\Exception $e) {
                    error_log("[MedEx] Failed to save Bad Actor lock: " . $e->getMessage());
                }
            }

            // Handle force_disable signal from server — disables module at Module Manager level
            // Server sends: {"force_disable": 1, "force_disable_message": "Account suspended"}
            if (!empty($decoded['force_disable'])) {
                $disableMsg = $decoded['force_disable_message'] ?? 'Remotely disabled by MedEx server';
                error_log("[MedEx] Server signaled force_disable: {$disableMsg}");
                try {
                    // Disable the module in Module Manager (mod_active=0)
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE modules SET mod_active = 0 WHERE mod_directory = 'oe-module-medex'",
                        []
                    );
                    // Also clear the globals enable flag
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE globals SET gl_value = '0' WHERE gl_name = 'medex_enable'",
                        []
                    );
                    // Store the reason so the dashboard can surface it
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE medex_prefs SET bad_actor_until = ?, bad_actor_message = ? WHERE 1",
                        [date('Y-m-d H:i:s', time() + 315360000), $disableMsg] // 10 year lock
                    );
                } catch (\Exception $e) {
                    error_log("[MedEx] Failed to apply force_disable: " . $e->getMessage());
                }
            }

            // Handle session expiry - clear token and retry once
            // $decoded['error'] may be a string or an array — coerce to string for strpos()
            $errorStr = $decoded['error'] ?? '';
            if (is_array($errorStr)) {
                $errorStr = json_encode($errorStr);
            }
            if ($errorStr !== '' && strpos((string)$errorStr, 'permission') !== false) {
                error_log("[MedEx] Session may have expired, clearing token");
                $this->clearSessionToken();
            }

            return $decoded;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            error_log("[MedEx] Request error: " . $e->getMessage());
            throw new \Exception("HTTP request failed: " . $e->getMessage());
        }
    }

    /**
     * Recursively sanitize data to ensure valid UTF-8 encoding
     * Prevents json_encode errors with malformed characters from database
     *
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    private function sanitizeUtf8($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeUtf8'], $data);
        }
        if (is_string($data)) {
            // Convert to UTF-8, replacing invalid sequences
            $cleaned = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Remove any remaining invalid/control characters
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleaned);
        }
        return $data;
    }

    /**
     * Render SMS Bot interface
     * This method is called by the MessagesPageListener to render the SMS bot
     * It redirects to the fixed sms_bot.php file with proper authentication
     *
     * @param bool $loggedIn Whether user is logged in
     * @return void
     */
    public function renderSMSBot(bool $loggedIn): void
    {
        if (!$loggedIn) {
            echo "<div class='alert alert-danger'>" . xlt('Not authorized') . "</div>";
            return;
        }
        
        // Get patient ID from request
        $pid = $_REQUEST['pid'] ?? null;
        
        if (empty($pid)) {
            echo "<div class='alert alert-warning'>" . xlt('Patient ID required') . "</div>";
            return;
        }
        
        try {
            // Generate CSRF token for the SMS bot
            $csrfToken = \OpenEMR\Common\Csrf\CsrfUtils::generateCsrfToken();
            
            // Build URL to our fixed SMS bot interface
            $webroot = $GLOBALS['web_root'] ?? '/openemr';
            $smsBotUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot.php';
            $redirectUrl = $smsBotUrl . '?pid=' . urlencode($pid) . '&csrf_token_form=' . urlencode($csrfToken);
            
            // Add any additional parameters
            if (!empty($_REQUEST['m'])) {
                $redirectUrl .= '&m=' . urlencode($_REQUEST['m']);
            }
            if (!empty($_REQUEST['nomenu'])) {
                $redirectUrl .= '&nomenu=' . urlencode($_REQUEST['nomenu']);
            }
            
            // Redirect to the interactive SMS bot
            echo "<script>window.location.href = '" . addslashes($redirectUrl) . "';</script>";
            echo "<noscript>";
            echo "<p>" . xlt('Redirecting to SMS Bot...') . "</p>";
            echo "<p><a href=\"" . htmlspecialchars($redirectUrl) . "\">" . xlt('Click here if you are not redirected automatically') . "</a></p>";
            echo "</noscript>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h3>" . xlt('SMS Bot Error') . "</h3>";
            echo "<p>" . xlt('Error loading SMS Bot: ') . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }

    /**
     * Get the module URL for the MedEx module
     *
     * @return string Module base URL
     */
    public static function getModuleUrl(): string
    {
        $webroot = $GLOBALS['web_root'] ?? '/openemr';
        return $webroot . '/interface/modules/custom_modules/oe-module-medex';
    }

    /**
     * Get last error message
     *
     * @return string|null Last error or null if no error
     */
    /**
     * Get OpenEMR version string
     *
     * @return string
     */
    public function getOpenEMRVersion(): string
    {
        if (isset($GLOBALS['v_major'])) {
            return $GLOBALS['v_major'] . '.' . ($GLOBALS['v_minor'] ?? '0') . '.' . ($GLOBALS['v_patch'] ?? '0');
        }

        // Fallback to database check if globals are not loaded
        try {
            $row = QueryUtils::querySingleRow("SELECT v_major, v_minor, v_patch FROM version LIMIT 1", []);
            if ($row) {
                return $row['v_major'] . '.' . $row['v_minor'] . '.' . $row['v_patch'];
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return '0.0.0';
    }

    /**
     * Check if module should inject into legacy core boards (Recall/Flow Board)
     * Returns false if OpenEMR version is 8.0 or less
     *
     * @return bool
     */
    public function canInjectCoreBoards(): bool
    {
        // Phase 3 injection hooks (.pt-comm-status, #pt_custom_navigation) are present
        // in our patient_tracker.php regardless of OpenEMR version number.
        // The module's JS gracefully degrades if hooks are missing (no errors).
        return true;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get pricing information from MedExBank API
     *
     * @param bool $force When true, always re-fetch from DB (ignores cache).
     *                    Use true for admin dashboard loads where price accuracy matters.
     *                    Non-admin callers use the default 7-day cache.
     * @return array Pricing data for services and add-ons
     */
    public function getPricing(bool $force = false): array
    {
        // When the API is unreachable we return an empty services list rather than
        // stale hardcoded prices. Prices change in OpenCart; a wrong price shown
        // to an admin is worse than showing "pricing unavailable".
        // The UI renders null prices as "—" and retries on the next page load
        // (defaults are never cached, so every request attempts a fresh fetch).
        $emptyFallback = ['success' => false, 'services' => []];

        // Pricing is group-specific: cache is invalidated when customer group changes.
        // The pricing endpoint reads from oc_product_recurring per customer_group_id —
        // the same table OpenCart admin uses to configure recurring billing plans.
        $currentGroup = (int)($this->sessionData['customer_group_id'] ?? 1);

        if (!$force) {
            $dbCache        = $this->readStatusCache();
            $pricingCacheTs  = $dbCache['pricing_cache_ts'] ?? 0;
            $pricingCacheGrp = (int)($dbCache['pricing_cache_group'] ?? 0);
            if (!empty($dbCache['pricing_cache'])
                && $pricingCacheGrp === $currentGroup
                && (time() - $pricingCacheTs) < self::PRICING_CACHE_TTL
            ) {
                return $dbCache['pricing_cache'];
            }
        }

        try {
            // Pass NO explicit token in $params — makeRequest() already appends the
            // session token to the URL for public endpoints (pricing is public).
            // Passing it in $params AND in the URL creates token[0]/token[1] arrays
            // which PHP sees as an array, breaking validateSessionToken() on the server.
            $response = $this->makeRequest('index.php?route=api/oemr/pricing', [], 'GET');
            if ($response && !empty($response['success']) && isset($response['services'])) {
                $this->updateStatusCache([
                    'pricing_cache'       => $response,
                    'pricing_cache_ts'    => time(),
                    'pricing_cache_group' => $currentGroup,
                ]);
                return $response;
            }
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to fetch pricing from API: ' . $e->getMessage());
        }

        // Return empty fallback — do NOT cache so next request retries the API
        return $emptyFallback;
    }

    /**
     * Get active campaigns for this practice
     *
     * Returns list of active reminder and recall campaigns
     *
     * @return array Campaign data
     */
    public function getCampaigns(): array
    {
        try {
            // practice_id is derived from token on server side for security
            $response = $this->makeRequest('/api/campaigns.php', [], 'GET');
            if ($response && isset($response['campaigns'])) {
                return $response['campaigns'];
            }
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to fetch campaigns from API: ' . $e->getMessage());
        }

        // Return empty array if API call fails
        return [];
    }

    /**
     * Get current subscriptions for this practice
     *
     * Returns information about active subscriptions including:
     * - Service status (active/trial/cancelled)
     * - Provider assignments
     * - Billing amounts
     * - Trial end dates
     *
     * @return array Subscription data keyed by service name
     */
    public function getSubscriptions(): array
    {
        // Pricing comes from oc_product_recurring via the pricing() endpoint.
        $pricingResponse = $this->getPricing();
        $customerGroupId = (int)($this->sessionData['customer_group_id'] ?? 1);

        // Convert pricing services to the apiPricing format expected by get_subscriptions.php
        $apiPricing = [];
        $services   = $pricingResponse['services'] ?? [];
        foreach ($services as $svcId => $priceInfo) {
            $apiPricing[$svcId] = [
                'customer_price' => (float)($priceInfo['price'] ?? 0.0),
                'base_price'     => (float)($priceInfo['price'] ?? 0.0),
            ];
        }

        // Read active services.
        // Priority 1: sessionData set by the login(true) call that always precedes this method
        //             in get_subscriptions.php — this is the live server truth.
        // Priority 2: local DB status cache (written by process_subscription + getEnabledServices).
        $enabledServices = [];
        $fromSession = array_values((array)($this->sessionData['enabled_services'] ?? []));
        if (!empty($fromSession)) {
            $enabledServices = $fromSession;
        } else {
            try {
                $dbCache = $this->readStatusCache();
                $cached = $dbCache['enabled_services'] ?? [];
                if (is_array($cached) && !empty($cached)) {
                    $enabledServices = $cached;
                }
            } catch (\Exception $e) {
                error_log('[MedEx] getSubscriptions: could not read status cache: ' . $e->getMessage());
            }
        }

        // Build subscriptions map keyed by service key (matches what get_subscriptions.php expects)
        $subscriptions = [];
        foreach ($enabledServices as $svcKey) {
            $subscriptions[$svcKey] = [
                'status' => 'active',
                'active' => true,
                'service_key' => $svcKey,
                'price' => $apiPricing[$svcKey]['customer_price'] ?? 0.0,
            ];
        }

        return [
            'subscriptions'    => $subscriptions,
            'active_services'  => $enabledServices,
            'pricing'          => $apiPricing,
            'customer_group_id' => $customerGroupId,
        ];
    }

    /**
     * Get service-level preference settings from MedEx SaaS.
     *
     * @param string $serviceKey
     * @return array<string,mixed>
     */
    public function getServicePreferences(string $serviceKey): array
    {
        $serviceKey = strtolower(trim($serviceKey));
        if ($serviceKey === '') {
            return [];
        }

        try {
            $response = $this->makeRequest(
                '/index.php?route=api/oemr/service_prefs&service_key=' . urlencode($serviceKey),
                [],
                'GET'
            );
            if (!empty($response['success']) && !empty($response['settings']) && is_array($response['settings'])) {
                return $response['settings'];
            }
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to fetch service preferences for ' . $serviceKey . ': ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get calendar feeds for this practice
     *
     * @return array{feeds: array}
     */
    public function getCalendarFeeds(): array
    {
        $retried = false;
        retry:
        try {
            error_log('[MedEx] getCalendarFeeds() called' . ($retried ? ' (retry)' : ''));
            // Use absolute path - baseUrl is api.hipaabank.net, we need /api
            $apiUrl = str_replace('/cart/upload', '/api', $this->baseUrl);
            $url = $apiUrl . '/calendar_feeds.php';
            
            $sessionToken = $this->getSessionToken();
            $url .= '?action=list&token=' . urlencode($sessionToken);
            
            error_log('[MedEx] getCalendarFeeds URL: ' . preg_replace('/token=[^&]+/', 'token=***', $url));
            
            $http = oeHttp::setOptions([
                'timeout' => 5,
                'verify' => false,
                'http_errors' => false
            ]);
            
            $response = $http->get($url);
            $httpCode = $response->getStatusCode();
            $body = $response->getBody();
            
            error_log('[MedEx] getCalendarFeeds HTTP ' . $httpCode . ': ' . substr($body, 0, 200));
            
            // If 401, token may be stale - force refresh and retry once
            if ($httpCode === 401 && !$retried) {
                error_log('[MedEx] getCalendarFeeds got 401 - forcing fresh login');
                $this->login(true); // Force refresh
                $retried = true;
                goto retry;
            }
            
            if ($httpCode === 200) {
                $data = json_decode($body, true);
                if ($data && isset($data['feeds'])) {
                    return ['feeds' => $data['feeds']];
                }
            }
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to fetch calendar feeds: ' . $e->getMessage());
            // If first attempt failed with invalid token, try refresh
            if (!$retried && strpos($e->getMessage(), '401') !== false) {
                try {
                    $this->login(true);
                    $retried = true;
                    goto retry;
                } catch (\Exception $e2) {
                    error_log('[MedEx] Retry login also failed: ' . $e2->getMessage());
                }
            }
        }
        return ['feeds' => []];
    }

    /**
     * Create a new calendar feed with provider/facility filters
     *
     * @param array $data Feed configuration (name, providers, facilities, openemr_user_id, openemr_username, feed_password)
     * @return array{success: bool, feed?: array, error?: string}
     */
    public function createCalendarFeed(array $data): array
    {
        try {
            $response = $this->makeRequest('../api/calendar_feeds.php', [
                'action' => 'create',
                'name' => $data['name'] ?? '',
                'providers' => implode(',', $data['providers'] ?? []),
                'facilities' => implode(',', $data['facilities'] ?? []),
                'provider_names' => json_encode($data['provider_names'] ?? []),
                'facility_names' => json_encode($data['facility_names'] ?? []),
                'openemr_user_id' => $data['openemr_user_id'] ?? 0,
                'openemr_username' => $data['openemr_username'] ?? '',
                'feed_password' => $data['feed_password'] ?? ''
            ], 'POST');
            
            if ($response && !empty($response['success'])) {
                return $response;
            }
            return ['success' => false, 'error' => $response['error'] ?? 'Unknown error'];
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to create calendar feed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete a calendar feed
     *
     * @param string $feedId Feed ID to delete
     * @return array{success: bool, error?: string}
     */
    public function deleteCalendarFeed(string $feedId): array
    {
        try {
            $response = $this->makeRequest('../api/calendar_feeds.php', [
                'action' => 'delete',
                'feed_id' => $feedId
            ], 'POST');
            
            if ($response && !empty($response['success'])) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => $response['error'] ?? 'Unknown error'];
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to delete calendar feed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get current configuration
     *
     * @return array{server_url: string|null, practice_id: string|null, api_key: string|null, configured: bool}
     */
    public function getConfig(): array
    {
        return [
            'server_url' => $this->baseUrl,
            'practice_id' => $this->practiceId,
            'api_key' => $this->apiKey ? substr($this->apiKey, 0, 20) . '...' : null,
            'configured' => $this->isConfigured()
        ];
    }

    /**
     * Generate SSO token for embedding SaaS pages
     * Creates a time-limited, signed token for secure iframe embedding
     *
     * @param int $ttl Time-to-live in seconds (default: 3600 = 1 hour)
     * @return string|null SSO token or null if not configured
     */
    public function generateSSOToken(int $ttl = 3600): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $payload = [
            'practice_id' => $this->practiceId,
            'issued_at' => time(),
            'expires_at' => time() + $ttl,
            'nonce' => bin2hex(random_bytes(16))
        ];

        // Sign the payload with API key (HMAC-SHA256)
        $signature = hash_hmac('sha256', json_encode($payload), $this->apiKey);

        // Combine payload and signature
        $token = base64_encode(json_encode([
            'payload' => $payload,
            'signature' => $signature
        ]));

        return $token;
    }

    /**
     * Get SaaS dashboard URL with SSO token for embedding
     *
     * @param string $page Page path (e.g., 'dashboard', 'settings', 'billing')
     * @param array $params Additional query parameters
     * @return string|null Full URL with SSO token or null if not configured
     */
    public function getSaaSUrl(string $page = 'dashboard', array $params = []): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Generate SSO token
        $ssoToken = $this->generateSSOToken();
        if (!$ssoToken) {
            return null;
        }

        // Build browser-facing URL using public endpoint rewrite (not internal k8s DNS).
        $url = rtrim(MedExConfig::publicBaseUrl(), '/') . '/index.php';

        // Map page names to MedEx routes
        $routes = [
            'dashboard' => 'account/account',
            'calendar' => 'information/calendar',
            'settings' => 'account/account',
            'register' => 'account/register',
            'campaigns' => 'information/campaigns',
            'billing' => 'account/subscription'
        ];

        $route = $routes[$page] ?? $routes['dashboard'];

        // Build query parameters
        $queryParams = array_merge([
            'route' => $route,
            'embed' => '1',
            'sso_token' => $ssoToken,
            'practice_id' => $this->practiceId
        ], $params);

        return $url . '?' . http_build_query($queryParams);
    }

    /**
     * Sync patient data with MedEx server
     *
     * @param string|int $pid Patient ID
     * @return array{success: bool, error?: string}
     */
    public function syncPatient($pid): array
    {
        try {
            // Get patient data from OpenEMR
            $patientRecords = QueryUtils::fetchRecords(
                "SELECT pid, fname, lname, mname, phone_cell, phone_home, email, 
                        street, city, state, postal_code, hipaa_allowsms, hipaa_allowemail, 
                        hipaa_voice, language, DOB, sex
                 FROM patient_data WHERE pid = ?",
                [$pid]
            );
            
            $patient = $patientRecords[0] ?? null;
            if (!$patient) {
                return ['success' => false, 'error' => 'Patient not found'];
            }

            // Get upcoming appointments
            $appointments = QueryUtils::fetchRecords(
                "SELECT pc_eid, pc_eventDate, pc_startTime, pc_facility, pc_aid
                 FROM openemr_postcalendar_events 
                 WHERE pc_pid = ? AND pc_eventDate >= CURDATE() 
                 ORDER BY pc_eventDate LIMIT 10",
                [$pid]
            );

            // Prepare patient sync data
            $syncData = [
                'patient' => $patient,
                'appointments' => $appointments,
                'practice_id' => $this->practiceId,
                'sync_timestamp' => date('Y-m-d H:i:s')
            ];

            // Send to MedEx API
            $response = $this->makeRequest('index.php?route=api/patient/sync', $syncData);
            
            if (isset($response['success'])) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => $response['error'] ?? 'Sync failed'];
            }
            
        } catch (\Exception $e) {
            error_log("MedExAPI syncPatient failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Insert an outgoing medex message record (compat helper)
     * Returns true on success, false on failure.
     */
    public function insertOutgoing(string $msg_pc_eid, string $msg_type, int|string $msg_reply, string $msg_extra_text): bool
    {
        try {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO medex_outgoing (msg_pc_eid, msg_type, msg_reply, msg_extra_text) VALUES (?,?,?,?)",
                [$msg_pc_eid, $msg_type, $msg_reply, $msg_extra_text]
            );
            return true;
        } catch (SqlQueryException $e) {
            error_log("MedExAPI insertOutgoing failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get last outgoing medex row for a patient id (compat helper)
     * Returns associative array or null.
     */
    public function getLastOutgoing(int $pid): ?array
    {
        try {
            $records = QueryUtils::fetchRecords(
                "SELECT * FROM medex_outgoing WHERE msg_pid = ? ORDER BY msg_uid DESC LIMIT 1",
                [$pid]
            );
            return $records[0] ?? null;
        } catch (SqlQueryException $e) {
            error_log("MedExAPI getLastOutgoing failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete recall-related data
     *
     * @param int $pid Patient ID
     * @return bool
     */
    public function deleteRecallData(int $pid): bool
    {
        try {
            // Delete outgoing messages for this recall
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?",
                ['recall_' . $pid]
            );
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Save medex preferences (compat helper)
     * Accepts an array of values matching legacy prepared statement order.
     */
    public function savePreferences(array $values): bool
    {
        try {
            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET ME_facilities = ?, ME_providers = ?, ME_hipaa_default_override = ?, PHONE_country_code = ?, POSTCARDS_local = ?, POSTCARDS_remote = ?, LABELS_local = ?, LABELS_choice = ?, combine_time = ?, postcard_top = ?",
                $values
            );
            return true;
        } catch (SqlQueryException $e) {
            error_log("MedExAPI savePreferences failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create cart/subscription request with MedEx API
     * Sends selected services and providers to create a cart session
     *
     * @param array $cartData Cart items with services and quantities
     * @return array Response with cart_id, total, and checkout details
     */
    public function createCart(array $cartData): array
    {
        try {
            $response = $this->makeRequest('index.php?route=api/oemr/create_cart', $cartData, 'POST');

            if ($response && isset($response['cart_id'])) {
                return [
                    'success' => true,
                    'cart_id' => $response['cart_id'],
                    'total' => $response['total'] ?? 0,
                    'items' => $response['items'] ?? [],
                    'braintree_token' => $response['braintree_token'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create cart'
            ];
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to create cart: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process payment and activate subscription
     *
     * @param string $cartId Cart ID from createCart
     * @param string $paymentNonce Braintree payment nonce from client
     * @return array Response with success status and subscription details
     */
    public function processPayment(string $cartId, string $paymentNonce): array
    {
        try {
            $response = $this->makeRequest('index.php?route=api/oemr/checkout', [
                'cart_id' => $cartId,
                'payment_nonce' => $paymentNonce
            ], 'POST');

            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'subscription_id' => $response['subscription_id'] ?? null,
                    'services' => $response['services'] ?? []
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Payment processing failed'
            ];
        } catch (\Exception $e) {
            error_log('[MedEx] Payment processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync practice data (providers, facilities, categories, etc.) to MedEx
     *
     * @return array Response with success status
     */
    public function syncPracticeData(): array
    {
        try {
            error_log('[MedEx] Starting practice data sync...');

            // Build callback URL using public-facing base URL.
            // In K8s/reverse-proxy environments $_SERVER['SERVER_NAME'] is an internal
            // hostname; use the 'medex_callback_base_url' global (set to the public URL,
            // e.g. https://emr-704.hipaabank.net) to override it.
            $callbackBase = $GLOBALS['medex_callback_base_url']
                ?? $GLOBALS['site_addr_oath']
                ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));
            $callbackToken = \OpenEMR\Common\Database\QueryUtils::fetchSingleValue(
                "SELECT gl_value FROM globals WHERE gl_name = ?",
                'gl_value',
                ['medex_callback_token']
            ) ?? '';
            $callbackSiteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_SESSION['site_id'] ?? 'default'));
            if ($callbackSiteId === '') {
                $callbackSiteId = 'default';
            }
            $callback = rtrim($callbackBase, '/')
                . '/interface/modules/custom_modules/oe-module-medex/public/callback.php'
                . '?token=' . rawurlencode($callbackToken)
                . '&site=' . rawurlencode($callbackSiteId);

            // Get selected providers from preferences
            $prefs = sqlQuery("SELECT ME_providers, ME_facilities FROM medex_prefs LIMIT 1");
            $selectedProviders = !empty($prefs['ME_providers']) ? explode('|', $prefs['ME_providers']) : [];
            $selectedFacilities = !empty($prefs['ME_facilities']) ? explode('|', $prefs['ME_facilities']) : [];

            // Build sync data array
            $syncData = [
                'callback_url' => $callback,
                'providers' => [],
                'facilities' => [],
                'categories' => [],
                'apptstats' => [],
                'checkedOut' => [],
                'clinical_reminders' => []
            ];

            // Get providers
            if (!empty($selectedProviders)) {
                $providerList = implode(',', array_map('intval', $selectedProviders));
                $result = sqlStatement("SELECT * FROM users WHERE id IN ($providerList)");
                while ($row = sqlFetchArray($result)) {
                    $syncData['providers'][] = $row;
                }
            }

            // Get facilities
            $result = sqlStatement("SELECT * FROM facility WHERE service_location='1'");
            while ($row = sqlFetchArray($result)) {
                if (empty($selectedFacilities) || in_array($row['id'], $selectedFacilities)) {
                    $row['messages_active'] = '1';
                    $syncData['facilities'][] = $row;
                }
            }

            // Get appointment categories
            $result = sqlStatement("SELECT pc_catid, pc_catname, pc_catdesc, pc_catcolor, pc_seq
                                   FROM openemr_postcalendar_categories
                                   WHERE pc_active = 1 AND pc_cattype='0'
                                   ORDER BY pc_catid");
            while ($row = sqlFetchArray($result)) {
                $syncData['categories'][] = $row;
            }

            // Get appointment statuses
            $result = sqlStatement("SELECT * FROM list_options WHERE list_id='apptstat' AND activity='1'");
            while ($row = sqlFetchArray($result)) {
                $syncData['apptstats'][] = $row;
            }

            // Get checked-out statuses
            $result = sqlStatement("SELECT option_id FROM list_options
                                   WHERE toggle_setting_2='1' AND list_id='apptstat' AND activity='1'");
            while ($row = sqlFetchArray($result)) {
                $syncData['checkedOut'][] = $row;
            }

            // Get clinical reminders (if cdr engine is available)
            if (function_exists('sqlStatementCdrEngine')) {
                $sql = "SELECT * FROM clinical_rules, list_options, rule_action, rule_action_item
                       WHERE clinical_rules.pid=0
                       AND clinical_rules.patient_reminder_flag = 1
                       AND clinical_rules.id = list_options.option_id
                       AND clinical_rules.id = rule_action.id
                       AND list_options.option_id = clinical_rules.id
                       AND rule_action.category = rule_action_item.category
                       AND rule_action.item = rule_action_item.item";
                $result = sqlStatementCdrEngine($sql);
                while ($row = sqlFetchArray($result)) {
                    $syncData['clinical_reminders'][] = $row;
                }
            }

            error_log('[MedEx] Sync data prepared - Providers: ' . count($syncData['providers']) .
                     ', Facilities: ' . count($syncData['facilities']) .
                     ', Categories: ' . count($syncData['categories']));

            // Send to MedEx via custom/addpractice endpoint
            // Note: makeRequest() automatically adds encrypted API key as 'token' param
            // No need for session token - this endpoint uses encrypted API key authentication
            $response = $this->makeRequest('index.php?route=api/custom/addpractice', $syncData, 'POST');

            if ($response && isset($response['success']) && $response['success']) {
                // Sync succeeded — treat the server response as the authoritative source of truth.
                // Wipe local service + pricing caches so the next page load re-fetches fresh
                // data from MedEx (pricing TTL, service TTL both zeroed).
                $cacheUpdate = [
                    'pricing_cache_ts'       => 0,  // force re-fetch of available services / pricing
                    'last_services_check_ts' => 0,  // force re-fetch of enabled_services on next load
                    'last_services_result'   => [],
                ];
                // If addpractice returned enabled_services use it immediately — no extra login() needed.
                if (!empty($response['enabled_services'])) {
                    $cacheUpdate['enabled_services']       = array_values($response['enabled_services']);
                    $cacheUpdate['last_services_result']   = array_values($response['enabled_services']);
                    $cacheUpdate['last_services_check_ts'] = time(); // mark fresh so we don't re-hit server
                    error_log('[MedEx] Post-sync enabled_services from server: ' . implode(', ', $response['enabled_services']));
                } else {
                    $cacheUpdate['enabled_services'] = [];
                }
                $this->updateStatusCache($cacheUpdate);
                // Clear the PHP session-level services cache so the menu reflects the new state
                // immediately on the next page load (no stale in-request cache).
                $urlHash  = substr(md5($this->baseUrl ?? ''), 0, 8);
                $cacheKey = 'medex_services_cache_' . $urlHash;
                unset($_SESSION[$cacheKey]);
                error_log('[MedEx] Practice data sync completed successfully — caches wiped');
                return ['success' => true, 'message' => 'Practice data synced successfully'];
            }

            $errorMsg = $response['error'] ?? 'Unknown error during sync';
            error_log('[MedEx] Practice data sync failed: ' . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];

        } catch (\Exception $e) {
            error_log('[MedEx] Practice data sync exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update service-specific provider flags in MedEx
     *
     * Sets P_appt, P_recall, or other service flags for selected providers
     *
     * @param string $serviceId Service identifier (e.g., 'APPOINTMENT_REMINDERS', 'RECALL')
     * @param string $providers Pipe-separated list of provider IDs
     * @return bool Success status
     */
    public function updateServiceProviders(string $serviceId, string $providers): bool
    {
        try {
            // Map service IDs to database column flags (case-insensitive)
            $serviceMap = [
                'appointment_reminders' => 'P_appt',
                'APPOINTMENT_REMINDERS' => 'P_appt',
                'recall' => 'P_recall',
                'RECALL' => 'P_recall',
                // Add more services as needed
            ];

            $flagColumn = $serviceMap[$serviceId] ?? null;
            if (!$flagColumn) {
                error_log("[MedEx] Unknown service ID: {$serviceId}");
                return false;
            }

            // Get practice ID
            $config = $this->getConfig();
            $practiceId = $config['practice_id'] ?? null;

            if (empty($practiceId)) {
                error_log("[MedEx] Cannot update service providers - practice ID not configured");
                return false;
            }

            // Build request data matching what activeProviders() expects
            $data = [
                'practice_id' => $practiceId,
                'msg_practice_id' => $practiceId,
                'ME_providers' => $providers,
                'service' => $serviceId,
                'flag_column' => $flagColumn
            ];

            error_log("[MedEx] Updating service providers - Service: {$serviceId}, Flag: {$flagColumn}, Providers: {$providers}");

            // Send to MedEx API endpoint
            $response = $this->makeRequest('/api/update_service_providers.php', $data, 'POST');

            if ($response && isset($response['success']) && $response['success']) {
                error_log("[MedEx] Service provider flags updated successfully");
                return true;
            }

            $errorMsg = $response['error'] ?? 'Unknown error updating service providers';
            error_log("[MedEx] Failed to update service providers: {$errorMsg}");
            return false;

        } catch (\Exception $e) {
            error_log("[MedEx] Exception updating service providers: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a secure chat link to a patient via SMS or email
     *
     * @param int $pid Patient ID
     * @param string $destination Phone number or email address
     * @param string $chatUrl The secure chat URL
     * @param string $method 'sms' or 'email'
     * @param string $token Optional: The secure chat token for API registration
     * @param string $userInitials Optional: User initials for message identification
     * @return bool Success status
     */
    public function sendSecureChatLink(int $pid, string $destination, string $chatUrl, string $method = 'sms', string $token = '', string $userInitials = ''): bool
    {
        try {
            // Get patient data
            $patient = sqlQuery("SELECT fname, lname FROM patient_data WHERE pid = ?", [$pid]);
            if (!$patient) {
                error_log("[MedEx Secure Chat] Patient not found: {$pid}");
                return false;
            }

            $patientName = $patient['fname'] . ' ' . $patient['lname'];

            // Get practice info
            $config = $this->getConfig();
            $practiceId = $config['practice_id'] ?? null;
            $practiceName = $GLOBALS['openemr_name'] ?? 'Your Healthcare Provider';

            if (empty($practiceId)) {
                error_log("[MedEx Secure Chat] Practice ID not configured");
                return false;
            }

            // Build message
            if ($method === 'sms') {
                $message = "Hello {$patient['fname']}, {$practiceName} has sent you a secure message. Click here to view: {$chatUrl}";
            } else {
                $message = "<p>Hello {$patient['fname']},</p>" .
                           "<p>{$practiceName} has sent you a secure message.</p>" .
                           "<p><a href=\"{$chatUrl}\" style=\"background:#007bff;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;\">View Message</a></p>" .
                           "<p>Or copy this link: {$chatUrl}</p>" .
                           "<p><small>This link expires in 72 hours.</small></p>";
            }

            // Send via MedEx API
            $data = [
                'practice_id' => $practiceId,
                'pid' => $pid,
                'destination' => $destination,
                'method' => $method,
                'message' => $message,
                'subject' => 'Secure Message from ' . $practiceName,
                'type' => 'secure_chat_link',
                'token' => $token,  // Pass token if provided
                'user_initials' => $userInitials  // Pass user initials for message tracking
            ];

            $response = $this->makeRequest('/index.php?route=api/send_secure_chat_link', $data, 'POST');

            if ($response && isset($response['success']) && $response['success']) {
                error_log("[MedEx Secure Chat] Link sent successfully via {$method} to {$destination}");
                return true;
            }

            $errorMsg = $response['error'] ?? 'Unknown error sending secure chat link';
            error_log("[MedEx Secure Chat] Failed to send: {$errorMsg}");
            return false;

        } catch (\Exception $e) {
            error_log("[MedEx Secure Chat] Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register secure chat token on MedEx SaaS
     *
     * Called after generating a token to ensure it's registered on the MedEx SaaS side
     * so chat_patient.php can validate it
     * Also passes OpenEMR integration settings for dual-write to portal messaging
     *
     * @param int $pid Patient ID
     * @param string $token The secure chat token
     * @param string $expiresAt Expiration timestamp
     * @param bool $isProvider Whether this is a provider token (can see everything)
     * @param string $userType 'patient', 'provider', or 'both'
     * @return bool Success status
     */
    public function registerSecureChatToken(int $pid, string $token, string $expiresAt = '', bool $isProvider = false, string $userType = 'patient'): bool
    {
        try {
            // Get practice info
            $config = $this->getConfig();
            $practiceId = $config['practice_id'] ?? null;

            if (empty($practiceId)) {
                error_log("[MedEx Secure Chat] Practice ID not configured, cannot register token");
                return false;
            }

            if (empty($expiresAt)) {
                $expiresAt = date('Y-m-d H:i:s', strtotime('+72 hours'));
            }

            // Get API key for OpenEMR integration - use existing MedEx API key
            $apiKeyResult = sqlQuery("SELECT ME_api_key FROM medex_prefs WHERE MedEx_id = ?", [$practiceId]);
            
            if (empty($apiKeyResult['ME_api_key'])) {
                error_log("[MedEx Secure Chat] Warning: No MedEx API key found for practice {$practiceId}");
                $apiKey = 'MISSING_API_KEY'; // Fallback to avoid breaking integration
            } else {
                // Use the existing MedEx API key for authentication
                $apiKey = $apiKeyResult['ME_api_key'];
            }

            // Get OpenEMR base URL
            $openemrUrl = $GLOBALS['webroot'] ?? '';
            if (empty($openemrUrl)) {
                // Fallback to constructing from server variables
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $openemrUrl = $protocol . '://' . $host;
            }

            // Register token on MedEx SaaS side with OpenEMR integration settings
            $data = [
                'practice_id' => $practiceId,
                'pid' => $pid,
                'token' => $token,
                'expires_at' => $expiresAt,
                'is_provider' => $isProvider ? 1 : 0,
                'user_type' => $userType,
                'openemr_url' => $openemrUrl,
                'openemr_api_key' => $apiKey,
                'provider_fname' => $providerInfo['fname'] ?? '',
                'provider_lname' => $providerInfo['lname'] ?? '',
                'provider_initials' => $providerInfo['initials'] ?? '',
                'provider_npi' => $providerInfo['npi'] ?? '',
                'provider_id' => $providerInfo['id'] ?? ''
            ];

            // OpenCart route - endpoint path only (makeRequest adds the baseUrl)
            $response = $this->makeRequest('/index.php?route=api/secure_chat/send_link', $data, 'POST');

            if ($response && isset($response['success']) && $response['success']) {
                error_log("[MedEx Secure Chat] Token registered successfully on MedEx SaaS for {$userType} with portal sync");
                return true;
            }

            $errorMsg = $response['error'] ?? 'Unknown error registering token';
            error_log("[MedEx Secure Chat] Failed to register token: {$errorMsg}");
            return false;

        } catch (\Exception $e) {
            error_log("[MedEx Secure Chat] Exception registering token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get secure chat history for a patient
     *
     * @param int $pid Patient ID
     * @param int $limit Number of records to fetch
     * @param int $offset Offset for pagination
     * @return array Chat history records
     */
    public function getSecureChatHistory(int $pid, int $limit = 25, int $offset = 0): array
    {
        try {
            $config = $this->getConfig();
            $practiceId = $config['practice_id'] ?? null;

            if (empty($practiceId)) {
                error_log("[MedEx Secure Chat] Practice ID not configured");
                return [];
            }

            // Request history from MedEx API
            $data = [
                'practice_id' => $practiceId,
                'pid' => $pid,
                'limit' => $limit,
                'offset' => $offset
            ];

            $response = $this->makeRequest('/api/get_secure_chat_history.php', $data, 'POST');

            if ($response && isset($response['success']) && $response['success']) {
                return $response['history'] ?? [];
            }

            error_log("[MedEx Secure Chat] Failed to fetch history: " . ($response['error'] ?? 'Unknown error'));
            return [];

        } catch (\Exception $e) {
            error_log("[MedEx Secure Chat] Exception fetching history: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // DB STATUS CACHE HELPERS
    // =========================================================================

    /**
     * Read the raw status JSON blob from medex_prefs into an array.
     * Returns [] on any failure so callers never need to null-check.
     */
    private function readStatusCache(): array
    {
        try {
            $row = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
                "SELECT status FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1",
                []
            );
            if ($row && !empty($row['status'])) {
                return json_decode($row['status'], true) ?? [];
            }
        } catch (\Exception $e) {
            // Non-fatal; callers fall through to network
        }
        return [];
    }

    /**
     * Merge $updates into the existing status JSON blob and persist.
     * Safe to call concurrently — never wipes unrelated keys.
     */
    private function updateStatusCache(array $updates): void
    {
        try {
            $current = $this->readStatusCache();
            $merged  = array_merge($current, $updates);
            // WHERE + ORDER BY + LIMIT 1 ensures we only ever touch the single
            // authoritative row even if a duplicate somehow exists.
            \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET status = ? WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1",
                [json_encode($merged)]
            );
        } catch (\Exception $e) {
            error_log('[MedEx] updateStatusCache failed: ' . $e->getMessage());
        }
    }

    /**
     * Bust the DB-level and session-level services caches.
     * Call after a subscription is completed so the next page load fetches
     * fresh enabled_services from the server instead of reading stale "[]".
     */
    public function bustServicesCache(): void
    {
        $this->updateStatusCache([
            'last_services_check_ts' => 0,
            'last_services_result'   => [],
        ]);
        // Also clear the inner session cache
        $urlHash  = substr(md5($this->baseUrl ?? ''), 0, 8);
        $cacheKey = 'medex_services_cache_' . $urlHash;
        unset($_SESSION[$cacheKey]);
    }
}
