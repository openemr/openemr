<?php

function getLayoutGroupProperties($formname) {
	$layoutData = sqlQuery(
        "SELECT * FROM layout_group_properties WHERE " .
        "grp_form_id = ? ",
        array($formname)
    );

    if(!empty($layoutData)) {
    	return $layoutData;
    }
    return false;
}

function getCurrentVal($formid, $frow, $formname, $pid, $from_trend_form, $currvalue, $edit_options, $source, $field_id) {
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
}

function preProcessData($pid) {
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
			$currvalue = getCurrentVal($formid, $frow1, $formname, $pid, $from_trend_form, $currvalue, $edit_options, $source, $field_id);
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

}

function lbf_form_top_section($pid) {
	global $formname, $encounter, $formid;

	$layoutData = getLayoutGroupProperties($formname);
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
}

function lbf_form_sub_section($pid) {
	global $formname, $encounter, $formid, $group_name, $group_seq;

	$layoutData = getLayoutGroupProperties($formname);
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
}