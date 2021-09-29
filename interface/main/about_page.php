<?php

/**
 * OpenEMR About Page
 *
 * This Displays an About page for OpenEMR Displaying Version Number, Support Phone Number
 * If it have been entered in Globals along with the Manual and On Line Support Links
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: jQuery UI Removal


require_once("../globals.php");

use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Core\Header;
use OpenEMR\Services\VersionService;

?>
<html>
<head>

    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("About");?> OpenEMR</title>
    <style>
        .donations-needed {
            margin-top: 25px;
            margin-bottom: 25px;
            color: #c9302c;
        }
        .donations-needed a, .donations-needed a:visited,
        .donations-needed a:active {
            color: #c9302c;
        }
        .donations-needed a.btn {
            color: #c9302c;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            animation: all 2s;
        }
        .donations-needed a.btn:hover {
            background-color: #c9302c;
            color: #fff;
        }
        .donations-needed .btn {
            border-radius: 8px;
            border: 2px solid #c9302c;
            color: #c9302c;
            background-color: transparent;
        }
    </style>

    <script>
        var registrationTranslations = <?php echo json_encode(array(
            'title' => xla('OpenEMR Product Registration'),
            'pleaseProvideValidEmail' => xla('Please provide a valid email address'),
            'success' => xla('Success'),
            'registeredSuccess' => xla('Your installation of OpenEMR has been registered'),
            'submit' => xla('Submit'),
            'noThanks' => xla('No Thanks'),
            'registeredEmail' => xla('Registered email'),
            'registeredId' => xla('Registered id'),
            'genericError' => xla('Error. Try again later'),
            'closeTooltip' => ''
        ));
            ?>;

        var registrationConstants = <?php echo json_encode(array(
            'webroot' => $GLOBALS['webroot']
        ))
            ?>;
    </script>

    <script src="<?php echo $webroot ?>/interface/product_registration/product_registration_service.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="<?php echo $webroot ?>/interface/product_registration/product_registration_controller.js?v=<?php echo $v_js_includes; ?>"></script>

    <script>
        $(function () {
            var productRegistrationController = new ProductRegistrationController();
            productRegistrationController.getProductRegistrationStatus(function(err, data) {
                if (err) { return; }

                if (data.statusAsString === 'UNREGISTERED') {
                    productRegistrationController.showProductRegistrationModal();
                } else if (data.statusAsString === 'REGISTERED') {
                    productRegistrationController.displayRegistrationInformationIfDivExists(data);
                }
            });
        });
    </script>
</head>
<?php
$versionService = new VersionService();
$version = $versionService->fetch();
?>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-4 offset-md-4 text-center">
                <h1><?php echo xlt("About");?>&nbsp;OpenEMR</h1>
                <h4><?php echo xlt('Version Number'); ?>: <?php echo "v" . text($openemr_version); ?></h4>
                <span class="text product-registration"><span class="email"></span></span><br />
                <?php if (!empty($GLOBALS['support_phone_number'])) { ?>
                    <span class="text"><?php  echo xlt('Support Phone Number'); ?>: <?php echo text($GLOBALS['support_phone_number']); ?></span><br />
                <?php } ?>
                <span class="text"><?php echo xlt('Unique Installation UUID'); ?>:<br /><?php echo text(UniqueInstallationUuid::getUniqueInstallationUuid()); ?></span><br />
                <a href="<?php echo "https://open-emr.org/wiki/index.php/OpenEMR_" . attr($version['v_major']) . "." . attr($version['v_minor']) . "." . attr($version['v_patch']) . "_Users_Guide"; ?>" rel="noopener" target="_blank" class="btn btn-block btn-secondary"><i class="fa fa-fw fa-book"></i>&nbsp;<?php echo xlt('User Manual'); ?></a>
                <?php if (!empty($GLOBALS['online_support_link'])) { ?>
                    <a href='<?php echo attr($GLOBALS["online_support_link"]); ?>' rel="noopener" target="_blank" class="btn btn-secondary btn-block"><i class="fa fa-fw fa-question-circle"></i>&nbsp;<?php echo xlt('Online Support'); ?></a>
                <?php } ?>
                <a href="../../acknowledge_license_cert.html" rel="noopener" target="_blank" class="btn btn-secondary btn-block"><i class="fa fa-fw fa-info-circle"></i><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a>
                <div class="donations-needed">
                    <span class="text"><?php echo xlt("Please consider sending in a donation to"); ?> OpenEMR:</span><br />
                    <a href="https://www.open-emr.org/donate/" rel="noopener" target="_blank" class="btn btn-lg btn-block"><i class="fa fa-2x fa-heart"></i><br /><?php echo xlt("DONATE NOW!"); ?></a>
                </div>
                <div class="review mb-5">
                    <a href="https://www.softwareadvice.com/medical/openemr-review/?step=1" title="<?php echo xla("Voice your opinion"); ?>" rel="noopener" target="_blank"><?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/review-logo.svg"); ?></a>
                </div>

            </div>
        </div>
    </div>

    <div class="product-registration-modal modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"></div>
                <div class="modal-body">
                    <p class="context"><?php echo xlt("Register your installation with OEMR to receive important notifications, such as security fixes and new release announcements."); ?></p>
                    <input placeholder="<?php echo xlt('email'); ?>" type="email" class="email w-100 text-body form-control" />
                    <p class="message font-italic"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary submit" ><?php echo xlt("Submit"); ?></button>
                    <button type="button" class="btn btn-secondary nothanks" ><?php echo xlt("No Thanks"); ?></button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
