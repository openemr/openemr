/**
 * MedEx Module - Patient Tracker Injection
 * Enhances core patient tracker (Flow Board) with MedEx campaign features
 *
 * Icons are populated INSTANTLY from inline data embedded by the PHP shutdown
 * function (window.medex_appointment_data). No AJAX delay.
 * AJAX fallback exists only for edge cases (dynamic row additions).
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

(function($) {

    /**
     * SMS_bot function - Opens two-way SMS interface
     * Moved from core patient_tracker.php to MedEx module
     */
    window.SMS_bot = function(pid) {
        top.restoreSession();
        var from = $('#form_from_date').val() || '';
        var to = $('#form_to_date').val() || '';
        var params = new URLSearchParams({
            nomenu: '1', go: 'SMS_bot', pid: pid, to: to, from: from
        });
        window.open('../main/messages/messages.php?' + params.toString(),
            'SMS_bot', 'width=370,height=600,resizable=0');
        return false;
    };

    /**
     * Populate icons from inline data — called by the inline <script> after
     * setting window.medex_appointment_data, and also on $(document).ready().
     * No AJAX needed — icons appear with the page.
     */
    window.medexPopulateIcons = function() {
        var data = window.medex_appointment_data;
        if (!data) return;

        $('.pt-comm-status').each(function() {
            var $c = $(this);
            if ($c.data('medex-done')) return; // already populated

            var eid = String($c.data('eid') || '');
            var pid = String($c.data('pid') || '');
            var date = String($c.data('date') || '');
            if (!eid || !pid || !date) return;

            var campaignHtml = (data.campaigns && data.campaigns[eid]) || '';
            var modalityHtml = (data.modalities && data.modalities[pid]) || '';
            var html = '';

            if (campaignHtml) {
                html = campaignHtml;
            } else if (modalityHtml) {
                var dateSquash = date.replace(/-/g, '');
                html = "<span style='font-size:0.7rem;' onclick='return calendarpopup(" +
                       JSON.stringify(eid) + "," + JSON.stringify(dateSquash) + ")'>" +
                       modalityHtml + "</span>";
            }

            if (html) {
                $c.html(html);
                $c.data('medex-done', true);
            }
        });
    };

    /**
     * AJAX fallback for a single appointment — only used when inline data is missing
     * (e.g. dynamically added rows not covered by the initial data embed)
     */
    function fetchCampaignStatusAjax(eid, pid, date, $container) {
        $.ajax({
            url: '../modules/custom_modules/oe-module-medex/public/ajax/get_campaign_status.php',
            method: 'POST',
            data: {
                csrf_token_form: window.medex_csrf_token,
                eid: eid, pid: pid, date: date
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) return;
                var html = '';
                if (response.html && response.html.length > 0) {
                    html = response.html;
                } else if (response.modalities && response.modalities.length > 0) {
                    html = "<span style='font-size:0.7rem;' onclick='return calendarpopup(" +
                           JSON.stringify(String(eid)) + "," +
                           JSON.stringify(String(date).replace(/-/g, '')) + ")'>" +
                           response.modalities + "</span>";
                }
                if (html) {
                    $container.html(html);
                    $container.data('medex-done', true);
                }
            }
        });
    }

    /**
     * Check for any .pt-comm-status divs not yet populated and try inline data,
     * falling back to AJAX only for truly missing entries.
     */
    function populateRemaining() {
        var data = window.medex_appointment_data;
        $('.pt-comm-status').each(function() {
            var $c = $(this);
            if ($c.data('medex-done')) return;

            var eid = String($c.data('eid') || '');
            var pid = String($c.data('pid') || '');
            var date = String($c.data('date') || '');
            if (!eid || !pid || !date) return;

            // Try inline data first
            if (data) {
                var campaignHtml = (data.campaigns && data.campaigns[eid]) || '';
                var modalityHtml = (data.modalities && data.modalities[pid]) || '';
                if (campaignHtml || modalityHtml) {
                    var html = campaignHtml || "<span style='font-size:0.7rem;' onclick='return calendarpopup(" +
                               JSON.stringify(eid) + "," + JSON.stringify(date.replace(/-/g, '')) + ")'>" +
                               modalityHtml + "</span>";
                    $c.html(html);
                    $c.data('medex-done', true);
                    return;
                }
            }

            // No inline data — AJAX fallback
            fetchCampaignStatusAjax(eid, pid, date, $c);
        });
    }

    /**
     * Inject MedEx navigation/status link
     */
    function injectNavigation() {
        var $nav = $('#pt_custom_navigation');
        if ($nav.length === 0 || !window.medex_is_admin || !window.medex_is_enabled) return;

        var statusText = window.medex_online ? 'On-line' : 'Off-line';
        var statusClass = window.medex_online ? 'text-success' : 'text-danger';

        var html = '<b>MedEx:</b> ' +
            '<a href="../modules/custom_modules/oe-module-medex/admin/settings.php" ' +
            'class="' + statusClass + '">' + statusText + '</a>';

        if (window.medex_update_available) {
            var color = '#17a2b8', icon = 'fa-info-circle';
            if (window.medex_update_priority === 'CRITICAL')  { color = '#dc3545'; icon = 'fa-exclamation-circle'; }
            else if (window.medex_update_priority === 'SECURITY')  { color = '#ffc107'; icon = 'fa-shield-alt'; }
            else if (window.medex_update_priority === 'IMPORTANT') { color = '#ff9800'; icon = 'fa-exclamation-triangle'; }

            html += ' <span style="display:inline-block;background:' + color +
                ';color:white;padding:2px 6px;border-radius:3px;font-size:11px;margin-left:8px;">' +
                '<i class="fa ' + icon + '"></i> ' +
                '<a href="../modules/custom_modules/oe-module-medex/admin/backups.php" ' +
                'style="color:white;text-decoration:none;">Update: v' + window.medex_update_version + '</a></span>';
        }

        $nav.html(html);
    }

    // --- Initialization ---

    $(function() {
        injectNavigation();

        // Populate from inline data if already available (normal full-page load)
        if (window.medex_appointment_data) {
            window.medexPopulateIcons();
        }

        // Safety net: catch any rows the MutationObserver misses
        var debounceTimer = null;
        var observer = new MutationObserver(function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(populateRemaining, 100);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });

})(jQuery);
