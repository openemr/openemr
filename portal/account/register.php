<?php

/**
 * Portal Registration Wizard
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();
session_regenerate_id(true);

unset($_SESSION['itsme']);
$_SESSION['authUser'] = 'portal-user';
$_SESSION['pid'] = true;
$_SESSION['register'] = true;

$_SESSION['site_id'] = isset($_SESSION['site_id']) ? $_SESSION['site_id'] : 'default';
$landingpage = "index.php?site=" . urlencode($_SESSION['site_id']);

$ignoreAuth_onsite_portal = true;

require_once("../../interface/globals.php");
if (!$GLOBALS['portal_onsite_two_register']) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    echo xlt("Not Authorized");
    @header('HTTP/1.1 401 Unauthorized');
    die();
}

$res2 = sqlStatement("select * from lang_languages where lang_description = ?", array(
    $GLOBALS['language_default']
));
for ($iter = 0; $row = sqlFetchArray($res2); $iter++) {
    $result2[$iter] = $row;
}
if (count($result2) == 1) {
    $defaultLangID = $result2[0]["lang_id"];
    $defaultLangName = $result2[0]["lang_description"];
} else {
    // default to english if any problems
    $defaultLangID = 1;
    $defaultLangName = "English";
}

if (!isset($_SESSION['language_choice'])) {
    $_SESSION['language_choice'] = $defaultLangID;
}
// collect languages if showing language menu
if ($GLOBALS['language_menu_login']) {
    // sorting order of language titles depends on language translation options.
    $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
    // Use and sort by the translated language name.
    $sql = "SELECT ll.lang_id, " . "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS trans_lang_description, " . "ll.lang_description " .
        "FROM lang_languages AS ll " . "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
        "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " . "ld.lang_id = ? " .
        "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
    $res3 = SqlStatement($sql, array(
        $mainLangID
    ));

    for ($iter = 0; $row = sqlFetchArray($res3); $iter++) {
        $result3[$iter] = $row;
    }

    if (count($result3) == 1) {
        // default to english if only return one language
        $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='1' />\n";
    }
} else {
    $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='" . attr($defaultLangID) . "' />\n";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('New Patient'); ?> | <?php echo xlt('Register'); ?></title>
    <meta name="description" content="Developed By sjpadgett@gmail.com" />

    <?php Header::setupHeader(['no_main-theme', 'datetime-picker', 'patientportal-style', 'patientportal-register']); ?>

    <script>
        var webRoot = <?php echo js_escape($GLOBALS['web_root']); ?>;
        top.webroot_url = webRoot;
        var newPid = 0;
        var curPid = 0;
        var provider = 0;

        function restoreSession() {
            //dummy functions so the dlgopen function will work in the patient portal
            return true;
        }
        $(function () {
            var navListItems = $('div.setup-panel div a'),
                allWells = $('.setup-content'),
                allNextBtn = $('.nextBtn'),
                allPrevBtn = $('.prevBtn');

            allWells.hide();

            navListItems.click(function (e) {
                e.preventDefault();
                if (!$(this).hasClass('disabled')) {
                    navListItems.removeClass('btn-primary').addClass('btn-light');
                    $(this).addClass('btn-primary').removeClass('btn-light');
                    allWells.hide();
                    $($(this).attr('href')).show();
                    $($(this).attr('href')).find('input:eq(0)').focus();
                }
            });

            allPrevBtn.click(function () {
                var curStep = $(this).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    prevstepwiz = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");
                prevstepwiz.removeClass('disabled').trigger('click');
            });

            allNextBtn.click(function () {
                var profile = $("#profileFrame").contents();

                // Fix for iFrame height
                window.addEventListener('message', function(e) {
                    var scroll_height = e.data;
                    document.getElementById('profileFrame').style.height = scroll_height + 'px';
                }, false);

                var curStep = $(this).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    nextstepwiz = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                    curInputs = curStep.find("input[type='text'],input[type='email'],select"),
                    isValid = true;

                $(".form-group").removeClass("has-error");
                for (var i = 0; i < curInputs.length; i++) {
                    if (!curInputs[i].validity.valid) {
                        isValid = false;
                        $(curInputs[i]).closest(".form-group").addClass("has-error");
                    }
                }
                if (isValid) {
                    if (curStepBtn == 'step-1') { // leaving step 1 setup profile frame. Prob not nec but in case
                    let fn = $("#fname").val().replace(/^./, $("#fname").val()[0].toUpperCase());
                    let ln = $("#lname").val().replace(/^./, $("#lname").val()[0].toUpperCase());
                    profile.find('input#fname').val(fn);
                        profile.find('input#mname').val($("#mname").val());
                    profile.find('input#lname').val(ln);
                        profile.find('input#dob').val($("#dob").val());
                        profile.find('input#email').val($("#emailInput").val());
                    profile.find('input#emailDirect').val($("#emailInput").val());
                    // disable to prevent already validated field changes.
                    profile.find('input#fname').prop("disabled", true);
                    profile.find('input#mname').prop("disabled", true);
                    profile.find('input#lname').prop("disabled", true);
                    profile.find('input#dob').prop("disabled", true);
                    profile.find('input#email').prop("disabled", true);
                    profile.find('input#emailDirect').prop("disabled", true);

                        profile.find('input[name=allowPatientPortal]').val(['YES']);
                    profile.find('input[name=hipaaAllowemail]').val(['YES']);
                        // need these for validation.
                        profile.find('select#providerid option:contains("Unassigned")').val('');
                    // must have a provider for many reasons. w/o save won't work.
                        //profile.find('select#providerid').attr('required', true);
                        profile.find('select#sex option:contains("Unassigned")').val('');
                        profile.find('select#sex').attr('required', true);

                        var pid = profile.find('input#pid').val();
                        if (pid < 1) { // form pid set in promise
                        callServer('get_newpid', '',
                            encodeURIComponent($("#dob").val()),
                            encodeURIComponent($("#lname").val()),
                            encodeURIComponent($("#fname").val()),
                            encodeURIComponent($("#emailInput").val()));
                        }
                    }
                    nextstepwiz.removeClass('disabled').trigger('click');
                }
            });

            $("#profileNext").click(function () {
                var profile = $("#profileFrame").contents();
                var curStep = $(this).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    nextstepwiz = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                    curInputs = $("#profileFrame").contents().find("input[type='text'],input[type='email'],select"),
                    isValid = true;
                $(".form-group").removeClass("has-error");
                var flg = 0;
                for (var i = 0; i < curInputs.length; i++) {
                    if (!curInputs[i].validity.valid) {
                        isValid = false;
                        if (!flg) {
                            curInputs[i].scrollIntoView();
                            curInputs[i].focus();
                            flg = 1;
                        }
                        $(curInputs[i]).closest(".form-group").addClass("has-error");
                    }
                }
            // test for new once again
            // this time using the profile data that will be saved as new patient.
            // callserver will intercept on fail or silence to continue.
            let stillNew = callServer('get_newpid', '',
                encodeURIComponent(profile.find('input#dob').val()),
                encodeURIComponent(profile.find('input#lname').val()),
                encodeURIComponent(profile.find('input#fname').val()),
                encodeURIComponent(profile.find('input#email').val()));
                if (isValid) {
                    provider = profile.find('select#providerid').val();
                    nextstepwiz.removeClass('disabled').trigger('click');
                }
            });

            $("#submitPatient").click(function () {
                var profile = $("#profileFrame").contents();
                var pid = profile.find('input#pid').val();

                if (pid < 1) {
                    callServer('get_newpid', '');
                }

                var isOk = checkRegistration(newPid);
                if (isOk) {
                    // Use portals rest api. flag 1 is write to chart. flag 0 writes an audit record for review in dashboard.
                    // rest update will determine if new or existing pid for save. In register step-1 we catch existing pid but,
                    // we can still use update here if we want to allow changing passwords.

                    // save the new patient.
                    document.getElementById('profileFrame').contentWindow.postMessage({submitForm: true}, window.location.origin);
                    $("#insuranceForm").submit();
                    //  cleanup is in callServer done promise. This starts end session.
                }
            });

            $('div.setup-panel div a.btn-primary').trigger('click');

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });

            $("#insuranceForm").submit(function (e) {
                e.preventDefault();
                var url = "account.php?action=new_insurance&pid=" + encodeURIComponent(newPid);
                $.ajax({
                    url: url,
                    type: 'post',
                    data: $("#insuranceForm").serialize(),
                    success: function (serverResponse) {
                        doCredentials(newPid) // this is the end for session.
                        return false;
                    }
                });
            });

            $('#selLanguage').on('change', function () {
                callServer("set_lang", this.value);
            });

            $(document.body).on('hidden.bs.modal', function () { //@TODO maybe make a promise for wiz exit
                callServer('cleanup');
            });

            $('#inscompany').on('change', function () {
                if ($('#inscompany').val().toUpperCase() === 'SELF') {
                    $("#insuranceForm input").removeAttr("required");
                    let message = <?php echo xlj('You have chosen to be self insured or currently do not have insurance. Click next to continue registration.'); ?>;
                    alert(message);
                }
            });

        $("#dob").on('blur', function () {
            let bday = $(this).val() ?? '';
            let age = Math.round(Math.abs((new Date().getTime() - new Date(bday).getTime())));
            age = Math.round(age / 1000 / 60 / 60 / 24);
            // need to be at least 30 days old otherwise likely an error.
            if (age < 30) {
                let msg = <?php echo (xlj("Invalid Date format or value! Type date as YYYY-MM-DD or use the calendar.") ); ?> ;
                $(this).val('');
                $(this).prop('placeholder', 'Invalid Date');
                alert(msg);
                return false;
            }
        });

        }); // ready end

        function doCredentials(pid) {
            callServer('do_signup', pid);
        }

        function checkRegistration(pid) {
            var profile = $("#profileFrame").contents();
            var curStep = $("#step-2"),
                curStepBtn = curStep.attr("id"),
                nextstepwiz = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                curInputs = $("#profileFrame").contents().find("input[type='text'],input[type='email'],select"),
                isValid = true;
            $(".form-group").removeClass("has-error");
            var flg = 0;
            for (var i = 0; i < curInputs.length; i++) {
                if (!curInputs[i].validity.valid) {
                    isValid = false;
                    if (!flg) {
                        curInputs[i].scrollIntoView();
                        curInputs[i].focus();
                        flg = 1;
                    }
                    $(curInputs[i]).closest(".form-group").addClass("has-error");
                }
            }

            if (!isValid) {
                return false;
            }

            return true;
        }

    function callServer(action, value, value2, last, first, email = null) {
            let message = '';
            let data = {
                'action': action,
                'value': value,
                'dob': value2,
                'last': last,
                'first': first,
                'email': email
            }
            if (action == 'do_signup') {
                data = {
                    'action': action,
                    'pid': value
                };
            } else if (action == 'notify_admin') {
                data = {
                    'action': action,
                    'pid': value,
                    'provider': value2
                };
            } else if (action == 'cleanup') {
                data = {
                    'action': action
                };
            }
            // The magic that is jquery ajax.
            $.ajax({
                type: 'GET',
                url: 'account.php',
                data: data
            }).done(function (rtn) {
                if (action == "cleanup") {
                window.location.href = "./../index.php" // Goto landing page.
                } else if (action == "set_lang") {
                    window.location.href = window.location.href;
                } else if (action == "get_newpid") {
                    if (parseInt(rtn) > 0) {
                        newPid = rtn;
                        $("#profileFrame").contents().find('input#pubpid').val(newPid);
                        $("#profileFrame").contents().find('input#pid').val(newPid);
                    } else {
                        // After error alert app exit to landing page.
                        // Existing user error. Error message is translated in account.lib.php.
                        dialog.alert(rtn);
                    }
                } else if (action == 'do_signup') {
                    if (rtn.indexOf('ERROR') !== -1) {
                        message = <?php echo xlj('Unable to either create credentials or send email.'); ?>;
                        message += "<br /><br />" + <?php echo xlj('Here is what we do know.'); ?> +": " + rtn + "<br />";
                        dialog.alert(message);
                        return false;
                    }
                    // For production. Here we're finished so do signup closing alert and then cleanup.
                    callServer('notify_admin', newPid, provider); // pnote notify to selected provider
                    // alert below for ease of testing.
                    //alert(rtn); // sync alert.. rtn holds username and password for testing.

                    message = <?php echo xlj("Your new credentials have been sent. Check your email inbox and also possibly your spam folder. Once you log into your patient portal feel free to make an appointment or send us a secure message. We look forward to seeing you soon."); ?>;
                    dialog.alert(message); // This is an async call. The modal close event exits us to portal landing page after cleanup.
                    return false;
                }
            }).fail(function (err) {
                message = <?php echo xlj('Something went wrong.') ?>;
                alert(message);
            });
        }
    </script>
</head>
<body class="mt-4 skin-blue">
    <div class="container">
        <h1 class="text-center"><?php echo xlt('Account Registration'); ?></h1>
        <div class="stepwiz">
            <div class="stepwiz-row setup-panel">
                <div class="stepwiz-step">
                    <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                    <p><?php echo xlt('Get Started') ?></p>
                </div>
                <div class="stepwiz-step">
                    <a href="#step-2" type="button" class="btn btn-light btn-circle disabled">2</a>
                    <p><?php echo xlt('Profile') ?></p>
                </div>
                <div class="stepwiz-step">
                    <a href="#step-3" type="button" class="btn btn-light btn-circle disabled">3</a>
                    <p><?php echo xlt('Insurance') ?></p>
                </div>
                <div class="stepwiz-step">
                    <a href="#step-4" type="button" class="btn btn-light btn-circle disabled">4</a>
                    <p><?php echo xlt('Register') ?></p>
                </div>
            </div>
        </div>
        <!-- // Start Forms // -->
        <form id="startForm" role="form" action="" method="post" onsubmit="">
            <div class="text-center setup-content" id="step-1">
                <legend class="bg-primary text-white"><?php echo xlt('Contact Information') ?></legend>
                <div class="jumbotron">
                    <?php if ($GLOBALS['language_menu_login']) { ?>
                        <?php if (count($result3) != 1) { ?>
                            <div class="form-group">
                                <label class="col-form-label" for="selLanguage"><?php echo xlt('Language'); ?></label>
                                <select class="form-control" id="selLanguage" name="languageChoice">
                                    <?php
                                    echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" .
                                        text(xl('Default') . " - " . xl($defaultLangName)) . "</option>\n";
                                    foreach ($result3 as $iter) {
                                        if ($GLOBALS['language_menu_showall']) {
                                            if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                                continue; // skip the dummy language
                                            }
                                            echo "<option value='" . attr($iter['lang_id']) . "'>" .
                                                text($iter['trans_lang_description']) . "</option>\n";
                                        } else {
                                            if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                                if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
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
                        <?php }
                    } ?>
                    <div class="form-row">
                        <div class="col form-group">
                          <label for="fname"><?php echo xlt('First Name') ?></label>
                          <input type="text" class="form-control" id="fname" required placeholder="<?php echo xla('First Name'); ?>" />
                        </div>
                        <div class="col form-group">
                          <label for="mname"><?php echo xlt('Middle Name') ?></label>
                          <input type="text" class="form-control" id="mname" placeholder="<?php echo xla('Full or Initial'); ?>" />
                        </div>
                        <div class="col form-group">
                          <label for="lname"><?php echo xlt('Last Name') ?></label>
                          <input type="text" class="form-control" id="lname" required placeholder="<?php echo xla('Enter Last'); ?>" />
                        </div>
                        <div class="col form-group">
                          <label for="dob"><?php echo xlt('Birth Date') ?></label>
                          <input id="dob" type="text" required class="form-control datepicker" placeholder="<?php echo xla('YYYY-MM-DD'); ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label" for="emailInput"><?php echo xlt('Enter E-Mail Address') ?></label>
                        <input id="emailInput" type="email" class="reg-email form-control" required placeholder="<?php echo xla('Enter email address to receive registration.'); ?>" maxlength="100" />
                    </div>
                </div>

                <button class="btn btn-primary nextBtn pull-right" type="button"><?php echo xlt('Next') ?></button>
            </div>
        </form>
        <!-- Profile Form -->
        <form id="profileForm" role="form" action="account.php" method="post">
            <div class="text-center setup-content" id="step-2" style="display: none">
                <legend class="bg-primary text-white"><?php echo xlt('Profile') ?></legend>
                <div class="jumbotron">
                    <iframe class="embedded-content" src="../patient/patientdata?pid=0&register=true" id="profileFrame" name="Profile Info"></iframe>
                </div>
                <button class="btn btn-primary prevBtn pull-left" type="button"><?php echo xlt('Previous') ?></button>
                <button class="btn btn-primary pull-right" type="button" id="profileNext"><?php echo xlt('Next') ?></button>
            </div>
        </form>
        <!-- Insurance Form -->
        <form id="insuranceForm" role="form" action="" method="post">
            <div class="text-center setup-content" id="step-3" style="display: none">
                <legend class='bg-primary text-white'><?php echo xlt('Insurance') ?></legend>
                <div class="jumbotron">
                    <div class="form-row">
                        <div class="col form-group">
                            <label for="provider"><?php echo xlt('Insurance Company') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="provider" id="inscompany" required placeholder="<?php echo xla('Enter Self if None'); ?>">
                            </div>
                        </div>
                        <div class="col form-group">
                            <label for="plan_name"><?php echo xlt('Plan Name') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="plan_name" required placeholder="<?php echo xla('required'); ?>">
                            </div>
                        </div>
                        <div class="col form-group">
                            <label for="policy_number"><?php echo xlt('Policy Number') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="policy_number" required placeholder="<?php echo xla('required'); ?>">
                            </div>
                        </div>
                      </div>
                      <div class="form-row">
                        <div class="col form-group">
                            <label for="group_number"><?php echo xlt('Group Number') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="group_number" required placeholder="<?php echo xla('required'); ?>">
                            </div>
                        </div>
                        <div class="col form-group">
                            <label for="date"><?php echo xlt('Policy Begin Date') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control datepicker" name="date" placeholder="<?php echo xla('Policy effective date'); ?>">
                            </div>
                        </div>
                        <div class="col form-group">
                            <label for="copay"><?php echo xlt('Co-Payment') ?></label>
                            <div class="controls inline-inputs">
                                <input type="number" class="form-control" name="copay" placeholder="<?php echo xla('Plan copay if known'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary prevBtn btn-sm pull-left" type="button"><?php echo xlt('Previous') ?></button>
                <button class="btn btn-primary nextBtn btn-sm pull-right" type="button"><?php echo xlt('Next') ?></button>
            </div>
        </form>
        <!-- End Insurance. Next what we've been striving towards..the end-->
        <div class="text-center setup-content" id="step-4" style="display: none">
            <legend class='bg-success text-white'><?php echo xlt('Register') ?></legend>
            <div class="jumbotron">
                <h4 class='bg-success'><?php echo xlt("All set. Click Send Request below to finish registration.") ?></h4>
                <hr />
                <p>
                    <?php echo xlt("An e-mail with your new account credentials will be sent to the e-mail address supplied earlier. You may still review or edit any part of your information by using the top step buttons to go to the appropriate panels. Note to be sure you have given your correct e-mail address. If after receiving credentials and you have trouble with access to the portal, please contact administration.") ?>
                </p>
            </div>
            <hr />
            <button class="btn btn-primary prevBtn float-left" type="button"><?php echo xlt('Previous') ?></button>
            <button class="btn btn-success float-right" type="button" id="submitPatient"><?php echo xlt('Send Request') ?></button>
        </div>
    </div>
</body>
</html>
