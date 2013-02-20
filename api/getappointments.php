<?php
header ("Content-Type:text/xml"); 
require_once 'includes/class.database.php';
require_once 'includes/functions.php';

$token = $_POST['token'];
$patientId = $_POST['patientId'];

$xml_string = "";
$xml_string .= "<Appointments>\n";


if ($userId = validateToken($token)) {
    $user = getUsername($userId);

    $acl_allow = acl_check('patients', 'appt', $user);
    if ($acl_allow) {

        $strQuery = "SELECT  pd.*, pd.fname as firstname, pd.lname as lastname, ope.pc_eid as appointmentId, 
                                    ope.pc_pid as patientId, ope.pc_title as location, ope.pc_hometext as reason,  
                                    ope.pc_eventDate as appointmentDate, ope.pc_startTime as startTime, 
                                    ope.pc_endTime as endTime 
                                FROM openemr_postcalendar_events as ope, patient_data as pd 
                                WHERE pd.pid=ope.pc_pid";
        if (isset($patientId)) {
            $strQuery .= " AND ope.pc_pid=" . $patientId;
        }

        $dbresult = $db->query($strQuery);
        echo $dbresult;

        if ($dbresult) {
            $xml_string .= "<status>0</status>\n";
            $xml_string .= "<reason>Success processing patient appointments records</reason>\n";
            $counter = 0;

            while ($row = $db->get_row($query = $strQuery, $output = ARRAY_A, $y = $counter)) {
                $xml_string .= "<Appointment>\n";

                foreach ($row as $fieldname => $fieldvalue) {
                    $rowvalue = xmlsafestring($fieldvalue);
                    $xml_string .= "<$fieldname>$rowvalue</$fieldname>\n";
                }

                $xml_string .= "</Appointment>\n";
                $counter++;
            }
        } else {
            $xml_string .= "<status>-1</status>\n";
            $xml_string .= "<reason>Could not find results</reason>\n";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}


$xml_string .= "</Appointments>\n";
echo $xml_string;
?>