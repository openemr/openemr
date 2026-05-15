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
    <script>
        (function () {
            'use strict';

            // --- State ---
            const state = {
                sortingOrder: 'id',
                reverse: false,
                filteredItems: [],
                itemsPerPage: 20,
                pagedItems: [],
                compose: { task: 'add' },
                selrecip: [],
                currentPage: 0,
                sentItems: [],
                allItems: [],
                deletedItems: [],
                inboxItems: <?php echo json_encode($theresult);?>,
                items: [],
                selected: null,
                userproper: <?php echo !empty($session->get('ptName', null)) ? js_escape($session->get('ptName')) : js_escape($dashuser['fname'] . ' ' . $dashuser['lname']);?>,
                isPortal: <?php echo json_encode(IS_PORTAL ?: ''); ?>,
                isDashboard: <?php echo json_encode(IS_DASHBOARD ?: ''); ?>,
                authrecips: <?php echo json_encode(getAuthPortalUsers());?>,
                xLate: {
                    fwd: <?php echo xlj('Forwarded Portal Message Re: '); ?>,
                    confirm: {
                        one: <?php echo xlj('Confirm to Archive Current Thread?'); ?>,
                        all: <?php echo xlj('Confirm to Archive Selected Messages?'); ?>,
                        err: <?php echo xlj('You are sending to yourself!'); ?>
                    }
                },
                csrf: <?php echo js_escape(CsrfUtils::collectCsrfToken($session, 'messages-portal')); ?>,
                isInit: false,
                isInbox: true,
                isSent: false,
                isAll: false,
                isTrash: false,
                errorLoadingMessages: false
            };

            state.cUserId = state.isPortal || state.isDashboard;
            state.items = state.inboxItems;

            // --- Helpers ---

            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                const div = document.createElement('div');
                div.textContent = String(text);
                return div.innerHTML;
            }

            function searchMatch(haystack, needle) {
                if (!needle) return true;
                return String(haystack).toLowerCase().indexOf(needle.toLowerCase()) !== -1;
            }

            function renderMessageBody(html) {
                return DOMPurify.sanitize(html, {
                    USE_PROFILES: { html: true },
                    FORBID_TAGS: ['a', 'img']
                });
            }

            function htmlToText(html) {
                const hold = document.createElement('DIV');
                hold.innerHTML = DOMPurify.sanitize(html, {
                    USE_PROFILES: { html: true },
                    FORBID_TAGS: ['a', 'img']
                });
                return hold.textContent || hold.innerText || '';
            }

            function limitTo(str, limit) {
                if (!str) return '';
                return str.length > limit ? str.substring(0, limit) : str;
            }

            async function postForm(url, params) {
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params
                });
                return response.json();
            }

            // --- Filtering & Paging ---

            function search(query) {
                if (!query) {
                    state.filteredItems = state.items.slice();
                } else {
                    state.filteredItems = state.items.filter(item => {
                        for (const attr in item) {
                            if (searchMatch(item[attr], query)) return true;
                        }
                        return false;
                    });
                }
                state.currentPage = 0;
                groupToPages();
            }

            function groupToPages() {
                state.selected = null;
                state.pagedItems = [];
                for (let i = 0; i < state.filteredItems.length; i++) {
                    const pageIdx = Math.floor(i / state.itemsPerPage);
                    if (i % state.itemsPerPage === 0) {
                        state.pagedItems[pageIdx] = [state.filteredItems[i]];
                    } else {
                        state.pagedItems[pageIdx].push(state.filteredItems[i]);
                    }
                }
                renderAll();
            }

            function prevPage() {
                if (state.currentPage > 0) {
                    state.currentPage--;
                    renderAll();
                }
            }

            function nextPage() {
                if (state.currentPage < state.pagedItems.length - 1) {
                    state.currentPage++;
                    renderAll();
                }
            }

            // --- Folder selection ---

            function selectInbox() {
                state.isInbox = true;
                state.isTrash = state.isAll = state.isSent = false;
                state.items = state.inboxItems;
                search();
            }

            function selectSent() {
                state.isSent = true;
                state.isTrash = state.isAll = state.isInbox = false;
                state.items = state.sentItems;
                search();
            }

            function selectAll() {
                state.isAll = true;
                state.isTrash = state.isSent = state.isInbox = false;
                state.items = state.allItems;
                search();
            }

            function selectTrash() {
                state.isTrash = true;
                state.isSent = state.isAll = state.isInbox = false;
                state.items = state.deletedItems;
                search();
            }

            // --- API calls ---

            async function getAllMessages() {
                try {
                    const data = await postForm('handle_note.php', $.param({ task: 'getall', csrf_token_form: state.csrf }));
                    if (data) {
                        state.allItems = Array.isArray(data) ? data.slice() : [];
                    }
                } catch (e) {
                    console.error('Error getting all messages:', e);
                }
            }

            async function getDeletedMessages() {
                try {
                    const data = await postForm('handle_note.php', $.param({ task: 'getdeleted', csrf_token_form: state.csrf }));
                    if (data) {
                        state.deletedItems = Array.isArray(data) ? data.slice() : [];
                    }
                } catch (e) {
                    console.error('Error getting deleted messages:', e);
                }
            }

            async function getSentMessages() {
                try {
                    const data = await postForm('handle_note.php', $.param({ task: 'getsent', csrf_token_form: state.csrf }));
                    state.sentItems = Array.isArray(data) ? data.slice() : [];
                } catch (e) {
                    console.error('Error getting sent messages:', e);
                }
            }

            async function deleteMessage(id) {
                try {
                    await postForm('handle_note.php', $.param({ task: 'delete', noteid: id, csrf_token_form: state.csrf }));
                } catch (e) {
                    alert(e.message);
                }
            }

            function deleteItem(idx) {
                if (!confirm(state.xLate.confirm.one)) return;
                const itemToDelete = state.allItems[idx];
                const idxInItems = state.items.indexOf(itemToDelete);
                deleteMessage(itemToDelete.mail_chain);
                if (idxInItems !== -1) {
                    state.items.splice(idxInItems, 1);
                }
                search();
                initData();
            }

            function batchDelete() {
                if (!confirm(state.xLate.confirm.all)) return;
                const itemToDelete = [];
                state.items.forEach((item, idx) => {
                    if (item._deleted) {
                        itemToDelete.push(item.id);
                    }
                });
                postForm('handle_note.php', $.param({
                    task: 'massdelete',
                    notejson: JSON.stringify(itemToDelete),
                    csrf_token_form: state.csrf
                })).then(() => {
                    window.location.reload();
                }).catch(e => {
                    alert(e.message);
                });
            }

            function readMessage(idx) {
                const item = state.pagedItems[state.currentPage][idx];
                if (item.message_status === 'New') {
                    postForm('handle_note.php', $.param({
                        task: 'setread',
                        noteid: item.id,
                        csrf_token_form: state.csrf
                    })).then(() => {
                        item.message_status = 'Read';
                    });
                }
                // Find in allItems by id
                const allIdx = state.allItems.findIndex(a => +a.id === +item.id);
                state.isAll = true;
                state.isTrash = state.isSent = state.isInbox = false;
                state.items = state.allItems;
                state.selected = allIdx !== -1 ? state.allItems[allIdx] : item;
                renderAll();
            }

            function closeMessage() {
                state.selected = null;
                groupToPages();
            }

            function readAll() {
                state.items.forEach(item => { item.message_status = 'Read'; });
                renderAll();
            }

            // --- Rendering ---

            function renderAll() {
                renderNav();
                renderToolbar();
                renderMessageList();
                renderMessageDetail();
                renderPaging();
                renderLoadingState();
            }

            function renderLoadingState() {
                const loadingRow = document.getElementById('loadingRow');
                const contentRow = document.getElementById('contentRow');
                if (loadingRow && contentRow) {
                    if (state.isInit) {
                        loadingRow.classList.add('d-none');
                        contentRow.classList.remove('d-none');
                    } else {
                        loadingRow.classList.remove('d-none');
                        contentRow.classList.add('d-none');
                    }
                }
            }

            function renderNav() {
                document.querySelectorAll('#navInbox, #navSent, #navAll, #navTrash').forEach(el => {
                    el.classList.remove('active');
                });
                if (state.isInbox) document.getElementById('navInbox').classList.add('active');
                if (state.isSent) document.getElementById('navSent').classList.add('active');
                if (state.isAll) document.getElementById('navAll').classList.add('active');
                if (state.isTrash) document.getElementById('navTrash').classList.add('active');

                const inboxBadge = document.getElementById('badgeInbox');
                const sentBadge = document.getElementById('badgeSent');
                const allBadge = document.getElementById('badgeAll');
                const trashBadge = document.getElementById('badgeTrash');
                if (inboxBadge) inboxBadge.textContent = state.inboxItems.length;
                if (sentBadge) sentBadge.textContent = state.sentItems.length;
                if (allBadge) allBadge.textContent = state.allItems.length;
                if (trashBadge) trashBadge.textContent = state.deletedItems.length;

                // Hide archive option when viewing the archive folder
                const archiveMenuItem = document.getElementById('archiveMenuItem');
                if (archiveMenuItem) {
                    archiveMenuItem.style.display = state.isTrash ? 'none' : '';
                }
            }

            function renderToolbar() {
                const toolbar = document.getElementById('inboxToolbar');
                if (toolbar) {
                    toolbar.style.display = state.selected ? 'none' : '';
                }
            }

            function renderMessageList() {
                const container = document.getElementById('messageListTable');
                if (!container) return;
                container.style.display = state.selected ? 'none' : '';

                const tbody = container.querySelector('tbody');
                if (!tbody) return;

                const currentPageItems = state.pagedItems[state.currentPage] || [];
                let html = '';
                currentPageItems.forEach((item, idx) => {
                    const strong = item.message_status === 'New' ? 'font-weight: bold;' : '';
                    const bodyPreview = limitTo(htmlToText(item.body), 35);
                    const dateStr = item.date || '';
                    html += `<tr role="button">
                        <td class="message-row">
                            <span class="col-sm-1" style="max-width: 5px;"><input type="checkbox" data-item-idx="${idx}" class="item-checkbox"></span>
                            <span class="col-sm-1 px-1 msg-click" data-idx="${idx}"><span style="${strong}">${escapeHtml(item.message_status)}</span></span>
                            <span class="col-sm-2 px-1 msg-click" data-idx="${idx}"><span style="${strong}">${escapeHtml(dateStr)}</span></span>
                            <span class="col-sm-3 px-1 msg-click" data-idx="${idx}">
                                <a class="btn-link"><span style="${strong}">${escapeHtml(item.sender_name)} to ${escapeHtml(item.recipient_name)}</span></a>
                            </span>
                            <span class="col-sm-1 msg-click" data-idx="${idx}">
                                <a class="btn-link"><span style="${strong}">${escapeHtml(item.title)}</span></a>
                            </span>
                            <span class="col-sm-4 px-1 msg-click" data-idx="${idx}"><span style="${strong}">${escapeHtml(bodyPreview)}</span></span>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;

                // Bind click handlers
                tbody.querySelectorAll('.msg-click').forEach(el => {
                    el.addEventListener('click', () => readMessage(parseInt(el.dataset.idx)));
                });
                tbody.querySelectorAll('.item-checkbox').forEach(cb => {
                    cb.addEventListener('change', function () {
                        const idx = parseInt(this.dataset.itemIdx);
                        const pageItems = state.pagedItems[state.currentPage] || [];
                        if (pageItems[idx]) {
                            pageItems[idx]._deleted = this.checked;
                        }
                    });
                });
            }

            function renderMessageDetail() {
                const container = document.getElementById('messageDetail');
                if (!container) return;
                container.style.display = state.selected ? '' : 'none';
                if (!state.selected) return;

                const sel = state.selected;
                const headerEl = document.getElementById('detailHeader');
                if (headerEl) {
                    headerEl.innerHTML = `<h5 class="pt-2">
                        <a href="javascript:;" id="btnCloseDetail"><?php echo xlt('Conversation from'); ?></a>
                        <strong>${escapeHtml(sel.sender_name)}</strong> <?php echo xlt('regarding'); ?> <strong>${escapeHtml(sel.title)}</strong> <?php echo xlt('on'); ?> &lt;${escapeHtml(sel.date)}&gt;
                    </h5>`;
                    document.getElementById('btnCloseDetail').addEventListener('click', closeMessage);
                }

                // Render chained messages
                const chainTbody = document.getElementById('chainTbody');
                if (!chainTbody) return;
                const chainId = sel.mail_chain;
                const chained = isNaN(chainId) ? state.allItems : state.allItems.filter(item => item.mail_chain == chainId);

                let html = '';
                chained.forEach(item => {
                    const isSelected = sel.id === item.id;
                    const showReply = sel.sender_id !== state.cUserId && isSelected;
                    const showForward = isSelected && sel.sender_id !== state.cUserId && !state.isPortal;
                    const showDelete = !state.isTrash && isSelected;

                    html += `<tr>
                        <td role="button">
                            <span class="col-sm px-1"><span>${escapeHtml(item.date)}</span></span>
                            <span class="col-sm"><span>${escapeHtml(item.message_status)}</span></span>
                            <span class="col-sm px-1"><span>${escapeHtml(item.sender_name)} to ${escapeHtml(item.recipient_name)}</span></span>
                            <span class="col-sm-1"><span>${escapeHtml(item.title)}</span></span>`;

                    if (!isSelected) {
                        html += `<span class="col-sm px-1"><span>${limitTo(htmlToText(item.body), 35)}</span></span>`;
                    }

                    html += `<span class='btn-group float-right m-0'>`;
                    if (showReply) {
                        html += `<button class="btn btn-primary btn-small btn-compose-reply" data-toggle="modal" data-mode="reply" data-noteid="${escapeHtml(sel.id)}" data-whoto="${escapeHtml(sel.sender_id)}" data-mtitle="${escapeHtml(sel.title)}" data-username="${escapeHtml(sel.sender_name)}" data-mailchain="${escapeHtml(sel.mail_chain)}" data-target="#modalCompose"><i class="fa fa-reply"></i></button>`;
                    }
                    if (showForward) {
                        html += `<button class="btn btn-primary btn-small btn-compose-fwd" data-toggle="modal" data-mode="forward" data-noteid="${escapeHtml(sel.id)}" data-whoto="${escapeHtml(sel.sender_id)}" data-mtitle="${escapeHtml(sel.title)}" data-username="${escapeHtml(sel.sender_name)}" data-mailchain="${escapeHtml(sel.mail_chain)}" data-target="#modalCompose"><i class="fa fa-share"></i></button>`;
                    }
                    if (showDelete) {
                        html += `<button class="btn btn-small btn-primary btn-delete-item" data-item-id="${escapeHtml(sel.id)}" title="<?php echo xla('Archive this message'); ?>"><i class="fa fa-trash fa-1x"></i></button>`;
                    }
                    html += `</span>`;

                    if (isSelected) {
                        html += `<div class='col jumbotron jumbotron-fluid my-3 p-1 bg-light text-dark rounded border border-info'><span>${renderMessageBody(sel.body)}</span></div>`;
                    }
                    html += `</td></tr>`;
                });
                chainTbody.innerHTML = html;

                // Bind reply/forward buttons to open modal
                chainTbody.querySelectorAll('.btn-compose-reply, .btn-compose-fwd').forEach(btn => {
                    btn.addEventListener('click', function () {
                        $('#modalCompose').data('relatedTarget', this);
                        $('#modalCompose').modal('show');
                    });
                });
                // Bind delete
                chainTbody.querySelectorAll('.btn-delete-item').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const itemIdx = state.items.indexOf(state.selected);
                        if (itemIdx !== -1) {
                            deleteItem(itemIdx);
                        }
                    });
                });
            }

            function renderPaging() {
                const pagingEl = document.getElementById('pagingControls');
                if (!pagingEl) return;
                pagingEl.style.display = state.selected ? 'none' : '';

                const currentPageItems = state.pagedItems[state.currentPage] || [];
                const startNum = (state.itemsPerPage * state.currentPage) + 1;
                const endNum = (state.itemsPerPage * state.currentPage) + currentPageItems.length;

                const infoEl = document.getElementById('pagingInfo');
                if (infoEl) {
                    infoEl.innerHTML = `<strong>${startNum}</strong>~<strong>${endNum}</strong> of <strong>${state.items.length}</strong>`;
                }

                const btnGroup = document.getElementById('pagingButtons');
                if (btnGroup) {
                    btnGroup.style.display = state.items.length > state.itemsPerPage ? '' : 'none';
                }
            }

            // --- Compose modal setup ---

            function setupComposeModal(e) {
                state.compose = { task: 'add' };
                $('#inputBody').summernote('destroy');

                // Get mode from data-mode attribute
                const relatedTarget = $(e.currentTarget).data('relatedTarget') || e.relatedTarget;
                const mode = relatedTarget ? $(relatedTarget).attr('data-mode') : 'add';
                state.compose.task = mode;

                // Populate the "Refer to Message" panel with the sanitized
                // body of the currently selected message when replying or
                // forwarding. This replaces the former Angular ng-bind-html.
                const referMsgEl = document.getElementById('referMsg');
                const referLabelEl = document.getElementById('referLabel');
                const referMsgIdEl = document.getElementById('referMsgId');
                const showRefer = (mode === 'forward' || mode === 'reply') && state.selected && state.selected.mail_chain;
                if (referMsgEl) {
                    if (showRefer) {
                        referMsgEl.innerHTML = renderMessageBody(state.selected.body);
                        referMsgEl.style.display = '';
                    } else {
                        referMsgEl.innerHTML = '';
                        referMsgEl.style.display = 'none';
                    }
                }
                if (referLabelEl) {
                    referLabelEl.style.display = showRefer ? '' : 'none';
                }
                if (referMsgIdEl && showRefer) {
                    referMsgIdEl.textContent = state.selected.id;
                }

                if (mode === 'forward') {
                    $('#modalCompose .modal-header .modal-title').html("Forward Message");
                    const recipId = $(relatedTarget).attr('data-whoto');
                    const title = $(relatedTarget).attr('data-mtitle');
                    const uname = $(relatedTarget).attr('data-username');
                    $('#selForwardto').prop('disabled', false).show();
                    $('#selSendto').hide();
                    $('#title').prop('disabled', false);
                    state.compose.title = title;
                    state.compose.selrecip = recipId;
                    state.compose.recipient_name = uname;
                    state.compose.recipient_id = recipId;

                    state.authrecips.forEach(o => {
                        if (o.userid === recipId) {
                            state.compose.pid = o.pid;
                        }
                    });
                    const referText = referMsgEl ? (referMsgEl.textContent || referMsgEl.innerText || '') : '';
                    const fmsg = '\n\n\n> ' + state.xLate.fwd + title + ' by ' + uname + '\n> ' + referText;
                    $('#finputBody').val(fmsg).show();
                    $('#inputBody').hide();
                    state.compose.noteid = $(relatedTarget).attr('data-noteid');
                    updateComposeForm();
                } else if (mode === 'reply') {
                    $('#inputBody').show().summernote({
                        focus: true,
                        height: '225px',
                        width: '100%',
                        tabsize: 4,
                        disableDragAndDrop: true,
                        dialogsInBody: true,
                        dialogsFade: true
                    });
                    $('#finputBody').hide();
                    $('#selForwardto').hide();
                    $('#selSendto').show();
                    $('#modalCompose .modal-header .modal-title').html(<?php echo xlj("Compose Reply Message"); ?>);
                    let chain = $(relatedTarget).attr('data-mailchain');
                    if (chain === '0') {
                        chain = $(relatedTarget).attr('data-noteid');
                    }
                    const recipId = $(relatedTarget).attr('data-whoto');
                    const title = $(relatedTarget).attr('data-mtitle');
                    const uname = $(relatedTarget).attr('data-username');
                    $('#selSendto').val(recipId);
                    $('#title').val(title);
                    state.compose.title = title;
                    state.compose.selrecip = recipId;
                    state.compose.recipient_name = uname;
                    state.compose.recipient_id = recipId;
                    state.compose.noteid = chain;
                    updateComposeForm();
                } else {
                    $('#inputBody').show().summernote({
                        width: '100%',
                        focus: true,
                        height: '375px',
                        tabsize: 4,
                        disableDragAndDrop: true,
                        dialogsInBody: true,
                        dialogsFade: true,
                        popover: {
                            image: [],
                            link: [],
                            air: []
                        }
                    });
                    $('#finputBody').hide();
                    $('#selForwardto').hide();
                    $('#selSendto').show().prop('disabled', false);
                    $('#modalCompose .modal-header .modal-title').html(<?php echo xlj("Compose New Message"); ?>);
                    state.compose.task = 'add';
                    $('#selSendto').prop('disabled', false);
                    $('#title').prop('disabled', false);
                    updateComposeForm();
                }
            }

            function updateComposeForm() {
                // Sync hidden fields with compose state
                $('#hiddenTask').val(state.compose.task || 'add');
                $('#hiddenNoteid').val(state.compose.noteid || '');
                if (state.compose.title) {
                    $('#title').val(state.compose.title);
                }
                if (state.compose.selrecip) {
                    $('#selSendto').val(state.compose.selrecip);
                    $('#selForwardto').val(state.compose.selrecip);
                }
            }

            function submitForm(e) {
                // Re-enable title for submit
                $('#title').prop('disabled', false);
                $('#selSendto').prop('disabled', false);

                const task = state.compose.task || 'add';
                const selrecip = $('#selSendto').val() || state.compose.selrecip;

                // Confirm when sending to yourself
                if (selrecip === state.cUserId) {
                    if (!confirm(state.xLate.confirm.err)) {
                        e.preventDefault();
                        return false;
                    }
                }

                // Populate hidden inputs so the natural form submission carries
                // the CSRF token and all metadata expected by handle_note.php.
                document.getElementById('csrf_token_form').value = state.csrf;
                document.getElementById('hiddenTask').value = task;
                document.getElementById('hiddenSenderName').value = state.userproper;

                if (task === 'forward') {
                    const $fwd = $('#selForwardto option:selected');
                    document.getElementById('hiddenSenderId').value = $fwd.val() || '';
                    document.getElementById('hiddenSenderName').value = $fwd.text() || state.userproper;
                    document.getElementById('hiddenRecipientId').value = state.compose.recipient_id || '';
                    document.getElementById('hiddenRecipientName').value = state.compose.recipient_name || '';
                    document.getElementById('hiddenPid').value = state.compose.pid || '';
                    document.getElementById('hiddenNoteid').value = state.compose.noteid || '';
                    // finputBody textarea already has its value via its name="inputBody"
                } else {
                    document.getElementById('hiddenSenderId').value = state.cUserId;
                    const recipName = $('#selSendto option:selected').text() || state.compose.recipient_name || '';
                    document.getElementById('hiddenRecipientName').value = recipName;
                    document.getElementById('hiddenRecipientId').value = selrecip || '';
                    document.getElementById('hiddenNoteid').value = state.compose.noteid || '';
                    // Copy Summernote contents into the hidden finputBody (which has name="inputBody")
                    document.getElementById('finputBody').value = $('#inputBody').summernote('code');
                }

                // Set replyid for message threading (replies and forwards).
                // Matches the original AngularJS ng-value='selected.reply_mail_chain'.
                document.getElementById('replyid').value = state.selected?.reply_mail_chain || '';

                // Let the form submit naturally with all values now populated
                return true;
            }

            // --- Data init ---

            async function initData() {
                try {
                    await Promise.all([
                        getSentMessages(),
                        getAllMessages(),
                        getDeletedMessages()
                    ]);
                    state.isInit = true;
                    state.errorLoadingMessages = false;
                } catch (e) {
                    state.errorLoadingMessages = true;
                    state.isInit = true;
                }
                selectInbox();
            }

            // --- DOM Ready ---

            document.addEventListener('DOMContentLoaded', function () {
                // Nav bindings
                document.getElementById('navInbox').addEventListener('click', e => { e.preventDefault(); selectInbox(); });
                document.getElementById('navSent').addEventListener('click', e => { e.preventDefault(); selectSent(); });
                document.getElementById('navAll').addEventListener('click', e => { e.preventDefault(); selectAll(); });
                document.getElementById('navTrash').addEventListener('click', e => { e.preventDefault(); selectTrash(); });

                // Toolbar bindings
                document.getElementById('btnReadAll').addEventListener('click', e => { e.preventDefault(); readAll(); });
                document.getElementById('btnBatchDelete').addEventListener('click', e => { e.preventDefault(); batchDelete(); });

                // Paging
                document.getElementById('btnPrev').addEventListener('click', e => { e.preventDefault(); prevPage(); });
                document.getElementById('btnNext').addEventListener('click', e => { e.preventDefault(); nextPage(); });

                // Compose modal
                $('#modalCompose').on('hidden.bs.modal', function () {
                    window.location.reload();
                });
                $('#modalCompose').on('show.bs.modal', setupComposeModal);

                // Form submit
                const form = document.getElementById('fcompose');
                if (form) {
                    form.addEventListener('submit', submitForm);
                }

                // Populate recipient selects
                const selSendto = document.getElementById('selSendto');
                const selForwardto = document.getElementById('selForwardto');
                state.authrecips.forEach(recip => {
                    const opt = document.createElement('option');
                    opt.value = recip.userid;
                    opt.textContent = recip.username;
                    selSendto.appendChild(opt);

                    if (recip.type === 'user') {
                        const opt2 = document.createElement('option');
                        opt2.value = recip.userid;
                        opt2.textContent = recip.username;
                        selForwardto.appendChild(opt2);
                    }
                });

                // Portal-only visibility
                if (state.isPortal) {
                    document.querySelectorAll('[data-show-dashboard]').forEach(el => el.style.display = 'none');
                }

                // Initialize data
                initData();
            });

            <?php
            if ($showSMS) {
                $globalsBag->getKernel()->getEventDispatcher()->dispatch(new SendSmsEvent($pid), SendSmsEvent::JAVASCRIPT_READY_SMS_POST);
            }
            ?>
        })();
    </script>
    <div class="container-fluid" id='main'>
        <div class='my-3'>
            <h2><i class='fa fa-envelope w-auto h-auto mr-2'></i><?php echo xlt('Secure Messaging'); ?></h2>
        </div>
        <div class="row" id="loadingRow">
            <div class="col-12">
                <div class="alert alert-info"><h3><?php echo xlt("Loading..."); ?> <i class="wait fa fa-cog fa-spin ml-2"></i></h3></div>
            </div>
        </div>
        <div class="row d-none" id="contentRow">
            <div class="col-md-2 p-0 m-0 text-left border-right bg-light text-dark">
                <div class="sticky-top">
                    <ul class="nav nav-pills nav-stacked flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" id="navInbox" data-toggle="pill" href="javascript:;"><span class="badge float-right" id="badgeInbox">0</span><?php echo xlt('Inbox'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navSent" data-toggle="pill" href="javascript:;"><span class="badge float-right" id="badgeSent">0</span><?php echo xlt('Sent{{Mails}}'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navAll" data-toggle="pill" href="javascript:;"><span class="badge float-right" id="badgeAll">0</span><?php echo xlt('All{{Mails}}'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navTrash" data-toggle="pill" href="javascript:;"><span class="badge float-right" id="badgeTrash">0</span><?php echo xlt('Archive'); ?></a>
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
                        <button class="btn btn-primary" title="<?php echo xla("Compose Message"); ?>" data-mode="add" data-toggle="modal" data-target="#modalCompose">
                            <span class="fa fa-edit fa-lg"></span> <?php echo xlt("Compose Message"); ?>
                        </button>
                        <?php
                        if ($showSMS) {
                            $globalsBag->getKernel()->getEventDispatcher()->dispatch(new SendSmsEvent($session->get('pid', 0)), SendSmsEvent::ACTIONS_RENDER_SMS_POST);
                        }
                        ?>
                        <a class="btn btn-secondary" data-toggle="tooltip" title="<?php echo xla("Refresh to see new messages"); ?>" id="refreshInbox" href="javascript:;" onclick='window.location.replace("./messages.php")'> <span class="fa fa-sync fa-lg"></span>
                        </a>
                        <div class="btn-group btn-group float-right">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><?php echo xlt('Actions'); ?></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a class="dropdown-item" href="javascript:;" id="btnReadAll"><?php echo xlt('Mark all as read'); ?></a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="" data-mode="add" data-toggle="modal" data-target="#modalCompose"><i class="fa fa-edit"></i> <?php echo xlt('Compose Message'); ?></a>
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
                            <div class="w-100 pl-1 mb-1 bg-light text-dark" id="detailHeader">
                            </div>
                            <div class="table-responsive row ml-1">
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
                <div class="float-right my-2" id="pagingControls">
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
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body ">
                            <div class="col-12">
                                <label id="referLabel" style="display:none;"><?php echo xlt('Refer to Message') . ' # '; ?><span id="referMsgId"></span></label>
                                <div class="jumbotron col-lg-12 m-1 p-1 bg-light text-dark" id="referMsg" style="display:none;"></div>

                                <form role="form" class="form-horizontal" name="fcompose" id="fcompose" method="post" action="./handle_note.php">
                                    <fieldset class="row">
                                        <div class="col-lg-6 input-group my-2">
                                            <label for="selSendto"><?php echo xlt('To{{Destination}}'); ?></label>
                                            <select class="form-control ml-2 to-select-forward" id="selForwardto" name="selForwardto" style="display:none;"></select>
                                            <select class="form-control ml-2 to-select-send" id="selSendto" name="selrecip"></select>
                                        </div>
                                        <div class="input-group col-lg-6 my-2">
                                            <label for="title"><?php echo xlt('Subject'); ?></label>
                                            <input type='text' list='listid' name='title' id='title' class="form-control ml-2" value="<?php echo xla('General'); ?>">
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
        <!--/row-->
    </div>
    <!--/container-->

</body>
</html>
