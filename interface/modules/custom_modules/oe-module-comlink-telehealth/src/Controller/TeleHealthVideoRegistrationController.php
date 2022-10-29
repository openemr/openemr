<?php

/**
 * Communicates with the Comlink User provisioning api.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Controller;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\UserVideoRegistrationRequest;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthPersonSettingsRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthProviderRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TelehealthRegistrationCodeService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthRemoteRegistrationService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Patient\PatientCreatedEvent;
use OpenEMR\Events\Patient\PatientUpdatedEvent;
use OpenEMR\Events\User\UserCreatedEvent;
use OpenEMR\Events\User\UserUpdatedEvent;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Exception;

class TeleHealthVideoRegistrationController
{
    /**
     * Repository for saving / retrieving telehealth user settings.
     * @var TeleHealthUserRepository
     */
    private $userRepository;


    /**
     * @var SystemLogger
     */
    private $logger;

    /**
     * @var TeleHealthProviderRepository
     */
    private $providerRepository;


    /**
     * @var TeleHealthRemoteRegistrationService
     */
    private $remoteService;

    public function __construct(TeleHealthRemoteRegistrationService $remoteService, TeleHealthProviderRepository $repo)
    {
        $this->userRepository = new TeleHealthUserRepository();
        $this->remoteService = $remoteService;
        $this->providerRepository = $repo;
        $this->logger = new SystemLogger();
    }

    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener(PatientCreatedEvent::EVENT_HANDLE, [$this, 'onPatientCreatedEvent']);
        $eventDispatcher->addListener(PatientUpdatedEvent::EVENT_HANDLE, [$this, 'onPatientUpdatedEvent']);
        $eventDispatcher->addListener(UserCreatedEvent::EVENT_HANDLE, [$this, 'onUserCreatedEvent']);
        $eventDispatcher->addListener(UserUpdatedEvent::EVENT_HANDLE, [$this, 'onUserUpdatedEvent']);
    }

    public function onPatientCreatedEvent(PatientCreatedEvent $event)
    {
        $patient = $event->getPatientData();
        $this->logger->debug(
            self::class . "->onPatientCreatedEvent received for patient ",
            ['uuid' => $patient['uuid'] ?? null, 'patient' => $patient]
        );
        try {
            $patient['uuid'] = UuidRegistry::uuidToString($patient['uuid']); // convert uuid to a string value
            $this->createPatientRegistration($patient);
        } catch (Exception $exception) {
            $this->logger->errorLogCaller("Failed to create patient registration. Error: "
                . $exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'patient' => $patient['uuid']]);
        }
    }

    public function onPatientUpdatedEvent(PatientUpdatedEvent $event)
    {
        try {
            $patient = $event->getNewPatientData();
            $oldPatient = $event->getDataBeforeUpdate();
            // we need the patient uuid so we are going to grab it from the pid
            $patientService = new PatientService();

            $patient['uuid'] = UuidRegistry::uuidToString($oldPatient['uuid']); // convert uuid to a string value
            $this->logger->debug(
                self::class . "->onPatientUpdatedEvent received for patient ",
                ['uuid' => $patient['uuid'] ?? null, 'patient' => $patient]
            );
            // let's grab the patient data and create the patient if its not registered
            $apiUser = $this->userRepository->getUser($patient['uuid']);
            if (empty($apiUser)) {
                $this->createPatientRegistration($patient);
            }
        } catch (Exception $exception) {
            $this->logger->errorLogCaller("Failed to create patient registration. Error: "
                . $exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'patient' => $patient['uuid']]);
        }
    }

    public function onUserCreatedEvent(UserCreatedEvent $event)
    {
        try {
            $user = $event->getUserData();
            $userService = new UserService();
            // our event doesn't have the uuid which is what we need
            $userWithUuid = $userService->getUserByUsername($event->getUsername());
            if (empty($userWithUuid)) {
                throw new \InvalidArgumentException("Could not find user with username " . $event->getUsername());
            }

            // we need to find out if we
            $providerRepo = $this->providerRepository;
            // find out if the provider is enabled, if so we create the registration
            $this->logger->debug(
                self::class . "->onUserCreatedEvent received for user ",
                ['username' => $event->getUsername(), 'userWithUuid' => $userWithUuid, 'uuid' => $userWithUuid['uuid'] ?? null]
            );
            if ($providerRepo->isEnabledProvider($userWithUuid['id'])) {
                $this->createUserRegistration($userWithUuid);
            } else {
                $this->logger->debug(
                    self::class . "->onUserCreatedEvent skipping registration as user is not enrolled",
                    ['username' => $event->getUsername(), 'userWithUuid' => $userWithUuid, 'uuid' => $userWithUuid['uuid'] ?? null]
                );
            }
        } catch (Exception $exception) {
            $this->logger->errorLogCaller("Failed to create user registration. Error: "
                . $exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'user' => $user['uuid']]);
        }
    }

    public function onUserUpdatedEvent(UserUpdatedEvent $event)
    {
        try {
            $user = $event->getNewUserData();
            $userService = new UserService();
            // our event doesn't have the uuid which is what we need
            $userWithUuid = $userService->getUser($event->getUserId());
            if (empty($userWithUuid)) {
                throw new \InvalidArgumentException("Could not find user with username " . $event->getUsername());
            }
            $this->logger->debug(self::class . "->onUserUpdatedEvent received for user ", ['uuid' => $userWithUuid['uuid'] ?? null]);

            $providerRepo = $this->providerRepository;

            // create the registration
            $apiUser = $this->userRepository->getUser($userWithUuid['uuid']);

            if ($providerRepo->isEnabledProvider($userWithUuid['id'])) {
                // create our registration if there is one
                if (empty($apiUser)) {
                    $this->logger->debug(self::class . "->onUserUpdatedEvent registering user with comlink", ['uuid' => $userWithUuid['uuid'] ?? null]);
                    $this->createUserRegistration($userWithUuid);
                } else {
                    if (!$apiUser->getIsActive()) {
                        $this->logger->debug(
                            self::class . "->onUserUpdatedEvent user auth record is suspended, activating",
                            ['uuid' => $userWithUuid['uuid'] ?? null]
                        );
                        // we need to activate the user
                        $this->resumeUser($apiUser->getUsername(), $apiUser->getAuthToken());
                    } else {
                        $this->logger->debug(
                            self::class . "->onUserUpdatedEvent user auth record is already active",
                            ['uuid' => $userWithUuid['uuid'] ?? null]
                        );
                        // TODO: if we ever want to update the password registration here we can do that here
                        // since we don't change the username and its a randomly generated password, there's no need to change
                        // the password.
                    }
                }
            } else {
                // we need to find out if a registration exists... if it does we need to deactivate it
                if (empty($apiUser)) {
                    $this->logger->debug(
                        self::class . "->onUserUpdatedEvent telehealth disabled and no auth record exists",
                        ['uuid' => $userWithUuid['uuid'] ?? null]
                    );
                    // we do nothing here if the provider is not enabled and there's no auth we just ignore this
                } else if ($apiUser->getIsActive()) {
                    $this->logger->debug(
                        self::class . "->onUserUpdatedEvent telehealth is disabled but registration is active. suspending user",
                        ['uuid' => $userWithUuid['uuid'] ?? null]
                    );
                    $this->suspendUser($apiUser->getUsername(), $apiUser->getAuthToken());
                }
            }
        } catch (Exception $exception) {
            $this->logger->errorLogCaller("Failed to create user registration. Error: "
                . $exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'user' => $user]);
        }
    }

    public function createPatientRegistration($patient)
    {
        return $this->remoteService->createPatientRegistration($patient);
    }

    public function createUserRegistration($user)
    {
        return $this->remoteService->createUserRegistration($user);
    }

    /**
     * Allows the user repository to be set for testing or extension purposes
     * @param TeleHealthUserRepository $userRepository
     */
    public function setTelehealthUserRepository(TeleHealthUserRepository $userRepository)
    {
        // TODO: @adunsulag refactor unit tests so we don't have this layer of indirections since this is only used in unit tests right now
        $this->remoteService->setTelehealthUserRepository($userRepository);
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
        return $this->remoteService->addNewUser($request);
    }

    /**
     * Updates an existing provisioned user with the Comlink video api system.  Everything but username can be changed
     * @param UserVideoRegistrationRequest $request
     * @return false|int returns false if the user fails to update, otherwise returns the integer id of the updated user
     */
    public function updateUser(UserVideoRegistrationRequest $request)
    {
        return $this->remoteService->updateUserFromRequest($request);
    }

    public function suspendUser(string $username, string $password): bool
    {
        return $this->remoteService->suspendUser($username, $password);
    }

    public function resumeUser(string $username, string $password): bool
    {
        return $this->remoteService->suspendUser($username, $password);
    }

    public function deactivateUser(string $username, string $password)
    {
        return $this->remoteService->deactivateUser($username, $password);
    }
}
