<?php

/**
 * This bootstrap file connects the module to the OpenEMR system hooking to the API, api scopes, and event notifications
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule;

use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\Admin\TeleHealthPatientAdminController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\Admin\TeleHealthUserAdminController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleconferenceRoomController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthCalendarController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthFrontendSettingsController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthPatientPortalController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthVideoRegistrationController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\CalendarEventCategoryRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthPersonSettingsRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthProviderRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthSessionRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\ParticipantListService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthParticipantInvitationMailerService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthProvisioningService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TelehealthRegistrationCodeService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthRemoteRegistrationService;
use Laminas\Form\Element\Tel;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Appointments\AppointmentSetEvent;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Bootstrap
{
    const OPENEMR_GLOBALS_LOCATION = "../../../../globals.php";
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    const MODULE_NAME = "";
    const MODULE_MENU_NAME = "TeleHealth";

    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private $eventDispatcher;

    private $moduleDirectoryName;

    /**
     * The OpenEMR Twig Environment
     * @var Environment
     */
    private $twig;

    /**
     * @var TelehealthGlobalConfig
     */
    private $globalsConfig;

    const COMLINK_VIDEO_TELEHEALTH_API = 'comlink_telehealth_video_uri';

    /**
     * @var TeleHealthPatientPortalController
     */
    private $patientPortalController;

    /**
     * @var TeleHealthVideoRegistrationController
     */
    private $registrationController;

    /**
     * @var TeleHealthUserAdminController
     */
    private $adminSettingsController;

    /**
     * @var TeleHealthPatientAdminController
     */
    private $patientAdminSettingsController;

    /**
     * @var TeleHealthPersonSettingsRepository
     */
    private $personSettingsRepository;

    /**
     * @var TeleHealthProviderRepository
     */
    private $providerRepository;

    /**
     * @var SystemLogger
     */
    private $logger;

    /**
     * @var TeleHealthCalendarController
     */
    private $calendarController;

    /**
     * @var array Hashmap of Service classname => Service used for dependency injection
     */
    private $serviceRegistry = [];

    public function __construct(EventDispatcher $dispatcher, ?Kernel $kernel = null)
    {
        global $GLOBALS;

        if (empty($kernel)) {
            $kernel = new Kernel();
        }
        $this->eventDispatcher = $dispatcher;
        $twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;

        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->logger = new SystemLogger();
        $this->globalsConfig = new TelehealthGlobalConfig($this->getURLPath(), $this->moduleDirectoryName, $this->twig);
    }

    public function getGlobalConfig(): TelehealthGlobalConfig
    {
        return $this->globalsConfig;
    }

    public function getTemplatePath()
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }

    public function getURLPath()
    {
        return $GLOBALS['webroot'] . self::MODULE_INSTALLATION_PATH . $this->moduleDirectoryName . "/public/";
    }

    /**
     * @return \Twig\Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    public function subscribeToEvents()
    {
        $this->addGlobalSettings();
        // we only show the telehealth settings if all of the telehealth configuration has been configured.
        if ($this->globalsConfig->isTelehealthConfigured()) {
            $this->subscribeToTemplateEvents();
            $this->subscribeToProviderEvents();
            // note we need to subscribe at the admin controller as it must precede the registration controller
            // we need our telehealth settings setup for a user before we hit the registration controller
            // as there is an implicit data dependency here.
            // TODO: would it be better to abstract this into a separate controller that controls the flow of events
            // instead of relying on the admin being called before the registration?
            $this->getTeleHealthUserAdminController()->subscribeToEvents($this->eventDispatcher);
            $this->getTeleHealthPatientAdminController()->subscribeToEvents($this->eventDispatcher);
            $this->getPatientPortalController()->subscribeToEvents($this->eventDispatcher);
            $this->getRegistrationController()->subscribeToEvents($this->eventDispatcher);
            $this->getCalendarController()->subscribeToEvents($this->eventDispatcher);
        }
    }

    public function getCalendarController()
    {
        if (empty($this->calendarController)) {
            $this->calendarController = new TeleHealthCalendarController(
                $this->globalsConfig,
                $this->getTwig(),
                $this->logger,
                $this->getAssetPath(),
                $this->getCurrentLoggedInUser()
            );
        }
        return $this->calendarController;
    }

    public function getCurrentLoggedInUser()
    {
        return $_SESSION['authUserID'] ?? null;
    }

    public function subscribeToProviderEvents()
    {
        $this->eventDispatcher->addListener(AppointmentSetEvent::EVENT_HANDLE, [$this, 'createSessionRecord'], 10);
    }

    public function createSessionRecord(AppointmentSetEvent $event)
    {
        $pc_catid = $event->givenAppointmentData()['pc_catid'] ?? null;
        $calCatRepo = new CalendarEventCategoryRepository();
        if (empty($calCatRepo->getEventCategoryForId($pc_catid))) {
            // not a telehealth category so we will just skip this.
            return;
        }

        $sessionRepo = new TeleHealthSessionRepository();
        $sessionRepo->getSessionByAppointmentId($event->eid);
    }

    public function subscribeToTemplateEvents()
    {
        $this->eventDispatcher->addListener(TwigEnvironmentEvent::EVENT_CREATED, [$this, 'addTemplateOverrideLoader']);
        $this->eventDispatcher->addListener(RenderEvent::EVENT_BODY_RENDER_POST, [$this, 'renderMainBodyTelehealthScripts']);
    }


    public function addTemplateOverrideLoader(TwigEnvironmentEvent $event)
    {
        $twig = $event->getTwigEnvironment();
        if ($twig === $this->twig) {
            // we do nothing if its our own twig environment instantiated that we already setup
            return;
        }
        // we make sure we can override our file system directory here.
        $loader = $twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->prependPath($this->getTemplatePath());
        }
    }

    private function getPublicPathFQDN()
    {
        // return the public path with the fully qualified domain name in it
        // qualified_site_addr already has the webroot in it.
        return $GLOBALS['qualified_site_addr'] . self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . '/' . 'public' . '/';
    }

    private function getAssetPath()
    {
        return $this->getURLPath() . 'assets' . '/';
    }

    public function renderMainBodyTelehealthScripts()
    {
        $scriptMinExtension = $this->globalsConfig->isDebugModeEnabled() ? ".js" : ".min.js";
        ?>
        <script src="<?php echo $this->getAssetPath();?>../<?php echo CacheUtils::addAssetCacheParamToPath("index.php"); ?>&action=get_telehealth_settings"></script>
        <link rel="stylesheet" href="<?php echo $this->getAssetPath();?>css/<?php echo CacheUtils::addAssetCacheParamToPath("telehealth.css"); ?>">
        <script src="<?php echo $this->getAssetPath();?>js/dist/<?php echo CacheUtils::addAssetCacheParamToPath("telehealth" . $scriptMinExtension); ?>"></script>
        <script src="<?php echo $this->getAssetPath();?>js/<?php echo CacheUtils::addAssetCacheParamToPath("telehealth-provider.js"); ?>"></script>
        <?php
    }

    public function addGlobalSettings()
    {
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalTeleHealthSettings']);
    }

    public function addGlobalTeleHealthSettings(GlobalsInitializedEvent $event)
    {
        $service = $event->getGlobalsService();
        $this->globalsConfig->setupConfiguration($service);
    }

    public function getTeleconferenceRoomController($isPatient): TeleconferenceRoomController
    {
        return new TeleconferenceRoomController(
            $this->getTwig(),
            new SystemLogger(),
            $this->getRegistrationController(),
            $this->getMailerService(),
            $this->getFrontendSettingsController(),
            $this->globalsConfig,
            $this->getProvisioningService(),
            $this->getParticipantListService(),
            $this->getAssetPath(),
            $isPatient
        );
    }

    public function getProvisioningService()
    {
        $service = $this->getService(TeleHealthProvisioningService::class);
        if (empty($service)) {
            $service = new TeleHealthProvisioningService(
                $this->getUserRepository(),
                $this->getProviderRepository(),
                $this->getRemoteRegistrationService()
            );
            $this->storeService(TeleHealthProvisioningService::class, $service);
        }
        return $service;
    }

    public function getParticipantListService()
    {
        $service = $this->getService(ParticipantListService::class);
        if (empty($service)) {
            $service = new ParticipantListService($this->getTwig(), $this->getProvisioningService(), $this->getPublicPathFQDN());
            $this->storeService(ParticipantListService::class, $service);
        }
        return $service;
    }

    public function getRegistrationController(): TeleHealthVideoRegistrationController
    {
        $globalsConfig = $this->globalsConfig;
        if (empty($this->registrationController)) {
            $this->registrationController = new TeleHealthVideoRegistrationController(
                $this->getRemoteRegistrationService(),
                $this->getProviderRepository()
            );
        }
        return $this->registrationController;
    }
    public function getPatientPortalController(): TeleHealthPatientPortalController
    {
        if (empty($this->patientPortalController)) {
            $this->patientPortalController = new TeleHealthPatientPortalController($this->twig, $this->getAssetPath(), $this->globalsConfig);
        }
        return $this->patientPortalController;
    }

    private function getTeleHealthPatientAdminController()
    {
        if (empty($this->patientAdminSettingsController)) {
            $this->patientAdminSettingsController = new TeleHealthPatientAdminController(
                $this->globalsConfig,
                $this->getUserRepository(),
                $this->getRemoteRegistrationService()
            );
        }
        return $this->patientAdminSettingsController;
    }

    private function getTeleHealthUserAdminController()
    {
        if (empty($this->adminSettingsController)) {
            $this->adminSettingsController = new TeleHealthUserAdminController(
                $this->globalsConfig,
                $this->getTwig(),
                $this->getPersonSettingsRepository()
            );
        }
        return $this->adminSettingsController;
    }

    private function getPersonSettingsRepository(): TeleHealthPersonSettingsRepository
    {
        if (empty($this->personSettingsRepository)) {
            $this->personSettingsRepository = new TeleHealthPersonSettingsRepository($this->logger);
        }
        return $this->personSettingsRepository;
    }

    private function getProviderRepository(): TeleHealthProviderRepository
    {
        if (empty($this->providerRepository)) {
            $this->providerRepository = new TeleHealthProviderRepository($this->logger, $this->globalsConfig);
        }
        return $this->providerRepository;
    }

    private function getRegistrationCodeService()
    {
        $service = $this->getService(TelehealthRegistrationCodeService::class);
        if (empty($service)) {
            $service = new TelehealthRegistrationCodeService($this->globalsConfig, $this->getUserRepository());
            $this->storeService(TelehealthRegistrationCodeService::class, $service);
        }
        return $service;
    }

    private function getMailerService()
    {
        return new TeleHealthParticipantInvitationMailerService($this->eventDispatcher, $this->getTwig(), $this->getPublicPathFQDN(), $this->globalsConfig);
    }

    private function getFrontendSettingsController()
    {
        return new TeleHealthFrontendSettingsController($this->getAssetPath(), $this->getTwig(), $this->globalsConfig);
    }

    private function getRemoteRegistrationService()
    {
        $service = $this->getService(TeleHealthRemoteRegistrationService::class);
        if (empty($service)) {
            $service = new TeleHealthRemoteRegistrationService($this->globalsConfig, $this->getRegistrationCodeService());
            $this->storeService(TeleHealthRemoteRegistrationService::class, $service);
        }
        return $service;
    }
    private function getUserRepository()
    {
        $service = $this->getService(TeleHealthUserRepository::class);
        if (empty($service)) {
            $service = new TeleHealthUserRepository();
            $this->storeService(TeleHealthUserRepository::class, $service);
        }
        return $service;
    }

    private function storeService($className, $obj)
    {
        $this->serviceRegistry[$className] = $obj;
    }

    private function getService($className)
    {
        if (isset($this->serviceRegistry[$className])) {
            return $this->serviceRegistry[$className];
        }
        return null;
    }
}
