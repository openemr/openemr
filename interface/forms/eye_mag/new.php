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

$query = "SELECT count(*) AS count " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? AND fe.date = ? AND " .
    "f.formdir = 'eye_mag' AND f.encounter = fe.encounter AND f.deleted = 0";
$erow = sqlQuery($query,array($pid,$encounter_date));
if ($erow['count'] > 0) {
    require_once("view.php");
    exit;
}  else {
    //Is this the first time Eye_Mag has been run???
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
         *                                  -> Communication
         */
        if (!$exists = sqlQuery("SELECT count(*) from categories where name ='Imaging' and Parent = '3'")) {
            /**
              * Imaging is not here, make them all...
              * This creates the imaging and Communication categories in Administration -> Practice -> Documents 
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

                $sql = "INSERT INTO categories 
                            select (select MAX(id) from categories) + 1, 'Communication', '', 3, rght, rght + 1 
                            from categories where name = 'Categories'";
                sqlQuery($sql);  
                $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Categories'";
                sqlQuery($sql);    
                $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Medical Record'";
                sqlQuery($sql);    
            }     
            $CLINICAL_zone = array("EXT","ANTSEG","POSTSEG","NEURO");
            for ($a = 0; $b < count($CLINICAL_zone); ++$a) {
                $sql = "INSERT INTO categories 
                    select (select MAX(id) from categories) + 1, '".$zones[$i]."', '".$category_value."', (select id from categories where name='Imaging' and parent=3), rght, rght + 1 
                    from categories where name = 'Imaging' and parent='3'";
                    sqlQuery($sql);       

                    $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Categories'";
                    sqlQuery($sql);    
                    $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Medical Record'";
                    sqlQuery($sql);    
                    $sql = "UPDATE categories SET rght = rght + 2 WHERE name = 'Imaging'";

            }

            $zone = array('FA/ICG','OCT','Optic Disc','AntSeg Photos','External Photos','Fundus','Radiology','VF');
            $zones[0]['CLINICAL_zone'] = "POSTSEG";
            $zones[1]['CLINICAL_zone'] = "POSTSEG";
            $zones[2]['CLINICAL_zone'] = "POSTSEG";
            $zones[3]['CLINICAL_zone'] = "ANTSEG";
            $zones[4]['CLINICAL_zone'] = "EXT";
            $zones[5]['CLINICAL_zone'] = "POSTSEG";
            $zones[6]['CLINICAL_zone'] = "NEURO";
            $zones[7]['CLINICAL_zone'] = "NEURO";
            
            for ($i = 0; $i < count($zones); ++$i) {
                $test = '';
                $test = sqlQuery("SELECT count(*) from categories where name ='".$zones[$i]."'");
                if ($test =='') {
                    if ($zones[$i] = '') 
                    $category_value = $zones[$i]['CLINICAL_zone'];
                    $sql = "INSERT INTO categories 
                    select (select MAX(id) from categories) + 1, '".$zone[$i]."', '".$category_value."', (select id from categories where name='Imaging' and parent=3), rght, rght + 1 
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
            $sql = "INSERT INTO `issue_types` (`active`, `category`, `type`, `plural`, `singular`, `abbreviation`, `style`, `force_show`, `ordering`) 
                                        VALUES ('1','default','eye','Past Ocular History','POH','O','0','0','4')";
            sqlQuery($sql);     
            //if we want to add Ophthalmic surgeries, this would be a spot...                      
        } 
    }
    $id = $erow['count']++;
    $newid = formSubmit($table_name, $_POST, $id, $userauthorized);
  
    $sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
        "user, groupname, authorized, formdir) values (";
    $sql .= "'$encounter_date'";
    $sql .= ", '$encounter','$form_name','$newid', '$pid', '$user', '$group', '$authorized', '$form_folder')";
    $answer = sqlInsert( $sql );
 }

require_once("view.php");
exit;

?>

