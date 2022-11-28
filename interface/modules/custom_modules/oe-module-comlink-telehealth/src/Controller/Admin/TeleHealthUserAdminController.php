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

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthPersonSettings;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthPersonSettingsRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\User\UserCreatedEvent;
use OpenEMR\Events\User\UserEditRenderEvent;
use OpenEMR\Events\User\UserUpdatedEvent;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;

class TeleHealthUserAdminController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TelehealthGlobalConfig
     */
    private $globalConfig;

    /**
     * @var TeleHealthPersonSettingsRepository
     */
    private $personSettingsRepository;

    public function __construct(TelehealthGlobalConfig $globalConfig, Environment $twig, TeleHealthPersonSettingsRepository $settingsRepository)
    {
        $this->config = $globalConfig;
        $this->twig = $twig;
        $this->personSettingsRepository = $settingsRepository;
    }

    public function subscribeToEvents(EventDispatcher $dispatcher)
    {

        $dispatcher->addListener(UserCreatedEvent::EVENT_HANDLE, [$this, 'saveTelehealthUserAction']);
        $dispatcher->addListener(UserUpdatedEvent::EVENT_HANDLE, [$this, 'saveTelehealthUserAction']);

        // add our user admin flags
        $dispatcher->addListener(UserEditRenderEvent::EVENT_USER_EDIT_RENDER_AFTER, [$this, 'render']);
    }

    public function render(UserEditRenderEvent $event)
    {
        if (!$this->isTelehealthRenderEvent($event)) {
            throw new \InvalidArgumentException("render() called with invalid event object");
        }
        $userAdminTwigData = [
            'forceTelehealthEnabled' => $this->config->shouldAutoProvisionProviders()
            ,'userEnabled' => $this->config->shouldAutoProvisionProviders() // start off with our auto provisioning
            ,'userId' => null
        ];
        // grab our global setting and force the checkbox
        $userId = $event->getUserId();
        if (!empty($userId)) {
            $userAdminTwigData['userId'] = $userId;
            // grab the user, grab our telehealth enabled settings
            // set our checkbox
            $repository = new TeleHealthPersonSettingsRepository(new SystemLogger());
            $settings = $repository->getSettingsForUser($userId);
            if (!empty($settings)) {
                $userAdminTwigData['userEnabled'] = $userAdminTwigData['forceTelehealthEnabled'] ? true : $settings->getIsEnabled();
            }
        }

        // need to grab the current user's
        echo $this->twig->render("comlink/admin/user_admin-extension.html.twig", $userAdminTwigData);
    }

    public function isTelehealthRenderEvent($event)
    {
        return $event instanceof UserEditRenderEvent;
    }

    public function isTelehealthUserEvent($event)
    {
        return $event instanceof UserUpdatedEvent || $event instanceof UserCreatedEvent;
    }

    private function getUserIdFromEvent($event)
    {
        if ($event instanceof UserUpdatedEvent) {
            return $event->getUserId();
        } else if ($event instanceof UserCreatedEvent) {
            $userData = $event->getUserData();
            // we have a uuid but we don't have an id
            $userService = new UserService();
            $user = $userService->getUserByUUID(UuidRegistry::uuidToString($userData['uuid']));
            return $user['id'] ?? null;
        }
        return null;
    }

    public function saveTelehealthUserAction($event)
    {
        if (!$this->isTelehealthUserEvent($event)) {
            throw new \InvalidArgumentException("saveTelehealthUserAction called with invalid event object");
        }
        // need to check our global settings

        $isTelehealthEnabled = ($_POST['comlink-telehealth-user-enable'] ?? "0") == "1";
        if ($this->config->shouldAutoProvisionProviders()) {
            $isTelehealthEnabled = true;
        }

        $userId = $this->getUserIdFromEvent($event);
        if (empty($userId)) {
            throw new \InvalidArgumentException("No user id found to save telehealth settings from event");
        }

        // we need to save our data that holds the user settings.
        $settings = $this->personSettingsRepository->getSettingsForUser($userId);
        if (empty($settings)) {
            $settings = new TeleHealthPersonSettings();
            $settings->setIsPatient(false);
            $settings->setDbRecordId($userId);
        }
        $settings->setIsEnabled($isTelehealthEnabled);

        // save our configured telehealth settings
        $this->personSettingsRepository->saveSettingsForPerson($settings);
    }
}
