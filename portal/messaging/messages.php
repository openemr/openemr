<?php

/**
 * messages.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Messaging\SendSmsEvent;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $session->get('portal_username'));
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../../interface/globals.php");
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    if (empty($session->get('authUserID'))) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }

    define('IS_DASHBOARD', $session->get('authUser'));
    define('IS_PORTAL', false);
}
$srcdir = $globalsBag->getString('srcdir');
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("./../lib/portal_mail.inc.php");

if (!$globalsBag->getBoolean('portal_onsite_two_enable')) {
    echo xlt('Patient Portal is turned off');
    exit;
}

$docid = empty($_REQUEST['docid']) ? 0 : (int)$_REQUEST['docid'];
$orderid = empty($_REQUEST['orderid']) ? 0 : (int)$_REQUEST['orderid'];

$result = getMails(IS_DASHBOARD ?: IS_PORTAL, 'inbox', '', '');
$theresult = [];
foreach ($result as $iter) {
    $theresult[] = $iter;
}

$isSMS = !empty($globalsBag->get('oefax_enable_sms') ?? 0);
$isEmail = !empty($globalsBag->get('oe_enable_email') ?? 0);
$showSMS = $isSMS && IS_DASHBOARD;
$dashuser = [];
if (IS_DASHBOARD) {
    $dashuser = getUserIDInfo($session->get('authUserID'));
}

function getAuthPortalUsers()
{
    $resultpd = $resultusers = $resultpatients = [];

    if (IS_DASHBOARD) { // admin can mail anyone
        $authusers = sqlStatement("SELECT users.username as userid,
 CONCAT(users.fname,' ',users.lname) as username, 'user' as type FROM users WHERE active = 1 AND portal_user = 1");
        while ($row = sqlFetchArray($authusers)) {
            $resultusers[] = $row;
        }
        if (count($resultusers ?? []) === 0) {
            $resultusers[] = sqlQuery("SELECT users.username as userid,
 CONCAT(users.fname,' ',users.lname) as username, 'user' as type FROM users WHERE id = 1");
        }

        $authpatients = sqlStatement("SELECT pao.portal_username as userid,
 CONCAT(patient_data.fname,' ',patient_data.lname) as username,'p' as type,patient_data.pid as pid FROM patient_data
 LEFT JOIN patient_access_onsite pao ON pao.pid = patient_data.pid
 WHERE allow_patient_portal = 'YES' AND pao.portal_username IS NOT NULL");
        while ($row = sqlFetchArray($authpatients)) {
            $resultpatients[] = $row;
        }

        $resultpd = array_merge($resultusers, $resultpatients);
    } else { // patient gets only portal users
        $resultpd = [];
        $authusers = sqlStatement("SELECT users.username as userid, CONCAT(users.fname,' ',users.lname) as username FROM users WHERE active = 1 AND portal_user = 1");
        while ($row = sqlFetchArray($authusers)) {
            $resultpd[] = $row;
        }
        if (count($resultpd ?? []) === 0) {
            $resultpd[] = sqlQuery("SELECT users.username as userid, CONCAT(users.fname,' ',users.lname) as username FROM users WHERE id = 1");
        }
    }

    return $resultpd;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <?php
    if (IS_PORTAL) {
        Header::setupHeader(['no_main-theme', 'portal-theme', 'summernote', 'dompurify']);
    } else {
        Header::setupHeader(['summernote', 'dompurify']);
    }
    ?>
    <title><?php echo xlt("Secure Messaging"); ?></title>
    <meta name="description" content="Mail Application" />
</head>
<body class="body_top">
<?php
$webRoot = $globalsBag->getString('web_root');
$jsVersion = $globalsBag->getString('v_js_includes', '');
?>
    <script>
        window.OE_MESSAGES_CONFIG = {
            inboxItems: <?php echo json_encode($theresult); ?>,
            userproper: <?php echo !empty($session->get('ptName', null)) ? js_escape($session->get('ptName')) : js_escape($dashuser['fname'] . ' ' . $dashuser['lname']); ?>,
            isPortal: <?php echo json_encode(IS_PORTAL ?: ''); ?>,
            isDashboard: <?php echo json_encode(IS_DASHBOARD ?: ''); ?>,
            authrecips: <?php echo json_encode(getAuthPortalUsers()); ?>,
            csrf: <?php echo js_escape(CsrfUtils::collectCsrfToken($session, 'messages-portal')); ?>,
            strings: {
                forwardedRe: <?php echo xlj('Forwarded Portal Message Re: '); ?>,
                confirmOne: <?php echo xlj('Confirm to Archive Current Thread?'); ?>,
                confirmAll: <?php echo xlj('Confirm to Archive Selected Messages?'); ?>,
                sendingToSelf: <?php echo xlj('You are sending to yourself!'); ?>,
                conversationFrom: <?php echo xlj('Conversation from'); ?>,
                regarding: <?php echo xlj('regarding'); ?>,
                onPrep: <?php echo xlj('on'); ?>,
                archiveTitle: <?php echo xlj('Archive this message'); ?>,
                composeReplyTitle: <?php echo xlj('Compose Reply Message'); ?>,
                composeNewTitle: <?php echo xlj('Compose New Message'); ?>
            }
        };
    </script>
    <script src="<?php echo attr($webRoot); ?>/portal/messaging/js/messages.js?v=<?php echo attr($jsVersion); ?>"></script>
    <?php if ($showSMS) { ?>
    <script>
        <?php $globalsBag->getKernel()->getEventDispatcher()->dispatch(new SendSmsEvent($pid), SendSmsEvent::JAVASCRIPT_READY_SMS_POST); ?>
    </script>
    <?php } ?>
    <div class="container-fluid" id='main'>
        <div class='my-3'>
            <h2><i class='fa fa-envelope w-auto h-auto me-2'></i><?php echo xlt('Secure Messaging'); ?></h2>
        </div>
        <div class="row" id="loadingRow">
            <div class="col-12">
                <div class="alert alert-info"><h3><?php echo xlt("Loading..."); ?> <i class="wait fa fa-cog fa-spin ms-2"></i></h3></div>
            </div>
        </div>
        <div class="row d-none" id="contentRow">
            <div class="col-md-2 p-0 m-0 text-start border-end bg-light text-dark">
                <div class="sticky-top">
                    <ul class="nav nav-pills nav-stacked flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" id="navInbox" data-bs-toggle="pill" href="javascript:;"><span class="badge float-end" id="badgeInbox">0</span><?php echo xlt('Inbox'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navSent" data-bs-toggle="pill" href="javascript:;"><span class="badge float-end" id="badgeSent">0</span><?php echo xlt('Sent{{Mails}}'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navAll" data-bs-toggle="pill" href="javascript:;"><span class="badge float-end" id="badgeAll">0</span><?php echo xlt('All{{Mails}}'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navTrash" data-bs-toggle="pill" href="javascript:;"><span class="badge float-end" id="badgeTrash">0</span><?php echo xlt('Archive'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $globalsBag->getString('web_root') ?>/portal/patient/provider" data-show-dashboard><?php echo xlt('Exit Mail'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-10">
                <!--inbox toolbar-->
                <div class="row" id="inboxToolbar">
                    <div class="col-12 mb-2">
                        <button class="btn btn-primary" title="<?php echo xla("Compose Message"); ?>" data-mode="add" data-bs-toggle="modal" data-bs-target="#modalCompose">
                            <span class="fa fa-edit fa-lg"></span> <?php echo xlt("Compose Message"); ?>
                        </button>
                        <?php
                        if ($showSMS) {
                            $globalsBag->getKernel()->getEventDispatcher()->dispatch(new SendSmsEvent($session->get('pid', 0)), SendSmsEvent::ACTIONS_RENDER_SMS_POST);
                        }
                        ?>
                        <a class="btn btn-secondary" data-bs-toggle="tooltip" title="<?php echo xla("Refresh to see new messages"); ?>" id="refreshInbox" href="javascript:;" onclick='window.location.replace("./messages.php")'> <span class="fa fa-sync fa-lg"></span>
                        </a>
                        <div class="btn-group btn-group float-end">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"><?php echo xlt('Actions'); ?></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:;" id="btnReadAll"><?php echo xlt('Mark all as read'); ?></a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="" data-mode="add" data-bs-toggle="modal" data-bs-target="#modalCompose"><i class="fa fa-edit"></i> <?php echo xlt('Compose Message'); ?></a>
                                </li>
                                <li id="archiveMenuItem">
                                    <a class="dropdown-item" href="javascript:;" id="btnBatchDelete"><i class="fa fa-trash"></i> <?php echo xlt('Send Selected to Archive'); ?></a></li>
                                <li>
                                    <a href="javascript:;" onclick='window.location.replace("./messages.php")' class="dropdown-item"><i class="fa fa-sync"></i> <?php echo xlt('Refresh'); ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo $globalsBag->getString('web_root') ?>/portal/patient/provider" data-show-dashboard class="dropdown-item"><i class="fa fa-home"></i> <?php echo xlt('Return Home'); ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12"></div>
                </div>
                <!--/inbox toolbar-->
                <div class="inbox" id="inboxPanel">
                    <!--message list-->
                    <div class="table-responsive" id="messageListTable">
                        <table class="table table-striped table-bordered table-hover refresh-container pull-down">
                            <thead class="bg-info d-none"></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!--message detail-->
                    <div class="container-fluid" id="messageDetail" style="display:none;">
                        <div class="row">
                            <div class="w-100 ps-1 mb-1 bg-light text-dark" id="detailHeader">
                            </div>
                            <div class="table-responsive row ms-1">
                                <table class="table table-hover table-striped table-bordered refresh-container pull-down">
                                    <thead><?php echo xlt('Associated Messages in thread.');?></thead>
                                    <tbody id="chainTbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/inbox panel-->
                <!--paging-->
                <div class="float-end my-2" id="pagingControls">
                    <span class="text-muted" id="pagingInfo"></span>
                    <div class="btn-group" id="pagingButtons">
                        <button type="button" class="btn btn-secondary btn-lg" id="btnPrev"><i class="fa fa-chevron-left"></i></button>
                        <button type="button" class="btn btn-secondary btn-lg" id="btnNext"><i class="fa fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <!-- /.modal compose message -->
            <div class="modal fade" id="modalCompose">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><?php echo xlt('Compose Message'); ?></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body ">
                            <div class="col-12">
                                <label id="referLabel" style="display:none;"><?php echo xlt('Refer to Message') . ' # '; ?><span id="referMsgId"></span></label>
                                <div class="col-lg-12 m-1 p-1 bg-light text-dark" id="referMsg" style="display:none;"></div>

                                <form role="form" class="form-horizontal" name="fcompose" id="fcompose" method="post" action="./handle_note.php">
                                    <fieldset class="row">
                                        <div class="col-lg-6 input-group my-2">
                                            <label for="selSendto"><?php echo xlt('To{{Destination}}'); ?></label>
                                            <select class="form-control ms-2 to-select-forward" id="selForwardto" name="selForwardto" style="display:none;"></select>
                                            <select class="form-control ms-2 to-select-send" id="selSendto" name="selrecip"></select>
                                        </div>
                                        <div class="input-group col-lg-6 my-2">
                                            <label for="title"><?php echo xlt('Subject'); ?></label>
                                            <input type='text' list='listid' name='title' id='title' class="form-control ms-2" value="<?php echo xla('General'); ?>">
                                            <datalist id='listid'>
                                                <option label='<?php echo xlt('General'); ?>'
                                                    value='<?php echo xla('General'); ?>'></option>
                                                <option label='<?php echo xlt('Insurance'); ?>'
                                                    value='<?php echo xla('Insurance'); ?>'></option>
                                                <option label='<?php echo xlt('Prior Auth'); ?>'
                                                    value='<?php echo xla('Prior Auth'); ?>'></option>
                                                <option label='<?php echo xlt('Bill/Collect'); ?>'
                                                    value='<?php echo xla('Bill/Collect'); ?>'></option>
                                                <option label='<?php echo xlt('Referral'); ?>'
                                                    value='<?php echo xla('Referral'); ?>'></option>
                                                <option label='<?php echo xlt('Pharmacy'); ?>'
                                                    value='<?php echo xla('Pharmacy'); ?>'></option>
                                            </datalist>
                                        </div>
                                        <div class="col-12" id="inputBody"></div>
                                        <textarea class="col-12" id="finputBody" name="inputBody" rows="8" style="display:none;"></textarea>
                                    </fieldset>
                                    <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="" />
                                    <input type='hidden' name='noteid' id='hiddenNoteid' value='' />
                                    <input type='hidden' name='replyid' id='replyid' value='' />
                                    <input type='hidden' name='recipient_id' id='hiddenRecipientId' value='' />
                                    <input type='hidden' name='recipient_name' id='hiddenRecipientName' value='' />
                                    <input type='hidden' name='sender_id' id='hiddenSenderId' value='' />
                                    <input type='hidden' name='sender_name' id='hiddenSenderName' value='' />
                                    <input type='hidden' name='task' id='hiddenTask' value='add' />
                                    <input type='hidden' name='pid' id='hiddenPid' value='' />
                                    <div class='modal-footer'>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                                        <button type="submit" id="submit" name="submit"
                                            class="btn btn-primary float-end" value="messages.php"><?php echo xlt('Send'); ?> <i
                                                class="fa fa-arrow-circle-right fa-lg"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal compose message -->
        </div>
        <!--/row-->
    </div>
    <!--/container-->

</body>
</html>
