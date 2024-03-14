<?php

namespace OpenEMR\Modules\WenoModule\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WenoValidate extends ModuleService
{
    private string $requestUrl = 'https://dev.wenoexchange.com/webapi/restapi/WenoManage';
    private string $messageID;
    private string $userEmail;
    private string $md5UserPassword;
    private mixed $encryptionKey;
    private Client $client;

    public function __construct()
    {
        parent::__construct();
        $this->generateMessageID();
        $this->setMessageProperties();
        $this->client = new Client();
    }

    /**
     * @return void
     */
    private function generateMessageID(): void
    {
        $random_string = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
        $timestamp = time();
        $this->messageID = substr($timestamp . $random_string, 0, 10);
    }

    /**
     * @return void
     */
    private function setMessageProperties(): void
    {
        $settings = $this->getVendorGlobals();
        $this->md5UserPassword = md5($settings['weno_admin_password'] ?? '');
        $this->userEmail = $settings['weno_admin_username'] ?? '';
        $this->encryptionKey = $settings['weno_encryption_key'] ?? '';
    }

    /**
     * @param $key
     * @return void
     */
    public function setNewEncryptionKey($key): void
    {
        $gbl = $this->getVendorGlobals();
        $gbl['weno_encryption_key'] = $key;
        // save the new key to the database.
        // save will also set the global to stay current.
        $this->saveVendorGlobals($gbl);
        error_log('A new encryption key ' . $key . ' was created and saved: ' . date('Y-d-m H:i:s', time()));
        $wenoLog = new WenoLogService();
        $wenoLog->insertWenoLog("new_encryption_key", "saved new key value " . $key);
    }

    /**
     * @return string
     */
    private function buildVerifyEncryptionKeyXML(): string
    {
        $this->setMessageProperties();
        return "<?xml version='1.0' encoding='utf-8'?>
        <MessageType xmlns='https://wexlb.wenoexchange.com/schema/Manage_Account'>
            <Header>
                <MessageID>{$this->messageID}</MessageID>
                <SentTime>" . date('Y-m-d\TH:i:s.v') . "</SentTime>
                <AdminUser>
                    <Email>{$this->userEmail}</Email>
                    <MD5Password>{$this->md5UserPassword}</MD5Password>
                </AdminUser>
                <SenderSoftware>
                    <SenderSoftwareDeveloper>Administrator</SenderSoftwareDeveloper>
                    <SenderSoftwareProduct>OpenEMR Weno EZ eRx</SenderSoftwareProduct>
                    <SenderSoftwareVersionRelease>7.0.2(1)</SenderSoftwareVersionRelease>
                </SenderSoftware>
            </Header>
            <Body>
                <ValidateEncKey>{$this->encryptionKey}</ValidateEncKey>
            </Body>
        </MessageType>";
    }

    /**
     * @return string
     */
    private function buildResetEncryptionKeyXML(): string
    {
        $this->setMessageProperties();
        return "<?xml version='1.0' encoding='utf-8'?>
        <MessageType xmlns='https://wexlb.wenoexchange.com/schema/Manage_Account'>
            <Header>
                <MessageID>{$this->messageID}</MessageID>
                <SentTime>" . date('Y-m-d\TH:i:s.v') . "</SentTime>
                <AdminUser>
                    <Email>{$this->userEmail}</Email>
                    <MD5Password>{$this->md5UserPassword}</MD5Password>
                </AdminUser>
                <SenderSoftware>
                    <SenderSoftwareDeveloper>Administrator</SenderSoftwareDeveloper>
                    <SenderSoftwareProduct>OpenEMR Weno EZ eRx</SenderSoftwareProduct>
                    <SenderSoftwareVersionRelease>7.0.2(1)</SenderSoftwareVersionRelease>
                </SenderSoftware>
            </Header>
            <Body>
                <ResetEncKey>True</ResetEncKey>
            </Body>
        </MessageType>";
    }

    /**
     * @return false|string
     */
    public function requestEncryptionKeyReset(): false|string
    {
        $payload = $this->buildResetEncryptionKeyXML();
        try {
            $response = $this->sendXMLRequest($payload);
            if (isset($response['Body']['Error'])) {
                $this->handleValidationFailure('reset_encryption_key', $response['Body']['Error']['Description'] ?? 'reset_failed');
                return false;
            }
            $newKey = $response['Body']['Success']['NewEncryptionKey'] ?? '';

            return ($response !== false && !empty($newKey)) ? trim($newKey) : false;
        } catch (GuzzleException $e) {
            // Handle Guzzle Exception
            return false;
        }
    }

    /**
     * @param        $code
     * @param string $desc
     * @return void
     */
    private function handleValidationFailure($code, string $desc = 'invalid'): void
    {
        error_log($desc . ': ' . date('Y-d-m H:i:s', time()));
        $wenoLog = new WenoLogService();
        $wenoLog->insertWenoLog($code, $desc);
    }

    /**
     * @param $response
     * @return string|bool
     */
    private function extractValidationResult($response): string|bool
    {
        if (isset($response['Body']['Error'])) {
            $this->handleValidationFailure('verify_encryption_key', $response['Body']['Error']['Description'] ?? 'invalid_key');
            return $response['Body']['Error']['Description'] ?? '0' . ' ' . 'error.';
        }
        return $response['Body']['Success']['EncryptionKeyValid'] ?? '0';
    }

    /**
     * @return bool
     */
    public function verifyEncryptionKey(): bool
    {
        $payload = $this->buildVerifyEncryptionKeyXML();
        try {
            $response = $this->sendXMLRequest($payload);
            $code = $response['Body']['Error']['Code'] ?? '';

            if ($response === false) {
                $this->handleValidationFailure('verify_encryption_key', 'empty_response');
                return false;
            }
            // extract the result
            $valid = $this->extractValidationResult($response);
            // check for valid response
            if ($valid === false) {
                $this->handleValidationFailure('verify_encryption_key', 'invalid_response');
                return false;
            } elseif (stripos($valid, 'ERROR') !== false || $code !== '') {
                $this->handleValidationFailure('verify_encryption_key', $valid);
                return false;
            } else {
                $valid = (strtolower($valid) === 'true') || ($valid == '1') && !empty($valid);
            }
            return $valid;
        } catch (\Exception $e) {
            // Handle Guzzle Exception
            return false;
        }
    }

    /**
     * @param $payload
     * @return array|false
     */
    private function sendXMLRequest($payload): array|false
    {
        try {
            $response = $this->client->post($this->requestUrl, [
                'body' => $payload,
                'headers' => [
                    'Content-Type' => 'text/xml',
                ],
            ]);
            $httpCode = $response->getStatusCode();

            if ($httpCode >= 200 && $httpCode < 300) {
                $result = $response->getBody()->getContents();
                $xmlContent = html_entity_decode($result);
                $xmlContent = preg_replace('/<string[^>]*>/', '', $xmlContent);
                $xmlContent = preg_replace('/<\/string>/', '', $xmlContent);
                $result = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
                $result = json_decode(json_encode($result), true);
                return $result ?: [];
            } else {
                return false;
            }
        } catch (GuzzleException $e) {
            // Handle Guzzle Exception
            return false;
        }
    }

    // Weno is working to get JSON requests to work!
    // Retain for future.
    /**
     * @param $bodyType
     * @return string|null
     */
    private function buildJsonRequestPayload($bodyType): ?string
    {
        $this->setMessageProperties();
        $payload = [
            'MessageType' => [
                'Header' => [
                    'MessageID' => $this->messageID,
                    'SentTime' => date('Y-m-d\TH:i:s.v'),
                    'AdminUser' => [
                        'Email' => $this->userEmail,
                        'MD5Password' => $this->md5UserPassword,
                    ],
                    'SenderSoftware' => [
                        'SenderSoftwareDeveloper' => 'sjpadgett@gmail.com',
                        'SenderSoftwareProduct' => 'OpenEMR Weno EZ eRx',
                        'SenderSoftwareVersionRelease' => '7.0.2(1)',
                    ],
                ],
                'Body' => $bodyType,
            ],
        ];
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * @param string $payload
     * @return bool|array
     */
    private function sendRequest(string $payload): bool|array
    {
        try {
            $response = $this->client->post($this->requestUrl, [
                'body' => $payload,
                'headers' => [
                    'Content-Type' => 'text/json',
                ],
            ]);
            $httpCode = $response->getStatusCode();
            $isError = false;
            if ($httpCode >= 200 && $httpCode < 300) {
                $result = json_decode($response->getBody()->getContents(), true);
                if (is_array($result) && isset($result["MessageType"]["Body"]["Error"])) {
                    $isError = true;
                    $errorCode = $result["MessageType"]["Body"]["Error"]["Code"] ?? '';
                    $errorDescription = $result["MessageType"]["Body"]["Error"]["Description"] ?? '';
                }
                return (is_array($result) && !$isError) ? $result : false;
            } else {
                return false;
            }
        } catch (GuzzleException $e) {
            // Handle Guzzle Exception
            return false;
        }
    }

    /**
     * @param $resetOnInvalid
     * @return bool
     */
    public function validateAdminCredentials($resetOnInvalid = false): bool
    {
        $newKey = '';
        $isKeyValid = $this->verifyEncryptionKey();
        if (!$isKeyValid && $resetOnInvalid) {
            $newKey = $this->requestEncryptionKeyReset();
            if (!empty($newKey)) {
                // save new admin production key.
                $this->setNewEncryptionKey($newKey);
            }
        }
        // return new key or encrypted key status (default).
        return !empty($newKey) ? trim($newKey) : $isKeyValid;
    }
}
