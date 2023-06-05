<?php

/**
 * secure_chat.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace PatientPortal;

// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../../src/Common/Session/SessionUtil.php");
\OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $_SESSION['pid']);
} else {
    \OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
    $admin = sqlQueryNoLog(
        "SELECT CONCAT(users.fname,' ',users.lname) as user_name FROM users WHERE id = ?",
        array($_SESSION['authUserID'])
    );
    define('ADMIN_USERNAME', $admin['user_name']);
    define('IS_DASHBOARD', $_SESSION['authUser']);
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
        if ($_GET['username'] != $_SESSION['ptName']) {
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
        if ($_POST['username'] != $_SESSION['ptName']) {
            $usernameManipulatedFlag = true;
        }
    }
}
if ($usernameManipulatedFlag) {
    http_response_code(401);
    die(xlt("Something went wrong"));
}

use OpenEMR\Core\Header;
use OpenEMR\PatientPortal\Chat\ChatController;

define('C_USER', IS_PORTAL ?: IS_DASHBOARD);
define('CHAT_HISTORY', '150');
define('CHAT_ONLINE_RANGE', '1');
define('ADMIN_USERNAME_PREFIX', 'adm_');

// Start application.
$msgApp = new ChatController();
?>
<!doctype html>
<html ng-app="MsgApp">
<head>
    <meta charset="utf-8" />
    <?php
    Header::setupHeader(['no_main-theme', 'ckeditor', 'angular', 'angular-sanitize', 'checklist-model']);
    ?>
    <title><?php echo xlt('Secure Chat'); ?></title>
    <meta name="author" content="Jerry Padgett sjpadgett{{at}} gmail {{dot}} com" />
</head>
<script>
    (function () {
        var MsgApp = angular.module('MsgApp', ['ngSanitize', "checklist-model"]);
        MsgApp.config(function ($compileProvider) {
                $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|file|ftp|blob):|data:image\//);
            }
        );
        MsgApp.directive('ngEnter', function () {
            return function (scope, element, attrs) {
                element.bind("keydown keypress", function (event) {
                    if (event.which === 13) {
                        scope.$apply(function () {
                            scope.$eval(attrs.ngEnter);
                        });
                        event.preventDefault();
                    }
                });
            };
        });
        MsgApp.directive('tooltip', function (e) {
            return {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    $(element).hover(function () {
                        $(element).tooltip('show');
                    }, function () {
                        $(element).tooltip('hide');
                    });
                }
            };
        });
        MsgApp.filter('unique', function () {
            return function (collection, keyname) {
                var output = [],
                    keys = [];
                angular.forEach(collection, function (item) {
                    var key = item[keyname];
                    if (keys.indexOf(key) === -1) {
                        keys.push(key);
                        output.push(item);
                    }
                });
                return output;
            };
        });

        MsgApp.controller('MsgAppCtrl', ['$scope', '$http', '$filter', function ($scope, $http, $filter) {
            $scope.urlListMessages = '?action=list'; // actions for restful
            $scope.urlSaveMessage = '?action=save';
            $scope.urlListOnlines = '?action=ping';
            $scope.urlGetAuthUsers = '?action=authusers';

            $scope.pidMessages = null;
            $scope.pidPingServer = null;

            $scope.beep = new Audio('beep.ogg'); // you've got mail!!!! really just a beep
            $scope.messages = [];
            $scope.online = null;
            $scope.lastMessageId = null;
            $scope.historyFromId = null;
            $scope.onlines = []; // all online users id and ip's
            $scope.user = <?php echo !empty($_SESSION['ptName']) ? js_escape($_SESSION['ptName']) : js_escape(ADMIN_USERNAME); ?>;// current user - dashboard user is from session authUserID
            $scope.userid = <?php echo IS_PORTAL ? js_escape($_SESSION['pid']) : js_escape($_SESSION['authUser']); ?>;
            $scope.isPortal = "<?php echo IS_PORTAL;?>";
            $scope.pusers = []; // selected recipients for chat
            $scope.chatusers = []; // authorize chat recipients for dashboard user
            $scope.noRecipError = <?php echo xlj("Please Select a Recipient for Message.") ?>;
            $scope.me = {
                username: $scope.user,
                message: null,
                sender_id: $scope.userid,
                recip_id: 0
            };
            $scope.checkAll = function () {
                $scope.pusers = [];
                $scope.pusers = $scope.chatusers.map(function (item) {
                    return item.recip_id;
                });
                $scope.getAuthUsers();
            };
            $scope.uncheckAll = function () {
                $scope.pusers = [];
                $scope.getAuthUsers();
            };
            $scope.makeCurrent = function (sel) {
                if (!sel.me) {
                    $scope.pusers.splice(0, $scope.pusers.length);
                    $scope.pusers.push(sel.sender_id);
                }
            };
            $scope.pageTitleNotificator = {
                vars: {
                    originalTitle: window.document.title,
                    interval: null,
                    status: 0
                },
                on: function (title, intervalSpeed) {
                    var self = this;
                    if (!self.vars.status) {
                        self.vars.interval = window.setInterval(function () {
                            window.document.title = (self.vars.originalTitle === window.document.title) ?
                                title : self.vars.originalTitle;
                        }, intervalSpeed || 500);
                        self.vars.status = 1;
                    }
                },
                off: function () {
                    window.clearInterval(this.vars.interval);
                    window.document.title = this.vars.originalTitle;
                    this.vars.status = 0;
                }
            };

            $scope.editor = '';
            $scope.editmsg = function () {
                $scope.editor = CKEDITOR.instances['messageContent'];
                if ($scope.editor) {
                    $scope.editor.destroy(true);
                }
                $scope.editor = CKEDITOR.replace('messageContent', {
                    toolbarGroups: [
                        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
                        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
                        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
                        { name: 'forms', groups: [ 'forms' ] },
                        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
                        { name: 'links', groups: [ 'links' ] },
                        { name: 'insert', groups: [ 'insert' ] },
                        { name: 'styles', groups: [ 'styles' ] },
                        { name: 'colors', groups: [ 'colors' ] },
                        { name: 'tools', groups: [ 'tools' ] },
                        { name: 'others', groups: [ 'others' ] },
                        { name: 'about', groups: [ 'about' ] }
                    ],
                    removeButtons: 'About,Table,Smiley,SpecialChar,PageBreak,Iframe,HorizontalRule,Anchor,Unlink,Link,NumberedList,BulletedList,Outdent,Indent,Blockquote,CreateDiv,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,Language,BidiRtl,BidiLtr,CopyFormatting,RemoveFormat,Superscript,Subscript,Strike,Underline,Italic,Bold,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,SelectAll,Scayt,Find,Replace,PasteFromWord,Templates,NewPage,ExportPdf,Maximize,ShowBlocks,Source,Save,Preview,Print,Cut,Copy,Paste,PasteText,TextColor,BGColor',
                    height: 250,
                    width: '100%',
                    resize_maxHeight: 650
                });
            };

            $scope.saveedit = function () {
                $scope.me.message = CKEDITOR.instances.messageContent.getData();
                $scope.saveMessage();
                $scope.editor.destroy(true);
            };

            $scope.saveMessage = function (form, callback) {
                $scope.me.recip_id = JSON.stringify(angular.copy($scope.pusers));
                var data = $.param($scope.me);
                if (!($scope.me.username && $scope.me.username.trim())) {
                    return $scope.openModal();
                }
                if (!($scope.me.message && $scope.me.message.trim() &&
                    $scope.me.username && $scope.me.username.trim())) {
                    return;
                }
                if ($scope.me.recip_id == "[]") {
                    alert($scope.noRecipError);
                    return;
                }
                $scope.me.message = '';
                return $http({
                    method: 'POST',
                    url: $scope.urlSaveMessage,
                    data: data,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).then(function successCallback(response) {
                    $scope.listMessages(true);
                }, function errorCallback(response) {
                });
            };

            $scope.replaceShortcodes = function (message) {
                var msg = '';
                msg = message.toString().replace(/(\[img])(.*)(\[\/img])/, "<img class='img-responsive' src='$2' />");
                msg = msg.toString().replace(/(\[url])(.*)(\[\/url])/, "<a href='$2'>$2</a>");
                msg = message.toString().replace("<img ", "<img class='img-responsive' ");
                return msg;
            };

            $scope.notifyLastMessage = function () {
                if (typeof window.Notification === 'undefined') {
                    return;
                }
                window.Notification.requestPermission(function (permission) {
                    var lastMessage = $scope.getLastMessage();
                    if (permission === 'granted' && lastMessage && lastMessage.username) {
                        var notify = new window.Notification('Message notification from ' + lastMessage.username + ' : ', {
                            body: 'New message' //lastMessage.message
                        });
                        notify.onclick = function () {
                            window.focus();
                        };
                        notify.onclose = function () {
                            $scope.pageTitleNotificator.off();
                        };
                        let timer = setInterval(function () {
                            notify && notify.close();
                            typeof timer !== 'undefined' && window.clearInterval(timer);
                        }, 100000);
                    }
                });
            };

            $scope.getLastMessage = function () {
                return $scope.messages[$scope.messages.length - 1];
            };

            $scope.listMessages = function (wasListingForMySubmission) {
                return $http.post($scope.urlListMessages, {}).then(function successCallback(response) {
                    $scope.messages = [];
                    angular.forEach(response.data, function (message) {
                        message.message = $scope.replaceShortcodes(message.message);
                        $scope.messages.push(message);
                    });

                    var lastMessage = $scope.getLastMessage();
                    var lastMessageId = lastMessage && lastMessage.id;

                    if ($scope.lastMessageId !== lastMessageId) {
                        $scope.onNewMessage(wasListingForMySubmission);
                    }
                    $scope.lastMessageId = lastMessageId;
                    if ($scope.pusers === '') { // refresh current in chat list.
                        angular.forEach($filter('unique')($scope.messages, 'sender_id'), function (m, k) {
                            var flg = false;
                            angular.forEach($scope.pusers, function (id) {
                                if (id === m.sender_id) {
                                    flg = true;
                                }
                            });
                            if (!flg) $scope.pusers.push(m.sender_id);
                        });
                    }
                    $scope.getOnlines();
                }, function errorCallback(response) {
                });
            };

            $scope.onNewMessage = function (wasListingForMySubmission) {
                if ($scope.lastMessageId && !wasListingForMySubmission) {
                    $scope.playAudio();
                    $scope.pageTitleNotificator.on('New message');
                    $scope.notifyLastMessage();
                }
                $scope.scrollDown();
                window.addEventListener('focus', function () {
                    $scope.pageTitleNotificator.off();
                });
            };

            $scope.getAuthUsers = function () {
                $scope.chatusers = [];
                return $http.post($scope.urlGetAuthUsers, {}).then(function successCallback(response) {
                    $scope.chatusers = response.data;
                }, function errorCallback(response) {
                });
            };

            $scope.pingServer = function (msgItem) {
                return $http.post($scope.urlListOnlines + '&username=' + $scope.user, {}).then(function successCallback(response) {
                    $scope.online = response.data;
                }, function errorCallback(response) {
                });
            };

            $scope.getOnlines = function () {
                return $http.post($scope.urlListOnlines + '&username=currentol', {}).then(function successCallback(response) {
                    $scope.onlines = response.data;
                }, function errorCallback(response) {
                });
            };

            $scope.init = function () {
                $scope.listMessages();
                $scope.pidMessages = window.setInterval($scope.listMessages, 3000);
                $scope.pidPingServer = window.setInterval($scope.pingServer, 5000);
                $scope.getAuthUsers();
                $("#popeditor").on("show.bs.modal", function () {
                    var height = $(window).height() - 200;
                    $(this).find(".modal-body").css("max-height", height);
                });
            };

            $scope.scrollDown = function () {
                var pidScroll;
                pidScroll = window.setInterval(function () {
                    $('.direct-chat-messages').scrollTop(window.Number.MAX_SAFE_INTEGER * 0.001);
                    window.clearInterval(pidScroll);
                }, 100);
            };

            $scope.clearHistory = function () {
                var lastMessage = $scope.getLastMessage();
                var lastMessageId = lastMessage && lastMessage.id;
                lastMessageId = (lastMessageId - 1 >= 2) ? lastMessageId - 1 : lastMessageId;
                lastMessageId && ($scope.historyFromId = lastMessageId);
            };

            $scope.openModal = function (e) {
                var mi = $('#popeditor').modal({backdrop: "static"});
                $scope.editmsg();
            };

            $scope.playAudio = function () {
                $scope.beep && $scope.beep.play();
            };

            $scope.renderMessageBody = function (html) {
                return jsAttr(html);
            };
            $scope.init();
        }]);
    })();
</script>
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

<body ng-controller="MsgAppCtrl">
    <div class="container-fluid">
        <!-- <h2 class="d-none">Secure Chat</h2> -->
        <div class="row">
            <div class="col-md-2 sidebar">
                <h5><span class="badge badge-primary"><?php echo xlt('Current Recipients'); ?></span></h5>
                <label ng-repeat="user in chatusers | unique : 'username'" ng-if="pusers.indexOf(user.recip_id) !== -1 && user.recip_id != me.sender_id">
                    <input type="checkbox" data-checklist-model="pusers" data-checklist-value="user.recip_id"> {{user.username}}
                </label>
                <h5><span class="badge badge-primary"><?php echo xlt('Available Recipients'); ?></span></h5>
                <span>
                    <button id="chkall" class="btn btn-sm btn-success" ng-show="!isPortal" ng-click="checkAll()" type="button"><?php echo xlt('All{{Recipients}}'); ?></button>
                    <button id="chknone" class="btn btn-sm btn-success" ng-show="!isPortal" ng-click="uncheckAll()" type="button"><?php echo xlt('None{{Recipients}}'); ?></button>
                </span>
                <label ng-repeat="user in chatusers | unique : 'username'" ng-show="!isPortal || (isPortal && user.dash)">
                    <input type="checkbox" data-checklist-model="pusers" data-checklist-value="user.recip_id"> {{user.username}}
                </label>
            </div>
            <div class="col-md-8 fixed-panel">
                <div class="card direct-chat direct-chat-warning">
                    <div class="card-heading bg-dark text-light py-2">
                        <div class="clearfix btn-group ml-2">
                            <a class='btn btn-primary' href='./../patient/provider' ng-show='!isPortal'><?php echo xlt('Home'); ?></a>
                            <a class="btn btn-secondary" href="" data-toggle="modal" data-target="#clear-history"><?php echo xlt('Clear history'); ?></a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="direct-chat-messages">
                            <div class="direct-chat-msg" ng-repeat="message in messages" ng-if="historyFromId < message.id" ng-class="{'right':!message.me}">
                                <div class="direct-chat-info clearfix">
                                    <span class="direct-chat-name" ng-class="{'float-left':message.me,'float-right':!message.me}">{{message.username }}</span>
                                    <span class="direct-chat-timestamp " ng-class="{'float-left':!message.me,'float-right':message.me}">{{message.date }}</span>
                                </div>
                                <i class="direct-chat-img fa fa-hand-o-left" style="cursor: pointer; font-size: 24px" ng-show="!message.me" ng-click="makeCurrent(message)" title="<?php echo xla('Click to activate and send to this recipient.'); ?>"></i>
                                <i class="direct-chat-img fa fa-hand-o-right" style="cursor: pointer; font-size:24px" ng-show="message.me" ng-click="makeCurrent(message)" title="<?php echo xla('Click to activate and send to this recipient.'); ?>"></i>

                                <div class="direct-chat-text right">
                                    <div class='px-0' title="<?php echo xla('Click to activate and send to this recipient.'); ?>" ng-click="makeCurrent(message)" ng-bind-html="renderMessageBody(message.message)"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer box-footer-hide">
                            <form id='msgfrm' ng-submit="saveMessage()">
                                <div class="input-group">
                                    <input type="text" placeholder="<?php echo xla('Type Message...'); ?>" id="msgedit" autofocus="autofocus" class="form-control" ng-model="me.message" ng-enter="saveMessage()">
                                    <span class="input-group-append">
                                        <button type="submit" class="btn btn-danger btn-flat"><?php echo xlt('Send'); ?></button>
                                        <button type="button" class="btn btn-success btn-flat" ng-click="openModal(event)"><?php echo xlt('Edit'); ?></button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 rtsidebar">
                <h5><span class="badge badge-primary"><?php echo xlt("Online Users"); ?> : {{ online.total || '0' }}</span>
                </h5>
                <label ng-repeat="ol in onlines | unique : 'username'">
                    <input type="checkbox" data-checklist-model="onlines" data-checklist-value="ol"> {{ol.username}}
                </label>
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
                        <button type="button" class="btn btn-success" data-dismiss="modal" ng-click="saveedit()"><?php echo xlt('Send It'); ?></button>
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
                        <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal" ng-click="clearHistory()"><?php echo xlt('Accept'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
