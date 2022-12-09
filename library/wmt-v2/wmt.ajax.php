<?php
// +-----------------------------------------------------------------------------+
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software Foundation, Inc.
// 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
// +------------------------------------------------------------------------------+

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../interface/globals.php");
require_once("$srcdir/wmt/wmt.class.php");
require_once("$srcdir/wmt/wmt.include.php");
require_once("$srcdir/auth.inc");

// Get request type
$type = $_REQUEST['type'];

if ($type == 'issues') {
	$category = $_REQUEST['category'];
	$active = ($_REQUEST['active'] == 'false') ? false : true;
	$comments = ($_REQUEST['comments'] == 'false') ? false : true;
	$limit = ($_REQUEST['comments'] == 'limit') ? true : false;
	$pid = $_REQUEST['pid'];
	
	$issues = wmtList::fetchPidItems($pid,$category,$active);

	foreach ($issues as $issue) {
		echo "<a class='iframe' href='$rootdir/patient_file/summary/add_edit_issue.php?issue=".$issue->id."' style='display:block;padding:3px'>$issue->title";
		if ($issue->diagnosis) echo " (".$issue->diagnosis.") ";
		if ($issue->begdate) echo " - Began: ".$issue->begdate;
		if ($issue->enddate) echo " - Ended: ".$issue->enddate;
		if ($issue->reaction) echo " - Reaction: ".$issue->reaction;
		if ($issue->comments && $comments) {
			if ($limit) $issue->comments = substr_replace($issue->comments, '...', 40); 
			echo " - " . $issue->comments;
		}
		echo "</a>";
	}
}

if ($type == 'issue_table') {
	$category = $_REQUEST['category'];
	$active = ($_REQUEST['active'] == 'false') ? false : true;
	$comments = ($_REQUEST['comments'] == 'false') ? false : true;
	$limit = ($_REQUEST['comments'] == 'limit') ? true : false;
	$pid = $_REQUEST['pid'];
	$encounter = $_REQUEST['encounter'];
	
	if ($category == "diagnosis") {
		echo wmtDiagnosis::diagnosisTable($encounter);
	}
	elseif ($category == "immunizations") {
		echo wmtImmunization::immunTable($pid);
	}	
	elseif ($category == "related") {
		echo wmtIssue::issuesRelated($pid, $encounter);
	}	
	else {
		echo wmtIssue::issueTable($pid, $category, $active);
	}
/*
	$issues = wmtList::fetchPidItems($pid,$category,$active);
	
	echo "<tr>\n";
	if ($category == 'medical_problem') echo "<th class=\"wmtHeader\" style=\"width:35%\">Issue</th>\n";
	if ($category == 'allergy') echo "<th class=\"wmtHeader\" style=\"width:35%\">Allergy</th>\n";
	if ($category == 'medication') echo "<th class=\"wmtHeader\" style=\"width:35%\">Medication</th>\n";
	if ($category != 'medication') echo "<th class=\"wmtHeader\" style=\"min-width:100px\">Began</th>\n";
	if ($category == 'medical_problem') echo "<th class=\"wmtHeader\" style=\"min-width:120px\">Diagnosis</th>\n";
	if ($category == 'medication') echo "<th class=\"wmtHeader\" style=\"min-width:100px\">Quantity</th>\n";
	if ($category == 'medication') echo "<th class=\"wmtHeader\" style=\"min-width:120px\">Dosage</th>\n";
	if ($category == 'allergy') echo "<th class=\"wmtHeader\" style=\"min-width:120px\">Reaction</th>\n";
	echo "<th class=\"wmtHeader\" style=\"width:50%\">Comments</th>\n";
	echo "<th class=\"wmtHeader\" style=\"width:30px\">Action</th>\n";
	echo "</tr>\n";
	
	foreach ($issues as $issue) {
		echo "<tr class=\"wmtLabel\">";
		echo "<td>".$issue->title."</td>\n";
		if ($category != 'medication') echo "<td>".$issue->begdate."</td>\n";
		if ($category == 'medical_problem') echo "<td>".$issue->diagnosis."</td>\n";
		if ($category == 'medication') echo "<td>".$issue->quantity."</td>\n";
		if ($category == 'medication') echo "<td>".$issue->dosage."</td>\n";
		if ($category == "allergy") echo "<td>".$issue->reaction."</td>\n";
		if ($comments) {
			if ($limit) $issue->comments = substr_replace($issue->comments, '...', 40); 
			echo "<td>" . $issue->comments."</td>\n";
		}
		echo "<td><a class='iframe css_button_small' href='$rootdir/patient_file/summary/add_edit_issue.php?issue=".$issue->id."'><span>Edit</span></a></td>";
		echo "</tr>";
	}
*/
}

if ($type == 'visit_table') {
	$active = ($_REQUEST['active'] == 'false') ? false : true;
	$comments = ($_REQUEST['comments'] == 'false') ? false : true;
	$limit = ($_REQUEST['comments'] == 'limit') ? true : false;
	$pid = $_REQUEST['pid'];
	
	$form_list = wmtForm::fetchPidForms('wcc', $pid, $active);
	
	echo "<tr>\n";
	echo "<th class=\"wmtHeader\" style=\"min-width:100px\">Version</th>\n";
	echo "<th class=\"wmtHeader\" style=\"min-width:100px\">Date</th>\n";
	echo "<th class=\"wmtHeader\" style=\"width:20%\">Caregiver</th>\n";
	echo "<th class=\"wmtHeader\" style=\"width:20%\">Provider</th>\n";
	echo "<th class=\"wmtHeader\" style=\"width:30%\">Notes</th>\n";
	echo "<th class=\"wmtHeader\" style=\"width:30px\">Action</th>\n";
	echo "</tr>\n";
	
	foreach ($form_list as $form_data) {
		echo "<tr class=\"wmtLabel\">";
		echo "<td>".ListLook($form_data->version,'WCC_Version')."</td>\n";
		$date = (strtotime($form_data->date) !== false)? date('Y-m-d',strtotime($form_data->date)) : "";
		echo "<td>".$date."</td>\n";
		echo "<td>".$form_data->caregiver."</td>\n";
		echo "<td>".UserNameFromID($form_data->provider)."</td>\n";
		if ($comments) {
			if ($limit) $form_data->staff_notes = substr_replace($form_data->staff_notes, '...', 40); 
			echo "<td>" . $form_data->staff_notes."</td>\n";
		}
		echo "<td><a class='css_button_small' target='_blank' href='$rootdir/forms/wcc/print.php?id=".$form_data->id."'><span>View</span></a></td>";
		echo "</tr>";
	}
}

if ($type == 'witness') {
	$username = $_REQUEST['username'];
	$md5pass = $_REQUEST['md5pass'];
	$sha1pass = $_REQUEST['sha1pass'];

    // get details about the user
    $authDB = sqlQuery("select id, password, authorized, see_auth".
                        ", cal_ui, active ".
                        " from users where username = '$username'");

    // if the user is active and password matches
    if ($authDB['active'] == 1) {
	    if ($authDB['password'] == $md5pass || $authDB['password'] == $sha1pass) {
	    	echo UserLook($username);
	    }
    }

}


?>
