/**
 * dynamic_finder.js
 *
 * Client behaviour for the Patient Finder (dynamic_finder.php): initialises the
 * server-side DataTable, wires the heading's search box and search-mode menu, the
 * per-column search, row click navigation, the row-detail toggle, and the
 * "open in new tab" / "exact search" controls. All server-provided values
 * (column definitions, translations, page size, CSRF token, webroot, the
 * search_any term) arrive via the inline `window.finderConfig` object the page
 * emits; no markup or server values are hard-coded here.
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
    var columns = cfg.columns || [];
    var detail = cfg.detail || {};
    // With row details on, a narrow first column holds each row's expand button, so
    // every finder column sits one place to the right.
    var columnOffset = detail.enabled ? 1 : 0;
    // How long typing in the heading's search box pauses before the list reloads.
    var SEARCH_DELAY_MS = 300;

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

    // The DataTables column config. The expand-button column is client-only: the server
    // has no column by that name, so it returns an empty cell in its place.
    function dataTableColumns() {
        var config = columns.map(function (c) {
            var col = {"sName": c.key};
            if (c.width) {
                col.width = c.width;
            }
            if (c.link) {
                col.mRender = linkRenderer;
            }
            return col;
        });
        if (detail.enabled) {
            config.unshift({
                "sName": 'finder_detail',
                "className": 'finder-detail-control',
                "width": '1.75rem',
                "orderable": false,
                "searchable": false,
                "mRender": function (data, type) {
                    return type === 'display' ? detailToggle() : '';
                }
            });
        }
        return config;
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
        top.restoreSession();
        fetch(detail.url + '?' + new URLSearchParams({pid: pid}), {credentials: 'same-origin'})
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('detail request failed: ' + response.status);
                }
                return response.json();
            })
            .then(function (body) {
                row.child('<div class="finder-detail">' + (body.html || '') + '</div>').show();
                setExpanded(button, true);
            })
            .catch(function () {
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

    // The heading's search box searches the list as the user types, matching what its
    // menu has picked: every column, the name, or the ZIP.
    function wireHeadingSearch(oTable, state) {
        var $input = $('#finder-search-input');
        var timer = null;
        $input.on('input', function () {
            var term = this.value;
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                oTable.api().search(term).draw();
            }, SEARCH_DELAY_MS);
        });
        $('#finder-search [data-search-mode]').on('click', function () {
            var $choice = $(this);
            state.searchMode = $choice.data('searchMode');
            $choice.siblings().removeClass('active').removeAttr('aria-current');
            $choice.addClass('active').attr('aria-current', 'true');
            $input.attr('placeholder', $choice.data('placeholder'));
            if ($input.val() !== '') {
                oTable.api().draw();
            }
            $input.trigger('focus');
        });
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

    $(function () {
        var serverUrl = 'dynamic_finder_ajax.php';
        if (cfg.searchAny) {
            serverUrl += '?search_any=' + encodeURIComponent(cfg.searchAny);
        }

        var searchState = {searchMode: 'all'};
        var oTable = $('#pt_table').dataTable({
            "processing": true,
            // next 2 lines invoke server side processing
            "serverSide": true,
            // NOTE kept the legacy command 'sAjaxSource' here for now since was unable to get
            // the new 'ajax' command to work.
            "sAjaxSource": serverUrl,
            "fnServerParams": function (aoData) {
                var searchType = $("#setting_search_type:checked").length > 0;
                aoData.push({"name": "searchType", "value": searchType});
                aoData.push({"name": "searchMode", "value": searchState.searchMode});
            },
            // dom invokes ColReorderWithResize and adds a div under the table for the
            // options form. The heading's own search box replaces DataTables' filter.
            "dom": 'Rlrt<"finder-options">ip',
            // Column names come over as $_GET['sColumns']; the Name column renders as a link.
            "columns": dataTableColumns(),
            // Keep the expand-button column first when columns are dragged.
            "colReorder": {"fixedColumnsLeft": columnOffset},
            // Let CSS size the table to its container; DataTables' computed pixel width
            // overflowed it by a pixel. Column widths set in Patient List Columns still apply.
            "autoWidth": false,
            "order": columns.map(function (c, idx) {
                return [idx + columnOffset, c.sortDir];
            }),
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": cfg.pageLength || 10,
            "language": cfg.language || {}
        });

        // The page-length menu goes into the heading bar, next to the tabs.
        $('#finder-length-slot').append($('#pt_table_length'));
        wireHeadingSearch(oTable, searchState);

        // The options form is rendered (hidden) by the Twig template; move it
        // under the table and wire the exact-search toggle.
        $("div.finder-options").append($("#finder-topform form"));
        $("#setting_search_type").on('change', function (e) {
            persistCriteria(this, e);
        });

        // Column-specific search fields (borrowed from the multi_filter.html example). A
        // field's cell position is its column's index, counting the expand-button column.
        $("thead input").keyup(function () {
            oTable.fnFilter(this.value, $(this).closest('td').index());
        });

        // A click anywhere in the expand-button cell shows the row's detail instead of
        // opening the patient.
        $('#pt_table').on('click', 'td.finder-detail-control', function (e) {
            var button = $(this).find('.finder-detail-toggle').get(0);
            if (!button) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            toggleDetail(oTable, button);
        });

        // OnClick handler for the rows
        $('#pt_table').on('click', 'tbody tr', function () {
            // ID of a row element is pid_{value}
            var newpid = this.id.substring(4);
            // The "No matching records found" row has no valid ID but is otherwise
            // clickable; guard against setting an invalid PID.
            if (newpid.length === 0) {
                return;
            }
            if (document.myform.form_new_window.checked) {
                openNewTopWindow(newpid);
            } else {
                top.restoreSession();
                const params = new URLSearchParams({set_pid: newpid, csrf_token_form: cfg.csrfToken});
                top.RTop.location = cfg.webroot
                    + "/interface/patient_file/summary/demographics.php?" + params;
            }
        });
    });
})();
