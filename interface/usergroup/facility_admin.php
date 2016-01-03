<?php
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
require_once("$srcdir/classes/POSRef.class.php");
require_once("$srcdir/options.inc.php");

require_once $srcdir.'/view_helper.class.php';

require_once $APP_ROOT.'/models/facility.class.php';

if (isset($_GET["fid"])) {
	$facilityId = $_GET["fid"];
} elseif (isset($_POST["fid"])) {
	$facilityId = $_POST["fid"];
}

$facility = new Facility($facilityId);

?>
<html>
  <head>
    <?php ViewHelper::stylesheetTag(array($css_header)); ?>

    <?php

    ViewHelper::scriptTag(array(
      '/library/dialog.js',
      '/library/js/jquery.1.3.2.js',
      '/library/js/common.js',
      '/library/js/fancybox/jquery.fancybox-1.2.6.js'
    ));

    ?>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>
    <?php require_once("$srcdir/erx_javascript.inc.php"); ?>
    <script type="text/javascript">
      function submitform() {
      <?php if($GLOBALS['erx_enable']) { ?>
        var alertMsg='',
            f=document.forms[0];

        for(i=0;i<f.length;i++) {
          if(f[i].type=='text' && f[i].value) {
            if(f[i].name == 'facility' || f[i].name == 'Washington') {
              alertMsg += checkLength(f[i].name,f[i].value,35);
              alertMsg += checkFacilityName(f[i].name,f[i].value);
            } else if(f[i].name == 'street') {
              alertMsg += checkLength(f[i].name,f[i].value,35);
              alertMsg += checkAlphaNumeric(f[i].name,f[i].value);
            } else if(f[i].name == 'phone' || f[i].name == 'fax') {
              alertMsg += checkPhone(f[i].name,f[i].value);
            } else if(f[i].name == 'federal_ein') {
              alertMsg += checkLength(f[i].name,f[i].value,10);
              alertMsg += checkFederalEin(f[i].name,f[i].value);
            }
          }
        }

        if(alertMsg) {
		      alert(alertMsg);
          return false;
        }
      <?php } ?>
        if (document.forms[0].facility.value.length>0 && document.forms[0].ncolor.value != '') {
          top.restoreSession();
          document.forms[0].submit();
        } else {
          if(document.forms[0].facility.value.length<=0){
            document.forms[0].facility.style.backgroundColor="red";
            document.forms[0].facility.focus();
          } else if(document.forms[0].ncolor.value == '') {
            document.forms[0].ncolor.style.backgroundColor="red";
            document.forms[0].ncolor.focus();	
          }
        }
      }

      $(document).ready(function(){
        $("#cancel").click(function() {
          parent.$.fn.fancybox.close();
        });
      });

      var cp = new ColorPicker('window');
      // Runs when a color is clicked
      function pickColor(color) {
        document.getElementById('ncolor').value = color;
      }

      var field;
      function pick(anchorname,target) {
        var cp = new ColorPicker('window');
        field=target;
        cp.show(anchorname);
      }

      function displayAlert() {
        if(document.getElementById('primary_business_entity').checked==false) {
          alert("<?php echo addslashes(xl('Primary Business Entity tax id is used as account id for NewCrop ePrescription. Changing the facility will affect the working in NewCrop.'));?>");
        } else if(document.getElementById('primary_business_entity').checked==true) {
          alert("<?php echo addslashes(xl('Once the Primary Business Facility is set, it should not be changed. Changing the facility will affect the working in NewCrop ePrescription.'));?>");
        }
      }
    </script>

    <?php if ($_POST["mode"] == "facility") { ?>
    <script type="text/javascript">
    <!--
    parent.$.fn.fancybox.close();
    //-->
    </script>
    <?php } ?>
  </head>
  <body class="body_top" style="width:600px;height:330px !important;">
    <table>
      <tr>
        <td>
          <span class="title"><?php xl('Edit Facility','e'); ?></span>
        </td>
        <td>
          <a class="css_button large_button" name='form_save' id='form_save' onclick='submitform()' href='#' >
            <span class='css_button_span large_button_span'><?php xl('Save','e');?></span>
          </a>
          <a class="css_button large_button" id='cancel' href='#'>
            <span class='css_button_span large_button_span'><?php xl('Cancel','e');?></span>
          </a>
        </td>
      </tr>
    </table>
    <?php include $VIEW_ROOT.'/facilities/form.view.php'; ?>
  </body>
</html>