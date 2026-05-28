<?php

/**
 * secure_chat.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace PatientPortal;

use OpenEMR\Common\Session\SessionWrapperFactory;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $session->get('pid'));
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!$session->has('authUserID')) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
    $admin = sqlQueryNoLog(
        "SELECT CONCAT(users.fname,' ',users.lname) as user_name FROM users WHERE id = ?",
        [$session->get('authUserID')]
    );
    define('ADMIN_USERNAME', $admin['user_name']);
    define('IS_DASHBOARD', $session->get('authUser'));
    define('IS_PORTAL', false);
    $_SERVER['REMOTE_ADDR'] = 'admin::' . $_SERVER['REMOTE_ADDR'];
}

// Ensure that username GET or POST parameters are not manipulated
$usernameManipulatedFlag = false;
if (!empty($_GET['username']) && ($_GET['username'] != 'currentol')) {
    if (empty(IS_PORTAL)) {
        if ($_GET['username'] != ADMIN_USERNAME) {
            $usernameManipulatedFlag = true;
        }
    } else {
        if ($_GET['username'] != $session->get('ptName')) {
            $usernameManipulatedFlag = true;
        }
    }
}
if (!empty($_POST['username'])) {
    if (empty(IS_PORTAL)) {
        if ($_POST['username'] != ADMIN_USERNAME) {
            $usernameManipulatedFlag = true;
        }
    } else {
        if ($_POST['username'] != $session->get('ptName')) {
            $usernameManipulatedFlag = true;
        }
    }
}
if ($usernameManipulatedFlag) {
    http_response_code(401);
    die(xlt("Something went wrong"));
}

use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PatientPortal\Chat\ChatController;

define('C_USER', IS_PORTAL ?: IS_DASHBOARD);
define('CHAT_HISTORY', '150');
define('CHAT_ONLINE_RANGE', '1');
define('ADMIN_USERNAME_PREFIX', 'adm_');

// Start application.
$msgApp = new ChatController();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <?php
    if (IS_PORTAL) {
        Header::setupHeader(['no_main-theme', 'portal-theme', 'ckeditor', 'dompurify']);
    } else {
        Header::setupHeader(['ckeditor', 'dompurify']);
    }
    ?>

    <title><?php echo xlt('Secure Chat'); ?></title>
    <meta name="author" content="Jerry Padgett sjpadgett{{at}} gmail {{dot}} com" />
</head>
<?php
$webRoot = OEGlobalsBag::getInstance()->getString('web_root');
$jsVersion = OEGlobalsBag::getInstance()->getString('v_js_includes', '');
?>
<script>
    window.OE_SECURE_CHAT_CONFIG = {
        user: <?php echo !empty($session->get('ptName')) ? js_escape($session->get('ptName')) : js_escape(ADMIN_USERNAME); ?>,
        userid: <?php echo IS_PORTAL ? js_escape($session->get('pid')) : js_escape($session->get('authUser')); ?>,
        isPortal: <?php echo IS_PORTAL ? 'true' : 'false'; ?>,
        noRecipError: <?php echo xlj('Please Select a Recipient for Message.'); ?>,
        clickTitle: <?php echo xlj('Click to activate and send to this recipient.'); ?>
    };
</script>
<script src="<?php echo attr($webRoot); ?>/portal/messaging/js/secure_chat.js?v=<?php echo attr($jsVersion); ?>"></script>
<style>
  .direct-chat-text {
    border-radius: 5px;
    position: relative;
    padding: 5px 10px;
    background: #FBFBFB;
    border: 1px solid var(--gray);
    margin: 5px 0 0 50px;
    color: var(--dark);
  }

  .direct-chat-msg,
  .direct-chat-text {
    display: block;
    word-wrap: break-word;
  }

  .direct-chat-img {
    border-radius: 50%;
    float: left;
    width: 40px;
    height: 40px;
  }

  .direct-chat-info {
    display: block;
    margin-bottom: 2px;
    font-size: 12px;
  }

  .direct-chat-msg {
    margin-bottom: 5px;
  }

  .direct-chat-messages {
    -webkit-transform: translate(0, 0);
    -ms-transform: translate(0, 0);
    -o-transform: translate(0, 0);
    transform: translate(0, 0);
    padding: 5px;
    height: calc(100vh - 175px);
    overflow: auto;
    word-wrap: break-word;
  }

  .direct-chat-text:before {
    border-width: 6px;
    margin-top: -6px;
  }

  .direct-chat-text:after {
    border-width: 5px;
    margin-top: -5px;
  }

  .direct-chat-text:after,
  .direct-chat-text:before {
    position: absolute;
    right: 100%;
    top: 15px;
    border: solid rgba(0, 0, 0, 0);
    border-right-color: #D2D6DE;
    content: ' ';
    height: 0;
    width: 0;
    pointer-events: none;
  }

  .direct-chat-warning .right > .direct-chat-text {
    background: rgba(251, 255, 178, 0.34);
    border-color: var(--danger);
    color: var(--black);
  }

  .right .direct-chat-text {
    margin-right: 50px;
    margin-left: 0;
  }

  .direct-chat-warning .right > .direct-chat-text:after,
  .direct-chat-warning .right > .direct-chat-text:before {
    border-left-color: #F39C12;
  }

  .right .direct-chat-text:after,
  .right .direct-chat-text:before {
    right: auto;
    left: 100%;
    border-right-color: rgba(0, 0, 0, 0);
    border-left-color: #D2D6DE;
  }

  .right .direct-chat-img {
    float: right;
  }

  .direct-chat-name {
    font-weight: 600;
  }

  .box-footer form {
    margin-bottom: 10px;
  }

  input,
  .btn,
  .alert,
  .modal-content {
    border-radius: 0 !important;
  }

  .sidebar {
    background-color: #f8f8ff;
    max-height: 95vh;
    margin-top: 5px;
    margin-right: 0;
    padding-right: 5px;
    overflow: auto;
  }

  .rtsidebar {
    background-color: #f8f8ff;
    max-height: 95vh;
    margin-top: 5px;
    margin-right: 0;
    overflow: auto;
  }

  .fixed-panel {
    height: 100%;
    padding: 5px 5px 0 5px;
  }

  h5 {
    font-size: 16px !important;
  }

  label {
    display: block;
  }

  legend {
    font-size: 14px;
    margin-bottom: 2px;
    background: var(--white);
  }

  .modal.modal-wide .modal-dialog {
    width: 75%;
  }

  .modal-wide .modal-body {
    overflow-y: auto;
  }
</style>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h5><span class="badge badge-primary"><?php echo xlt('Current Recipients'); ?></span></h5>
                <div id="currentRecipients"></div>
                <h5><span class="badge badge-primary"><?php echo xlt('Available Recipients'); ?></span></h5>
                <span data-show-dashboard>
                    <button id="chkall" class="btn btn-sm btn-success" type="button"><?php echo xlt('All{{Recipients}}'); ?></button>
                    <button id="chknone" class="btn btn-sm btn-success" type="button"><?php echo xlt('None{{Recipients}}'); ?></button>
                </span>
                <div id="availableRecipients"></div>
            </div>
            <div class="col-md-8 fixed-panel">
                <div class="card direct-chat direct-chat-warning">
                    <div class="card-heading bg-dark text-light py-2">
                        <div class="clearfix btn-group ml-2">
                            <a class='btn btn-primary' href='./../patient/provider' data-show-dashboard><?php echo xlt('Home'); ?></a>
                            <a class="btn btn-secondary" href="" data-toggle="modal" data-target="#clear-history"><?php echo xlt('Clear history'); ?></a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="direct-chat-messages">
                        </div>
                        <div class="card-footer box-footer-hide">
                            <form id='msgfrm'>
                                <div class="input-group">
                                    <input type="text" placeholder="<?php echo xla('Type Message...'); ?>" id="msgedit" autofocus="autofocus" class="form-control">
                                    <span class="input-group-append">
                                        <button type="submit" class="btn btn-danger btn-flat"><?php echo xlt('Send'); ?></button>
                                        <button type="button" id="btnEditMsg" class="btn btn-success btn-flat"><?php echo xlt('Edit'); ?></button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 rtsidebar">
                <h5><span class="badge badge-primary"><?php echo xlt("Online Users"); ?> : <span id="onlineCount">0</span></span>
                </h5>
                <div id="onlineUsers"></div>
            </div>
        </div>
    </div>

    <div class="modal modal-wide fade" id="popeditor">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <h5 class='modal-title'><?php echo xlt('You may send Message with Image or Video'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only"><?php echo xlt('Close'); ?></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <textarea cols='80' rows='10' id='messageContent' name='content'></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal"><?php echo xlt('Dismiss'); ?></button>
                        <button type="button" id="btnSendEdit" class="btn btn-success" data-dismiss="modal"><?php echo xlt('Send It'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal" id="clear-history">
        <div class="modal-dialog">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only"><?php echo xlt('Close'); ?></span>
                        </button>
                        <h4 class="modal-title"><?php echo xlt('Chat history'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <label class="radio"><?php echo xlt('Are you sure you want to clear chats session history?'); ?></label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                        <button type="button" id="btnClearHistory" class="btn btn-sm btn-primary" data-dismiss="modal"><?php echo xlt('Accept'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
