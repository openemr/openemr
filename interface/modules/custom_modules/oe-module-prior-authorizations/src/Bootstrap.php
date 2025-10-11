<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * All Rights Reserved
 *
 */

namespace Juggernaut\OpenEMR\Modules\PriorAuthModule;

use OpenEMR\Core\Kernel;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Menu\PatientMenuEvent;
use OpenEMR\Common\Twig\TwigContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = 'oe-module-prior-authorizations';
    public const MODULE_PATH = __DIR__;
    public const MODULE_NAMESPACE = 'Juggernaut\\OpenEMR\\Modules\\PriorAuthModule';

    private array $paths = [];
    private $twig;

    public function __construct(/**
         * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
         */
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?Kernel $kernel = null
    ) {
        $this->paths = [self::MODULE_PATH . '/templates'];
        $this->twig = new TwigContainer($this->getTemplatePath(), $this->kernel);
        $this->twigEnv = $this->twig->getTwig();
    }


    public function subscribeToEvents(): void
    {
        $this->registerMenuItems();
        //$this->registerPatientMenuItems();
    }

    public function registerMenuItems(): void
    {
        /**
         * @var EventDispatcherInterface $eventDispatcher
         * @var array $module
         * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
         * @global                       $module @see ModulesApplication::loadCustomModule
         */
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'oe_module_priorauth_add_menu_item']);
        $this->eventDispatcher->addListener(PatientMenuEvent::MENU_UPDATE, [$this, 'oe_module_priorauth_patient_menu_item']);
    }

    public function registerPatientMenuItems(): void
    {
        $this->eventDispatcher->addListener(PatientMenuEvent::MENU_UPDATE, [$this, 'oe_module_priorauth_patient_menu_item']);
    }

    public function oeModulePriorauthAddMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'auth0';
        $menuItem->label = xlt("Authorizations");
        $menuItem->url = "/interface/modules/custom_modules/oe-module-prior-authorizations/public/reports/list_report.php";
        $menuItem->children = [];
        $menuItem->acl_req = ["patients", "docs"];
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'repimg') {
                foreach ($item->children as $childItem) {
                    if ($childItem->label == 'Insurance') {
                        $childItem->children[] = $menuItem;
                        break 2;
                    }
                }
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    public function oeModulePriorauthPatientMenuItem(PatientMenuEvent $menuEvent): PatientMenuEvent
    {
        $existingMenu = $menuEvent->getMenu();

        $menuItem = new \stdClass();
        $menuItem->label = "Prior Auths";
        $menuItem->url = $GLOBALS['webroot'] . "/interface/modules/custom_modules/oe-module-prior-authorizations/public/patient_auth_manager.php";
        $menuItem->menu_id = "mod_pa";
        $menuItem->target = "mod";

        $existingMenu[] = $menuItem;

        $menuEvent->setMenu($existingMenu);

        return $menuEvent;
    }

    private function getTemplatePath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }
}
