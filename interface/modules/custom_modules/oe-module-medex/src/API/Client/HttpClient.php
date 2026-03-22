<?php

/**
 * MedEx HTTP Client
 * Handles HTTP requests to MedEx server with session management
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Client;

use OpenEMR\Common\Http\oeHttp;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class HttpClient
{
    private ?string $url = null;
    /** @var array<string,mixed> */
    private array $postData = [];
    private CookieJar $cookieJar;
    private string $response = '';

    public function __construct(private string $sessionFile)
    {
        $this->cookieJar = new CookieJar();
        $this->restoreSession();
    }

    private function restoreSession(): void
    {
        if (file_exists($this->sessionFile)) {
            $cookies = json_decode(file_get_contents($this->sessionFile), true);
            if (is_array($cookies)) {
                foreach ($cookies as $name => $value) {
                    $this->cookieJar->setCookie(new SetCookie([
                        'Name' => $name,
                        'Value' => $value,
                        'Domain' => parse_url($this->url ?? '', PHP_URL_HOST)
                    ]));
                }
            }
        }
    }

    public function makeRequest(): void
    {
        try {
            $response = oeHttp::setOptions([
                'cookies' => $this->cookieJar,
                'verify' => false,
                'http_errors' => false,
                'allow_redirects' => true
            ])->asFormParams()->post($this->url ?? '', $this->postData);

            $httpCode = $response->getStatusCode();
            $this->response = $response->getBody()->getContents();

            // Optional diagnostics for MedEx connectivity issues
            if (!empty($GLOBALS['medex_debug_log'] ?? '')) {
                $log = $GLOBALS['medex_debug_log_file'] ?? '/tmp/medex.log';
                $std_log = @fopen($log, 'ab');
                if ($std_log !== false) {
                    $timed = date('Y-m-d H:i:s');
                    fwrite(
                        $std_log,
                        "**********************\nMedEx HTTP: " . $timed . "\n" .
                        "URL: " . $this->url . "\n" .
                        "HTTP: " . $httpCode . "\n"
                    );
                    fclose($std_log);
                }
            }

            $this->saveSession();
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            error_log("MedEx HTTP Request failed: " . $e->getMessage());
            $this->response = '';
        }
    }

    private function saveSession(): void
    {
        if (empty($this->sessionFile)) {
            return;
        }

        $sessionFileDir = dirname($this->sessionFile);
        if (!file_exists($sessionFileDir)) {
            mkdir($sessionFileDir, 0755, true);
        }

        $cookies = [];
        foreach ($this->cookieJar->toArray() as $cookie) {
            $cookies[$cookie['Name']] = $cookie['Value'];
        }

        file_put_contents($this->sessionFile, json_encode($cookies));
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @param array<string,mixed> $postData
     */
    public function setData(array $postData): void
    {
        $this->postData = $postData;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getResponse(): ?array
    {
        return json_decode($this->response, true);
    }

    public function getRawResponse(): string
    {
        return $this->response;
    }
}
