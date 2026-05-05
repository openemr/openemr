<?php
/**
 * MedEx Module Configuration & Permissions
 * Manages subscription-based feature access for MedEx services
 *
 * Services:
 * 1. MedEx Messages - Communication/reminders
 * 2. Calendar Sync (View Only) - iCal export with HIPAA authentication
 * 3. Calendar Sync (Full) - Alternative calendar UI with appointment management
 *
 * @package   OpenEMR
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 * 
 * ⚠️ IP NOTICE: This file is part of the MedEx Module.
 * The MedEx Module is PROPRIETARY/CLOSED SOURCE software owned by MedEx.
 * It is NOT open source and NOT licensed under GNU GPL.
 * Unauthorized copying, modification, or distribution is prohibited.
 */

namespace OpenEMR\Modules\MedEx;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\oeHttp;

class MedExConfig
{
    // ---------------------------------------------------------------
    // SINGLE SOURCE OF TRUTH for the MedEx server URLs.
    // Never hardcode these anywhere else — always use MedExConfig::baseUrl() / mainSiteUrl()
    // ---------------------------------------------------------------
    // Default: Public SaaS endpoint (works for all installations)
    // Kubernetes users in same cluster: Set medex_base_url global to:
    //   http://medex-api.medex.svc.cluster.local/cart/upload
    public const DEFAULT_BASE_URL = 'https://api.hipaabank.net/cart/upload';

    /** Root of the API server (no /cart/upload path) — for customer-facing pages, tutorial, etc. */
    public const DEFAULT_MAIN_URL = 'https://medexbank.com';
    public const DEFAULT_AGREEMENTS_URL = 'https://api.hipaabank.net/cart/upload';
    public const TERMS_VERSION = '2026-03-29';
    public const BAA_VERSION = '2026-03-29';
    public const OTP_WHATSAPP_ENABLED = false;
    public const OTP_HOUSE_ACCOUNT_SMS = 'HOUSE_SMS';
    public const OTP_HOUSE_ACCOUNT_WHATSAPP = 'HOUSE_WHATSAPP';
    public const OTP_HOUSE_ACCOUNT_EMAIL = 'HOUSE_EMAIL';
    public const OTP_HOUSE_EMAIL_COST = 0.01;

    /**
     * Returns the active MedEx base URL (includes /cart/upload path).
     * Priority: globals DB table (medex_bank_url / medex_base_url) → DEFAULT_BASE_URL
     */
    public static function baseUrl(): string
    {
        $url = $GLOBALS['medex_bank_url']
            ?? $GLOBALS['medex_base_url']
            ?? self::DEFAULT_BASE_URL;
        $url = rtrim((string)$url, '/');

        // In-cluster OpenEMR pods currently fail TLS handshake to api.hipaabank.net.
        // Keep server-to-server calls on an external host by using medexbank.com.
        if (!empty(getenv('KUBERNETES_SERVICE_HOST')) && preg_match('#^https?://api\.hipaabank\.net/cart/upload$#i', $url)) {
            return 'https://medexbank.com/cart/upload';
        }
        return $url;
    }

    /**
     * Returns the MedEx base URL safe for use in browser-facing iframe src attributes.
     *
     * baseUrl() may return an internal Kubernetes service name
     * (e.g. http://medex-api.medex.svc.cluster.local/cart/upload) which is only
     * reachable by PHP server-side code.  This method translates any such internal
     * hostname to the public HTTPS endpoint so the browser can load the iframe.
     *
     * Resolution order:
     *   1. medex_public_url global (optional override, e.g. for custom domains)
     *   2. Rewrite internal k8s cluster DNS → medexbank.com (public branded host)
     *   3. Force HTTPS
     */
    public static function publicBaseUrl(): string
    {
        // Allow an explicit override for non-standard deployments
        if (!empty($GLOBALS['medex_public_url'])) {
            $override = rtrim((string)$GLOBALS['medex_public_url'], '/');
            $override = preg_replace(
                '#^https?://medexbank\.com/cart/upload#i',
                'https://api.hipaabank.net/cart/upload',
                $override
            );
            return rtrim((string)$override, '/');
        }

        $url = self::baseUrl();

        // Rewrite internal Kubernetes service DNS → public API hostname
        $url = preg_replace(
            '#https?://medex-api\.medex\.svc\.cluster\.local#i',
            'https://api.hipaabank.net',
            $url
        );

        // medexbank.com main site does not serve all API rewrite routes (e.g., /chat/secure/*).
        // Normalize browser links to api.hipaabank.net unless a custom medex_public_url is set.
        $url = preg_replace(
            '#^https?://medexbank\.com/cart/upload#i',
            'https://api.hipaabank.net/cart/upload',
            $url
        );

        // Rewrite Docker-internal hostname → localhost (dev environments)
        $url = str_replace('host.docker.internal', 'localhost', $url);

        // Ensure HTTPS for any http://hostname that isn't localhost
        if (preg_match('#^http://(?!localhost)#i', $url)) {
            $url = 'https://' . substr($url, strlen('http://'));
        }

        return rtrim($url, '/');
    }

    /**
     * Returns the root of the MedEx server (strips /cart/upload).
     * Use this for links to customer-facing pages: tutorial, terms, BAA, subscriptions.
     * e.g. https://api.hipaabank.net
     */
    public static function mainSiteUrl(): string
    {
        return rtrim(str_replace('/cart/upload', '', self::publicBaseUrl()), '/');
    }

    /**
     * Public tutorial URL for browser-facing links.
     */
    public static function tutorialUrl(): string
    {
        return self::mainSiteUrl() . '/help/tutorial.html';
    }

    public static function termsUrl(): string
    {
        $base = rtrim((string)($GLOBALS['medex_agreements_url'] ?? self::DEFAULT_AGREEMENTS_URL), '/');
        return $base . '/index.php?route=information/information&information_id=5';
    }

    public static function baaUrl(): string
    {
        $base = rtrim((string)($GLOBALS['medex_agreements_url'] ?? self::DEFAULT_AGREEMENTS_URL), '/');
        return $base . '/index.php?route=information/information&information_id=8';
    }

    public static function privacyUrl(): string
    {
        $base = rtrim((string)($GLOBALS['medex_agreements_url'] ?? self::DEFAULT_AGREEMENTS_URL), '/');
        return $base . '/index.php?route=information/information&information_id=3';
    }

    // Service feature flags
    public const SERVICE_MESSAGES = 'medex_messages';
    public const SERVICE_CALENDAR_VIEW = 'medex_calendar_view';
    public const SERVICE_CALENDAR_FULL = 'medex_calendar_full';

    /** @var array<string> */
    private array $enabled_services = [];
    private ?string $medex_url = null;
    private ?string $api_key = null;
    private bool $enabled = false;

    public function __construct()
    {
        $this->loadConfig();
        $this->loadEnabledServices();
    }

    /**
     * Load configuration from database or config file
     *
     * @return void
     */
    private function loadConfig(): void
    {
        // Load from globals table
        $sql = "SELECT gl_name, gl_value FROM globals WHERE gl_name LIKE 'medex_%'";
        $rows = QueryUtils::fetchRecords($sql);

        foreach ($rows as $row) {
            switch ($row['gl_name']) {
                case 'medex_enable':
                    $this->enabled = (bool)$row['gl_value'];
                    break;
                case 'medex_server_url':
                case 'medex_bank_url':
                case 'medex_base_url':
                    $this->medex_url = rtrim($row['gl_value'], '/');
                    break;
                case 'medex_api_key':
                    $this->api_key = $row['gl_value'];
                    break;
            }
        }
    }

    /**
     * Check which services are enabled/subscribed
     * Queries MedEx to get active subscriptions
     *
     * @return void
     */
    private function loadEnabledServices(): void
    {
        if (empty($this->api_key) || empty($this->medex_url)) {
            return;
        }

        try {
            // Query MedEx for active subscriptions
            $response = oeHttp::setOptions([
                'timeout' => 10,
                'http_errors' => false
            ])->asFormParams()->post(
                $this->medex_url . '/index.php?route=api/calendarsync/authenticate',
                ['api_key' => $this->api_key]
            );

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                if ($data['success'] ?? false) {
                    // Store enabled services from MedEx subscription data
                    $this->enabled_services = $data['enabled_services'] ?? [];
                }
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            error_log("MedEx loadEnabledServices failed: " . $e->getMessage());
        }
    }

    /**
     * Check if a specific service is enabled
     *
     * @param string $service
     * @return bool
     */
    public function isServiceEnabled(string $service): bool
    {
        // enabled_services may be associative {"service": true} or indexed ["service"]
        if (isset($this->enabled_services[$service])) {
            return (bool)$this->enabled_services[$service];
        }
        return in_array($service, $this->enabled_services);
    }

    /**
     * Get all enabled services
     *
     * @return array<string>
     */
    public function getEnabledServices(): array
    {
        return $this->enabled_services;
    }

    /**
     * Check if Messages service is enabled
     *
     * @return bool
     */
    public function canUseMessages(): bool
    {
        return $this->isServiceEnabled(self::SERVICE_MESSAGES);
    }

    /**
     * Check if Calendar View (read-only export) is enabled
     *
     * @return bool
     */
    public function canUseCalendarView(): bool
    {
        return $this->isServiceEnabled(self::SERVICE_CALENDAR_VIEW);
    }

    /**
     * Check if Calendar Full (appointment management) is enabled
     *
     * @return bool
     */
    public function canUseCalendarFull(): bool
    {
        return $this->isServiceEnabled(self::SERVICE_CALENDAR_FULL);
    }

    /**
     * Verify permission before sending data to MedEx
     * Throws exception if service not enabled
     *
     * @param string $service
     * @return void
     * @throws \Exception
     */
    public function requireService(string $service): void
    {
        if (!$this->isServiceEnabled($service)) {
            throw new \Exception("MedEx service '$service' is not enabled. Please subscribe to this service in your MedEx account.");
        }
    }

    /**
     * Get MedEx API URL (instance method — prefers DB value, falls back to constant)
     *
     * @return string
     */
    public function getMedExUrl(): string
    {
        return $this->medex_url ?? self::DEFAULT_BASE_URL;
    }

    /**
     * Get API key
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->api_key;
    }
}
