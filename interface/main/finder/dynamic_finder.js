/**
 * dynamic_finder.js
 *
 * Client behaviour for the Patient Finder (dynamic_finder.php): initialises the
 * server-side DataTable, wires the per-column search, row hover/click navigation,
 * and the "open in new tab" / "exact search" controls. All server-provided values
 * (column definitions, translations, page size, CSRF token, webroot, the
 * search_any term) arrive via the inline `window.finderConfig` object the page
 * emits; no markup or server values are hard-coded here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function () {
    'use strict';

    var cfg = window.finderConfig || {};
    var columns = cfg.columns || [];
    var detail = cfg.detail || {};

    function escapeAttr(text) {
        return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // The button that expands a row to show the Patient Finder Detail layout.
    function detailToggle() {
        var label = escapeAttr(detail.showLabel || '');
        return '<button type="button" class="btn btn-link btn-sm p-0 mr-1 finder-detail-toggle"'
            + ' aria-expanded="false" title="' + label + '" aria-label="' + label + '">'
            + '<i class="fa fa-fw fa-caret-right" aria-hidden="true"></i></button>';
    }

    // DataTables mRender callback for a column: the Name column renders as a link, and the
    // first column carries the expand button when the detail layout shows anything. Cell
    // data arrives HTML-escaped from the server.
    function cellRenderer(column, index) {
        return function (data, type) {
            if (type !== 'display') {
                return data;
            }
            var html = column.link ? '<a href="">' + data + '</a>' : data;
            return index === 0 && detail.enabled ? detailToggle() + html : html;
        };
    }

    function setExpanded(button, expanded) {
        var label = expanded ? detail.hideLabel : detail.showLabel;
        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        button.setAttribute('title', label || '');
        button.setAttribute('aria-label', label || '');
        $(button).find('i').toggleClass('fa-caret-right', !expanded).toggleClass('fa-caret-down', expanded);
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
            },
            // dom invokes ColReorderWithResize and allows inclusion of a custom div
            "dom": 'Rlfrt<"mytopdiv">ip',
            // Column names come over as $_GET['sColumns']; the Name column renders as a link.
            "columns": columns.map(function (c, idx) {
                var col = {"sName": c.key};
                if (c.width) {
                    col.width = c.width;
                }
                if (c.link || (idx === 0 && detail.enabled)) {
                    col.mRender = cellRenderer(c, idx);
                }
                return col;
            }),
            "order": columns.map(function (c, idx) {
                return [idx, c.sortDir];
            }),
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": cfg.pageLength || 10,
            "language": cfg.language || {}
        });

        // The top controls form is rendered (hidden) by the Twig template; move it
        // into the DataTables-created div and wire the exact-search toggle.
        $("div.mytopdiv").append($("#finder-topform form"));
        $("#setting_search_type").on('change', function (e) {
            persistCriteria(this, e);
        });

        // Column-specific search fields (borrowed from the multi_filter.html example).
        $("thead input").keyup(function () {
            oTable.fnFilter(this.value, $("thead input").index(this));
        });

        $('#pt_table').on('mouseenter', 'tbody tr', function () {
            $(this).find('a').css('text-decoration', 'underline');
        });
        $('#pt_table').on('mouseleave', 'tbody tr', function () {
            $(this).find('a').css('text-decoration', '');
        });
        // The expand button shows the row's detail instead of opening the patient.
        $('#pt_table').on('click', '.finder-detail-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDetail(oTable, this);
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
