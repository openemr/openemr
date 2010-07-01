<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/POSRef.class.php");

$alertmsg = '';
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>
<?php
// Old Browser comp trigger on js

if (isset($_POST["mode"]) && $_POST["mode"] == "facility") {
  	echo '
<script type="text/javascript">
<!--
parent.$.fn.fancybox.close();
//-->
</script>

	';
}
?>
<script type="text/javascript">
/// todo, move this to a common library

function submitform() {
    if (document.forms[0].facility.value.length>0) {
        top.restoreSession();
        document.forms[0].submit();
		parent.$.fn.fancybox.close();
		parent.location.reload();
    } else {
        document.forms[0].facility.style.backgroundColor="red";
        document.forms[0].facility.focus();
    }
}

function toggle( target, div ) {

    $mode = $(target).find(".indicator").text();
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

    // load divs
    $("#stats_div").load("stats.php");
    $("#notes_div").load("pnotes_fragment.php");

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

});

$(document).ready(function(){
    $("#cancel").click(function() {
		  parent.$.fn.fancybox.close();
	 });
});

</script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">
<table>
<tr><td>
    <span class="title"><?php xl('Add Facility','e'); ?></span>&nbsp;&nbsp;&nbsp;</td>
    <td colspan=5 align=center style="padding-left:2px;">
        <a onclick="submitform();" class="css_button large_button" name='form_save' id='form_save' href='#'>
            <span class='css_button_span large_button_span'><?php xl('Save','e');?></span>
        </a>
        <a class="css_button large_button" id='cancel' href='#' >
            <span class='css_button_span large_button_span'><?php xl('Cancel','e');?></span>
        </a>
</td></tr>
</table>

<br>

<form name='facility' method='post' action="facilities.php" onsubmit='return top.restoreSession()'>
    <input type=hidden name=mode value="facility">
    <table border=0 cellpadding=0 cellspacing=0>
        <tr>
        <td><span class="text"><?php xl('Name','e'); ?>: </span></td><td><input type=entry name=facility size=20 value=""><span class="mandatory">&nbsp;*</span></td>
        <td width=20>&nbsp;</td>
        <td><span class="text"><?php xl('Phone','e'); ?>: </span></td><td><input type=entry name=phone size=20 value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('Address','e'); ?>: </span></td><td><input type=entry size=20 name=street value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Fax','e'); ?>: </span></td><td><input type=entry name=fax size=20 value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('City','e'); ?>: </span></td><td><input type=entry size=20 name=city value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Zip Code','e'); ?>: </span></td><td><input type=entry size=20 name=postal_code value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('State','e'); ?>: </span></td><td><input type=entry size=20 name=state value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Federal EIN','e'); ?>: </span></td><td><input type=entry size=20 name=federal_ein value=""></td>
        </tr>
        <tr>
        <td height="22"><span class="text"><?php xl('Country','e'); ?>: </span></td><td><input type=entry size=20 name=country_code value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code','e') : xl('Facility NPI','e')); ?>:
        </span></td><td><input type=entry size=20 name=facility_npi value=""></td>
        </tr>

        <tr>
          <td><span class='text'><?php xl('Billing Location','e'); ?>: </span></td><td><input type='checkbox' name='billing_location' value = '1'></td>
          <td>&nbsp;</td>
          <td><span class='text'><?php xl('Accepts Assignment','e'); ?><br>(<?php xl('only if billing location','e'); ?>): </span></td> <td><input type='checkbox' name='accepts_assignment' value = '1'></td>
        </tr>
        <tr>
          <td><span class='text'><?php xl('Service Location','e'); ?>: </span></td> <td><input type='checkbox' name='service_location' value = '1'></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td> <td>&nbsp;</td>
        </tr>

        <tr>
            <td><span class=text><?php xl('POS Code','e'); ?>: </span></td>
            <td colspan="6">
                <select name="pos_code">
                <?php
                $pc = new POSRef();

                foreach ($pc->get_pos_ref() as $pos) {
                    echo "<option value=\"" . $pos["code"] . "\" ";
                    echo ">" . $pos['code']  . ": ". $pos['title'];
                    echo "</option>\n";
                }

                ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('Billing Attn','e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="attn" size="45"></td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('CLIA Number','e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="domain_identifier" size="45"></td>
        </tr>

        <tr height="25" style="valign:bottom;">
        <td><font class="mandatory">*</font><span class="text"> <?php echo xl('Required','e'); ?></span></td><td>&nbsp;</td><td>&nbsp;</td>
        <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
    </table>
</form>

<script language="JavaScript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
</script>

</body>
</html>
