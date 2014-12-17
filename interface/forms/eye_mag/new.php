<?php
/** 
* forms/eye_mag/new.php 
* 
* The page shown when the user requests a new form
*
* Copyright (C) 2010-14 Raymond Magauran <magauran@MedFetch.com> 
* 
* LICENSE: This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 3 
* of the License, or (at your option) any later version. 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details. 
* You should have received a copy of the GNU General Public License 
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
* 
* @package OpenEMR 
* @author Ray Magauran <magauran@MedFetch.com> 
* @link http://www.open-emr.org 
*/

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "eye_mag";
$table_name = "form_eye_mag";
$form_folder = "eye_mag";
include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
formHeader("Form: ".$form_name);
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$escapedGet = array_map('mysql_real_escape_string', $_REQUEST); @extract($escapedGet);
$escapedGet = array_map('mysql_real_escape_string', $_SESSION); @extract($escapedGet);




if (!$user) $user = $_SESSION['authUser'];
if (!$group) $group = $_SESSION['authProvider'];

if (!$encounter) $encounter = date("Ymd");
$query = "select * from form_encounter where pid ='$pid' and encounter= '$encounter'";
$encounter_data = sqlQuery($query);
$encounter_date = $encounter_data[date];

/**
  * This creates the imaging categories in Administration -> Practice -> Documents 
  * for form_eye_mag if not present.
  */
$exists = sqlQuery("SELECT count(*) from categories where name ='Imaging' and parent = '3'");
if (!$exists) {
    $sql = "INSERT INTO categories 
                select (select MAX(id) from categories) + 1, 'Imaging', '', 3, rght, rght + 1 
                from categories where name = 'Categories'";
    sqlQuery($sql);  
    $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Categories'";
    sqlQuery($sql);    
    $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Medical Record'";
    sqlQuery($sql);    
}     
$zones = array('FA/ICG','OCT','Optic Disc','Photos - AntSeg','Photos - External','Photos - Retina','Radiology','VF');
for ($i = 0; $i < count($zones); ++$i) {
    $test = '';
    $test = sqlQuery("SELECT count(*) from categories where name ='".$zones[$i]."'");
    if ($test =='') {
        $sql = "INSERT INTO categories 
        select (select MAX(id) from categories) + 1, '".$zones[$i]."', '', (select id from categories where name='Imaging' and parent=3), rght, rght + 1 
        from categories where name = 'Imaging' and parent='3'";
        sqlQuery($sql);       

        $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Categories'";
        sqlQuery($sql);    
        $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Medical Record'";
        sqlQuery($sql);    
        $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Imaging'";
    }

    $sql = "UPDATE categories_seq SET id = (select MAX(id) from categories)";
    sqlQuery($sql);     
}

$erow = sqlQuery("SELECT count(*) AS count " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = '$pid' AND fe.date = '" . $encounter_date . "' AND " .
    "f.formdir = 'eye_mag' AND f.encounter = fe.encounter AND f.deleted = 0");
if ($erow['count'] > 0) {
    require_once("view.php");
    exit;
}  else {

    //is this the first time Eye_Mag has been run???
    //Check the DB for ANY records.  If none exist, we have some setup work to do...

    $erow = sqlQuery("SELECT count(*) AS count FROM form_eye_mag");
    if ($erow['count'] == 0) {
        /**
         *  This is what we want:
         *   Documents(1) -> Medical Record(3) -->
         *                                  -> Imaging  ->
         *                                              -> FA/ICG
         *                                              -> OCT
         *                                              -> Optic Disc
         *                                              -> Photos - AntSeg
         *                                              -> Photos - External
         *                                              -> Photos - Retina
         *                                              -> Radiology
         *                                              -> VF
         */
        if (!$exists = sqlQuery("SELECT count(*) from categories where name ='Imaging' and Parent = '3'")) {
            //Imaging is not here, make them all...
            //$url = "http://www.oculoplasticsllc.com/openemr/controller.php?practice_settings&document_category&action=add_node&parent_id=3&name=Imaging&Add%20Category=Add%20Category&parent_is=14&process=hidden";
            //$homepage = file_get_contents($url);
            //echo $homepage;
            //exit;

            $sql = "INSERT INTO categories 
                select (select MAX(id) from categories) + 1, 'Imaging', '', 3, rght, rght + 1 
                from categories where name = 'Categories'";
            sqlQuery($sql);  
            $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Categories'";
            sqlQuery($sql);    
            $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Medical Record'";
            sqlQuery($sql);    
                
            $zones = array('FA/ICG','OCT','Optic Disc','Photos - AntSeg','Photos - External','Photos - Retina','Radiology','VF');
            
            $parent = sqlQuery('select parent from categories where name="Imaging"');
            for ($i = 0; $i < count($zone); ++$i) {
                $sql = "INSERT INTO categories 
                select (select MAX(id) from categories) + 1, 'FA/ICG', '', $parent, rght, rght + 1 
                from categories where name = 'Imaging'";
                //echo $sql."<br />";
                sqlQuery($sql);       

                $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Categories'";
                sqlQuery($sql);    
                $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Medical Record'";
                sqlQuery($sql);    
                $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Imaging'";
            }
            $sql = "UPDATE categories_seq SET id = (select MAX(id) from categories)";
            echo $sql."<br />";
            sqlQuery($sql);     
        } 
    }
//    $newid = formSubmit($table_name, $_POST, $id, $userauthorized);
    $sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
        "user, groupname, authorized, formdir) values (";
        
        /*if($date == "NOW()")
            $sql .= "$date";
        else
            $sql .= "NOW()";
        // $sql .= "'$date'";

        //if the encounter note is not being written on the day of the visit, 
        //perhaps we can add it that way since we use this field for PRIORS
        //apparently there is a session variable with the encounter date - lastcalcdate?
        //Could it be that simple? Maybe
        //Why not use encounter_date?
        */

            $sql .= "'$encounter_date'";
            $sql .= ", '$encounter',  '$form_name', '$newid', '$pid', '$user',          " .
            "'$group', '$authorized', '$form_folder')";
//echo $sql;
sqlInsert( $sql );
}

require_once("view.php");
exit;

?>

