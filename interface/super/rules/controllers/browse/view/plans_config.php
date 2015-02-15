<?php
/**
 * view/plans_config.php  UI for CDR admin rules plan
 *
 * UI to select or add new plans in plans configuration
 *  
 * Copyright (C) 2014 Jan Jajalla <Jajalla23@gmail.com>
 * Copyright (C) 2014 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is didtributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Jan Jajalla <Jajalla23@gmail.com>
 * @author Roberto Vaquez <robertogagliotta@gmail.com>
 * @link http://www.open-emr.org
*/
?>

<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['webroot'] . '/library/css/jquery-ui-1.8.21.custom.css'?>" />
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
	addAll:'<?php echo out(xl('Add all rules to plan')); ?>',
	removeAll:'<?php echo out(xl('Remove all rules from plan')); ?>',
	itemsCount:'<?php echo out(xl('rules already in plan')); ?>'
});
</script>

<script language="javascript" src="<?php js_src('list.js') ?>"></script>
<script language="javascript" src="<?php js_src('jQuery.fn.sortElements.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {	
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
		});

		//Update Plan status
		$("#cdr-status").click(function() {
                        if (window.buttonStatus == "active")
                            {
                               $deactivatePlan();
                               $togglePlanStatus(false);
                             } else {

			        $activatePlan();
			        $togglePlanStatus(true);
                             }
		});

		//Cancel
		$("#cdr-button-cancel").click(function() {
			if (confirm('<?php echo xls('Are you sure you want to cancel your changes?'); ?>')) {
				$loadRules(
					$('#cdr-plans-select').find('option:selected').attr('id'),
					$('#cdr-plans-select').find('option:selected').attr('p_id')
				);
	        }
		});

		//Delete Plan
		$("#delete_plan").click(function() {
			if (confirm('<?php echo xls('Are you sure you want to delete this plan?'); ?>')) {
				var selected_plan = $('#cdr-plans-select').find('option:selected').attr('id');
				var selected_plan_pid = $('#cdr-plans-select').find('option:selected').attr('p_id');

				$("body").addClass("loading");
				
				$.post
		    	(
			    	'<?php echo  _base_url() . 
			    			"/library/RulesPlanMappingEventHandlers_ajax.php?action=deletePlan&plan_id="; ?>' + selected_plan
			    			+ '&plan_pid=' + selected_plan_pid							
				)
				.done(function(resp) {
					$("body").removeClass("loading");
					location.reload();    
			    })
			    .fail(function (jqXHR, textStatus) {
				    console.log(textStatus);
                                        alert('<?php echo xls('Error while deleting the plan'); ?>');
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
				alert('<?php echo xls('No Changes Detected'); ?>');
				return;
			} else if (is_new_plan && plan_name.length == 0) {
				alert('<?php echo xls('Plan Name Missing'); ?>');
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

			           	alert('<?php echo xls('Plan Added Successfully'); ?>');
	        		
			        } else {
			           	alert('<?php echo xls('Plan Updated Successfully'); ?>');
			        }

		            $loadRules(plan_id, 0);
		            
				} else if (obj.status_code == '001') {
					alert('<?php echo xls('Unknown Error'); ?>');

				} else if (obj.status_code == '002') {
					alert('<?php echo xls('Plan Name Already Taken'); ?>');
					$('#new_plan_name')
						.css({'border-color':'red',
							'border-width':'3px'
						});
					$('#new_plan_name').focus();
				} else {
					//Error
					console.log(obj.status_message);
		            if (is_new_plan) {
			           	alert('<?php echo xls('Error while adding new plan'); ?>');
			        } else {
			           	alert('<?php echo xls('Error while updating the plan'); ?>');
			        }
				}

	            $("body").removeClass("loading");
			})
			.fail(function (jqXHR, textStatus) {
				console.log(textStatus);
	            if (is_new_plan) {
		           	alert('<?php echo xls('Error while adding new plan'); ?>');
		        } else {
		           	alert('<?php echo xls('Error while updating the plan'); ?>');
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
				$("#plan_status_div").hide();
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
									$('<option value="' + obj.rule_id + '" selected="selected" init_value="selected">' + obj.rule_title + '</option>')
								);
						} else {
							$("#cdr_rules_select")
								.append(
									$('<option value="' + obj.rule_id + '" init_value="not-selected">' + obj.rule_title + '</option>')
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
	    			'/library/RulesPlanMappingEventHandlers_ajax.php?action=getPlanStatus&plan_id='; ?>' + selected_plan
	    			+ '&plan_pid=' + selected_plan_pid
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
                        .append('<?php echo '<label>' . out(xl('Plan Name')) . ': </label>'; ?>')
			.append('<input id="new_plan_name" type="text" name="new_plan_name">');

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
                              alert('<?php echo xls('Plan Status Changed'); ?>');
                             }
                           if (obj == '002') {
                              alert('<?php echo xls('Plan Status Failed to Change'); ?>');
                             }
	    })
	    .fail(function(jqXHR, textStatus) {
		    console.log(textStatus);
			alert('<?php echo xls('Error'); ?>');
	    });
	}

	$activatePlan = function() {
        $("#plan-status-label").text('<?php echo out(xl('Status')) . ': ' . out(xl('Active')); ?>');
        window.buttonStatus = "active";
        $("#cdr-status").removeAttr("disabled");
        $("#cdr-status").text('<?php echo out(xl('Deactivate')); ?>');

		$("#cdr-rules_cont").removeClass("overlay");
	}

	$deactivatePlan = function() {
        $("#plan-status-label").text('<?php echo out(xl('Status')) . ': ' . out(xl('Inactive')); ?>');
        window.buttonStatus = "inactive";
        $("#cdr-status").removeAttr("disabled");
        $("#cdr-status").text('<?php echo out(xl('Activate')); ?>');

		$("#cdr-rules_cont").addClass("overlay"); 
	}	

</script>

<div class="cdr-mappings">
	<br/>
	<div><b><?php echo out( xl( 'View Plan Rules' )); ?></b></div>
	<br/>
	<div id="cdr_mappings_form-div" class="cdr-form">
		<div class="cdr-plans">
			<?php echo out(xl('Plan')) . ':'; ?>
			<select id="cdr-plans-select" name="cdr-plans-select" class="cdr-plans-select-class">
                             <option id="select_plan" value="select_plan">- <?php echo out( xl( 'SELECT PLAN' )); ?> -</option>
				<option id="divider" value="divider" disabled/>
				<option id="add_new_plan" value="add_new_plan"><?php echo out( xl( 'ADD NEW PLAN' )); ?></option>
			</select>
			<input title="<?php echo out(xl('Delete Plan')); ?>" id="delete_plan" class="delete_button" type="image" style="display: none;"/>
		</div>	
		<div id="new_plan_container"></div>
		<div id="cdr_hide_show-div" style="display: none;">
			<div id="plan_status_div" class="plan-status_div">
                                <label id='plan-status-label'><?php echo out( xl( 'Status' )) . ':'; ?></label>
				<button id='cdr-status' disable><?php echo out( xl( 'Activate' )); ?></button>
 			</div>
			<br/>
			
			<div id="cdr-rules_cont">
				<div id="cdr_rules" class="cdr-rules-class"></div>   	
	      	
		      	<div id="cdr_buttons_div" class="cdr-buttons-class">
		      		<button id='cdr-button-cancel'><?php echo out( xl( 'Cancel' )); ?></button>
		      		<button id='cdr-button-submit'><?php echo out( xl( 'Submit' )); ?></button>
		      	</div>
		    </div>
      	</div>
	</div>
</div>

<div class="modal"></div>
