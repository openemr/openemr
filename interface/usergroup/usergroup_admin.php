<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/md5.js");
require_once("$srcdir/sql.inc");
require_once("$srcdir/auth.inc");
require_once("$srcdir/formdata.inc.php");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");
require_once ($GLOBALS['srcdir'] . "/classes/postmaster.php");

$alertmsg = '';
$bg_msg = '';
$set_active_msg=0;

/* Sending a mail to the admin when the breakglass user is activated only if $GLOBALS['Emergency_Login_email'] is set to 1 */
$bg_count=count($access_group);
$mail_id = explode(".",$SMTP_HOST);
for($i=0;$i<$bg_count;$i++){
if(($_GET['access_group'][$i] == "Emergency Login") && ($_GET['active'] == 'on') && ($_GET['pre_active'] == 0)){
  if(($_GET['get_admin_id'] == 1) && ($_GET['admin_id'] != "")){
	$res = sqlStatement("select username from users where id={$_GET["id"]}");
	$row = sqlFetchArray($res);
	$uname=$row['username'];
	$mail = new MyMailer();
        $mail->SetLanguage("en",$GLOBALS['fileroot'] . "/library/" );
        $mail->From = "admin@".$mail_id[1].".".$mail_id[2];     
        $mail->FromName = "Administrator OpenEMR";
        $text_body  = "Hello Security Admin,\n\n The Emergency Login user ".$uname.
                                                " was activated at ".date('l jS \of F Y h:i:s A')." \n\nThanks,\nAdmin OpenEMR.";
        $mail->Body = $text_body;
        $mail->Subject = "Emergency Login User Activated";
        $mail->AddAddress($_GET['admin_id']);
        $mail->Send();
}
}
}
/* To refresh and save variables in mail frame */
if ($_GET["privatemode"]=="user_admin") {
    if ($_GET["mode"] == "update") {
      if ($_GET["username"]) {
        // $tqvar = addslashes(trim($_GET["username"]));
        $tqvar = trim(formData('username','G'));
        $user_data = mysql_fetch_array(sqlStatement("select * from users where id={$_GET["id"]}"));
        sqlStatement("update users set username='$tqvar' where id={$_GET["id"]}");
        sqlStatement("update groups set user='$tqvar' where user='". $user_data["username"]  ."'");
        //echo "query was: " ."update groups set user='$tqvar' where user='". $user_data["username"]  ."'" ;
      }
      if ($_GET["taxid"]) {
        $tqvar = formData('taxid','G');
        sqlStatement("update users set federaltaxid='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["drugid"]) {
        $tqvar = formData('drugid','G');
        sqlStatement("update users set federaldrugid='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["upin"]) {
        $tqvar = formData('upin','G');
        sqlStatement("update users set upin='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["npi"]) {
        $tqvar = formData('npi','G');
        sqlStatement("update users set npi='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["taxonomy"]) {
        $tqvar = formData('taxonomy','G');
        sqlStatement("update users set taxonomy = '$tqvar' where id= {$_GET["id"]}");
      }
      if ($_GET["lname"]) {
        $tqvar = formData('lname','G');
        sqlStatement("update users set lname='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["job"]) {
        $tqvar = formData('job','G');
        sqlStatement("update users set specialty='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["mname"]) {
              $tqvar = formData('mname','G');
              sqlStatement("update users set mname='$tqvar' where id={$_GET["id"]}");
      }
      if ($_GET["facility_id"]) {
              $tqvar = formData('facility_id','G');
              sqlStatement("update users set facility_id = '$tqvar' where id = {$_GET["id"]}");
              //(CHEMED) Update facility name when changing the id
              sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = '$tqvar' AND users.id = {$_GET["id"]}");
              //END (CHEMED)
      }
      if ($GLOBALS['restrict_user_facility'] && $_GET["schedule_facility"]) {
          sqlStatement("delete from users_facility
            where tablename='users'
            and table_id={$_GET["id"]}
            and facility_id not in (" . implode(",", $_GET['schedule_facility']) . ")");
          foreach($_GET["schedule_facility"] as $tqvar) {
          sqlStatement("replace into users_facility set
                facility_id = '$tqvar',
                tablename='users',
                table_id = {$_GET["id"]}");
        }
      }
      if ($_GET["fname"]) {
              $tqvar = formData('fname','G');
              sqlStatement("update users set fname='$tqvar' where id={$_GET["id"]}");
      }

      //(CHEMED) Calendar UI preference
      if ($_GET["cal_ui"]) {
              $tqvar = formData('cal_ui','G');
              sqlStatement("update users set cal_ui = '$tqvar' where id = {$_GET["id"]}");

              // added by bgm to set this session variable if the current user has edited
          //   their own settings
          if ($_SESSION['authId'] == $_GET["id"]) {
            $_SESSION['cal_ui'] = $tqvar;
          }
      }
      //END (CHEMED) Calendar UI preference

      if (isset($_GET['default_warehouse'])) {
        sqlStatement("UPDATE users SET default_warehouse = '" .
          formData('default_warehouse','G') .
          "' WHERE id = '" . formData('id','G') . "'");
      }

     if ($_GET["newauthPass"] && $_GET["newauthPass"] != "d41d8cd98f00b204e9800998ecf8427e") { // account for empty
	$tqvar = formData('newauthPass','G');
// When the user password is updated and the password history option is enabled, update the password history in database. A new password expiration is also calculated
	if($GLOBALS['password_history'] != 0 ){
		$updatepwd = UpdatePasswordHistory($_GET["id"], $tqvar);
	}else
	{
		sqlStatement("update users set password='$tqvar' where id={$_GET["id"]}");
		if($GLOBALS['password_expiration_days'] != 0){
			$exp_days=$GLOBALS['password_expiration_days'];
			$exp_date = date('Y-m-d', strtotime("+$exp_days days"));
			sqlStatement("update users set pwd_expiration_date='$exp_date' where id=$userid");
		}
	}
}

      // for relay health single sign-on
      if ($_GET["ssi_relayhealth"]) {
        $tqvar = formData('ssi_relayhealth','G');
        sqlStatement("update users set ssi_relayhealth = '$tqvar' where id = {$_GET["id"]}");
      }

      $tqvar  = $_GET["authorized"] ? 1 : 0;
      $actvar = $_GET["active"]     ? 1 : 0;
      $calvar = $_GET["calendar"]   ? 1 : 0;
  
      sqlStatement("UPDATE users SET authorized = $tqvar, active = $actvar, " .
        "calendar = $calvar, see_auth = '" . $_GET['see_auth'] . "' WHERE " .
        "id = {$_GET["id"]}");
      //Display message when Emergency Login user was activated 
      $bg_count=count($_GET['access_group']);
      for($i=0;$i<$bg_count;$i++){
        if(($_GET['access_group'][$i] == "Emergency Login") && ($_GET['pre_active'] == 0) && ($actvar == 1)){
         $show_message = 1;
        }
      }
      if(($_GET['access_group'])){
	for($i=0;$i<$bg_count;$i++){
        if(($_GET['access_group'][$i] == "Emergency Login") && ($_GET['user_type']) == "" && ($_GET['check_acl'] == 1) && ($_GET['active']) != ""){
         $set_active_msg=1;
        }
      }
    }	
      if ($_GET["comments"]) {
        $tqvar = formData('comments','G');
        sqlStatement("update users set info = '$tqvar' where id = {$_GET["id"]}");
      }

      if (isset($phpgacl_location) && acl_check('admin', 'acl')) {
        // Set the access control group of user
        $user_data = mysql_fetch_array(sqlStatement("select username from users where id={$_GET["id"]}"));
        set_user_aro($_GET['access_group'], $user_data["username"],
          formData('fname','G'), formData('mname','G'), formData('lname','G'));
      }

      $ws = new WSProvider($_GET['id']);

    }
}

/* To refresh and save variables in mail frame  - Arb*/
if (isset($_POST["mode"])) {
  if ($_POST["mode"] == "new_user") {
    if ($_POST["authorized"] != "1") {
      $_POST["authorized"] = 0;
    }
    // $_POST["info"] = addslashes($_POST["info"]);

    $calvar = $_POST["calendar"] ? 1 : 0;

    $res = sqlStatement("select distinct username from users where username != ''");
    $doit = true;
    while ($row = mysql_fetch_array($res)) {
      if ($doit == true && $row['username'] == trim(formData('rumple'))) {
        $doit = false;
      }
    }

    if ($doit == true) {
      //if password expiration option is enabled,  calculate the expiration date of the password
      if($GLOBALS['password_expiration_days'] != 0){
      $exp_days = $GLOBALS['password_expiration_days'];
      $exp_date = date('Y-m-d', strtotime("+$exp_days days"));
      }
      $prov_id = idSqlStatement("insert into users set " .
        "username = '"         . trim(formData('rumple'       )) .
        "', password = '"      . trim(formData('newauthPass'  )) .
        "', fname = '"         . trim(formData('fname'        )) .
        "', mname = '"         . trim(formData('mname'        )) .
        "', lname = '"         . trim(formData('lname'        )) .
        "', federaltaxid = '"  . trim(formData('federaltaxid' )) .
        "', authorized = '"    . trim(formData('authorized'   )) .
        "', info = '"          . trim(formData('info'         )) .
        "', federaldrugid = '" . trim(formData('federaldrugid')) .
        "', upin = '"          . trim(formData('upin'         )) .
        "', npi  = '"          . trim(formData('npi'          )).
        "', taxonomy = '"      . trim(formData('taxonomy'     )) .
        "', facility_id = '"   . trim(formData('facility_id'  )) .
        "', specialty = '"     . trim(formData('specialty'    )) .
        "', see_auth = '"      . trim(formData('see_auth'     )) .
	    "', cal_ui = '"        . trim(formData('cal_ui'       )) .
        "', default_warehouse = '" . trim(formData('default_warehouse')) .
        "', calendar = '"      . $calvar                         .
        "', pwd_expiration_date = '" . trim("$exp_date") .
        "'");
      //set the facility name from the selected facility_id
      sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = '" . trim(formData('facility_id')) . "' AND users.username = '" . trim(formData('rumple')) . "'");

      sqlStatement("insert into groups set name = '" . trim(formData('groupname')) .
        "', user = '" . trim(formData('rumple')) . "'");

      if (isset($phpgacl_location) && acl_check('admin', 'acl') && trim(formData('rumple'))) {
        // Set the access control group of user
        set_user_aro($_POST['access_group'], trim(formData('rumple')),
          trim(formData('fname')), trim(formData('mname')), trim(formData('lname')));
      }

      $ws = new WSProvider($prov_id);

    } else {
      $alertmsg .= xl('User','','',' ') . trim(formData('rumple')) . xl('already exists.','',' ');
    }
   if($_POST['access_group']){
	 $bg_count=count($_POST['access_group']);
         for($i=0;$i<$bg_count;$i++){
          if($_POST['access_group'][$i] == "Emergency Login"){
             $set_active_msg=1;
           }
	}
      }
  }
  else if ($_POST["mode"] == "new_group") {
    $res = sqlStatement("select distinct name, user from groups");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;
    $doit = 1;
    foreach ($result as $iter) {
      if ($doit == 1 && $iter{"name"} == trim(formData('groupname')) && $iter{"user"} == trim(formData('rumple')))
        $doit--;
    }
    if ($doit == 1) {
      sqlStatement("insert into groups set name = '" . trim(formData('groupname')) .
        "', user = '" . trim(formData('rumple')) . "'");
    } else {
      $alertmsg .= "User " . trim(formData('rumple')) .
        " is already a member of group " . trim(formData('groupname')) . ". ";
    }
  }
}

if (isset($_GET["mode"])) {

  /*******************************************************************
  // This is the code to delete a user.  Note that the link which invokes
  // this is commented out.  Somebody must have figured it was too dangerous.
  //
  if ($_GET["mode"] == "delete") {
    $res = sqlStatement("select distinct username, id from users where id = '" .
      $_GET["id"] . "'");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;

    // TBD: Before deleting the user, we should check all tables that
    // reference users to make sure this user is not referenced!

    foreach($result as $iter) {
      sqlStatement("delete from groups where user = '" . $iter{"username"} . "'");
    }
    sqlStatement("delete from users where id = '" . $_GET["id"] . "'");
  }
  *******************************************************************/

  if ($_GET["mode"] == "delete_group") {
    $res = sqlStatement("select distinct user from groups where id = '" .
      $_GET["id"] . "'");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;
    foreach($result as $iter)
      $un = $iter{"user"};
    $res = sqlStatement("select name, user from groups where user = '$un' " .
      "and id != '" . $_GET["id"] . "'");

    // Remove the user only if they are also in some other group.  I.e. every
    // user must be a member of at least one group.
    if (sqlFetchArray($res) != FALSE) {
      sqlStatement("delete from groups where id = '" . $_GET["id"] . "'");
    } else {
      $alertmsg .= "You must add this user to some other group before " .
        "removing them from this group. ";
    }
  }
}

$form_inactive = empty($_REQUEST['form_inactive']) ? false : true;

?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>

<script type="text/javascript">

$(document).ready(function(){

    // fancy box
    enable_modals();

    tabbify();

    // special size for
	$(".iframe_medium").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 450,
		'frameWidth' : 660
	});


});

</script>
<script language="JavaScript">

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

</script>

</head>
<body class="body_top">

<div>
    <div>
       <table>
	  <tr >
		<td><b><?php xl('User / Groups','e'); ?></b></td>
		<td><a href="usergroup_admin_add.php" class="iframe_medium css_button"><span><?php xl('Add User','e'); ?></span></a>
		</td>
	  </tr>
	</table>
    </div>
    <div style="width:650px;">
        <div>

<form name='userlist' method='post' action='usergroup_admin.php' onsubmit='return top.restoreSession()'>
    <input type='checkbox' name='form_inactive' value='1' onclick='submit()' <?php if ($form_inactive) echo 'checked '; ?>/>
    <span class='text' style = "margin-left:-3px"> <?php xl('Include inactive users','e'); ?> </span>
</form>
<?
if($set_active_msg == 1){
echo "<font class='alert'>".xl('Emergency Login ACL is chosen. The user is still in active state, please de-activate the user and activate the same when required during emergency situations. Visit Administration->Users for activation or de-activation.')."</font><br>";
}
if ($show_message == 1){
 echo "<font class='alert'>".xl('The following Emergency Login User is activated:')." "."<b>".$_GET['fname']."</b>"."</font><br>";
 echo "<font class='alert'>".xl('Emergency Login activation email will be circulated only if following settings in the interface/globals.php file are configured:')." \$GLOBALS['Emergency_Login_email'], \$GLOBALS['Emergency_Login_email_id']</font>";
}

?>
<table cellpadding="1" cellspacing="0" class="showborder">
	<tbody><tr height="22" class="showborder_head">
		<th width="180px"><b><?php xl('Username','e'); ?></b></th>
		<th width="270px"><b><?php xl('Real Name','e'); ?></b></th>
		<th width="320px"><b><span class="bold"><?php xl('Additional Info','e'); ?></span></b></th>
		<th><b><?php xl('Authorized','e'); ?>?</b></th>

		<?php
$query = "SELECT * FROM users WHERE username != '' ";
if (!$form_inactive) $query .= "AND active = '1' ";
$query .= "ORDER BY username";
$res = sqlStatement($query);
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result4[$iter] = $row;
foreach ($result4 as $iter) {
  if ($iter{"authorized"}) {
    $iter{"authorized"} = xl('yes');
  } else {
      $iter{"authorized"} = "";
  }
  print "<tr height=20  class='text' style='border-bottom: 1px dashed;'>
		<td class='text'><b><a href='user_admin.php?id=" . $iter{"id"} .
    "' class='iframe_medium' onclick='top.restoreSession()'><span>" . $iter{"username"} . "</span></a></b>" ."&nbsp;</td>
	<td><span class='text'>" .$iter{"fname"} . ' ' . $iter{"lname"}."</span>&nbsp;</td>
	<td><span class='text'>" .$iter{"info"} . "</span>&nbsp;</td>
	<td align='left'><span class='text'>" .$iter{"authorized"} . "</span>&nbsp;</td>";
  print "<td><!--<a href='usergroup_admin.php?mode=delete&id=" . $iter{"id"} .
    "' class='link_submit'>[Delete]</a>--></td>";
  print "</tr>\n";
}
?>
	</tbody></table>
<?php
if (empty($GLOBALS['disable_non_default_groups'])) {
  $res = sqlStatement("select * from groups order by name");
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $result5[$iter] = $row;

  foreach ($result5 as $iter) {
    $grouplist{$iter{"name"}} .= $iter{"user"} .
      "(<a class='link_submit' href='usergroup_admin.php?mode=delete_group&id=" .
      $iter{"id"} . "' onclick='top.restoreSession()'>Remove</a>), ";
  }

  foreach ($grouplist as $groupname => $list) {
    print "<span class='bold'>" . $groupname . "</span><br>\n<span class='text'>" .
      substr($list,0,strlen($list)-2) . "</span><br>\n";
  }
}
?>
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
