<?php

/**
 * Handles the display of weno selected pharmacies
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Omega Systems Group International. <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;

// This template is embedded in demographics dashboard rendering via
// RenderPharmacySectionEvent. Do not hard-exit the parent page when the
// user lacks patients/rx — Front Office and other non-clinical roles need
// demographics/appointments without prescription access.
if (!AclMain::aclCheckCore('patients', 'rx')) {
    AccessDeniedHelper::logDenial('ACL check failed for patients/rx: Pharmacy Selector');
    return;
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pharmacyService = new PharmacyService();
$prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($session->get('pid'));
$prim_pharmacy = is_array($prim_pharmacy) ? $prim_pharmacy : [];
$alt_pharmacy = $pharmacyService->getWenoAlternatePharm($session->get('pid'));
$alt_pharmacy = is_array($alt_pharmacy) ? $alt_pharmacy : [];

$primary_pharmacy = ($prim_pharmacy['business_name'] ?? false) ? ($prim_pharmacy['business_name'] . ' - ' . ($prim_pharmacy['address_line_1'] ?? '') .
    ' ' . ($prim_pharmacy['city'] ?? '') . ', ' . ($prim_pharmacy['state'] ?? '')) : '';
$alternate_pharmacy = ($alt_pharmacy['business_name'] ?? false) ? ($alt_pharmacy['business_name'] . ' - ' . ($alt_pharmacy['address_line_1'] ?? '') .
    ' ' . ($alt_pharmacy['city'] ?? '') . ', ' . ($alt_pharmacy['state'] ?? '')) : '';
?>

<div class="row col-12">
    <div>
        <label><b><?php echo xlt("Weno Primary Pharmacy"); ?>:</b></label>
        <span><?php echo text($primary_pharmacy); ?></span>
    </div>
    <div>
        <label><b><?php echo xlt("Weno Alt Pharmacy"); ?>:</b></label>
        <span><?php echo text($alternate_pharmacy); ?></span>
    </div>
</div>
