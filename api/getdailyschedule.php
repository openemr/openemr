<?php

header("Content-Type:text/xml");
require_once 'includes/class.database.php';
require_once 'includes/functions.php';
$myDate = $_POST['searchDate'];
$myProvider = $_POST['providerID'];

$xml_string = "";
$xml_string .= "<AppointmentsList>\n";

$strQuery = "SELECT	openemr_postcalendar_events.pc_startTime as time, 
			lang_constants.constant_name as status,
			openemr_postcalendar_events.pc_title as type, 
			openemr_postcalendar_events.pc_hometext as notes, 
			patient_data.fname, 
			patient_data.mname, 
			patient_data.lname,
			facility.name as location,
			DATE_FORMAT(openemr_postcalendar_events.pc_eventDate, '%c/%d/%Y') as dos,
			openemr_postcalendar_events.pc_pid
		FROM openemr_postcalendar_events 
		INNER JOIN lang_constants ON left(openemr_postcalendar_events.pc_apptstatus,2) = left(lang_constants.constant_name, 2) 
		INNER JOIN patient_data ON openemr_postcalendar_events.pc_pid = patient_data.id
		INNER JOIN facility ON openemr_postcalendar_events.pc_facility = facility.id";

if (!empty($myDate)) {
    $strQuery .= " WHERE openemr_postcalendar_events.pc_eventDate = STR_TO_DATE('$myDate', '%m/%d/%Y')";
}

if (!empty($myProvider)) {
    if (!empty($myDate)) {
        $strQuery .= " AND ";
    } else {
        $strQuery .= " WHERE ";
    }
    $strQuery .= "openemr_postcalendar_events.pc_aid = '$myProvider'";
}


$strQuery .= " ORDER BY dos, time";


$dbresult = $db->query($strQuery);
if ($dbresult) {
    $xml_string .= "<status>0</status>\n";
    $xml_string .= "<reason>The daily schedule Record has been fetched</reason>\n";
    $counter = 0;

    while ($row = $db->get_row($query = $strQuery, $output = ARRAY_A, $y = $counter)) {
        $xml_string .= "<Appointment>\n";
        $ref_pateintid = -1;
        foreach ($row as $fieldname => $fieldvalue) {
            if ($fieldname == "pc_pid")
                $ref_pateintid = $fieldvalue;

            $rowvalue = xmlsafestring($fieldvalue);
            $xml_string .= "<$fieldname>$rowvalue</$fieldname>\n";
        } // foreach

        $strQuery2 = "SELECT procedure_type.name AS ordertitle, procedure_order.order_status AS orderstatus
				FROM procedure_order, procedure_type 
				WHERE procedure_order.procedure_type_id = procedure_type.procedure_type_id AND 
					procedure_order.patient_id = '$ref_pateintid'";

        $dbresult2 = $db->query($strQuery2);
        if ($dbresult2) {
            $xml_string .= "<OrdersList>\n";
            $counter2 = 0;
            while ($row2 = $db->get_row($query = $strQuery2, $output = ARRAY_A, $y = $counter2)) {
                $xml_string .= "<Order>\n";
                foreach ($row2 as $fieldname => $fieldvalue) {
                    $rowvalue2 = xmlsafestring($fieldvalue);
                    $xml_string .= "<$fieldname>$rowvalue2</$fieldname>\n";
                } // foreach
                $counter2++;
                $xml_string .= "<orderimage>Encounter_order_bottle.png</orderimage>\n";
                $xml_string .= "</Order>\n";
            }
            $xml_string .= "</OrdersList>\n";
        }
        $xml_string .= "<link></link>\n";
        $xml_string .= "<link2></link2>\n";
        $xml_string .= "</Patient>\n";
        $counter++;
    } // while
} else {
    $xml_string .= "<status>-1</status>\n";
    $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
}
$xml_string .= "</AppointmentsList>\n";

echo $xml_string;
?>