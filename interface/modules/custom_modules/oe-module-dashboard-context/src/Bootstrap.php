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
use OpenEMR\Events\PatientDemographics\RenderEvent as pRenderEvent;
use OpenEMR\Events\UserInterface\PageHeadingRenderEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\DashboardContext\Controller\ContextWidgetController;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenEMR\Core\OEGlobalsBag;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/oe-module-dashboard-context";
    const MODULE_NAME = "oe-module-dashboard-context";

    private readonly string $moduleDirectoryName;

    private $logger;

    private readonly string $modulePath;

    public string $installPath;

    public function __construct(
        /**
         * @var EventDispatcherInterface The object responsible for sending and subscribing to events
         */
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->installPath = OEGlobalsBag::getInstance()->get('web_root') . self::MODULE_INSTALLATION_PATH;
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
        $this->registerPageHeadingWidget();
        //$this->registerDashboardWidget(); // TODO sjp Save if we want to allow user to move the dropdown to top of view.
    }

    public function registerDashboardWidget(): void
    {
        $this->eventDispatcher->addListener(pRenderEvent::EVENT_SECTION_LIST_RENDER_BEFORE, $this->renderDashboardWidget(...));
    }

    /**
     * @return void
     */
    public function registerMenuItems(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addCustomMenuItem(...));
    }

    /**
     * Register the page heading widget renderer (navbar dropdown)
     *
     * @return void
     */
    public function registerPageHeadingWidget(): void
    {
        $this->eventDispatcher->addListener(
            PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER,
            $this->renderPageHeadingWidget(...),
            10
        );
    }

    /**
     * Render the context manager widget on the patient dashboard
     * Reserved for future as option of where to locate the widget
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
        if (!(OEGlobalsBag::getInstance()->get('dashboard_context_show_widget') ?? true)) {
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
     * Render the context selector in the page heading navbar
     * This adds a compact dropdown to the title nav area on the demographics page
     * (between the page title and the action buttons)
     *
     * @param PageHeadingRenderEvent $event
     * @return PageHeadingRenderEvent
     */
    public function renderPageHeadingWidget(PageHeadingRenderEvent $event): PageHeadingRenderEvent
    {
        // Debug: Log that listener was called
        $pageId = $event->getPageId();
        $this->logger->debug("DashboardContext: PageHeadingRenderEvent fired", ['page_id' => $pageId]);

        // Only render on demographics/patient dashboard page
        if (!in_array($pageId, ['core.mrd'])) {
            $this->logger->debug("DashboardContext: Skipping - page_id not matched", ['page_id' => $pageId]);
            return $event;
        }

        // Check if widget should be shown
        if (!(OEGlobalsBag::getInstance()->get('dashboard_context_show_widget') ?? true)) {
            return $event;
        }

        try {
            $controller = new ContextWidgetController();
            $navHtml = $controller->renderNavbarDropdown();

            $this->logger->debug("DashboardContext: Appending titleNavContent", ['length' => strlen($navHtml)]);

            // Append HTML content to be injected into the title nav area
            // This will appear between the page title and the action buttons
            // Let modules be modules ...
            $event->appendTitleNavContent($navHtml);
        } catch (\Exception $e) {
            $this->logger->error("DashboardContext: Error rendering navbar widget", ['error' => $e->getMessage()]);
        }

        return $event;
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
