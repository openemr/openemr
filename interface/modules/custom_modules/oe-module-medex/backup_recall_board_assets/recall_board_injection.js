/**
 * MedEx Module - Recall Board Injection
 * Enhances core recall board with MedEx campaign features
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

/**
 * This file is loaded when MedEx module is active.
 * It injects campaign status, communication tracking, and enhanced features
 * into the core OpenEMR recall board.
 */

$(document).ready(function() {
    console.log('[MedEx] Recall board injection script loaded');
    console.log('[MedEx] CSRF Token:', window.medex_csrf_token);

    /**
     * Hide Postcard/Label columns if disabled in settings
     */
    function hideDisabledColumns() {
        console.log('[MedEx] Checking column visibility settings...');
        console.log('[MedEx] Postcards enabled:', window.medex_postcards_enabled);
        console.log('[MedEx] Labels enabled:', window.medex_labels_enabled);

        // Hide Postcards column if disabled
        if (window.medex_postcards_enabled === false) {
            console.log('[MedEx] Hiding Postcards column (disabled in settings)');

            // Hide header
            $('.divTableHeading .divTableCell').each(function() {
                if ($(this).text().trim() === 'Postcards') {
                    $(this).hide();
                }
            });

            // Hide all postcard cells in rows (find by input name)
            $('input[name="postcards"]').each(function() {
                $(this).closest('.divTableCell').hide();
            });
        }

        // Hide Labels column if disabled
        if (window.medex_labels_enabled === false) {
            console.log('[MedEx] Hiding Labels column (disabled in settings)');

            // Hide header
            $('.divTableHeading .divTableCell').each(function() {
                if ($(this).text().trim() === 'Labels') {
                    $(this).hide();
                }
            });

            // Hide all label cells in rows (find by input name)
            $('input[name="labels"]').each(function() {
                $(this).closest('.divTableCell').hide();
            });
        }
    }

    /**
     * Convert checkboxes to toggle switches (MedEx UX enhancement)
     */
    function convertCheckboxesToToggles() {
        console.log('[MedEx] Converting checkboxes to toggle switches...');

        // Convert Postcards checkboxes
        $('input[name="postcards"]').each(function() {
            var $checkbox = $(this);
            if (!$checkbox.parent().hasClass('switch')) {
                $checkbox.wrap('<label class="switch"></label>');
                $checkbox.after('<span class="slider round"></span>');
            }
        });

        // Convert Labels checkboxes
        $('input[name="labels"]').each(function() {
            var $checkbox = $(this);
            if (!$checkbox.parent().hasClass('switch')) {
                $checkbox.wrap('<label class="switch"></label>');
                $checkbox.after('<span class="slider round"></span>');
            }
        });

        // Convert Office: Phone checkboxes
        $('input[name="msg_phone"]').each(function() {
            var $checkbox = $(this);
            if (!$checkbox.parent().hasClass('switch')) {
                $checkbox.wrap('<label class="switch"></label>');
                $checkbox.after('<span class="slider round"></span>');
            }
        });

        console.log('[MedEx] Checkboxes converted to toggle switches');
    }

    /**
     * Convert action icons to colored Bootstrap buttons (matching MedEx interface)
     */
    function convertActionIconsToButtons() {
        console.log('[MedEx] Converting action icons to colored Bootstrap buttons...');

        // Convert edit icons to blue Bootstrap buttons
        $('.actions_column .fa-pencil').each(function() {
            var $icon = $(this);
            if (!$icon.parent().hasClass('btn')) {
                var onclick = $icon.attr('onclick');
                var title = $icon.attr('title') || 'Edit';
                var $button = $('<span class="btn btn-sm btn-primary" data-toggle="tooltip" title="' + title + '" style="padding-right:10px; cursor:pointer;"><i class="fa fa-edit"></i></span>');
                $button.on('click', function(e) {
                    e.preventDefault();
                    if (onclick) eval(onclick);
                });
                $icon.replaceWith($button);
            }
        });

        // Convert delete icons to red Bootstrap buttons
        $('.actions_column .fa-times').each(function() {
            var $icon = $(this);
            if (!$icon.parent().hasClass('btn')) {
                var onclick = $icon.attr('onclick');
                var title = $icon.attr('title') || 'Delete';
                var $button = $('<span class="btn btn-sm btn-danger" data-toggle="tooltip" title="' + title + '" style="padding-right:10px; cursor:pointer;"><i class="fa fa-trash"></i></span>');
                $button.on('click', function(e) {
                    e.preventDefault();
                    if (onclick) eval(onclick);
                });
                $icon.replaceWith($button);
            }
        });

        console.log('[MedEx] Action icons converted to Bootstrap buttons');
    }

    /**
     * Change column headers to match MedEx styling
     */
    function updateColumnHeaders() {
        console.log('[MedEx] Updating column headers...');

        // Change "Office: Phone" to just "Office"
        $('.divTableHeading .divTableCell').each(function() {
            var $cell = $(this);
            if ($cell.text().trim() === 'Office: Phone') {
                $cell.text('Office');
            }
        });

        console.log('[MedEx] Column headers updated');
    }


    /**
     * Enhance Office column with scheduler icon only
     */
    function enhanceOfficeColumn() {
        console.log('[MedEx] Enhancing Office column with scheduler icon...');

        $('.divTableRow[data-pid]').each(function() {
            var $row = $(this);
            var pid = $row.data('pid');
            var $officeCell = $row.find('.msg_manual');

            if ($officeCell.length && !$officeCell.data('medex-enhanced')) {
                // Mark as enhanced to avoid duplicate processing
                $officeCell.data('medex-enhanced', true);

                // Preserve original calendar onclick
                var $calendarIcon = $officeCell.find('.fa-calendar-check-o');
                var calendarOnclick = $calendarIcon.attr('onclick') || '';

                // Clear existing content and rebuild
                $officeCell.html('<div></div>');
                var $container = $officeCell.find('div');

                // Calendar icon - open scheduler (Bootstrap info cyan)
                var $calendarBtn = $('<button type="button" class="btn btn-info medex-scheduler" data-pid="' + pid + '" data-toggle="tooltip" title="Schedule appointment">' +
                    '<i class="fa fa-calendar-check-o"></i>' +
                    '</button>');

                $calendarBtn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Trigger original calendar onclick
                    if (calendarOnclick) {
                        eval(calendarOnclick);
                    }
                });

                $container.append($calendarBtn);
            }
        });

        console.log('[MedEx] Office column enhanced with scheduler');
    }

    /**
     * Enhance Contact column with communication method icons
     * Icons shown based on patient contact info and MedEx campaign permissions
     * Will be updated with actual permissions when campaign data is loaded
     */
    function enhanceContactColumn() {
        console.log('[MedEx] Enhancing Contact column with communication icons...');

        $('.divTableRow[data-pid]').each(function() {
            var $row = $(this);
            var pid = $row.data('pid');
            var $contactCell = $row.find('[id^="contact_"]');
            var $notesTextarea = $row.find('#msg_notes_' + pid);
            var $phoneCheckbox = $row.find('#msg_phone_' + pid);

            if ($contactCell.length && !$contactCell.data('medex-enhanced')) {
                // Mark as enhanced to avoid duplicate processing
                $contactCell.data('medex-enhanced', true);

                // Create container for communication icons (horizontal layout)
                var $iconsContainer = $('<div class="medex-communication-icons"></div>');

                // 1. Phone icon - will be styled based on campaign permissions (checked in updateContactIcons)
                var phoneNumber = $contactCell.text().match(/C:\s*(\d{3}-\d{3}-\d{4})|H:\s*(\d{3}-\d{3}-\d{4})/);
                if (phoneNumber) {
                    var $phoneIcon = $('<button type="button" class="btn btn-sm btn-secondary medex-comm-phone" data-pid="' + pid + '" data-toggle="tooltip" title="Document phone call (checking permissions...)">' +
                        '<i class="fa fa-phone"></i>' +
                        '</button>');

                    $phoneIcon.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var $btn = $(this);

                        // Toggle phone call documentation mode
                        if ($btn.hasClass('btn-danger')) {
                            $btn.removeClass('btn-danger').addClass($btn.data('base-color') || 'btn-secondary');
                            $notesTextarea.css('background-color', '');
                            $phoneCheckbox.prop('checked', false);
                        } else {
                            $btn.data('base-color', $btn.hasClass('btn-info') ? 'btn-info' : ($btn.hasClass('btn-warning') ? 'btn-warning' : 'btn-secondary'));
                            $btn.removeClass('btn-secondary btn-info btn-warning').addClass('btn-danger');
                            $notesTextarea.css('background-color', '#fff3cd');
                            $notesTextarea.focus();
                            $phoneCheckbox.prop('checked', true);
                        }
                    });

                    // Initialize state if checkbox is already checked
                    if ($phoneCheckbox.is(':checked')) {
                        $phoneIcon.removeClass('btn-secondary').addClass('btn-danger');
                        $notesTextarea.css('background-color', '#fff3cd');
                    }

                    $iconsContainer.append($phoneIcon);
                }

                // 2. SMS icon - will be enabled/disabled based on campaign permissions
                var $smsIcon = $('<button type="button" class="btn btn-sm btn-secondary medex-comm-sms" data-pid="' + pid + '" data-toggle="tooltip" title="SMS (checking permissions...)" disabled>' +
                    '<i class="fa fa-comment"></i>' +
                    '</button>');
                $iconsContainer.append($smsIcon);

                // 3. Email icon - will be enabled/disabled based on campaign permissions
                var $emailIcon = $('<button type="button" class="btn btn-sm btn-secondary medex-comm-email" data-pid="' + pid + '" data-toggle="tooltip" title="Email (checking permissions...)" disabled>' +
                    '<i class="fa fa-envelope"></i>' +
                    '</button>');
                $iconsContainer.append($emailIcon);

                // Append icons container to contact cell
                $contactCell.append('<br>');
                $contactCell.append($iconsContainer);

                // Initialize Bootstrap tooltips for all icons
                $iconsContainer.find('[data-toggle="tooltip"]').tooltip();
            }
        });

        console.log('[MedEx] Contact column enhanced with placeholder icons');
    }

    /**
     * Update Contact column communication icons based on campaign permissions
     * Called after campaign data is loaded with modality permissions
     */
    function updateContactIcons(pid, modalities) {
        var $contactCell = $('#contact_' + pid);
        if (!$contactCell.length || !modalities || !modalities.ALLOWED) {
            return;
        }

        console.log('[MedEx] Updating contact icons for PID ' + pid, modalities.ALLOWED);

        // Update Phone icon (uses AVM / hipaa_voice permission)
        var $phoneIcon = $contactCell.find('.medex-comm-phone');
        if ($phoneIcon.length) {
            if (modalities.ALLOWED.AVM === 'YES') {
                // Phone calls fully available - patient opted in to automated voice messages
                $phoneIcon
                    .removeClass('btn-secondary btn-warning').addClass('btn-info')
                    .attr('title', 'Document phone call - Patient allows automated voice messages')
                    .data('base-color', 'btn-info');
            } else {
                // Check if any phone exists (can still manually call)
                var hasPhone = $contactCell.text().match(/C:\s*(\d{3}-\d{3}-\d{4})|H:\s*(\d{3}-\d{3}-\d{4})/);
                if (hasPhone) {
                    // Has phone but declined automated voice - allow manual with warning
                    $phoneIcon
                        .removeClass('btn-secondary btn-info').addClass('btn-warning')
                        .attr('title', 'Document phone call - WARNING: Patient declined automated voice messages')
                        .data('base-color', 'btn-warning');
                } else {
                    // No phone - but this shouldn't happen since we check phone before creating icon
                    $phoneIcon
                        .removeClass('btn-info btn-warning').addClass('btn-secondary')
                        .attr('title', 'Document phone call')
                        .data('base-color', 'btn-secondary');
                }
            }
            // Refresh tooltip
            $phoneIcon.tooltip('dispose').tooltip();
        }

        // Update SMS icon
        var $smsIcon = $contactCell.find('.medex-comm-sms');
        if ($smsIcon.length) {
            if (modalities.ALLOWED.SMS === 'YES') {
                // SMS fully available - patient opted in to automated SMS
                $smsIcon
                    .prop('disabled', false)
                    .removeClass('btn-secondary btn-warning').addClass('btn-success')
                    .attr('title', 'Open SMS Bot - Patient allows automated SMS')
                    .off('click') // Remove old handler
                    .on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        openSMSBot(pid);
                    });
            } else {
                // Check if cell phone exists (can still manually SMS)
                var hasCellPhone = $contactCell.text().match(/C:\s*(\d{3}-\d{3}-\d{4})/);
                if (hasCellPhone) {
                    // Has cell but declined automated SMS - allow manual with warning
                    $smsIcon
                        .prop('disabled', false)
                        .removeClass('btn-secondary btn-success').addClass('btn-warning')
                        .attr('title', 'Open SMS Bot - WARNING: Patient declined automated SMS campaigns')
                        .off('click')
                        .on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            openSMSBot(pid);
                        });
                } else {
                    // No cell phone - completely disable
                    $smsIcon
                        .prop('disabled', true)
                        .removeClass('btn-success btn-warning').addClass('btn-secondary')
                        .attr('title', 'SMS: Not Available (no cell phone number on file)')
                        .off('click');
                }
            }
            // Refresh tooltip
            $smsIcon.tooltip('dispose').tooltip();
        }

        // Update Email icon
        var $emailIcon = $contactCell.find('.medex-comm-email');
        if ($emailIcon.length) {
            // Extract FULL email from the mailto link (not the abbreviated display text)
            var $emailLink = $contactCell.find('a[href^="mailto:"]');
            var email = null;
            if ($emailLink.length) {
                // Get email from href attribute (e.g., "mailto:john@example.com" -> "john@example.com")
                var href = $emailLink.attr('href');
                email = href ? href.replace('mailto:', '') : null;
            }

            if (modalities.ALLOWED.EMAIL === 'YES') {
                // Email fully available - patient opted in to automated email
                if (email) {
                    $emailIcon
                        .prop('disabled', false)
                        .removeClass('btn-secondary btn-warning').addClass('btn-primary')
                        .attr('title', 'Send email to ' + email + ' - Patient allows automated email')
                        .data('email', email)
                        .off('click') // Remove old handler
                        .on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            window.location.href = 'mailto:' + $(this).data('email');
                        });
                } else {
                    // No email address found
                    $emailIcon
                        .prop('disabled', true)
                        .removeClass('btn-primary btn-warning').addClass('btn-secondary')
                        .attr('title', 'Email: No email address on file')
                        .off('click');
                }
            } else {
                // Check if email exists (can still manually email)
                if (email) {
                    // Has email but declined automated email - allow manual with warning
                    $emailIcon
                        .prop('disabled', false)
                        .removeClass('btn-secondary btn-primary').addClass('btn-warning')
                        .attr('title', 'Send email to ' + email + ' - WARNING: Patient declined automated email campaigns')
                        .data('email', email)
                        .off('click')
                        .on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            window.location.href = 'mailto:' + $(this).data('email');
                        });
                } else {
                    // No email address - completely disable
                    $emailIcon
                        .prop('disabled', true)
                        .removeClass('btn-primary btn-warning').addClass('btn-secondary')
                        .attr('title', 'Email: Not Available (no email address on file)')
                        .off('click');
                }
            }
            // Refresh tooltip
            $emailIcon.tooltip('dispose').tooltip();
        }

        console.log('[MedEx] Contact icons updated for PID ' + pid);
    }

    /**
     * Open SMS Bot interface for patient
     * Opens a popup window showing MedEx SMS Bot from medexbank.com with authentication
     */
    function openSMSBot(pid) {
        console.log('[MedEx] Opening SMS Bot for PID:', pid);

        // Fetch MedEx authentication token first
        $.ajax({
            type: 'POST',
            url: '../../modules/custom_modules/oe-module-medex/public/ajax.php',
            data: {
                action: 'get_medex_token',
                csrf_token_form: window.medex_csrf_token || ''
            },
            dataType: 'json'
        }).done(function(result) {
            console.log('[MedEx] get_medex_token response:', result);
            if (result.success && result.token) {
                // Build authenticated SMS Bot URL with provider identification
                var url = result.server + '/cart/upload/index.php?route=information/TM/SMS&pid=' + pid +
                          '&token=' + encodeURIComponent(result.token) +
                          '&r=' + encodeURIComponent(result.display || '');

                // Add provider UID if available (identifies who's sending messages)
                if (result.provider_uid) {
                    url += '&P_UID=' + encodeURIComponent(result.provider_uid);
                }
                if (result.phone_style) {
                    url += '&phone_style=' + encodeURIComponent(result.phone_style);
                }

                console.log('[MedEx] Opening SMS Bot URL:', url);
                // Open in popup window sized to phone style
                var sizes = {
                    S8: { w: 360, h: 640 },
                    iPhone14: { w: 350, h: 760 },
                    iPhone4: { w: 350, h: 700 },
                    Pixel8: { w: 350, h: 760 },
                    minimal: { w: 420, h: 680 }
                };
                var styleKey = result.phone_style || 'S8';
                var size = sizes[styleKey] || sizes.S8;
                var features = 'width=' + size.w + ',height=' + size.h + ',resizable=1,scrollbars=0,toolbar=0,menubar=0,location=0,status=0';
                var popup = window.open(url, 'SMS_bot', features);
                if (!popup) {
                    alert('Popup blocked! Please allow popups for this site and try again.');
                }
            } else {
                console.error('[MedEx] Token request failed:', result);
                alert('Error: ' + (result.error || 'Unable to connect to MedEx. Please check your MedEx settings.'));
            }
        }).fail(function(xhr, status, error) {
            console.error('[MedEx] Failed to get MedEx token:', status, error);
            console.error('[MedEx] Response text:', xhr.responseText);
            alert('Error: Unable to connect to MedEx. Please check your MedEx settings.');
        });

        return false;
    }

    /**
     * Inject campaign status into Status column for each recall
     */
    function injectCampaignStatus() {
        console.log('[MedEx] Starting campaign status injection...');
        // Get campaign data for all visible recalls
        var pids = [];
        $('.divTableRow[data-pid]').each(function() {
            var pid = $(this).data('pid');
            if (pid) pids.push(pid);
        });

        console.log('[MedEx] Found PIDs:', pids);
        if (pids.length === 0) {
            console.log('[MedEx] No recalls found on page');
            return;
        }

        // Fetch campaign status from MedEx API
        console.log('[MedEx] Fetching campaign status from AJAX endpoint...');
        $.ajax({
            type: 'POST',
            url: '../../modules/custom_modules/oe-module-medex/public/ajax.php',
            data: {
                action: 'get_campaign_status',
                pids: JSON.stringify(pids),
                csrf_token_form: window.medex_csrf_token || ''
            },
            dataType: 'json'
        }).done(function(campaigns) {
            console.log('[MedEx] Received campaign data:', campaigns);

            // Safety check: if campaigns is empty or not an object, don't inject anything
            if (!campaigns || typeof campaigns !== 'object' || Object.keys(campaigns).length === 0) {
                console.log('[MedEx] No campaign data available - skipping injection');
                return;
            }

            // Inject modality icons and campaign info for each recall
            $.each(campaigns, function(pid, campaign) {
                // 1. Update contact icons (SMS/Email/Phone buttons) based on permissions
                var contactEl = $('#contact_' + pid);
                console.log('[MedEx] PID ' + pid + ' - Looking for #contact_' + pid + ', found: ' + contactEl.length);
                if (contactEl.length && campaign.modalities) {
                    console.log('[MedEx] PID ' + pid + ' modalities:', campaign.modalities);

                    // OLD: Inject legacy modality icons (strike-through red X icons)
                    // We no longer need these since the modern buttons show the same info with color coding
                    // var modalityHTML = buildModalityHTML(campaign.modalities);
                    // contactEl.append(modalityHTML);

                    // Update contact icons (SMS/Email/Phone buttons) based on permissions
                    updateContactIcons(pid, campaign.modalities);
                    console.log('[MedEx] PID ' + pid + ' - Contact icons updated');
                } else {
                    console.warn('[MedEx] PID ' + pid + ' - Contact element not found or no modalities');
                }

                // 2. Inject campaign toggle controls into Campaigns column
                injectCampaignControls(pid, campaign.modalities, campaign.events);

                // 3. Inject campaign status into Status column
                var statusEl = $('#status_' + pid);
                if (statusEl.length && campaign.events && campaign.events.length > 0) {
                    // Build campaign status HTML
                    var html = buildCampaignHTML(campaign);

                    // Append to existing content (ASC order - oldest first, newest at bottom)
                    var existing = statusEl.html();
                    if (existing && existing.trim() !== '' && existing !== '&nbsp;') {
                        statusEl.append('<br />' + html);
                    } else {
                        statusEl.html(html);
                    }
                    // Scroll to bottom to show most recent status
                    statusEl.scrollTop(statusEl[0].scrollHeight);
                }

                // 3. Add row background color based on campaign status
                var row = $('.divTableRow[data-pid="' + pid + '"]');
                row.removeClass('whitish greenish reddish yellowish');
                row.addClass(campaign.statusClass);
            });
        }).fail(function(xhr, status, error) {
            console.error('[MedEx] AJAX request failed:', status, error);
            console.error('[MedEx] Response:', xhr.responseText);
        });
    }

    /**
     * Build modality icons HTML for Contact column
     * Uses HTML from medex_icons table (same as original)
     */
    function buildModalityHTML(modalities) {
        // Original just concatenated: $pat['SMS'] . $pat['AVM'] . $pat['EMAIL']
        // Each contains HTML from medex_icons.i_html field
        return (modalities.SMS || '') + (modalities.AVM || '') + (modalities.EMAIL || '');
    }

    /**
     * Build campaign status HTML with event timeline (for Status column)
     */
    function buildCampaignHTML(campaign) {
        var html = '<div style="font-size:0.9em;">';

        // Show campaign events (QUEUED/SENT/READ/FAILED)
        if (campaign.events && campaign.events.length > 0) {
            campaign.events.forEach(function(event) {
                var date = new Date(event.date);
                var when = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear() +
                          ' @ ' + date.getHours() + ':' +
                          (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();

                var icon = getCampaignIcon(event.type, event.status);
                var statusLabel = event.status;

                html += '<small>' + icon + ' <b>' + event.type + ':</b> ' + statusLabel + ' ' + when + '</small><br />';
            });
        }

        html += '</div>';
        return html;
    }

    /**
     * Get appropriate icon for campaign status
     */
    function getCampaignIcon(type, status) {
        var icons = {
            'SMS': {
                'QUEUED': '<i class="fa fa-comment-o"></i>',
                'SENT': '<i class="fa fa-comment"></i>',
                'READ': '<i class="fa fa-comment text-success"></i>',
                'FAILED': '<i class="fa fa-comment text-danger"></i>'
            },
            'EMAIL': {
                'QUEUED': '<i class="fa fa-envelope-o"></i>',
                'SENT': '<i class="fa fa-envelope"></i>',
                'READ': '<i class="fa fa-envelope-open-o text-success"></i>',
                'FAILED': '<i class="fa fa-envelope text-danger"></i>'
            },
            'AVM': {
                'QUEUED': '<i class="fa fa-phone"></i>',
                'SENT': '<i class="fa fa-phone text-primary"></i>',
                'CALL': '<i class="fa fa-phone-square text-success"></i>',
                'FAILED': '<i class="fa fa-phone text-danger"></i>'
            }
        };

        if (!icons[type]) {
            console.warn('[MedEx] Unknown campaign type:', type);
            return '<i class="fa fa-question"></i>';
        }
        return icons[type][status] || '';
    }

    /**
     * Inject campaign toggle controls into Campaigns column
     * Creates toggle switches for SMS/EMAIL/AVM with MedEx styling
     */
    function injectCampaignControls(pid, modalities, events) {
        var $campaignsCell = $('#campaigns_' + pid);
        if (!$campaignsCell.length) return;

        var html = '<div class="medex-campaign-controls">';

        // Check which campaigns are currently active
        var activeCampaigns = {};
        if (events && events.length > 0) {
            events.forEach(function(event) {
                activeCampaigns[event.type] = true;
            });
        }

        // Create toggle switches for each modality
        ['SMS', 'EMAIL', 'AVM'].forEach(function(type) {
            var allowed = modalities.ALLOWED && modalities.ALLOWED[type] === 'YES';
            var active = activeCampaigns[type] || false;
            var disabled = !allowed;

            html += '<div class="campaign-toggle">';

            // Toggle switch
            html += '<label class="switch' + (disabled ? ' disabled' : '') + '">';
            html += '<input type="checkbox" class="campaign-enable" ';
            html += 'data-pid="' + pid + '" data-type="' + type + '" ';
            if (active) html += 'checked ';
            if (disabled) html += 'disabled ';
            html += '/>';
            html += '<span class="slider round"></span>';
            html += '</label>';

            // Label
            html += '<span class="modality-label">' + type + '</span>';

            html += '</div>';
        });

        html += '</div>';

        $campaignsCell.html(html);
    }

    /**
     * Handle campaign toggle switch changes
     */
    $(document).on('change', '.campaign-enable', function() {
        var $toggle = $(this);
        var pid = $toggle.data('pid');
        var type = $toggle.data('type');
        var enabled = $toggle.is(':checked');

        console.log('[MedEx] Toggle campaign:', type, 'for PID:', pid, 'enabled:', enabled);

        // TODO: Send to AJAX endpoint to enable/disable campaign
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'toggle_campaign',
                pid: pid,
                type: type,
                enabled: enabled
            },
            success: function(response) {
                console.log('[MedEx] Campaign toggled successfully');
                // Reload campaign data to update status
                loadCampaignData(ajaxUrl);
            },
            error: function(xhr, status, error) {
                console.error('[MedEx] Failed to toggle campaign:', error);
                // Revert toggle on error
                $toggle.prop('checked', !enabled);
            }
        });
    });

    /**
     * TODO: LOB.com Integration for Postcard/Label Mailing Service
     *
     * When postcards or labels are printed, detect the status column update
     * and offer option to send via LOB.com for actual physical mailing.
     *
     * LOB.com API: https://www.lob.com/
     * - Create postcard design
     * - Submit address and message
     * - LOB prints and mails the physical postcard
     * - Track delivery status
     *
     * Implementation:
     * 1. Detect when status column shows "Postcard: XX/XX/XX" or "Label: XX/XX/XX"
     * 2. Show modal: "Send via LOB.com for actual mailing?"
     * 3. If yes, call MedEx API endpoint: send_via_lob
     * 4. MedEx backend calls LOB.com API
     * 5. Update status column with LOB tracking info
     */
    function watchForPostcardLabelPrints() {
        // Use MutationObserver to watch for status column changes
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id && mutation.target.id.startsWith('status_')) {
                    var content = $(mutation.target).text();

                    // Detect postcard or label print
                    if (content.includes('Postcard:') || content.includes('Label:')) {
                        var pid = mutation.target.id.replace('status_', '');
                        var type = content.includes('Postcard') ? 'postcard' : 'label';

                        // Offer LOB.com mailing
                        offerLOBMailing(pid, type);
                    }
                }
            });
        });

        // Observe all status columns
        $('.divTableCell[id^="status_"]').each(function() {
            observer.observe(this, {
                childList: true,
                subtree: true,
                characterData: true
            });
        });
    }

    /**
     * Offer to send postcard/label via LOB.com
     */
    function offerLOBMailing(pid, type) {
        // TODO: Implement LOB.com integration
        // For now, just show placeholder

        var message = 'Send this ' + type + ' via LOB.com for actual mailing?\n\n' +
                      'LOB.com will print and mail the physical ' + type + ' to the patient.';

        if (confirm(message)) {
            sendViaLOB(pid, type);
        }
    }

    /**
     * Send postcard/label via LOB.com API
     */
    function sendViaLOB(pid, type) {
        $.ajax({
            type: 'POST',
            url: '../../modules/custom_modules/oe-module-medex/public/ajax.php',
            data: {
                action: 'send_via_lob',
                pid: pid,
                type: type,
                csrf_token_form: window.medex_csrf_token || ''
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                alert('Sent to LOB.com for mailing! Tracking ID: ' + result.tracking_id);

                // Update status column with LOB tracking (append = ASC order)
                var statusEl = $('#status_' + pid);
                var existing = statusEl.html();
                if (existing && existing.trim() !== '' && existing !== '&nbsp;') {
                    statusEl.append('<br /><small><b>LOB Tracking:</b> ' + result.tracking_id + '</small>');
                } else {
                    statusEl.html('<small><b>LOB Tracking:</b> ' + result.tracking_id + '</small>');
                }
                statusEl.scrollTop(statusEl[0].scrollHeight);
            } else {
                alert('Error sending to LOB.com: ' + result.error);
            }
        });
    }

    // Initialize when recall board loads
    if ($('.divTableRow[data-pid]').length > 0) {
        // Apply MedEx UX enhancements
        hideDisabledColumns();  // Hide columns first if disabled in settings
        updateColumnHeaders();
        convertCheckboxesToToggles();
        convertActionIconsToButtons();
        enhanceOfficeColumn();
        enhanceContactColumn();

        // Inject campaign status
        injectCampaignStatus();

        // Watch for postcard/label prints
        watchForPostcardLabelPrints();
    }

    // Re-inject when board is refreshed
    $(document).on('recall_board_refreshed', function() {
        hideDisabledColumns();  // Hide columns first if disabled in settings
        updateColumnHeaders();
        convertCheckboxesToToggles();
        convertActionIconsToButtons();
        enhanceOfficeColumn();
        enhanceContactColumn();
        injectCampaignStatus();
        watchForPostcardLabelPrints();
    });

});
