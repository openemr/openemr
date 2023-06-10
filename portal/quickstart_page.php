<?php

/**
 * Patient Portal QuickStart
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");
$title = xlt("My Quickstarts");

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<!-- Language grabbed by PDF var that has the correct format !-->
<html lang="<?php echo $GLOBALS['pdf_language']; ?>">
<head>
    <title><?php echo($title); ?></title>
    <meta name="description" content="Patient Portal" />
    <meta name="author" content="Dashboard | sjpadgett@gmail.com" />

    <?php
    Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']);
    echo "<script>var cpid='" . attr($cpid ?? $pid) . "';var cuser='" . attr($cuser ?? 'portal-user') . "';var webRoot='" . $GLOBALS['web_root'] . "';</script>";
    ?>
    <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet">
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>

    <script>
        $(function () {
            let ele = parent.document.getElementById('topNav');
            if ($(parent.document.getElementById('topNav')).is('.collapse:not(.show)')) {
                ele.classList.toggle('collapse');
            }
        });
    </script>
</head>

<body class="pt-2">
    <div class="modal fade" id="formdialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog bg-light">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo xlt('About Portal Dashboard') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div>
                    <span><?php echo xlt('Please see forum or wiki'); ?>
                <a href="<?php echo attr('https://community.open-emr.org/'); ?>" target="_blank"><?php echo xlt("Visit Forum"); ?></a>
                    </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="okButton" data-dismiss="modal" class="btn btn-secondary"><?php echo xlt('Close...') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="container p-3">
        <div class="jumbotron jumbotron-fluid text-center p-1">
            <h3><?php echo xlt('My Quick Starts') ?><i class="fa fa-user text-danger ml-2" style="font-size: 3rem;"></i></h3>
            <p>
                <button class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#formdialog"><?php echo xlt('Tell me more') ?></button>
            </p>
        </div>
        <div class='jumbotron jumbotron-fluid p-4'>
            <div class="row">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fa fa-file-text mr-1"></i><?php echo xlt('Forms') ?></h4>
                        <a class="btn btn-success" href="<?php echo $GLOBALS['web_root']; ?>/portal/patient/onsitedocuments?pid=<?php echo attr_url($pid); ?>"><?php echo xlt('Manage Forms') ?></a>
                    </div>
                </div>
                <!--<div class="card overflow-auto">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fa fa-envelope mr-1"></i><?php /*echo xlt('Mail') */?></h4>
                        <a class="btn btn-success" href="<?php /*echo $GLOBALS['web_root']; */?>/portal/messaging/messages.php"><?php /*echo xlt('Secure Mail') */?></a>
                    </div>
                </div>-->
                <!--<div class="col">
                    <h4><i class="fa fa-message mr-1"></i><?php /*echo xlt('Chat') */ ?></h4>
                    <a class="btn btn-success" href="<?php /*echo $GLOBALS['web_root'];*/ ?>/portal/messaging/secure_chat.php"><?php /*echo xlt('Chat Messaging') */ ?></a>
                </div>-->
                <div class="card overflow-auto">
                    <div class="card-body">
                        <h4><i class="card-title fas fa-file-signature mr-1"></i><?php echo xlt('Signature') ?></h4>
                        <a data-type="admin-signature" class="btn btn-primary" href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal">
                            <span><?php echo xlt('Signature on File'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /container -->
