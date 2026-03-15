<?php

/**
 * Edit insurance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patientvalidation.inc.php");
require_once("$srcdir/pid.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\PatientDemographics\UpdateEvent;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// make sure permissions are checked before we allow this page to be accessed.
if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
    AccessDeniedHelper::deny('Updating demographics is not authorized');
}

// Session pid must be right or bad things can happen when demographics are saved!
//
$set_pid = $_GET["set_pid"] ?? ($_GET["pid"] ?? null);
if ($set_pid && $set_pid != $session->get("pid")) {
    setpid($set_pid);
}

$result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

 // Check authorization.
if ($pid) {
    // Create and fire the patient demographics update event
    $updateEvent = new UpdateEvent($pid);
    $updateEvent = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch($updateEvent, UpdateEvent::EVENT_HANDLE);

    if (
        !$updateEvent->authorized() ||
        !AclMain::aclCheckCore('patients', 'demo', '', 'write')
    ) {
        AccessDeniedHelper::deny('Updating insurance is not authorized');
    }

    if ($result['squad'] && ! AclMain::aclCheckCore('squads', $result['squad'])) {
        AccessDeniedHelper::deny('Unauthorized access to patient squad');
    }
} else {
    if (!AclMain::aclCheckCore('patients', 'demo', '', ['write','addonly'])) {
        AccessDeniedHelper::deny('Adding insurance is not authorized');
    }
}
// $statii = array('married','single','divorced','widowed','separated','domestic partner');
// $provideri = getProviderInfo();
$insurancei = OEGlobalsBag::getInstance()->get('insurance_information') != '0' ? getInsuranceProvidersExtra() : getInsuranceProviders();
//Check to see if only one insurance is allowed
$insurance_array = OEGlobalsBag::getInstance()->getBoolean('insurance_only_one') ? ['primary'] : ['primary', 'secondary', 'tertiary'];

//Check to see if only one insurance is allowed
if (OEGlobalsBag::getInstance()->getBoolean('insurance_only_one')) {
    $insurance_headings = [xl("Primary Insurance Provider")];
} else {
    $insurance_headings = [xl("Primary Insurance Provider"), xl("Secondary Insurance Provider"), xl("Tertiary Insurance provider")];
}

$twig = (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig();
//$insurance_info[0]['active'] = true;
//$insuranceTypes = array_map(function($item) { return $item['type'];}, $insurance_info);
//$insrender(uranceTypes = array_unique($insuranceTypes);
// we DO NOT want to allow users to add states/localities in this form per Business Rules
// so if we have a state add button we are removing it here.
$state_data_type = OEGlobalsBag::getInstance()->get('state_data_type') === '26' ? '1' : OEGlobalsBag::getInstance()->get('state_data_type');
$country_data_type = OEGlobalsBag::getInstance()->get('country_data_type') === '26' ? '1' : OEGlobalsBag::getInstance()->get('country_data_type');
echo $twig->render(
    "patient/insurance/insurance_edit.html.twig",
    [
        'insuranceTypes' => $insurance_array,
        'activeType' => $insurance_array[0],
        'patient' => $result,
        'puuid' => UuidRegistry::uuidToString($result['uuid'])
        ,'insuranceProviderList' => $insurancei
        ,'enableSwapSecondaryInsurance' => OEGlobalsBag::getInstance()->getBoolean('enable_swap_secondary_insurance')
        ,'include_employers' => !OEGlobalsBag::getInstance()->getBoolean('omit_employers') === true
        ,'useStateTerminology' => OEGlobalsBag::getInstance()->getInt('phone_country_code') === 1
        ,'state_list' => OEGlobalsBag::getInstance()->get('state_list')
        ,'state_data_type' => $state_data_type
        ,'country_data_type' => $country_data_type
        ,'country_list' => OEGlobalsBag::getInstance()->get('country_list')
        // policy_types is defined in patient.inc.php
        ,'policy_types' => OEGlobalsBag::getInstance()->get('policy_types')
        ,'uspsVerifyAddress' => OEGlobalsBag::getInstance()->get('usps_apiv3_client_id')
        ,'languageDirection' => OEGlobalsBag::getInstance()->get('language_direction') ?? ''
        ,'rightJustifyLabels' => OEGlobalsBag::getInstance()->getBoolean('right_justify_labels_demographics')
    ]
);
exit;
