<?php
//  ------------------------------------------------------------------------ //
//                     Garden State Health Systems                           //
//                    Copyright (c) 2010 gshsys.com                          //
//                      <http://www.gshsys.com/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

function getHeaderData() {

// Reserved for future use
	
}

function getMedicationData() {
	global $pid;

	$sql = " 
		SELECT prescriptions.date_added ,
			prescriptions.patient_id,
			prescriptions.start_date,
			prescriptions.quantity,
			prescriptions.interval,
			prescriptions.note,
			prescriptions.drug,
			prescriptions.medication,
			prescriptions.active,
			prescriptions.provider_id 
		FROM prescriptions 
		WHERE prescriptions.patient_id =  ".$pid;
		
	$result = sqlStatement($sql);
	return $result;
}

function getImmunizationData() {
	global $pid;
	
	$sql = " SELECT immunizations.administered_date,
			immunizations.patient_id,
			immunizations.vis_date,
			immunizations.note,
			immunizations.immunization_id,
			immunizations.manufacturer,
			list_options.title
		FROM immunizations , list_options
		WHERE immunizations.immunization_id = list_options.option_id and immunizations.patient_id = ".$pid." and list_id = 'immunizations' " ;
	
	$result = sqlStatement($sql);
	return $result;
}


function getProcedureData() {

	global $pid;

	$sql = " 
	SELECT 
		lists.date, 
		lists.pid,
		lists.type,
		lists.title as proc_title,
		lists.diagnosis,
		lists.outcome,
		lists.groupname,
		form_encounter.onset_date,
		form_encounter.encounter,
		lists.comments,
		facility.city,
		facility.street,
		facility.state,
		users.title,
		users.fname,
		users.lname
	from lists
	left join issue_encounter
	on issue_encounter.list_id = lists.id
	left join form_encounter
	on form_encounter.encounter = issue_encounter.encounter
	left join facility
	on form_encounter.facility_id = facility.id
	left join users
	on form_encounter.provider_id = users.id
	where lists.type = 'surgery' and lists.pid=".$pid;

	$result = sqlStatement($sql);
	return $result;
}


function getProblemData() {

	global $pid;

	$sql = " 
	select fe.encounter, fe.reason, fe.provider_id, u.title, u.fname, u.lname, 
		fe.facility_id, f.street, f.city, f.state, ie.list_id, l.pid, l.title as prob_title, l.diagnosis, 
		l.outcome, l.groupname, l.begdate, l.enddate, l.type, l.comments , l.date, cd.code_text
	from lists as l 
	left join issue_encounter as ie
	on ie.list_id = l.id
	left join form_encounter as fe
	on fe.encounter = ie.encounter
	left join facility as f
	on fe.facility_id = f.id
	left join users as u
	on fe.provider_id = u.id
	left join codes as cd
	on cd.code = SUBSTRING(l.diagnosis, LOCATE(':',l.diagnosis)+1)
	where l.type = 'medical_problem' and l.pid=".$pid;
		
	$result = sqlStatement($sql);
	return $result;
}


function getAlertData() {

	global $pid;

	$sql = " 
	select fe.reason, fe.provider_id, fe.facility_id, fe.encounter,
		ie.list_id, l.pid, l.title as alert_title, l.outcome, 
		l.groupname, l.begdate, l.enddate, l.type, l.diagnosis, l.date ,
			f.street, f.city, f.state, u.title, u.fname, u.lname, cd.code_text
	from lists as l 
	left join issue_encounter as ie
	on ie.list_id = l.id
	left join form_encounter as fe
	on fe.encounter = ie.encounter
	left join facility as f
	on fe.facility_id = f.id
	left join users as u
	on fe.provider_id = u.id
	left join codes as cd
	on cd.code = SUBSTRING(l.diagnosis, LOCATE(':',l.diagnosis)+1)
	where l.type = 'allergy' and l.pid=".$pid;
		
	$result = sqlStatement($sql);
	return $result;
}


function getResultData() {

	global $pid;

	$sql = " 
	select date, pid, groupname, ankle_able_to_bear_weight_steps, ankle_x_ray_interpretation
	from form_ankleinjury
	where pid =".$pid;
		
	$result = sqlStatement($sql);
	return $result;
}


function getActorData() {
	global $pid;

	$sql = " 
	select fname, lname, DOB, sex, pid, street, city, state, postal_code, phone_contact
	from patient_data
	where pid=".$pid;
		
	$result = sqlStatement($sql);
	return $result;
}

?>
