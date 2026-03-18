<?php

/**
 * Recall Board Display Service
 * Core recall board display functionality
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2024-2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\RecallBoard;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class DisplayService
{
    private string $rcb_selectors;
    private string $rcb_facility;
    private string $rcb_provider;

    public function __construct(string $rcb_selectors = 'block', string $rcb_facility = '', string $rcb_provider = '')
    {
        $this->rcb_selectors = $rcb_selectors;
        $this->rcb_facility = $rcb_facility;
        $this->rcb_provider = $rcb_provider;
    }

    public function display_recalls()
    {
        $rcb_selectors = $this->rcb_selectors;
        $rcb_facility = $this->rcb_facility;
        $rcb_provider = $this->rcb_provider;

        // Initialize form variables
        $form_patient_id = $_REQUEST['form_patient_id'] ?? '';
        $form_patient_name = $_REQUEST['form_patient_name'] ?? '';
        $setting_selectors = $_SESSION['setting_selectors'] ?? 'block';

        // Get date range - convert from display format to Y-m-d
        $from_date = date('Y-m-d', strtotime('-6 months'));
        if (!empty($_REQUEST['form_from_date'])) {
            $parsed = date_create($_REQUEST['form_from_date']);
            if ($parsed) {
                $from_date = date_format($parsed, 'Y-m-d');
            }
        }

        $globals = OEGlobalsBag::getInstance();
        $ptkr_end_date = (string) $globals->get('ptkr_end_date');

        if (str_starts_with($ptkr_end_date, 'Y')) {
            $ptkr_time = substr($ptkr_end_date, 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d'), date('Y') + $ptkr_time);
        } elseif (str_starts_with($ptkr_end_date, 'M')) {
            $ptkr_time = substr($ptkr_end_date, 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m') + $ptkr_time, date('d'), date('Y'));
        } elseif (str_starts_with($ptkr_end_date, 'D')) {
            $ptkr_time = substr($ptkr_end_date, 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d') + $ptkr_time, date('Y'));
        } else {
            $ptkr_future_time = mktime(0, 0, 0, date('m') + 3, date('d'), date('Y'));
        }
        $to_date = date('Y-m-d', $ptkr_future_time);
        if (!empty($_REQUEST['form_to_date'])) {
            $parsed = date_create($_REQUEST['form_to_date']);
            if ($parsed) {
                $to_date = date_format($parsed, 'Y-m-d');
            }
        }

        $recalls = $this->get_recalls($from_date, $to_date);
        $processed = $this->recall_board_process($recalls);

        ob_start();
        ?>

    <div class="container mt-3">
        <div class="row" id="rcb_selectors" style="display: <?php echo attr($rcb_selectors); ?>">
            <div class="col-12 text-center">
                <h2><?php echo xlt('Recall Board'); ?></h2>
                <p class="text-danger"><?php echo xlt('Persons needing a recall, no appt scheduled yet.'); ?></p>
            </div>
            <div class="col-12 jumbotron p-4">
                <div class="showRFlow text-center" id="show_recalls_params">
                    <form name="rcb" id="rcb" method="post">
                        <input type="hidden" name="go" value="Recalls" />
                        <div class="text-center row align-items-center">
                            <div class="col-sm-4 text-center mt-3">
                                <div class="form-group row justify-content-center mx-sm-1">
                                    <select class="form-control form-control-sm" id="form_facility" name="form_facility"
                                        <?php
                                        $facilities = QueryUtils::fetchRecords("SELECT * FROM facility ORDER BY id");
                                        $select_facs = '';
                                        $count_facs = 0;
                                        foreach ($facilities as $fac) {
                                            $true = ($fac['id'] == $rcb_facility) ? "selected=true" : '';
                                            $select_facs .= "<option value=" . attr($fac['id']) . " " . $true . ">" . text($fac['name']) . "</option>\n";
                                            $count_facs++;
                                        }
                                        if ($count_facs < '1') {
                                            echo "disabled";
                                        }
                                        ?>  onchange="show_this();">
                                        <option value=""><?php echo xlt('All Facilities'); ?></option>
                                        <?php echo $select_facs; ?>
                                    </select>
                                </div>
                                <div class="form-group row mx-sm-1">
                                    <input placeholder="<?php echo xla('Patient ID'); ?>" class="form-control form-control-sm text-center" type="text" id="form_patient_id" name="form_patient_id" value="<?php echo (!empty($form_patient_id)) ? attr($form_patient_id) : ""; ?>" onKeyUp="show_this();" />
                                </div>
                            </div>

                            <div class="col-sm-4 text-center mt-3">
                                <div class="form-group row mx-sm-1 justify-content-center">
                                    <?php
                                    $query = "SELECT id, lname, fname FROM users WHERE authorized = 1  AND active = 1 ORDER BY lname, fname";
                                    $providers = QueryUtils::fetchRecords($query);
                                    $count_provs = count($providers);
                                    ?>
                                    <select class="form-control form-control-sm" id="form_provider" name="form_provider" <?php if ($count_provs < '2') {
                                        echo "disabled"; } ?> onchange="show_this();">
                                        <option value="" selected><?php echo xlt('All Providers'); ?></option>
                                        <?php
                                        foreach ($providers as $urow) {
                                            $provid = $urow['id'];
                                            echo "<option value='" . attr($provid) . "'";
                                            if ($rcb_provider !== '' && $provid == ($_POST['form_provider'] ?? '')) {
                                                echo " selected";
                                            } elseif (!isset($_POST['form_provider']) && $_SESSION['userauthorized'] && $provid == $_SESSION['authUserID']) {
                                                echo " selected";
                                            }
                                            echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group row mx-sm-1">
                                    <input type="text" placeholder="<?php echo xla('Patient Name'); ?>" class="form-control form-control-sm text-center" id="form_patient_name" name="form_patient_name" value="<?php echo (!empty($form_patient_name)) ? attr($form_patient_name) : ""; ?>" onKeyUp="show_this();" />
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="input-append">
                                    <div class="form-group row mt-md-5">
                                        <label for="flow_from" class="col"><?php echo xlt('From'); ?>:</label>
                                        <div class="col">
                                            <input id="form_from_date" name="form_from_date" class="datepicker form-control form-control-sm text-center" value="<?php echo attr(oeFormatShortDate($from_date)); ?>" style="max-width: 140px; min-width: 85px;" />
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="flow_to" class="col">&nbsp;&nbsp;<?php echo xlt('To{{Range}}'); ?>:</label>
                                        <div class="col">
                                            <input id="form_to_date" name="form_to_date" class="datepicker form-control form-control-sm text-center" value="<?php echo attr(oeFormatShortDate($to_date)); ?>" style="max-width:140px;min-width:85px;">
                                        </div>
                                    </div>
                                    <div class="form-group row" role="group">
                                        <div class="col text-right">
                                            <button class="btn btn-primary btn-filter" type="submit" id="filter_submit" value="<?php echo xla('Filter'); ?>"><?php echo xlt('Filter'); ?></button>
                                            <button class="btn btn-primary btn-add" onclick="goReminderRecall('addRecall');return false;"><?php echo xlt('New Recall'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div name="message" id="message" class="warning"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center">
        <div class="showRecalls mx-auto" id="show_recalls">
            <div name="message" id="message" class="warning"></div>
            <span class="text-right fa-stack fa-lg pull_right small" id="rcb_caret" onclick="toggleRcbSelectors();" data-toggle="tooltip" data-placement="auto" title="Show/Hide the Filters" style="color: <?php echo $color = (!empty($setting_selectors) && ($setting_selectors == 'none')) ? 'var(--danger)' : 'var(--black)'; ?>; position: relative; float: right; right: 0; top: 0;">
                <i class="far fa-square fa-stack-2x"></i>
                <i id="print_caret" class='fas fa-caret-<?php echo $caret = ($rcb_selectors === 'none') ? 'down' : 'up'; ?> fa-stack-1x'></i>
            </span>

            <div class="tab-content">
               <div class="tab-pane active" id="tab-all">
                    <?php
                        $this->recall_board_top();
                        if (is_array($processed) && isset($processed['ALL'])) {
                            echo $processed['ALL'];
                        }
                        $this->recall_board_bot();
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleRcbSelectors() {
            if ($("#rcb_selectors").css('display') === 'none') {
                $.post( "<?php echo $globals->get('webroot') . "/interface/main/messages/messages.php"; ?>", {
                    'rcb_selectors' : 'block',
                    success: function (data) {
                        $("#rcb_selectors").slideToggle();
                        $("#rcb_caret").css('color','var(--black)');
                    }
                });
            } else {
                $.post( "<?php echo $globals->get('webroot') . "/interface/main/messages/messages.php"; ?>", {
                    'rcb_selectors' : 'none',
                    success: function (data) {
                        $("#rcb_selectors").slideToggle();
                        $("#rcb_caret").css('color','var(--danger)');
                    }
                });
            }
            $("#print_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
        }

        $(function () {
            show_this();

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($globals->get('srcdir') . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });

            // Initialize Bootstrap tooltips - let themes handle styling
            $('[data-toggle="tooltip"]').tooltip({
                boundary: 'window',
                sanitize: false,
                trigger: 'hover focus' // Standard Bootstrap triggers
            });

            // Make note tooltips sticky on click for scrolling long content
            $(document).on('click', '.note-tooltip', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $this = $(this);

                if ($this.hasClass('tooltip-sticky')) {
                    // Already sticky, clicking again closes it
                    // Remove classes FIRST before hiding (so hide.bs.tooltip event allows it)
                    $this.removeClass('tooltip-sticky tooltip-hovering');
                    $this.tooltip('hide');
                } else {
                    // Close any other open sticky tooltips first
                    $('.note-tooltip.tooltip-sticky').each(function() {
                        $(this).removeClass('tooltip-sticky tooltip-hovering');
                        $(this).tooltip('hide');
                    });

                    // Make this tooltip sticky - show it and prevent mouseout from hiding
                    $this.tooltip('show');
                    $this.addClass('tooltip-sticky');

                    // Make the tooltip itself clickable so we can scroll
                    setTimeout(function() {
                        var tooltipId = $this.attr('aria-describedby');
                        if (tooltipId) {
                            $('#' + tooltipId).on('mouseenter', function() {
                                $this.addClass('tooltip-hovering');
                            }).on('mouseleave', function() {
                                $this.removeClass('tooltip-hovering');
                            });
                        }
                    }, 10);
                }
            });

            // Prevent tooltip from closing when hovering over it (if sticky)
            $(document).on('hide.bs.tooltip', '.note-tooltip', function(e) {
                if ($(this).hasClass('tooltip-sticky') || $(this).hasClass('tooltip-hovering')) {
                    return false;
                }
            });

            // Close sticky tooltips when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.note-tooltip, .tooltip').length) {
                    $('.note-tooltip.tooltip-sticky').each(function() {
                        $(this).tooltip('hide');
                        $(this).removeClass('tooltip-sticky tooltip-hovering');
                    });
                }
            });
        });

        var translations = {
            patient_required: <?php echo xlj('Please select a patient'); ?>,
            date_required: <?php echo xlj('Please select a recall date'); ?>,
            provider_required: <?php echo xlj('Please select a provider'); ?>,
            facility_required: <?php echo xlj('Please select a facility'); ?>,
            no_recalls_found: <?php echo xlj('No Recalls Found'); ?>
        };
    </script>
        <!-- Postcard template modal -->
        <div class="modal" id="rcbPostcardModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo xlt('Postcard Preview and Template'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="postcardTemplateText"><?php echo xlt('Template'); ?></label>
                                    <textarea id="postcardTemplateText" class="form-control" rows="4" style="min-height:120px;max-height:220px;resize:vertical;"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label><?php echo xlt('Available Variables (click to insert)'); ?></label>
                                <div class="variable-list" style="display:flex;flex-direction:column;gap:6px;max-height:200px;overflow:auto;padding-left:6px;">
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{patient_name}}">{{patient_name}}</button>
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{patient_address}}">{{patient_address}}</button>
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{patient_dob}}">{{patient_dob}}</button>
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{patient_phone}}">{{patient_phone}}</button>
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{practice_name}}">{{practice_name}}</button>
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{practice_phone}}">{{practice_phone}}</button>
                                    <button type="button" class="btn btn-light text-left rcb-var" data-var="{{practice_address}}">{{practice_address}}</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo xlt('Preview'); ?></label>
                            <div id="postcardPreview" class="postcard-wrapper" style="max-width:760px;margin:auto;">
                                <div class="postcard-back" id="postcardBack" style="position:relative;width:100%;padding-top:62%;background-size:cover;background-repeat:no-repeat;background-position:center;border:1px solid #ddd;border-radius:4px;overflow:hidden;">
                                    <div id="postcardMessage" style="position:absolute;left:6%;top:8%;width:48%;height:84%;overflow:auto;padding:12px;box-sizing:border-box;color:#000;font-size:14px;line-height:1.35;"></div>
                                    <div id="postcardAddress" style="position:absolute;right:6%;top:32%;width:34%;height:46%;overflow:auto;padding:12px;box-sizing:border-box;color:#000;font-size:13px;line-height:1.25;text-align:left;"></div>
                                    <div id="postcardStamp" style="position:absolute;right:6%;top:6%;width:12%;height:14%;border:2px dashed rgba(0,0,0,0.15);box-sizing:border-box;background:rgba(255,255,255,0.35);"></div>
                                </div>
                            </div>
                            <style>
                                /* Ensure scrollbars look reasonable inside modal preview */
                                .postcard-back::-webkit-scrollbar { height:6px; width:6px }
                                .postcard-back::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius:3px }
                                /* Recipient name styling per USPS address placement */
                                #postcardAddress .recipient-name { font-weight:700; font-size:14px; margin-bottom:6px }
                            </style>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt('Close'); ?></button>
                        <button type="button" class="btn btn-primary" id="savePostcardTemplateBtn"><?php echo xlt('Save Template'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <script>
                // Postcard template handling and visual overlay onto postcard back image
                var rcb_postcard_template = <?php echo json_encode($globals->get('recall_board_postcard_top') ?? ''); ?>;
                var rcb_practice_name = <?php echo json_encode(QueryUtils::fetchSingleValue("SELECT name FROM facility WHERE primary_business_entity='1' LIMIT 1", 'name', []) ?? ''); ?>;
                var rcb_practice_phone = <?php echo json_encode(QueryUtils::fetchSingleValue("SELECT phone FROM facility WHERE primary_business_entity='1' LIMIT 1", 'phone', []) ?? ''); ?>;
                var rcb_practice_address = <?php echo json_encode(QueryUtils::fetchSingleValue("SELECT CONCAT(street, '\n', city, ', ', state, ' ', postal_code) AS addr FROM facility WHERE primary_business_entity='1' LIMIT 1", 'addr', []) ?? ''); ?>;
                var postcardBackUrl = <?php echo json_encode($globals->get('webroot') . '/public/assets/images/postcard_back.svg'); ?>;

                function escapeHtml(unsafe) {
                    return String(unsafe)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
                }

                function renderPostcardPreview(template, patientName, patientAddress, patientPhone, patientDob) {
                    var out = template || '';

                    // Example patient data used when none provided
                    var exampleName = 'John Q. Public';
                    var exampleAddress = '123 Main St\nAnytown, ST 00000';
                    var examplePhone = '(555) 555-1212';
                    var exampleDob = '01/01/1970';

                    var pName = patientName || exampleName;
                    var pAddr = patientAddress || exampleAddress;
                    var pPhone = patientPhone || examplePhone;
                    var pDob = patientDob || exampleDob;

                    out = out.replace(/\{\{patient_name\}\}/g, pName);
                    out = out.replace(/\{\{patient_address\}\}/g, pAddr.replace(/\n/g, '\\n'));
                    out = out.replace(/\{\{patient_phone\}\}/g, pPhone);
                    out = out.replace(/\{\{patient_dob\}\}/g, pDob);
                    out = out.replace(/\{\{practice_name\}\}/g, rcb_practice_name || '{{practice_name}}');
                    out = out.replace(/\{\{practice_phone\}\}/g, rcb_practice_phone || '{{practice_phone}}');
                    out = out.replace(/\{\{practice_address\}\}/g, rcb_practice_address || '{{practice_address}}');

                    // Message (left side) - preserve line breaks
                    var messageHtml = escapeHtml(out).replace(/\n/g, '<br/>');

                    // Address (right side) - prefer patient address placeholder, else show example
                    var addrHtml = '<div class="recipient-name">' + escapeHtml(pName) + '</div>' + escapeHtml(pAddr).replace(/\n/g, '<br/>');

                    // Apply to overlay elements
                    $('#postcardBack').css('background-image', 'url(' + postcardBackUrl + ')');
                    $('#postcardMessage').html(messageHtml);
                    // Recipient name + address placed according to USPS guidance (right half)
                    $('#postcardAddress').html(addrHtml);
                }

                function openPostcardModal(patientName, patientAddr) {
                    $('#postcardTemplateText').val(rcb_postcard_template || "Hi, It is time to reschedule your annual exam with us! Please call us at {{practice_phone}}.");
                    renderPostcardPreview($('#postcardTemplateText').val(), patientName || 'Patient Name', patientAddr || null);
                    $('#rcbPostcardModal').modal('show');
                }

                $(document).on('input', '#postcardTemplateText', function() {
                    renderPostcardPreview($(this).val(), 'Patient Name');
                });

                // Insert variable into textarea at cursor when clicking the badge
                $(document).on('click', '.rcb-var', function(e) {
                    e.preventDefault();
                    var v = $(this).data('var');
                    var $ta = $('#postcardTemplateText');
                    // insert at cursor/caret
                    var ta = $ta.get(0);
                    if (ta && typeof ta.selectionStart === 'number') {
                        var start = ta.selectionStart;
                        var end = ta.selectionEnd;
                        var text = $ta.val();
                        var newText = text.substring(0, start) + v + text.substring(end);
                        $ta.val(newText);
                        // set caret after inserted text
                        ta.selectionStart = ta.selectionEnd = start + v.length;
                        $ta.trigger('input');
                        $ta.focus();
                    } else {
                        // fallback - append
                        $ta.val($ta.val() + v);
                        $ta.trigger('input');
                        $ta.focus();
                    }
                });

                $('#savePostcardTemplateBtn').on('click', function() {
                    var val = $('#postcardTemplateText').val();
                    $.post('<?php echo $globals->get('webroot') . "/interface/main/messages/save.php"; ?>', { action: 'save_postcard_template', postcard_top: val }, function(resp) {
                        try {
                            var j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            if (j.success) {
                                rcb_postcard_template = val;
                                $('#rcbPostcardModal').modal('hide');
                            } else {
                                alert('<?php echo xla('Save failed'); ?>');
                            }
                        } catch (e) { alert('<?php echo xla('Save failed'); ?>'); }
                    }).fail(function() { alert('<?php echo xla('Save failed'); ?>'); });
                });
        </script>
    <?php
        $content = ob_get_clean();
        echo $content;
    }

    private function get_recalls($from_date, $to_date)
    {
        // Use core recalls table
        $recallsTable = 'patient_recalls';

        $query = "SELECT * FROM {$recallsTable}, patient_data AS pat
                    WHERE pat.pid={$recallsTable}.r_pid AND
                    r_eventDate >= ? AND
                    r_eventDate <= ? AND
                    IFNULL(pat.deceased_date,0) = 0
                    ORDER BY r_eventDate ASC";
        $recalls = QueryUtils::fetchRecords($query, [$from_date, $to_date]);
        return !empty($recalls) ? $recalls : null;
    }

    private function recall_board_process($recalls)
    {
        $globals = OEGlobalsBag::getInstance();
        $showPostcards = $globals->get('recall_board_enable_postcards') ?? true;
        $showLabels = $globals->get('recall_board_enable_labels') ?? true;
        $facility = [];
        $provider = [];
        $count_facilities = 0;
        $count_providers = 0;
        $process = [];
        $process['ALL'] = '';

        if (empty($recalls)) {
            return false;
        }

        $facilities = QueryUtils::fetchRecords("SELECT id, name FROM facility WHERE service_location != 0");
        foreach ($facilities as $facrow) {
            $facility[$facrow['id']] = $facrow['name'];
            $count_facilities++;
        }

        $providers = QueryUtils::fetchRecords("SELECT * FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
        foreach ($providers as $prov) {
            $fname = $prov['fname'] ?? '';
            $provider[$prov['id']] = ($fname !== '' ? $fname[0] : '') . " " . ($prov['lname'] ?? '');
            if (!empty($prov['suffix'])) {
                $provider[$prov['id']] .= ', ' . $prov['suffix'];
            }
            $count_providers++;
        }

        foreach ($recalls as $recall) {
            // Auto-delete recall if appointment was made within 90 days of recall date and more than 16 hours ago
            // This keeps the board clean without requiring manual deletion for completed recalls
            $apptCheckRows = QueryUtils::fetchRecords(
                "SELECT pc_eid FROM openemr_postcalendar_events
                 WHERE pc_eventDate >= CURDATE()
                 AND pc_pid = ?
                 AND pc_eventDate > (? - INTERVAL 90 DAY)
                 AND pc_time > (CURDATE() - INTERVAL 16 HOUR)",
                [$recall['r_pid'], $recall['r_eventDate']]
            );
            $apptCheck = $apptCheckRows[0] ?? null;

            if ($apptCheck) {
                // Appointment made - auto-delete this recall
                RecallService::deleteRecall($recall['r_pid'], $recall['r_ID']);
                continue; // Skip displaying this recall
            }

            ob_start();
            $pname = $recall['fname'] . ' ' . $recall['lname'];
            echo '<div class="divTableRow ALL text-center"
                data-pid="' . attr($recall['r_pid']) . '"
                data-facility="' . attr($recall['r_facility']) . '"
                data-provider="' . attr($recall['r_provider']) . '"
                data-pname="' . attr($pname) . '"
                id="recall_' . attr($recall['r_pid']) . '">';

            $query = "SELECT cal.pc_eventDate,pat.DOB FROM openemr_postcalendar_events AS cal JOIN patient_data AS pat ON cal.pc_pid=pat.pid WHERE cal.pc_pid =? ORDER BY cal.pc_eventDate DESC LIMIT 1";
            $result2Rows = QueryUtils::fetchRecords($query, [$recall['r_pid']]);
            $result2 = $result2Rows[0] ?? [];
            $last_visit = $result2['pc_eventDate'] ?? null;
            if (empty($result2['DOB'] ?? '')) {
                $dobRows = QueryUtils::fetchRecords("Select DOB From patient_data Where `pid` = ?", [$recall['r_pid']]);
                $result2['DOB'] = $dobRows[0]['DOB'] ?? '';
            }
            $DOB = oeFormatShortDate($result2['DOB'] ?? '');
            $age = RecallService::getAge($result2['DOB'] ?? 0);

            echo '<div class="divTableCell"><a href="#" onclick="show_patient(\'' . attr($recall['r_pid']) . '\');"> ' . text($recall['fname']) . ' ' . text($recall['lname']) . '</a>';
            if ($globals->get('ptkr_show_pid')) {
                echo '<br /><span data-toggle="tooltip" data-placement="auto" title="' . xla("Patient ID") . '" class="small">' . xlt('PID') . ': ' . text($recall['r_pid']) . '</span>';
            }
            echo '<br /><span data-toggle="tooltip" data-placement="auto" title="' . xla("Most recent visit") . '" class="small">' . xlt("Last Visit") . ': ' . text(oeFormatShortDate($last_visit)) . '</span>';
            echo '<br /><span class="small" data-toggle="tooltip" data-placement="auto" title="' . xla("Date of Birth and Age") . '">' . xlt('DOB') . ': ' . text($DOB) . ' (' . $age . ')</span>';
            echo '</div>';

            echo '<div class="divTableCell appt_date">' . text(oeFormatShortDate($recall['r_eventDate']));
            if ($recall['r_reason'] > '') {
                echo '<br />' . text($recall['r_reason']);
            }
            if (isset($provider[$recall['r_provider']]) && strlen($provider[$recall['r_provider']]) > 14) {
                $provider[$recall['r_provider']] = substr($provider[$recall['r_provider']], 0, 14) . "...";
            }
            if (isset($facility[$recall['r_facility']]) && strlen((string) $facility[$recall['r_facility']]) > 20) {
                $facility[$recall['r_facility']] = substr((string) $facility[$recall['r_facility']], 0, 17) . "...";
            }

            if ($count_providers > '1' && isset($provider[$recall['r_provider']])) {
                echo "<br /><span data-toggle='tooltip' data-placement='auto'  title='" . xla('Provider') . "'>" . text($provider[$recall['r_provider']]) . "</span>";
            }
            if (( $count_facilities > '1' ) && ( $_REQUEST['form_facility'] == '' ) && isset($facility[$recall['r_facility']])) {
                echo "<br /><span data-toggle='tooltip' data-placement='auto'  title='" . xla('Facility') . "'>" . text($facility[$recall['r_facility']]) . "</span><br />";
            }

            echo '</div>';
            echo '<div class="divTableCell phones" id="contact_' . attr($recall['r_pid']) . '">';
            if ($recall['phone_cell'] > '') {
                echo 'C: ' . text($recall['phone_cell']) . "<br />";
            }
            if ($recall['phone_home'] > '') {
                echo 'H: ' . text($recall['phone_home']) . "<br />";
            }
            if ($recall['email'] > '') {
                $mailto = $recall['email'];
                if (strlen((string) $recall['email']) > 15) {
                    $recall['email'] = substr((string) $recall['email'], 0, 12) . "...";
                }
                echo 'E: <a data-toggle="tooltip" data-placement="auto" title="' . xla('Send an email to ') . attr($mailto) . '" href="mailto:' . attr($mailto) . '">' . text($recall['email']) . '</a><br />';
            }
            // Render core modality icons (phone, SMS, email) based on available contact
            // information.
            $icons = [];
            $phoneAllowed = (!empty($recall['phone_cell']) || !empty($recall['phone_home'])) && (($recall['hipaa_voice'] ?? '') !== 'NO');
            $icons[] = '<span class="rcb-mod-icon rcb-mod-phone' . ($phoneAllowed ? '' : ' rcb-mod-not-allowed') . '" title="' . ($phoneAllowed ? xla('Phone') : xla('Phone not available')) . '"><i class="fa fa-phone"></i></span>';

            $smsAllowed = !empty($recall['phone_cell']) && (($recall['hipaa_allowsms'] ?? '') !== 'NO');
            $icons[] = '<span class="rcb-mod-icon rcb-mod-sms' . ($smsAllowed ? '' : ' rcb-mod-not-allowed') . '" title="' . ($smsAllowed ? xla('SMS') : xla('SMS not available')) . '"><i class="fa fa-comment"></i></span>';

            $emailAllowed = !empty($recall['email']) && (($recall['hipaa_allowemail'] ?? '') !== 'NO');
            $icons[] = '<span class="rcb-mod-icon rcb-mod-email' . ($emailAllowed ? '' : ' rcb-mod-not-allowed') . '" title="' . ($emailAllowed ? xla('Email') : xla('Email not available')) . '"><i class="fa fa-envelope"></i></span>';

            echo '<div class="rcb-modalities" style="margin-top:6px;">' . implode('', $icons) . '</div>';

            echo '</div>';

            // Postcards checkbox column (conditionally rendered)
            if ($showPostcards) {
                echo '  <div class="divTableCell text-center postcards">';
                echo '<input type="checkbox" name="postcards" id="postcards_' . attr($recall['r_pid']) . '" value="' . attr($recall['r_pid']) . '" />';
                echo '</div>';
            }

            // Labels checkbox column (conditionally rendered)
            if ($showLabels) {
                echo '  <div class="divTableCell text-center labels">';
                echo '<input type="checkbox" name="labels" id="labels_' . attr($recall['r_pid']) . '" value="' . attr($recall['r_pid']) . '" />';
                echo '</div>';
            }

            // Office: Phone column - checkbox for phone call + schedule button
            echo '  <div class="divTableCell text-center msg_manual">';
            echo '<span class="fa fa-fw spaced_icon">';
            echo '<input type="checkbox" name="msg_phone" id="msg_phone_' . attr($recall['r_pid']) . '" onclick="process_this(\'phone\',\'' . attr($recall['r_pid']) . '\',\'' . attr($recall['r_ID']) . '\')" />';
            echo '</span>';
            echo '<span data-toggle="tooltip" data-placement="auto" title="' . xla('Scheduling') . '" class="fa fa-calendar-check-o fa-fw" onclick="newEvt(\'' . attr($recall['r_pid']) . '\',\'\');"></span>';
            echo '</div>';

            // Notes column - for adding new action notes
            echo '  <div class="divTableCell text-left notes_column">';
            // Blank textarea for adding new notes (reason is shown in Recall Info column)
            echo '<textarea onblur="process_this(\'notes\',\'' . attr($recall['r_pid']) . '\',\'' . attr($recall['r_ID']) . '\');" name="msg_notes" id="msg_notes_' . attr($recall['r_pid']) . '" placeholder="' . xla('Add note...') . '" style="width:98%;height:60px;"></textarea>';
            echo '</div>';

            /**
             * Status column - Core recall board actions (postcards, labels, phone, notes)
             *
             * MODULE INJECTION POINT:
             * External modules (e.g. recall campaigns) can inject additional status information
             * by using JavaScript to append content to this div: #status_{$pid}
             *
             * This allows modules to add campaign tracking (emails, SMS, automated calls) without
             * modifying core code.
             */
            echo '  <div class="divTableCell text-center status_column">';
            echo '<div class="status_content" id="status_' . attr($recall['r_pid']) . '">';
            $status = $this->getRecallActions($recall['r_pid']);
            echo $status ?: '&nbsp;';
            echo '</div>';
            echo '</div>';

            // Actions column - edit + delete (schedule is in Office: Phone column)
            echo '  <div class="divTableCell text-center actions_column" style="white-space:nowrap;">
            <i class="fa fa-pencil" style="cursor:pointer; margin-right:8px;" onclick="goReminderRecall(\'addRecall\',\'' . attr($recall['r_pid']) . '\');" title="' . xla('Edit Recall') . '"></i>
            <i class="fa fa-times" style="cursor:pointer;" onclick="delete_Recall(\'' . attr($recall['r_pid']) . '\',\'' . attr($recall['r_ID']) . '\');" title="' . xla('Delete Recall') . '"></i>';
            echo '</div>';
            echo '</div>';

            $content = ob_get_clean();
            $process['ALL'] .= $content;
        }
        return $process;
    }

    private function recall_board_top()
    {
        $globals = OEGlobalsBag::getInstance();
        $showPostcards = $globals->get('recall_board_enable_postcards') ?? true;
        $showLabels = $globals->get('recall_board_enable_labels') ?? true;

        // Allow any module to inject assets into the recall board by providing a 'recall_buttons_loader.php'
        $scan_dirs = [
            $globals->get('srcdir') . '/../modules',
            $globals->get('srcdir') . '/../interface/modules/custom_modules'
        ];
        
        foreach ($scan_dirs as $modules_dir) {
            if (is_dir($modules_dir)) {
                $modules = scandir($modules_dir);
                foreach ($modules as $mod) {
                    if ($mod === '.' || $mod === '..') {
                        continue;
                    }
                    $loader = $modules_dir . '/' . $mod . '/recall_buttons_loader.php';
                    if (file_exists($loader)) {
                        try {
                            include_once $loader;
                        } catch (\Throwable $t) {
                            // ignore if loader fails
                        }
                    }
                }
            }
        }
        ?>
        <div class="divTable" style="width: 100%;">
            <div class="divTableBody">
                <div class="divTableRow divTableHeading">
                    <div class="divTableCell text-center"><?php echo xlt('Patient'); ?></div>
                    <div class="divTableCell text-center"><?php echo xlt('Recall Info'); ?></div>
                    <div class="divTableCell text-center"><?php echo xlt('Contact'); ?></div>
                    <?php if ($showPostcards) : ?>
                    <div class="divTableCell text-center">
                        <?php echo xlt('Postcards'); ?>
                            <div class="postcard-icons-core" style="margin-top:6px;">
                                <i class="fa fa-envelope fa-fw" style="cursor:pointer; margin-right:8px;" onclick="openPostcardModal();" id="rcb_mail_icon" title="<?php echo xla('Mail/Postcards Template'); ?>"></i>
                                <i class="fa fa-square-o fa-fw" style="cursor:pointer;" onclick="checkAll('postcards',true);" id="chk_postcards" title="<?php echo xla('Select All'); ?>"></i>
                                <i class="fa fa-print fa-fw" style="cursor:pointer; margin-left:8px;" onclick="process_this('postcards');" title="<?php echo xla('Print Postcards'); ?>"></i>
                            </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($showLabels) : ?>
                    <div class="divTableCell text-center">
                        <?php echo xlt('Labels'); ?>
                        <div class="label-icons-core" style="margin-top:6px;">
                            <i class="fa fa-square-o fa-fw" style="cursor:pointer;" onclick="checkAll('labels',true);" id="chk_labels" title="<?php echo xla('Check All'); ?>"></i>
                            <i class="fa fa-print fa-fw" style="cursor:pointer;" onclick="process_this('labels');" title="<?php echo xla('Print Labels'); ?>"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="divTableCell text-center"><?php echo xlt('Office') . ": " . xlt('Phone'); ?></div>
                    <div class="divTableCell text-center"><?php echo xlt('Notes'); ?></div>
                    <div class="divTableCell text-center"><?php echo xlt('Status'); ?></div>
                    <div class="divTableCell text-center"><?php echo xlt('Actions'); ?></div>
                </div>
                <style>
                    /* Limit status column height to ~4 lines and make it scrollable */
                    .status_column .status_content {
                        max-height: calc(2.0rem * 4);
                        overflow-y: auto;
                        overflow-x: hidden;
                        display: flex;
                        flex-direction: column;
                        gap: 6px;
                        align-items: flex-start; /* newest-first alignment at top */
                        background-color: #f5f7f9; /* light gray background for status area */
                        padding: 6px; /* space for readability */
                        border-radius: 4px;
                        border: 1px solid rgba(0,0,0,0.04);
                    }
                    /* Ensure note tooltips and small text wrap nicely inside status column */
                    .status_column .status_content small { white-space: normal; text-align: left; }

                    /* Prevent email links and contact icons in Contact column from wrapping */
                    .divTable .phones a { white-space: nowrap; }
                    .divTable .phones { white-space: nowrap; overflow-x: auto; }

                    /* Prevent postcard/header icons from wrapping in core (non-module) */
                    .postcard-icons-core { display:flex; gap:6px; align-items:center; flex-wrap:nowrap; }

                    /* Core fallback modality icons (non-module): simple FA icons with denied overlay */
                    .rcb-modalities { display:flex; gap:6px; align-items:center; margin-top:6px; }
                    .rcb-mod-icon { position:relative; display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:6px; background:#f6f7f8; border:1px solid rgba(0,0,0,0.08); color:#222; }
                    .rcb-mod-icon .fa { font-size:14px; }
                    .rcb-mod-not-allowed::after {
                        content: '\00d7';
                        position:absolute;
                        right:-6px;
                        top:-6px;
                        width:18px;
                        height:18px;
                        line-height:18px;
                        text-align:center;
                        font-size:12px;
                        color:#fff;
                        background:#d9534f; /* bootstrap danger */
                        border-radius:50%;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
                    }

                    /* Ensure all Recall Board rows/cells are top-aligned */
                    .divTableRow .divTableCell { vertical-align: top; }
                </style>
                <script>
                    // Ensure status columns scroll to top on load so newest items (ordered DESC)
                    (function($){
                        $(function(){
                            $('.status_content').each(function(){
                                try { this.scrollTop = 0; } catch (e) {}
                            });
                            // Defensive: ensure cells render top-aligned in case CSS is overridden
                            $('.divTableRow .divTableCell').css('vertical-align','top');
                        });
                    })(jQuery);
                </script>
        <?php
    }

    private function recall_board_bot()
    {
        ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get recall board actions (postcards, labels, phone, notes) for a patient
     *
     * @param int $pid Patient ID
     * @return string HTML formatted status text
     */
    private function getRecallActions($pid)
    {
        $actionsTable = 'recall_board_actions';

        $sql = "SELECT msg_type, msg_date, msg_extra_text, msg_reply
            FROM {$actionsTable}
            WHERE msg_pc_eid = ?
            ORDER BY msg_date DESC";

        $results = QueryUtils::fetchRecords($sql, ['recall_' . $pid]);

        $actions = [];
        foreach ($results as $row) {
            $type = $row['msg_type'];
            $date = date('m/d/Y', strtotime($row['msg_date']));
            $time = date('g:iA', strtotime($row['msg_date']));
            $extraText = $row['msg_extra_text'] ?? '';
            $userId = $row['msg_reply'] ?? null;

            // Get user info if this was a manual action
            $userName = '';
            if ($userId && is_numeric($userId)) {
                $userRows = QueryUtils::fetchRecords("SELECT fname, lname FROM users WHERE id = ?", [$userId]);
                $user = $userRows[0] ?? null;
                if ($user) {
                    $userName = $user['fname'] . ' ' . $user['lname'];
                }
            }

            // Format label based on action type
            if ($type === 'postcards') {
                $label = 'Postcard';
            } elseif ($type === 'labels') {
                $label = 'Label';
            } elseif ($type === 'phone') {
                $label = 'Phone';
            } elseif ($type === 'notes') {
                $label = 'Note';
            } else {
                $label = ucfirst($type);
            }

            // Build action line with optional note content
            $actionLine = '<small><b>' . text($label) . ':</b> ' . text($date) . ' @ ' . text($time);

            // Add user name for manual actions
            if ($userName) {
                $actionLine .= '<br /><span style="font-size:0.9em; color:#888;">by ' . text($userName) . '</span>';
            }

            // For notes and phone calls, show the content with tooltip for longer text
            if (($type === 'notes' || $type === 'phone') && !empty($extraText)) {
                // Truncate at 40 characters for display
                $truncated = strlen($extraText) > 40 ? substr($extraText, 0, 37) . '...' : $extraText;

                // Use clickable tooltip for long content (allows scrolling)
                $tooltip = str_replace(["\r\n", "\n", "\r"], '&#10;', $extraText);
                $actionLine .= '<br /><span class="note-tooltip" style="font-style:italic; color:#666; cursor:pointer; text-decoration:underline dotted;" '
                             . 'data-toggle="tooltip" data-placement="left" data-html="true" '
                             . 'data-template=\'<div class="tooltip" role="tooltip"><div class="arrow"></div>'
                             . '<div class="tooltip-inner" style="max-width:400px; max-height:300px; overflow-y:auto; text-align:left; white-space:pre-wrap;"></div></div>\' '
                             . 'title="' . attr($tooltip) . '">'
                             . text($truncated) . '</span>';
            }

            $actionLine .= '</small>';
            $actions[] = $actionLine;
        }

        return !empty($actions) ? implode('<br />', $actions) : '';
    }
}
