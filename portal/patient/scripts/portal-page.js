/**
 * Page helpers shared by the portal/patient pages: `app` (alerts, the progress bar, HTML
 * escaping) and `view` (`<template>` copies, the pager, the document list's active filter).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
var app = (function () {
    'use strict';

    const HTML_ESCAPES = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;',
        '`': '&#x60;'
    };

    return {
        /**
         * Show a message with the page's async alert, or a browser alert where there is none.
         *
         * @param {string} message
         * @param {string} style the alert style, such as 'danger'
         * @param {number} timeout milliseconds; below 1000 means 15 seconds
         */
        appendAlert: function (message, style, timeout) {
            if (!message) {
                return;
            }
            if (timeout < 1000) {
                timeout = 15000;
            }
            if (typeof asyncAlertMsg !== 'undefined') {
                if (message.includes("Site ID is missing from session data")) {
                    parent.parent.location.replace(top.webroot_url + "/portal/verify_session.php" + '?w&u');
                }
                asyncAlertMsg(message, timeout, style);
            } else {
                alert(message);
            }
        },

        /**
         * Show the progress bar with the id elementId.
         */
        showProgress: function (elementId) {
            $('#' + elementId).show();
            $('#' + elementId).animate({width: '150'}, 'fast');
        },

        /**
         * Hide the progress bar with the id elementId.
         */
        hideProgress: function (elementId) {
            setTimeout(function () {
                $('#' + elementId).hide();
            }, 100);
            $('#' + elementId).animate({width: '0'}, 'fast');
        },

        /**
         * Escape a value for HTML text or a quoted attribute. null and undefined become ''.
         *
         * @param {*} unsafe
         * @returns {string}
         */
        escapeHtml: function (unsafe) {
            if (unsafe === null || unsafe === undefined) {
                return '';
            }
            return String(unsafe).replace(/[&<>"'`]/g, function (ch) {
                return HTML_ESCAPES[ch];
            });
        },

        /**
         * A date value for moment(). An empty value gives defaultDate ('' unless passed).
         */
        parseDate: function (str, defaultDate) {
            if (str instanceof Date) {
                return str;
            }
            if (str === '' || typeof str === 'undefined') {
                return typeof defaultDate === 'undefined' ? '' : defaultDate;
            }
            return str;
        },

        /**
         * The message to show for a failed API call: the response body as sent.
         *
         * @param {{responseText?: string}|null} error a PortalApiError
         * @returns {string}
         */
        getErrorMessage: function (error) {
            return (error && error.responseText) || '';
        }
    };
}());

/**
 * Rendering helpers: `<template>` copies, the pager and the document list's active filter.
 */
var view = (function () {
    'use strict';

    /**
     * Whole numbers only, so the pager markup holds nothing from the response but digits.
     */
    function pageNumber(value) {
        const number = parseInt(value, 10);
        return Number.isNaN(number) ? 0 : number;
    }

    return {
        /**
         * A copy of the content of the <template> element with the id templateId.
         *
         * @param {string} templateId
         * @returns {DocumentFragment}
         */
        copyTemplate: function (templateId) {
            return document.getElementById(templateId).content.cloneNode(true);
        },

        /**
         * Replace the content of the element with the id containerId by a copy of a <template>.
         *
         * @param {string} templateId
         * @param {string} containerId
         * @returns {HTMLElement} the container
         */
        renderTemplate: function (templateId, containerId) {
            const container = document.getElementById(containerId);
            container.replaceChildren(this.copyTemplate(templateId));
            return container;
        },

        /**
         * The pager for a list with more than one page: buttons with the ids page-1, page-2, …
         * Built as elements, not HTML, so nothing from the response is parsed as markup.
         *
         * @param {{totalPages: *, currentPage: *}} page
         * @param {boolean} top the pager above the list, rather than below it
         * @returns {DocumentFragment} the pager, or an empty fragment for a single page
         */
        pagination: function (page, top = false) {
            const totalPages = pageNumber(page.totalPages);
            const currentPage = pageNumber(page.currentPage);
            const pager = document.createDocumentFragment();
            if (totalPages <= 1) {
                return pager;
            }

            let first = 1;
            let last = totalPages;
            if (last > 10) {
                first = currentPage - 5;
                first = first > 1 ? first : 1;

                last = last > (first + 9) ? (first + 9) : last;
            }

            const nav = document.createElement('span');
            nav.setAttribute('aria-label', 'Page navigation');
            const list = document.createElement('span');
            list.className = top ? 'pagination float-left m-0 mb-1' : 'pagination float-right';
            nav.append(list);

            /**
             * A page button: its label, and the page it opens.
             */
            const button = function (label, target, active = false) {
                const item = document.createElement('li');
                if (active) {
                    item.className = 'active';
                }
                const link = document.createElement('a');
                link.className = 'pageButton btn btn-sm btn-outline-secondary';
                link.id = 'page-' + target;
                link.href = '#';
                link.textContent = label;
                item.append(link);
                list.append(item);
            };
            if (first > 1) {
                button('«', first - 1);
            }
            for (let i = first; i <= last; i++) {
                button(String(i), i, currentPage === i);
            }
            if (last < totalPages) {
                button('»', last + 1);
            }

            pager.append(nav);
            return pager;
        },

        /**
         * The "show active only" checkbox of the document list.
         *
         * @param {boolean} isActiveOnly
         * @returns {HTMLElement}
         */
        activeFilter: function (isActiveOnly) {
            const showActive = localStorage.getItem('showActive') === 'true';
            const filter = document.createElement('span');
            filter.className = 'table-active-filter float-right';
            const inner = document.createElement('span');
            inner.className = 'show-active-filter';
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = 'active-checkbox';
            checkbox.className = 'active-only-checkbox';
            checkbox.checked = isActiveOnly;
            label.append(checkbox, ' ', showActive ? '(Show Active Status)' : '(Show All)');
            inner.append(label);
            filter.append(inner);
            return filter;
        }
    };
}());
