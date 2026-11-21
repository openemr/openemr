<?php

/**
 * Printable medication list for one patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\PatientDemographics\ViewEvent;

final class ActiveMedicationPrintPage
{
    /**
     * Write the print page for the requested patient and stop.
     */
    public static function emit(): never
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $pid = ActiveMedicationListService::printPatientId(
            CurrentRequest::get()->query->get('pid'),
            $session->get('pid', 0)
        );
        if ($pid < 1) {
            AccessDeniedHelper::deny('No patient selected');
        }

        $viewEvent = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch(
            new ViewEvent($pid),
            ViewEvent::EVENT_HANDLE
        );
        if ($viewEvent->authorized() !== true) {
            AccessDeniedHelper::deny('Unauthorized access to medication list');
        }
        if (!AclMain::aclCheckCore('patients', 'med')) {
            AccessDeniedHelper::deny('Unauthorized access to medication list');
        }
        self::denyUnlessSquadAllowed($pid);

        $patient = self::patientRow($pid);
        $includePrescriptions = !OEGlobalsBag::getInstance()->getBoolean('disable_prescriptions')
            && AclMain::aclCheckCore('patients', 'rx');
        $service = new ActiveMedicationListService();
        $active = $service->getActiveList($pid, $includePrescriptions);
        $inactive = $service->getInactiveList($pid, $active, $includePrescriptions);
        $formatMedDate = static fn (?string $d): string => ActiveMedicationListService::formatListDate(
            $d,
            static fn (string $raw) => oeFormatShortDate($raw)
        );
        $section = new ActiveMedicationSectionView(ServiceContainer::getTwig());
        ob_start();
        Header::setupHeader(['common']);
        $header = ob_get_clean();
        ob_start();
        require OEGlobalsBag::getInstance()->getSrcDir() . '/restoreSession.php';
        $printLog = ob_get_clean();
        echo ServiceContainer::getTwig()->render(
            'patient/active_medications_print.html.twig',
            [
                'header' => is_string($header) ? $header : '',
                'printLog' => is_string($printLog) ? $printLog : '',
                'patientName' => ActiveMedicationListService::patientName($patient),
                'activeHtml' => $section->render(
                    'Active',
                    $active,
                    xl(...),
                    $formatMedDate,
                    'table table-bordered table-sm'
                ),
                'inactiveHtml' => $section->render(
                    'Inactive',
                    $inactive,
                    xl(...),
                    $formatMedDate,
                    'table table-bordered table-sm'
                ),
            ]
        );
        exit;
    }

    /**
     * Stop when this patient belongs to a squad the user cannot open.
     *
     * The medications section of the patient report uses the same check.
     * It runs before the page reads the chart or the medication lists.
     */
    private static function denyUnlessSquadAllowed(int $pid): void
    {
        $loaded = getPatientData($pid, 'squad');
        $squad = '';
        if (is_array($loaded)) {
            $value = $loaded['squad'] ?? null;
            if (is_string($value)) {
                $squad = $value;
            }
        }
        if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
            AccessDeniedHelper::deny('Not authorized for squad: ' . $squad);
        }
    }

    /**
     * Name columns for the print page. A missing row is empty.
     *
     * @return array<string, mixed>
     */
    private static function patientRow(int $pid): array
    {
        $patient = [];
        foreach (getPatientData($pid, 'title, fname, mname, lname') as $column => $value) {
            if (!is_string($column)) {
                continue;
            }
            $patient[$column] = $value;
        }

        return $patient;
    }
}
