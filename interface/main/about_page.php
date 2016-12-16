<?php
/**
 * OpenEMR About Page
 *
 * This Displays an About page for OpenEMR Displaying Version Number, Support Phone Number
 * If it have been entered in Globals along with the Manual and On Line Support Links
 *
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @link http://www.open-emr.org
 *
 * Please help the overall project by sending changes you make to the author and to the OpenEMR community.
 *
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
?>
<html>
<head>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-11-4/themes/ui-darkness/jquery-ui.min.css" />
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

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

    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-2-2-0/index.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']  ?>/jquery-ui-1-11-4/jquery-ui.min.js"></script>

    <script type="text/javascript">
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
            'closeTooltip' => xla('Close')
        ));
        ?>;

        var registrationConstants = <?php echo json_encode(array(
            'webroot' => $GLOBALS['webroot']
        ))
        ?>;
    </script>

    <script type="text/javascript" src="<?php echo $webroot ?>/interface/product_registration/product_registration_service.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="<?php echo $webroot ?>/interface/product_registration/product_registration_controller.js?v=<?php echo $v_js_includes; ?>"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            var productRegistrationController = new ProductRegistrationController();
            productRegistrationController.getProductRegistrationStatus(function(err, data) {
                if (err) { return; }

                if (data.status === 'UNREGISTERED') {
                    productRegistrationController.showProductRegistrationModal();
                } else if (data.status === 'REGISTERED') {
                    productRegistrationController.displayRegistrationInformationIfDivExists(data);
                }
            });
        });
    </script>
</head>

<body class="body_top">
    <div style="text-align: center;">
        <span class="title"><?php  echo xlt('About'); ?> OpenEMR</span><br><br>
        <span class="text"><?php  echo xlt('Version Number'); ?>: <?php echo "v".text($openemr_version) ?></span><br><br>
        <span class="text product-registration"><span class="email"></span> <span class="id"></span></span><br><br>
        <?php if (!empty($GLOBALS['support_phone_number'])) { ?>
            <span class="text"><?php  echo xlt('Support Phone Number'); ?>: <?php echo $GLOBALS['support_phone_number'] ?></span><br><br>
        <?php } ?>
    </div>
    <a href="<?php echo "http://open-emr.org/wiki/index.php/OpenEMR_".attr($v_major).".".attr($v_minor).".".attr($v_patch)."_Users_Guide"; ?>" target="_blank" class="css_button"><span><?php echo xlt('User Manual'); ?></span></a><br><br>
    <?php if (!empty($GLOBALS['online_support_link'])) { ?>
        <a href='<?php echo $GLOBALS["online_support_link"]; ?>' target="_blank" class="css_button"><span><?php echo xlt('Online Support'); ?></span></a><br><br>
    <?php } ?>
    <a href="../../acknowledge_license_cert.html" target="_blank" class="css_button"><span><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></span></a><br>
    <div class="donations-needed">
        <span class="text"><?php echo xlt("Please consider sending in a donation to"); ?> OpenEMR:</span><br>
        <a href="http://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=V6EVVTYYK264C" target="_blank" class="btn btn-lg btn-block"><i class="fa fa-2x fa-heart"></i><br/><?php echo xlt("DONATE NOW!"); ?></a>
    </div>

    <div class="product-registration-modal" style="display: none">
        <p class="context"><?php echo xlt("Register your installation with OEMR to receive important notifications, such as security fixes and new release announcements."); ?></p>
        <input placeholder="<?php echo xlt('email'); ?>" type="email" class="email" style="width: 100%; color: black" />
        <p class="message" style="font-style: italic"></p>
    </div>
</body>
</html>
