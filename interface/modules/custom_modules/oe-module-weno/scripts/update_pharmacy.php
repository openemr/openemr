<?php

require_once(dirname(__DIR__, 5) . "/interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;

if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Pharmacy Selector")]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!CsrfUtils::verifyCsrfToken($data["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$pharmacyService = new PharmacyService();
$pid = $data['pid'] ?? 0;

$sql = "SELECT primary_ncpdp, alternate_ncpdp, is_history, search_persist FROM weno_assigned_pharmacy WHERE pid = ? LIMIT 1";
$result = sqlQuery($sql, [$pid]);

if (!$result) {
    $persist = array(
        'all_day' => '',
        'weno_only' => '',
        'weno_coverage' => 'Local',
        'weno_zipcode' => '',
        'weno_city' => '',
        'weno_state' => '',
    );
    $persist = json_encode($persist);
} else {
    $persist = $result['search_persist'] ?? '';
}

$updateData = array(
    "primary_pharmacy" => $data['primary'] ?? '',
    "alternate_pharmacy" => $data['alternate'] ?? '',
    "search_persist" => $persist
);

$pharmacyService->updatePatientWenoPharmacy($data['pid'], $updateData);
// TODO: query weno_assigned_pharmacy if $result is now unique for primary_ncpdp or alternate_ncpdp and add back as history
echo text(json_encode(['status' => 'success', 'message' => 'Pharmacy selection saved successfully']));
exit;
