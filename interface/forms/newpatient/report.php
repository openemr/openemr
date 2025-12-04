<?php

/**
 * Encounter form report function.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\UserService;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\Globals\GlobalFeaturesEnum;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\MedicationDispense\FhirMedicationDispenseLocalDispensaryService;
use OpenEMR\Services\PatientService;

function newpatient_report($pid, $encounter, $cols, $id): void
{
    $res = sqlStatement("select e.*, f.name as facility_name from form_encounter as e join facility as f on f.id = e.facility_id where e.pid=? and e.id=?", [$pid,$id]);
    $twig = new TwigContainer(__DIR__, $GLOBALS['kernel']);
    $t = $twig->getTwig();
    $encounters = [];
    $userService = new UserService();
    while ($result = sqlFetchArray($res)) {
        $hasAccess = (empty($result['sensitivity']) || AclMain::aclCheckCore('sensitivities', $result['sensitivity']));
        $rawProvider = $userService->getUser($result["provider_id"]);
        $rawRefProvider = $userService->getUser($result["referring_provider_id"]);
        $calendar_category = (new AppointmentService())->getOneCalendarCategory($result['pc_catid']);
        $reason = (!$hasAccess) ? false : $result['reason'];
        $provider = (!$hasAccess) ? false : $rawProvider['fname'] .
            (($rawProvider['mname'] ?? '') ? " " . $rawProvider['mname'] . " " : " ") .
            $rawProvider['lname'] .
            ($rawProvider['suffix'] ? ", " . $rawProvider['suffix'] : '') .
            ($rawProvider['valedictory'] ? ", " . $rawProvider['valedictory'] : '');
        $referringProvider = (!$hasAccess || !$rawRefProvider) ? false : $rawRefProvider['fname'] . " " . $rawRefProvider['lname'];
        $posCode = (!$hasAccess) ? false : sprintf('%02d', trim($result['pos_code'] ?? false));
        $posCode = ($posCode && $posCode != '00') ? $posCode : false;
        $facility_name = (!$hasAccess) ? false : $result['facility_name'];

        $encounterRecord = [
            'category' => xl_appt_category($calendar_category[0]['pc_catname']),
            'reason' => $reason,
            'provider' => $provider,
            'referringProvider' => $referringProvider,
            'posCode' => $posCode,
            'facility' => $facility_name,
            'dispensedMedications' => []
        ];
        /**
         * @var \OpenEMR\Core\OEGlobalsBag $globalsBag
         */
        $globalsBag = $GLOBALS['globalsBag'];
        if ($globalsBag->getInt(GlobalFeaturesEnum::INHOUSE_PHARMACY->value, 0) === 1) {
            $encounterUuid = UuidRegistry::uuidToString($result['uuid']);
            $patientService = new PatientService();
            $patientUuid = UuidRegistry::uuidToString($patientService->getUuid($pid));
            $localDispensary = new FhirMedicationDispenseLocalDispensaryService();
            $medications = $localDispensary->getDispensedMedicationSummaryForEncounter($patientUuid, $encounterUuid);
            $encounterRecord['dispensedMedications'] = $medications;
        }
        $encounters[] = $encounterRecord;
    }
    // TODO: @adunsulag in future EMR version switch this to templates/newpatient/report.html.twig
    echo $t->render("templates/report.html.twig", ['encounters' => $encounters]);
}
