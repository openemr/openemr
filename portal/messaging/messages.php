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

// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $_SESSION['portal_username']);
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }

    define('IS_DASHBOARD', $_SESSION['authUser']);
    define('IS_PORTAL', false);
}

require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("./../lib/portal_mail.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}

$docid = empty($_REQUEST['docid']) ? 0 : (int)$_REQUEST['docid'];
$orderid = empty($_REQUEST['orderid']) ? 0 : (int)$_REQUEST['orderid'];

$result = getMails(IS_DASHBOARD ?: IS_PORTAL, 'inbox', '', '');
$theresult = array();
foreach ($result as $iter) {
    $theresult[] = $iter;
}

$dashuser = array();
if (IS_DASHBOARD) {
    $dashuser = getUserIDInfo($_SESSION['authUserID']);
}

function getAuthPortalUsers()
{
    $resultpd = $resultusers = $resultpatients = array();

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

        $authpatients = sqlStatement("SELECT (CONCAT(patient_data.fname, patient_data.id)) as userid,
 CONCAT(patient_data.fname,' ',patient_data.lname) as username,'p' as type,patient_data.pid as pid FROM patient_data WHERE allow_patient_portal = 'YES'");
        while ($row = sqlFetchArray($authpatients)) {
            $resultpatients[] = $row;
        }

        $resultpd = array_merge($resultusers, $resultpatients);
    } else { // patient gets only portal users
        $resultpd = array();
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
    <?php Header::setupHeader(['no_main-theme', 'patientportal-style', 'ckeditor', 'angular', 'angular-sanitize', 'checklist-model']); ?>
    <title><?php echo xlt("Secure Messaging"); ?></title>
    <meta name="description" content="Mail Application" />
</head>
<body class="skin-blue">
    <script>
        (function () {
            var app = angular.module("emrMessageApp", ['ngSanitize', "checklist-model"]);
            app.controller('inboxCtrl', ['$scope', '$filter', '$http', '$window', function ($scope, $filter, $http, $window) {
                $scope.date = new Date;
                $scope.sortingOrder = 'id';
                $scope.pageSizes = [5, 10, 20, 50, 100];
                $scope.reverse = false;
                $scope.filteredItems = [];
                $scope.groupedItems = [];
                $scope.itemsPerPage = 20;
                $scope.pagedItems = [];
                $scope.compose = [];
                $scope.selrecip = [];
                $scope.currentPage = 0;
                $scope.sentItems = [];
                $scope.allItems = [];
                $scope.deletedItems = [];
                $scope.inboxItems = [];
                $scope.inboxItems = <?php echo json_encode($theresult);?>;
                $scope.userproper = <?php echo !empty($_SESSION['ptName']) ? js_escape($_SESSION['ptName']) : js_escape($dashuser['fname'] . ' ' . $dashuser['lname']);?>;
                $scope.isPortal = "<?php echo IS_PORTAL;?>";
                $scope.isDashboard = "<?php echo IS_DASHBOARD ?: 0;?>";
                $scope.cUserId = $scope.isPortal ? $scope.isPortal : $scope.isDashboard;
                $scope.authrecips = <?php echo json_encode(getAuthPortalUsers());?>;
                $scope.compose.task = 'add';
                $scope.xLate = [];
                $scope.xLate.confirm = [];
                $scope.xLate.fwd = <?php echo xlj('Forwarded Portal Message Re: '); ?>;
                $scope.xLate.confirm.one = <?php echo xlj('Confirm to Delete Current Thread?'); ?>;
                $scope.xLate.confirm.all = <?php echo xlj('Confirm to Delete Selected?'); ?>;
                $scope.xLate.confirm.err = <?php echo xlj('You are sending to yourself!'); ?>;  // I think I got rid of this ability - look into..
                $scope.csrf = <?php echo js_escape(CsrfUtils::collectCsrfToken('messages-portal')); ?>;

                $scope.init = function () {
                    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
                    $scope.getSentMessages();
                    $scope.getAllMessages();
                    $scope.getDeletedMessages();
                    $scope.isInboxSelected();
                    $scope.search();
                    $scope.isInit = true;
                    $('#main').show();
                }

                const searchMatch = function (haystack, needle) {
                    if (!needle) {
                        return true;
                    }
                    return haystack.toLowerCase().indexOf(needle.toLowerCase()) !== -1;
                };

                // filter the items
                $scope.search = function () {
                    $scope.filteredItems = $filter('filter')($scope.items, function (item) {
                        for (var attr in item) {
                            if (searchMatch(item[attr], $scope.query))
                                return true;
                        }
                        return false;
                    });
                    $scope.currentPage = 0;
                    // now group by pages
                    $scope.groupToPages();
                };

                // calculate page in place
                $scope.groupToPages = function () {
                    $scope.selected = null;
                    $scope.pagedItems = [];
                    for (let i = 0; i < $scope.filteredItems.length; i++) {
                        if (i % $scope.itemsPerPage === 0) {
                            $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.filteredItems[i]];
                        } else {
                            $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.filteredItems[i]);
                        }
                    }
                };

                $scope.range = function (start, end) {
                    const ret = [];
                    if (!end) {
                        end = start;
                        start = 0;
                    }
                    for (var i = start; i < end; i++) {
                        ret.push(i);
                    }
                    return ret;
                };

                $scope.prevPage = function () {
                    if ($scope.currentPage > 0) {
                        $scope.currentPage--;
                    }
                    return false;
                };

                $scope.nextPage = function () {
                    if ($scope.currentPage < $scope.pagedItems.length - 1) {
                        $scope.currentPage++;
                    }
                    return false;
                };

                $scope.setPage = function () {
                    $scope.currentPage = this.n;
                };

                $scope.deleteItem = function (idx) {
                    if (!confirm($scope.xLate.confirm.one)) return false;
                    const itemToDelete = $scope.allItems[idx];
                    const idxInItems = $scope.items.indexOf(itemToDelete);
                    $scope.deleteMessage(itemToDelete.mail_chain); // Just this user's message
                    $scope.items.splice(idxInItems, 1);
                    $scope.search();
                    $scope.init()
                    return false;
                };

                $scope.batchDelete = function (i) {
                    if (!confirm($scope.xLate.confirm.all)) return false;
                    var itemToDelete = [];
                    angular.forEach(i, function (o, key) {
                        if (o.hasOwnProperty('deleted')) {
                            itemToDelete.push($scope.items[i.indexOf(o)].id);
                        }
                    })
                    $http.post('handle_note.php', $.param({'task': 'massdelete', 'notejson': JSON.stringify(itemToDelete), 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                        $window.location.reload();
                    }, function errorCallback(response) {
                        alert(response.data);
                    });
                    return false;
                };

                $scope.deleteMessage = function (id) {
                    $http.post('handle_note.php', $.param({'task': 'delete', 'noteid': id, 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                        return true;
                    }, function errorCallback(response) {
                        alert(response.data);
                    });

                };

                $scope.isMessageSelected = function () {
                    return typeof $scope.selected !== "undefined" && $scope.selected !== null;
                };

                $scope.isSentSelected = function () {
                    $scope.isSent = true;
                    $scope.isTrash = $scope.isAll = $scope.isInbox = false;
                    $scope.items = [];
                    $scope.items = $scope.sentItems;
                    $scope.search();
                    return true;
                }

                $scope.isTrashSelected = function () {
                    $scope.isTrash = true;
                    $scope.isSent = $scope.isAll = $scope.isInbox = false;
                    $scope.items = [];
                    $scope.items = $scope.deletedItems;
                    $scope.search();
                    return true;
                }

                $scope.isInboxSelected = function () {
                    $scope.isInbox = true;
                    $scope.isTrash = $scope.isAll = $scope.isSent = false;
                    $scope.items = $scope.inboxItems;
                    $scope.search();
                    return true;
                }

                $scope.isAllSelected = function () {
                    $scope.isAll = true;
                    $scope.isTrash = $scope.isSent = $scope.isInbox = false;
                    $scope.items = $scope.allItems;
                    $scope.search();
                    return true;
                }

                $scope.readMessage = function (idx) {
                    if ($scope.items[idx].message_status == 'New') { // mark mail read else ignore
                        $http.post('handle_note.php', $.param({'task': 'setread', 'noteid': $scope.items[idx].id, 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                            $scope.items[idx].message_status = 'Read';
                            $scope.selected.message_status = 'Read';
                        }, function errorCallback(response) {
                            alert(response.data);
                        });
                    }
                    idx = $filter('getById')($scope.allItems, this.item.id);
                    $scope.isAll = true;
                    $scope.isTrash = $scope.isSent = $scope.isInbox = false;
                    $scope.items = $scope.allItems;
                    $scope.selected = $scope.items[idx];
                };

                $scope.selMessage = function (idx) {
                    $scope.selected = $scope.allItems[idx];

                };

                $scope.readAll = function () {
                    for (var i in $scope.items) {
                        $scope.items[i].message_status = 'Read';
                    }
                };

                $scope.closeMessage = function () {
                    $scope.selected = null;
                };

                $scope.renderMessageBody = function (html) {
                    return html;
                };

                $scope.htmlToText = function (html) {
                    const hold = document.createElement('DIV');
                    hold.innerHTML = html;
                    return jsText(hold.textContent || hold.innerText || '');
                };

                $scope.getInbox = function () {
                    $http.post('handle_note.php', $.param({'task': 'getinbox', 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                        if (response.data) {
                            $scope.inboxItems = angular.copy(response.data);
                        } else alert(response.data);
                    }, function errorCallback(response) {
                        alert(response.data);
                    });
                };

                $scope.getAllMessages = function () {
                    $http.post('handle_note.php', $.param({'task': 'getall', 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                        if (response.data) {
                            $scope.allItems = angular.copy(response.data);
                        } else alert(response.data);
                    }, function errorCallback(response) {
                        alert(response.data);
                    });
                };

                $scope.getDeletedMessages = function () {
                    $http.post('handle_note.php', $.param({'task': 'getdeleted', 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                        if (response.data) {
                            $scope.deletedItems = [];
                            $scope.deletedItems = angular.copy(response.data);
                        } else alert(response.data);
                    }, function errorCallback(response) {
                        alert(response.data);
                    });
                };

                $scope.getSentMessages = function () {
                    $http.post('handle_note.php', $.param({'task': 'getsent', 'csrf_token_form': $scope.csrf})).then(function successCallback(response) {
                        $scope.sentItems = [];
                        $scope.sentItems = angular.copy(response.data);
                    }, function errorCallback(response) {
                        alert(response.data);
                    });
                };

                $scope.submitForm = function (compose) {
                    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
                    // re-enable title for submit
                    $("#title").prop("disabled", false);
                    $("#selSendto").prop("disabled", false);

                    compose.csrf_token_form = $scope.csrf;
                    compose.sender_id = $scope.cUserId;
                    compose.sender_name = $scope.userproper;
                    if ($scope.selrecip == $scope.cUserId) {
                        if (!confirm($scope.xLate.confirm.err))
                            return false;
                    }
                    if (compose.task == 'add') {
                        compose.recipient_name = $("#selSendto option:selected").text();
                    }
                    if (compose.task == 'forward') { // Just overwrite default reply but send to pnotes.
                        compose.sender_id = $("#selForwardto option:selected").val();
                        compose.sender_name = $("#selForwardto option:selected").text();
                        compose.selrecip = compose.recipient_id;
                    } else {
                        compose.inputBody = CKEDITOR.instances.inputBody.getData();
                    }
                    return true; // okay to submit
                }

                $('#modalCompose').on('hidden.bs.modal', function (e) {
                    window.location.reload();
                });

                $('#modalCompose').on('show.bs.modal', function (e) {
                    // Sets up the compose modal before we show it
                    $scope.compose = [];
                    if ($scope.editor) {
                        $scope.editor.destroy(true);
                    }
                    var mode = $(e.relatedTarget).attr('data-mode');
                    $scope.compose.task = mode;
                    if (mode == 'forward') {
                        $('#modalCompose .modal-header .modal-title').html("Forward Message");
                        $scope.compose.task = mode;
                        var recipId = $(e.relatedTarget).attr('data-whoto');
                        var title = $(e.relatedTarget).attr('data-mtitle');
                        var uname = $(e.relatedTarget).attr('data-username');
                        $(e.currentTarget).find('select[id="selSendto"]').prop("disabled", false);
                        $(e.currentTarget).find('input[name="title"]').prop("disabled", false);
                        $scope.compose.title = title;
                        $scope.compose.selrecip = recipId;
                        $scope.compose.selrecip.username = uname;
                        $scope.compose.recipient_name = uname;
                        $scope.compose.recipient_id = recipId;
                        angular.forEach($scope.authrecips, function (o, key) {// Need the pid of patient for pnotes.
                            if (o.userid == recipId) {
                                $scope.compose.pid = o.pid;
                            }
                        })
                        const fmsg = '\n\n\n> ' + $scope.xLate.fwd + title + ' by ' + uname + '\n> ' + $("#referMsg").text();
                        $("textarea#finputBody").text(fmsg)
                        $scope.compose.noteid = $(e.relatedTarget).attr('data-noteid');
                    } else if (mode == 'reply') {
                        $scope.editor = CKEDITOR.instances['inputBody'];
                        if ($scope.editor) {
                            $scope.editor.destroy(true);
                        }
                        $scope.editor = CKEDITOR.replace('inputBody', {
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
                        $('#modalCompose .modal-header .modal-title').html(<?php xlt("Compose Reply Message"); ?>)
                        $scope.compose.task = mode;
                        //get data attributes of the clicked element (selected recipient) for replies only
                        var chain = $(e.relatedTarget).attr('data-mailchain');
                        if (chain == '0') {
                            chain = $(e.relatedTarget).attr('data-noteid');
                        }
                        let recipId = $(e.relatedTarget).attr('data-whoto');
                        let title = $(e.relatedTarget).attr('data-mtitle');
                        let uname = $(e.relatedTarget).attr('data-username');
                        $(e.currentTarget).find('select[id="selSendto"]').val(recipId)
                        $(e.currentTarget).find('input[name="title"]').val(title);
                        // Set the modal var's
                        $scope.compose.title = title;
                        $scope.compose.selrecip = recipId;
                        $scope.compose.selrecip.username = uname;
                        $scope.compose.recipient_name = uname;
                        $scope.compose.recipient_id = recipId;
                        $scope.compose.noteid = chain;
                    } else {
                        $scope.editor = CKEDITOR.instances['inputBody'];
                        if ($scope.editor) {
                            $scope.editor.destroy(true);
                        }
                        $scope.editor = CKEDITOR.replace('inputBody', {
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

                        $('#modalCompose .modal-header .modal-title').html(<?php xlt("Compose New Message"); ?>);
                        $scope.compose.task = 'add';
                        $(e.currentTarget).find('select[id="selSendto"]').prop("disabled", false);
                        $(e.currentTarget).find('input[name="title"]').prop("disabled", false);
                    }
                    if ($scope.compose.task != 'reply') {
                        $scope.$apply();
                    }
                }); // on modal
                // initialize application
                if (!$scope.isInit) {
                    $scope.init();
                }
            }])  /* end inbox functions */
            .filter('Chained', function () {
                return function (input, id) {
                    var output = [];
                    if (isNaN(id)) {
                        output = input;
                    } else {
                        angular.forEach(input, function (item) {
                            if (item.mail_chain == id) {
                                output.push(item)
                            }
                        });
                    }
                    return output;
                }
            }).filter('getById', function () {
                return function (input, id) {
                    var i = 0, len = input.length;
                    for (; i < len; i++) {
                        if (+input[i].id == +id) {
                            return i;
                        }
                    }
                    return null;
                }
            }).controller('messageCtrl', ['$scope', function ($scope) {
                $scope.message = function (idx) {
                    return items(idx);
                };

            }]);   // end messageCtrl

        })(); // application end
    </script>
    <ng ng-app="emrMessageApp">
        <div class="container-fluid" id='main' style="display: none">
            <div class='my-3'>
                <h2><i class='fa fa-envelope w-auto h-auto mr-2'></i><?php echo xlt('Secure Messaging'); ?></h2>
            </div>
            <div class="row" ng-controller="inboxCtrl">
                <div class="col-md-2 p-0 m-0 text-left border-right bg-secondary">
                    <div class="sticky-top">
                        <ul class="nav nav-pills nav-stacked flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="pill" href="javascript:;" ng-click="isInboxSelected()"><span class="badge float-right">{{inboxItems.length}}</span><?php echo xlt('Inbox'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="javascript:;" ng-click="isSentSelected()"><span class="badge float-right">{{sentItems.length}}</span><?php echo xlt('Sent{{Mails}}'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="javascript:;" ng-click="isAllSelected()"><span class="badge float-right">{{allItems.length}}</span><?php echo xlt('All{{Mails}}'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="javascript:;" ng-click="isTrashSelected()"><span class="badge float-right">{{deletedItems.length}}</span><?php echo xlt('Archive'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $GLOBALS['web_root'] ?>/portal/patient/provider" ng-show="!isPortal"><?php echo xlt('Exit Mail'); ?></a>
                            </li>
                            <!--<li class="nav-item">
                                <a class="nav-link" href="javascript:;" onclick='window.location.replace("<?php /*echo $GLOBALS['web_root'] */ ?>/portal/home.php")' ng-show="isPortal"><?php /*echo xlt('Exit'); */ ?></a>
                            </li>-->
                        </ul>
                    </div>
                </div>
                <div class="col-md-10">
                    <!--inbox toolbar-->
                    <div class="row" ng-show="!isMessageSelected()">
                        <div class="col-12 mb-2">
                            <a class="btn btn-secondary" data-toggle="tooltip" title="Refresh" id="refreshInbox" href="javascript:;" onclick='window.location.replace("./messages.php")'> <span class="fa fa-sync fa-lg"></span>
                            </a>
                            <button class="btn btn-secondary" title="<?php echo xla("New Note"); ?>" data-mode="add" data-toggle="modal" data-target="#modalCompose">
                                <span class="fa fa-edit fa-lg"></span>
                            </button>
                            <div class="btn-group btn-group float-right">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><?php echo xlt('Actions'); ?></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a class="dropdown-item" href="javascript:;" ng-click="readAll()"><?php echo xlt('Mark all as read'); ?></a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="" data-mode="add" data-toggle="modal" data-target="#modalCompose"><i class="fa fa-edit"></i><?php echo xlt('Compose new'); ?></a>
                                    </li>
                                    <li ng-show='!isTrash'>
                                        <a class="dropdown-item" href="javascript:;" ng-click="batchDelete(items)"><i class="fa fa-trash"></i><?php echo xlt('Send Selected to Archive'); ?></a></li>
                                    <li>
                                        <a href="javascript:;" onclick='window.location.replace("./messages.php")' ng-show="isPortal" class="dropdown-item"><i class="fa fa-sync"></i><?php echo xlt('Refresh'); ?></a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $GLOBALS['web_root'] ?>/portal/patient/provider" ng-show="!isPortal" class="dropdown-item"><i class="fa fa-home"></i><?php echo xlt('Return Home'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!--/col-->
                        <div class="col-12"></div>
                    </div>
                    <!--/row-->
                    <!--/inbox toolbar-->
                    <div class="inbox" id="inboxPanel">
                        <!--message list-->
                        <div class="table-responsive" ng-show="!isMessageSelected()">
                            <table class="table table-striped table-bordered table-hover refresh-container pull-down">
                                <thead class="bg-info d-none"></thead>
                                <tbody>
                                <tr ng-repeat="item in pagedItems[currentPage]">
                                    <!--  | orderBy:sortingOrder:reverse -->
                                    <td><span class="col-sm-1" style="max-width: 5px;"><input type="checkbox" checklist-model="item.deleted" value={{item.deleted}}></span>
                                        <span class="col-sm-1 px-1" style="max-width: 8px;"><span ng-class="{strong: !item.read}">{{item.id}}</span></span>
                                        <span class="col-sm-1 px-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.message_status}}</span></span>
                                        <span class="col-sm-2 px-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.date | date:'yyyy-MM-dd hh:mm'}}</span></span>
                                        <span class="col-sm-3 px-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.sender_name}} to
                                                {{item.recipient_name}}</span></span> <span class="col-sm-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.title}}</span></span>
                                        <span class="col-sm-4 px-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}" ng-bind='(htmlToText(item.body) | limitTo:35)'></span></span>
                                        <!-- below for attachments, eventually -->
                                        <!-- <span class="col-sm-1 " ng-click="readMessage($index)"><span ng-show="item.attachment"
                                    class="glyphicon glyphicon-paperclip float-right"></span> <span ng-show="item.priority==1"
                                    class="float-right glyphicon glyphicon-warning-sign text-danger"></span></span> -->
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <!--message detail-->
                        <div class="container-fluid" ng-show="isMessageSelected()">
                            <div class="row" ng-controller="messageCtrl">
                                <div class="w-100 pl-1 mb-1 bg-secondary">
                                    <h5 class="pt-2">
                                        <a href="javascript:;" ng-click="groupToPages()"><?php echo xlt('Conversation from'); ?></a>
                                        <strong>{{selected.sender_name}}</strong> <?php echo xlt('regarding'); ?> <strong>{{selected.title}}</strong> <?php echo xlt('on'); ?> &lt;{{selected.date | date:'yyyy-MM-dd hh:mm'}}&gt;
                                    </h5>
                                    <!-- Leave below for future menu items -->
                                    <!--<span class="btn-group float-right">
                                        <button ng-show="selected.sender_id != cUserId" class="btn btn-primary" title="<?php /*echo xla('Reply to this message'); */?>" data-toggle="modal" data-mode="reply" data-noteid='{{selected.id}}' data-whoto='{{selected.sender_id}}' data-mtitle='{{selected.title}}' data-username='{{selected.sender_name}}' data-mailchain='{{selected.mail_chain}}' data-target="#modalCompose">
                                            <i class="fa fa-reply"></i> <?php /*echo xlt('Reply'); */?></button>
                                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" title="<?php /*echo xla("More options"); */?>"></button>
                                        <ul class="dropdown-menu float-right">
                                            <li ng-show='!isTrash'><a href="javascript:;" ng-click="batchDelete(items)"><i class="fa fa-trash"></i> <?php /*echo xlt('Send to Archive'); */?></a></li>
                                        </ul>
                                        <button ng-show='!isTrash' class="btn btn-md btn-primary float-right" ng-click="deleteItem(items.indexOf(selected))" title="<?php /*echo xla('Delete this message'); */?>" data-toggle="tooltip">
                                            <i class="fa fa-trash fa-1x"></i>
                                        </button>
                                    </span>-->
                                </div>
                                <div class="table-responsive row ml-1">
                                    <table class="table table-hover table-striped table-bordered refresh-container pull-down">
                                        <thead><?php echo xlt('Associated Messages in thread.');?></thead>
                                        <tbody>
                                        <tr class="animate-repeat" ng-repeat="item in allItems | Chained:selected.mail_chain">
                                            <td>
                                                <span class="col-sm" style="max-width: 8px;"><span ng-class="{strong: !item.read}">{{item.id}}</span></span> <span class="col-sm px-1" ng-click="readMessage($index)"><span>{{item.date | date:'yyyy-MM-dd hh:mm'}}</span></span>
                                                <span class="col-sm" ng-click="readMessage($index)"><span>{{item.message_status}}</span></span>
                                                <span class="col-sm px-1" ng-click="readMessage($index)"><span>{{item.sender_name}}
                                                        to {{item.recipient_name}}</span></span> <span class="col-sm-1" ng-click="readMessage($index)"><span>{{item.title}}</span></span>
                                                <span class="col-sm px-1" ng-hide="selected.id == item.id" ng-click="readMessage($index)"><span ng-bind-html='(htmlToText(item.body) | limitTo:35)'></span></span>
                                                <span class='btn-group float-right m-0'>
                                                    <button ng-show="selected.sender_id != cUserId && selected.id == item.id" class="btn btn-primary btn-small" title="<?php echo xla('Reply to this message'); ?>" data-toggle="modal" data-mode="reply" data-noteid='{{selected.id}}' data-whoto='{{selected.sender_id}}' data-mtitle='{{selected.title}}' data-username='{{selected.sender_name}}' data-mailchain='{{selected.mail_chain}}' data-target="#modalCompose"><i class="fa fa-reply"></i></button>
                                                    <button ng-show="selected.id == item.id && selected.sender_id != cUserId && !isPortal" class="btn btn-primary btn-small" title="<?php echo xla('Forward message to practice.'); ?>" data-toggle="modal" data-mode="forward" data-noteid='{{selected.id}}' data-whoto='{{selected.sender_id}}' data-mtitle='{{selected.title}}' data-username='{{selected.sender_name}}' data-mailchain='{{selected.mail_chain}}' data-target="#modalCompose"><i class="fa fa-share"></i></button>
                                                    <button ng-show='!isTrash && selected.id == item.id' class="btn btn-small btn-primary" ng-click="deleteItem(items.indexOf(selected))" title="<?php echo xla('Delete this message'); ?>" data-toggle="tooltip"><i class="fa fa-trash fa-1x"></i>
                                                    </button>
                                                </span>
                                                <div class='col jumbotron jumbotron-fluid my-3 p-1 bg-secondary rounded border border-info' ng-show="selected.id == item.id">
                                                    <span ng-bind-html=renderMessageBody(selected.body)></span>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--/message body-->
                            </div>
                            <!--/row-->
                        </div>
                    </div>
                    <!--/inbox panel-->
                    <!--paging-->
                    <div class="float-right my-2" ng-hide="selected">
                    <span class="text-muted"><strong>{{(itemsPerPage * currentPage) + 1}}</strong>~<strong>{{(itemsPerPage
                                * currentPage) + pagedItems[currentPage].length}}</strong> of <strong>{{items.length}}</strong></span>
                        <div class="btn-group" ng-show="items.length > itemsPerPage">
                            <button type="button" class="btn btn-secondary btn-lg" ng-class="{disabled: currentPage == 0}" ng-click="prevPage()"><i class="fa fa-chevron-left"></i></button>
                            <button type="button" class="btn btn-secondary btn-lg" ng-class="{disabled: currentPage == pagedItems.length - 1}" ng-click="nextPage()"><i class="fa fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                <!-- /.modal compose message -->
                <div class="modal fade" id="modalCompose">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><?php echo xlt('Compose Message'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body ">
                                <div class="col-12">
                                    <label ng-show='selected.mail_chain'><?php echo xlt('Refer to Message') . ' # '; ?>{{selected.id}}</label>
                                    <div class="jumbotron col-lg-12 m-1 p-1 bg-secondary" id="referMsg" ng-show='selected.mail_chain' ng-bind-html='renderMessageBody(selected.body)'></div>

                                    <form role="form" class="form-horizontal" ng-submit="submitForm(compose)" name="fcompose" id="fcompose" method="post" action="./handle_note.php">
                                        <fieldset class="row">
                                            <div class="col-lg-6 input-group my-2">
                                                <label for="selSendto"><?php echo xlt('To{{Destination}}'); ?></label>
                                                <select class="form-control ml-2 to-select-forward" id="selForwardto" ng-hide="compose.task != 'forward'" ng-model="compose.selrecip" ng-options="recip.userid as recip.username for recip in authrecips | filter:'user' track by recip.userid"></select>
                                                <select class="form-control ml-2 to-select-send" id="selSendto" ng-hide="compose.task == 'forward'" ng-model="compose.selrecip" ng-options="recip.userid as recip.username for recip in authrecips track by recip.userid"></select>
                                            </div>
                                            <div class="input-group col-lg-6 my-2">
                                                <label for="title"><?php echo xlt('Subject'); ?></label>
                                                <input type='text' list='listid' name='title' id='title' class="form-control ml-2" ng-model='compose.title' value="<?php echo xla('General'); ?>">
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
                                            <div class="col-12" id="inputBody" ng-hide="compose.task == 'forward'" ng-model="compose.inputBody"></div>
                                            <textarea class="col-12" id="finputBody" rows="8" ng-hide="compose.task != 'forward'" ng-model="compose.inputBody"></textarea>
                                        </fieldset>
                                        <input type="hidden" name="csrf_token_form" id="csrf_token_form" ng-value="compose.csrf_token_form" />
                                        <input type='hidden' name='noteid' id='noteid' ng-value="compose.noteid" />
                                        <input type='hidden' name='replyid' id='replyid' ng-value='selected.reply_mail_chain' />
                                        <input type='hidden' name='recipient_id' ng-value='compose.selrecip' />
                                        <input type='hidden' name='recipient_name' ng-value='compose.recipient_name' />
                                        <input type='hidden' name='sender_id' ng-value='compose.sender_id' />
                                        <input type='hidden' name='sender_name' ng-value='compose.sender_name' />
                                        <input type='hidden' name='task' ng-value='compose.task' />
                                        <input type='hidden' name='inputBody' ng-value='compose.inputBody' />
                                        <input type='hidden' name='pid' ng-value='compose.pid' />
                                        <div class='modal-footer'>
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                                            <button type="submit" id="submit" name="submit"
                                                class="btn btn-primary float-right" value="messages.php"><?php echo xlt('Send'); ?> <i
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
            <!--/row ng-controller-->
        </div>
        <!--/container--> </ng>

</body>
</html>
