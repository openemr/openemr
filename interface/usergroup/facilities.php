<?php

require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once $srcdir.'/view_helper.class.php';

require_once $webserver_root.'/app/models/facility.class.php';

$alertmsg = '';

if (isset($_POST["mode"]) && $_POST["mode"] == "facility") {
  $facilityId = formData('fid', 'P', true);
  if (strlen($facilityId) == 0) $facilityId = NULL;
  $facility = new Facility($facilityId);
  $facility->name = formData('facility', 'P', true);
  $facility->phone = formData('phone', 'P', true);
  $facility->fax = formData('fax', 'P', true);
  $facility->street = formData('street', 'P', true);
  $facility->city = formData('city', 'P', true);
  $facility->state = formData('state', 'P', true);
  $facility->postal_code = formData('postal_code', 'P', true);
  $facility->country_code = formData('country_code', 'P', true);
  $facility->federal_ein = formData('federal_ein', 'P', true);
  $facility->website = formData('website', 'P', true);
  $facility->email = formData('email', 'P', true);
  $facility->color = formData('ncolor', 'P', true);
  $facility->service_location = formData('service_location', 'P', true);
  $facility->billing_location = formData('billing_location', 'P', true);
  $facility->accepts_assignment = formData('accepts_assignment', 'P', true);
  $facility->pos_code = formData('pos_code', 'P', true);
  $facility->domain_identifier = formData('domain_identifier', 'P', true);
  $facility->facility_npi = formData('facility_npi', 'P', true);
  $facility->attn = formData('attn', 'P', true);
  $facility->primary_business_entity = formData('primary_business_entity', 'P', true);
  $facility->tax_id_type = formData('tax_id_type', 'P', true);
  $facility->save();
}
?>
<html>
  <head>
    <?php ViewHelper::stylesheetTag(array($css_header, '/library/js/fancybox/jquery.fancybox-1.2.6.css')); ?>

    <?php

    ViewHelper::scriptTag(array(
      '/library/dialog.js',
      '/library/js/jquery.1.3.2.js',
      '/library/js/common.js',
      '/library/js/fancybox/jquery.fancybox-1.2.6.js',
      '/library/js/jquery-ui.js'
    ));

    ?>
    <script type="text/javascript">
      $(document).ready(function(){
        // fancy box
        enable_modals();

        // special size for
      	$(".addfac_modal").fancybox( {
      		'overlayOpacity' : 0.5,
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
  </head>
  <body class="body_top">
    <div>
      <div>
        <table>
          <tr>
            <td>
              <b><?php xl('Facilities','e'); ?></b>
            </td>
            <td>
              <a href="facilities_add.php" class="iframe addfac_modal css_button"><span><?php xl('Add','e');?></span></a>
            </td>
          </tr>
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
              $facilities = Facility::getAllByName();

              if (count($facilities) > 0) {
                foreach($facilities as $facility) {
                  $varstreet="";//these are assigned conditionally below,blank assignment is done so that old values doesn't get propagated to next level.
                  $varcity="";
                  $varstate="";
          
                  if ($facility->street!="")$varstreet=$facility->street.",";
                  if ($facility->city!="")$varcity=$facility->city.",";
                  if ($facility->state!="")$varstate=$facility->state.",";
            ?>
            <tr height="22">
              <td valign="top" class="text">
                <b>
                  <a href="facility_admin.php?fid=<?php echo $facility->getId(); ?>" class="iframe medium_modal">
                    <span><?php echo htmlspecialchars($facility->name);?></span>
                  </a>
                </b>
              </td>
              <td valign="top" class="text">
                <?php echo htmlspecialchars($varstreet.$varcity.$varstate.$facility->country_code." ".$facility->postal_code); ?>
              </td>
              <td>
                <?php echo htmlspecialchars($facility->phone);?>
              </td>
            </tr>
            <?php
                }
              }

              if (count($facilities)<=0) {
            ?>
            <tr height="25">
              <td colspan="3"  style="text-align:center;font-weight:bold;">
                <?php echo xl( "Currently there are no facilities." ); ?>
              </td>
            </tr>
            <?php } ?>
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