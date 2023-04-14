<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Comlink\OpenEMR\Modules\TeleHealthModule\Util\TelehealthAuthUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\SystemLogger;

class TelehealthConfigurationVerifier
{
    private Client $httpClient;

    public function __construct(private SystemLogger $logger, private TeleHealthProvisioningService $provisioningService, private TeleHealthUserRepository $userRepository, private TelehealthGlobalConfig $config)
    {
        $this->httpClient = new Client();
    }

    public function verifyInstallationSettings($user)
    {
        // using a json object so we can add additional checks / messages later
        $resultObject = [
            'status' => 'error'
            ,'message' => xlt('Could not verify settings')
        ];

        $config = $this->config;
        if (!$config->isTelehealthConfigured()) {
            $resultObject['message'] = xlt('Telehealth settings must be saved to verify configuration');
        } else {
            try {
                $provider = $this->provisioningService->getOrCreateTelehealthProvider($user);
                $bridgeSettings = $this->getVerifyBridgeSettings($provider);
                http_response_code(200);
                $resultObject['message'] = xlt('Settings verified');
                $resultObject['status'] = 'success';
                $resultObject['bridgeSettings'] = $bridgeSettings;
            } catch (\Exception $exception) {
                $this->logger->errorLogCaller(
                    "Failed to verify telehealth connection settings" . $exception->getMessage(),
                    ['trace' => $exception->getTraceAsString()]
                );
                $resultObject["message"] = xlt("Could not successfully communicate with telehealth servers.  Check that your Telehealth configuration settings are valid.");
            }
        }
        echo json_encode($resultObject);
    }

    private function getVerifyBridgeSettings(TeleHealthUser $user)
    {
        $password = $this->userRepository->decryptPassword($user->getAuthToken());
        $hashedPassword = TelehealthAuthUtils::getFormattedPassword($password);
        $data = ["userId" => $user->getUsername(), "passwordHash" => $hashedPassword
            , "type" => "normal"
            , 'telehealthApiUrl' => $this->config->getTelehealthAPIURI()
            , 'successMessage' => xlj("Successfully verified settings")
            , 'errorMessage' => xlj("Telehealth Video API URI is invalid")
        ];
        return $data;
    }
}
