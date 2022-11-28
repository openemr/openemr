<?php

/**
 * This controller class handles the hooks and connections for the patient administrative pages in the OpenEMR system.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Controller\Admin;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\UserVideoRegistrationRequest;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TelehealthRegistrationCodeService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthRemoteRegistrationService;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TeleHealthPatientAdminController
{
    /**
     * @var TelehealthGlobalConfig
     */
    private $globalConfig;

    /**
     * @var TeleHealthUserRepository
     */
    private $userRepository;

    /**
     * @var TelehealthRegistrationCodeService
     */
    private $registrationCodeService;

    /**
     * @var TeleHealthRemoteRegistrationService
     */
    private $remoteRegistrationService;

    public function __construct(TelehealthGlobalConfig $globalConfig, TeleHealthUserRepository $userRepository, TeleHealthRemoteRegistrationService $remoteService)
    {
        $this->globalConfig = $globalConfig;
        $this->userRepository = $userRepository;
        $this->registrationCodeService = new TelehealthRegistrationCodeService($globalConfig, $userRepository);
        $this->remoteRegistrationService = $remoteService;
    }

    public function subscribeToEvents(EventDispatcher $dispatcher)
    {
        // TODO: @adunsulag remove these checks when we embed into core.
        // until we embed this into core we need to check to make sure we even exist before adding in this functionality
        if (class_exists('\OpenEMR\Events\Patient\Summary\PortalCredentialsTemplateDataFilterEvent')) {
            $dispatcher->addListener(\OpenEMR\Events\Patient\Summary\PortalCredentialsTemplateDataFilterEvent::EVENT_HANDLE, [$this, 'setupRegistrationCodeField']);
        }

        if (class_exists('\OpenEMR\Events\Patient\Summary\PortalCredentialsUpdatedEvent')) {
            $dispatcher->addListener(\OpenEMR\Events\Patient\Summary\PortalCredentialsUpdatedEvent::EVENT_UPDATE_POST, [$this, 'saveRegistrationCode']);
        }
    }

    public function saveRegistrationCode(\OpenEMR\Events\Patient\Summary\PortalCredentialsUpdatedEvent $event)
    {
        $patientService = new PatientService();
        $patient = $patientService->findByPid($event->getPid());

        $patient['uuid'] = UuidRegistry::uuidToString($patient['uuid']); // need to convert this over so we can work with it.
        $user = $this->userRepository->getUser($patient['uuid']);
        if (empty($user)) {
            // no credentials exist
            $this->remoteRegistrationService->createPatientRegistration($patient);
        } else if ($this->shouldUpdateRegistrationCodeForUser($user)) {
            $user->setRegistrationCode($this->registrationCodeService->generateRegistrationCode());
            $request = $this->remoteRegistrationService->populateRequestFromUser($user);
            // setup our first name and last name pieces here
            $request->setFirstName($patient['fname']);
            $request->setLastName($patient['lname']);
            $this->remoteRegistrationService->updateUserFromRequest($request);
        }
    }

    private function shouldUpdateRegistrationCodeForUser(TeleHealthUser $user)
    {
        return empty($user->getRegistrationCode()) || !empty($_POST['comlink_registration_new_code']);
    }

    public function setupRegistrationCodeField($event)
    {
        // we need to inject in the display of the registation code if the twig template is the display template
        $data = $event->getData() ?? [];
        $data['comlink_app_title'] = $this->globalConfig->getAppTitle();

        // if we have one we will populate it, otherwise it will get generated at the time we save the credentials.
        $registrationCode = $this->registrationCodeService->getRegistrationCodeForPatient($event->getPid());

        // we need to inject in the actual code if the twig template is the email message
        // if matches message.html.twig, message.text.twig
        if (strpos($event->getTemplateName(), 'emails/patient/portal_login/message') === 0) {
            $data['comlink_registration_code'] = $registrationCode;
        } else if ($event->getTemplateName() == 'patient/portal_login/print.html.twig') {
            // inject the data needed for the user edit field
            $extFormField = $data['extensionsFormFields'] ?? [];
            $extFormField['registration-code'] = [
                'comlink_registration_code' => $registrationCode
            ];
            $data['extensionsFormFields'] = $extFormField;
        }
        $event->setData($data);
        return $event;
    }
}
