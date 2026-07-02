<?php

/**
 * FQHC module bootstrap class.
 *
 * Wires the module's event subscribers. For Step 1 this only adds a top-level
 * "FQHC" navigation item (additively, via the menu event) that opens the host
 * page. Nothing here modifies a certified code path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\Fqhc;

use OpenEMR\Menu\MenuEvent;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_INSTALLATION_PATH = '/interface/modules/custom_modules/oe-module-fqhc';
    private const MENU_ID = 'fqhc0';
    private const REPORT_MENU_ID = 'fqhc_report0';
    private const ELIGIBILITY_WORKLIST_MENU_ID = 'fqhc_eligibility_worklist0';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addMenuItem(...));
    }

    /**
     * Append a top-level "FQHC" menu item (opening the Patient Snapshot) with a
     * "UDS Report" child. Guarded so repeated dispatches never duplicate the
     * entry.
     */
    public function addMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        foreach ($menu as $item) {
            if ($item instanceof stdClass && ($item->menu_id ?? null) === self::MENU_ID) {
                return $event;
            }
        }

        $fqhc = new stdClass();
        $fqhc->requirement = 0;
        $fqhc->target = 'fqhc';
        $fqhc->menu_id = self::MENU_ID;
        $fqhc->label = xlt('FQHC');
        $fqhc->url = self::MODULE_INSTALLATION_PATH . '/public/index.php';
        $fqhc->children = [$this->reportMenuItem(), $this->eligibilityWorklistMenuItem()];
        $fqhc->acl_req = ['patients', 'demo'];
        $fqhc->global_req = [];

        $menu[] = $fqhc;
        $event->setMenu($menu);

        return $event;
    }

    /**
     * The "UDS Report" child item that opens the reporting page.
     */
    private function reportMenuItem(): stdClass
    {
        $report = new stdClass();
        $report->requirement = 0;
        $report->target = 'fqhc-report';
        $report->menu_id = self::REPORT_MENU_ID;
        $report->label = xlt('UDS Report');
        $report->url = self::MODULE_INSTALLATION_PATH . '/public/report.php';
        $report->children = [];
        $report->acl_req = ['patients', 'demo'];
        $report->global_req = [];

        return $report;
    }

    /**
     * The "Eligibility Worklist" child item that opens the data-quality
     * worklist page (issue #28).
     */
    private function eligibilityWorklistMenuItem(): stdClass
    {
        $worklist = new stdClass();
        $worklist->requirement = 0;
        $worklist->target = 'fqhc-eligibility-worklist';
        $worklist->menu_id = self::ELIGIBILITY_WORKLIST_MENU_ID;
        $worklist->label = xlt('Eligibility Worklist');
        $worklist->url = self::MODULE_INSTALLATION_PATH . '/public/eligibility-worklist.php';
        $worklist->children = [];
        $worklist->acl_req = ['patients', 'demo'];
        $worklist->global_req = [];

        return $worklist;
    }
}
