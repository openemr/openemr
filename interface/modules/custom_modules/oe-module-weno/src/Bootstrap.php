<?php

namespace OpenEMR\Modules\WenoModule;

use OpenEMR\Modules\WenoModule\WenoGlobalConfig;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Services\Globals\GlobalsService;

class Bootstrap {

    const OPENEMR_GLOBALS_LOCATION = "../../../../globals.php";
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    const MODULE_NAME = "";
    const MODULE_MENU_NAME = "Weno";

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
     * @var WenoGlobalConfig
     */
    private $globalsConfig;

    /**
     * @var SystemLogger
     */
    private $logger;

    public function subscribeToEvents()
    {
        $this->addGlobalSettings();
        $this->registerMenuItems();
    }

    public function __construct(EventDispatcher $dispatcher, ?Kernel $kernel = null)
    {
        global $GLOBALS;

        if (empty($kernel)) {
            $kernel = new Kernel();
        }
        $this->eventDispatcher = $dispatcher;

        $this->globalsConfig = new WenoGlobalConfig($GLOBALS);
        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->logger = new SystemLogger();
    }

    /**
     * @return \Twig\Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    public function addGlobalWenoSettings(GlobalsInitializedEvent $event)
    {
        global $GLOBALS;

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        $userMode = (array_key_exists('mode', $_GET) && $_GET['mode'] == 'user');
        
        $service = $event->getGlobalsService();
        $service->addUserSpecificTab(self::MODULE_MENU_NAME);

        foreach ($settings as $key => $config) {
            $value = $GLOBALS[$key] ?? $config['default'];
            if($userMode){
                $service->appendToSection(
                    self::MODULE_MENU_NAME,
                    $key,
                    new GlobalSetting(
                        xlt($config['title']),
                        $config['type'],
                        $value,
                        xlt($config['description']),
                        $config['user_setting']
                    )
                );
            } else {
                if($config['user_setting']){
                    continue;
                }
                $service->appendToSection(
                    self::MODULE_MENU_NAME,
                    $key,
                    new GlobalSetting(
                        xlt($config['title']),
                        $config['type'],
                        $value,
                        xlt($config['description']),
                        $config['user_setting']
                    )
                );
            }
        }
    }

    public function addGlobalSettings()
    {
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalWenoSettings']);
    }

    public function registerMenuItems()
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addCustomMenuItem']);
    }

    public function addCustomMenuItem(MenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'mod0';
        $menuItem->label = xlt("Weno");
        // TODO: pull the install location into a constant into the codebase so if OpenEMR changes this location it
        // doesn't break any modules.
        $menuItem->url = "/interface/modules/custom_modules/oe-module-weno/public/facilities.php";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Several examples are provided
         */
        $menuItem->acl_req = ["patients", "demo"];

        /**
         * If you would like to restrict this menu to only logged in users who have access to see all user data
         */
        //$menuItem->acl_req = ["admin", "users"];

        /**
         * If you would like to restrict this menu to logged in users who can access patient demographic information
         */
        //$menuItem->acl_req = ["users", "demo"];


        /**
         * This menu flag takes a boolean property defined in the $GLOBALS array that OpenEMR populates.
         * It allows a menu item to display if the property is true, and be hidden if the property is false
         */
        //$menuItem->global_req = ["custom_skeleton_module_enable"];

        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'modimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }
}

?>