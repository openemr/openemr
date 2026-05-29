/**
 * messages.js
 *
 * Client-side logic for the patient portal secure messaging inbox. PHP-side
 * values (translations, session-derived user identity, mode flags, initial
 * inbox payload, CSRF token, etc.) are passed in via window.OE_MESSAGES_CONFIG,
 * which messages.php emits before this file loads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/* global DOMPurify */

(function () {
    'use strict';

    const config = window.OE_MESSAGES_CONFIG || { strings: {} };


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
        inboxItems: config.inboxItems,
        items: [],
        selected: null,
        userproper: config.userproper,
        isPortal: config.isPortal,
        isDashboard: config.isDashboard,
        authrecips: config.authrecips,
        xLate: {
            fwd: config.strings.forwardedRe,
            confirm: {
                one: config.strings.confirmOne,
                all: config.strings.confirmAll,
                err: config.strings.sendingToSelf
            }
        },
        csrf: config.csrf,
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
        // nosemgrep: openemr-js-innerhtml-dynamic -- DOMPurify-sanitized; we then read textContent only.
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
        // handle_note.php returns JSON for read endpoints and plain "ok"/"error"
        // strings for write endpoints. Parse JSON when possible, fall back to
        // the raw text so write callers don't crash on a non-JSON success body.
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch {
            return text;
        }
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
            throw e;
        }
    }

    function deleteItem(item) {
        if (!item) return;
        if (!confirm(state.xLate.confirm.one)) return;
        const removeFromList = (list) => {
            if (!Array.isArray(list)) return;
            for (let i = list.length - 1; i >= 0; i--) {
                if (list[i] && list[i].mail_chain === item.mail_chain) {
                    list.splice(i, 1);
                }
            }
        };
        deleteMessage(item.mail_chain).then(() => {
            removeFromList(state.items);
            removeFromList(state.inboxItems);
            removeFromList(state.sentItems);
            removeFromList(state.allItems);
            if (state.selected && state.selected.mail_chain === item.mail_chain) {
                state.selected = null;
            }
            search();
            getDeletedMessages();
        });
    }

    function batchDelete() {
        if (!confirm(state.xLate.confirm.all)) return;
        const itemToDelete = [];
        state.items.forEach(item => {
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

    function readMessage(item) {
        if (!item) return;
        if (item.message_status === 'New') {
            postForm('handle_note.php', $.param({
                task: 'setread',
                noteid: item.id,
                csrf_token_form: state.csrf
            })).then(() => {
                const markReadById = (list, id) => {
                    if (!Array.isArray(list)) return;
                    list.forEach(entry => {
                        if (entry && entry.id === id) {
                            entry.message_status = 'Read';
                        }
                    });
                };
                markReadById(state.items, item.id);
                markReadById(state.inboxItems, item.id);
                markReadById(state.sentItems, item.id);
                markReadById(state.allItems, item.id);
                renderAll();
            });
        }
        state.selected = item;
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
        // nosemgrep: openemr-js-innerhtml-dynamic -- every ${} interpolation above goes through escapeHtml() or is a static class/style literal.
        tbody.innerHTML = html;

        // Bind click handlers
        tbody.querySelectorAll('.msg-click').forEach(el => {
            el.addEventListener('click', () => {
                const idx = parseInt(el.dataset.idx, 10);
                const item = (state.pagedItems[state.currentPage] || [])[idx];
                readMessage(item);
            });
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
            // nosemgrep: openemr-js-innerhtml-dynamic -- every ${} interpolation goes through escapeHtml().
            headerEl.innerHTML = `<h5 class="pt-2">
                <a href="javascript:;" id="btnCloseDetail">${escapeHtml(config.strings.conversationFrom)}</a>
                <strong>${escapeHtml(sel.sender_name)}</strong> ${escapeHtml(config.strings.regarding)} <strong>${escapeHtml(sel.title)}</strong> ${escapeHtml(config.strings.onPrep)} &lt;${escapeHtml(sel.date)}&gt;
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
                html += `<button class="btn btn-small btn-primary btn-delete-item" data-item-id="${escapeHtml(sel.id)}" title="${escapeHtml(config.strings.archiveTitle)}"><i class="fa fa-trash fa-1x"></i></button>`;
            }
            html += `</span>`;

            if (isSelected) {
                html += `<div class='col jumbotron jumbotron-fluid my-3 p-1 bg-light text-dark rounded border border-info'><span>${renderMessageBody(sel.body)}</span></div>`;
            }
            html += `</td></tr>`;
        });
        // nosemgrep: openemr-js-innerhtml-dynamic -- ${} interpolations use escapeHtml(); the only HTML body comes from renderMessageBody() which is DOMPurify-sanitized.
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
            btn.addEventListener('click', () => deleteItem(state.selected));
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
            // nosemgrep: openemr-js-innerhtml-dynamic -- startNum/endNum are integers from itemsPerPage * currentPage; state.items.length is a Number.
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
                // nosemgrep: openemr-js-innerhtml-dynamic -- renderMessageBody() runs DOMPurify.sanitize() with the html profile and FORBID_TAGS=['a','img'].
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
            $('#modalCompose .modal-header .modal-title').text(config.strings.composeReplyTitle);
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
            $('#modalCompose .modal-header .modal-title').text(config.strings.composeNewTitle);
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
        } catch {
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

    // Test seam: exposes pure helpers for unit tests. Not part of the public
    // surface — production code never reads from here.
    if (typeof window !== 'undefined') {
        window.__OE_MESSAGES_TEST__ = {
            escapeHtml,
            htmlToText,
            limitTo,
            renderMessageBody
        };
    }
})();
