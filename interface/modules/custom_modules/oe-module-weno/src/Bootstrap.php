<?php

/**
 * Contains all of the Weno global settings and configuration
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Omega Systems Group <https://omegasystemsgroup.com/>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\Patient\PatientBeforeCreatedAuxEvent;
use OpenEMR\Events\Patient\PatientUpdatedEventAux;
use OpenEMR\Events\PatientDemographics\RenderEvent as pRenderEvent;
use OpenEMR\Events\PatientDemographics\RenderPharmacySectionEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\WenoModule\Services\ModuleService;
use OpenEMR\Modules\WenoModule\Services\SelectedPatientPharmacy;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class Bootstrap
{
    const MODULE_MENU_NAME = "Weno";

    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private $eventDispatcher;

    private $moduleDirectoryName;

    /**
     * The OpenEMR Twig Environment
     *
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

    private string $modulePath;

    /**
     * @var SelectedPatientPharmacy
     */
    private SelectedPatientPharmacy $selectedPatientPharmacy;
    public string $installPath;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->installPath = $GLOBALS['web_root'] . "/interface/modules/custom_modules/oe-module-weno";
        $this->eventDispatcher = $dispatcher;
        $this->globalsConfig = new WenoGlobalConfig();
        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->modulePath = dirname(__DIR__);
        $this->logger = new SystemLogger();
        $this->selectedPatientPharmacy = new SelectedPatientPharmacy();
    }

    /**
     * @return void
     */
    public function subscribeToEvents(): void
    {
        $modService = new ModuleService();
        // let Admin configure Weno if module is not configured.
        $this->addGlobalSettings();
        if (!$modService->isWenoConfigured()) {
            return;
        }
        $this->registerMenuItems();
        $this->registerDemographicsEvents();
        $this->demographicsSelectorEvents();
        $this->demographicsDisplaySelectedEvents();
        $this->patientSaveEvents();
        $this->patientUpdateEvents();
        $modService::setModuleState('oe-module-weno', '1', '0');
    }

    /**
     * @return \Twig\Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @param GlobalsInitializedEvent $event
     * @return void
     */
    public function addGlobalWenoSettings(GlobalsInitializedEvent $event): void
    {
        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        $userMode = (array_key_exists('mode', $_GET) && $_GET['mode'] == 'user');

        $service = $event->getGlobalsService();
        $service->addUserSpecificTab(self::MODULE_MENU_NAME);

        foreach ($settings as $key => $config) {
            $value = $GLOBALS[$key] ?? $config['default'];
            if ($userMode) {
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
                if ($config['user_setting']) {
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

    /**
     * @return void
     */
    public function registerDemographicsEvents(): void
    {
        $this->eventDispatcher->addListener(pRenderEvent::EVENT_SECTION_LIST_RENDER_BEFORE, [$this, 'renderWenoSection']);
    }

    /**
     * @param pRenderEvent $event
     * @return void
     */
    public function renderWenoSection(pRenderEvent $event): void
    {
        $path = __DIR__;
        $path = str_replace("src", "templates", $path);
        $pid = $event->getPid();
        ?>
        <section class="card mb-2">
            <?php
            // Weno expand collapse widget
            $widgetTitle = self::MODULE_MENU_NAME . " " . xlt("eRx");
            $widgetLabel = "wenocard";
            $widgetButtonLabel = xl("Edit");
            $widgetButtonLink = ""; // "return newEvt();";
            $widgetButtonClass = "d-none";
            $linkMethod = "html";
            $bodyClass = "notab";
            $widgetAuth = false;
            $fixedWidth = false;
            $forceExpandAlways = false;

            expand_collapse_widget(
                $widgetTitle,
                $widgetLabel,
                $widgetButtonLabel,
                $widgetButtonLink,
                $widgetButtonClass,
                $linkMethod,
                $bodyClass,
                $widgetAuth,
                $fixedWidth,
                $forceExpandAlways
            );
            ?>

            <div> <?php include $path . "/weno_fragment.php"; ?> </div>
        </section>
        <?php
    }

    /**
     * @return void
     */
    public function addGlobalSettings(): void
    {
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalWenoSettings']);
    }

    /**
     * @return void
     */
    public function registerMenuItems(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addCustomMenuItem']);
    }

    /**
     * @param MenuEvent $event
     * @return MenuEvent
     */
    public function addCustomMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();
        // Top level menu
        $topMenu = new \stdClass();
        $topMenu->requirement = 0;
        $topMenu->target = 'adm0';
        $topMenu->menu_id = 'adm';
        $topMenu->label = xlt("Weno eRx Tools");
        $topMenu->icon = "fa-caret-right";
        $topMenu->children = [];
        $topMenu->acl_req = ["admin", "super"];
        $topMenu->global_req = ["weno_rx_enable"];
        //Prescription Log
        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'rep';
        $menuItem->menu_id = 'rep0';
        $menuItem->label = xlt("Weno Prescription Log");
        $menuItem->url = "/interface/modules/custom_modules/oe-module-weno/templates/rxlogmanager.php";
        $menuItem->children = [];
        $menuItem->acl_req = ["patients", "demo", 'write'];
        $menuItem->global_req = ["weno_rx_enable"];
        //Weno log
        $dlMenu = new \stdClass();
        $dlMenu->requirement = 0;
        $dlMenu->target = 'adm1';
        $dlMenu->menu_id = 'adm';
        $dlMenu->label = xlt("Weno Downloads Management");
        $dlMenu->url = "/interface/modules/custom_modules/oe-module-weno/templates/download_log_viewer.php";
        $dlMenu->children = [];
        $dlMenu->acl_req = ["admin", "super"];
        $dlMenu->global_req = ["weno_rx_enable"];
        //Weno Setup
        $setupMenu = new \stdClass();
        $setupMenu->requirement = 0;
        $setupMenu->target = 'adm0';
        $setupMenu->menu_id = 'adm';
        $setupMenu->label = xlt("Weno eRx Service Setup");
        $setupMenu->url = "/interface/modules/custom_modules/oe-module-weno/templates/weno_setup.php";
        $setupMenu->children = [];
        $setupMenu->acl_req = ["admin", "super"];
        $setupMenu->global_req = ["weno_rx_enable"];
        // Background Services
        $serviceMenu = new \stdClass();
        $serviceMenu->requirement = 0;
        $serviceMenu->target = 'rpt0';
        $serviceMenu->menu_id = 'rep';
        $serviceMenu->label = xlt("Background Services (Convenience)");
        $serviceMenu->url = "/interface/reports/background_services.php";
        $serviceMenu->children = [];
        $serviceMenu->acl_req = ["admin", "super"];
        $serviceMenu->global_req = ["weno_rx_enable"];
        // Write the menu items to the menu
        foreach ($menu as $item) {
            if ($item->menu_id == 'admimg') {
                $item->children[] = $topMenu;
                foreach ($item->children as $other) {
                    if ($other->label == 'Weno eRx Tools') {
                        $other->children[] = $dlMenu;
                        $other->children[] = $setupMenu;
                        $other->children[] = $serviceMenu;
                        break;
                    }
                }
            }
            if ($item->menu_id == 'repimg') {
                foreach ($item->children as $clientReport) {
                    if ($clientReport->label == 'Clients') {
                        $clientReport->children[] = $menuItem;
                        break;
                    }
                }
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    /**
     * @return void
     */
    public function demographicsSelectorEvents(): void
    {
        $this->eventDispatcher->addListener(RenderPharmacySectionEvent::RENDER_AFTER_PHARMACY_SECTION, [$this, 'renderWenoPharmacySelector']);
    }

    /**
     * @return void
     */
    public function renderWenoPharmacySelector(): void
    {
        include_once($this->modulePath) . "/templates/pharmacy_list_form.php";
    }

    /**
     * @return void
     */
    public function demographicsDisplaySelectedEvents(): void
    {
        $this->eventDispatcher->addListener(RenderPharmacySectionEvent::RENDER_AFTER_SELECTED_PHARMACY_SECTION, [$this, 'renderSelectedWenoPharmacies']);
    }

    /**
     * @return void
     */
    public function renderSelectedWenoPharmacies(): void
    {
        echo "<br>";
        include_once($this->modulePath) . "/templates/pharmacy_list_display.php";
    }

    /**
     * @return void
     */
    public function patientSaveEvents(): void
    {
        $this->eventDispatcher->addListener(PatientBeforeCreatedAuxEvent::EVENT_HANDLE, [$this, 'persistPatientWenoPharmacies']);
    }

    /**
     * @param PatientBeforeCreatedAuxEvent $event
     * @return void
     */
    public function persistPatientWenoPharmacies(PatientBeforeCreatedAuxEvent $event): void
    {
        $patientData = $event->getPatientData();
        $this->selectedPatientPharmacy->prepSelectedPharmacy($patientData);
    }

    /**
     * @return void
     */
    public function patientUpdateEvents(): void
    {
        $this->eventDispatcher->addListener(PatientUpdatedEventAux::EVENT_HANDLE, [$this, 'updatePatientWenoPharmacies']);
    }

    /**
     * @param PatientUpdatedEventAux $event
     * @return void
     */
    public function updatePatientWenoPharmacies(PatientUpdatedEventAux $event): void
    {
        $updatedPatientData = $event->getUpdatedPatientData();
        $this->selectedPatientPharmacy->prepForUpdatePharmacy($updatedPatientData);
    }
}

?>
