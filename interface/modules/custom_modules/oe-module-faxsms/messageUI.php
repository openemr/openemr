<?php

/**
 * Fax and SMS Module UI Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//header("Permissions-Policy: speaker=(self)");
//header("Feature-Policy: speaker 'self'");

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Messaging\SendNotificationEvent;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Enums\ServiceType;
use OpenEMR\Modules\FaxSMS\View\MessageUiProfile;

$assetBase = OEGlobalsBag::getInstance()->getWebRoot() . "/interface/modules/custom_modules/oe-module-faxsms/public";

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$faxsmsServiceType = $_REQUEST['type'] ?? null;
$serviceType = is_string($faxsmsServiceType) ? $faxsmsServiceType : '';
$clientApp = AppDispatch::getApiService($serviceType);
$service = $clientApp::getServiceType();
$serviceEnum = ServiceType::fromValue($service);
$title = $serviceEnum->getTranslatedDisplayName();
$tabTitle = $serviceType == "sms" ? xlt('SMS') : ($serviceType == "email" ? xlt('Email') : xlt('FAX'));
// Single source of truth for which tabs/columns this service+channel renders.
$uiTabs = MessageUiProfile::tabs($serviceEnum, $serviceType);
$site_id = $session->get('site_id');
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $tabTitle ?? ''; ?></title>
    <link rel="stylesheet" href="<?php echo OEGlobalsBag::getInstance()->getKernel()->getAssetsRelative(); ?>/dropzone/dist/dropzone.css">
    <script src="<?php echo OEGlobalsBag::getInstance()->getKernel()->getAssetsRelative(); ?>/utif2/UTIF.js"></script>
    <?php
    if (!$clientApp->verifyAcl()) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
    Header::setupHeader(['opener', 'datetime-picker', 'jspdf', 'jstiff']);
    echo "<script>let pid=" . js_escape($pid ?? 0) . ";let portalUrl=" . js_escape($clientApp->portalUrl ?? '') .
        ";let currentService=" . js_escape($service) . ";let serviceType=" . js_escape($serviceType) . ";let csrfToken=" . js_escape(CsrfUtils::collectCsrfToken($session, 'contact-form')) . "</script>";
    echo ServiceType::renderJsConstants();
    ?>
    <script type="text/javascript" src="<?php echo OEGlobalsBag::getInstance()->getKernel()->getAssetsRelative(); ?>/dropzone/dist/dropzone.js"></script>
    <script type="text/javascript" src="<?php echo $assetBase; ?>/js/messageUI.js"></script>

    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php
                $datetimepicker_timepicker = false;
                $datetimepicker_showseconds = false;
                $datetimepicker_formatInput = false;
                require(OEGlobalsBag::getInstance()->getSrcDir() . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
                ?>
            });
            let dateRange = new Date(new Date().setDate(new Date().getDate() - 1));
            $("#fromdate").val(dateRange.toJSON().slice(0, 10));
            $("#todate").val(new Date().toJSON().slice(0, 10));

            // Action anchors now use href="#" as a focusable/pointer affordance
            // only; their behavior lives in onclick. Cancel the navigation
            // default so a click never jumps the page. Tab links use
            // href="#<id>" (a fragment) and are not matched by a[href="#"].
            $(document).on('click', 'a[href="#"]', function (e) {
                e.preventDefault();
            });

            retrieveMsgs();
            $('#received').tab('show');
        });

        <?php
        $param = [
            'is_universal' => 1,
            'modal_size' => 'modal-mlg',
            'modal_height' => 775,
            'modal_size_height' => 'full',
            'type' => 'email'
        ];
        OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch(
            new SendNotificationEvent($pid ?? 0, $param),
            SendNotificationEvent::JAVASCRIPT_READY_NOTIFICATION_POST
        );
        ?>

    </script>

</head>
<body class="body_top">
    <!--<iframe src="library/rc_phone_widget.php" style="width: 100%; height: 100%;"></iframe>-->
    <div class="sticky-top">
        <nav class="navbar navbar-expand-xl navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#"><h4><?php echo $title; ?><i class="brand ml-1" id="brand-top"></i></h4></a>
                <button type="button" class="bg-primary navbar-toggler mr-auto" data-toggle="collapse" data-target="#nav-header-collapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="nav-header-collapse">
                    <form class="navbar-form navbar-left form-inline" method="GET" role="search">
                        <div class="form-group">
                            <label for="fromdate" class="mx-1 font-weight-bolder" for="formdate"><?php echo xlt('Activities From Date') ?>:</label>
                            <input type="text" id="fromdate" name="fromdate" class="form-control input-sm datepicker" placeholder="YYYY-MM-DD" value=''>
                        </div>
                        <div class="form-group">
                            <label class="mx-1 font-weight-bolder" for="todate"><?php echo xlt('To Date') ?>:</label>
                            <input type="text" id="todate" name="todate" class="form-control input-sm datepicker" placeholder="YYYY-MM-DD" value=''>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary btn-search" onclick="retrieveMsgs(event,this)" title="<?php echo xla('Click to get current history.') ?>"></button>
                        </div>
                    </form>
                    <?php if ($clientApp->verifyAcl('patients', 'appt')) { ?>
                        <div class="nav-item dropdown ml-auto">
                            <button class="btn btn-lg btn-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                <?php echo xlt('Account Actions'); ?><span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item" href="#" onclick="doSetup(event)"><?php echo xlt('Account Credentials'); ?></a>
                                <?php if ($serviceType == 'sms') { ?>
                                    <a class="dropdown-item" href="#" onclick="popNotify('', './library/rc_sms_notification.php?dryrun=1&type=sms&site=<?php echo attr($site_id) ?>')"><?php echo xlt('Test SMS Reminders'); ?></a>
                                    <a class="dropdown-item" href="#" onclick="popNotify('live', './library/rc_sms_notification.php?type=sms&site=<?php echo attr($site_id) ?>')"><?php echo xlt('Send SMS Reminders'); ?></a>
                                <?php } ?>
                                <?php if ($serviceType == 'email') { ?>
                                    <a class="dropdown-item" href="#" onclick="popNotify('', './library/rc_sms_notification.php?dryrun=1&type=email&site=<?php echo attr($site_id) ?>')"><?php echo xlt('Test Email Reminders'); ?></a>
                                    <a class="dropdown-item" href="#" onclick="popNotify('live', './library/rc_sms_notification.php?type=email&site=<?php echo attr($site_id) ?>')"><?php echo xlt('Send Email Reminders'); ?></a>
                                <?php } ?>
                                <?php if ($serviceEnum === ServiceType::ETHERFAX) { ?>
                                    <a class="dropdown-item" href="#" onclick="docInfo(event, portalUrl)"><?php echo xlt('Portal Gateway'); ?></a>
                                <?php } ?>
                            </div>
                            <?php if ($serviceEnum === ServiceType::ETHERFAX) { ?>
                                <button type="button" class="nav-item btn btn-secondary btn-transmit" onclick="docInfo(event, portalUrl)"><?php echo xlt('Account Portal'); ?>
                                </button>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div><!-- /.navbar-collapse -->
        </nav>
    </div>
    <div class="container-fluid main-container mt-3">
        <div class="row">
            <div class="col-md-10 offset-md-1 content">
                <h3><?php echo xlt("Activities") ?><i class="brand ml-1" id="brand"></i></h3>
                <div id="dashboard" class="card">
                    <!-- Nav tabs -->
                    <ul id="tab-menu" class="nav nav-pills mb-1" role="tablist">
                        <?php $faxsmsFirstTab = true; ?>
                        <?php foreach ($uiTabs as $faxsmsTab) {
                            $faxsmsId = is_string($faxsmsTab['id'] ?? null) ? $faxsmsTab['id'] : '';
                            $faxsmsLabel = is_string($faxsmsTab['navLabel'] ?? null) ? $faxsmsTab['navLabel'] : '';
                            $faxsmsRefresh = is_string($faxsmsTab['refresh'] ?? null) ? $faxsmsTab['refresh'] : '';
                            ?>
                            <li class="nav-item" role="tab">
                                <a class="nav-link<?php echo $faxsmsFirstTab ? ' active' : ''; ?>" href="#<?php echo attr($faxsmsId); ?>" aria-controls="<?php echo attr($faxsmsId); ?>" role="tab" data-toggle="tab"><?php echo $faxsmsLabel; // nosemgrep: echoed-request -- xlt()-escaped nav label from MessageUiProfile, not request input
                                ?>
                                    <?php if ($faxsmsRefresh !== '') { ?>
                                        <span class="fa fa-redo ml-1" onclick="<?php echo attr($faxsmsRefresh); ?>"
                                            title="<?php echo xla('Click to refresh using current date range. Refreshing just this tab.'); ?>"></span>
                                    <?php } ?>
                                </a>
                            </li>
                            <?php $faxsmsFirstTab = false; ?>
                        <?php } ?>
                        <?php if ($serviceType == 'email') {
                            $param = ['is_universal' => 1, 'type' => 'email'];
                            OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch(
                                new SendNotificationEvent($pid ?? 0, $param),
                                SendNotificationEvent::ACTIONS_RENDER_NOTIFICATION_POST
                            );
                        } ?>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <?php $faxsmsFirstPane = true; ?>
                        <?php foreach ($uiTabs as $faxsmsTab) {
                            $faxsmsId = is_string($faxsmsTab['id'] ?? null) ? $faxsmsTab['id'] : '';
                            $faxsmsType = is_string($faxsmsTab['type'] ?? null) ? $faxsmsTab['type'] : 'table';
                            $faxsmsTableId = is_string($faxsmsTab['tableId'] ?? null) ? $faxsmsTab['tableId'] : '';
                            $faxsmsCols = is_array($faxsmsTab['columns'] ?? null) ? $faxsmsTab['columns'] : [];
                            ?>
                            <div role="tabpanel" class="container-fluid tab-pane fade<?php echo $faxsmsFirstPane ? ' show active' : ''; ?>" id="<?php echo attr($faxsmsId); ?>">
                                <?php if ($faxsmsType === 'dropzone') { ?>
                                    <div class="panel container-fluid">
                                        <div id="fax-queue-container">
                                            <div id="fax-queue">
                                                <form id="faxQueue" method="post" enctype="multipart/form-data" class="dropzone"></form>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped" id="<?php echo attr($faxsmsTableId); ?>">
                                            <thead>
                                            <tr>
                                                <?php foreach ($faxsmsCols as $faxsmsCol) {
                                                    if (is_array($faxsmsCol) && isset($faxsmsCol['raw'])) {
                                                        echo is_string($faxsmsCol['raw']) ? $faxsmsCol['raw'] : ''; // nosemgrep: echoed-request -- controlled pre-built markup from MessageUiProfile
                                                    } else {
                                                        echo '<th>' . (is_string($faxsmsCol) ? $faxsmsCol : '') . '</th>'; // nosemgrep: echoed-request -- xlt()-escaped column label from MessageUiProfile
                                                    }
                                                } ?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?php echo xlt("No Items Try Refresh"); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php $faxsmsFirstPane = false; ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div><!-- /.navbar-container -->
</body>
</html>
