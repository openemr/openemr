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
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function () {
    'use strict';

    var cfg = window.finderConfig || {};
    var columns = cfg.columns || [];

    // Render the Name column cell as a link (DataTables mRender callback).
    function wrapInLink(data, type) {
        if (type === 'display') {
            return '<a href="">' + data + '</a>';
        }
        return data;
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
            "columns": columns.map(function (c) {
                var col = {"sName": c.key};
                if (c.link) {
                    col.mRender = wrapInLink;
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
                top.RTop.location = cfg.webroot
                    + "/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
            }
        });
    });
})();
