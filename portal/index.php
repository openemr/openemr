<?php
/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//setting the session & other config options

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();


//don't require standard openemr authorization in globals.php
$ignoreAuth = 1;

//For redirect if the site on session does not match
$landingpage = "index.php?site=" . urlencode($_GET['site']);

//includes
require_once('../interface/globals.php');
require_once(dirname(__FILE__) . "/lib/appsql.class.php");
$logit = new ApplicationTable();

use OpenEMR\Core\Header;

//exit if portal is turned off
if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}
$auth['portal_pwd'] = '';
if (isset($_GET['woops'])) {
    unset($_GET['woops']);
    unset($_SESSION['password_update']);
}
if (isset($_GET['forward']) && isset($_GET['validate'])) {
    $validate = hex2bin($_GET['validate']);
    if ($validate <= time()) {
        error_log("PORTAL ERROR: " . errorLogEscape('One time reset link expired. Dying.'), 0);
        $logit->portalLog('password reset attempt', '', ($_POST['uname'] . ':link expired'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        die(xlt("Your one time credential reset link has expired. Reset and try again.") . "time:$validate time:".time());
    }
    $aReset = $_GET['forward'];
    $aReset = substr($_GET['forward'], 0, 64);
    $aReset = hex2bin($aReset);
    $token = hash('sha256', $aReset);
    $auth = sqlQueryNoLog("Select * From patient_access_onsite Where portal_login_username = ?", array($token));
    if ($auth === false) {
        error_log("PORTAL ERROR: " . errorLogEscape('One time reset:' . $aReset), 0);
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid username'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
    $_SESSION['portal_username'] = $auth['portal_username'];
    $_SESSION['portal_login_username'] = '';
    $_SESSION['password_update'] = 2;
    $_SESSION['onetime'] = $auth['portal_pwd'];
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
        $defaultLangID = $result2[0]["lang_id"];
        $defaultLangName = $result2[0]["lang_description"];
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
        $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='" . attr($defaultLangID) . "' />\n";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Patient Portal Login'); ?></title>
    <?php
        Header::setupHeader(['no_main-theme', 'datetime-picker']);
    ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/gritter/js/jquery.gritter.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['assets_static_relative']; ?>/gritter/css/jquery.gritter.css" />
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/emodal/dist/eModal.min.js"></script>
    <link rel="stylesheet" type="text/css" href="assets/css/base.css?v=<?php echo $v_js_includes; ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/register.css?v=<?php echo $v_js_includes; ?>" />
<script type="text/javascript">
    function process() {
        if (!(validate())) {
            alert (<?php echo xlj('Field(s) are missing!'); ?>);
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
            alert (<?php echo xlj('Field(s) are missing!'); ?>);
            return false;
        }
        if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
            alert (<?php echo xlj('The new password fields are not the same.'); ?>);
            return false;
        }
        if (document.getElementById('pass').value == document.getElementById('pass_new').value) {
            alert (<?php echo xlj('The new password can not be the same as the current password.'); ?>);
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
<style>
    .table > tbody > tr > td {
        border-top: 0px;
    }
</style>
</head>
<body class="skin-blue">
<br><br>
<div class="container text-center">
    <?php if (isset($_SESSION['password_update']) || isset($_GET['password_update'])) {
        $_SESSION['password_update']=1;
        ?>
      <div id="wrapper" class="centerwrapper text-center">
        <h2 class="title"><?php echo xlt('Please Enter New Credentials'); ?></h2>
        <form action="get_patient_info.php" method="POST" onsubmit="return process_new_pass()" >
            <input style="display:none" type="text" name="dummyuname"/>
            <input style="display:none" type="password" name="dummypass"/>
            <table class="table table-condensed" style="border-bottom:0px;width:100%">
                <tr>
                    <td width="35%"><strong><?php echo xlt('Account Name'); ?><strong></td>
                    <td><input class="form-control" name="uname" id="uname" type="text" readonly autocomplete="none"
                            value="<?php echo attr($_SESSION['portal_username']); ?>"/></td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('New User Name'); ?><strong></td>
                    <td><input class="form-control" name="login_uname" id="login_uname" type="text" required autocomplete="none"
                            placeholder="<?php echo xla('Must be 8 to 20 characters'); ?>" pattern=".{8,20}"
                            value="<?php echo attr($_SESSION['portal_login_username']); ?>" />
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('Current Password');?><strong></td>
                    <td>
                        <input class="form-control" name="pass" id="pass" type="password" <?php echo $_SESSION['onetime'] ? ' readonly ': ''; ?>
                            autocomplete="none" value="<?php echo attr($_SESSION['onetime']); $_SESSION['password_update']=$_SESSION['onetime'] ? 2 : 1;
                            unset($_SESSION['onetime']); ?>" required />
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('New Password');?><strong></td>
                    <td>
                        <input class="form-control" name="pass_new" id="pass_new" type="password" required
                            placeholder="<?php echo xla('Min length is 8 with upper,lowercase,numbers mix'); ?>" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('Confirm New Password');?><strong></td>
                    <td>
                        <input class="form-control" name="pass_new_confirm" id="pass_new_confirm" type="password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                    </td>
                </tr>
                <?php if ($GLOBALS['enforce_signin_email']) { ?>
                     <tr>
                        <td><strong><?php echo xlt('Confirm Email Address');?><strong></td>
                        <td>
                            <input class="form-control" name="passaddon" id="passaddon" required placeholder="<?php echo xla('Current on record trusted email'); ?>" type="email" autocomplete="none" value=""  />
                        </td>
                    </tr>
                <?php } ?>
                <tr>

                </tr>
            </table>
            <input class="btn btn-primary pull-right" type="submit" value="<?php echo xla('Log In');?>" />
            <input class="btn pull-right" type="button" onclick="document.location.replace('./index.php?woops=1');" value="<?php echo xla('Cancel');?>" />
        </form>
        <div class="copyright"><?php echo xlt('Powered by');?> OpenEMR Since 2004</div>
      </div>
    <?php } elseif (isset($_GET['requestNew'])) { ?>
    <div id="wrapper" class="centerwrapper" style="text-align:center;" >
        <form  class="form-inline" id="resetPass" action="#" method="post" >
            <div class="row">
                <div class="col-sm-10 col-md-offset-1 text-center">
                    <fieldset>
                        <legend class='bg-primary'><h3><?php echo xlt('Patient Credentials Reset') ?></h3></legend>
                        <div class="well">
                        <div class="row">
                            <div class="form-group inline">
                                <label class="control-label" for="fname"><?php echo xlt('First{{Name}}')?></label>
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
                                            placeholder="<?php echo xla('Current trusted email address on record.'); ?>" maxlength="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button id="submitRequest" class="btn btn-primary nextBtn pull-right" type="submit"><?php echo xlt('Verify') ?></button>
                        <input class="btn pull-right" type="button" onclick="document.location.replace('./index.php?woops=1');" value="<?php echo xla('Cancel');?>" />
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
                                            <input type="text" class="form-control" name="uname" id="uname" type="text" autocomplete="none" required>
                                        </div>
                                    </div>
                                    <div class="form-group inline">
                                        <label class="control-label" for="pass"><?php echo xlt('Password')?></label>
                                        <div class="controls inline-inputs">
                                            <input class="form-control" name="pass" id="pass" type="password" required autocomplete="none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php if ($GLOBALS['enforce_signin_email']) { ?>
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label" for="passaddon"><?php echo xlt('E-Mail Address')?></label>
                                        <div class="controls inline-inputs">
                                            <input class="form-control" style="width: 100%" name="passaddon" id="passaddon" placeholder="<?php echo xla('Current on file trusted email'); ?>" type="email" autocomplete="none" />
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php if ($GLOBALS['language_menu_login']) { ?>
                            <?php if (count($result3) != 1) { ?>
                        <div class="form-group row">
                            <label for="selLanguage"><?php echo xlt('Language'); ?></label>
                            <select class="form-control" id="selLanguage" name="languageChoice">
                                <?php
                                echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" .
                                     text(xl('Default') . " - " . xl($defaultLangName)) . "</option>\n";
                                foreach ($result3 as $iter) {
                                    if ($GLOBALS['language_menu_showall']) {
                                        if (! $GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                            continue; // skip the dummy language
                                        }
                                        echo "<option value='" . attr($iter['lang_id']) . "'>" .
                                        text($iter['trans_lang_description']) . "</option>\n";
                                    } else {
                                        if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                            if (! $GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                                continue; // skip the dummy language
                                            }
                                            echo "<option value='" . attr($iter['lang_id']) . "'>" .
                                            text($iter['trans_lang_description']) . "</option>\n";
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
$(function() {

<?php // if something went wrong
if (isset($_GET['requestNew'])) {
    $_SESSION['register'] = true;
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
    $("#resetPass").on('submit', function (e) {
        e.preventDefault();
        callServer('is_new', '');
        return false;
    });
<?php } ?>
<?php if (isset($_GET['w'])) { ?>
    var unique_id = $.gritter.add({
        title: '<span class="red">' + <?php echo xlj('Oops!');?> + '</span>',
        text: <?php echo xlj('Something went wrong. Please try again.'); ?>,
        sticky: false,
        time: '5000',
        class_name: 'my-nonsticky-class'
    });
<?php } ?>
<?php // if successfully logged out
if (isset($_GET['logout'])) { ?>
    var unique_id = $.gritter.add({
        title: '<span class="green">' + <?php echo xlj('Success');?> + '</span>',
        text: <?php echo xlj('You have been successfully logged out.');?>,
        sticky: false,
        time: '5000',
        class_name: 'my-nonsticky-class'
    });
<?php } ?>

return false;
});

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
            if (parseInt(rtn) !== 0) {
                var yes = confirm(<?php echo xlj("Account is validated. Send new credentials?") ?>);
                if(!yes)
                    callServer('cleanup');
                else
                    callServer('do_signup', parseInt(rtn));
            }
            else {
                // After error alert app exit to landing page.
                var message = <?php echo xlj('Unable to find your records. Be sure to use your correct Dob, First and Last name and Email of record.') ?>;
                message += "<br>" + <?php echo xlj('All search inputs are case sensitive and must match entries in your profile.'); ?>;
                eModal.alert(message);
                return false;
            }
        }
        else if (action == 'do_signup') {
            if (rtn.indexOf('ERROR') !== -1) {
                var message = <?php echo xlj('Unable to either create credentials or send email.'); ?>;
                message += "<br><br>" + <?php echo xlj('Here is what we do know.'); ?> + ": " + rtn + "<br>";
                eModal.alert(message);
                return false;
            }
            //alert(rtn); // sync alert.. rtn holds username and password for testing.
            var message = <?php echo xlj("Your new credentials have been sent. Check your email inbox and also possibly your spam folder. Once you log into your patient portal feel free to make an appointment or send us a secure message. We look forward to seeing you soon."); ?>;
            eModal.alert(message); // This is an async call. The modal close event exits us to portal landing page after cleanup.
            return false;
        }
    }).fail(function (err) {
        var message = <?php echo xlj('Something went wrong.') ?>;
        alert(message);
    });
}
</script>
</body>
</html>
