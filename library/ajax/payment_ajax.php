<?php

/**
 * This section handles ajax for insurance,patient and for encounters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//=================================
if (isset($_POST["ajax_mode"])) {
    AjaxDropDownCode();
}

//=================================
function AjaxDropDownCode()
{
    if ($_POST["ajax_mode"] == "set") {//insurance
        $CountIndex = 1;
        $StringForAjax = "<div id='AjaxContainerInsurance'><table class='table table-sm table-striped w-100 bg-light text-dark'>
	  <tr class='text bg-dark text-light'>
		<td width='50'>" . xlt('Code') . "</td>
		<td width='300'>" . xlt('Name') . "</td>
	    <td width='200'>" . xlt('Address') . "</td>
	  </tr>" .
        //ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
        //PlaceValues(event,'&nbsp;','')==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClick('&nbsp;','')==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        "<tr class='text' height='20'  bgcolor='" . attr($bgcolor ?? '') . "' id=\"tr_insurance_" . attr($CountIndex) . "\"
	  onkeydown=\"ProcessKeyForColoring(event," . attr_js($CountIndex) . ");PlaceValues(event,'&nbsp;','')\"   onclick=\"PutTheValuesClick('&nbsp;','')\">
			<td colspan='3' align='center'><a id='anchor_insurance_code_" . attr($CountIndex) . "' href='#'></a></td>
	  </tr>";
        $insurance_text_ajax = trim((isset($_POST['insurance_text_ajax']) ? $_POST['insurance_text_ajax'] : ''));
        $res = sqlStatement("SELECT insurance_companies.id,name,city,state,country FROM insurance_companies
			left join addresses on insurance_companies.id=addresses.foreign_id  where name like ? or  insurance_companies.id like ? ORDER BY name", array($insurance_text_ajax . '%', $insurance_text_ajax . '%'));
        while ($row = sqlFetchArray($res)) {
            if ($CountIndex % 2 == 1) {
                $bgcolor = '#ddddff';
            } else {
                $bgcolor = '#ffdddd';
            }

                $CountIndex++;
                $Id = $row['id'];
                $Name = $row['name'];
                $City = $row['city'];
                $State = $row['state'];
                $Country = $row['country'];
                $Address = $City . ', ' . $State . ', ' . $Country;
                $StringForAjax .= "<tr class='text' id=\"tr_insurance_" . attr($CountIndex) . "\"
		onkeydown='ProcessKeyForColoring(event,$CountIndex);PlaceValues(event," . attr_js($Id) . "," . attr_js($Name) . ")'
			   onclick='PutTheValuesClick(" . attr_js($Id) . "," . attr_js($Name) . ")'>
			<td><a id='anchor_insurance_code_" . attr($CountIndex) . "' href='#'>" . text($Id) . "</a></td>
			<td><a href='#'>" . text($Name)  . "</a></td>
		    <td><a href='#'>" . text($Address) . "</a></td>
</tr>";
        }

        $StringForAjax .= "</table></div>";
        echo text(strlen($_POST['insurance_text_ajax'])) . '~`~`' . $StringForAjax;
        die;
    }

//===============================================================================
    if ($_POST["ajax_mode"] == "set_patient") {//patient.
    //From 2 areas this ajax is called.So 2 pairs of functions are used.
        //PlaceValues==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClick==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PlaceValuesDistribute==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClickDistribute==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        if (isset($_POST['patient_code']) && $_POST['patient_code'] != '') {
            $patient_code = trim((isset($_POST['patient_code']) ? $_POST['patient_code'] : ''));
            if (isset($_POST['submit_or_simple_type']) && $_POST['submit_or_simple_type'] == 'Simple') {
                $StringToAppend = "PutTheValuesClickPatient";
                $StringToAppend2 = "PlaceValuesPatient";
            } else {
                $StringToAppend = "PutTheValuesClickDistribute";
                $StringToAppend2 = "PlaceValuesDistribute";
            }

            $patient_code_complete = $_POST['patient_code'];//we need the spaces here
        } elseif (isset($_POST['insurance_text_ajax']) && $_POST['insurance_text_ajax'] != '') {
            $patient_code = trim((isset($_POST['insurance_text_ajax']) ? $_POST['insurance_text_ajax'] : ''));
            $StringToAppend = "PutTheValuesClick";
            $StringToAppend2 = "PlaceValues";
            $patient_code_complete = $_POST['insurance_text_ajax'];//we need the spaces here
        }

        $CountIndex = 1;
        $StringForAjax = "<div id='AjaxContainerPatient'><table class='table table-sm table-striped w-50 bg-light text-dark'>
	  <tr class='text bg-dark text-light'>
		<td width='50'>" . xlt('Code') . "</td>
		<td width='100'>" . xlt('Last Name') . "</td>
	    <td width='100'>" . xlt('First Name') . "</td>
	    <td width='100'>" . xlt('Middle Name') . "</td>
	    <td width='100'>" . xlt('Date of Birth') . "</td>
	  </tr>" .
        //ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
        "<tr class='text' id=\"tr_insurance_" . attr($CountIndex) . "\"
	  onkeydown=\"ProcessKeyForColoring(event," . attr_js($CountIndex) . ");$StringToAppend2(event,'&nbsp;','')\"   onclick=\"$StringToAppend('&nbsp;','')\">
			<td colspan='5' align='center'><a id='anchor_insurance_code_" . attr($CountIndex) . "' href='#'></a></td>
	  </tr>

	  ";
        $res = sqlStatement(
            "SELECT pid as id,fname,lname,mname,DOB FROM patient_data
			 where  fname like ? or lname like ? or mname like ? or
			 CONCAT(lname,' ',fname,' ',mname) like ? or pid like ? ORDER BY lname",
            array(
                $patient_code . '%',
                $patient_code . '%',
                $patient_code . '%',
                $patient_code . '%',
                $patient_code . '%'
            )
        );
        while ($row = sqlFetchArray($res)) {
            if ($CountIndex % 2 == 1) {
                $bgcolor = '#ddddff';
            } else {
                $bgcolor = '#ffdddd';
            }

                $CountIndex++;
                $Id = $row['id'];
                $fname = $row['fname'];
                $lname = $row['lname'];
                $mname = $row['mname'];
                $Name = $lname . ' ' . $fname . ' ' . $mname;
                $DOB = oeFormatShortDate($row['DOB']);
                $StringForAjax .= "<tr class='text' id=\"tr_insurance_" . attr($CountIndex) . "\"
		 onkeydown='ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event," . attr_js($Id) . "," . attr_js($Name) . ")' onclick=\"$StringToAppend(" . attr_js($Id) . ", " . attr_js($Name) . ")\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >" . text($Id) . "</a></td>
			<td><a href='#'>" . text($lname) . "</a></td>
		    <td><a href='#'>" . text($fname) . "</a></td>
            <td><a href='#'>" . text($mname) . "</a></td>
            <td><a href='#'>" . text($DOB) . "</a></td>
  </tr>";
        }

        $StringForAjax .= "</table></div>";
        echo text(strlen($patient_code_complete)) . '~`~`' . $StringForAjax;
        die;
    }

//===============================================================================
    if ($_POST["ajax_mode"] == "encounter") {//encounter
    //PlaceValuesEncounter==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClickEncounter==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        if (isset($_POST['encounter_patient_code'])) {
            $patient_code = trim((isset($_POST['encounter_patient_code']) ? $_POST['encounter_patient_code'] : ''));
            $StringToAppend = "PutTheValuesClickEncounter";
            $StringToAppend2 = "PlaceValuesEncounter";
        }

        $CountIndex = 1;
        $StringForAjax = "<div id='AjaxContainerEncounter'><table width='202' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='100'>" . xlt('Encounter') . "</td>
		<td width='100'>" . xlt('Date') . "</td>
	  </tr>" .
        //ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
        "<tr class='text' height='20'  bgcolor='" . attr($bgcolor) . "' id=\"tr_insurance_" . attr($CountIndex) . "\"
	  onkeydown=\"ProcessKeyForColoring(event," . attr_js($CountIndex) . ");$StringToAppend2(event,'&nbsp;','')\"   onclick=\"$StringToAppend('&nbsp;','')\">
			<td colspan='2' align='center'><a id='anchor_insurance_code_" . attr($CountIndex) . "' href='#'></a></td>
	  </tr>

	  ";
        $res = sqlStatement("SELECT date,encounter FROM form_encounter
			 where pid =? ORDER BY encounter", array($patient_code));
        while ($row = sqlFetchArray($res)) {
            if ($CountIndex % 2 == 1) {
                $bgcolor = '#ddddff';
            } else {
                $bgcolor = '#ffdddd';
            }

                $CountIndex++;
                $Date = $row['date'];
                $Date = explode(' ', $Date);
                $Date = oeFormatShortDate($Date[0]);
                $Encounter = $row['encounter'];
                $StringForAjax .= "<tr class='text'  bgcolor='" . attr($bgcolor) . "' id=\"tr_insurance_" . attr($CountIndex) . "\"
		 onkeydown=\"ProcessKeyForColoring(event," . attr_js($CountIndex) . ");$StringToAppend2(event," . attr_js($Encounter) . "," . attr_js($Date) . ")\"   onclick=\"$StringToAppend(" . attr_js($Encounter) . "," . attr_js($Date) . ")\">
			<td><a id='anchor_insurance_code_" . attr($CountIndex) . "' href='#' >" . text($Encounter) . "</a></td>
			<td><a href='#'>" . text($Date) . "</a></td>
  </tr>";
        }

        $StringForAjax .= "</table></div>";
        echo $StringForAjax;
        die;
    }
}
