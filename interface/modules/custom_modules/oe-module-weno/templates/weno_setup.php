<?php

/**
 * Config Module.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-24 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\WenoModule\Services\ModuleService;

$module_config = 1;

$vendors = [];
$vendors['weno_rx_enable'] = '';
$vendors['weno_rx_enable_test'] = '';
$vendors['weno_encryption_key'] = '';
$vendors['weno_admin_username'] = '';
$vendors['weno_admin_password'] = '';

$boot = new ModuleService();
if (($_POST['form_save'] ?? null) || ($_POST['form_save_top'] ?? null)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    unset($_POST['form_save'], $_POST['form_save_top'], $_POST['csrf_token_form']);
    $boot->saveVendorGlobals($_POST);
}

$vendors = $boot->getVendorGlobals();
?>
<!DOCTYPE HTML>
<html lang="eng">
<head>
    <title>><?php echo xlt("Enable Vendors") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (count($vendors ?? []) === 0) {
        $vendors = $boot->getVendorGlobals();
    }

    Header::setupHeader();
    ?>
    <script>
        $(function () {
            $(".collapse.show").each(function () {
                $(this).prev(".card-header").find(".fa").addClass("fa-minus").removeClass("fa-plus");
            });
            $(".collapse").on('show.bs.collapse', function () {
                $(this).prev(".card-header").find(".fa").removeClass("fa-plus").addClass("fa-minus");
            }).on('hide.bs.collapse', function () {
                $(this).prev(".card-header").find(".fa").removeClass("fa-minus").addClass("fa-plus");
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="form-group text-center m-2 p-2">
            <h2><?php echo xlt("Weno eRx Service Admin Setup"); ?></h2>
        </div>
        <form id="set_form" name="set_form" class="form" role="form" method="post" action="">
            <div id="set-weno">
                <div class="row form-group">
                    <div class="col-12">
                        <button type="submit" id="form_save_top" name="form_save_top" class="btn btn-primary btn-save" value="Save"><?php echo xlt("Save Setup"); ?></button>
                    </div>
                </div>
                <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
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
                        <label for="" class="col-sm-6"><?php echo xlt("Weno Encryption Key") ?></label>
                        <div class="col-sm-6" title="<?php echo xla("Encryption key issued by Weno eRx service on the Weno Developer Page.") ?>">
                            <input type="password" class="form-control persist" maxlength="255" name="weno_encryption_key" id="weno_encryption_key" value="<?php echo attr($vendors['weno_encryption_key']); ?>" />
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="weno_admin_username" class="col-sm-6"><?php echo xlt("Weno Admin Username") ?></label>
                        <div class="col-sm-6" title="<?php echo xla("This is required for Weno Pharmacy Directory Download in Background Services. Same as email for logging in into Weno") ?>">
                            <input type="text" class="form-control persist" maxlength="255" name="weno_admin_username" id="weno_admin_username" value="<?php echo attr($vendors['weno_admin_username']); ?>" />
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="weno_admin_password" class="col-sm-6"><?php echo xlt("Weno Admin Password") ?></label>
                        <div class="col-sm-6" title="<?php echo xla("Required Weno account password") ?>">
                            <input type="password" class="form-control persist" maxlength="255" name="weno_admin_password" id="weno_admin_password" value="<?php echo attr($vendors['weno_admin_password']); ?>" />
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-12">
                            <button type="submit" id="form_save" name="form_save" class="btn btn-primary btn-save float-right" value="Save"><?php echo xlt("Save Setup"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="">
            <div id="accordion" class="accordion">
                <div class="card mb-1">
                    <div class="card-header p-1 mb-1 bg-dark text-light collapsed collapsed" data-toggle="collapse" href="#collapseOne">
                        <h5 class="mb-0"><i class="fa fa-plus mr-2"></i><?php echo xlt("Chores After Setup"); ?></h5>
                    </div>
                    <div id="collapseOne" class="card-body collapse" data-parent="#accordion">
                        <p>Hold</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header p-1 bg-dark text-light collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                        <h5 class="mb-0"><i class="fa fa-plus mr-2"></i><?php echo xlt("Some Helpful Sites"); ?></h5>
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
