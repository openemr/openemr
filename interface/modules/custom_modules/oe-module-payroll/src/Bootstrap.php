<?php

/*
 * @package OpenEMR
 *
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2023.
 * @license "All rights reserved"
 */

namespace Juggernaut\Modules\Payroll;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    const MODULE_NAME = "oe-module-payroll";
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

    public function __construct(EventDispatcher $dispatcher, ?Kernel $kernel = null)
    {
        global $GLOBALS;

        if (empty($kernel)) {
            $kernel = new Kernel();
        }

        $this->eventDispatcher = $dispatcher;
        /*$twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;*/

        $this->moduleDirectoryName = basename(dirname(__DIR__));
        //$this->logger = new SystemLogger();

        // we inject our globals value.
        $this->globalsConfig = new GlobalConfig($GLOBALS);
        $this->logger = new SystemLogger();
    }
    public function subscribeToEvents()
    {
            $this->registerMenuItems();
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
        global $GLOBALS;

        $service = $event->getGlobalsService();
        $section = xlt("Payroll Module");
        $service->createSection($section, 'Portal');

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $value = $GLOBALS[$key] ?? $config['default'];
            $service->appendToSection(
                $section,
                $key,
                new GlobalSetting(
                    xlt($config['title']),
                    $config['type'],
                    $value,
                    xlt($config['description']),
                    true
                )
            );
        }
    }


    /**
     * @param TwigEnvironmentEvent $event
     */
    public function addTemplateOverrideLoader(TwigEnvironmentEvent $event)
    {
        try {
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
        } catch (LoaderError $error) {
            $this->logger->errorLogCaller("Failed to create template loader", ['innerMessage' => $error->getMessage(), 'trace' => $error->getTraceAsString()]);
        }
    }

    public function registerMenuItems(): void
    {
            /**
             * @var EventDispatcherInterface $eventDispatcher
             * @var array $module
             * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
             * @global                       $module @see ModulesApplication::loadCustomModule
             */
            $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addProviderRateMenuItem']);
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'adminEarningsReportMenuItem']);
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'providerViewEarningsReportMenuItem']);
    }

    public function addProviderRateMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'adm';
        $menuItem->menu_id = 'adm0';
        $menuItem->label = xlt("Set Providers Rates");
        // TODO: pull the install location into a constant into the codebase so if OpenEMR changes this location it
        // doesn't break any modules.
        $menuItem->url = "/interface/modules/custom_modules/oe-module-payroll/public/provider_rate/index.php";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Several examples are provided
         */
        $menuItem->acl_req = ["admin", "super"];

        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'admimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    public function adminEarningsReportMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'rep';
        $menuItem->menu_id = 'rep0';
        $menuItem->label = xlt("Providers Earnings Admin");
        // TODO: pull the install location into a constant into the codebase so if OpenEMR changes this location it
        // doesn't break any modules.
        $menuItem->url = "/interface/modules/custom_modules/oe-module-payroll/public/reports/admin_provider_earning_report.php";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Several examples are provided
         */
        $menuItem->acl_req = ["admin", "super"];

        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'repimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    public function providerViewEarningsReportMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'rep';
        $menuItem->menu_id = 'rep0';
        $menuItem->label = xlt("Providers Earnings Self");
        // TODO: pull the install location into a constant into the codebase so if OpenEMR changes this location it
        // doesn't break any modules.
        $menuItem->url = "/interface/modules/custom_modules/oe-module-payroll/public/reports/provider_earning_report.php";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Several examples are provided
         */
        $menuItem->acl_req = ["patients", "appt"];

        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'repimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    private function getPublicPath()
    {
        return self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    private function getAssetPath()
    {
        return $this->getPublicPath() . 'assets' . DIRECTORY_SEPARATOR;
    }


}
