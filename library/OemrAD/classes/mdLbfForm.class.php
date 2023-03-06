<?php

namespace OpenEMR\OemrAd;

/**
 * LbfForm Class
 */
class LbfForm {
	
	function __construct(){
	}

	/*
	public static function lbf_form_head_section() {
		$dlTitle = xlt('Select Encounter');

		return <<<EOF
		<style type="text/css">
			.configLink {
				text-transform: none!important;
				margin-right: 10px;
			}
			.global_copy_container, .sub_section_copy_container {
				display: inline-block;
    			float: right;
    			font-weight: normal;
			}

			#global_request_data, .request_data {
				display: none;
			}
		</style>

		<script type="text/javascript">
			// This invokes the find-addressbook popup.
			function add_doc_popup(section_id = '', formname = '', encounter = '', pid = '') {
				var url = '{$GLOBALS['webroot']}/library/OemrAD/interface/forms/LBF/lbf_select_encounter.php'+'?pid='+pid+'&section_id='+section_id+'&formname='+formname+'&encounter='+encounter;
			  	let title = "{$dlTitle}";
			  	dlgopen(url, 'selectEncounter', 600, 400, '', title);
			}

			async function globalCopy(event, pid, formname, encounter, section_id) {
				event.preventDefault();
            	event.stopPropagation();

            	add_doc_popup(section_id, formname, encounter, pid);
			}

			async function setEncounter(section_id, encounter_id, form_id, pid, c_action) {
				await fetchExtExam(encounter_id, form_id, pid, section_id);
			}

			async function fetchExtExam(encounterId, id, pid, section_id = 'global') {
				var msg = "Load data from selected encounter form into this form? \\n\\n Current Data in this form will be overwritten.";

				var confirmBox = confirm(msg);

				if(confirmBox != true) {
					return false;
				}

				if(section_id != 'global') {
					var inputVals = $('#'+section_id+'_request_data').val();
				} else {
					var inputVals = $('#global_request_data').val();
				}

				var valObj = {};
				if(inputVals != '') {
					valObj = JSON.parse(inputVals);
				}

				if(section_id == 'global') {
					valObj['section_id'] = 'global';
				}

				valObj['encounter_id'] = encounterId;
				valObj['f_id'] = id;

				const result = await $.ajax({
					type: "POST",
					url: "{$GLOBALS['webroot']}/library/OemrAD/interface/forms/LBF/ajax/fetch_lbf_form.php",
					datatype: "json",
					data: valObj
				});

				if(result != '' && confirmBox == true) {
					var resultObj = JSON.parse(result);

					if(section_id == 'global') {
						extexam[section_id](resultObj['formData'], resultObj['group_check_list']);
					} else {
						extexam.global(resultObj['formData'], resultObj['group_check_list'], section_id);
					}
				}
			}

			var extexam = {};

			extexam.global = function(data, list_data = [], sectionId = '') {
				$.each(data, function(section, fields){
					if(list_data['form_cb_'+section]) {
						if(list_data['form_cb_'+section] == 1) {
							$('input[name="'+'form_cb_'+section+'"]').prop('checked', true);
							$('#div_'+section).css("display", "block");
						} else {
							$('input[name="'+'form_cb_'+section+'"]').prop('checked', false);
							$('#div_'+section).css("display", "none");
						}
					}

					if(sectionId != '') {
						if(section == sectionId) {
							setFielValue(fields, section);
						}
					} else {
						setFielValue(fields, section);
					}	
				});
			}

			var setFielValue = function(data, section) {
				if(data && section) {
					$.each(data, function(k, field){
						if(field['data_type'] == '21') {
							var chValues = field['currentvalue'].split('|');
							var eleStr = [];
							
							$('#div_'+section+' [name^="form_'+field['field_id']+'["]').prop( "checked", false );

							$.each(chValues, function(chk, chVal){
								eleStr.push('#div_'+section+' [name="form_'+field['field_id']+'['+chVal+']"]');
							});

							var eStr = eleStr.join(', ');
							var ele = $(eStr);
							setInputVal(ele, '1');
						} else if(field['data_type'] == '22') {
							var tlValues = field['currentvalue'].split('|');
							$.each(tlValues, function(tlk, tlVal){
								var tlVals = tlVal.split(':');

								var tlele = $('#div_'+section+' [name="form_'+field['field_id']+'['+tlVals[0]+']"]');
								setInputVal(tlele, tlVals[1]);
							});
						} else if(field['data_type'] == '25') {
							$('#div_'+section+' [name^="check_'+field['field_id']+'["]').prop( "checked", false );
							
							var tcheleStr = [];
							var tchValues = field['currentvalue'].split('|');
							
							$.each(tchValues, function(tchk, tchVal){
								var tchVals = tchVal.split(':');

								var chele = $('#div_'+section+' [name="check_'+field['field_id']+'['+tchVals[0]+']"]');
								setInputVal(chele, tchVals[1]);

								var tchele = $('#div_'+section+' [name="form_'+field['field_id']+'['+tchVals[0]+']"]');
								setInputVal(tchele, tchVals[2]);
							});

						} else if(field['data_type'] == '34') {
							$('#div_'+section+' #form_'+field['field_id']+'_div').html(field['currentvalue']);
							var ele1 = $('#div_'+section+' [name="form_'+field['field_id']+'"]');
							setInputVal(ele1, field['currentvalue']);
						} else if(field['data_type'] == '36') {
							var smValues = field['currentvalue'].split('|');
							var ele = $('#div_'+section+' [name="form_'+field['field_id']+'[]"]');
							setInputVal(ele, smValues);
						} else {
							var ele = $('#div_'+section+' [name="form_'+field['field_id']+'"]');
							setInputVal(ele, field['currentvalue']);
						}
					});
				}
			}

			var setInputVal = function(ele, value) {
				//console.log(value);
				if(ele.length > 0) {
					if($(ele).is("input:text")) {
						ele.val(value);
					} else if($(ele).is("select")) {
						$(ele).val(value);
						//$(ele).val(value).change();
					} else if($(ele).is("select [multiple='multiple']")) {
						$(ele).val(value);
					} else if($(ele).is("textarea")) {
						$(ele).val(value);
					} else if($(ele).is("input:checkbox")) {
						$.each(ele, function(inx, c_ele){
							if($(c_ele).val() == value) {
								$(c_ele).prop( "checked", true );
							} else {
								$(c_ele).prop( "checked", false );
							}
						});
					} else if($(ele).is("input:radio")) {
						$.each(ele, function(inx, c_ele){
							if($(c_ele).val() == value) {
								$(c_ele).prop( "checked", true );
							} else {
								$(c_ele).prop( "checked", false );
							}
						});
					}
				}
			}
		</script>
EOF;
	}
	*/

	/*
	public static function getLayoutGroupProperties($formname) {
		$layoutData = sqlQuery(
            "SELECT * FROM layout_group_properties WHERE " .
            "grp_form_id = ? ",
            array($formname)
        );

        if(!empty($layoutData)) {
        	return $layoutData;
        }
        return false;
	}*/

	/*
	public static function getCurrentVal($formid, $frow, $formname, $pid, $from_trend_form, $currvalue, $edit_options, $source, $field_id) {
		if (!$from_trend_form && empty($currvalue) && isOption($edit_options, 'P') !== false) {
            if ($source == 'F' && !$formid) {
                // Form attribute for new form, get value from most recent form instance.
                // Form attributes of existing forms are expected to have existing values.
                $tmp = sqlQuery(
                    "SELECT encounter, form_id FROM forms WHERE " .
                    "pid = ? AND formdir = ? AND deleted = 0 " .
                    "ORDER BY date DESC LIMIT 1",
                    array($pid, $formname)
                );
                if (!empty($tmp['encounter'])) {
                    $currvalue = lbf_current_value($frow, $tmp['form_id'], $tmp['encounter']);
                }

            } else if ($source == 'E') {
                // Visit attribute, get most recent value as of this visit.
                // Even if the form already exists for this visit it may have a readonly value that only
                // exists in a previous visit and was created from a different form.
                $tmp = sqlQuery(
                    "SELECT sa.field_value FROM form_encounter AS e1 " .
                    "JOIN form_encounter AS e2 ON " .
                    "e2.pid = e1.pid AND (e2.date < e1.date OR (e2.date = e1.date AND e2.encounter <= e1.encounter)) " .
                    "JOIN shared_attributes AS sa ON " .
                    "sa.pid = e2.pid AND sa.encounter = e2.encounter AND sa.field_id = ?" .
                    "WHERE e1.pid = ? AND e1.encounter = ? " .
                    "ORDER BY e2.date DESC, e2.encounter DESC LIMIT 1",
                    array($field_id, $pid, $visitid)
                );
                if (isset($tmp['field_value'])) {
                    $currvalue = $tmp['field_value'];
                }
            }
        } // End "P" option logic.

        return $currvalue;
	}*/

	/*
	public static function ext_lbf_before_process($pid) {
		global $formname, $formid, $encounter, $group_check_list, $from_trend_form, $visitid;

		if(!isset($group_check_list) ||  empty($group_check_list)) {
			$group_check_list = array();
		}

		$fres = sqlStatement("SELECT * FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 " .
                "ORDER BY group_id, seq", array($formname));
		$formData = array();

		while ($frow1 = sqlFetchArray($fres)) {
			$field_id = $frow1['field_id'];
			$source = $frow1['source'];
			$edit_options = $frow1['edit_options'];

			$currvalue = lbf_current_value($frow1, $formid, $encounter);

			if(empty($currvalue)) {
				$currvalue = self::getCurrentVal($formid, $frow1, $formname, $pid, $from_trend_form, $currvalue, $edit_options, $source, $field_id);
			}

			if(isset($frow1['group_id'])) {
				$tfrow = $frow1;
				$tfrow['currentvalue'] = isset($currvalue) ? $currvalue : '';
				$formData['lbf'.$frow1['group_id']][] = $tfrow;
			}
		}

		foreach ($formData as $gk => $group) {
			$checked = 0;
			foreach ($group as $fk => $field) {
				if(!empty($field['currentvalue'])) {
					$checked = 1;
				}
			}

			$group_check_list['form_cb_'.$gk] = $checked;
		}

	}*/

	/*
	public static function lbf_form_top_section($pid) {
		global $formname, $encounter, $formid;

		$layoutData = self::getLayoutGroupProperties($formname);
		$isActiveCopy = isset($layoutData['grp_activate_copy']) ? $layoutData['grp_activate_copy'] : 0;

		$tmm_req = array();
		$tmp_req['formname'] = $formname;
		$tmp_req['encounter'] = $encounter;
		$tmp_req['formid'] = $formid;

		$requestStr = json_encode($tmp_req);
		
		$globalCopyTxt = xlt('Global Copy');
		$htmlOut = "";
		
		if($isActiveCopy) {
		$htmlOut = <<<EOF
			<div class="global_copy_container">
				<textarea type="hidden" disabled="disabled" name="global_request_data" id="global_request_data" >{$requestStr}</textarea>
				<a href="javascript: void(0)" class="globalConfigLink" onClick="globalCopy(event, '{$pid}', '{$formname}', '{$encounter}', 'global')">{$globalCopyTxt}</a>
			</div>
EOF;
		}

		return $htmlOut;
	}*/

	/*
	public static function lbf_form_sub_section($pid) {
		global $formname, $encounter, $formid, $group_name, $group_seq;

		$layoutData = self::getLayoutGroupProperties($formname);
		$isActiveCopy = isset($layoutData['grp_activate_copy']) ? $layoutData['grp_activate_copy'] : 0;

		$tmm_req = array();
		$tmp_req['formname'] = $formname;
		$tmp_req['encounter'] = $encounter;
		$tmp_req['formid'] = $formid;
		$tmp_req['section_id'] = $group_seq;

		$secCopyTxt = xlt('Section Copy');
		$htmlOut = "";

		$requestStr = json_encode($tmp_req);

		if($isActiveCopy) {
		$htmlOut = <<<EOF
			<div class="sub_section_copy_container">
				<textarea type="hidden" disabled="disabled" name="{$group_seq}_request_data" class="request_data" id="{$group_seq}_request_data" >{$requestStr}</textarea>
				<a href="javascript: void(0)" class="subConfigLink" onClick="globalCopy(event, '{$pid}', '{$formname}', '{$encounter}', '{$group_seq}')">{$secCopyTxt}</a>
			</div>
EOF;
		}

		return $htmlOut;
	}*/
}