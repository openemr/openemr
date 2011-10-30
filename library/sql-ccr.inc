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

if($_POST['ccrAction'] == 'generate'){
  if(isset($_POST['show_date'])){
    $set = "on";
    $start = $_POST['Start'];
    $start = $start." 00:00:00";
    $end = $_POST['End'];
    $end = $end." 23:59:59";
  }
}

function getHeaderData() {

// Reserved for future use
	
}

function getMedicationData() {
	global $pid,$set,$start,$end;
  if($set == "on"){
    $sql = " 
      SELECT prescriptions.date_added ,
        prescriptions.patient_id,
        prescriptions.start_date,
        prescriptions.quantity,
        prescriptions.interval,
        prescriptions.note,
        prescriptions.drug,
        prescriptions.medication,
        IF(prescriptions.active=1,'Active','Prior History No Longer Active') AS active,
        prescriptions.provider_id,
        prescriptions.size,
	prescriptions.rxnorm_drugcode,
        IFNULL(prescriptions.refills,0) AS refills,
        lo2.title AS form,
        lo.title
      FROM prescriptions 
      LEFT JOIN list_options AS lo
      ON lo.list_id = 'drug_units' AND prescriptions.unit = lo.option_id
      LEFT JOIN list_options AS lo2
      ON lo2.list_id = 'drug_form' AND prescriptions.form = lo2.option_id
      WHERE prescriptions.patient_id = ?
      AND prescriptions.date_added BETWEEN ? AND ?
      UNION
      SELECT 
        DATE(DATE) AS date_added,
        pid AS patient_id,
        begdate AS start_date,
        '' AS quantity,
        '' AS `interval`,
        comments AS note,
        title AS drug,
        '' AS medication,
        IF((isnull(enddate) OR enddate = '0000-00-00' OR enddate >= CURDATE()),'Active','Prior History No Longer Active') AS active,
        '' AS provider_id,
        '' AS size,
'' AS rxnorm_drugcode,
        0 AS refills,
        '' AS form,
        '' AS title 
      FROM
        lists 
      WHERE `type` = 'medication' 
        AND pid = ?
        AND `date` BETWEEN ? AND ?";
    $result = sqlStatement($sql, array($pid,$start,$end,$pid,$start,$end) );
  }else{
    $sql = " 
      SELECT prescriptions.date_added ,
        prescriptions.patient_id,
        prescriptions.start_date,
        prescriptions.quantity,
        prescriptions.interval,
        prescriptions.note,
        prescriptions.drug,
        prescriptions.medication,
        IF(prescriptions.active=1,'Active','Prior History No Longer Active') AS active,
        prescriptions.provider_id,
        prescriptions.size,
	prescriptions.rxnorm_drugcode,
        IFNULL(prescriptions.refills,0) AS refills,
        lo2.title AS form,
        lo.title
      FROM prescriptions 
      LEFT JOIN list_options AS lo
      ON lo.list_id = 'drug_units' AND prescriptions.unit = lo.option_id
      LEFT JOIN list_options AS lo2
      ON lo2.list_id = 'drug_form' AND prescriptions.form = lo2.option_id
      WHERE prescriptions.patient_id = ?
      UNION
      SELECT 
        DATE(DATE) AS date_added,
        pid AS patient_id,
        begdate AS start_date,
        '' AS quantity,
        '' AS `interval`,
        comments AS note,
        title AS drug,
        '' AS medication,
        IF((isnull(enddate) OR enddate = '0000-00-00' OR enddate >= CURDATE()),'Active','Prior History No Longer Active') AS active,
        '' AS provider_id,
        '' AS size,
	'' AS rxnorm_drugcode,
        0 AS refills,
        '' AS form,
        '' AS title 
      FROM
        lists 
      WHERE `type` = 'medication' 
        AND pid = ?";
    $result = sqlStatement($sql, array($pid,$pid) );
  }
	return $result;
}

function getImmunizationData() {
	global $pid,$set,$start,$end;
  if($set == "on"){
    $sql = "SELECT
      immunizations.administered_date,
      immunizations.patient_id,
      immunizations.vis_date,
      immunizations.note,
      immunizations.immunization_id,
      immunizations.manufacturer,
      codes.code_text AS title
    FROM immunizations 
    LEFT JOIN codes ON immunizations.cvx_code = codes.code
    LEFT JOIN code_types ON codes.code_type = code_types.ct_id
    WHERE immunizations.patient_id = ? AND code_types.ct_key = 'CVX'
    AND create_date BETWEEN ? AND ?" ;
    $result = sqlStatement($sql, array($pid,$start,$end) );
  }else{
    $sql = "SELECT
      immunizations.administered_date,
      immunizations.patient_id,
      immunizations.vis_date,
      immunizations.note,
      immunizations.immunization_id,
      immunizations.manufacturer,
      codes.code_text AS title
    FROM immunizations 
    LEFT JOIN codes ON immunizations.cvx_code = codes.code
    LEFT JOIN code_types ON codes.code_type = code_types.ct_id
    WHERE immunizations.patient_id = ? AND code_types.ct_key = 'CVX'";
    $result = sqlStatement($sql, array($pid) );
  }
	return $result;
}


function getProcedureData() {

	global $pid,$set,$start,$end;
  if($set == "on"){
    $sql = " 
    SELECT 
      lists.title as proc_title,
      lists.date as `date`,
      list_options.title as outcome,
      '' as laterality,
      '' as body_site,
      lists.type as `type`,
      lists.diagnosis as `code`,
      IF(SUBSTRING(lists.diagnosis,1,LOCATE(':',lists.diagnosis)-1) = 'ICD9','ICD9-CM',SUBSTRING(lists.diagnosis,1,LOCATE(':',lists.diagnosis)-1)) AS coding
    FROM
      lists 
      LEFT JOIN issue_encounter 
        ON issue_encounter.list_id = lists.id 
      LEFT JOIN form_encounter 
        ON form_encounter.encounter = issue_encounter.encounter 
      LEFT JOIN facility 
        ON form_encounter.facility_id = facility.id 
      LEFT JOIN users 
        ON form_encounter.provider_id = users.id 
      LEFT JOIN list_options
        ON lists.outcome = list_options.option_id
        AND list_options.list_id = 'outcome'
    WHERE lists.type = 'surgery' 
      AND lists.pid = ?
      AND lists.date BETWEEN ? AND ?
    UNION
    SELECT 
      pt.name as proc_title,
      prs.date as `date`,
      '' as outcome,
      ptt.laterality as laterality,
      ptt.body_site as body_site,
      'Lab Order' as `type`,
      ptt.standard_code as `code`,
      IF(SUBSTRING(ptt.standard_code,1,LOCATE(':',ptt.standard_code)-1) = 'ICD9','ICD9-CM',SUBSTRING(ptt.standard_code,1,LOCATE(':',ptt.standard_code)-1)) AS coding
    FROM
      procedure_result AS prs 
      LEFT JOIN procedure_report AS prp 
        ON prs.procedure_report_id = prp.procedure_report_id 
      LEFT JOIN procedure_order AS po 
        ON prp.procedure_order_id = po.procedure_order_id 
      LEFT JOIN procedure_type AS pt 
        ON prs.procedure_type_id = pt.procedure_type_id 
      LEFT JOIN procedure_type AS ptt 
        ON pt.parent = ptt.procedure_type_id 
        AND ptt.procedure_type = 'ord' 
      LEFT JOIN list_options AS lo 
        ON lo.list_id = 'proc_unit' 
        AND pt.units = lo.option_id 
    WHERE po.patient_id = ?
    AND prs.date BETWEEN ? AND ?";
  
    $result = sqlStatement($sql, array($pid,$start,$end,$pid,$start,$end) );
  }else{
    $sql = " 
    SELECT 
      lists.title as proc_title,
      lists.date as `date`,
      list_options.title as outcome,
      '' as laterality,
      '' as body_site,
      lists.type as `type`,
      lists.diagnosis as `code`,
      IF(SUBSTRING(lists.diagnosis,1,LOCATE(':',lists.diagnosis)-1) = 'ICD9','ICD9-CM',SUBSTRING(lists.diagnosis,1,LOCATE(':',lists.diagnosis)-1)) AS coding
    FROM
      lists 
      LEFT JOIN issue_encounter 
        ON issue_encounter.list_id = lists.id 
      LEFT JOIN form_encounter 
        ON form_encounter.encounter = issue_encounter.encounter 
      LEFT JOIN facility 
        ON form_encounter.facility_id = facility.id 
      LEFT JOIN users 
        ON form_encounter.provider_id = users.id 
      LEFT JOIN list_options
        ON lists.outcome = list_options.option_id
        AND list_options.list_id = 'outcome'
    WHERE lists.type = 'surgery' 
      AND lists.pid = ? 
    UNION
    SELECT 
      pt.name as proc_title,
      prs.date as `date`,
      '' as outcome,
      ptt.laterality as laterality,
      ptt.body_site as body_site,
      'Lab Order' as `type`,
      ptt.standard_code as `code`,
      IF(SUBSTRING(ptt.standard_code,1,LOCATE(':',ptt.standard_code)-1) = 'ICD9','ICD9-CM',SUBSTRING(ptt.standard_code,1,LOCATE(':',ptt.standard_code)-1)) AS coding
    FROM
      procedure_result AS prs 
      LEFT JOIN procedure_report AS prp 
        ON prs.procedure_report_id = prp.procedure_report_id 
      LEFT JOIN procedure_order AS po 
        ON prp.procedure_order_id = po.procedure_order_id 
      LEFT JOIN procedure_type AS pt 
        ON prs.procedure_type_id = pt.procedure_type_id 
      LEFT JOIN procedure_type AS ptt 
        ON pt.parent = ptt.procedure_type_id 
        AND ptt.procedure_type = 'ord' 
      LEFT JOIN list_options AS lo 
        ON lo.list_id = 'proc_unit' 
        AND pt.units = lo.option_id 
    WHERE po.patient_id = ? ";
  
    $result = sqlStatement($sql, array($pid,$pid) );
  }
	return $result;
}


function getProblemData() {

	global $pid,$set,$start,$end;
  if($set == "on"){
    $sql = " 
    SELECT fe.encounter, fe.reason, fe.provider_id, u.title, u.fname, u.lname, 
      fe.facility_id, f.street, f.city, f.state, ie.list_id, l.pid, l.title AS prob_title, l.diagnosis, 
      l.outcome, l.groupname, l.begdate, l.enddate, l.type, l.comments , l.date, cd.code_text
    FROM lists AS l 
    LEFT JOIN issue_encounter AS ie ON ie.list_id = l.id
    LEFT JOIN form_encounter AS fe ON fe.encounter = ie.encounter
    LEFT JOIN facility AS f ON fe.facility_id = f.id
    LEFT JOIN users AS u ON fe.provider_id = u.id
    LEFT JOIN codes AS cd ON cd.code = SUBSTRING(l.diagnosis, LOCATE(':',l.diagnosis)+1)
    LEFT JOIN code_types AS ct ON ct.ct_id = cd.code_type
    WHERE l.type = 'medical_problem' AND l.pid=? AND ct.ct_key = SUBSTRING(l.diagnosis, 1, LOCATE(':',l.diagnosis)-1)
    AND l.date BETWEEN ? AND ?";
    $result = sqlStatement($sql, array($pid,$start,$end) );
  }else{
    $sql = " 
    SELECT fe.encounter, fe.reason, fe.provider_id, u.title, u.fname, u.lname, 
      fe.facility_id, f.street, f.city, f.state, ie.list_id, l.pid, l.title AS prob_title, l.diagnosis, 
      l.outcome, l.groupname, l.begdate, l.enddate, l.type, l.comments , l.date, cd.code_text
    FROM lists AS l 
    LEFT JOIN issue_encounter AS ie ON ie.list_id = l.id
    LEFT JOIN form_encounter AS fe ON fe.encounter = ie.encounter
    LEFT JOIN facility AS f ON fe.facility_id = f.id
    LEFT JOIN users AS u ON fe.provider_id = u.id
    LEFT JOIN codes AS cd ON cd.code = SUBSTRING(l.diagnosis, LOCATE(':',l.diagnosis)+1)
    LEFT JOIN code_types AS ct ON ct.ct_id = cd.code_type
    WHERE l.type = 'medical_problem' AND l.pid=? AND ct.ct_key = SUBSTRING(l.diagnosis, 1, LOCATE(':',l.diagnosis)-1)";
    $result = sqlStatement($sql, array($pid) );
  }
	return $result;
}


function getAlertData() {

	global $pid,$set,$start,$end;
  if($set == "on"){
	$sql = " 
    select fe.reason, fe.provider_id, fe.facility_id, fe.encounter,
      ie.list_id, l.pid, l.title as alert_title, l.outcome, 
      l.groupname, l.begdate, l.enddate, l.type, l.diagnosis, l.date ,
      l.reaction , l.comments ,
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
    where l.type = 'allergy' and l.pid=?
    AND l.date BETWEEN ? AND ?";
    
    $result = sqlStatement($sql, array($pid,$start,$end) );
  }else{
    $sql = " 
    select fe.reason, fe.provider_id, fe.facility_id, fe.encounter,
      ie.list_id, l.pid, l.title as alert_title, l.outcome, 
      l.groupname, l.begdate, l.enddate, l.type, l.diagnosis, l.date ,
      l.reaction , l.comments ,
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
    where l.type = 'allergy' and l.pid=?";
      
    $result = sqlStatement($sql, array($pid) );
  }
	return $result;
}


function getResultData() {

	global $pid,$set,$start,$end;
  if($set == "on"){
    $sql = "
      SELECT 
        prs.procedure_result_id as `pid`,
        pt.name as `name`,
        pt.procedure_type_id as `type`,
        prs.date as `date`,
        concat_ws(' ',prs.result,lo.title) as `result`,
        prs.range as `range`,
        prs.abnormal as `abnormal`,
        prs.comments as `comments`,
        ptt.lab_id AS `lab`
      FROM
        procedure_result AS prs 
        LEFT JOIN procedure_report AS prp 
          ON prs.procedure_report_id = prp.procedure_report_id 
        LEFT JOIN procedure_order AS po 
          ON prp.procedure_order_id = po.procedure_order_id
        LEFT JOIN procedure_type AS pt 
          ON prs.procedure_type_id = pt.procedure_type_id
          LEFT JOIN procedure_type AS ptt 
          ON pt.parent = ptt.procedure_type_id
          AND ptt.procedure_type = 'ord'
        LEFT JOIN list_options AS lo
          ON lo.list_id = 'proc_unit' AND pt.units = lo.option_id
      WHERE po.patient_id=?
      AND prs.date BETWEEN ? AND ?";
		
    $result = sqlStatement($sql, array($pid,$start,$end) );
  }else{
    $sql = "
      SELECT 
        prs.procedure_result_id as `pid`,
        pt.name as `name`,
        pt.procedure_type_id as `type`,
        prs.date as `date`,
        concat_ws(' ',prs.result,lo.title) as `result`,
        prs.range as `range`,
        prs.abnormal as `abnormal`,
        prs.comments as `comments`,
        ptt.lab_id AS `lab`
      FROM
        procedure_result AS prs 
        LEFT JOIN procedure_report AS prp 
          ON prs.procedure_report_id = prp.procedure_report_id 
        LEFT JOIN procedure_order AS po 
          ON prp.procedure_order_id = po.procedure_order_id
        LEFT JOIN procedure_type AS pt 
          ON prs.procedure_type_id = pt.procedure_type_id
          LEFT JOIN procedure_type AS ptt 
          ON pt.parent = ptt.procedure_type_id
          AND ptt.procedure_type = 'ord'
        LEFT JOIN list_options AS lo
          ON lo.list_id = 'proc_unit' AND pt.units = lo.option_id
      WHERE po.patient_id=?";
		
    $result = sqlStatement($sql, array($pid) );
  }
	return $result;
}


function getActorData() {
	global $pid;

	$sql = " 
	select fname, lname, DOB, sex, pid, street, city, state, postal_code, phone_contact
	from patient_data
	where pid=?";
		
	$result[0] = sqlStatement($sql, array($pid) );
  
  $sql2 = " 
	SELECT * FROM users AS u LEFT JOIN facility AS f ON u.facility_id = f.id WHERE u.id=?";
		
	$result[1] = sqlStatement($sql2, array($_SESSION['authUserID']) );
  
  $sql3 = "
  SELECT 
    u.*
  FROM
    procedure_type AS pt 
    LEFT JOIN procedure_order AS po 
      ON po.procedure_type_id = pt.procedure_type_id 
    LEFT JOIN forms AS f 
      ON f.form_id = po.procedure_order_id 
    LEFT JOIN list_options AS lo 
      ON lo.title = f.form_name
    LEFT JOIN users AS u
    ON pt.lab_id = u.id
  WHERE f.pid = ? 
    AND lo.list_id = 'proc_type' 
    AND lo.option_id = 'ord'
    GROUP BY u.id";
		
	$result[2] = sqlStatement($sql3, array($pid) );
  
	return $result;
}


function getReportFilename() {
  global $pid;

  $sql = "
    select fname, lname, pid
    from patient_data
    where pid=?";

  $result = sqlQuery($sql, array($pid) );
	$result_filename = $result['lname']."-".$result['fname']."-".$result['pid']."-".date("mdY",time());

  return $result_filename;
}

?>
