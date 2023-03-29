<?php

namespace OEMR\OpenEMR\Modules\Voicenote;

use OEMR\OpenEMR\Modules\Voicenote\Controller\VoicenoteMainController;
use OEMR\OpenEMR\Modules\Voicenote\Controller\VoicenoteController;
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
    const MODULE_MENU_NAME = "Voicenote";

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
     * @var VoicenoteGlobalConfig
     */
    private $globalsConfig;

    /**
     * @var SystemLogger
     */
    private $logger;

    /**
     * @var FaxDispatchController
     */
    private $demographicsDispatchController;

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

        $this->globalsConfig = new VoicenoteGlobalConfig($GLOBALS);
        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->logger = new SystemLogger();
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
        // we only show the Addon settings if all of the Addon configuration has been configured.
        if ($this->globalsConfig->isVoicenoteConfigured()) {
            $this->subscribeToTemplateEvents();
            $this->subscribeToProviderEvents();
            // note we need to subscribe at the admin controller as it must precede the registration controller
            // we need our Addon settings setup for a user before we hit the registration controller
            // as there is an implicit data dependency here.
            // TODO: would it be better to abstract this into a separate controller that controls the flow of events
            // instead of relying on the admin being called before the registration?
            $this->getVoicenoteController()->subscribeToEvents($this->eventDispatcher);
        }
    }

    public function getVoicenoteController() {
        if (empty($this->demographicsDispatchController)) {
            $this->demographicsDispatchController = new VoicenoteController(
                $this->globalsConfig,
                $this->getTwig(),
                $this->logger,
                $this->getAssetPath(),
                $this->getCurrentLoggedInUser()
            );
        }
        return $this->demographicsDispatchController;
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
    }

    public function subscribeToTemplateEvents()
    {
    }


    public function addTemplateOverrideLoader(TwigEnvironmentEvent $event)
    {
    }

    private function getPublicPath()
    {
        return $GLOBALS['webroot'] . self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    private function getAssetPath()
    {
        return $this->getPublicPath() . 'assets' . DIRECTORY_SEPARATOR;
    }

    public function addGlobalSettings()
    {
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalModuleSettings']);
    }

    public function addGlobalModuleSettings(GlobalsInitializedEvent $event)
    {
    }

    public function getVoicenoteMainController($isPatient): VoicenoteMainController
    {
        return new VoicenoteMainController(
            $this->getTwig(),
            new SystemLogger(),
            $this->getAssetPath(),
            $isPatient
        );
    }
}
