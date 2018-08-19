<?php
    /**
     * /interface/main/mobile/m_save.php
     *
     * @package MedEx
     * @link    http://www.MedExBank.com
     * @author  MedEx <support@MedExBank.com>
     * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
    
    require_once "../../globals.php";
    require_once "$srcdir/acl.inc";
    require_once "$srcdir/documents.php";
    require_once "$srcdir/forms.inc";
    require_once "$srcdir/patient.inc";
    require_once "$srcdir/options.inc.php";
    
    $action = $_REQUEST['go'];
    $setting_mRoom = prevSetting('', 'setting_mRoom', 'setting_mRoom', '');
    
    //echo "setting_mFind =$setting_mFind and setting_mRoom =$setting_mRoom";
    
    if ($action == 'pat_search') {
       // if (!$_GET['term']) exit;
        $param = "%" . $_GET['term'] . "%";
        $query = "SELECT * FROM patient_data WHERE fname LIKE ? OR lname LIKE ? ORDER by lname DESC, fname DESC LIMIT 10";
        $result = sqlStatement($query, array($param, $param));
        while ($frow = sqlFetchArray($result)) {
            $data['Label'] = 'Name';
            $data['value'] = $frow['fname'] . " " . $frow['lname'];
            $data['pid'] = $frow['pid'];
            //whatelese do we need...
            $results[] = $data;
        }
        //echo $query. " -- ".$param;
        echo json_encode($results);
        exit;
    }
    
        //save it
    if ($action == "save_media") {
        /*
         echo ":: data received via GET ::\n\n";
         print_r($_GET);
     
         echo "\n\n:: Data received via POST ::\n\n";
         print_r($_POST);
     
         echo "\n\n:: Data received as \"raw\" (text/plain encoding) ::\n\n";
         if (isset($HTTP_RAW_POST_DATA)) { echo $HTTP_RAW_POST_DATA; }
     
         echo "\n\n:: Files received ::\n\n";
         print_r($_FILES);
        */
       
       if (!empty($_FILES)) {
           $name     = $_FILES['file']['name'];
           $type     = $_FILES['file']['type'];
           $tmp_name = $_FILES['file']['tmp_name'];
           $size     = $_FILES['file']['size'];
           $error    = $_FILES['file']['error'];
           $owner    = $GLOBALS['userauthorized'];
           
           if (preg_match('/image/', text($type))) {
               image_fix_orientation($tmp_name);
           }
           $return = addNewDocument($name, $type, $tmp_name, $error, $size, $owner, $_REQUEST['pid'], $_REQUEST['category']);
           //var_dump($return);
           $task['DOC_ID'] = $return['doc_id'];
           //$task['DOC_url'] = $filepath.'/'.$filename;
           //echo "File uploaded!";
            if ($_POST['encounter']) {
                $sql = "UPDATE documents set encounter_id=? where id=?"; //link it to this encounter
                sqlQuery($sql, array($encounter, $task['DOC_ID']));
            }
            $task['message'] = "File successfully uploaded.";
            
            echo json_encode($task);
            exit;
       } else {
           echo "Nothing to do...";
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
        $results_byRoom = sqlStatement($query,array($_REQUEST['room']));
        while ($row = sqlFetchArray($results_byRoom)) {
            $row['room'] = $_REQUEST['setting_mRoom'];
            $people[] = $row;
        }
        //var_dump($results_byRoom);
        echo json_encode($people);
        exit;
    }
    
    function image_fix_orientation($filename) {
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
    
    ?>
   