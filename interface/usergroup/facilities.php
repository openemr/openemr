<?php

/**
 * Facilities.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ranganath Pathak <pathak01@hotmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Facilities")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

$alertmsg = '';

$columns = [
    "name" => "facility",
    "phone" => true,
    "fax" => true,
    "street" => true,
    "city" => true,
    "state" => true,
    "postal_code" => true,
    "country_code" => true,
    "federal_ein" => true,
    "website" => true,
    "email" => true,
    "color" => "ncolor",
    "service_location" => true,
    "billing_location" => true,
    "accepts_assignment" => true,
    "pos_code" => true,
    "domain_identifier" => true,
    "attn" => true,
    "tax_id_type" => true,
    "primary_business_entity" => true,
    "facility_npi" => true,
    "facility_taxonomy" => true,
    "facility_code" => "facility_id",
    "mail_street" => true,
    "mail_street2" => true,
    "mail_city" => true,
    "mail_state" => true,
    "mail_zip" => true,
    "oid" => true,
    "iban" => true,
    "info" => true,
    "inactive" => true
];

$values = [];
// Populate the $values array with a associative array of column names => $_POST value
foreach ($columns as $c => $v) {
    // Ensure form fields that have a different name from the column gets corrected
    $k = ($columns[$c] !== true) ? $v : $c;
    $values[$c] = trim($_POST[$k] ?? '');
}

/*      Inserting New facility                  */
if (($_POST["mode"] ?? "") == "facility" && (empty($_POST["newmode"]) || ($_POST["newmode"] != "admin_facility"))) {
    $insert_id = $facilityService->insertFacility($values);
    exit(); // sjp 12/20/17 for ajax save
}

/*      Editing existing facility                   */
if (($_POST["mode"] ?? "") == "facility" && $_POST["newmode"] == "admin_facility") {
    // Since it's an edit, add in the facility ID
    $values["id"] = trim($_POST['fid'] ?? '');
    $facilityService->updateFacility($values);

    // Update facility name for all users with this facility.
    // This is necessary because some provider based code uses facility name for lookups instead of facility id.
    //
    $facilityService->updateUsersFacility($values['name'], $values['id']);
    exit(); // sjp 12/20/17 for ajax save
}

?>
<!DOCTYPE html >
<html>
<head>
<title><?php echo xlt("Facilities") ; ?></title>
<?php Header::setupHeader(['common']); ?>
<script>
function refreshme() {
    top.restoreSession();
    document.location.reload();
}
$(function () {
    $(".medium_modal").on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dlgopen('', '', 1020, 900, '', '', {
            allowResize: true,
            allowDrag: true, // note these default to true if not defined here. left as example.
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".addfac_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 1020, 620, '', '', {
            allowResize: false,
            allowDrag: true,
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $("#form_inactive").on('click', function(e) {
        $(".inactive").toggleClass('d-none');
        $("#form_inactive i").toggleClass("fa-square-check");
        $("#form_inactive i").toggleClass("fa-square");
    });

});
</script>
</head>

<body class="">
<div class="container">
    <div class="row">
        <div class="col-12 my-2 justify-content-between d-flex align-items-center">
            <a href="facilities_add.php" class="addfac_modal btn btn-text btn-add"><?php echo xlt('Add Facility');?></a>
            <div class="form-check d-flex align-items-center">
                <input class="form-check-input" type="checkbox" value="" id="form_inactive">
                <label class="form-check-label" for="form_inactive">
                    <?php echo xlt('Include Inactive Facilities'); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo xlt('Name'); ?></th>
                            <th scope="col"><?php echo xlt('Tax ID'); ?></th>
                            <th scope="col"><?php echo xlt('NPI'); ?></th>
                            <th scope="col"><?php echo xlt('Billing Address'); ?></th>
                            <th scope="col"><?php echo xlt('Mailing Address'); ?></th>
                            <th scope="col"><?php echo xlt('Phone'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fres = 0;
                        $fres = $facilityService->getAllFacility();
                        if ($fres) {
                            $result2 = array();
                            for ($iter3 = 0; $iter3 < sizeof($fres); $iter3++) {
                                $result2[$iter3] = $fres[$iter3];
                            }

                            foreach ($result2 as $iter3) {
                                $varstreet = "";//these are assigned conditionally below,blank assignment is done so that old values doesn't get propagated to next level.
                                $varcity = "";
                                $varstate = "";
                                $varmstreet = "";
                                $varmcity = "";
                                $varmstate = "";
                                $varstreet = $iter3["street"];
                                if ($iter3["street"] != "") {
                                    $varstreet = $iter3["street"] . " ";
                                }

                                if ($iter3["city"] != "") {
                                    $varcity = $iter3["city"] . ", ";
                                }

                                if ($iter3["state"] != "") {
                                    $varstate = $iter3["state"] . " ";
                                }

                                $varmstreet = $iter3["mail_street"];
                                if ($iter3["mail_street"] != "") {
                                    $varmstreet = $iter3["mail_street"] . " ";
                                }

                                if ($iter3["mail_city"] != "") {
                                    $varmcity = $iter3["mail_city"] . ", ";
                                }

                                if ($iter3["mail_state"] != "") {
                                    $varmstate = $iter3["mail_state"] . " ";
                                }
                                ?>
                            <tr class="<?php echo ($iter3['inactive']) ? 'inactive text-muted d-none' : '';?>">
                                <td valign="top" class="text">
                                    <a class="font-weight-bold medium_modal" href="facility_admin.php?fid=<?php echo attr_url($iter3["id"]); ?>"><?php echo xlt($iter3["name"]);?></a>
                                    <?php echo ($iter3['inactive']) ? "<br>(" . xlt("Inactive") . ")" : ''; ?>
                                </td>
                                <td valign="top" class="text"><?php echo text($iter3["federal_ein"]); ?>&nbsp;</td>
                                <td valign="top" class="text"><?php echo text($iter3["facility_npi"]); ?>&nbsp;</td>
                                <td valign="top" class="text"><?php echo text($varstreet) . "<br>" . text($varcity) . text($varstate) . text($iter3["country_code"]) . " " . text($iter3["postal_code"]); ?></td>
                                <td valign="top" class="text"><?php echo text($varmstreet) . "<br>" . text($varmcity) . text($varmstate) . " " . text($iter3['mail_zip']); ?></td>
                                <td><?php echo text($iter3["phone"]);?>&nbsp;</td>
                            </tr>
                                <?php
                            }
                        }

                        if (count($result2) <= 0) {?>
                        <tr height="25">
                            <td colspan="3" class="text-center font-weight-bold"> <?php echo xlt("Currently there are no facilities."); ?></td>
                        </tr>
                            <?php
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- end of div container -->
<script>
<?php
if ($alertmsg = trim($alertmsg)) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}
?>
</script>
</body>
</html>
