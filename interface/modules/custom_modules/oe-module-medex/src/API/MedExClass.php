<?php

/**
 * MedEx Main Class - Coordinates all MedEx API services
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi;

use MedExApi\Client\HttpClient;
use MedExApi\Services\PracticeService;
use MedExApi\Services\CampaignService;
use MedExApi\Services\EventsService;
use MedExApi\Services\CallbackService;
use MedExApi\Services\LoggingService;
use MedExApi\Services\DisplayService;
use MedExApi\Services\SetupService;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\VersionService;

class MedEx
{
    public string $lastError = '';
    public HttpClient $curl;
    public PracticeService $practice;
    public CampaignService $campaign;
    public EventsService $events;
    public CallbackService $callback;
    public LoggingService $logging;
    public DisplayService $display;
    public SetupService $setup;
    /** @phpstan-ignore-next-line property.unusedType (cookie may be set by HttpClient in future) */
    private ?string $cookie = null;
    private string $url;

    /**
     * Initialize MedEx API client
     *
     * @param string $url MedEx server URL
     * @param string $sessionFile Cookie jar file path
     */
    public function __construct(string $url, string $sessionFile = 'cookiejar_MedExAPI')
    {
        if ($sessionFile == 'cookiejar_MedExAPI') {
            $tmpDir = $GLOBALS['temporary_files_dir'] ?? sys_get_temp_dir();
            $sessionFile = $tmpDir . '/cookiejar_MedExAPI';
        }

        // Use http:// for localhost, docker-internal, or k8s cluster-internal URLs; https:// otherwise
        $isLocal = str_contains($url, 'localhost')
            || str_contains($url, 'host.docker.internal')
            || str_contains($url, '.svc.cluster.local');
        $protocol = $isLocal ? 'http://' : 'https://';
        $cleanUrl = rtrim(preg_replace('/^https?\:\/\//', '', $url), '/');
        if (str_contains($cleanUrl, '/cart/upload')) {
            $cleanUrl = preg_replace('#/cart/upload/?$#', '', $cleanUrl);
        }
        $this->url = rtrim($protocol . $cleanUrl, '/') . '/cart/upload/index.php?route=api/';

        // Initialize HTTP client
        $this->curl = new HttpClient($sessionFile);

        // Initialize services
        $this->practice = new PracticeService($this);
        $this->campaign = new CampaignService($this);
        $this->events = new EventsService($this);
        $this->callback = new CallbackService($this);
        $this->logging = new LoggingService($this);
        $this->display = new DisplayService($this);
        $this->setup = new SetupService($this);
    }

    public function getCookie(): ?string
    {
        return $this->cookie;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Internal login method that always makes fresh API call
     *
     * @param array<string,mixed> $info
     * @return array<string,mixed>|null
     */
    private function just_login(array $info): ?array
    {
        if (empty($info)) {
            return null;
        }

        $versionService = new VersionService();
        $version = $versionService->fetch();

        $this->curl->setUrl($this->getUrl('login'));
        $this->curl->setData([
            'username'  => $info['ME_username'] ?? '',
            'key'       => $info['ME_api_key'] ?? '',
            'UID'       => $info['MedEx_id'] ?? '',
            'MedEx'     => 'OpenEMR',
            'major'     => attr($version['v_major'] ?? ''),
            'minor'     => attr($version['v_minor'] ?? ''),
            'patch'     => attr($version['v_patch'] ?? ''),
            'database'  => attr($version['v_database'] ?? ''),
            'acl'       => attr($version['v_acl'] ?? ''),
            'callback_key' => $info['callback_key'] ?? ''
        ]);

        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (($info['force'] ?? '0') == '1') {
            return $response;
        }

        $this->logging->log_this($response, 'Login Response');
        $this->logging->log_this($response['status'] ?? [], 'Response');

        if (!empty($response['token'])) {
            $response['practice'] = $this->practice->sync($response['token']);
            $response['generate'] = $this->events->generate($response['token'], $response['campaigns']['events'] ?? []);
            $response['success'] = "200";
        }

        // Only update status if response is successful (has enabled_services AND no error)
        // Don't overwrite with error responses - preserve existing enabled_services
        if (!empty($response['enabled_services']) && empty($response['error'])) {
            // Check if status is locked (manually configured)
            $currentStatus = sqlQuery("SELECT status FROM medex_prefs LIMIT 1");
            $statusData = !empty($currentStatus['status']) ? json_decode($currentStatus['status'], true) : [];
            
            if (empty($statusData['locked'])) {
                // Not locked, safe to update
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_prefs SET status = ?",
                    [json_encode($response)]
                );
            } else {
                // Locked - preserve enabled_services but update other fields
                $response['enabled_services'] = $statusData['enabled_services'];
                $response['locked'] = 1;
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_prefs SET status = ?",
                    [json_encode($response)]
                );
            }
        } else if (isset($response['error'])) {
            // API error - don't overwrite status at all, just log it
            $this->logging->log_this($response, 'Login API Error - status NOT updated to preserve enabled_services');
        }

        return $response;
    }

    /**
     * Login to MedEx (uses cache if recent)
     *
     * @param string|int $force Force fresh login if > 0
     * @return array<string,mixed>|false
     */
    public function login(string|int $force = ''): array|false
    {
        $info = $this->getPreferences();

        if (
            empty($info) ||
            empty($info['ME_username']) ||
            empty($info['ME_api_key']) ||
            empty($info['MedEx_id']) ||
            (($GLOBALS['medex_enable'] ?? '') !== '1')
        ) {
            return false;
        }

        $info['callback_key'] = $_POST['callback_key'] ?? '';

        // Check if status needs refresh
        $expired = '0';
        $statusPayload = null;

        if (empty($force)) {
            $timer = strtotime((string)($info['MedEx_lastupdated'] ?? ''));
            $utc_now = date('Y-m-d H:i:s');
            $hour_ago = strtotime($utc_now . "-60 minutes");
            if ($hour_ago > $timer) {
                $expired = '1';
            }
        }

        if (($expired === '1') || ((string)$force > '0')) {
            $info['force'] = (string)$force;
            $statusPayload = $this->just_login($info);
        } else {
            $statusPayload = json_decode((string)($info['status'] ?? ''), true);
        }

        if (is_array($statusPayload) && isset($statusPayload['error'])) {
            $this->lastError = $statusPayload['error'];
            // Background service management has been removed; do not touch background_services table.
            return $statusPayload;
        }

        return $statusPayload ?: false;
    }

    /**
     * Get MedEx preferences from database
     *
     * @return array<string,mixed>
     */
    public function getPreferences(): array
    {
        $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs");
        $info = $prefsRecords[0] ?? [];

        // Background service table no longer used for MedEx; provide sensible defaults.
        $info['execute_interval'] = $info['execute_interval'] ?? null;
        $info['active'] = $info['active'] ?? null;
        $info['running'] = $info['running'] ?? null;

        return $info;
    }

    /**
     * Build full API URL for a method
     *
     * @param string $method
     * @return string
     */
    public function getUrl(string $method): string
    {
        return $this->url . $method;
    }

    /**
     * Check if patient allows specific message modality
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $appt
     * @param array<string,mixed> $icon
     * @return array{0: mixed, 1: string|false}
     */
    public function checkModality(array $event, array $appt, array $icon = []): array
    {
        $mType = $event['M_type'] ?? '';

        if ($mType == "SMS") {
            if (empty($appt['phone_cell']) || ($appt["hipaa_allowsms"] ?? '') == "NO") {
                return [$icon['SMS']['NotAllowed'] ?? null, false];
            } else {
                $phone = preg_replace("/[^0-9]/", "", (string)$appt["phone_cell"]);
                return [$icon['SMS']['ALLOWED'] ?? null, $phone];
            }
        } elseif ($mType == "AVM") {
            if (
                (empty($appt["phone_home"]) && empty($appt["phone_cell"])) ||
                ($appt["hipaa_voice"] ?? '') == "NO"
            ) {
                return [$icon['AVM']['NotAllowed'] ?? null, false];
            } else {
                if (!empty($appt["phone_cell"])) {
                    $phone = preg_replace("/[^0-9]/", "", (string)$appt["phone_cell"]);
                } else {
                    $phone = preg_replace("/[^0-9]/", "", (string)($appt["phone_home"] ?? ''));
                }
                return [$icon['AVM']['ALLOWED'] ?? null, $phone];
            }
        } elseif ($mType == "EMAIL") {
            if (empty($appt["email"]) || ($appt["hipaa_allowemail"] ?? '') == "NO") {
                return [$icon['EMAIL']['NotAllowed'] ?? null, false];
            } else {
                return [$icon['EMAIL']['ALLOWED'] ?? null, $appt["email"]];
            }
        } else {
            return [false, false];
        }
    }
}
