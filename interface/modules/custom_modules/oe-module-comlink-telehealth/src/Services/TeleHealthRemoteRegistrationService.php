<?php

/**
 * Handles the API communication with the Comlink telehealth provisioning service.  Activation, suspension, updating,
 * creation of telehealth services for patients and users are handled here.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;

use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Models\UserVideoRegistrationRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use Ramsey\Uuid\Rfc4122\UuidV4;

class TeleHealthRemoteRegistrationService
{
    /**
     * @var TelehealthRegistrationCodeService
     */
    private $codeService;

    /**
     * API url endpoint to send registration requests to.
     * @var string
     */
    private $apiURL;

    /*
     * UserID for api authentication needed for comlink video service
     * @var string
     */
    private $apiId;

    /*
     * Password for api authentication needed for comlink video service
     * @var string
     */
    private $apiPassword;

    /*
     * CMSID for api authentication needed for comlink video service
     * @var string
     */
    private $apiCMSID;


    /**
     * Client
     */
    private $httpClient;

    /**
     * Unique installation id of the OpenEMR Institution
     * @var string
     */
    private $institutionId;

    /**
     * Name of the OpenEMR institution
     * @var string
     */
    private $institutionName;

    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(TelehealthGlobalConfig $config, TelehealthRegistrationCodeService $codeService)
    {
        $this->apiURL = $config->getRegistrationAPIURI();
        $this->apiId = $config->getRegistrationAPIUserId();
        $this->apiPassword = $config->getRegistrationAPIPassword();
        $this->apiCMSID = $config->getRegistrationAPICmsId();
        $this->institutionId = $config->getInstitutionId();
        $this->institutionName = $config->getInstitutionName();
        $this->userRepository = new TeleHealthUserRepository();
        $this->httpClient = new Client();
        $this->logger = new SystemLogger();
        $this->codeService = $codeService;
    }

    public function createPatientRegistration($patient)
    {
        $registrationRequest = new UserVideoRegistrationRequest();
        $registrationRequest->setDbRecordId($patient['id']);
        $registrationRequest->setIsPatient(true);
        $registrationRequest->setUsername($patient['uuid']);
        $registrationRequest->setPassword($this->userRepository->createUniquePassword());
        $registrationRequest->setInstituationId($this->institutionId);
        $registrationRequest->setInstitutionName($this->institutionName);
        $registrationRequest->setFirstName($patient['fname'] ?? null);
        $registrationRequest->setLastName($patient['lname'] ?? null);
        $registrationRequest->setRegistrationCode($this->codeService->generateRegistrationCode());
        $this->logger->debug("createPatientRegistration called");
        $userId = $this->addNewUser($registrationRequest);
        return !empty($userId);
    }

    public function createUserRegistration($user)
    {
        $registrationRequest = new UserVideoRegistrationRequest();
        $registrationRequest->setDbRecordId($user['id']);
        $registrationRequest->setIsPatient(false);
        $registrationRequest->setUsername($user['uuid']);
        $registrationRequest->setPassword($this->userRepository->createUniquePassword());
        $registrationRequest->setInstituationId($this->institutionId);
        $registrationRequest->setInstitutionName($this->institutionName);
        $registrationRequest->setFirstName($user['fname'] ?? null);
        $registrationRequest->setLastName($user['lname'] ?? null);
        $registrationRequest->setRegistrationCode($this->codeService->generateRegistrationCode());
        $this->logger->debug("createUserRegistration called");
        $userId = $this->addNewUser($registrationRequest);
        return !empty($userId);
    }

    /**
     * @return TeleHealthUserRepository
     */
    public function getUserRepository(): TeleHealthUserRepository
    {
        return $this->userRepository;
    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }


    /**
     * Allows the http client used for api requests to be set for testing or extension purposes
     * @param Client $client
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
    }

    /**
     * Allows the user repository to be set for testing or extension purposes
     * @param TeleHealthUserRepository $userRepository
     */
    public function setTelehealthUserRepository(TeleHealthUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Returns if a registration should be created for the given provider id.  This does not answer whether a registration
     * exists, but whether the user passes the criteria for creating a registration record regardless of whether it exists or not.
     * @param $providerId
     * @return bool
     */
    public function shouldCreateRegistrationForProvider($providerId)
    {
        return $this->providerRepository->isEnabledProvider($providerId);
    }

    /**
     * Provisions a new user with the Comlink video api system
     * @param UserVideoRegistrationRequest $request
     * @return false|int returns false if the user fails to add, otherwise returns the integer id of the provisioned user
     */
    public function addNewUser(UserVideoRegistrationRequest $request)
    {
        if (!$request->isValid()) {
            throw new \InvalidArgumentException("request is missing username, password, or institutionId");
        }

        $securePassword = $request->getPassword();
        $request->setPassword($this->userRepository->decryptPassword($securePassword));
        $httpDataRequest = $request->toArray();

        $response = $this->sendAPIRequest($this->getEndpointUrl("userprovision"), $httpDataRequest);

        if ($response['status'] != 200) {
            (new SystemLogger())->errorLogCaller("Failed to provision user", ['username' => $request->getUsername()
                , 'response' => $response]);
            return false;
        } else {
            try {
                $userSaveRecord = new TeleHealthUser();
                $userSaveRecord->setIsPatient($request->isPatient());
                $userSaveRecord->setDbRecordId($request->getDbRecordId());
                $userSaveRecord->setUsername($request->getUsername());
                $userSaveRecord->setAuthToken($securePassword);
                $userSaveRecord->setDateRegistered(new \DateTime());
                $userSaveRecord->setIsActive(true);
                $userSaveRecord->setRegistrationCode($request->getRegistrationCode());
                $userId = $this->userRepository->saveUser($userSaveRecord);
                $this->logger->debug("Registered user on comlink api ", ['username' => $request->getUsername(), 'id' => $userId]);
            } catch (SqlQueryException $exception) {
                $this->logger->errorLogCaller("User registered on comlink api but did not save to database", ['record' => $userSaveRecord]);
                throw $exception;
            }
            return $userId;
        }
    }

    private function getEndpointUrl($endpoint)
    {
        return $this->apiURL . $endpoint;
    }

    public function populateRequestFromUser(TeleHealthUser $user): UserVideoRegistrationRequest
    {
        $request = new UserVideoRegistrationRequest();
        $request->setRegistrationCode($user->getRegistrationCode())
            ->setUsername($user->getUsername())
            ->setPassword($user->getAuthToken())
            ->setDbRecordId($user->getId())
            ->setIsPatient($user->getIsPatient())
            ->setInstitutionName($this->institutionName)
            ->setInstituationId($this->institutionId);
        return $request;
    }

    /**
     * Updates an existing provisioned user with the Comlink video api system.  Everything but username can be changed
     * @param UserVideoRegistrationRequest $request
     * @return false|int returns false if the user fails to update, otherwise returns the integer id of the updated user
     */
    public function updateUserFromRequest(UserVideoRegistrationRequest $request)
    {
        if (!$request->isValid()) {
            throw new \InvalidArgumentException("request is missing username, password, or institutionId");
        }

        // first make sure we can do the api request
        $dbUserRecord = $this->userRepository->getUser($request->getUsername());
        if (empty($dbUserRecord)) {
            throw new \BadMethodCallException("user does not exist for username " . $request->getUsername());
        }

        $securePassword = $request->getPassword();
        $request->setPassword($this->userRepository->decryptPassword($securePassword));
        $httpDataRequest = $request->toArray();

        $response = $this->sendAPIRequest($this->getEndpointUrl("userupdate"), $httpDataRequest);

        if ($response['status'] != 200) {
            $this->logger->errorLogCaller("Failed to update provisioned user", ['username' => $request->getUsername()
                , 'response' => $response]);
            return false;
        } else {
            $dbUserRecord->setAuthToken($securePassword);
            $dbUserRecord->setIsActive(true);
            $dbUserRecord->setRegistrationCode($request->getRegistrationCode());
            $userId = $this->userRepository->saveUser($dbUserRecord);
            $this->logger->debug("Updated user on comlink api ", ['username' => $request->getUsername(), 'id' => $userId]);
            return $userId;
        }
    }

    public function suspendUser(string $username, string $password): bool
    {
        // first make sure we can do the api request
        $dbUserRecord = $this->userRepository->getUser($username);
        if (empty($dbUserRecord)) {
            throw new \BadMethodCallException("user does not exist for username " . $username);
        }

        $decryptedPassword = $this->userRepository->decryptPassword($password);
        $httpDataRequest = ['userName' => $username, 'passwordString' => $decryptedPassword];
        $decryptedPassword = null;

        $response = $this->sendAPIRequest($this->getEndpointUrl("usersuspend"), $httpDataRequest);
        unset($httpDataRequest['passwordString']);

        if ($response['status'] != 200) {
            $this->logger->errorLogCaller("Failed to suspend user", ['username' => $username, 'response' => $response]);
            return false;
        } else {
            $this->logger->debug("Suspended user on comlink api ", ['username' => $username]);
        }
        $dbUserRecord->setIsActive(false);
        $this->userRepository->saveUser($dbUserRecord);
        return true;
    }

    public function resumeUser(string $username, string $password): bool
    {
        // first make sure we can do the api request
        $dbUserRecord = $this->userRepository->getUser($username);
        if (empty($dbUserRecord)) {
            throw new \BadMethodCallException("user does not exist for username " . $username);
        }

        $passwordString = $this->userRepository->decryptPassword($password);
        $httpDataRequest = ['userName' => $username, 'passwordString' => $passwordString];
        $passwordString = null; // clear out passwords in memory

        $response = $this->sendAPIRequest($this->getEndpointUrl("userresume"), $httpDataRequest);
        $httpDataRequest = null; // clear out passwords in memory
        if ($response['status'] != 200) {
            $this->logger->errorLogCaller("Failed to resume user", ['username' => $username, 'response' => $response]);
            return false;
        } else {
            $this->logger->debug("Resumed user on comlink api ", ['username' => $username]);
        }
        $dbUserRecord->setIsActive(true);
        $this->userRepository->saveUser($dbUserRecord);
        return true;
    }

    public function deactivateUser(string $username, string $password)
    {
        // first make sure we can do the api request
        $dbUserRecord = $this->userRepository->getUser($username);
        if (empty($dbUserRecord)) {
            throw new \BadMethodCallException("user does not exist for username " . $username);
        }

        $httpDataRequest = ['userName' => $username, 'passwordString' => $password];

        $response = $this->sendAPIRequest($this->getEndpointUrl("userresume"), $httpDataRequest);

        if ($response['status'] != 200) {
            $this->logger->errorLogCaller("Failed to deactivate user", ['username' => $username, 'response' => $response]);
            return false;
        } else {
            $this->logger->debug("Deactivated user on comlink api ", ['username' => $username]);
        }
        $dbUserRecord->setIsActive(false);
        $this->userRepository->saveUser($dbUserRecord);
        return true;
    }

    public function verifyProvisioningServiceIsValid()
    {
        $randomUuid = UuidV4::uuid4()->toString();
        $randomPassword = UuidV4::uuid4()->toString();

        // if we are not authorized we will get a 401 response from this.
        $response = $this->sendAPIRequest($this->getEndpointUrl('usersuspend'), ['userName' => $randomUuid, 'passwordString' => $randomPassword]);
        return ['status' => $response['internalStatus'], 'message' => $response['internalError']];
    }

    private function sendAPIRequest($endpointUrl, array $body)
    {
        if (empty($this->httpClient)) {
            throw new \BadMethodCallException("httpClient must be setup in order to send request");
        }

        // because this could be an already existing event we've tried saving before we decode the json, even though
        // on the first event notification we may be doubling the work
        $client = $this->getHttpClient();
        $internalErrorResponse = null;
        $bodyResponse = null;
        $statusCode = 500;
        $internalStatusCode = 200;

        try {
            $httpRequestOptions = [
                "headers" => [
                    "SvcmgrTk1" => $this->apiId
                    ,"SvcmgrTk2" => $this->apiPassword
                    ,"SvcmgrTk3" => $this->apiCMSID
                ],
                "body" => json_encode($body)
            ];
            $response = $client->post($endpointUrl, $httpRequestOptions);
            $statusCode = $response->getStatusCode();
            $response->getBody()->rewind();
            $bodyResponse = $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            $this->logger->errorLogCaller(
                "Failed to send registration request Exception: " . $exception->getMessage(),
                ['trace' => $exception->getTraceAsString(), 'endUrl' => $endpointUrl]
            );
            if ($exception->getCode() == 401) { // unauthorized exception meaning the credentials are incorrect
                $statusCode = 401;
            }
            $internalErrorResponse = $exception->getMessage();
            $internalStatusCode = $exception->getCode();
        }

        return ['status' => $statusCode, 'internalStatus' => $internalStatusCode, 'bodyResponse' => $bodyResponse, 'internalError' => $internalErrorResponse];
    }
}
