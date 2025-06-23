<?php

/**
 * view/plans_config.php  UI for CDR admin rules plan
 *
 * UI to select or add new plans in plans configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jan Jajalla <Jajalla23@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Jan Jajalla <Jajalla23@gmail.com>
 * @copyright Copyright (c) 2014 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\ClinicalDecisionRules\Interface\Common;

// TODO: Look at moving js_src and css_src values into our config.yaml files as we should centralize these assets to
// keep things up to date / consistent across the application
?>

<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] . '/jquery-ui-themes/themes/ui-lightness/jquery-ui.min.css'; ?>" />
<link rel="stylesheet" href="<?php Common::css_src('cdr-multiselect/common.css') ?>" />
<link rel="stylesheet" href="<?php Common::css_src('cdr-multiselect/ui.multiselect.css') ?>" />
<link rel="stylesheet" href="<?php Common::css_src('cdr-multiselect/plans_config.css') ?>" />

<script src="<?php Common::js_src('cdr-multiselect/jquery.min.js') ?>"></script>
<script src="<?php Common::js_src('cdr-multiselect/jquery-ui.min.js') ?>"></script>
<script src="<?php Common::js_src('cdr-multiselect/plugins/localisation/jquery.localisation-min.js') ?>"></script>
<script src="<?php Common::js_src('cdr-multiselect/plugins/scrollTo/jquery.scrollTo-min.js') ?>"></script>
<script src="<?php Common::js_src('cdr-multiselect/ui.multiselect.js?v=' . $GLOBALS['v_js_includes']) ?>"></script>
<script>
    // Below variables are to be used in the javascript for the cdr-multiselect(from cdr-multiselect/locale/ui-multiselect-cdr.js)
    $.extend($.ui.multiselect.locale, {
        addAll:<?php echo xlj('Add all rules to plan'); ?>,
        removeAll:<?php echo xlj('Remove all rules from plan'); ?>,
        itemsCount:<?php echo xlj('rules already in plan'); ?>
    });
</script>

<script src="<?php Common::js_src('list.js') ?>"></script>
<script src="<?php Common::js_src('jQuery.fn.sortElements.js') ?>"></script>

<script>
    $(function () {
        //load plans
        $.post(
            'index.php?action=ajax!getNonCQMPlans'
        ).done(function (data) {
            $.each(data, function (idx, obj) {
                $('<option id="' + jsAttr(obj.plan_id) + '" p_id="' + jsAttr(obj.plan_pid) + '" value="' + jsAttr(obj.plan_id) + '">' + jsText(obj.plan_title) + '</option>').insertAfter('#select_plan').insertBefore('#divider');
            });
        });

        //Change selected plan
        $("#cdr-plans-select").change(function () {
            $loadRules(
                $('#cdr-plans-select').find('option:selected').attr('id'),
                $('#cdr-plans-select').find('option:selected').attr('p_id')
            );
        });

        //Update Plan status
        $("#cdr-status").click(function () {
            if (window.buttonStatus == "active") {
                $deactivatePlan();
                $togglePlanStatus(false);
            } else {

                $activatePlan();
                $togglePlanStatus(true);
            }
        });

        //Cancel
        $("#cdr-button-cancel").click(function () {
            if (confirm(<?php echo xlj('Are you sure you want to cancel your changes?'); ?>)) {
                $loadRules(
                    $('#cdr-plans-select').find('option:selected').attr('id'),
                    $('#cdr-plans-select').find('option:selected').attr('p_id')
                );
            }
        });

        //Delete Plan
        $("#delete_plan").click(function () {
            if (confirm(<?php echo xlj('Are you sure you want to delete this plan?'); ?>)) {
                var selected_plan = $('#cdr-plans-select').find('option:selected').attr('id');
                var selected_plan_pid = $('#cdr-plans-select').find('option:selected').attr('p_id');

                $("body").addClass("loading");

                $.post
                (
                    'index.php?action=ajax!deletePlan&plan_id=' + encodeURIComponent(selected_plan)
                    + '&plan_pid=' + encodeURIComponent(selected_plan_pid)
                ).done(function (resp) {
                    $("body").removeClass("loading");
                    location.reload();
                }).fail(function (jqXHR, textStatus) {
                    console.log(textStatus);
                    alert(<?php echo xlj('Error while deleting the plan'); ?>);
                    $("body").removeClass("loading");
                });
            }
        });

        //Submit Changes
        $("#cdr-button-submit").click(function () {
            var plan_id = $('#cdr-plans-select').find('option:selected').attr('id');
            var plan_name = $('#cdr-plans-select').find('option:selected').text();
            var is_new_plan = false;

            if (plan_id == 'add_new_plan') {
                //reset
                $('#new_plan_name').css({
                    'border-color': '',
                    'border-width': ''
                });

                plan_name = $("#new_plan_name").val();
                is_new_plan = true;
            }

            var new_selected = new Array;
            var new_unselected = new Array;

            $('#cdr_rules_select option').each(function () {
                if ($(this).attr('selected') && ($(this).attr('init_value') == 'not-selected')) {
                    new_selected.push($(this).val());

                } else if (!$(this).attr('selected') && ($(this).attr('init_value') == 'selected')) {
                    new_unselected.push($(this).val());
                }

            });

            //Validate
            if (new_selected.length == 0 && new_unselected.length == 0) {
                alert(<?php echo xlj('No Changes Detected'); ?>);
                return;
            } else if (is_new_plan && plan_name.length == 0) {
                alert(<?php echo xlj('Plan Name Missing'); ?>);
                $('#new_plan_name').css({
                    'border-color': 'red',
                    'border-width': '3px'
                });
                $('#new_plan_name').focus();
                return;
            }

            $("body").addClass("loading");

            var postData =
                {
                    "plan_id": plan_id,
                    "added_rules": new_selected,
                    "removed_rules": new_unselected,
                    "plan_name": plan_name
                }
            var dataString = JSON.stringify(postData);

            $.post(
                'index.php?action=ajax!commitChanges',
                dataString).done(function (obj) {
                if (obj.status_code == '000') {
                    //Success
                    if (is_new_plan) {
                        $('<option id="' + jsAttr(obj.plan_id) + '" value="' + jsAttr(obj.plan_id) + '">' + jsText(obj.plan_title) + '</option>').insertAfter('#select_plan').insertBefore('#divider').attr("selected", "selected");
                        plan_id = obj.plan_id;

                        alert(<?php echo xlj('Plan Added Successfully'); ?>);

                    } else {
                        alert(<?php echo xlj('Plan Updated Successfully'); ?>);
                    }

                    $loadRules(plan_id, 0);

                } else if (obj.status_code == '001') {
                    alert(<?php echo xlj('Unknown Error'); ?>);

                } else if (obj.status_code == '002') {
                    alert(<?php echo xlj('Plan Name Already Taken'); ?>);
                    $('#new_plan_name').css({
                        'border-color': 'red',
                        'border-width': '3px'
                    });
                    $('#new_plan_name').focus();
                } else {
                    //Error
                    console.log(obj.status_message);
                    if (is_new_plan) {
                        alert(<?php echo xlj('Error while adding new plan'); ?>);
                    } else {
                        alert(<?php echo xlj('Error while updating the plan'); ?>);
                    }
                }

                $("body").removeClass("loading");
            }).fail(function (jqXHR, textStatus) {
                console.log(textStatus);
                if (is_new_plan) {
                    alert(<?php echo xlj('Error while adding new plan'); ?>);
                } else {
                    alert(<?php echo xlj('Error while updating the plan'); ?>);
                }

                $("body").removeClass("loading");
            });
        });
    });

    $loadRules = function (selected_plan, selected_plan_pid) {
        $("#cdr_rules").empty(selected_plan);
        $('#new_plan_container').empty();

        if (selected_plan != 'select_plan') {
            $("body").addClass("loading");

            $("#cdr_hide_show-div").show();
            $("#delete_plan").show();
            $("#plan_status_div").show();

            if (selected_plan == 'add_new_plan') {
                $("#delete_plan").hide();
                $("#plan_status_div").hide();
                $newPlan();

            } else {
                $loadPlanStatus(selected_plan, selected_plan_pid);
            }

            $.post
            (
                'index.php?action=ajax!getRulesInAndNotInPlan&plan_id=' + encodeURIComponent(selected_plan)
            ).done(function (data) {
                $('#cdr_rules').append('<select id="cdr_rules_select" class="multiselect" multiple="multiple" name="cdr_rules_select[]"/>');

                $.each(data, function (idx, obj) {
                    if (obj.selected == "true") {
                        $("#cdr_rules_select").append(
                            $('<option value="' + jsAttr(obj.rule_id) + '" selected="selected" init_value="selected">' + jsText(obj.rule_title) + '</option>')
                        );
                    } else {
                        $("#cdr_rules_select").append(
                            $('<option value="' + jsAttr(obj.rule_id) + '" init_value="not-selected">' + jsText(obj.rule_title) + '</option>')
                        );
                    }
                });

                $("#cdr_rules_select").multiselect({dividerLocation: 0.45});
                $("body").removeClass("loading");
            });
        } else {
            $("#cdr_hide_show-div").hide();
            $("#delete_plan").hide();
        }
    }

    $loadPlanStatus = function (selected_plan, selected_plan_pid) {
        $.post
        (
            'index.php?action=ajax!getPlanStatus&plan_id=' + encodeURIComponent(selected_plan)
            + '&plan_pid=' + encodeURIComponent(selected_plan_pid)
        ).done(function (obj) {

            if (obj.is_plan_active) {
                $activatePlan();
            } else {
                $deactivatePlan();
            }

        }).fail(function (jqXHR, textStatus) {
            console.log(textStatus);
            alert(<?php echo xlj('Error'); ?>);
        });

    }

    $newPlan = function () {
        $('#new_plan_container').append('<label>' + jsText(<?php echo xlj('Plan Name'); ?>) + ': </label>').append('<input class="col-4 form-control ml-1" id="new_plan_name" type="text" name="new_plan_name" />');

        $("#cdr-rules_cont").removeClass("overlay");
    }

    $togglePlanStatus = function (isActive) {
        var selected_plan = $('#cdr-plans-select').find('option:selected').attr('id');
        var selected_plan_pid = $('#cdr-plans-select').find('option:selected').attr('p_id');
        var action = 'activate';

        if (!isActive) {
            action = 'deactivate';
        }

        var postToggle =
            {
                "selected_plan": selected_plan,
                "plan_pid": selected_plan_pid,
                "plan_status": action
            }
        var dataStringToggle = JSON.stringify(postToggle);

        $.post(
            'index.php?action=ajax!togglePlanStatus'
            , dataStringToggle).done(function (obj) {
            if (obj == '007') {
                alert(<?php echo xlj('Plan Status Changed'); ?>);
            }
            if (obj == '002') {
                alert(<?php echo xlj('Plan Status Failed to Change'); ?>);
            }
        }).fail(function (jqXHR, textStatus) {
            console.log(textStatus);
            alert(<?php echo xlj('Error'); ?>);
        });
    }

    $activatePlan = function () {
        $("#plan-status-label").text(jsText(<?php echo xlj('Status'); ?>) + ': ' + jsText(<?php echo xlj('Active{{Plan}}'); ?>));
        window.buttonStatus = "active";
        $("#cdr-status").removeAttr("disabled");
        $("#cdr-status").text(jsText(<?php echo xlj('Deactivate'); ?>));

        $("#cdr-rules_cont").removeClass("overlay");
    }

    $deactivatePlan = function () {
        $("#plan-status-label").text(jsText(<?php echo xlj('Status'); ?>) + ': ' + jsText(<?php echo xlj('Inactive'); ?>));
        window.buttonStatus = "inactive";
        $("#cdr-status").removeAttr("disabled");
        $("#cdr-status").text(jsText(<?php echo xlj('Activate'); ?>));

        $("#cdr-rules_cont").addClass("overlay");
    }

</script>

<div class="cdr-mappings col-12">
    <br />
    <header class="title"><?php echo xlt('View Plan Rules'); ?>
        <a href="index.php?action=browse!list" class="btn btn-primary btn-back mx-auto" onclick="top.restoreSession();"><?php echo xlt('Back'); ?></a>
    </header>
    <hr />
    <div id="cdr_mappings_form-div" class="cdr-form">
        <div class="cdr-plans input-group">
            <label><?php echo xlt('Plan') . ':'; ?></label>
            <select id="cdr-plans-select" name="cdr-plans-select" class="cdr-plans-select-class col-4 form-control ml-1 mb-2">
                <option id="select_plan" value="select_plan">- <?php echo xlt('Select Plan'); ?> -</option>
                <option id="divider" class="divider" value="divider" disabled></option>
                <option id="add_new_plan" value="add_new_plan"><?php echo xlt('Add New Plan'); ?></option>
            </select>
            <span class="ml-1">
                <button title="<?php echo xla('Delete Plan'); ?>" id="delete_plan" class="btn btn-outline-danger delete_button mt-2" type="button" style="display: none;"></button>
            </span>
        </div>
        <div id="new_plan_container" class="input-group"></div>
        <div id="cdr_hide_show-div" style="display: none;">
            <div id="plan_status_div" class="plan-status_div mt-2">
                <label id='plan-status-label'><?php echo xlt('Status') . ':'; ?></label>
                <button id='cdr-status' class="btn btn-sm btn-primary" disabled><?php echo xlt('Activate'); ?></button>
            </div>
            <br />
            <div id="cdr-rules_cont">
                <div id="cdr_rules" class="cdr-rules-class"></div>
                <div id="cdr_buttons_div" class="cdr-buttons-class btn-group my-1">
                    <button id='cdr-button-cancel' class="btn btn-secondary"><?php echo xlt('Cancel'); ?></button>
                    <button id='cdr-button-submit' class="btn btn-primary"><?php echo xlt('Submit'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal"></div>
