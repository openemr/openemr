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
?>

<link type="text/css" rel="stylesheet" href="<?php css_src('cdr-multiselect/common.css') ?>" />
<link type="text/css" rel="stylesheet" href="<?php css_src('cdr-multiselect/ui.multiselect.css') ?>" />
<link type="text/css" rel="stylesheet" href="<?php css_src('cdr-multiselect/plans_config.css') ?>" />

<script language="javascript" src="<?php js_src('/cdr-multiselect/jquery.min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/jquery-ui.min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/plugins/localisation/jquery.localisation-min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/plugins/scrollTo/jquery.scrollTo-min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/ui.multiselect.js') ?>"></script>
<script type="text/javascript">
    // Below variables are to be used in the javascript for the cdr-multiselect(from cdr-multiselect/locale/ui-multiselect-cdr.js)
    $.extend($.ui.multiselect.locale, {
        addAll:<?php echo xlj('Add all Reminders and Alerts to plan'); ?>,
        removeAll:<?php echo xlj('Remove all from plan'); ?>,
        itemsCount:<?php echo xlj('Reminders and Alerts already in plan'); ?>
    });
</script>

<script language="javascript" src="<?php js_src('list.js') ?>"></script>
<script language="javascript" src="<?php js_src('jQuery.fn.sortElements.js') ?>"></script>

<script type="text/javascript">
    $(function() {
        $("#cdr_hide_show-div").hide();
        $("#delete_plan").hide();
        $("#plan_status").hide();
        //load plans
        $("#cdr-plans").load('<?php library_src('RulesPlanMappingEventHandlers_ajax.php') ?>');
        
        $.post(
            '<?php echo  _base_url() . '/library/RulesPlanMappingEventHandlers_ajax.php?action=getNonCQMPlans'; ?>'
        )
         .done(function(resp) {
             var data = $.parseJSON(resp);
            
             $.each(data, function(idx, obj) {
                 $('<option id="' + obj.plan_id + '" p_id="' + obj.plan_pid + '" value="' + obj.plan_id + '">' + obj.plan_title + '</option>')
                     .insertAfter('#select_plan')
                     .insertBefore('#divider');
             });
         });
        
        //Change selected plan
        $("#cdr-plans-select").change(function() {
            $loadRules(
                $('#cdr-plans-select').find('option:selected').attr('id'),
                $('#cdr-plans-select').find('option:selected').attr('p_id')
            );
            $("#plan-status").trigger('click');
        });
        
        //Update Plan status
        $("#plan_status").click(function() {
            if (window.buttonStatus == "active")
            {
                $("#plan_status").html('<i class="fa fa-bell"> <?php echo xla('Inactive'); ?></i>');
                $deactivatePlan();
                $togglePlanStatus(false);
            } else {
                $("#plan_status").html('<i class="fa fa-bell"> <?php echo xla('Active'); ?></i>');
                $activatePlan();
                $togglePlanStatus(true);
            }
        });
        
        //Cancel
        $("#cdr-button-cancel").click(function() {
            if (confirm(<?php echo xlj('Are you sure you want to cancel your changes?'); ?>)) {
                $loadRules(
                    $('#cdr-plans-select').find('option:selected').attr('id'),
                    $('#cdr-plans-select').find('option:selected').attr('p_id')
                );
            }
        });
        
        //Delete Plan
        $("#delete_plan").click(function() {
            if (confirm(<?php echo xlj('Are you sure you want to delete this plan?'); ?>)) {
                var selected_plan = $('#cdr-plans-select').find('option:selected').attr('id');
                var selected_plan_pid = $('#cdr-plans-select').find('option:selected').attr('p_id');
                
                $("body").addClass("loading");
                
                $.post
                 (
                     '<?php echo  _base_url() .
                         "/library/RulesPlanMappingEventHandlers_ajax.php?action=deletePlan&plan_id="; ?>' + encodeURIComponent(selected_plan)
                     + '&plan_pid=' + encodeURIComponent(selected_plan_pid)
                 )
                 .done(function(resp) {
                     $("body").removeClass("loading");
                     location.reload();
                 })
                 .fail(function (jqXHR, textStatus) {
                     console.log(textStatus);
                     alert(<?php echo xlj('Error while deleting the plan'); ?>);
                     $("body").removeClass("loading");
                 });
            }
        });
        
        //Submit Changes
        $("#cdr-button-submit").click(function() {
            var plan_id = $('#cdr-plans-select').find('option:selected').attr('id');
            var plan_name = $('#cdr-plans-select').find('option:selected').text();
            var is_new_plan = false;
            
            if (plan_id == 'add_new_plan') {
                //reset
                $('#new_plan_name')
                    .css({'border-color':'',
                             'border-width':''
                         });
                
                plan_name = $("#new_plan_name").val();
                is_new_plan = true;
            }
            
            var new_selected = new Array;
            var new_unselected = new Array;
            
            $('#cdr_rules_select option').each(function() {
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
                $('#new_plan_name')
                    .css({'border-color':'red',
                             'border-width':'3px'
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
                    "plan_name" : plan_name
                }
            var dataString = JSON.stringify(postData);
            
            $.post(
                '<?php echo  _base_url() . '/library/RulesPlanMappingEventHandlers_ajax.php?action=commitChanges'; ?>',
                dataString)
             .done(function(resp) {
                 var obj = $.parseJSON(resp);
                 if (obj.status_code == '000') {
                     //Success
                     if (is_new_plan) {
                         $('<option id="' + obj.plan_id + '" value="' + obj.plan_id + '">' + obj.plan_title + '</option>')
                             .insertAfter('#select_plan')
                             .insertBefore('#divider')
                             .attr("selected","selected");
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
                     $('#new_plan_name')
                         .css({'border-color':'red',
                                  'border-width':'3px'
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
             })
             .fail(function (jqXHR, textStatus) {
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
    
    $loadRules = function(selected_plan, selected_plan_pid){
        $("#cdr_rules").empty(selected_plan);
        $('#new_plan_container').empty();
        
        if (selected_plan != 'select_plan') {
            $("body").addClass("loading");
            
            $("#cdr_hide_show-div").show();
            $("#delete_plan").show();
            $("#plan_status_div").show();
            
            if (selected_plan == 'add_new_plan') {
                $("#delete_plan").hide();
                $("#plan_status").hide();
                $newPlan();
                
            } else {
                $loadPlanStatus(selected_plan, selected_plan_pid);
            }
            
            $.post
             (
                 '<?php echo  _base_url() .
                     '/library/RulesPlanMappingEventHandlers_ajax.php?action=getRulesInAndNotInPlan&plan_id='; ?>' + selected_plan
             )
             .done(function(resp) {
                 var data = $.parseJSON(resp);
                
                 $('#cdr_rules')
                     .append('<select id="cdr_rules_select" class="multiselect" multiple="multiple" name="cdr_rules_select[]"/>');
                
                 $.each(data, function(idx, obj) {
                     if (obj.selected  == "true") {
                         $("#cdr_rules_select")
                             .append(
                                 $('<option value="' + obj.rule_id + '" selected="selected" init_value="selected">' + obj.rule_title  +'</option>')
                             );
                     } else {
                         $("#cdr_rules_select")
                             .append(
                                 $('<option value="' + obj.rule_id + '" init_value="not-selected">' + obj.rule_title +'</option>')
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
    
    $loadPlanStatus = function(selected_plan, selected_plan_pid) {
        $.post
         (
             '<?php echo  _base_url() .
                 '/library/RulesPlanMappingEventHandlers_ajax.php?action=getPlanStatus&plan_id='; ?>' + encodeURIComponent(selected_plan)
             + '&plan_pid=' + encodeURIComponent(selected_plan_pid)
         )
         .done(function(resp) {
             var obj = $.parseJSON(resp);
            
             if (obj.is_plan_active) {
                 $activatePlan();
             } else {
                 $deactivatePlan();
             }
            
         })
         .fail(function (jqXHR, textStatus) {
             console.log(textStatus);
             alert('<?php echo xls('Error'); ?>');
         });
        
    }
    
    $newPlan = function() {
        $('#new_plan_container')
            .append('<?php echo '<label>' . xla('Plan Name') . ': '; ?>')
            .append('<input class="form-control" id="new_plan_name" type="text" name="new_plan_name"></label>');
        
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
                "plan_pid":  selected_plan_pid,
                "plan_status": action
            }
        var dataStringToggle = JSON.stringify(postToggle);
        
        $.post(
            '<?php echo  _base_url() . '/library/RulesPlanMappingEventHandlers_ajax.php?action=togglePlanStatus'; ?>'
            , dataStringToggle).done(function(resp) {
            var obj = $.parseJSON(resp);
            if (obj == '007')
            {
                alert(<?php echo xlj('Plan Status Changed'); ?>);
            }
            if (obj == '002') {
                alert(<?php echo xlj('Plan Status Failed to Change'); ?>);
            }
        })
         .fail(function(jqXHR, textStatus) {
             console.log(textStatus);
             alert(<?php echo xlj('Error'); ?>);
         });
    }
    
    $activatePlan = function() {
        window.buttonStatus = "active";
        $("#delete_plan").show();
        $("#plan_status").html('<i class="fa fa-bell"> <?php echo xlt('Activated'(; ?></i>').show();
    
        $("#cdr-rules_cont").removeClass("overlay");
    }
    
    $deactivatePlan = function() {
        window.buttonStatus = "inactive";
        $("#delete_plan").show();
        $("#plan_status").html('<i class="fa fa-bell-slash"><?php echo xlt('Inactive'); ?></i>').show();
    
        $("#cdr-rules_cont").addClass("overlay");
    }

</script>
<div class="title" style="display:none"><a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/rules/index.php?browse!plans_config"><?php
            // this will display the TAB title
            echo xlt('Care Plans'); ?><?php
            $in = xlt($rule->title);
            echo mb_strlen($in) > 10 ? mb_substr($in, 0, 10)."..." : $in;
?></a>
</div>
<br /><br />


<div class="row text-center">
    <div class="col-9 offset-1">
        <div class="col-12 text-left">
            <div class="title"><?php echo xlt('Care Plans'); ?>:
                <span class="small">
                    <select id="cdr-plans-select" name="cdr-plans-select" class="cdr-plans-select-class">
                        <option id="select_plan" value="select_plan">- <?php echo xlt('SELECT PLAN'); ?> -</option>
                        <option id="divider" value="divider" disabled/>
                        <option id="add_new_plan" value="add_new_plan"><?php echo xlt('ADD NEW PLAN'); ?></option>
                    </select>
                </span>
            </div>
        </div>
        <div class="cdr-mappings row text-center">
            <div class="col-12 text-center">
                <button id="delete_plan"
                        class="btn btn-sm btn-danger icon_1X"
                        title="<?php echo xla('Delete Plan'); ?>"
                        style="display: none;"><i class="fa fa-trash-o"></i>
                </button>
                <button id="plan_status"
                        title="<?php echo xla('Plan Status: Active or Inactive'); ?>"
                        class="btn btn-sm btn-primary icon_2X">
                    <i class="fa fa-bell"><?php echo xlt('Activate'); ?></i>
                </button>
                <div  class="plan-status_div">
                </div>
                
            </div>
            <div id="cdr_mappings_form-div" class="cdr-form col-10 row text-left">
    
                <div id="new_plan_container" class="col-6"></div>
                <div id="cdr_hide_show-div" style="display: none;">
                    <div id="cdr-rules_cont">
                        <div id="cdr_rules" class="cdr-rules-class"></div>
                        <div id="cdr_buttons_div" class="cdr-buttons-class">
                            <button id='cdr-button-cancel'><?php echo xlt('Cancel'); ?></button>
                            <button id='cdr-button-submit'><?php echo xlt('Submit'); ?></button>
                        </div>
                    </div>
                   
    
    
                </div>
            </div>
        </div>
    
        <div class="modal"></div>
    </div>
</div>
