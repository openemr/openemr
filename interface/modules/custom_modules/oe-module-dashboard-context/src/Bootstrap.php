<?php

/**
 * Dashboard Context Manager Module Bootstrap Class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\DashboardContext;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\DashboardContext\Controller\ContextWidgetController;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/oe-module-dashboard-context";
    const MODULE_NAME = "oe-module-dashboard-context";

    private readonly string $moduleDirectoryName;

    private $logger;

    private readonly string $modulePath;

    public string $installPath;

    public function __construct(/**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events
     */
    private readonly EventDispatcherInterface $eventDispatcher)
    {
        $this->installPath = $GLOBALS['web_root'] . self::MODULE_INSTALLATION_PATH;
        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->modulePath = dirname(__DIR__);
        $this->logger = new SystemLogger();
    }

    /**
     * @return void
     */
    public function subscribeToEvents(): void
    {
        $this->registerMenuItems();
        $this->registerDashboardWidget();
    }

    /**
     * @return void
     */
    public function registerMenuItems(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addCustomMenuItem(...));
    }

    /**
     * Register the dashboard widget renderer
     *
     * @return void
     */
    public function registerDashboardWidget(): void
    {
        $this->eventDispatcher->addListener(
            RenderEvent::EVENT_SECTION_LIST_RENDER_TOP,
            $this->renderDashboardWidget(...),
            10
        );
    }

    /**
     * Render the context manager widget on the patient dashboard
     *
     * @param RenderEvent $event
     * @return void
     */
    public function renderDashboardWidget(RenderEvent $event): void
    {
        $pid = $event->getPid();
        if (empty($pid)) {
            return;
        }

        // Check if widget should be shown
        if (!($GLOBALS['dashboard_context_show_widget'] ?? true)) {
            return;
        }

        try {
            $controller = new ContextWidgetController();
            echo $controller->renderWidget();
        } catch (\Exception $e) {
            $this->logger->error("DashboardContext: Error rendering widget", ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param MenuEvent $event
     * @return MenuEvent
     */
    public function addCustomMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        // Find the Admin menu and add our item as a child
        foreach ($menu as $menuItem) {
            if ($menuItem->menu_id === 'admimg' || ($menuItem->label ?? '') === 'Admin') {
                // Create Dashboard Contexts menu item
                $contextMenuItem = new stdClass();
                $contextMenuItem->requirement = 0;
                $contextMenuItem->target = 'adm0';
                $contextMenuItem->menu_id = 'dashctx0';
                $contextMenuItem->label = xlt("Dashboard Contexts");
                $contextMenuItem->url = self::MODULE_INSTALLATION_PATH . "/public/admin.php";
                $contextMenuItem->children = [];
                $contextMenuItem->acl_req = ["admin", "users"];
                $contextMenuItem->global_req = [];

                // Add to Admin menu children
                $menuItem->children[] = $contextMenuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }
}
