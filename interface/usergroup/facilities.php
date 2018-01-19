<?php
/**
 * Facilities.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak01@hotmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/acl.inc");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$alertmsg = '';

/*		Inserting New facility					*/
if (isset($_POST["mode"]) && $_POST["mode"] == "facility" && $_POST["newmode"] != "admin_facility") {
    $newFacility = array(
      "name" => trim(isset($_POST["facility"]) ? $_POST["facility"] : ''),
      "phone" => trim(isset($_POST["phone"]) ? $_POST["phone"] : ''),
      "fax" => trim(isset($_POST["fax"]) ? $_POST["fax"] : ''),
      "street" => trim(isset($_POST["street"]) ? $_POST["street"] : ''),
      "city" => trim(isset($_POST["city"]) ? $_POST["city"] : ''),
      "state" => trim(isset($_POST["state"]) ? $_POST["state"] : ''),
      "postal_code" => trim(isset($_POST["postal_code"]) ? $_POST["postal_code"] : ''),
      "country_code" => trim(isset($_POST["country_code"]) ? $_POST["country_code"] : ''),
      "federal_ein" => trim(isset($_POST["federal_ein"]) ? $_POST["federal_ein"] : ''),
      "website" => trim(isset($_POST["website"]) ? $_POST["website"] : ''),
      "email" => trim(isset($_POST["email"]) ? $_POST["email"] : ''),
      "color" => trim(isset($_POST["ncolor"]) ? $_POST["ncolor"] : ''),
      "service_location" => trim(isset($_POST["service_location"]) ? $_POST["service_location"] : ''),
      "billing_location" => trim(isset($_POST["billing_location"]) ? $_POST["billing_location"] : ''),
      "accepts_assignment" => trim(isset($_POST["accepts_assignment"]) ? $_POST["accepts_assignment"] : ''),
      "pos_code" => trim(isset($_POST["pos_code"]) ? $_POST["pos_code"] : ''),
      "domain_identifier" => trim(isset($_POST["domain_identifier"]) ? $_POST["domain_identifier"] : ''),
      "attn" => trim(isset($_POST["attn"]) ? $_POST["attn"] : ''),
      "tax_id_type" =>  trim(isset($_POST["tax_id_type"]) ? $_POST["tax_id_type"] : ''),
      "primary_business_entity" => trim(isset($_POST["primary_business_entity"]) ? $_POST["primary_business_entity"] : ''),
      "facility_npi" => trim(isset($_POST["facility_npi"]) ? $_POST["facility_npi"] : ''),
      "facility_taxonomy" => trim(isset($_POST["facility_taxonomy"]) ? $_POST["facility_taxonomy"] : ''),
      "facility_code" => trim(isset($_POST["facility_id"]) ? $_POST["facility_id"] : '')
    );

    $insert_id = $facilityService->insert($newFacility);
    exit(); // sjp 12/20/17 for ajax save
}

/*		Editing existing facility					*/
if (isset($_POST["mode"]) && $_POST["mode"] == "facility" && $_POST["newmode"] == "admin_facility") {
    $newFacility = array(
      "fid" => trim(isset($_POST["fid"]) ? $_POST["fid"] : ''),
      "name" => trim(isset($_POST["facility"]) ? $_POST["facility"] : ''),
      "phone" => trim(isset($_POST["phone"]) ? $_POST["phone"] : ''),
      "fax" => trim(isset($_POST["fax"]) ? $_POST["fax"] : ''),
      "street" => trim(isset($_POST["street"]) ? $_POST["street"] : ''),
      "city" => trim(isset($_POST["city"]) ? $_POST["city"] : ''),
      "state" => trim(isset($_POST["state"]) ? $_POST["state"] : ''),
      "postal_code" => trim(isset($_POST["postal_code"]) ? $_POST["postal_code"] : ''),
      "country_code" => trim(isset($_POST["country_code"]) ? $_POST["country_code"] : ''),
      "federal_ein" => trim(isset($_POST["federal_ein"]) ? $_POST["federal_ein"] : ''),
      "website" => trim(isset($_POST["website"]) ? $_POST["website"] : ''),
      "email" => trim(isset($_POST["email"]) ? $_POST["email"] : ''),
      "color" => trim(isset($_POST["ncolor"]) ? $_POST["ncolor"] : ''),
      "service_location" => trim(isset($_POST["service_location"]) ? $_POST["service_location"] : ''),
      "billing_location" => trim(isset($_POST["billing_location"]) ? $_POST["billing_location"] : ''),
      "accepts_assignment" => trim(isset($_POST["accepts_assignment"]) ? $_POST["accepts_assignment"] : ''),
      "pos_code" => trim(isset($_POST["pos_code"]) ? $_POST["pos_code"] : ''),
      "domain_identifier" => trim(isset($_POST["domain_identifier"]) ? $_POST["domain_identifier"] : ''),
      "attn" => trim(isset($_POST["attn"]) ? $_POST["attn"] : ''),
      "tax_id_type" =>  trim(isset($_POST["tax_id_type"]) ? $_POST["tax_id_type"] : ''),
      "primary_business_entity" => trim(isset($_POST["primary_business_entity"]) ? $_POST["primary_business_entity"] : ''),
      "facility_npi" => trim(isset($_POST["facility_npi"]) ? $_POST["facility_npi"] : ''),
      "facility_taxonomy" => trim(isset($_POST["facility_taxonomy"]) ? $_POST["facility_taxonomy"] : ''),
      "facility_code" => trim(isset($_POST["facility_id"]) ? $_POST["facility_id"] : '')
    );

    $facilityService->update($newFacility);

    // Update facility name for all users with this facility.
    // This is necassary because some provider based code uses facility name for lookups instead of facility id.
    //
    $facilityService->updateUsersFacility($newFacility['name'], $newFacility['fid']);
    exit(); // sjp 12/20/17 for ajax save
}

?>
<!DOCTYPE html >
<html>
<head>

<title><?php echo xlt("Facilities") ; ?></title>

    <?php Header::setupHeader(['common', 'jquery-ui']); ?>

<script type="text/javascript">

function refreshme() {
    top.restoreSession();
    document.location.reload();
}
$(document).ready(function(){

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 700, 590, '', '', {
            allowResize: false,
            allowDrag: true, // note these default to true if not defined here. left as example.
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".addfac_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 700, 620, '', '', {
            allowResize: false,
            allowDrag: true,
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});

</script>
</head>

<body class="body_top">

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
            <div class="page-header clearfix">
                <h2 class="clearfix"><?php echo xlt("Facilities") ; ?></h2>
            </div>
            <a href="facilities_add.php" class="addfac_modal btn btn-default btn-add"><span><?php echo xlt('Add Facility');?></span></a>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-12">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo xlt('Name'); ?></th>
                                <th><?php echo xlt('Address'); ?></th>
                                <th><?php echo xlt('Phone'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fres = 0;
                            $fres = $facilityService->getAll();
                            if ($fres) {
                                $result2 = array();
                                for ($iter3 = 0; $iter3 < sizeof($fres); $iter3++) {
                                    $result2[$iter3] = $fres[$iter3];
                                }

                                foreach ($result2 as $iter3) {
                                    $varstreet="";//these are assigned conditionally below,blank assignment is done so that old values doesn't get propagated to next level.
                                    $varcity="";
                                    $varstate="";
                                    $varstreet=$iter3["street"];
                                    if ($iter3["street"]!="") {
                                        $varstreet=$iter3["street"].",";
                                    }

                                    if ($iter3["city"]!="") {
                                        $varcity=$iter3["city"].",";
                                    }

                                    if ($iter3["state"]!="") {
                                        $varstate=$iter3["state"].",";
                                    }
                            ?>
                            <tr height="22">
                                 <td valign="top" class="text"><b><a href="facility_admin.php?fid=<?php echo attr($iter3["id"]); ?>" class="medium_modal"><span><?php echo text($iter3["name"]);?></span></a></b>&nbsp;</td>
                                 <td valign="top" class="text"><?php echo text($varstreet.$varcity.$varstate.$iter3["country_code"]." ".$iter3["postal_code"]); ?>&nbsp;</td>
                                 <td><?php echo text($iter3["phone"]);?>&nbsp;</td>
                            </tr>
                            <?php
                                }
                            }

                            if (count($result2)<=0) {?>
                            <tr height="25">
                                <td colspan="3"  style="text-align:center;font-weight:bold;"> <?php echo xlt("Currently there are no facilities."); ?></td>
                            </tr>
                            <?php
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- end of div container -->
    <script language="JavaScript">
    <?php
    if ($alertmsg = trim($alertmsg)) {
        echo "alert('$alertmsg');\n";
    }
    ?>
    </script>

</body>
</html>
