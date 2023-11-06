<?php

/**
 * Bootstrap file for the exporter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter;

/**
 * Note the below use statements are importing classes from the OpenEMR core codebase
 */
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Events\RestApiExtend\RestApiResourceServiceEvent;
use OpenEMR\Events\RestApiExtend\RestApiScopeEvent;
use OpenEMR\Modules\EhiExporter\Services\EhiExporter;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

// we import our own classes here.. although this use statement is unnecessary it forces the autoloader to be tested.
use OpenEMR\Modules\CustomModuleSkeleton\TaskRestController;


class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    const MODULE_NAME = "oe-module-ehi-exporter";
    const CERTIFIED_RELEASE_VERSION = "7.0.2";
    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private $eventDispatcher;

    /**
     * @var GlobalConfig Holds our module global configuration values that can be used throughout the module.
     */
    private $globalsConfig;

    /**
     * @var string The folder name of the module.  Set dynamically from searching the filesystem.
     */
    private $moduleDirectoryName;

    /**
     * @var \Twig\Environment The twig rendering environment
     */
    private $twig;

    /**
     * @var SystemLogger
     */
    private $logger;

    private static self $instance;

    public function __construct(EventDispatcherInterface $eventDispatcher, ?Kernel $kernel = null)
    {
        if (empty($kernel)) {
            $kernel = new Kernel();
        }

        // NOTE: eventually you will be able to pull the twig container directly from the kernel instead of instantiating
        // it here.
        $twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;

        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->eventDispatcher = $eventDispatcher;

        // we inject our globals value.
        $this->globalsConfig = new GlobalConfig($GLOBALS);
        $this->logger = new SystemLogger();
    }

    public static function instantiate(EventDispatcher $eventDispatcher, Kernel $kernel): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new Bootstrap($eventDispatcher, $kernel);
            self::$instance->subscribeToEvents();
        }
        return self::$instance;
    }

    public function getAssetPath()
    {
        return $GLOBALS['webroot'] . self::MODULE_INSTALLATION_PATH . $this->moduleDirectoryName . "/public/assets/";
    }

    public function getLogger()
    {
        return new SystemLogger();
    }

    public function getExporter()
    {
        $xmlConfigPath = $GLOBALS['webserver_root'] . DIRECTORY_SEPARATOR . 'Documentation' . DIRECTORY_SEPARATOR . 'EHI_Export';
        // . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'openemr.openemr.xml';
        return new EhiExporter(
            $GLOBALS['webserver_root'] . $this->getPublicPath(),
            $this->getPublicPath(),
            $xmlConfigPath,
            $this->getTwig()
        );
    }

    public function getTwig()
    {
        $container = new TwigContainer($this->getTemplatePath(), $GLOBALS['kernel']);
        return $container->getTwig();
    }

    public function subscribeToEvents()
    {
//        $this->addGlobalSettings();
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addCustomModuleMenuItem']);
    }

    public function addCustomModuleMenuItem(MenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'msc';
        $menuItem->menu_id = 'ehiExporter0';
        $menuItem->label = xlt("Electronic Health Information Export");
        $menuItem->url = "/interface/modules/custom_modules/" . self::MODULE_NAME . "/public/";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Only Administrators are allowed to use this feature.
         */
        $menuItem->acl_req = ["admin", "super"];
        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'misimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    /**
     * @return GlobalConfig
     */
    public function getGlobalConfig()
    {
        return $this->globalsConfig;
    }

    public function addGlobalSettings()
    {
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalSettingsSection']);
    }

    public function addGlobalSettingsSection(GlobalsInitializedEvent $event)
    {
        // when we add more configuration we can add this here...
//        $service = $event->getGlobalsService();
//        $section = xlt("Ehi Exporter Module");
//        $service->createSection($section, 'Portal');
//
//        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();
//
//        foreach ($settings as $key => $config) {
//            $value = $GLOBALS[$key] ?? $config['default'];
//            $service->appendToSection(
//                $section,
//                $key,
//                new GlobalSetting(
//                    xlt($config['title']),
//                    $config['type'],
//                    $value,
//                    xlt($config['description']),
//                    true
//                )
//            );
//        }
    }

    private function getPublicPath()
    {
        return self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    public function getTemplatePath()
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }
}
