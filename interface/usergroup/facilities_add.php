<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/POSRef.class.php");
require_once("$srcdir/options.inc.php");

require_once $srcdir.'/view_helper.class.php';

require_once $APP_ROOT.'/models/facility.class.php';

$facility = new Facility();

$alertmsg = '';
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

    include "$srcdir/erx_javascript.inc.php";
    ?>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>
    <?php
    // Old Browser comp trigger on js
    if (isset($_POST["mode"]) && $_POST["mode"] == "facility") { ?>
    <script type="text/javascript">
    <!--
      parent.$.fn.fancybox.close();
    //-->
    </script>
    <?php } ?>
    <script type="text/javascript">
    /// todo, move this to a common library

      function submitform() {
      <?php if($GLOBALS['erx_enable']) { ?>
        var alertMsg='',
            f=document.forms[0];

        for(i=0;i<f.length;i++){
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
          if(document.forms[0].facility.value.length<=0) {
            document.forms[0].facility.style.backgroundColor="red";
            document.forms[0].facility.focus();
          } else if(document.forms[0].ncolor.value == '') {
            document.forms[0].ncolor.style.backgroundColor="red";
            document.forms[0].ncolor.focus();	
          }
        }
      }

      function toggle( target, div ) {
        var $mode = $(target).find(".indicator").text();
        
        if ( $mode == "collapse" ) {
          $(target).find(".indicator").text( "expand" );
          $(div).hide();
        } else {
          $(target).find(".indicator").text( "collapse" );
          $(div).show();
        }
      }

      $(document).ready(function(){
        $("#dem_view").click( function() {
            toggle( $(this), "#DEM" );
        });

        // fancy box
        enable_modals();

        tabbify();

        // special size for
        $(".large_modal").fancybox( {
          'overlayOpacity' : 0.0,
          'showCloseButton' : true,
          'frameHeight' : 600,
          'frameWidth' : 1000
        });

        // special size for
        $(".medium_modal").fancybox( {
          'overlayOpacity' : 0.0,
          'showCloseButton' : true,
          'frameHeight' : 260,
          'frameWidth' : 510
        });

        $("#cancel").click(function() {
          parent.$.fn.fancybox.close();
        });
      });

      var cp = new ColorPicker('window'),
          field = null;
      // Runs when a color is clicked
      function pickColor(color) {
     	  document.getElementById('ncolor').value = color;
      }

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
  </head>
  <body class="body_top">
    <table>
      <tr>
        <td>
          <span class="title"><?php xl('Add Facility','e'); ?></span>
        </td>
        <td colspan=5 align=center style="padding-left:2px;">
          <a onclick="submitform();" class="css_button large_button" name='form_save' id='form_save' href='#'>
            <span class='css_button_span large_button_span'>
              <?php xl('Save','e');?>
            </span>
          </a>
          <a class="css_button large_button" id='cancel' href='#' >
            <span class='css_button_span large_button_span'>
              <?php xl('Cancel','e');?>
            </span>
          </a>
        </td>
      </tr>
    </table>

    <br>

    <?php include $VIEW_ROOT.'/facilities/form.view.php'; ?>

    <script language="JavaScript">
    <?php
      if ($alertmsg = trim($alertmsg)) {
        echo "alert('$alertmsg');\n";
      }
    ?>
    </script>
  </body>
</html>