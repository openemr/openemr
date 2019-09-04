<?php
/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace Modules\oeFax\Controller;

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Core\Header;

$clientApp = AppDispatch::getApiService();
$logged_in = $clientApp->authenticate();
$service = $clientApp::getServiceType();
$title = $service == "1" ? 'RingCentral' : 'Twilio';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fax/SMS</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/dropzone/dist/dropzone.css">
    <?php
    Header::setupHeader(['opener', 'datetime-picker']);
    echo "<script>var pid=" . js_escape($pid) . ";var portalUrl=" . js_escape($clientApp->portalUrl) .
        ";var Service=" . js_escape($service) . ";</script>";
    ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dropzone/dist/dropzone.js"></script>
    <script>
        const queueMsg = '' + <?php echo xlj('Fax Queue. Drop files or Click here to Fax.') ?>;
        Dropzone.autoDiscover = false;
        $(function () {
            fileTypes = '';
            if (Service === '2') {
                fileTypes = 'application/pdf';
            }
            var faxQueue = new Dropzone("#faxQueue", {
                paramName: 'fax',
                url: 'faxProcessUploads',
                dictDefaultMessage: queueMsg,
                clickable: true,
                enqueueForUpload: true,
                maxFilesize: 25,
                acceptedFiles: fileTypes,
                uploadMultiple: false,
                addRemoveLinks: true,
                init: function (e) {
                    let ofile = '';
                    this.on("addedfile", function (file) {
                        console.log('new file added ', file);
                        ofile = file;
                    });
                    this.on("sending", function (file) {
                        console.log('upload started ', file);
                        $('.meter').show();
                    });
                    this.on("success", function (file, response) {
                        let thisFile = response;
                        console.log('upload success ', thisFile);
                        sendFax(thisFile, 'queue');
                    });
                    this.on("queuecomplete", function (progress) {
                        $('.meter').delay(999).slideUp(999);
                    });
                    this.on("removedfile", function (file) {
                        console.log(file);
                    });
                }
            });
        });
        $(function () {
            $('.datepicker').datetimepicker({
                <?php
                $datetimepicker_timepicker = false;
                $datetimepicker_showseconds = false;
                $datetimepicker_formatInput = false;
                require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
                ?>
            });
            var dateRange = new Date(new Date().setDate(new Date().getDate() - 7));
            $("#fromdate").val(dateRange.toJSON().slice(0, 10));
            $("#todate").val(new Date().toJSON().slice(0, 10));
            if (Service === '2') {
                $(".ringcentral").hide();
            }
            // populate
            retrieveMsgs();
        });

        var wait = '<span id="wait"><?php echo xlt("Fetching Remote") . '..';?><i class="fa fa-cog fa-spin fa-2x"></i></span>';

        var sendFax = function (filePath, from = '') {
            let btnClose = '<?php echo xlt("Cancel"); ?>';
            let title = '<?php echo xlt("Send To Contact"); ?>';
            let url = top.webroot_url + '/modules/oeFax/contact.php?isDocuments=false&isQueue=' +
                encodeURIComponent(from) + '&file=' + filePath; // do not encode filePath
            // leave dialog name param empty so send dialogs can cascade.
            dlgopen(url, '', 'modal-sm', 550, '', title, { // dialog restores session
                buttons: [
                    {text: btnClose, close: true, style: 'default btn-sm'}
                ]
            });
        };

        var docInfo = function (e, ppath) {
            top.restoreSession();
            let msg = <?php echo xlj('Your Account Portal') ?>;
            dlgopen(ppath, '_blank', 1240, 900, true, msg)
        };

        var popNotify = function (e, ppath) {
            top.restoreSession();
            let msg = <?php echo xlj('Are you sure you wish to send all scheduled reminders now.') ?>;
            if (e === 'live') {
                let yn = confirm(msg);
                if (!yn) return false;
            }
            let msg1 = <?php echo xlj('Appointment Reminder Alerts') ?>;
            dlgopen(ppath, '_blank', 1240, 900, true, msg1)
        };

        var doSetup = function (e) {
            top.restoreSession();
            e.preventDefault();
            let msg = <?php echo xlj('Credentials and SMS Notifications') ?>;
            dlgopen('', 'setup', 'modal-md', 700, '', msg, {
                buttons: [
                    {text: 'Cancel', close: true, style: 'default  btn-sm'}
                ],
                url: 'setup.php',
                sizeHeight: 'full'
            });
        };

        // For use with window cascade popup Twilio
        function viewDocument(e, docuri) {
            top.restoreSession();
            e.preventDefault();
            let width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ?
                document.documentElement.clientWidth : screen.width;
            let height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ?
                document.documentElement.clientHeight : screen.height;
            height = screen.height ? screen.height * 0.95 : height;

            cascwin(docuri, '', width / 2, (height), "resizable=1,scrollbars=1,location=0,toolbar=0");
        }

        function getDocument(e, docuri, docid, downFlag) {
            top.restoreSession();
            e.preventDefault();
            let wait = '<span id="wait"><?php echo xlt("Fetching Document") . '..';?><i class="fa fa-cog fa-spin fa-2x"></i></span>';
            let actionUrl = 'viewFax';
            $("#brand").append(wait);
            return $.post(actionUrl, {
                'docuri': docuri,
                'docid': docid,
                'pid': pid,
                'download': downFlag
            }).done(function (data) {
                $("#wait").remove();
                if (downFlag === 'true') {
                    location.href = "disposeDoc";
                    return false;
                }
                let btnClose = <?php echo xlj('Done'); ?>;
                let url = data;
                dlgopen(url, '', 'modal-mlg', 500, '', '', {
                    url: url,
                    type: 'iframe',
                    sizeHeight: 'full'
                });
            });
        }

        // Fax and SMS status
        function retrieveMsgs(e = '', req = '') {
            top.restoreSession();
            if (e) {
                e.preventDefault();
            }
            let actionUrl = 'getPending';
            let id = pid;
            let datefrom = $('#fromdate').val();
            let dateto = $('#todate').val();
            let data = [];

            $("#brand").append(wait);
            $("#rcvdetails tbody").empty();
            $("#sentdetails tbody").empty();
            $("#msgdetails tbody").empty();
            return $.post(actionUrl,
                {
                    'pid': pid,
                    'datefrom': datefrom,
                    'dateto': dateto
                }, function () {
                }, 'json').done(function (data) {
                if (data.error) {
                    $("#wait").remove();
                    var err = (data.error.search(/Exception/) !== -1 ? 1 : 0);
                    if (!err) {
                        err = (data.error.search(/Error:/) !== -1 ? 1 : 0);
                    }
                    if (err) {
                        alertMsg(data.error);
                    }
                    return false;
                }
                // populate our panels
                $("#rcvdetails tbody").empty().append(data[0]);
                $("#sentdetails tbody").empty().append(data[1]);
                $("#msgdetails tbody").empty().append(data[2]);
                // get call logs
                getLogs();
            }).fail(function (xhr, status, error) {
                alertMsg(<?php echo xlj('Not Authenticated. Restart from menu.') ?>, 5000)
            }).always(function () {
                $("#wait").remove();
            });
        }

        // Our Call Logs.
        function getLogs() {
            top.restoreSession();
            let actionUrl = 'getCallLogs';
            let id = pid;
            let datefrom = $('#fromdate').val();
            let dateto = $('#todate').val();

            $("#brand").append(wait);
            return $.post(actionUrl, {
                'pid': pid,
                'datefrom': datefrom,
                'dateto': dateto
            }).done(function (data) {
                var err = (data.search(/Exception/) !== -1 ? 1 : 0);
                if (!err) {
                    err = (data.search(/Error:/) !== -1 ? 1 : 0);
                }
                if (err) {
                    alertMsg(data);
                }
                $("#logdetails tbody").empty().append(data);

                // Get SMS appointments notifications
                getNotificationLog();
            }).always(function () {
                $("#wait").remove();
            });
        }

        function getNotificationLog() {
            top.restoreSession();
            let actionUrl = 'getNotificationLog';
            let id = pid;
            let datefrom = $('#fromdate').val() + " 00:00:00";
            let dateto = $('#todate').val() + " 23:59:59";

            $("#brand").append(wait);
            return $.post(actionUrl, {
                'pid': pid,
                'datefrom': datefrom,
                'dateto': dateto
            }).done(function (data) {
                var err = (data.search(/Exception/) !== -1 ? 1 : 0);
                if (!err) {
                    err = (data.search(/Error:/) !== -1 ? 1 : 0);
                }
                if (err) {
                    alertMsg(data);
                }
                $("#alertdetails tbody").empty().append(data);
            }).always(function () {
                $("#wait").remove();
            });
        }

        function getSelResource() {
            return $('#resource option:selected').val();
        }

    </script>
    <style>
    </style>
</head>
<body>
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#nav-header-collapse">
                    <span class="sr-only"><?php echo xlt('Toggle'); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">
                    <?php echo "oeFaxSMS ($title)"; ?>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="nav-header-collapse">
                <form class="navbar-form navbar-left form-inline" method="GET" role="search">
                    <div class="form-group">
                        <label for="formdate"><?php echo xlt('Activities From Date:') ?></label>
                        <input type="text" id="fromdate" name="fromdate" class="form-control input-sm datepicker" placeholder="YYYY-MM-DD" value=''>
                    </div>
                    <div class="form-group">
                        <label for="todate"><?php echo xlt('To Date:') ?></label>
                        <input type="text" id="todate" name="todate" class="form-control input-sm datepicker" placeholder="YYYY-MM-DD" value=''>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-default" onclick="retrieveMsgs(event,this)" title="<?php echo xla('Click to get current history.') ?>">
                            <i class="glyphicon glyphicon-refresh"></i></button>
                        <span id="brand"></span>
                    </div>
                </form>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown ">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <?php echo xlt('Actions'); ?>
                            <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class=""><a href="#" onclick="doSetup(event)"><?php echo xlt('Account Credentials'); ?></a></li>
                            <li class=""><a href="#" onclick="popNotify('', './rc_sms_notification.php?dryrun=1&site=<?php echo $_SESSION['site_id'] ?>')"><?php echo xlt('Test SMS Reminders'); ?></a></li>
                            <li class=""><a href="#" onclick="popNotify('live', './rc_sms_notification.php?site=<?php echo $_SESSION['site_id'] ?>')"><?php echo xlt('Send SMS Reminders'); ?></a></li>
                            <li class="ringcentral"><a href="#" onclick="docInfo(event, portalUrl)"><?php echo xlt('Portal Gateway'); ?></a></li>
                        </ul>
                    </li>
                    <li class="ringcentral">
                        <a class="ringcentral" href="#" onclick="docInfo(event, portalUrl)"><?php echo xlt('Visit Account Portal'); ?></a>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
    <div class="container-fluid main-container" style="margin-top:50px">
        <div class="col-md-10 col-md-offset-1 content">
            <h3><?php echo xlt("Activities") ?></h3>
            <div id="dashboard" class="panel">
                <!-- Nav tabs -->
                <ul id="tab-menu" class="nav nav-tabs" role="tablist">
                    <li role="presentation"><a href="#received" aria-controls="received" role="tab" data-toggle="tab"><?php echo xlt("Received") ?></a></li>
                    <li role="presentation"><a href="#sent" aria-controls="sent" role="tab" data-toggle="tab"><?php echo xlt("Sent") ?></a></li>
                    <li class="ringcentral" role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><?php echo xlt("SMS Log") ?></a></li>
                    <li class="ringcentral" role="presentation"><a href="#logs" aria-controls="logs" role="tab" data-toggle="tab"><?php echo xlt("Call Log") ?></a></li>
                    <li role="presentation">
                        <a href="#alertlogs" aria-controls="alertlogs" role="tab" data-toggle="tab"><?php echo xlt("Notifications Log") ?>&nbsp;&nbsp;
                            <span class="glyphicon glyphicon-refresh" onclick="getNotificationLog(event,this)"
                                title="<?php echo xla('Click to refresh using current date range. Refreshing just this tab.') ?>"></span></a>
                    </li>
                    <li class="active" role="presentation"><a href="#upLoad" aria-controls="logs" role="tab" data-toggle="tab"><?php echo xlt("Upload Fax") ?></a></li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="received">
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped" id="rcvdetails">
                                <thead>
                                <tr>
                                    <th><?php echo xlt("Date") ?></th>
                                    <th class="ringcentral"><?php echo xlt("Type") ?></th>
                                    <th><?php echo xlt("Pages") ?></th>
                                    <th><?php echo xlt("From") ?></th>
                                    <th><?php echo xlt("To") ?></th>
                                    <th><?php echo xlt("Result") ?></th>
                                    <th class="ringcentral"><?php echo xlt("Download") ?></th>
                                    <th><?php echo xlt("View") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo xlt("No Items Try Refresh") ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="sent">
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped" id="sentdetails">
                                <thead>
                                <tr>
                                    <th><?php echo xlt("Date") ?></th>
                                    <th class="ringcentral"><?php echo xlt("Type") ?></th>
                                    <th><?php echo xlt("Pages") ?></th>
                                    <th><?php echo xlt("From") ?></th>
                                    <th><?php echo xlt("To") ?></th>
                                    <th><?php echo xlt("Result") ?></th>
                                    <th class="ringcentral"><?php echo xlt("Download") ?></th>
                                    <th><?php echo xlt("View") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo xlt("No Items Try Refresh") ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="messages">
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped" id="msgdetails">
                                <thead>
                                <tr>
                                    <th><?php echo xlt("Date") ?></th>
                                    <th><?php echo xlt("Type") ?></th>
                                    <th><?php echo xlt("From") ?></th>
                                    <th><?php echo xlt("To") ?></th>
                                    <th><?php echo xlt("Result") ?></th>
                                    <th><?php echo xlt("Download") ?></th>
                                    <th><?php echo xlt("View") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo xlt("No Items Try Refresh") ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="logs">
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped" id="logdetails">
                                <thead>
                                <tr>
                                    <th><?php echo xlt("Date") ?></th>
                                    <th><?php echo xlt("Type") ?></th>
                                    <th><?php echo xlt("From") ?></th>
                                    <th><?php echo xlt("To") ?></th>
                                    <th><?php echo xlt("Action") ?></th>
                                    <th><?php echo xlt("Result") ?></th>
                                    <th><?php echo xlt("Id") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo xlt("No Items Try Refresh") ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="alertlogs">
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped" id="alertdetails">
                                <thead>
                                <tr>
                                    <th><?php echo xlt("Id") ?></th>
                                    <th><?php echo xlt("Date Sent") ?></th>
                                    <th><?php echo xlt("Appt Date Time") ?></th>
                                    <th><?php echo xlt("Patient") ?></th>
                                    <th><?php echo xlt("Message") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo xlt("No Items") ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in active" id="upLoad">
                        <div class="panel container-fluid">
                            <div id="fax-queue-container">
                                <div id="fax-queue">
                                    <form id="faxQueue" method="post" enctype="multipart/form-data" class="dropzone"></form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--</div>-->
                <footer class="pull-left footer">
                    <p class="col-md-12">
                    <hr class="divider">
                    </p>
                </footer>
            </div>

</body>
</html>
