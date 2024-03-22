<?php

/**
 * Config Module.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Modules\WenoModule\Services\ModuleService;
use OpenEMR\Modules\WenoModule\Services\WenoValidate;

if (!AclMain::aclCheckCore('admin', 'super')) {
    // renders in MM iFrame
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Must be an Admin")]);
    exit;
}

$vendors = [];
$vendors['weno_rx_enable'] = '';
$vendors['weno_rx_enable_test'] = '';
$vendors['weno_encryption_key'] = '';
$vendors['weno_admin_username'] = '';
$vendors['weno_admin_password'] = '';
$vendors['weno_secondary_encryption_key'] = '';
$vendors['weno_secondary_admin_username'] = '';
$vendors['weno_secondary_admin_password'] = '';

$facilityUrl = $GLOBALS['web_root'] . "/interface/modules/custom_modules/oe-module-weno/templates/setup_facilities.php";
$usersUrl = $GLOBALS['web_root'] . "/interface/modules/custom_modules/oe-module-weno/templates/weno_users.php";
$saveAction = false;
$isValidKey = true;
$boot = new ModuleService();
$wenoValidate = new WenoValidate();
if (($_POST['form_save'] ?? null)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    unset($_POST['form_save'], $_POST['csrf_token_form']);
    $boot->saveVendorGlobals($_POST);
    $isValidKey = $wenoValidate->verifyEncryptionKey();
    $saveAction = true;
}
if (isset($_REQUEST['form_reset_key'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    unset($_GET['form_reset_key']);
    // if we are here then we need to reset the key.
    $newKey = $wenoValidate->requestEncryptionKeyReset();
    $wenoValidate->setNewEncryptionKey($newKey);
    // Redirect to the same page to refresh the page with the new key.
    $isValidKey = true;
}

$vendors = $boot->getVendorGlobals();
?>

<!DOCTYPE HTML>
<html lang="eng">
<head>
    <title><?php echo xlt("Weno Config") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (count($vendors ?? []) === 0) {
        $vendors = $boot->getVendorGlobals();
    }
    Header::setupHeader();
    ?>
    <script>
        $(function () {
            let isValidKey = <?php echo js_escape($isValidKey); ?>;
            let saveAction = <?php echo js_escape($saveAction); ?>;
            $(".collapse.show").each(function () {
                $(this).prev(".card-header").find(".fa").addClass("fa-minus").removeClass("fa-expand");
            });
            $(".collapse").on('show.bs.collapse', function () {
                $(this).prev(".card-header").find(".fa").removeClass("fa-expand").addClass("fa-minus");
            }).on('hide.bs.collapse', function () {
                $(this).prev(".card-header").find(".fa").removeClass("fa-minus").addClass("fa-expand");
            });

            // Auto save on changes. Will activate when we add a admin setting choice to turn on.
            /*const persistChange = document.querySelectorAll('.persist');
            persistChange.forEach(persist => {
                persist.addEventListener('change', () => {
                    $("#form_save_top").click();
                });
            });*/

            function togglePasswordVisibility(inputField) {
                inputField.type = inputField.type === "password" ? "text" : "password";
            }

            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(function (field) {
                const eyeIcon = document.createElement("span");
                eyeIcon.innerHTML = '<button type="button" class="btn btn-outline-primary"><i class="fa fa-eye"></i></button>';
                eyeIcon.addEventListener("click", function () {
                    togglePasswordVisibility(field);
                });
                field.parentNode.insertBefore(eyeIcon, field.nextSibling);
            });
            document.getElementById('app_refresh_top').scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});

            if (isValidKey === false) {
                $('#form_reset_key').removeClass('d-none');
                const warnMsg = "<?php echo xlt('The Encryption key did not pass validation. Clicking Reset button will reset your account encryption key.'); ?>";
                syncAlertMsg(warnMsg, 5000, 'danger', 'lg');
            } else if (isValidKey >= 100) {
                $('#form_reset_key').addClass('d-none');
                const warnMsg = "<?php echo xlt('Primary Admin Username and or Primary Admin Password is invalid. Try to reenter or correct in your Weno Dashboard.'); ?>";
                syncAlertMsg(warnMsg, 5000, 'danger', 'lg');
            } else {
                if (saveAction) {
                    const successMsg = "<?php echo xlt('Admin Settings Successfully Validated and Saved!'); ?>";
                    syncAlertMsg(successMsg, 3000, 'success');
                }
                $('#form_reset_key').addClass('d-none');
            }
        });
    </script>
</head>
<body>
    <div class="container-xl">
        <div class="form-group text-center m-2 p-2">
            <h2><?php echo xlt("Weno eRx Service Admin Setup"); ?></h2>
            <h6><small><?php echo xlt("Admin section must be validated. Other sections auto save."); ?></small></h6>
        </div>
        <div class="card mb-1">
            <div class="card-header p-1 mb-3 bg-light text-dark collapsed collapsed" role="button" data-toggle="collapse" href="#collapseOne">
                <h6 class="mb-0"><i class="fa fa-expand mr-2"></i><?php echo xlt("Setup Help"); ?></h6>
            </div>
            <div id="collapseOne" class="card-body collapse" data-parent="#accordion">
                <!-- Currently no plans to translate. -->
                <?php
                echo nl2br(text("There are three sections within the Weno eRx Service Admin Setup that allow the user to setup almost all the necessary settings to successfully start e-prescribing. The only other item is that each Weno prescriber credentials are set up in their User Settings.

*** The Weno Primary Admin Section.
- All values must be entered and validated.
- If validation fails because either email and/or password are invalid an alert will be shown stating such.
- If the encryption key is deemed invalid an alert will show and a new Encryption Reset button enabled. First try re-entering the key but if that doesn't work clicking the Reset button will create a new key. This change will also be reflected in the Admins main Weno account and no other actions are needed by the user. You may look on the key as an API token which may be a more familiar term to the reader.

*** The Map Weno User Id`s (Required)  Section.
- This section presents a table of all authorised users showing their default facility if assigned and an input field to enter their Weno user id Uxxxx. This value is important in order to form a relationship between Weno and the OpenEMR user for tracking prescriptions.
- All values are automatically saved for the user whenever the Weno Provider ID is entered or changed.
- As a convenience, an edit button is supplied to present a dialog containing the Users settings in edit mode. From here user may edit any setting such as assigning a default facility. This would be the same as accessing Users from top menu Admin->Users selected provider.

*** The Map Weno Facility Id`s (Required)  Section.
- This section is pretty self explanatory with perhaps noting this same data may be accessed from top menu Admin->Other->Weno Management as explained below.
- This section also auto saves for convenience."));
                ?>
            </div>
        </div>
        <form id="set_form" name="set_form" class="form" role="form" method="post" action="">
            <div id="set-weno">
                <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="row form-group">
                    <div class="col-12 text-center">
                        <h5>
                            <?php echo xlt("Weno Primary Admin Section") . ' <cite>(' . xlt('Required') . ')</cite>'; ?>
                            <hr class="text-dark bg-light" />
                        </h5>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row form-group">
                        <label for="weno_rx_enable" class="col-sm-6"><?php echo xlt("Enable Weno eRx Service"); ?></label>
                        <div class="col-sm-6" title="<?php echo xla("Contact https://online.wenoexchange.com to sign up for Weno Free eRx service.") ?>">
                            <input type="checkbox" class="checkbox persist" name="weno_rx_enable" id="weno_rx_enable" value="1" <?php echo $vendors['weno_rx_enable'] == '1' ? 'checked' : ''; ?>>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="weno_rx_enable_test" class="col-sm-6"><?php echo xlt("Enable Weno eRx Service Test Mode"); ?></label>
                        <div class="col-sm-6" title="<?php echo xla("Enable Weno eRx Service Test mode. This option will automatically include test pharmacies in your pharmacy download.") ?>">
                            <input type="checkbox" class="checkbox persist" name="weno_rx_enable_test" id="weno_rx_enable_test" value="1" <?php echo $vendors['weno_rx_enable_test'] == '1' ? 'checked' : ''; ?>>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="" class="col-sm-6"><?php echo xlt("Weno Primary Encryption Key") ?></label>
                        <div class="col-sm-6 input-group-append" title="<?php echo xla("Encryption key issued by Weno eRx service on the Weno Developer Page.") ?>">
                            <input type="password" class="form-control persist-admin" maxlength="255" name="weno_encryption_key" id="weno_encryption_key" value="<?php echo attr($vendors['weno_encryption_key']); ?>" />
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="weno_admin_username" class="col-sm-6"><?php echo xlt("Weno Primary Admin Username") ?></label>
                        <div class="col-sm-6" title="<?php echo xla("This is required for Weno Pharmacy Directory Download in Background Services. Same as email for logging in into Weno") ?>">
                            <input type="text" class="form-control persist-admin" maxlength="255" name="weno_admin_username" id="weno_admin_username" value="<?php echo attr($vendors['weno_admin_username']); ?>" />
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="weno_admin_password" class="col-sm-6"><?php echo xlt("Weno Primary Admin Password") ?></label>
                        <div class="col-sm-6 input-group-append" title="<?php echo xla("Required Weno account password") ?>">
                            <input type="password" class="form-control persist-admin" maxlength="255" name="weno_admin_password" id="weno_admin_password" value="<?php echo attr($vendors['weno_admin_password']); ?>" />
                        </div>
                        <div class="col form-group mt-1">
                            <button type="submit" id="form_reset_key" name="form_reset_key" class="d-none btn btn-success btn-sm btn-refresh m-1 float-right" value="Reset" title="<?php echo xla("The Encryption key did not pass validation. Clicking this button will reset your encryption key so you may continue."); ?>"><?php echo xlt("Encryption Reset"); ?></button>
                        </div>
                        <div class="col-12 form-group">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-refresh float-left" id="app_refresh_top" onclick="top.location.reload()"
                                title="<?php echo xla("Same as a browser refresh. Click to implement any new menus and Configuration items."); ?>"><?php echo xlt("Restart OpenEMR"); ?>
                            </button>
                            <button type="submit" id="form_save_top" name="form_save" class="btn btn-success btn-sm btn-save float-right" value="Save"><?php echo xlt("Validate and Save Admin"); ?></button>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-12 text-center">
                            <h5>
                                <hr class="text-dark bg-light" />
                                <?php echo xlt("Map Weno User Id's") . ' <cite>(' . xlt('Required') . ')</cite>'; ?>
                            </h5>
                        </div>
                        <iframe id="userFrame" src="<?php echo $usersUrl; ?>" class="w-100" style="border: none; min-height: 300px; max-height:600px;" height="250" title="<?php echo xla("Users") ?>"></iframe>
                    </div>
                    <div class="row form-group">
                        <div class="col-12 text-center">
                            <h5>
                                <hr class="text-dark bg-light" />
                                <?php echo xlt("Map Weno Facility Id's") . ' <cite>(' . xlt('Required') . ')</cite>'; ?>
                            </h5>
                        </div>
                        <iframe src="<?php echo $facilityUrl; ?>" class="w-100" style="border: none; min-height: 300px; max-height:600px;" height="250" title="<?php echo xla("Facilities") ?>"></iframe>
                    </div>
                    <div class="row form-group d-none">
                        <div class="col-12 text-center">
                            <h5>
                                <hr class="text-dark bg-light" />
                                <?php echo xlt("Weno Secondary Admin Section") . ' <cite>(' . xlt('Not Required') . ')</cite>'; ?>
                                <hr class="text-dark bg-light" />
                            </h5>
                        </div>
                    </div>
                    <div class="form-group d-none">
                        <div class="row form-group">
                            <label for="weno_secondary_encryption_key" class="col-sm-6"><?php echo xlt("Weno Secondary Encryption Key") ?></label>
                            <div class="col-sm-6 input-group-append" title="<?php echo xla("Backup Encryption key issued by Weno eRx service on the Weno Developer Page.") ?>">
                                <input type="password" class="form-control" maxlength="255" name="weno_secondary_encryption_key" id="weno_secondary_encryption_key" value="<?php echo attr($vendors['weno_secondary_encryption_key']); ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="weno_secondary_admin_username" class="col-sm-6"><?php echo xlt("Weno Secondary Admin Username") ?></label>
                            <div class="col-sm-6" title="<?php echo xla("This is required for Weno Pharmacy Directory Download in Background Services. Same as email for logging in into Weno") ?>">
                                <input type="text" class="form-control" maxlength="255" name="weno_secondary_admin_username" id="weno_secondary_admin_username" value="<?php echo attr($vendors['weno_secondary_admin_username']); ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="weno_secondary_admin_password" class="col-sm-6"><?php echo xlt("Weno Secondary Admin Password") ?></label>
                            <div class="col-sm-6 input-group-append" title="<?php echo xla("Required Weno account password") ?>">
                                <input type="password" class="form-control" maxlength="255" name="weno_secondary_admin_password" id="weno_secondary_admin_password" value="<?php echo attr($vendors['weno_secondary_admin_password']); ?>" />
                            </div>
                        </div>
                        <hr class="text-dark bg-light" />
                        <div class="row form-group">
                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-refresh float-left" id="app_refresh" onclick="top.location.reload()"
                                    title="<?php echo xla("Same as a browser refresh. Click to implement any new menus and Configuration items."); ?>"><?php echo xlt("Restart OpenEMR"); ?>
                                </button>
                                <button type="submit" id="form_save" name="form_save" class="btn btn-sm btn-success btn-save float-right" value="Save"><?php echo xlt("Validate and Save"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="mb-5">
            <div id="accordion" class="accordion">
                <div class="card mb-1">
                    <div class="card-header p-1 mb-1 bg-dark text-light collapsed collapsed" data-toggle="collapse" href="#collapseOne">
                        <h5 class="mb-0"><i class="fa fa-expand mr-2"></i><?php echo xlt("Setup Help"); ?></h5>
                    </div>
                    <div id="collapseOne" class="card-body collapse" data-parent="#accordion">
                        <!-- Currently no plans to translate. -->
                        <?php
                        echo nl2br(text("There are three sections within the Weno eRx Service Admin Setup that allow the user to setup almost all the necessary settings to successfully start e-prescribing. The only other item is that each Weno prescriber credentials are set up in their User Settings.

*** The Weno Primary Admin Section.
- All values must be entered and validated.
- If validation fails because either email and/or password are invalid an alert will be shown stating such.
- If the encryption key is deemed invalid an alert will show and a new Encryption Reset button enabled. First try re-entering the key but if that doesn't work clicking the Reset button will create a new key. This change will also be reflected in the Admins main Weno account and no other actions are needed by the user. You may look on the key as an API token which may be a more familiar term to the reader.

*** The Map Weno User Id`s (Required)  Section.
- This section presents a table of all authorised users showing their default facility if assigned and an input field to enter their Weno user id Uxxxx. This value is important in order to form a relationship between Weno and the OpenEMR user for tracking prescriptions.
- All values are automatically saved for the user whenever the Weno Provider ID is entered or changed.
- As a convenience, an edit button is supplied to present a dialog containing the Users settings in edit mode. From here user may edit any setting such as assigning a default facility. This would be the same as accessing Users from top menu Admin->Users selected provider.

*** The Map Weno Facility Id`s (Required)  Section.
- This section is pretty self explanatory with perhaps noting this same data may be accessed from top menu Admin->Other->Weno Management as explained below.
- This section also auto saves for convenience."));
                        ?>
                    </div>
                </div>
                <div id="help-links" class="card d-none">
                    <div class="card-header p-1 bg-dark text-light collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                        <h5 class="mb-0"><i class="fa fa-expand mr-2"></i><?php echo xlt("Some Helpful Sites"); ?></h5>
                    </div>
                    <div id="collapseTwo" class="card-body collapse" data-parent="#accordion">
                        <p>Hold</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
