/**
 * secure_chat.js
 *
 * Client-side logic for the patient portal secure chat. PHP-side values
 * (translations, session-derived user identity, mode flags) are passed in via
 * window.OE_SECURE_CHAT_CONFIG, which secure_chat.php emits before this file
 * loads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/* global DOMPurify, CKEDITOR */

(function () {
    'use strict';

    const config = window.OE_SECURE_CHAT_CONFIG || {};

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
        user: config.user,
        userid: String(config.userid),
        isPortal: !!config.isPortal,
        noRecipError: config.noRecipError,
        clickTitle: config.clickTitle,
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

    // Escapes for both HTML text and HTML attribute contexts. textContent on
    // its own only entity-escapes < > and &; we also escape " and ' so that
    // values interpolated into double- or single-quoted attribute values
    // can't break out and inject new attributes (CWE-79). The chat-message
    // and recipient-list rendering interpolates user-supplied content into
    // attributes like data-sender-id="${...}", so attribute-safe output is
    // mandatory.
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function safeUrl(url) {
        try {
            const u = new URL(url, window.location.origin);
            if (['http:', 'https:'].includes(u.protocol)) {
                return u.toString();
            }
        } catch {
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

    async function parseJsonOrText(response) {
        // secure_chat action endpoints return JSON for reads and plain text
        // for writes. Try JSON first, fall back to the raw text so write
        // callers don't crash on a non-JSON success body.
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch {
            return text;
        }
    }

    async function postData(url) {
        const response = await fetch(url, { method: 'POST', credentials: 'same-origin' });
        return parseJsonOrText(response);
    }

    async function postForm(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: data
        });
        return parseJsonOrText(response);
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
        // nosemgrep: openemr-js-innerhtml-dynamic -- every ${} interpolation goes through escapeHtml().
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
        // nosemgrep: openemr-js-innerhtml-dynamic -- every ${} interpolation goes through escapeHtml(); `checked` is the literal 'checked' or ''.
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
            const clickTitle = state.clickTitle;
            const sanitizedBody = sanitizeHtml(message.message);

            html += `<div class="direct-chat-msg ${alignClass}">
                <div class="direct-chat-info clearfix">
                    <span class="direct-chat-name ${nameFloat}">${escapeHtml(message.username)}</span>
                    <span class="direct-chat-timestamp ${timeFloat}">${escapeHtml(message.date)}</span>
                </div>
                <i class="direct-chat-img fa ${handIcon}" style="cursor: pointer; font-size: 24px" data-sender-id="${escapeHtml(message.sender_id)}" data-is-me="${isMe ? '1' : '0'}" title="${escapeHtml(clickTitle)}"></i>
                <div class="direct-chat-text right">
                    <div class='px-0' title="${escapeHtml(clickTitle)}" data-sender-id="${escapeHtml(message.sender_id)}" data-is-me="${isMe ? '1' : '0'}">${sanitizedBody}</div>
                </div>
            </div>`;
        });
        // nosemgrep: openemr-js-innerhtml-dynamic -- ${} interpolations use escapeHtml(); sanitizedBody = DOMPurify.sanitize(message.message).
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
        // nosemgrep: openemr-js-innerhtml-dynamic -- the only ${} interpolation goes through escapeHtml().
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

    // Test seam: exposes pure helpers for unit tests. Not part of the public
    // surface — production code never reads from here.
    if (typeof window !== 'undefined') {
        window.__OE_SECURE_CHAT_TEST__ = {
            escapeHtml,
            safeUrl,
            replaceShortcodes,
            sanitizeHtml,
            unique
        };
    }
})();
