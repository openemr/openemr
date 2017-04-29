<?php
require_once("../globals.php");
require_once("../../library/acl.inc");

$facilityService = new \services\FacilityService();

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
      "facility_code" => trim(isset($_POST["facility_id"]) ? $_POST["facility_id"] : '')
  );

  $insert_id = $facilityService->insert($newFacility);
}

/*		Editing existing facility					*/
if (isset($_POST["mode"]) && $_POST["mode"] == "facility" && $_POST["newmode"] == "admin_facility")
{
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
      "facility_code" => trim(isset($_POST["facility_id"]) ? $_POST["facility_id"] : '')
  );

  $facilityService->update($newFacility);
}

?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-3-2/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>

<script type="text/javascript">


$(document).ready(function(){

    // fancy box
    enable_modals();

    // special size for
	$(".addfac_modal").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 460,
		'frameWidth' : 650
	});

    // special size for
	$(".medium_modal").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 460,
		'frameWidth' : 650
	});

});

</script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">

<div>
    <div>
	<table><tr><td>
        <b><?php xl('Facilities','e'); ?></b>&nbsp;</td><td>
		 <a href="facilities_add.php" class="iframe addfac_modal css_button"><span><?php xl('Add','e');?></span></a>
		 </td></tr>
	</table>
    </div>
    <div class="tabContainer" style="width:550px;">
        <div>
<table cellpadding="1" cellspacing="0" class="showborder">
	<tr class="showborder_head" height="22">
		<th style="border-style:1px solid #000" width="140px"><?php xl('Name','e'); ?></th>
		<th style="border-style:1px solid #000" width="320px"><?php xl('Address','e'); ?></th>
		<th style="border-style:1px solid #000"><?php xl('Phone','e'); ?></th>
    </tr>
     <?php
        $fres = 0;
        $fres = $facilityService->getAll();
        if ($fres) {
          $result2 = array();
          for ($iter3 = 0; $iter3 < sizeof($fres); $iter3++)
            $result2[$iter3] = $fres[$iter3];
          foreach($result2 as $iter3) {
			$varstreet="";//these are assigned conditionally below,blank assignment is done so that old values doesn't get propagated to next level.
			$varcity="";
			$varstate="";
          $varstreet=$iter3["street"];
          if ($iter3["street"]!="")$varstreet=$iter3["street"].",";
          if ($iter3["city"]!="")$varcity=$iter3["city"].",";
          if ($iter3["state"]!="")$varstate=$iter3["state"].",";
    ?>
    <tr height="22">
       <td valign="top" class="text"><b><a href="facility_admin.php?fid=<?php echo $iter3["id"];?>" class="iframe medium_modal"><span><?php echo htmlspecialchars($iter3["name"]);?></span></a></b>&nbsp;</td>
       <td valign="top" class="text"><?php echo htmlspecialchars($varstreet.$varcity.$varstate.$iter3["country_code"]." ".$iter3["postal_code"]); ?>&nbsp;</td>
       <td><?php echo htmlspecialchars($iter3["phone"]);?>&nbsp;</td>
    </tr>
<?php
  }
}
 if (count($result2)<=0)
  {?>
  <tr height="25">
		<td colspan="3"  style="text-align:center;font-weight:bold;"> <?php echo xl( "Currently there are no facilities." ); ?></td>
	</tr>
  <?php }
?>
	</table>
        </div>
    </div>
</div>
<script language="JavaScript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
</script>

</body>
</html>
