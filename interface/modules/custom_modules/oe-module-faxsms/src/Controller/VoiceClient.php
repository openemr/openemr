<?php

namespace OpenEMR\Modules\FaxSMS\Controller;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

class VoiceClient extends AppDispatch
{
    use AuthenticateTrait;

    public static $timeZone;
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $redirectUrl;
    public $portalUrl;
    public $credentials;
    public $cacheDir;
    public $apiBase;
    protected $platform;
    protected $rcsdk;
    protected CryptoGen $crypto;
    private VoiceClient $client;
    public function __construct()
    {
        if (empty($GLOBALS['oe_enable_voice'] ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        $this->portalUrl = $this->credentials['production'] ?? null ? "https://service.ringcentral.com/" : "https://service.devtest.ringcentral.com/";
        $this->serverUrl = $this->credentials['production'] ?? null ? "https://platform.ringcentral.com" : "https://platform.devtest.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'] ?? null;
        $this->initializeSDK();
        //$this->initVoice($this->platform);
        parent::__construct();
    }

    public function getVoiceCredentials(): mixed
    {
        $vendor = '_voice';
        $this->authUser = (int)$this->getSession('authUserID');
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        $credentials = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", [$this->authUser, $vendor]);

        if (empty($credentials)) {
            return [
                'extension' => '',
                'phone' => '',
                'smsNumber' => '',
                'appKey' => '',
                'appSecret' => '',
                'server' => '',
                'portal' => '',
                'production' => '',
                'jwt' => ''
            ];
        } else {
            $credentials = $credentials['credentials'];
        }

        $decrypt = $this->crypto->decryptStandard($credentials);
        return json_decode($decrypt, true);
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        return $this->getSetup();
    }

    function sendFax(): string|bool
    {
        return '';
        // TODO: Implement sendFax() method.
    }

    function sendSMS(): mixed
    {
        return '';
        // TODO: Implement sendSMS() method.
    }

    function sendEmail(): mixed
    {
        return '';
        // TODO: Implement sendEmail() method.
    }

    function fetchReminderCount(): string|bool
    {
        return '';
        // TODO: Implement fetchReminderCount() method.
    }
}
