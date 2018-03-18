<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @author Cassian LUP <cassi.lup@gmail.com>
 * @link http://www.open-emr.org
 */

    //setting the session & other config options
    session_start();

    //don't require standard openemr authorization in globals.php
    $ignoreAuth = 1;

    //For redirect if the site on session does not match
    $landingpage = "index.php?site=".$_GET['site'];

    //includes
    require_once('../interface/globals.php');

    use OpenEMR\Core\Header;

    ini_set("error_log", E_ERROR || ~E_NOTICE);
    //exit if portal is turned off
if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo htmlspecialchars(xl('Patient Portal is turned off'), ENT_NOQUOTES);
    exit;
}

    // security measure -- will check on next page.
    $_SESSION['itsme'] = 1;
    //

    //
    // Deal with language selection
    //
    // collect default language id (skip this if this is a password update)
if (!(isset($_SESSION['password_update']) || isset($_GET['requestNew']))) {
    $res2 = sqlStatement("select * from lang_languages where lang_description = ?", array($GLOBALS['language_default']));
    for ($iter = 0; $row = sqlFetchArray($res2); $iter++) {
        $result2[$iter] = $row;
    }

    if (count($result2) == 1) {
        $defaultLangID = $result2[0]{"lang_id"};
        $defaultLangName = $result2[0]{"lang_description"};
    } else {
        //default to english if any problems
        $defaultLangID = 1;
        $defaultLangName = "English";
    }

  // set session variable to default so login information appears in default language
    $_SESSION['language_choice'] = $defaultLangID;
  // collect languages if showing language menu
    if ($GLOBALS['language_menu_login']) {
        // sorting order of language titles depends on language translation options.
        $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
        if ($mainLangID == '1' && !empty($GLOBALS['skip_english_translation'])) {
            $sql = "SELECT * FROM lang_languages ORDER BY lang_description, lang_id";
            $res3=SqlStatement($sql);
        } else {
          // Use and sort by the translated language name.
            $sql = "SELECT ll.lang_id, " .
                 "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS trans_lang_description, " .
                 "ll.lang_description " .
                 "FROM lang_languages AS ll " .
                 "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
                 "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
                 "ld.lang_id = ? " .
                 "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
            $res3=SqlStatement($sql, array($mainLangID));
        }
        for ($iter = 0; $row = sqlFetchArray($res3); $iter++) {
            $result3[$iter] = $row;
        }
        if (count($result3) == 1) {
          //default to english if only return one language
            $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='1' />\n";
        }
    } else {
        $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='".htmlspecialchars($defaultLangID, ENT_QUOTES)."' />\n";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Patient Portal Login'); ?></title>
    <?php
        $css = $GLOBALS['css_header'];
        $GLOBALS['css_header'] = "";
        Header::setupHeader(['datetime-picker']);
        //$GLOBALS['css_header'] = $css;
    ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery.gritter-1-7-4/js/jquery.gritter.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery.gritter-1-7-4/css/jquery.gritter.css" />
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/emodal-1-2-65/dist/eModal.js"></script>
    <link rel="stylesheet" type="text/css" href="assets/css/base.css?v=<?php echo $v_js_includes; ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/register.css?v=<?php echo $v_js_includes; ?>" />
<script type="text/javascript">
    function process() {
        if (!(validate())) {
            alert ('<?php echo addslashes(xl('Field(s) are missing!')); ?>');
            return false;
        }
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
            alert ('<?php echo addslashes(xl('Field(s) are missing!')); ?>');
            return false;
        }
        if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
            alert ('<?php echo addslashes(xl('The new password fields are not the same.')); ?>');
            return false;
        }
        if (document.getElementById('pass').value == document.getElementById('pass_new').value) {
            alert ('<?php echo addslashes(xl('The new password can not be the same as the current password.')); ?>');
            return false;
        }
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
</head>
<body class="skin-blue">
<br><br>
<div class="container text-center">
    <?php if (isset($_SESSION['password_update']) || isset($_GET['password_update'])) {
        $_SESSION['password_update']=1;
        ?>
      <div id="wrapper" class="centerwrapper" style="text-align:center;">
        <h2 class="title"><?php echo xlt('Please Enter a New Password'); ?></h2>
        <form action="get_patient_info.php" method="POST" onsubmit="return process_new_pass()" >
            <table style="width:100%">
                <tr>
                    <td class="algnRight"><?php echo xlt('User Name'); ?></td>
                    <td><input name="uname" id="uname" type="text" autocomplete="off" value="<?php echo attr($_SESSION['portal_username']); ?>"/></td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo xlt('Current Password');?></td>
                    <td>
                        <input name="pass" id="pass" type="password" autocomplete="off" value="" />
                    </td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo xlt('New Password');?></td>
                    <td>
                        <input name="pass_new" id="pass_new" type="password" />
                    </td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo xlt('Confirm New Password');?></td>
                    <td>
                        <input name="pass_new_confirm" id="pass_new_confirm" type="password" />
                    </td>
                </tr>
                 <tr>
                    <td class="algnRight"><?php echo xlt('Confirm Email Address');?></td>
                    <td>
                        <input name="passaddon" id="passaddon" placeholder="<?php echo xla('Your on file email address'); ?>" type="email" autocomplete="off" value=""  />
                    </td>
                </tr>
                <tr>
                    <td colspan=2><br><input class="pull-right" type="submit" value="<?php echo xla('Log In');?>" /></td>
                </tr>
            </table>
        </form>
        <div class="copyright"><?php echo xlt('Powered by');?> OpenEMR</div>
      </div>
    <?php } elseif (isset($_GET['requestNew'])) { ?>
    <div id="wrapper" class="centerwrapper" style="text-align:center;" >
        <form  class="form-inline" id="resetPass" action="" method="" >
            <div class="row">
                <div class="col-sm-10 col-md-offset-1 text-center">
                    <fieldset>
                        <legend class='bg-primary'><h3><?php echo xlt('Patient Credentials Reset') ?></h3></legend>
                        <div class="well">
                        <div class="row">
                            <div class="form-group inline">
                                <label class="control-label" for="fname"><?php echo xlt('First')?></label>
                                <div class="controls inline-inputs">
                                    <input type="text" class="form-control" id="fname" required placeholder="<?php echo xla('First Name'); ?>">
                                </div>
                            </div>
                            <div class="form-group inline">
                                <label class="control-label" for="lname"><?php echo xlt('Last Name')?></label>
                                <div class="controls inline-inputs">
                                    <input type="text" class="form-control" id="lname" required placeholder="<?php echo xla('Enter Last'); ?>">
                                </div>
                            </div>
                            <div class="form-group inline">
                                <label class="control-label" for="dob"><?php echo xlt('Birth Date')?></label>
                                <div class="controls inline-inputs">
                                    <div class="input-group">
                                        <input id="dob" type="text" required class="form-control datepicker" placeholder="<?php echo xla('YYYY-MM-DD'); ?>" />
                                    </div>
                                </div>
                            </div></div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label" for="emailInput"><?php echo xlt('Enter E-Mail Address')?></label>
                                    <div class="controls inline-inputs">
                                        <input id="emailInput" type="email" class="form-control" style="width: 100%" required
                                            placeholder="<?php echo xla('Must be current email address on file.'); ?>" maxlength="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button id="submitRequest" class="btn btn-primary nextBtn btn-sm pull-right" type="button"><?php echo xlt('Verify') ?></button>
                    </fieldset>
                </div>
            </div>
        </form>
    </div>
    <?php } else {
?>  <!-- Main logon -->
    <div id="wrapper" class="row centerwrapper text-center">
    <img style="width:65%" src='<?php echo $GLOBALS['images_static_relative']; ?>/login-logo.png'/>
    <form  class="form-inline text-center" action="get_patient_info.php" method="POST" onsubmit="return process()">
        <div class="row">
                <div class="col-sm-12 text-center">
                    <fieldset>
                        <legend class="bg-primary"><h3><?php echo xlt('Patient Portal Login'); ?></h3></legend>
                        <div class="well">
                        <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group inline">
                                        <label class="control-label" for="uname"><?php echo xlt('Username')?></label>
                                        <div class="controls inline-inputs">
                                            <input type="text" class="form-control" name="uname" id="uname" type="text" autocomplete="on" required>
                                        </div>
                                    </div>
                                    <div class="form-group inline">
                                        <label class="control-label" for="pass"><?php echo xlt('Password')?></label>
                                        <div class="controls inline-inputs">
                                            <input class="form-control" name="pass" id="pass" type="password" required autocomplete="on">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label" for="passaddon"><?php echo xlt('E-Mail Address')?></label>
                                    <div class="controls inline-inputs">
                                        <input class="form-control" style="width: 100%" name="passaddon" id="passaddon" placeholder="<?php echo xla('on file email'); ?>" type="email" autocomplete="on" />
                                    </div>
                                </div>
                            </div>
                        <?php if ($GLOBALS['language_menu_login']) { ?>
                        <?php if (count($result3) != 1) { ?>
                        <div class="form-group row">
                            <label for="selLanguage"><?php echo xlt('Language'); ?></label>
                            <select class="form-control" id="selLanguage" name="languageChoice">
                            <?php
                                echo "<option selected='selected' value='" . htmlspecialchars($defaultLangID, ENT_QUOTES) . "'>" .
                                     htmlspecialchars(xl('Default') . " - " . xl($defaultLangName), ENT_NOQUOTES) . "</option>\n";
                            foreach ($result3 as $iter) {
                                if ($GLOBALS['language_menu_showall']) {
                                    if (! $GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                        continue; // skip the dummy language
                                    }
                                    echo "<option value='" . htmlspecialchars($iter['lang_id'], ENT_QUOTES) . "'>" .
                                         htmlspecialchars($iter['trans_lang_description'], ENT_NOQUOTES) . "</option>\n";
                                } else {
                                    if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                        if (! $GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                            continue; // skip the dummy language
                                        }
                                        echo "<option value='" . htmlspecialchars($iter['lang_id'], ENT_QUOTES) . "'>" .
                                             htmlspecialchars($iter['trans_lang_description'], ENT_NOQUOTES) . "</option>\n";
                                    }
                                }
                            }
                            ?>
                          </select>
                        </div>
                        <?php } } ?>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <?php if ($GLOBALS['portal_onsite_two_register']) { ?>
                                <button class="btn btn-default pull-left"  onclick="location.replace('./account/register.php')"><?php echo xlt('Register');?></button>
                            <?php } ?>
                            <?php if ($GLOBALS['portal_two_pass_reset'] && isset($_GET['w']) && (isset($_GET['u']) || isset($_GET['p']))) { ?>
                               <button class="btn btn-danger" onclick="location.replace('./index.php?requestNew=1')" style="margin-left:10px"><?php echo xlt('Reset Credentials');?></button>
                            <?php } ?>
                                <button  class="btn btn-success pull-right" type="submit" ><?php echo xlt('Log In');?></button>
                        </div>
                    </fieldset>
                </div>
          </div>
            <?php if (!(empty($hiddenLanguageField))) {
                echo $hiddenLanguageField; } ?>
    </form>
    </div><!-- div wrapper -->
    <?php } ?> <!--  logon wrapper -->
</div><!-- container -->

<script type="text/javascript">
$(document).ready(function() {

<?php // if something went wrong
if (isset($_GET['requestNew'])) {
    $_SESSION['patient_portal_onsite_two'] = true;
    $_SESSION['authUser'] = 'portal-user';
    $_SESSION['pid'] = true;
    ?>
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
     });
    $(document.body).on('hidden.bs.modal', function () {
        callServer('cleanup');
    });
    $("#submitRequest").click(function () {
        callServer('is_new', '');
    });
<?php } ?>
<?php if (isset($_GET['w'])) { ?>
    var unique_id = $.gritter.add({
        title: '<span class="red"><?php echo xlt('Oops!');?></span>',
        text: '<?php echo xlt('Something went wrong. Please try again.'); ?>',
        sticky: false,
        time: '5000',
        class_name: 'my-nonsticky-class'
    });
<?php } ?>
<?php // if successfully logged out
if (isset($_GET['logout'])) { ?>
    var unique_id = $.gritter.add({
        title: '<span class="green"><?php echo xlt('Success');?></span>',
        text: '<?php echo xlt('You have been successfully logged out.');?>',
        sticky: false,
        time: '5000',
        class_name: 'my-nonsticky-class'
    });
<?php } ?>

return false;
});
/* Test Data
$("#emailInput").val("me@me.com");
$("#fname").val("Jerry");
$("#lname").val("Padgett");
$("#dob").val("1919-03-03"); */

function callServer(action, value, value2, last, first) {
    var data = {
        'action' : action,
        'value' : value,
        'dob' : $("#dob").val(),
        'last' : $("#lname").val(),
        'first' : $("#fname").val(),
        'email' : $("#emailInput").val()
    }
    if (action == 'do_signup') {
        data = {
            'action': action,
            'pid': value
        };
    }
    else if (action == 'notify_admin') {
        data = {
            'action': action,
            'pid': value,
            'provider': value2
        };
    }
    else if (action == 'cleanup') {
        data = {
            'action': action
        }
    };
    $.ajax({
        type : 'GET',
        url : './account/account.php',
        data : data
    }).done(function (rtn) {
        if (action == "cleanup") {
            window.location.href = "./index.php" // Goto landing page.
        }
        else if (action == "is_new") {
            if (parseInt(rtn) > 0) {
                var yes = confirm('<?php echo xls("Account is validated. Send new credentials?") ?>');
                if(!yes)
                    callServer('cleanup');
                else
                    callServer('do_signup', parseInt(rtn));
            }
            else {
                // After error alert app exit to landing page.
                var message = "<?php echo xls('Unable to find your records. Be sure to use your correct Dob, First and Last name and Email of record. If you have opted out of email with none on file then leave blank.'); ?>";
                eModal.alert(message);
            }
        }
        else if (action == 'do_signup') {
            if (rtn == "") {
                var message = "<?php echo xlt('Unable to either create credentials or send email.'); ?>";
                alert(message);
                return false;
            }
            //alert(rtn); // sync alert.. rtn holds username and password for testing.
            var message = "<?php echo xls("Your new credentials have been sent. Check your email inbox and also possibly your spam folder. Once you log into your patient portal feel free to make an appointment or send us a secure message. We look forward to seeing you soon."); ?>"
            eModal.alert(message); // This is an async call. The modal close event exits us to portal landing page after cleanup.
        }
    }).fail(function (err) {
        var message = "<?php echo xls('Something went wrong.') ?>";
        alert(message);
    });
}
</script>
</body>
</html>
