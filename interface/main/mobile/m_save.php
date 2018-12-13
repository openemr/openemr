<?php
    /**
     * /interface/main/mobile/m_save.php
     *
     * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
     *
     * LICENSE: This program is free software: you can redistribute it and/or modify
     *  it under the terms of the GNU Affero General Public License as
     *  published by the Free Software Foundation, either version 3 of the
     *  License, or (at your option) any later version.
     *
     *  This program is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU Affero General Public License for more details.
     *
     *  You should have received a copy of the GNU Affero General Public License
     *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
     *
     * @package OpenEMR
     * @author Ray Magauran <magauran@MedExBank.com>
     * @link http://www.open-emr.org
     * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
    
    require_once "../../globals.php";
    require_once "$srcdir/acl.inc";
    require_once "$srcdir/documents.php";
    require_once "$srcdir/forms.inc";
    require_once "$srcdir/patient.inc";
    require_once "$srcdir/options.inc.php";
    require_once("$srcdir/MedEx/API.php");
    
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    
    //need to check acl to add documents??
    $action = $_REQUEST['go'];
    $setting_mRoom = prevSetting('', 'setting_mRoom', 'setting_mRoom', '');
    
    if ($action == 'pat_search') {
        if (!$_GET['term']) exit;
        $param = "%" . $_GET['term'] . "%";
        $query = "SELECT * FROM patient_data WHERE fname LIKE ? OR lname LIKE ? ORDER by lname DESC, fname DESC LIMIT 10";
        $result = sqlStatement($query, array($param, $param));
        while ($frow = sqlFetchArray($result)) {
            $data['Label']  = text('Name');
            $data['value']  = text($frow['fname']) . " " . text($frow['lname']);
            $data['pid']    = text($frow['pid']);
            //whatelese do we need...
            $results[]      = $data;
        }
        echo json_encode($results);
        exit;
    }
    
    if ($action == 'sms_search') {
        if (!$_GET['term']) exit;
        $query = "SELECT * FROM patient_data WHERE pid like ? ORDER by lname DESC, fname DESC LIMIT 1";
        $result = sqlStatement($query, array($_GET['term']));
        while ($frow = sqlFetchArray($result)) {
            $data['Label']  = 'Name';
            $data['value']  = text($frow['fname'] . " " . $frow['lname']);
            $data['pid']    = text($frow['pid']);
            
            $data['mobile'] = text($frow['phone_cell']);
            $data['home_phone'] = text($frow['phone_home']);
            $data['allow']  = text($frow['hipaa_allowsms']);
            $sql = "SELECT * FROM `medex_outgoing` where msg_pid=? ORDER BY `medex_outgoing`.`msg_uid` DESC LIMIT 1";
            //$data['sql'] = $sql;
            $result2 = sqlQuery($sql, array($frow['pid']));
            $data['msg_last_updated'] = $result2['msg_date'];
            $data['medex_uid'] = $result2['medex_uid'];
        }
        
        echo json_encode($data);
        exit;
    }
    
    if ($action == 'search_Docs') {
        //we are looking for a doc list
        if ($_REQUEST['pid'] < '1') { exit; }
        $docs = document_engine($_REQUEST['pid'], $_REQUEST['category']);
        //$result = array2jstree($docs);
        for ($i=0; $i< count($docs[0]); $i++) {
           // $out .= "<pre>".print_r($docs, true)."</pre>";
            $out .= '<div class="col-xs-6 col-sm-6 col-md-6 col-md-offset-1 col-lg-4 text-center custom-file-upload">';
            $out .="<div class='card bg-success'>
                    <img type='".$docs[0][$i]['mimetype']."'
                    class='shrinkToFit'
                    src='/openemr/controller.php?document&retrieve&patient_id=".$docs[0][$i]['foreign_id']."&document_id=".$docs[0][$i]['id']."&as_file=false' />
<div class='card-body'><b class='card-title'>". text($docs[0][$i]['name'])." (".text($docs[0][$i]['date']).") <br></b></div>
</div></div>";
        }
        echo $out;
        exit;
    }

     if ($GLOBALS['medex_enable'] == '1') {
         if ( ($_REQUEST['go'] == 'SMS_refresh') ||
              ($_REQUEST['SMS_bot'] == '1')
            ) {
                $result = $MedEx->login('1');
                //echo $result['status'];
                $MedEx->display->SMS_bot($result['status']);
                exit();
            }
        $result = $MedEx->login();
        $logged_in = $result['status'];
    }
    
    if ($action == 'sms_search_old') {
        $query = "SELECT * FROM patient_data WHERE pid like ? ORDER by lname DESC, fname DESC LIMIT 1";
        $result = sqlStatement($query, array($_GET['term']));
        while ($frow = sqlFetchArray($result)) {
            $data['query']  = $query." + ".$_GET['term'];
            $data['Label']  = text('Name');
            $data['value']  = text($frow['fname']) . " " . text($frow['lname']);
            $data['pid']    = text($frow['pid']);
            $data['mobile'] = text($frow['phone_cell']);
            $data['allow']  = text($frow['hipaa_allowsms']);
            //whatelese do we need...
        }
        echo json_encode($data);
        exit;
    }
    
    if ($action == "save_media") {
        if (!empty($_FILES)) {
            foreach ($_FILES as $file) {
                $name        = $file['name'];
                $type        = $file['type'];
                $tmp_name    = $file['tmp_name'];
                $size        = $file['size'];
                $error       = $file['error'];
                $owner       = $GLOBALS['userauthorized'];
                
                if (preg_match('/image/', text($type))) {
                    image_fix_orientation($tmp_name);
                }
                if ($error != 0) {
                    continue;
                }
                $returns = addNewDocument($name, $type, $tmp_name, $error, $size, $owner, $_REQUEST['pid'], $_REQUEST['category']);
                $task['DOC_ID'] = $returns['doc_id'];
                //$task['DOC_url'] = $filepath.'/'.$filename;
                /*
                if ($_POST['encounter']) {
                    $sql = "UPDATE documents set encounter_id=? where id=?"; //link it to this encounter
                   sqlQuery($sql, array($encounter, $task['DOC_ID']));
                }
                */
                $count++;
                if ($count==1) {
                    $task['message'] = xlt("File successfully uploaded.");
                } elseif ($count > 1) {
                    $task['message'] = xlt("Files successfully uploaded.");
                }
            }
            echo json_encode($task);
            exit;
        } else {
            echo xlt("Nothing to do...");
        }
    }
    
    if ($action == "byRoom") {
        // We are getting a list of who is in which room.
        // If there are more than one in a room, eg. waiting room or staff error, we need to list all there...
        if (!$_REQUEST['room']) { exit; }
        if ($_REQUEST['room']=='all') {
            $query = "select fname,lname,pid from patient_data
                  where pid in (
                    SELECT pc_pid FROM `openemr_postcalendar_events`
                    where pc_apptstatus in (
                      SELECT option_id FROM list_options
                      WHERE list_id = 'apptstat' and toggle_setting_1 ='1' and activity='1'
                    ) and
                  pc_eventDate = CURDATE() )";
            $results_byRoom = sqlStatement($query);
            while ($row = sqlFetchArray($results_byRoom)) {
                //$row['room'] = text($_REQUEST['setting_mRoom']);
                $people[] = $row;
            }
        } else {
            $query = "select fname,lname,pid from patient_data
                  where pid in (
                    SELECT pc_pid FROM `openemr_postcalendar_events`
                    where pc_room=? and pc_apptstatus in (
                      SELECT option_id FROM list_options
                      WHERE list_id = 'apptstat' and toggle_setting_1 ='1' and activity='1'
                    ) and
                  pc_eventDate = CURDATE() )";
            //echo $query;
            $results_byRoom = sqlStatement($query, array($_REQUEST['room']));
            while ($row = sqlFetchArray($results_byRoom)) {
                $row['room'] = text($_REQUEST['setting_mRoom']);
                $people[] = $row;
            }
        }
        //var_dump($results_byRoom);
        echo json_encode($people);
        exit;
    }
    
    function image_fix_orientation($filename)
    {
        $exif = exif_read_data($filename);
        if (!empty($exif['Orientation'])) {
            $image = imagecreatefromjpeg($filename);
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;
                    
                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;
                    
                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
            }
            imagejpeg($image, $filename, 90);
        }
    }
    
    /**
     *  This function builds an array of documents for this patient ($pid).
     *  We first list all the categories this practice has created by name and by category_id
     *  for this patient ($pid)
     *  Each document info from documents table is added to these as arrays
     *
     *  @param string $pid patient_id
     *  @return array($documents)
     */
    function document_engine($pid, $category='')
    {
        if (empty($pid)) { return false; }
        $sql1 =  sqlStatement("Select * from categories");
        while ($row1 = sqlFetchArray($sql1)) {
            $categories[] = $row1;
            $my_name[$row1['id']] = $row1['name'];
            $children_names[$row1['parent']][]=$row1['name'];
            $parent_name[$row1['name']] = $my_name[$row1['parent']];
            if ($row1['value'] >'') {
                //if there is a value, tells us what segment of exam ($zone) this belongs in...
                $zones[$row1['value']][] = $row1;
            } else {
                if ($row1['name'] != "Categories") {
                    $zones['OTHER'][] = $row1;
                }
            }
        }
        
        $query = "Select *
                from
                categories, documents,categories_to_documents
                where documents.foreign_id=? and documents.id=categories_to_documents.document_id and
                categories_to_documents.category_id=categories.id ORDER BY categories.name";
        $sql2 =  sqlStatement($query, array($pid));
        while ($row2 = sqlFetchArray($sql2)) {
            //the document may not be created on the same day as the encounter, use encounter date first
            //get encounter date from encounter id
            if ($row2['encounter_id']) {
                $visit= getEncounterDateByEncounter($row2['encounter_id']);
                $row2['encounter_date'] = oeFormatSDFT(strtotime($visit['date']));
            } else {
                $row2['encounter_date'] = $row2['docdate'];
            }
            if ($category) {
                if ($row2['category_id'] != $category) {
                    continue;
                }
            }
            $documents[]= $row2;
            $docs_in_cat_id[$row2['category_id']][] = $row2;
            if ($row2['value'] > '') {
                $docs_in_zone[$row2['value']][] = $row2;
            } else {
                $docs_in_zone['OTHER'][]=$row2;
            }
            
            $docs_in_name[$row2['name']][] = $row2;
            $docs_by_date[$row2['encounter_date']][] = $row2;
        }
        
        return array($documents);
    }