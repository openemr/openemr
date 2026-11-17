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
        if (!AclMain::aclCheckCore('patients', 'med')) {
            AccessDeniedHelper::deny('Unauthorized access to medication list');
        }

        $patient = self::patientRow($pid);
        $squadValue = $patient['squad'] ?? null;
        $squad = is_string($squadValue) ? $squadValue : '';
        if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
            AccessDeniedHelper::deny('Not authorized for squad: ' . $squad);
        }

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
        Header::setupHeader();
        $header = ob_get_clean();
        echo ServiceContainer::getTwig()->render(
            'patient/active_medications_print.html.twig',
            [
                'header' => is_string($header) ? $header : '',
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
     * Name and squad columns for the print page. A missing row is empty.
     *
     * @return array<string, mixed>
     */
    private static function patientRow(int $pid): array
    {
        $patient = [];
        foreach (getPatientData($pid, 'squad, title, fname, mname, lname') as $column => $value) {
            if (!is_string($column)) {
                continue;
            }
            $patient[$column] = $value;
        }

        return $patient;
    }
}
