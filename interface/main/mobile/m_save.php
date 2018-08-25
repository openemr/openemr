<?php
    /**
     * /interface/main/mobile/m_save.php
     *
     * @package Mobile OpenEMR
     * @link    http://www.open-emr.org
     * @author  MedEx <support@MedExBank.com>
     * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
    
    require_once "../../globals.php";
    require_once "$srcdir/acl.inc";
    require_once "$srcdir/documents.php";
    require_once "$srcdir/forms.inc";
    require_once "$srcdir/patient.inc";
    require_once "$srcdir/options.inc.php";
    
    //need to check acl to add documents??
    
    $action = $_REQUEST['go'];
    $setting_mRoom = prevSetting('', 'setting_mRoom', 'setting_mRoom', '');
    
if ($action == 'pat_search') {
    // if (!$_GET['term']) exit;
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
