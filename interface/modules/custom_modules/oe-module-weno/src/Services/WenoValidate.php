<?php

namespace OpenEMR\Modules\WenoModule\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WenoValidate extends ModuleService
{
    private string $requestUrl = 'https://online.wenoexchange.com/webapi/restapi/WenoManage';
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
        $this->encryptionKey = $settings['weno_encryption_key'] ?: 'Missing';
    }

    /**
     * @param $key
     * @return void
     */
    public function setNewEncryptionKey($key): void
    {
        $gbl = $this->getVendorGlobals();
        $gbl['weno_encryption_key'] = $key;
        $GLOBALS['weno_encryption_key'] = $key;
        // save the new key to the database.
        // save will also set the global to stay current.
        $this->saveVendorGlobals($gbl);
        error_log('A new encryption key was created and saved: ' . date('Y-m-d H:i:s', time()));
    }

    /**
     * @return string
     */
    private function buildVerifyEncryptionKey(): string
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
    private function buildResetEncryptionKey(): string
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
            return $response['Body']['Error']['Description'] ?? '0' . ' ' . 'error.';
        }
        return $response['Body']['Success']['EncryptionKeyValid'] ?? '0';
    }

    /**
     * @return false|string
     */
    public function requestEncryptionKeyReset(): false|int|string
    {
        $payload = $this->buildResetEncryptionKey();
        try {
            $response = $this->sendRequest($payload);
            // check for wire error
            if (is_string($response) && (stripos($response, 'connection_problem_') !== false)) {
                $this->handleValidationFailure('reset_encryption_key', $response);
                if (stripos($response, 'notconnected') !== false) {
                    return 999;
                }
                return 998;
            }
            if (isset($response['Body']['Error'])) {
                $this->handleValidationFailure('reset_encryption_key', $response['Body']['Error']['Description'] ?? 'reset_failed');
                return false;
            }

            $newKey = $response['Body']['Success']['NewEncryptionKey'] ?? '';
            return ($response !== false && !empty($newKey)) ? trim($newKey) : false;
        } catch (\Exception $e) {
            // Handle Exception
            return false;
        }
    }

    /**
     * @return bool|int|string
     */
    public function verifyEncryptionKey(): bool|int|string
    {
        $payload = $this->buildVerifyEncryptionKey();
        try {
            $response = $this->sendRequest($payload);
            // check for wire error
            if (is_string($response) && (stripos($response, 'connection_problem_') !== false)) {
                $this->handleValidationFailure('verify_encryption_key', $response);
                if (stripos($response, 'notconnected') !== false) {
                    return 999;
                }
                return 998;
            }

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
                return (int)$code;
            } else {
                $valid = (strtolower($valid) === 'true') || ($valid == '1') && !empty($valid);
            }
            return $valid;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $payload
     * @return array|false
     */
    private function sendRequest($payload): false|array|string
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
                // more escaping hell!
                $xmlContent = html_entity_decode($result);
                $xmlContent = preg_replace('/<string[^>]*>/', '', $xmlContent);
                $xmlContent = preg_replace('/<\/string>/', '', $xmlContent);
                $result = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
                $result = json_decode(json_encode($result), true); // make associative array.
                return $result ?: [];
            } else {
                error_log(text("invalid_http_status_$httpCode"));
                return "connection_problem_$httpCode";
            }
        } catch (GuzzleException $e) {
            error_log(errorLogEscape($e->getMessage()));
            return 'connection_problem_notconnected';
        }
    }

    /**
     * @param $resetOnInvalid
     * @return bool
     */
    public function validateAdminCredentials($resetOnInvalid = false, $where = "prescription"): bool
    {
        $newKey = false;
        $isKeyValid = $this->verifyEncryptionKey();
        if ($isKeyValid >= 998) {
            return $isKeyValid;
        }
        if (!$isKeyValid && $resetOnInvalid) {
            $newKey = $this->requestEncryptionKeyReset();
            if (!empty($newKey)) {
                // save new admin production key.
                $this->setNewEncryptionKey($newKey);
                $wenoLog = new WenoLogService();
                $wenoLog->insertWenoLog(text("$where"), "reset_encryption_key");
            }
            return false;
        }
        // return new key or encrypted key status (default).
        return !empty($newKey) ? trim($newKey) : $isKeyValid;
    }
}
