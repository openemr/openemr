<?php

/**
 * Fax SMS Authentication Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Exception;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\SDK;

trait AuthenticateTrait
{
    private static int $authAttemptCount = 0;
    private static int $lastAuthAttempt = 0;

    public function authenticate($acl = []): bool|int|string
    {
        if (empty($this->credentials['appKey'])) {
            $this->credentials = $this->getCredentials();
            if (empty($this->credentials['appKey'])) {
                return xl('Missing or Invalid RingCentral Credentials. Please contact your administrator.');
            }
        }
        // Only authenticate if token is invalid or expired
        if ($this->isTokenValid()) {
            return 1;
        }

        return $this->authenticateRingCentral();
    }

    private function isTokenValid(): bool
    {
        if (!$this->platform->loggedIn()) {
            return false;
        }

        $authData = $this->platform->auth()->data();

        // Check if we have expire_time (absolute timestamp)
        if (isset($authData['expire_time'])) {
            // If expire_time is not set or is in the past, return false
            return (time() + 300) < $authData['expire_time'];
        }

        return false;
    }

    /**
     * @return int|string
     */
    private function authenticateRingCentral(): int|string
    {
        self::$authAttemptCount++;
        self::$lastAuthAttempt = time();

        try {
            $authBack = $this->cacheDir . DIRECTORY_SEPARATOR . 'platform.json';
            $cachedAuth = $this->getCachedAuth($authBack);
            if (!empty($cachedAuth['refresh_token'])) {
                $this->platform->auth()->setData($cachedAuth);
            }

            if ($this->platform->loggedIn()) {
                return 1;
            } else {
                return $this->loginWithJWT();
            }
        } catch (Exception $e) {
            return text($e->getMessage());
        }
    }

    /**
     * @param string $authBack
     * @return array
     */
    private function getCachedAuth(string $authBack): array
    {
        if (file_exists($authBack)) {
            $cachedAuth = file_get_contents($authBack);
            $cachedAuth = json_decode($this->crypto->decryptStandard($cachedAuth), true);

            // Don't delete cache immediately - validate first
            if ($this->isValidCachedAuth($cachedAuth)) {
                return $cachedAuth;
            }
            // If cached auth is invalid, delete the file
            unlink($authBack); // Only delete if invalid
        }
        return [];
    }

    private function isValidCachedAuth(array $authData): bool
    {
        if (empty($authData['access_token']) || empty($authData['expires_in'])) {
            return false;
        }

        // Check if token expires within next 5 minutes
        return (time() + 300) < $authData['expire_time'];
    }

    /**
     * @return int|string
     */
    private function loginWithJWT(): int|string
    {
        $jwt = trim($this->credentials['jwt'] ?? '');
        $maxRetries = 3;
        $baseDelay = 1; // seconds

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $this->platform->login(['jwt' => $jwt]);
                if ($this->platform->loggedIn()) {
                    $this->setSession('sessionAccessToken', $this->platform->auth()->data());
                    $this->cacheAuthData($this->platform);
                    return 1;
                }
            } catch (ApiException $e) {
                if ($e->getCode() === 429) {
                    $delay = $baseDelay * 2 ** $attempt; // Exponential backoff
                    sleep($delay);
                    continue;
                }
                return js_escape(['error' => "API Error: " . text($e->getMessage()) . " - " . text($e->getCode())]);
            } catch (Exception $e) {
                return js_escape(['error' => "Error: " . text($e->getMessage())]);
            }
        }

        return js_escape(['error' => "Login with JWT failed after {$maxRetries} attempts."]);
    }

    /**
     * @param $platform
     * @return void
     */
    private function cacheAuthData($platform): void
    {
        $data = $platform->auth()->data();
        $encryptedData = $this->crypto->encryptStandard(json_encode($data));
        file_put_contents($this->cacheDir . DIRECTORY_SEPARATOR . 'platform.json', $encryptedData);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function initializeSDK(): void
    {
        if (isset($this->credentials['appKey'], $this->credentials['appSecret'])) {
            $this->rcsdk = new SDK($this->credentials['appKey'], $this->credentials['appSecret'], $this->serverUrl, 'OpenEMR', '1.0.0');
            $this->platform = $this->rcsdk->platform();
        } else {
            throw new Exception("App Key and App Secret are required to initialize SDK.");
        }
    }
}
