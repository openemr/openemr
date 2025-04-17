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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;

if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Pharmacy Selector")]);
    exit;
}

$pharmacyService = new PharmacyService();
$prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? false;
$alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? false;

$primary_pharmacy = ($prim_pharmacy['business_name'] ?? false) ? ($prim_pharmacy['business_name'] . ' - ' . ($prim_pharmacy['address_line_1'] ?? '') .
    ' ' . ($prim_pharmacy['city'] ?? '') . ', ' . ($prim_pharmacy['state'] ?? '')) : '';
$alternate_pharmacy = ($alt_pharmacy['business_name'] ?? false) ? ($alt_pharmacy['business_name'] . ' - ' . ($alt_pharmacy['address_line_1'] ?? '') .
    ' ' . ($alt_pharmacy['city'] ?? '') . ', ' . $alt_pharmacy['state'] ?? '') : '';
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
