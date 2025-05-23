<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;

use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TelehealthProviderNotEnrolledException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TeleHealthProviderSuspendedException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TelehealthProvisioningServiceRequestException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthProviderRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;

class TeleHealthProvisioningService
{
    /**
     * @var TeleHealthUserRepository
     */
    private $telehealthUserRepo;

    /**
     * @var TeleHealthProviderRepository
     */
    private $providerRepository;

    /**
     * @var TeleHealthRemoteRegistrationService
     */
    private $telehealthRegistration;


    public function __construct(TeleHealthUserRepository $userRepo, TeleHealthProviderRepository $providerRepo, TeleHealthRemoteRegistrationService $registrationService)
    {
        $this->telehealthUserRepo = $userRepo;
        $this->providerRepository = $providerRepo;
        $this->telehealthRegistration = $registrationService;
    }

    /**
     * @return TeleHealthRemoteRegistrationService
     */
    public function getRemoteRegistrationService(): TeleHealthRemoteRegistrationService
    {
        return $this->telehealthRegistration;
    }
    /**
     * @param $user - a user as returned from UserService
     * @return \Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser|null
     * @throws TelehealthProvisioningServiceRequestException
     */
    public function getOrCreateTelehealthProvider($user)
    {
        $providerTelehealthSettings = $this->telehealthUserRepo->getUser($user['uuid']);
        if (empty($providerTelehealthSettings)) {
            if ($this->providerRepository->isEnabledProvider($user['id'])) {
                if ($this->telehealthRegistration->createUserRegistration($user)) {
                    $providerTelehealthSettings = $this->telehealthUserRepo->getUser($user['uuid']);
                } else {
                    throw new TelehealthProvisioningServiceRequestException("Could not create telehealth registration for user " . $user['uuid']);
                }
            } else {
                // we should never hit this situation as we are supposed to prevent launching of appointments on the client side of things.
                throw new TelehealthProviderNotEnrolledException("Provider is either suspended or not enrolled in telehealth. Cannot create telehealth registration for user " . $user['uuid']);
            }
        } else if (!$providerTelehealthSettings->getIsActive()) {
            // provider is disabled... can't launch settings with this provider
            throw new TeleHealthProviderSuspendedException("Provider's telehealth subscription is suspended for user " . $user['uuid']);
        }
        return $providerTelehealthSettings;
    }

    /**
     * @param $patient
     * @return \Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser|null
     * @throws TelehealthProvisioningServiceRequestException
     */
    public function getOrCreateTelehealthPatient($patient)
    {
        $telehealthSettings = $this->telehealthUserRepo->getUser($patient['uuid']);
        if (empty($telehealthSettings)) {
            if ($this->telehealthRegistration->createPatientRegistration($patient)) {
                $telehealthSettings = $this->telehealthUserRepo->getUser($patient['uuid']);
            } else {
                throw new TelehealthProvisioningServiceRequestException("Could not create video registration for patient " . $patient['uuid']);
            }
        }
        return $telehealthSettings;
    }
}
