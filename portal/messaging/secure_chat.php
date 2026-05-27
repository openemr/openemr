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
<script>
    (function () {
        'use strict';

        const state = {
            messages: [],
            onlines: [],
            chatusers: [],
            pusers: [],
            lastMessageId: null,
            historyFromId: null,
            pidMessages: null,
            pidPingServer: null,
            beep: new Audio('beep.ogg'),
            user: <?php echo !empty($session->get('ptName')) ? js_escape($session->get('ptName')) : js_escape(ADMIN_USERNAME); ?>,
            userid: String(<?php echo IS_PORTAL ? js_escape($session->get('pid')) : js_escape($session->get('authUser')); ?>),
            isPortal: <?php echo IS_PORTAL ? 'true' : 'false'; ?>,
            noRecipError: <?php echo xlj("Please Select a Recipient for Message.") ?>,
            editor: null
        };

        const pageTitleNotificator = {
            originalTitle: document.title,
            interval: null,
            status: 0,
            on(title, intervalSpeed) {
                if (!this.status) {
                    this.interval = setInterval(() => {
                        document.title = (this.originalTitle === document.title) ? title : this.originalTitle;
                    }, intervalSpeed || 500);
                    this.status = 1;
                }
            },
            off() {
                clearInterval(this.interval);
                document.title = this.originalTitle;
                this.status = 0;
            }
        };

        // --- Helper functions ---

        function unique(collection, keyname) {
            const keys = [];
            const output = [];
            collection.forEach(item => {
                const key = item[keyname];
                if (keys.indexOf(key) === -1) {
                    keys.push(key);
                    output.push(item);
                }
            });
            return output;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function safeUrl(url) {
            try {
                const u = new URL(url, window.location.origin);
                if (['http:', 'https:'].includes(u.protocol)) {
                    return u.toString();
                }
            } catch (e) {
                // Invalid URL
            }
            return '#';
        }

        function replaceShortcodes(message) {
            let msg = message.toString();
            // Replace [img] shortcodes with safe src URLs
            msg = msg.replace(/\[img](.*?)\[\/img]/g, (match, url) => {
                return `<img class='img-responsive' src='${safeUrl(url)}' />`;
            });
            // Replace [url] shortcodes with safe href URLs
            msg = msg.replace(/\[url](.*?)\[\/url]/g, (match, url) => {
                const safe = safeUrl(url);
                return `<a href='${safe}'>${escapeHtml(url)}</a>`;
            });
            msg = msg.replace(/<img /g, "<img class='img-responsive' ");
            return msg;
        }

        async function postData(url) {
            const response = await fetch(url, { method: 'POST', credentials: 'same-origin' });
            return response.json();
        }

        async function postForm(url, data) {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            });
            return response.json();
        }

        // --- Rendering ---

        function renderCurrentRecipients() {
            const container = document.getElementById('currentRecipients');
            const users = unique(state.chatusers, 'username');
            let html = '';
            users.forEach(user => {
                const recipId = String(user.recip_id);
                if (state.pusers.includes(recipId) && recipId !== state.userid) {
                    html += `<label><input type="checkbox" data-recip-id="${escapeHtml(recipId)}" data-section="current" checked> ${escapeHtml(user.username)}</label>`;
                }
            });
            container.innerHTML = html;
            bindRecipientCheckboxes(container);
        }

        function renderAvailableRecipients() {
            const container = document.getElementById('availableRecipients');
            const users = unique(state.chatusers, 'username');
            let html = '';
            users.forEach(user => {
                const recipId = String(user.recip_id);
                if (!state.isPortal || (state.isPortal && user.dash)) {
                    const checked = state.pusers.includes(recipId) ? 'checked' : '';
                    html += `<label><input type="checkbox" data-recip-id="${escapeHtml(recipId)}" data-section="available" ${checked}> ${escapeHtml(user.username)}</label>`;
                }
            });
            container.innerHTML = html;
            bindRecipientCheckboxes(container);
        }

        function bindRecipientCheckboxes(container) {
            container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.addEventListener('change', function () {
                    const recipId = String(this.dataset.recipId);
                    if (this.checked) {
                        if (!state.pusers.includes(recipId)) {
                            state.pusers.push(recipId);
                        }
                    } else {
                        const idx = state.pusers.indexOf(recipId);
                        if (idx !== -1) {
                            state.pusers.splice(idx, 1);
                        }
                    }
                    renderCurrentRecipients();
                    renderAvailableRecipients();
                });
            });
        }

        function sanitizeHtml(html) {
            return DOMPurify.sanitize(html, { USE_PROFILES: { html: true } });
        }

        function renderMessages() {
            const container = document.querySelector('.direct-chat-messages');
            let html = '';
            state.messages.forEach(message => {
                if (state.historyFromId && state.historyFromId >= message.id) {
                    return;
                }
                const isMe = message.me;
                const alignClass = !isMe ? 'right' : '';
                const nameFloat = isMe ? 'float-left' : 'float-right';
                const timeFloat = !isMe ? 'float-left' : 'float-right';
                const handIcon = !isMe ? 'fa-hand-o-left' : 'fa-hand-o-right';
                const clickTitle = <?php echo xlj('Click to activate and send to this recipient.'); ?>;
                const sanitizedBody = sanitizeHtml(message.message);

                html += `<div class="direct-chat-msg ${alignClass}">
                    <div class="direct-chat-info clearfix">
                        <span class="direct-chat-name ${nameFloat}">${escapeHtml(message.username)}</span>
                        <span class="direct-chat-timestamp ${timeFloat}">${escapeHtml(message.date)}</span>
                    </div>
                    <i class="direct-chat-img fa ${handIcon}" style="cursor: pointer; font-size: 24px" data-sender-id="${escapeHtml(message.sender_id)}" data-is-me="${isMe ? '1' : '0'}" title="${clickTitle}"></i>
                    <div class="direct-chat-text right">
                        <div class='px-0' title="${clickTitle}" data-sender-id="${escapeHtml(message.sender_id)}" data-is-me="${isMe ? '1' : '0'}">${sanitizedBody}</div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;

            // Bind click-to-select-recipient on icons and message bodies
            container.querySelectorAll('[data-sender-id]').forEach(el => {
                el.addEventListener('click', function () {
                    if (this.dataset.isMe === '0') {
                        state.pusers.length = 0;
                        state.pusers.push(this.dataset.senderId);
                        renderCurrentRecipients();
                        renderAvailableRecipients();
                    }
                });
            });
        }

        function renderOnlines() {
            const container = document.getElementById('onlineUsers');
            const users = unique(state.onlines, 'username');
            let html = '';
            users.forEach(ol => {
                html += `<label><input type="checkbox" disabled> ${escapeHtml(ol.username)}</label>`;
            });
            container.innerHTML = html;
        }

        function renderOnlineCount() {
            const el = document.getElementById('onlineCount');
            if (el && state.online) {
                el.textContent = state.online.total || '0';
            }
        }

        // --- Data fetching ---

        async function listMessages(wasMySubmission) {
            try {
                const data = await postData('?action=list');
                state.messages = [];
                if (Array.isArray(data)) {
                    data.forEach(message => {
                        message.message = replaceShortcodes(message.message);
                        if (message.sender_id !== undefined && message.sender_id !== null) {
                            message.sender_id = String(message.sender_id);
                        }
                        state.messages.push(message);
                    });
                }
                const lastMessage = state.messages[state.messages.length - 1];
                const lastMessageId = lastMessage && lastMessage.id;

                if (state.lastMessageId !== lastMessageId) {
                    onNewMessage(wasMySubmission);
                }
                state.lastMessageId = lastMessageId;
                renderMessages();
                getOnlines();
            } catch (e) {
                console.error('Error listing messages:', e);
            }
        }

        async function getAuthUsers() {
            try {
                const data = await postData('?action=authusers');
                const users = Array.isArray(data) ? data : [];
                state.chatusers = users.map(u => ({
                    ...u,
                    recip_id: u.recip_id !== undefined && u.recip_id !== null ? String(u.recip_id) : u.recip_id
                }));
                renderCurrentRecipients();
                renderAvailableRecipients();
            } catch (e) {
                console.error('Error getting auth users:', e);
            }
        }

        async function pingServer() {
            try {
                const data = await postData('?action=ping&username=' + encodeURIComponent(state.user));
                state.online = data;
                renderOnlineCount();
            } catch (e) {
                console.error('Error pinging server:', e);
            }
        }

        async function getOnlines() {
            try {
                const data = await postData('?action=ping&username=currentol');
                state.onlines = Array.isArray(data) ? data : [];
                renderOnlines();
            } catch (e) {
                console.error('Error getting onlines:', e);
            }
        }

        async function saveMessage() {
            const msgInput = document.getElementById('msgedit');
            const message = msgInput ? msgInput.value.trim() : '';

            if (!state.user || !state.user.trim()) {
                openModal();
                return;
            }
            if (!message) {
                return;
            }
            if (state.pusers.length === 0) {
                alert(state.noRecipError);
                return;
            }

            const params = new URLSearchParams();
            params.set('username', state.user);
            params.set('message', message);
            params.set('sender_id', state.userid);
            params.set('recip_id', JSON.stringify(state.pusers));

            try {
                await postForm('?action=save', params.toString());
                if (msgInput) {
                    msgInput.value = '';
                }
                listMessages(true);
            } catch (e) {
                console.error('Error saving message:', e);
            }
        }

        // --- Notifications ---

        function onNewMessage(wasMySubmission) {
            if (state.lastMessageId && !wasMySubmission) {
                playAudio();
                pageTitleNotificator.on('New message');
                notifyLastMessage();
            }
            scrollDown();
            window.addEventListener('focus', () => pageTitleNotificator.off(), { once: true });
        }

        function notifyLastMessage() {
            if (typeof Notification === 'undefined') {
                return;
            }
            Notification.requestPermission(permission => {
                const lastMessage = state.messages[state.messages.length - 1];
                if (permission === 'granted' && lastMessage && lastMessage.username) {
                    const notify = new Notification('Message notification from ' + lastMessage.username + ' : ', {
                        body: 'New message'
                    });
                    notify.onclick = () => window.focus();
                    notify.onclose = () => pageTitleNotificator.off();
                    const timer = setInterval(() => {
                        notify.close();
                        clearInterval(timer);
                    }, 100000);
                }
            });
        }

        function playAudio() {
            if (state.beep) {
                state.beep.play();
            }
        }

        function scrollDown() {
            setTimeout(() => {
                const el = document.querySelector('.direct-chat-messages');
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            }, 100);
        }

        // --- CKEditor modal ---

        function openModal() {
            $('#popeditor').modal({ backdrop: 'static' });
            editmsg();
        }

        function editmsg() {
            if (state.editor) {
                state.editor.destroy(true);
            }
            state.editor = CKEDITOR.replace('messageContent', {
                toolbarGroups: [
                    { name: 'document', groups: ['mode', 'document', 'doctools'] },
                    { name: 'clipboard', groups: ['clipboard', 'undo'] },
                    { name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing'] },
                    { name: 'forms', groups: ['forms'] },
                    { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
                    { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph'] },
                    { name: 'links', groups: ['links'] },
                    { name: 'insert', groups: ['insert'] },
                    { name: 'styles', groups: ['styles'] },
                    { name: 'colors', groups: ['colors'] },
                    { name: 'tools', groups: ['tools'] },
                    { name: 'others', groups: ['others'] },
                    { name: 'about', groups: ['about'] }
                ],
                removeButtons: 'About,Table,Smiley,SpecialChar,PageBreak,Iframe,HorizontalRule,Anchor,Unlink,Link,NumberedList,BulletedList,Outdent,Indent,Blockquote,CreateDiv,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,Language,BidiRtl,BidiLtr,CopyFormatting,RemoveFormat,Superscript,Subscript,Strike,Underline,Italic,Bold,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,SelectAll,Scayt,Find,Replace,PasteFromWord,Templates,NewPage,ExportPdf,Maximize,ShowBlocks,Source,Save,Preview,Print,Cut,Copy,Paste,PasteText,TextColor,BGColor',
                height: 250,
                width: '100%',
                resize_maxHeight: 650
            });
        }

        async function saveedit() {
            const content = CKEDITOR.instances.messageContent.getData();
            if (!state.user || !state.user.trim()) {
                return;
            }
            if (!content || !content.trim()) {
                return;
            }
            if (state.pusers.length === 0) {
                alert(state.noRecipError);
                return;
            }

            const params = new URLSearchParams();
            params.set('username', state.user);
            params.set('message', content);
            params.set('sender_id', state.userid);
            params.set('recip_id', JSON.stringify(state.pusers));

            try {
                await postForm('?action=save', params.toString());
                state.editor.destroy(true);
                listMessages(true);
            } catch (e) {
                console.error('Error saving edited message:', e);
            }
        }

        // --- History ---

        function clearHistory() {
            const lastMessage = state.messages[state.messages.length - 1];
            let lastMessageId = lastMessage && lastMessage.id;
            lastMessageId = (lastMessageId - 1 >= 2) ? lastMessageId - 1 : lastMessageId;
            if (lastMessageId) {
                state.historyFromId = lastMessageId;
                renderMessages();
            }
        }

        // --- Recipient controls ---

        function checkAll() {
            state.pusers = state.chatusers.map(item => item.recip_id);
            getAuthUsers();
        }

        function uncheckAll() {
            state.pusers = [];
            getAuthUsers();
        }

        // --- Init ---

        function init() {
            listMessages();
            state.pidMessages = setInterval(listMessages, 3000);
            state.pidPingServer = setInterval(pingServer, 5000);
            getAuthUsers();

            $('#popeditor').on('show.bs.modal', function () {
                const height = $(window).height() - 200;
                $(this).find('.modal-body').css('max-height', height);
            });

            // Form submit
            const form = document.getElementById('msgfrm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    saveMessage();
                });
            }

            // Enter key on input
            const msgInput = document.getElementById('msgedit');
            if (msgInput) {
                msgInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        saveMessage();
                    }
                });
            }

            // Button bindings
            const btnCheckAll = document.getElementById('chkall');
            if (btnCheckAll) {
                btnCheckAll.addEventListener('click', checkAll);
            }
            const btnCheckNone = document.getElementById('chknone');
            if (btnCheckNone) {
                btnCheckNone.addEventListener('click', uncheckAll);
            }
            const btnEdit = document.getElementById('btnEditMsg');
            if (btnEdit) {
                btnEdit.addEventListener('click', () => openModal());
            }
            const btnSendEdit = document.getElementById('btnSendEdit');
            if (btnSendEdit) {
                btnSendEdit.addEventListener('click', () => saveedit());
            }
            const btnClearHistory = document.getElementById('btnClearHistory');
            if (btnClearHistory) {
                btnClearHistory.addEventListener('click', clearHistory);
            }

            // Toggle portal-only elements
            if (state.isPortal) {
                document.querySelectorAll('[data-show-dashboard]').forEach(el => {
                    el.style.display = 'none';
                });
            } else {
                document.querySelectorAll('[data-show-portal]').forEach(el => {
                    el.style.display = 'none';
                });
            }
        }

        document.addEventListener('DOMContentLoaded', init);
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
