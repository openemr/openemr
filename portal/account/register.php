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

// script is brought in as require_once in index.php when applicable

use OpenEMR\Core\Header;

if ($portalRegistrationAuthorization !== true) {
    (new SystemLogger())->debug("attempted to use register.php directly, so failed");
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    echo xlt("Not Authorized");
    header('HTTP/1.1 401 Unauthorized');
    die();
}

if (empty($GLOBALS['portal_onsite_two_register']) || empty($GLOBALS['google_recaptcha_site_key']) || empty($GLOBALS['google_recaptcha_secret_key'])) {
    (new SystemLogger())->debug("attempted to use register.php despite register feature being turned off, so failed");
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    echo xlt("Not Authorized");
    header('HTTP/1.1 401 Unauthorized');
    die();
}

unset($_SESSION['itsme']);
$_SESSION['authUser'] = 'portal-user';
$_SESSION['pid'] = true;
$_SESSION['register'] = true;
$_SESSION['register_silo_ajax'] = true;

$landingpage = "index.php?site=" . urlencode($_SESSION['site_id']);

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
                window.addEventListener('message', function (e) {
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
                        // disable to prevent already validated field changes.
                        profile.find('input#fname').prop("disabled", true);
                        profile.find('input#mname').prop("disabled", true);
                        profile.find('input#lname').prop("disabled", true);
                        profile.find('input#dob').prop("disabled", true);
                        profile.find('input#email').prop("disabled", true);
                        profile.find('input#emailDirect').prop("disabled", true);
                        profile.find('input#pid').prop('disabled', true);
                        profile.find('input#pid').val('');

                        profile.find('input[name=allowPatientPortal]').val(['YES']);
                        profile.find('input[name=hipaaAllowemail]').val(['YES']);
                        // need these for validation.
                        profile.find('select#providerid option:contains("Unassigned")').val('');
                        // must have a provider for many reasons. w/o save won't work.
                        //profile.find('select#providerid').attr('required', true);
                        profile.find('select#sex option:contains("Unassigned")').val('');
                        profile.find('select#sex').attr('required', true);
                    }
                    nextstepwiz.removeClass('disabled').trigger('click');
                }
            });

            $("#profileNext").click(function () {
                var profile = $("#profileFrame").contents();
                var curStep = $(this).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    nextstepwiz = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                    curInputs = profile.find("input[type='text'],input[type='email'],select"),
                    isValid = true;
                $(".form-group").removeClass("has-error");
                let flg = 0;
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
                if (isValid) {
                    provider = profile.find('select#providerid').val();
                    nextstepwiz.removeClass('disabled').trigger('click');
                }
            });

            $("#submitPatient").click(function () {
                let profile = $("#profileFrame").contents();
                if (checkRegistration()) {
                    // Use portals rest api. flag 1 is write to chart. flag 0 writes an audit record for review in dashboard.
                    //  (unclear what above means since no flags here and is being saved directly in chart)
                    // Save the new patient.
                    document.getElementById('profileFrame').contentWindow.postMessage({submitForm: true}, window.location.origin);
                    // lets force a second here to ensure above patient import is called to server prior than the insurance import
                    //  (this order is critical because the patient import sets up the pid to be used on the backend, so that no
                    //   pid adjustments can be made on the frontend)
                    delayPromise(1000).then(() => $("#insuranceForm").submit());
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
                let url = "account/account.php?action=new_insurance";
                $.ajax({
                    url: url,
                    type: 'post',
                    data: $("#insuranceForm").serialize()
                }).done(function (serverResponse) {
                        doCredentials(provider) // this is the end for session.
                        return false;
                    });
            });

            $('#inscompany').on('change', function () {
                if ($('#inscompany').val().toUpperCase() === 'SELF') {
                    $("#insuranceForm input").removeAttr("required");
                    let message = <?php echo xlj('You have chosen to be self insured or currently do not have insurance. Click next to continue registration.'); ?>;
                    //alert(message);
                }
            });

            $("#dob").on('blur', function () {
                let bday = $(this).val() ?? '';
                let age = Math.round(Math.abs((new Date().getTime() - new Date(bday).getTime())));
                age = Math.round(age / 1000 / 60 / 60 / 24);
                // need to be at least 30 days old otherwise likely an error.
                if (age < 30) {
                    let msg = <?php echo(xlj("Invalid Date format or value! Type date as YYYY-MM-DD or use the calendar.")); ?> ;
                    $(this).val('');
                    $(this).prop('placeholder', 'Invalid Date');
                    alert(msg);
                    return false;
                }
            });

        }); // ready end

        function delayPromise(time) {
            return new Promise(resolve => setTimeout(resolve, time));
        }

        function doCredentials(provider) {
            window.location.href = "account/account.php?action=do_signup&provider=" + encodeURIComponent(provider);
        }

        function checkRegistration() {
            var profile = $("#profileFrame").contents();
            var curStep = $("#step-2"),
                curStepBtn = curStep.attr("id"),
                nextstepwiz = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                curInputs = $("#profileFrame").contents().find("input[type='text'],input[type='email'],select"),
                isValid = true;
            $(".form-group").removeClass("has-error");
            let flg = 0;
            for (let i = 0; i < curInputs.length; i++) {
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
            return isValid;
        }

    </script>
</head>
<body class="mt-4 skin-blue">
    <div class="container-lg">
        <h1 class="text-center"><?php echo xlt('Account Registration'); ?></h1>
        <div class="stepwiz">
            <div class="stepwiz-row setup-panel">
                <div class="stepwiz-step">
                    <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                    <p><?php echo xlt('Verify Email') ?></p>
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
        <form id="startForm" role="form">
            <div class="text-center setup-content" id="step-1">
                <div class="jumbotron">
                    <input type="hidden" name="languageChoice" value="<?php echo attr($languageRegistration); ?>" />
                    <input type="hidden" id="fname" name="fname" value="<?php echo attr($fnameRegistration); ?>" />
                    <input type="hidden" id="mname" name="mname" value="<?php echo attr($mnameRegistration); ?>" />
                    <input type="hidden" id="lname" name="lname" value="<?php echo attr($lnameRegistration); ?>" />
                    <input type="hidden" id="dob" name="dob" value="<?php echo attr($dobRegistration); ?>" />
                    <input type="hidden" id="emailInput" name="email" value="<?php echo attr($emailRegistration); ?>" />
                    <div class="alert alert-success" role="alert"><?php echo xlt("Your email has been verified. Click Next."); ?></div>
                </div>
                <button class="btn btn-primary nextBtn pull-right" type="button"><?php echo xlt('Next') ?></button>
            </div>
        </form>
        <!-- Profile Form -->
        <form id="profileForm" role="form" action="account/account.php" method="post">
            <div class="text-center setup-content" id="step-2" style="display: none">
                <legend class="bg-primary text-white"><?php echo xlt('Profile') ?></legend>
                <div class="jumbotron">
                    <iframe class="embedded-content" src="patient/patientdata?pid=0&register=true" id="profileFrame" name="Profile Info"></iframe>
                </div>
                <button class="btn btn-primary pull-right" type="button" id="profileNext"><?php echo xlt('Next') ?></button>
            </div>
        </form>
        <!-- Insurance Form -->
        <form id="insuranceForm" role="form" action="" method="post">
            <div class="text-center setup-content" id="step-3" style="display: none">
                <legend class='bg-primary text-white'><?php echo xlt('Insurance') ?></legend>
                <div class="jumbotron">
                    <div class="form-row">
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="provider"><?php echo xlt('Insurance Company') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="provider" id="inscompany" required placeholder="<?php echo xla('Enter Self if None'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="plan_name"><?php echo xlt('Plan Name') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="plan_name" required placeholder="<?php echo xla('required'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="policy_number"><?php echo xlt('Policy Number') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="policy_number" required placeholder="<?php echo xla('required'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="group_number"><?php echo xlt('Group Number') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control" name="group_number" required placeholder="<?php echo xla('required'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="date"><?php echo xlt('Policy Begin Date') ?></label>
                            <div class="controls inline-inputs">
                                <input type="text" class="form-control datepicker" name="date" placeholder="<?php echo xla('Policy effective date'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
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
                    <?php echo xlt("An e-mail with your new account credentials will be sent to the e-mail address supplied earlier. You may still review or edit any part of your information by using the top step buttons to go to the appropriate panels. If after receiving credentials and you have trouble with access to the portal, please contact administration.") ?>
                </p>
            </div>
            <hr />
            <button class="btn btn-primary prevBtn float-left" type="button"><?php echo xlt('Previous') ?></button>
            <button class="btn btn-success float-right" type="button" id="submitPatient"><?php echo xlt('Send Request') ?></button>
        </div>
    </div>
</body>
</html>
