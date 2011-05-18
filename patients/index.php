<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

    //setting the session & other config options
    session_start();

    //don't require standard openemr authorization in globals.php
    $ignoreAuth = 1;

    //SANITIZE ALL ESCAPES
    $fake_register_globals=false;

    //STOP FAKE REGISTER GLOBALS
    $sanitize_all_escapes=true;

    //includes
    require_once('../interface/globals.php');
    require_once("$srcdir/sha1.js");
    // 

    //exit if portal is turned off
    if ( !(isset($GLOBALS['portal_onsite_enable'])) || !($GLOBALS['portal_onsite_enable']) ) {
      echo htmlspecialchars( xl('Patient Portal is turned off'), ENT_NOQUOTES);
      exit;
    }

    // security measure -- will check on next page.
    $_SESSION['itsme'] = 1;
    // 
?>

<html>
<head>
    <title><?php echo htmlspecialchars( xl('Access your patient information'), ENT_NOQUOTES); ?></title>

    <script type="text/javascript" src="../library/js/jquery-1.5.js"></script>
    <script type="text/javascript" src="../library/js/jquery.gritter.min.js"></script>

    <link rel="stylesheet" type="text/css" href="css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="css/base.css" />

    <script type="text/javascript">
        function process() {
            
            if (!(validate())) {
                alert ('<?php echo addslashes( xl('Field(s) are missing!') ); ?>');
                return false;
            }
            document.getElementById('code').value = SHA1(document.getElementById('pass').value);
            document.getElementById('pass').value='';
        }
	function validate() {
            var pass=true;            
	    if (document.getElementById('uname').value == "") {
		document.getElementById('uname').style.border = "1px solid red";
                pass=false;
	    }
	    if (document.getElementById('pass').value == "") {
		document.getElementById('pass').style.border = "1px solid red";
                pass=false;
	    }
            return pass;
	}
        function process_new_pass() {

            if (!(validate_new_pass())) {
                alert ('<?php echo addslashes( xl('Field(s) are missing!') ); ?>');
                return false;
            }
            if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
                alert ('<?php echo addslashes( xl('The new password fields are not the same.') ); ?>');
                return false;
            }
            if (document.getElementById('pass').value == document.getElementById('pass_new').value) {
                alert ('<?php echo addslashes( xl('The new password can not be the same as the current password.') ); ?>');
                return false;
            }
            document.getElementById('code').value = SHA1(document.getElementById('pass').value);
            document.getElementById('pass').value='';
            document.getElementById('code_new').value = SHA1(document.getElementById('pass_new').value);
            document.getElementById('pass_new').value='';
            document.getElementById('code_new_confirm').value = SHA1(document.getElementById('pass_new_confirm').value);
            document.getElementById('pass_new_confirm').value='';
        }
        function validate_new_pass() {
            var pass=true;
            if (document.getElementById('uname').value == "") {
                document.getElementById('uname').style.border = "1px solid red";
                pass=false;
            }
            if (document.getElementById('pass').value == "") {
                document.getElementById('pass').style.border = "1px solid red";
                pass=false;
            }
            if (document.getElementById('pass_new').value == "") {
                document.getElementById('pass_new').style.border = "1px solid red";
                pass=false;
            }
            if (document.getElementById('pass_new_confirm').value == "") {
                document.getElementById('pass_new_confirm').style.border = "1px solid red";
                pass=false;
            }
            return pass;
        }
    </script>
    <style type="text/css">
	body {
	    font-family: sans-serif;
	    background-color: #638fd0;
	    
	    background: -webkit-radial-gradient(circle, white, #638fd0);
	    background: -moz-radial-gradient(circle, white, #638fd0);
	}

    </style>
    
    
</head>
<body>
<br><br>
    <center>

    <?php if (isset($_SESSION['password_update'])) { ?>
      <div id="wrapper" class="centerwrapper">
        <h2 class="title"><?php echo htmlspecialchars( xl('Please Enter a New Password'), ENT_NOQUOTES); ?></h2>
        <form action="get_patient_info.php" method="POST" onsubmit="return process_new_pass()" >
            <table>
                <tr>
                    <td class="algnRight"><?php echo htmlspecialchars( xl('User Name'), ENT_NOQUOTES); ?></td>
                    <td><input name="uname" id="uname" type="text" /></td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo htmlspecialchars( xl('Current Password'), ENT_NOQUOTES);?></>
                    <td>
                        <input name="pass" id="pass" type="password" />
                        <input type="hidden" id="code" name="code" type="hidden" />
                    </td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo htmlspecialchars( xl('New Password'), ENT_NOQUOTES);?></>
                    <td>
                        <input name="pass_new" id="pass_new" type="password" />
                        <input type="hidden" id="code_new" name="code_new" type="hidden" />
                    </td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo htmlspecialchars( xl('Confirm New Password'), ENT_NOQUOTES);?></>
                    <td>
                        <input name="pass_new_confirm" id="pass_new_confirm" type="password" />
                        <input type="hidden" id="code_new_confirm" name="code_new_confirm" type="hidden" />
                    </td>
                </tr>
                <tr>
                    <td colspan=2><br><center><input type="submit" value="<?php echo htmlspecialchars( xl('Log In'), ENT_QUOTES);?>" /></center></td>
                </tr>
            </table>
        </form>

        <div class="copyright"><?php echo htmlspecialchars( xl('Powered by'), ENT_NOQUOTES);?> <a href="../../">OpenEMR</a></div>
      </div>
    <?php } else { ?>
      <div id="wrapper" class="centerwrapper">
	<h2 class="title"><?php echo htmlspecialchars( xl('Access your patient information'), ENT_NOQUOTES); ?></h2>
	<form action="get_patient_info.php" method="POST" onsubmit="return process()" >
	    <table>
		<tr>
		    <td class="algnRight"><?php echo htmlspecialchars( xl('User Name'), ENT_NOQUOTES); ?></td>
		    <td><input name="uname" id="uname" type="text" /></td>
		</tr>
		<tr>
		    <td class="algnRight"><?php echo htmlspecialchars( xl('Password'), ENT_NOQUOTES);?></>
		    <td>
			<input name="pass" id="pass" type="password" />
			<input type="hidden" id="code" name="code" type="hidden" />
		    </td>
		</tr>
		<tr>
		    <td colspan=2><br><center><input type="submit" value="<?php echo htmlspecialchars( xl('Log In'), ENT_QUOTES);?>" /></center></td>
		</tr>
	    </table>
	</form>
    
        <div class="copyright"><?php echo htmlspecialchars( xl('Powered by'), ENT_NOQUOTES);?> <a href="../../">OpenEMR</a></div>
      </div>
    <?php } ?>

    </center>

<script type="text/javascript">
      $(document).ready(function() {

<?php // if something went wrong
     if (isset($_GET['w'])) { ?>    
	var unique_id = $.gritter.add({
	    title: '<span class="red"><?php echo htmlspecialchars( xl('Oops!'), ENT_QUOTES);?></span>',
	    text: '<?php echo htmlspecialchars( xl('Something went wrong. Please try again.', ENT_QUOTES)); ?>',
	    sticky: false,
	    time: '5000',
	    class_name: 'my-nonsticky-class'
	});    
<?php } ?>

<?php // if successfully logged out
     if (isset($_GET['logout'])) { ?>    
	var unique_id = $.gritter.add({
	    title: '<span class="green"><?php echo htmlspecialchars( xl('Success'), ENT_QUOTES);?></span>',
	    text: '<?php echo htmlspecialchars( xl('You have been successfully logged out.'), ENT_QUOTES);?>',
	    sticky: false,
	    time: '5000',
	    class_name: 'my-nonsticky-class'
	});    
<?php } ?>
	return false;
    
    });
</script>

</body>
</html>
