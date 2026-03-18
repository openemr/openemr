<?php

/**
 * Add/Edit Recall Form
 * Core recall form functionality
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2017-2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\RecallBoard;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class AddRecallForm
{
    public function display_add_recall($pid = 'new', array $result_pat = [], ?int $userid = null)
    {
        ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center" id="add_recall">
                <h2><?php echo ($pid !== 'new') ? xlt('Edit Recall') : xlt('New Recall'); ?></h2>
                <p class="text-danger" name="div_response" id="div_response"><?php echo xlt('Create a reminder to schedule a future visit.'); ?></p>
            </div>

            <form class="prefs p-4 row" name="addRecall" id="addRecall">
                <input type="hidden" name="go" id="go" value="addRecall" />
                <input type="hidden" name="action" id="go" value="addRecall" />
                <div class="col-4 divTable m-2 ml-auto">
                    <div class="row divTableBody prefs">
                            <div class="divTableCell divTableHeading text-right form-group col-4 col-md-4"><label><?php echo xlt('Name'); ?></label></div>
                            <div class="divTableCell indent20 form-group col-8 col-md-8">
                                <input type="text" name="new_recall_name" id="new_recall_name" class="form-control"
                                        onclick="recall_name_click(this)"
                                        value="<?php echo attr($result_pat['fname'] ?? '') . " " . attr($result_pat['lname'] ?? ''); ?>" />
                                <input type="hidden" name="new_pid" id="new_pid" value="<?php echo attr($result_pat['pid'] ?? ''); ?>" />
                            </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('DOB'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8">
                            <?php
                                $DOB = oeFormatShortDate($result_pat['DOB'] ?? '');
                            ?>
                            <span name="new_DOB" id="new_DOB" style="width: 90px;"><?php echo text($DOB); ?></span> -
                            <span id="new_age" name="new_age"><?php echo text($result_pat['age'] ?? ''); ?></span>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Recall When'); ?></label>
                        </div>
                        <div class="form-group col-8 col-md-8 divTableCell indent20">
                            <span class="font-weight-bold"><?php echo xlt('Last Visit'); ?>: </span>
                            <input type="text" value="" name="DOLV" id="DOLV" class="" />
                            <br />
                            <!-- Feel free to add in any dates you would like to show here...
                            <input type="radio" name="new_recall_when" id="new_recall_when_6mos" value="180">
                            <label for="new_recall_when_6mos" class="input-helper input-helper--checkbox">+ 6 <?php echo xlt('months'); ?></label><br />
                            -->
                            <div class="m-2 mb-3 ml-4">
                                <label for="new_recall_when_1yr" class="input-helper input-helper--checkbox">
                                    <input type="radio" name="new_recall_when" id="new_recall_when_1yr" value="365" /> <?php echo xlt('plus 1 year'); ?>
                                </label>
                                <br />
                                <label for="new_recall_when_2yr" class="p-15 input-helper input-helper--checkbox">
                                <input type="radio" name="new_recall_when" id="new_recall_when_2yr" value="730" /> <?php echo xlt('plus 2 years'); ?>
                                </label>
                                <br />
                                <label for="new_recall_when_3yr" class="input-helper input-helper--checkbox">
                                <input type="radio" name="new_recall_when" id="new_recall_when_3yr" value="1095" /> <?php echo xlt('plus 3 years'); ?></label>
                            </div>
                            <span class="font-weight-bold"> <?php echo xlt('Date'); ?>:</span>
                            <input class="datepicker form-control-sm text-center" type="text" id="form_recall_date" name="form_recall_date" value="<?php echo attr(oeFormatShortDate($result_pat['recall_date'] ?? '')); ?>" />
                        </div>

                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                                <label><?php echo xlt('Recall Reason'); ?></label>
                        </div>
                        <div class="form-group col-8 col-md-8 divTableCell indent20">
                            <input class="form-control" type="text" name="new_reason" id="new_reason" value="<?php if (($result_pat['PLAN'] ?? '') > '') {
                                 echo attr(rtrim("|", trim((string) $result_pat['PLAN']))); } ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                            <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                                <label><?php echo xlt('Provider'); ?></label>
                            </div>
                            <div class="form-group col-8 col-md-8 divTableCell indent20">
                                    <?php
                                    $providers = QueryUtils::fetchRecords("SELECT id, username, fname, lname FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
                                //This is an internal practice function so ignore the suffix as extraneous information.  We know who we are.
                                    $defaultProvider = $_SESSION['authUserID'];
                                // or, if we have chosen a provider in the calendar, default to them
                                // choose the first one if multiple have been selected
                                    if (is_countable($_SESSION['pc_username'] ?? null)) {
                                        if (count($_SESSION['pc_username']) >= 1) {
                                            // get the numeric ID of the first provider in the array
                                            $pc_username = $_SESSION['pc_username'];
                                            $results = QueryUtils::fetchRecords("SELECT id FROM users WHERE username=?", [$pc_username[0]]);
                                            if (!empty($results[0]['id'])) {
                                                $defaultProvider = $results[0]['id'];
                                            }
                                        }
                                    }
                                // if we clicked on a provider's schedule to add the event, use THAT.
                                    $userid = $userid ?? null;
                                    if ($userid) {
                                        $defaultProvider = $userid;
                                    }

                                    echo "<select class='form-control' name='new_provider' id='new_provider' style='width: 95%;'>";
                                    foreach ($providers as $urow) {
                                        echo "    <option value='" . attr($urow['id']) . "'";
                                        if ($urow['id'] == $defaultProvider) {
                                            echo " selected";
                                        }
                                        echo ">" . text($urow['lname']);
                                        if ($urow['fname']) {
                                            echo ", " . text($urow['fname']);
                                        }
                                        echo "</option>\n";
                                    }
                                    echo "</select>";
                                    ?>
                            </div>
                    </div>
                    <div class="row divTableBody prefs">
                            <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                                <label><?php echo xlt('Facility'); ?></label>
                            </div>
                            <div class="form-group col-8 col-md-8 divTableCell indent20">
                                <select class="form-control ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all" name="new_facility" id="new_facility" style="width: 95%;">
                                    <?php
                                        $facilities = QueryUtils::fetchRecords("SELECT id, name, primary_business_entity FROM facility WHERE service_location != 0");
                                    foreach ($facilities as $facrow) {
                                        if ($facrow['primary_business_entity'] == '1') {
                                            $selected = 'selected="selected"';
                                            echo "<option value='" . attr($facrow['id']) . "' $selected>" . text($facrow['name']) . "</option>";
                                        } else {
                                            $selected = '';
                                            echo "<option value='" . attr($facrow['id']) . "' $selected>" . text($facrow['name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                    </div>
                </div>
                <div class="col-4 divTable m-2 mr-auto">
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Address'); ?></label>
                        </div>
                        <div class="divTableCell form-group col-8 col-md-8">
                            <div class="col-12 mb-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('Address'); ?>" name="new_address" id="new_address" value="<?php echo attr($result_pat['street'] ?? ''); ?>" />
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('City'); ?>" name="new_city" id="new_city" value="<?php echo attr($result_pat['city'] ?? ''); ?>" />
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('State'); ?>" name="new_state" id="new_state" value="<?php echo attr($result_pat['state'] ?? ''); ?>" />
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('ZIP Code'); ?>" name="new_postal_code" id="new_postal_code" value="<?php echo attr($result_pat['postal_code'] ?? ''); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Home Phone'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8">
                            <input type="text" name="new_phone_home" id="new_phone_home" class="form-control" value="<?php echo attr($result_pat['phone_home'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Mobile Phone'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8">
                            <input type="text" name="new_phone_cell" id="new_phone_cell" class="form-control" value="<?php echo attr($result_pat['phone_cell'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label data-toggle="tooltip" data-placement="top" title="<?php echo xla('Text Message permission'); ?>"><?php echo xlt('SMS OK'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                                    <input type="radio" class="form-check-input" name="new_allowsms" id="new_allowsms_yes" value="YES" />
                                    <label class="form-check-label" for="new_allowsms_yes"><?php echo xlt('YES'); ?></label>
                           <input class="form-check-input" type="radio" name="new_allowsms" id="new_allowsms_no" value="NO" />
                            <label class="form-check-label" for="new_allowsms_no"><?php echo xlt('NO'); ?></label>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label data-toggle="tooltip" data-placement="top" title="<?php echo xla('Automated Voice Message permission'); ?>"><?php echo xlt('AVM OK'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                            <input class="form-check-input" type="radio" name="new_voice" id="new_voice_yes" value="YES" />
                            <label class="form-check-label" for="new_voice_yes"><?php echo xlt('YES'); ?></label>
                            <input class="form-check-input" type="radio" name="new_voice" id="new_voice_no" value="NO" />
                            <label class="form-check-label" for="new_voice_no"><?php echo xlt('NO'); ?></label>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('E-Mail'); ?></label>
                            </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                            <input type="email" name="new_email" id="new_email" class="form-control" value="<?php echo attr($result_pat['email'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('E-mail OK'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                                <input class="form-check-input" type="radio" name="new_email_allow" id="new_email_yes" value="YES" />
                            <label class="form-check-label" for="new_email_yes"><?php echo xlt('YES'); ?></label>
                            <input class="form-check-input" type="radio" name="new_email_allow" id="new_email_no" value="NO" />
                            <label class="form-check-label" for="new_email_no"><?php echo xlt('NO'); ?></label>
                        </div>
                    </div>
                </div>
            </form>

            <div class="col-12 text-center">
                <button class="btn btn-primary btn-add" style="float: none;" onclick="add_this_recall();" value="<?php echo xla('Add Recall'); ?>" id="add_new" name="add_new"><?php echo xlt('Add Recall'); ?></button>
                <p>
                    <em class="small text-muted">* <?php echo xlt('N.B.{{Nota bene}}') . " " . xlt('Demographic changes made here are recorded system-wide'); ?>.</em>
                </p>
            </div>
        </div>
    </div>
        <script>
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $(function () {
                $('.datepicker').datetimepicker({
                        <?php $datetimepicker_timepicker = false; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = true; ?>
                        <?php
                        $globals = OEGlobalsBag::getInstance();
                        require($globals->get('srcdir') . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
                        ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });
                <?php
                if (!empty($pid) && $pid !== 'new') {
                    ?>
                setpatient('<?php echo text($pid); ?>');
                    <?php
                }
                ?>
            var xljs_NOTE = '<?php echo xl("NOTE"); ?>';
            var xljs_PthsApSched = '<?php echo xl("This patient already has an appointment scheduled for"); ?>';
            var xljs_PlsDecRecDate = '<?php echo xl("Please select a recall date"); ?>';

            var translations = {
                patient_required: <?php echo xlj('Please select a patient'); ?>,
                date_required: <?php echo xlj('Please select a recall date'); ?>,
                provider_required: <?php echo xlj('Please select a provider'); ?>,
                facility_required: <?php echo xlj('Please select a facility'); ?>,
                no_recalls_found: <?php echo xlj('No Recalls Found'); ?>
            };

        </script>
            <?php
    }
}
