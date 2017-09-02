<?php
/**
 * User password change tool
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/auth.inc");

use OpenEMR\Core\Header;

if ($GLOBALS['use_active_directory']) {
    exit();
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Change Password'); ?></title>

<script src="checkpwd_validation.js" type="text/javascript"></script>

<script language='JavaScript'>
//Validating password and display message if password field is empty - starts
var webroot='<?php echo $webroot?>';
function update_password()
{
    top.restoreSession();
    // Not Empty
    // Strong if required
    // Matches

    $.post("user_info_ajax.php",
        {
            curPass:    $("input[name='curPass']").val(),
            newPass:    $("input[name='newPass']").val(),
            newPass2:   $("input[name='newPass2']").val(),
        },
        function(data)
        {
            $("input[type='password']").val("");
            $("#display_msg").html(data);
        }

    );
    return false;
}

</script>
</head>
<body class="body_top" onload="document.forms[0].curPass.focus()">

<?php

$ip=$_SERVER['REMOTE_ADDR'];
$res = sqlStatement("select fname,lname,username from users where id=?", array($_SESSION["authId"]));
$row = sqlFetchArray($res);
      $iter=$row;
?>
<div class="container">
   <div class="row">
      <div class="col-xs-12">
         <div class="page-header">
            <h3><?php echo xlt('Change Password'); ?></h3>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-xs-12">
        <div id="display_msg"></div>
      </div>
   </div>

  <div class="row">
     <div class="col-xs-12">
        <form method='post' action='user_info.php' class='form-horizontal' onsubmit='return update_password()'>
        <input type=hidden name=secure_pwd value="<?php echo $GLOBALS['secure_password']; ?>">
        <div class="form-group">
           <label class='control-label col-sm-2'><?php echo xlt('Full Name') . ":"; ?></label>
           <div class="col-sm-10">
           <p class="form-control-static"><?php echo text($iter["fname"]) . " " . text($iter["lname"]) ?></p>
           </div>
        </div>
        <div class="form-group">
           <label class='control-label col-sm-2'><?php echo xlt('User Name') . ":"; ?></label>
           <div class="col-sm-10">
           <p class="form-control-static"><?php echo text($iter["username"]) ?></p>
           </div>
        </div>
        <div class="form-group">
           <label for='curPass' class='control-label col-sm-2'><?php echo xlt('Current Password') . ":"; ?></label>
           <div class='col-sm-3'>
           <input type='password' class='form-control'  name='curPass'  id='curPass' value="" autocomplete='off'>
           </div>
        </div>
        <div class="form-group">
           <label class='control-label col-sm-2'><?php echo xlt('New Password') . ":"; ?></label>
           <div class='col-sm-3'>
           <input type='password' class='form-control' name='newPass'  value="" autocomplete='off'>
           </div>
        </div>
        <div class="form-group">
           <label class='control-label col-sm-2'><?php echo xlt('Repeat New Password') . ":"; ?></label>
           <div class='col-sm-3'>
           <input type='password' class='form-control' name=newPass2  value="" autocomplete='off'>
           </div>
        </div>
        <div class="form-group">
           <div class='col-sm-offset-2 col-sm-10'>
              <button type="Submit" class='btn btn-default btn-save'><?php echo xlt('Save Changes'); ?></button>
           </div>
        </div>
        </form>
     </div>
  </div>
</div>

</body>
</html>

<?php
//  da39a3ee5e6b4b0d3255bfef95601890afd80709 == blank
?>
