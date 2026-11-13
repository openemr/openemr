/**
 * dynamic_finder.js
 *
 * Client behaviour for the Patient Finder (dynamic_finder.php): initialises the
 * server-side DataTables for the Patient List and Recent Patients tabs, wires the
 * heading's search box, search-mode menu and page-length menu to whichever tab is
 * showing, and per table the column search, row click navigation and row-detail
 * toggle, plus the "open in new tab" / "exact search" controls and Customize. All
 * server-provided values (column definitions, translations, page size, CSRF token,
 * webroot, the search_any term) arrive via the inline `window.finderConfig` object
 * the page emits; no markup or server values are hard-coded here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function () {
    'use strict';

    var cfg = window.finderConfig || {};
    var detail = cfg.detail || {};
    var recent = cfg.recent || {};
    // With row details on, a narrow first column holds each row's expand button, so
    // every finder column sits one place to the right.
    var columnOffset = detail.enabled ? 1 : 0;
    // How long typing in the heading's search box pauses before the list reloads.
    var SEARCH_DELAY_MS = 300;
    // The shared Admin tab the menu opens Lists in, and the tab Customize uses when
    // that one shows another admin page.
    var ADMIN_TAB = 'adm';
    var LIST_EDITOR_TAB = 'lst';
    var LIST_EDITOR_TABS = [ADMIN_TAB, LIST_EDITOR_TAB];

    // The finder's two tabs. finderList tells the server which tab asks; listId is the
    // column list Customize opens for it.
    var TABS = {
        list: {
            key: 'list',
            tableId: 'pt_table',
            searchRowId: 'advanced_search',
            columns: cfg.columns || [],
            finderList: 'patient',
            listId: 'ptlistcols',
            // Sorted by every column in its configured direction.
            sortedAtStart: true,
            searchAny: cfg.searchAny || ''
        },
        recent: {
            key: 'recent',
            tableId: 'recent_table',
            searchRowId: 'recent_advanced_search',
            columns: recent.columns || [],
            finderList: 'recent',
            listId: recent.listId || 'recent_patient_columns',
            // Most recent first, until a column title is clicked.
            sortedAtStart: false,
            emptyText: recent.emptyText || '',
            searchAny: ''
        }
    };

    // What the heading's search box and menu hold; each table searches with it when it draws.
    var search = {term: '', mode: 'all'};
    // The DataTable of each tab, made the first time its tab shows.
    var tables = {};
    // The search each table last drew with, so switching tabs redraws only when it changed.
    var drawnSearch = {};
    var activeTab = TABS.list;

    function escapeAttr(text) {
        return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // The button that expands a row to show the Patient Finder Detail layout. CSS draws
    // it as a blue square with a right arrow, and red with a down arrow while open.
    function detailToggle() {
        var label = escapeAttr(detail.showLabel || '');
        return '<button type="button" class="finder-detail-toggle"'
            + ' aria-expanded="false" title="' + label + '" aria-label="' + label + '"></button>';
    }

    // DataTables mRender callback for the Name column, which renders as a link. Cell data
    // arrives HTML-escaped from the server.
    function linkRenderer(data, type) {
        return type === 'display' ? '<a href="">' + data + '</a>' : data;
    }

    // A tab's DataTables column config. The expand-button column is client-only: the
    // server has no column by that name, so it returns an empty cell in its place. A
    // table that starts unsorted sorts a column in its configured direction first.
    function dataTableColumns(tab) {
        var config = tab.columns.map(function (c) {
            var col = {"sName": c.key};
            if (c.width) {
                col.width = c.width;
            }
            if (c.link) {
                col.mRender = linkRenderer;
            }
            if (!tab.sortedAtStart) {
                col.orderSequence = c.sortDir === 'desc' ? ['desc', 'asc'] : ['asc', 'desc'];
            }
            return col;
        });
        if (detail.enabled) {
            config.unshift({
                "sName": 'finder_detail',
                "className": 'finder-detail-control',
                "orderable": false,
                "searchable": false,
                "mRender": function (data, type) {
                    return type === 'display' ? detailToggle() : '';
                }
            });
        }
        return config;
    }

    // DataTables puts each column's width on the column-title cells only. While the
    // search boxes take the titles' place that row is hidden, and the browser ignores a
    // hidden row when sizing columns, so the search-box cells carry the widths too.
    function copyWidthsToSearchRow(tab, config) {
        var cells = $('#' + tab.searchRowId + ' > td');
        config.forEach(function (col, idx) {
            if (col.width) {
                cells.eq(idx).css('width', col.width);
            }
        });
    }

    function setExpanded(button, expanded) {
        var label = expanded ? detail.hideLabel : detail.showLabel;
        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        button.setAttribute('title', label || '');
        button.setAttribute('aria-label', label || '');
    }

    // Show or hide a row's detail, fetching it the first time. DataTables child rows span
    // every column, however many the site shows.
    function toggleDetail(oTable, button) {
        var tr = $(button).closest('tr');
        var row = oTable.api().row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            setExpanded(button, false);
            return;
        }
        if (row.child() && row.child().length) {
            row.child.show();
            setExpanded(button, true);
            return;
        }
        var pid = (tr.attr('id') || '').substring(4);
        if (pid.length === 0) {
            return;
        }
        // A redraw while the request is out replaces the table's rows, and the row API
        // would then point at whichever patient now sits in that place.
        var stillShown = function () {
            return document.body.contains(tr[0]) && tr.attr('id') === 'pid_' + pid;
        };
        top.restoreSession();
        fetch(detail.url + '?' + new URLSearchParams({detail: pid}), {credentials: 'same-origin'})
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('detail request failed: ' + response.status);
                }
                return response.json();
            })
            .then(function (body) {
                if (!stillShown()) {
                    return;
                }
                row.child('<div class="finder-detail">' + (body.html || '') + '</div>').show();
                setExpanded(button, true);
            })
            .catch(function () {
                if (!stillShown()) {
                    return;
                }
                var message = $('<div class="finder-detail text-danger"></div>').text(detail.errorText || '');
                row.child(message).show();
                setExpanded(button, true);
            });
    }

    function openNewTopWindow(pid) {
        document.fnew.patientID.value = pid;
        top.restoreSession();
        document.fnew.submit();
    }

    function openPatient(pid) {
        if (document.myform.form_new_window.checked) {
            openNewTopWindow(pid);
            return;
        }
        top.restoreSession();
        var params = new URLSearchParams({set_pid: pid, csrf_token_form: cfg.csrfToken});
        top.RTop.location = cfg.webroot + "/interface/patient_file/summary/demographics.php?" + params;
    }

    // Whether "Search with exact method" is ticked.
    function isExactSearch() {
        return $("#setting_search_type:checked").length > 0;
    }

    // Redraw a tab's table with the heading's search when it last drew with another.
    function applySearch(tab) {
        var oTable = tables[tab.key];
        var last = drawnSearch[tab.key];
        if (!oTable || (last && last.term === search.term && last.mode === search.mode
            && last.exact === isExactSearch())) {
            return;
        }
        oTable.api().search(search.term).draw();
    }

    // The heading's search box searches the showing tab's list as the user types,
    // matching what its menu has picked: every column, or one kind of field such as the
    // name, phone or address.
    function wireHeadingSearch() {
        var $input = $('#finder-search-input');
        var timer = null;
        $input.on('input', function () {
            var term = this.value;
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                search.term = term;
                applySearch(activeTab);
            }, SEARCH_DELAY_MS);
        });
        $('#finder-search [data-search-mode]').on('click', function () {
            var $choice = $(this);
            search.mode = $choice.data('searchMode');
            $choice.siblings().removeClass('active').removeAttr('aria-current');
            $choice.addClass('active').attr('aria-current', 'true');
            $input.attr('placeholder', $choice.data('placeholder'));
            if (search.term !== '') {
                applySearch(activeTab);
            }
            $input.trigger('focus');
        });
    }

    // The Font Size menu sets the lists' text size and keeps it as the user's setting.
    function wireFontSize() {
        $('#finder-option-buttons [data-font-size]').on('click', function () {
            var $choice = $(this);
            var size = $choice.data('fontSize');
            $choice.siblings().removeClass('active').attr('aria-current', 'false');
            $choice.addClass('active').attr('aria-current', 'true');
            $('#finderTabs').attr('data-finder-font-size', size);
            top.restoreSession();
            $.post(cfg.webroot + '/library/ajax/user_settings.php', {
                target: cfg.fontSizeSetting,
                setting: size,
                csrf_token_form: cfg.csrfToken
            });
        });
    }

    // Open Administration > Lists at the given list. A tab already showing the List
    // editor is reused; so is the shared Admin tab when it isn't open. Otherwise the
    // editor opens in a tab of its own, so another admin page isn't replaced.
    function openListEditor(listId) {
        var url = cfg.webroot + '/interface/super/edit_list.php?' + new URLSearchParams({list_id: listId});
        var tabs = window.top;
        if (typeof tabs.navigateTab !== 'function') {
            window.open(url, '_blank');
            return;
        }
        var name = LIST_EDITOR_TABS.find(function (tabName) {
            return listEditorShownIn(tabs, tabName);
        });
        if (!name) {
            name = tabs.document.querySelector("iframe[name='" + ADMIN_TAB + "']") ? LIST_EDITOR_TAB : ADMIN_TAB;
        }
        tabs.navigateTab(url, name, function () {
            tabs.activateTabByName(name, true);
        });
    }

    function listEditorShownIn(tabs, tabName) {
        var frame = tabs.document.querySelector("iframe[name='" + tabName + "']");
        if (!frame) {
            return false;
        }
        try {
            return frame.contentWindow.location.pathname.indexOf('/interface/super/edit_list.php') !== -1;
        } catch {
            return false; // not readable yet, or from elsewhere
        }
    }

    // Persist the "exact search" preference as a user setting.
    function persistCriteria(el, e) {
        if (e) {
            e.preventDefault();
        }
        var target = cfg.uspfx + 'patient_finder_exact_search';
        var val = el.checked ? ' checked' : ' ';
        top.restoreSession();
        $.post(cfg.webroot + '/library/ajax/user_settings.php', {
            target: target,
            setting: val,
            csrf_token_form: cfg.csrfToken
        });
    }

    // The Recent Patients search row follows the Patient List's, which OemrUI's search
    // icon shows and hides.
    function syncSearchRow() {
        var hidden = $('#' + TABS.list.searchRowId).hasClass('d-none');
        $('#' + TABS.recent.searchRowId).toggleClass('d-none', hidden);
    }

    // Show the showing tab's page-length menu in the heading bar.
    function showLengthMenu(tab) {
        $('#finder-length-slot .finder-length').each(function () {
            $(this).toggleClass('d-none', $(this).data('tab') !== tab.key);
        });
    }

    // Make a tab's DataTable and wire its column search, detail toggle and row clicks.
    function createTable(tab) {
        var serverUrl = 'dynamic_finder_ajax.php';
        if (tab.searchAny) {
            serverUrl += '?search_any=' + encodeURIComponent(tab.searchAny);
        }
        var columnConfig = dataTableColumns(tab);
        var language = cfg.language || {};
        if (tab.emptyText) {
            language = $.extend({}, language, {"emptyTable": tab.emptyText});
        }
        var $table = $('#' + tab.tableId);
        var oTable = $table.dataTable({
            "processing": true,
            // next 2 lines invoke server side processing
            "serverSide": true,
            // NOTE kept the legacy command 'sAjaxSource' here for now since was unable to get
            // the new 'ajax' command to work.
            "sAjaxSource": serverUrl,
            "fnServerParams": function (aoData) {
                var searchType = isExactSearch();
                aoData.push({"name": "searchType", "value": searchType});
                aoData.push({"name": "searchMode", "value": search.mode});
                aoData.push({"name": "finderList", "value": tab.finderList});
                drawnSearch[tab.key] = {term: search.term, mode: search.mode, exact: searchType};
            },
            // dom invokes ColReorderWithResize. The heading's own search box replaces
            // DataTables' filter, and the options sit under the tables outside DataTables.
            "dom": 'Rlrtip',
            // Column names come over as $_GET['sColumns']; the Name column renders as a link.
            "columns": columnConfig,
            // Keep the expand-button column first when columns are dragged.
            "colReorder": {"fixedColumnsLeft": columnOffset},
            // Let CSS size the table to its container; DataTables' computed pixel width
            // overflowed it by a pixel. Column widths set in the column list still apply.
            "autoWidth": false,
            "order": tab.sortedAtStart
                ? tab.columns.map(function (c, idx) {
                    return [idx + columnOffset, c.sortDir];
                })
                : [],
            "search": {"search": search.term},
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": cfg.pageLength || 10,
            "language": language
        });
        tables[tab.key] = oTable;

        copyWidthsToSearchRow(tab, columnConfig);

        // The page-length menu goes into the heading bar, next to the tabs. Like the
        // OemrUI search icon, it shows from medium screens up.
        var $length = $('#' + tab.tableId + '_length').addClass('d-none d-md-initial');
        $('<div class="finder-length"></div>').attr('data-tab', tab.key).append($length)
            .appendTo('#finder-length-slot');

        // Column-specific search fields (borrowed from the multi_filter.html example). A
        // field's cell position is its column's index, counting the expand-button column.
        $table.find('thead input').on('keyup', function () {
            oTable.fnFilter(this.value, $(this).closest('td').index());
        });

        // A click anywhere in the expand-button cell shows the row's detail instead of
        // opening the patient.
        $table.on('click', 'td.finder-detail-control', function (e) {
            var button = $(this).find('.finder-detail-toggle').get(0);
            if (!button) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            toggleDetail(oTable, button);
        });

        // A row opens its patient. Its id is pid_{value}; the "No matching records
        // found" row has none, so it opens nothing.
        $table.on('click', 'tbody tr', function (e) {
            var pid = this.id.substring(4);
            if (pid.length > 0) {
                // The name link has an empty href, which would reload the finder.
                e.preventDefault();
                openPatient(pid);
            }
        });
        return oTable;
    }

    // Switch the heading's controls to a tab, making its table the first time it shows.
    // Recent Patients reloads each time, since opening a patient changes it.
    function showTab(tab) {
        activeTab = tab;
        if (!tables[tab.key]) {
            syncSearchRow();
            createTable(tab);
        } else if (tab === TABS.recent) {
            tables[tab.key].api().search(search.term).draw(false);
        } else {
            applySearch(tab);
        }
        showLengthMenu(tab);
    }

    $(function () {
        createTable(TABS.list);
        showLengthMenu(TABS.list);
        wireHeadingSearch();

        $('#listTab').on('shown.bs.tab', function () {
            showTab(TABS.list);
        });
        $('#recentTab').on('shown.bs.tab', function () {
            showTab(TABS.recent);
        });

        // OemrUI's search icon shows and hides the Patient List's search row.
        new MutationObserver(syncSearchRow).observe(
            document.getElementById(TABS.list.searchRowId),
            {attributes: true, attributeFilter: ['class']}
        );

        wireFontSize();
        $('#finder-customize').on('click', function () {
            openListEditor(activeTab.listId);
        });
        $("#setting_search_type").on('change', function (e) {
            persistCriteria(this, e);
            // The showing list's matches change with the method; the other tab redraws
            // when it is shown, since it last drew with the other method.
            var oTable = tables[activeTab.key];
            if (oTable) {
                oTable.api().draw();
            }
        });
    });
})();
