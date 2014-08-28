<?php
#require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once(dirname(__FILE__). "/sql.inc");
require_once(dirname(__FILE__). "/formdata.inc.php");

function newEvent($event, $user, $groupname, $success, $comments="", $patient_id=null) {
    $adodb = $GLOBALS['adodb']['db'];
    $crt_user=isset($_SERVER['SSL_CLIENT_S_DN_CN']) ?  $_SERVER['SSL_CLIENT_S_DN_CN'] : null;
    /* More details added to the log */
    $sql = "insert into log ( date, event, user, groupname, success, comments, crt_user, patient_id) " .
            "values ( NOW(), " . $adodb->qstr($event) . "," . $adodb->qstr($user) .
            "," . $adodb->qstr($groupname) . "," . $adodb->qstr($success) . "," .
            $adodb->qstr($comments) ."," .
            $adodb->qstr($crt_user) ."," . $adodb->qstr($patient_id). ")";

    $ret = sqlInsertClean_audit($sql);
    if(($patient_id=="NULL") || ($patient_id==null))$patient_id=0;

    send_atna_audit_msg($user, $groupname, $event, $patient_id, $success, $comments);
}

function getEventByDate($date, $user="", $cols="DISTINCT date, event, user, groupname, patient_id, success, comments, checksum")
{
    $sql = "SELECT $cols FROM log WHERE date >= '$date 00:00:00' AND date <= '$date 23:59:59'";
    if ($user) $sql .= " AND user LIKE '$user'";
    $sql .= " ORDER BY date DESC LIMIT 5000";
    $res = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    return $all;
}

/******************
 * Get records from the LOG and Extended_Log table
 * using the optional parameters:
 *   date : a specific date  (defaults to today)
 *   user : a specific user  (defaults to none)
 *   cols : gather specific columns  (defaults to date,event,user,groupname,comments)
 *   sortby : sort the results by  (defaults to none)
 * RETURNS:
 *   array of results
 ******************/
function getEvents($params) 
{
    // parse the parameters
    $cols = "DISTINCT date, event, user, groupname, patient_id, success, comments,checksum,crt_user, id ";
    if (isset($params['cols']) && $params['cols'] != "") $cols = $params['cols'];

    $date1 = date("Y-m-d", time());
    if (isset($params['sdate']) && $params['sdate'] != "") $date1= $params['sdate'];
    
    $date2 = date("Y-m-d", time());
    if (isset($params['edate']) && $params['edate'] != "") $date2= $params['edate'];
    
    $user = "";
    if (isset($params['user']) && $params['user'] != "") $user= $params['user'];

    //VicarePlus :: For Generating log with patient id.
    $patient = "";
    if (isset($params['patient']) && $params['patient'] != "") $patient= $params['patient'];
    
    $sortby = "";
    if (isset($params['sortby']) && $params['sortby'] != "") $sortby = $params['sortby'];
    
    $levent = "";
    if (isset($params['levent']) && $params['levent'] != "") $levent = $params['levent'];
    
     $tevent = "";
    if (isset($params['tevent']) && $params['tevent'] != "") $tevent = $params['tevent'];
    
     $event = "";
    if (isset($params['event']) && $params['event'] != "") $event = $params['event'];
    if ($event!=""){
    if ($sortby == "comments") $sortby = "description";
    if ($sortby == "groupname") $sortby = ""; //VicarePlus :: since there is no groupname in extended_log
    if ($sortby == "success") $sortby = "";   //VicarePlus :: since there is no success field in extended_log
    if ($sortby == "checksum") $sortby = "";  //VicarePlus :: since there is no checksum field in extended_log
    $columns = "DISTINCT date, event, user, recipient,patient_id,description";
    $sql = "SELECT $columns FROM extended_log WHERE date >= '$date1 00:00:00' AND date <= '$date2 23:59:59'";
    if ($user != "") $sql .= " AND user LIKE '$user'";
    if ($patient != "") $sql .= " AND patient_id LIKE '$patient'";
    if ($levent != "") $sql .= " AND event LIKE '$levent%'";
    if ($sortby != "") $sql .= " ORDER BY ".$sortby." DESC "; // descending order
    $sql .= " LIMIT 5000";
    }
    else
    {
    // do the query
    $sql = "SELECT $cols FROM log WHERE date >= '$date1 00:00:00' AND date <= '$date2 23:59:59'";
    if ($user != "") $sql .= " AND user LIKE '$user'";
    if ($patient != "") $sql .= " AND patient_id LIKE '$patient'";
    if ($levent != "") $sql .= " AND event LIKE '$levent%'";
    if ($tevent != "") $sql .= " AND event LIKE '%$tevent'";
    if ($sortby != "") $sql .= " ORDER BY ".$sortby." DESC "; // descending order
    $sql .= " LIMIT 5000";
    }
    $res = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    return $all;
}

/* Given an SQL insert/update that was just performeds:
 * - Find the table and primary id of the row that was created/modified
 * - Calculate the SHA1 checksum of that row (with all the
 *   column values concatenated together).
 * - Return the SHA1 checksum as a 40 char hex string.
 * If this is not an insert/update query, return "".
 * If multiple rows were modified, return "".
 * If we're unable to determine the row modified, return "".
 *
 * TODO: May need to incorporate the binded stuff (still analyzing)
 *
 */
function sql_checksum_of_modified_row($statement)
{
    $table = "";
    $rid = "";

    $tokens = preg_split("/[\s,(\'\"]+/", $statement);
    /* Identifying the id for insert/replace statements for calculating the checksum */
        if((strcasecmp($tokens[0],"INSERT")==0) || (strcasecmp($tokens[0],"REPLACE")==0)){
        $table = $tokens[2];
        $rid = mysql_insert_id($GLOBALS['dbh']);
	/* For handling the table that doesn't have auto-increment column */
        if ($rid === 0 || $rid === FALSE) {
          if($table == "gacl_aco_map" || $table == "gacl_aro_groups_map" || $table == "gacl_aro_map" || $table == "gacl_axo_groups_map" || $table == "gacl_axo_map")
           $id="acl_id";
          else if($table == "gacl_groups_aro_map" || $table == "gacl_groups_axo_map")
          $id="group_id";
          else
           $id="id";
	  /* To handle insert statements */
          if($tokens[3] == $id){
             for($i=4;$i<count($tokens);$i++){
		 if(strcasecmp($tokens[$i],"VALUES")==0){
                  $rid=$tokens[$i+1];
                     break;
                }// if close
              }//for close
            }//if close
	/* To handle replace statements */
          else if(strcasecmp($tokens[3],"SET")==0){
		 if((strcasecmp($tokens[4],"ID")==0) || (strcasecmp($tokens[4],"`ID`")==0)){
                  $rid=$tokens[6];
           }// if close
        }

	else {		
            return "";
          }
        }
    }
     /* Identifying the id for update statements for calculating the checksum */
       else if(strcasecmp($tokens[0],"UPDATE")==0){
        $table = $tokens[1];

        $offset = 3;
        $total = count($tokens);

        /* Identifying the primary key column for the updated record */ 
        if ($table == "form_physical_exam") {
            $id = "forms_id";
        }
	else if ($table == "claims"){
	    $id = "patient_id";
	}
	else if ($table == "openemr_postcalendar_events") {
	    $id = "pc_eid";
	}
	 else if ($table == "lang_languages"){
            $id = "lang_id";
	 }
	 else if ($table == "openemr_postcalendar_categories" || $table == "openemr_postcalendar_topics"){
            $id = "pc_catid";
	 }
	 else if ($table == "openemr_postcalendar_limits"){
            $id = "pc_limitid";
	 }
         else if($table == "gacl_aco_map" || $table == "gacl_aro_groups_map" || $table == "gacl_aro_map" || $table == "gacl_axo_groups_map" || $table == "gacl_axo_map"){
           $id="acl_id";
          }
          else if($table == "gacl_groups_aro_map" || $table == "gacl_groups_axo_map"){
          $id="group_id";
          }
	   else {
            $id = "id";
           }
        
         /* Identifying the primary key value for the updated record */
        while ($offset < $total) {
            /* There are 4 possible ways that the id=123 can be parsed:
             * ('id', '=', '123')
             * ('id=', '123')
             * ('id=123')
	     * ('id', '=123')
             */
            $rid = "";
           /*id=', '123'*/
            if (($tokens[$offset] == "$id=") && ($offset + 1 < $total)) {
                $rid = $tokens[$offset+1];
                break;
            }
	   /* 'id', '=', '123' */
            else if ($tokens[$offset] == "$id" && $tokens[$offset+1] == "=" && ($offset+2 < $total)) {
                $rid = $tokens[$offset+2];
                break;
             }
            /*id=123*/
   	    else if (strpos($tokens[$offset], "$id=") === 0) {
                $tid = substr($tokens[$offset], strlen($id)+1);
		if(is_numeric($tid))
		 $rid=$tid;
		 break;
             }
	   /*'id', '=123' */
	     else if($tokens[$offset] == "$id") {
                $tid = substr($tokens[$offset+1],1);
                if(is_numeric($tid))
                 $rid=$tid;
                break;
	      }	
            $offset += 1;
        }//while ($offset < $total)
    }// else if ($tokens[0] == 'update' || $tokens[0] == 'UPDATE' )

    if ($table == "" || $rid == "") {
        return "";
    }
   /* Framing sql statements for calculating checksum */ 
   if ($table == "form_physical_exam") {
        $sql = "select * from $table where forms_id = $rid";
    }
   else if ($table == "claims"){
		$sql = "select * from $table where patient_id = $rid";
        }
   else if ($table == "openemr_postcalendar_events") {
            $sql = "select * from $table where pc_eid = $rid";
        }
    else if ($table == "lang_languages") {
            $sql = "select * from $table where lang_id = $rid";
    }
    else if ($table == "openemr_postcalendar_categories" || $table == "openemr_postcalendar_topics"){
	    $sql = "select * from $table where pc_catid = $rid";
    	 }
    else if ($table == "openemr_postcalendar_limits"){
           $sql = "select * from $table where pc_limitid = $rid";
	 }
    else if ($table ==  "gacl_aco_map" || $table == "gacl_aro_groups_map" || $table == "gacl_aro_map" || $table == "gacl_axo_groups_map" || $table == "gacl_axo_map"){
           $sql = "select * from $table where acl_id = $rid";
         }
     else if($table == "gacl_groups_aro_map" || $table == "gacl_groups_axo_map"){
	   $sql = "select * from $table where group_id = $rid";
      }
     else {
        $sql = "select * from $table where id = $rid";
    }
    // When this function is working perfectly, can then shift to the
    // sqlQueryNoLog() function.
    $results = sqlQueryNoLogIgnoreError($sql);    
    $column_values = "";
   /* Concatenating the column values for the row inserted/updated */
    if (is_array($results)) {
        foreach ($results as $field_name => $field) {
            $column_values .= $field;
        }
    }
    // ViCarePlus: As per NIST standard, the encryption algorithm SHA1 is used

    //error_log("COLUMN_VALUES: ".$column_values,0);
    return sha1($column_values);
}

/* Create an XML audit record corresponding to RFC 3881.
 * The parameters passed are the column values (from table 'log')
 * for a single audit record.
 */
function create_rfc3881_msg($user, $group, $event, $patient_id, $outcome, $comments)
{

    /* Event action codes indicate whether the event is read/write.
     * C = create, R = read, U = update, D = delete, E = execute
     */
    $eventActionCode = 'E';
    if (substr($event, -7) == "-create") {
        $eventActionCode = 'C';
    }
    else if (substr($event, -7) == "-insert") {
        $eventActionCode = 'C';
    }
    else if (substr($event, -7) == "-select") {
        $eventActionCode = 'R';
    }
    else if (substr($event, -7) == "-update") {
        $eventActionCode = 'U';
    }
    else if (substr($event, -7) == "-delete") {
        $eventActionCode = 'D';
    }

    $date_obj = new DateTime();
    $eventDateTime = $date_obj->format(DATE_ATOM);

    /* For EventOutcomeIndicator, 0 = success and 4 = minor error */
    $eventOutcome = ($outcome === 1) ? 0 : 4;

    /* The choice of event codes is up to OpenEMR.
     * We're using the same event codes as
     * https://iheprofiles.projects.openhealthtools.org/
     */
    $eventIDcodeSystemName = "DCM";
    $eventIDcode = 0;
    $eventIDdisplayName = $event;

    if (strpos($event, 'patient-record') !== FALSE) {
        $eventIDcode = 110110;
        $eventIDdisplayName = 'Patient Record';
    }
    else if (strpos($event, 'view') !== FALSE) {
        $eventIDCode = 110110;
        $eventIDdisplayName = 'Patient Record';
    }
    else if (strpos($event, 'login') !== FALSE) {
        $eventIDcode = 110122;
        $eventIDdisplayName = 'Login';
    }
    else if (strpos($event, 'logout') !== FALSE) {
        $eventIDcode = 110123;
        $eventIDdisplayName = 'Logout';
    }
    else if (strpos($event, 'scheduling') !== FALSE) {
        $eventIDcode = 110111;
        $eventIDdisplayName = 'Patient Care Assignment';
    }
    else if (strpos($event, 'security-administration') !== FALSE) {
        $eventIDcode = 110129;
        $eventIDdisplayName = 'Security Administration';
    }


   

    /* Variables used in ActiveParticipant section, which identifies
     * the IP address and application of the source and destination.
     */
    $srcUserID = $_SERVER['SERVER_NAME'] . '|OpenEMR';
    $srcNetwork = $_SERVER['SERVER_ADDR'];
    $destUserID = $GLOBALS['atna_audit_host'];
    $destNetwork = $GLOBALS['atna_audit_host'];

    $userID = $user;
    $userTypeCode = 1;
    $userRole = 6;
    $userCode = 11;
    $userDisplayName = 'User Identifier';

    $patientID = "";
    $patientTypeCode = "";
    $patientRole = "";
    $patientCode = "";
    $patientDisplayName = "";

    if ($eventIdDisplayName == 'Patient Record') {
        $patientID = $patient_id;
        $pattientTypeCode = 1;
        $patientRole = 1;
        $patientCode = 2;
        $patientDisplayName = 'Patient Number';
    }

    /* Construct the XML audit message, and save to $msg */
    $msg =  '<?xml version="1.0" encoding="ASCII"?>';
    $msg .= '<AuditMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
    $msg .= 'xsi:noNamespaceSchemaLocation="healthcare-security-audit.xsd">';

    /* Indicate the event code, text name, read/write type, and date/time */
    $msg .= "<EventIdentification EventActionCode=\"$eventActionCode\" ";
    $msg .= "EventDateTime=\"$eventDateTime\" ";
    $msg .= "EventOutcomeIndicator=\"$eventOutcome\">";
    $msg .= "<EventID code=\"eventIDcode\" displayName=\"$eventIDdisplayName\" ";
    $msg .= "codeSystemName=\"DCM\" />";
    $msg .= "</EventIdentification>";

    /* Indicate the IP address and application of the source and destination */
    $msg .= "<ActiveParticipant UserID=\"$srcUserID\" UserIsRequestor=\"true\" ";
    $msg .= "NetworkAccessPointID=\"$srcNetwork\" NetworkAccessPointTypeCode=\"2\" >";
    $msg .= "<RoleIDCode code=\"110153\" displayName=\"Source\" codeSystemName=\"DCM\" />";
    $msg .= "</ActiveParticipant>";
    $msg .= "<ActiveParticipant UserID=\"$destUserID\" UserIsRequestor=\"false\" ";
    $msg .= "NetworkAccessPointID=\"$destNetwork\" NetworkAccessPointTypeCode=\"2\" >";
    $msg .= "<RoleIDCode code=\"110152\" displayName=\"Destination\" codeSystemName=\"DCM\" />";
    $msg .= "</ActiveParticipant>";

    $msg .= "<AuditSourceIdentification AuditSourceID=\"$srcUserID\" />";

    /* Indicate the username who generated this audit record */
    $msg .= "<ParticipantObjectIdentification ParticipantObjectID=\"$user\" ";
    $msg .= "ParticipantObjectTypeCode=\"1\" ";
    $msg .= "ParticipantObjectTypeCodeRole=\"6\" >";
    $msg .= "<ParticipantObjectIDTypeCode code=\"11\" ";
    $msg .= "displayName=\"User Identifier\" ";
    $msg .= "codeSystemName=\"RFC-3881\" /></ParticipantObjectIdentification>";

    if ($eventIDdisplayName == 'Patient Record' && $patient_id != 0) {
        $msg .= "<ParticipantObjectIdentification ParticipantObjectID=\"$patient_id\" ";
        $msg .= "ParticipantObjectTypeCode=\"1\" ";
        $msg .= "ParticipantObjectTypeCodeRole=\"1\" >";
        $msg .= "<ParticipantObjectIDTypeCode code=\"2\" ";
        $msg .= "displayName=\"Patient Number\" ";
        $msg .= "codeSystemName=\"RFC-3881\" /></ParticipantObjectIdentification>";
    }
    $msg .= "</AuditMessage>";

    /* Add the syslog header */
    $date_obj = new DateTime($date);
    $datestr= $date_obj->format(DATE_ATOM);
    $msg = "<13> " . $datestr . " " . $_SERVER['SERVER_NAME'] . " " . $msg;
    return $msg;
}


/* Create a TLS (SSLv3) connection to the given host/port.
 * $localcert is the path to a PEM file with a client certificate and private key.
 * $cafile is the path to the CA certificate file, for
 *  authenticating the remote machine's certificate.
 * If $cafile is "", the remote machine's certificate is not verified.
 * If $localcert is "", we don't pass a client certificate in the connection.
 *
 * Return a stream resource that can be used with fwrite(), fread(), etc.
 * Returns FALSE on error.
 */
function create_tls_conn($host, $port, $localcert, $cafile) {
    $sslopts = array();
    if ($cafile !== null && $cafile != "") {
        $sslopts['cafile'] = $cafile;
        $sslopts['verify_peer'] = TRUE;
        $sslopts['verify_depth'] = 10;
    }
    if ($localcert !== null && $localcert != "") {
        $sslopts['local_cert'] = $localcert;
    }
    $opts = array('tls' => $sslopts, 'ssl' => $sslopts);
    $ctx = stream_context_create($opts);
    $timeout = 60;
    $flags = STREAM_CLIENT_CONNECT;

    $olderr = error_reporting(0);
    $conn = stream_socket_client('tls://' . $host . ":" . $port, $errno, $errstr,
                                 $timeout, $flags, $ctx);
    error_reporting($olderr);
    return $conn;
}


/* This function is used to send audit records to an Audit Repository Server,
 * as described in the Audit Trail and Node Authentication (ATNA) standard.
 * Given the fields in a single audit record:
 * - Create an XML audit message according to RFC 3881, including the RFC5425 syslog header.
 * - Create a TLS connection that performs bi-directions certificate authentication,
 *   according to RFC 5425.
 * - Send the XML message on the TLS connection.
 */
function send_atna_audit_msg($user, $group, $event, $patient_id, $outcome, $comments)
{
    /* If no ATNA repository server is configured, return */
    if ($GLOBALS['atna_audit_host'] === null || $GLOBALS['atna_audit_host'] == "" || !($GLOBALS['enable_atna_audit'])) {
        return;
    }
    $host = $GLOBALS['atna_audit_host'];
    $port = $GLOBALS['atna_audit_port'];
    $localcert = $GLOBALS['atna_audit_localcert'];
    $cacert = $GLOBALS['atna_audit_cacert'];
    $conn = create_tls_conn($host, $port, $localcert, $cacert);
    if ($conn !== FALSE) {
        $msg = create_rfc3881_msg($user, $group, $event, $patient_id, $outcome, $comments);
        $len = strlen($msg);
        fwrite($conn, $msg);
        fclose($conn);
    }
}


/* Add an entry into the audit log table, indicating that an
 * SQL query was performed. $outcome is true if the statement
 * successfully completed.  Determine the event type based on
 * the tables present in the SQL query.
 */
function auditSQLEvent($statement, $outcome, $binds=NULL)
{

    $user =  isset($_SESSION['authUser']) ? $_SESSION['authUser'] : "";
	/* Don't log anything if the audit logging is not enabled. Exception for "emergency" users */
   if (!isset($GLOBALS['enable_auditlog']) || !($GLOBALS['enable_auditlog']))
   {
   	    if ((soundex($user) != soundex("emergency")) && (soundex($user) != soundex("breakglass")))
   	    return;
   }


   $statement = trim($statement);

    /* Don't audit SQL statements done to the audit log,
     * or we'll have an infinite loop.
     */
    if ((stripos($statement, "insert into log") !== FALSE) ||
        (stripos($statement, "FROM log ") !== FALSE) ) {
        return;
    }

    $group = isset($_SESSION['authGroup']) ?  $_SESSION['authGroup'] : "";
    $comments = $statement;

    $processed_binds = "";
    if (is_array($binds)) {
        // Need to include the binded variable elements in the logging
        $first_loop=true;
        foreach ($binds as $value_bind) {
            if ($first_loop) {
                //no comma
                $processed_binds .= "'" . add_escape_custom($value_bind) . "'";
                $first_loop=false;
            }
            else {
                //add a comma
                $processed_binds .= ",'" . add_escape_custom($value_bind) . "'";
            }
        }
        if (!empty($processed_binds)) {
            $processed_binds = "(" . $processed_binds . ")";
            $comments .= " " . $processed_binds;
        }
    }

    $success = 1;
    $checksum = "";
    if ($outcome === FALSE) {
        $success = 0;
    }
    if ($outcome !== FALSE) {
        // Should use the $statement rather than the processed
        // variables, which includes the binded stuff. If do
        // indeed need the binded values, then will need
        // to include this as a separate array.

        //error_log("STATEMENT: ".$statement,0);
        //error_log("BINDS: ".$processed_binds,0);
        $checksum = sql_checksum_of_modified_row($statement);
        //error_log("CHECKSUM: ".$checksum,0);
    }
    /* Determine the query type (select, update, insert, delete) */
    $querytype = "select";
    $querytypes = array("select", "update", "insert", "delete","replace");
    foreach ($querytypes as $qtype) {
        if (stripos($statement, $qtype) === 0) {
            $querytype = $qtype;
        }
    }

    /* Determine the audit event based on the database tables */
    $event = "other";
    $tables = array("billing" => "patient-record",
                    "claims" => "patient-record",
                    "employer_data" => "patient-record",
                    "forms" => "patient-record",
		    "form_encounter" => "patient-record",
                    "form_dictation" => "patient-record",
                    "form_misc_billing_options" => "patient-record",
                    "form_reviewofs" => "patient-record",
                    "form_ros" => "patient-record",
                    "form_soap" => "patient-record",
                    "form_vitals" => "patient-record",
                    "history_data" => "patient-record",
                    "immunizations" => "patient-record",
                    "insurance_data" => "patient-record",
                    "issue_encounter" => "patient-record",
		    "lists" => "patient-record",
                    "patient_data" => "patient-record",
                    "payments" => "patient-record",
                    "pnotes" => "patient-record",
		    "onotes" => "patient-record",
                    "prescriptions" => "order",
                    "transactions" => "patient-record",
					"amendments" => "patient-record",
					"amendments_history" => "patient-record",
                    "facility" => "security-administration",
                    "pharmacies" => "security-administration",
                    "addresses" => "security-administration",
                    "phone_numbers" => "security-administration",
                    "x12_partners" => "security-administration",
                    "insurance_companies" => "security-administration",
                    "codes" => "security-administration",
                    "registry" => "security-administration", 
                    "users" => "security-administration",
                    "groups" => "security-administration",
                    "openemr_postcalendar_events" => "scheduling",
    				"openemr_postcalendar_categories" => "security-administration",
    				"openemr_postcalendar_limits" => "security-administration",
    				"openemr_postcalendar_topics" => "security-administration",
    				"gacl_acl" => "security-administration",
    				"gacl_acl_sections" => "security-administration",
    				"gacl_acl_seq" => "security-administration",
    				"gacl_aco" => "security-administration",
    				"gacl_aco_map" => "security-administration",
    				"gacl_aco_sections" => "security-administration",
    				"gacl_aco_sections_seq" => "security-administration",
    				"gacl_aco_seq" => "security-administration",
    				"gacl_aro" => "security-administration",
    				"gacl_aro_groups" => "security-administration",    				
    				"gacl_aro_groups_id_seq" => "security-administration",
    				"gacl_aro_groups_map" => "security-administration",
    				"gacl_aro_map" => "security-administration",
    				"gacl_aro_sections" => "security-administration",
    				"gacl_aro_sections_seq" => "security-administration",
    				"gacl_aro_seq" => "security-administration",
    				"gacl_axo" => "security-administration",
    				"gacl_axo_groups" => "security-administration",
    				"gacl_axo_groups_map" => "security-administration",
    				"gacl_axo_map" => "security-administration",
    				"gacl_axo_sections" => "security-administration",
    				"gacl_groups_aro_map" => "security-administration",
    				"gacl_groups_axo_map" => "security-administration",
    				"gacl_phpgacl" => "security-administration"    				
                  );

    /* When searching for table names, truncate the SQL statement,
     * removing any WHERE, SET, or VALUE clauses.
     */
	$truncated_sql = $statement;
	$truncated_sql = str_replace("\n", " ", $truncated_sql);
	if ($querytype == "select") {
	$startwhere = stripos($truncated_sql, " where ");
        if ($startwhere > 0) {
        $truncated_sql = substr($truncated_sql, 0, $startwhere);
    }
 }
	else {
     $startparen = stripos($truncated_sql, "(" );
     $startset = stripos($truncated_sql, " set ");
     $startvalues = stripos($truncated_sql, " values ");

        if ($startparen > 0) {
            $truncated_sql = substr($truncated_sql, 0, $startparen);
        }
        if ($startvalues > 0) {
            $truncated_sql = substr($truncated_sql, 0, $startvalues);
        }
        if ($startset > 0) {
            $truncated_sql = substr($truncated_sql, 0, $startset);
        }
    }
    foreach ($tables as $table => $value) {
        if (strpos($truncated_sql, $table) !== FALSE) {
            $event = $value;
             break;
        }
      else if (strpos($truncated_sql, "form_") !== FALSE) {
            $event = "patient-record";
             break;
        }
    }

    /* Avoid filling the audit log with trivial SELECT statements.
     * Skip SELECTs from unknown tables.  
     * Skip SELECT count() statements.
     * Skip the SELECT made by the authCheckSession() function.
     */
    if ($querytype == "select") {
        if ($event == "other")
            return;
        if (stripos($statement, "SELECT count(" ) === 0)
            return;
        if (stripos($statement, "select username, password from users") === 0)
            return;
    }


    /* If the event is a patient-record, then note the patient id */
    $pid = 0;
    if ($event == "patient-record") {
        if (array_key_exists('pid', $_SESSION) && $_SESSION['pid'] != '') {
            $pid = $_SESSION['pid'];
        }
    }

    /* If query events are not enabled, don't log them */
    if (($querytype == "select") && !($GLOBALS['audit_events_query']))
    { 
       if ((soundex($user) != soundex("emergency")) && (soundex($user) != soundex("breakglass")))
       return;
    }

    if (!($GLOBALS["audit_events_${event}"])) 
    {
    	if ((soundex($user) != soundex("emergency")) && (soundex($user) != soundex("breakglass")))
    	return;
    }
           

    $event = $event . "-" . $querytype;

    $adodb = $GLOBALS['adodb']['db'];
   
    // ViSolve : Don't log sequences - to avoid the affect due to GenID calls
    if (strpos($comments, "sequences") !== FALSE) return;
	
	$encrypt_comment = 'No';
	//July 1, 2014: Ensoftek: Check and encrypt audit logging
	if ($GLOBALS["enable_auditlog_encryption"]) {
		$comments =  aes256Encrypt($comments);
		$encrypt_comment = 'Yes';
	}
	
	$current_datetime = date("Y-m-d H:i:s");
    $SSL_CLIENT_S_DN_CN=isset($_SERVER['SSL_CLIENT_S_DN_CN']) ? $_SERVER['SSL_CLIENT_S_DN_CN'] : '';
    $sql = "insert into log (date, event, user, groupname, comments, patient_id, success, checksum,crt_user) " .
         "values ( ".
		 $adodb->qstr($current_datetime). ", ".
         $adodb->qstr($event) . ", " .
         $adodb->qstr($user) . "," . 
         $adodb->qstr($group) . "," .
         $adodb->qstr($comments) . "," .
         $adodb->qstr($pid) . "," .
         $adodb->qstr($success) . "," . 
         $adodb->qstr($checksum) . "," .
         $adodb->qstr($SSL_CLIENT_S_DN_CN) .")";
	sqlInsertClean_audit($sql);
	
	$last_log_id = $GLOBALS['adodb']['db']->Insert_ID();
	$checksumGenerate = '';
	//July 1, 2014: Ensoftek: Record the encryption checksum in a secondary table(log_comment_encrypt)
	if ($querytype == 'update') {
		$concatLogColumns = $current_datetime.$event.$user.$group.$comments.$pid.$success.$checksum.$SSL_CLIENT_S_DN_CN;
		$checksumGenerate = sha1($concatLogColumns);
	}
	$encryptLogQry = "INSERT INTO log_comment_encrypt (log_id, encrypt, checksum) ".
					 " VALUES ( ".
					  $adodb->qstr($last_log_id) . "," .
					  $adodb->qstr($encrypt_comment) . "," .
					  $adodb->qstr($checksumGenerate) .")"; 
	sqlInsertClean_audit($encryptLogQry);
	
    send_atna_audit_msg($user, $group, $event, $pid, $success, $comments);
    //return $ret;
}

// May-29-2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
// Insert Audit Logging Status into the LOG table.
function auditSQLAuditTamper($enable)
{
    $user =  isset($_SESSION['authUser']) ? $_SESSION['authUser'] : "";
    $group = isset($_SESSION['authGroup']) ?  $_SESSION['authGroup'] : "";
    $pid = 0;
    $checksum = "";
    $success = 1;
	$event = "security-administration" . "-" . "insert";


    $adodb = $GLOBALS['adodb']['db'];
   
	if ($enable == "1")
	{
		$comments = "Audit Logging Enabled.";
	}
	else
	{
		$comments = "Audit Logging Disabled.";
	}
	   
    $SSL_CLIENT_S_DN_CN=isset($_SERVER['SSL_CLIENT_S_DN_CN']) ? $_SERVER['SSL_CLIENT_S_DN_CN'] : '';
    $sql = "insert into log (date, event, user, groupname, comments, patient_id, success, checksum,crt_user) " .
         "values ( NOW(), " . 
         $adodb->qstr($event) . ", " .
         $adodb->qstr($user) . "," . 
         $adodb->qstr($group) . "," .
         $adodb->qstr($comments) . "," .
         $adodb->qstr($pid) . "," .
         $adodb->qstr($success) . "," . 
         $adodb->qstr($checksum) . "," .
         $adodb->qstr($SSL_CLIENT_S_DN_CN) .")";

    sqlInsertClean_audit($sql);
    send_atna_audit_msg($user, $group, $event, $pid, $success, $comments);
}

/**
 * Record the patient disclosures.
 * @param $dates    - The date when the disclosures are sent to the thrid party.
 * @param $event    - The type of the disclosure.
 * @param $pid      - The id of the patient for whom the disclosures are recorded.
 * @param $comment  - The recipient name and description of the disclosure.
 * @uname           - The username who is recording the disclosure.
 */
function recordDisclosure($dates,$event,$pid,$recipient,$description,$user)
{
        $adodb = $GLOBALS['adodb']['db'];
        $crt_user= $_SERVER['SSL_CLIENT_S_DN_CN'];
        $groupname=$_SESSION['authProvider'];
        $success=1;
        $sql = "insert into extended_log ( date, event, user, recipient, patient_id, description) " .
            "values (" . $adodb->qstr($dates) . "," . $adodb->qstr($event) . "," . $adodb->qstr($user) .
            "," . $adodb->qstr($recipient) . ",".
            $adodb->qstr($pid) ."," .
            $adodb->qstr($description) .")";
        $ret = sqlInsertClean_audit($sql);
}
/**
 * Edit the disclosures that is recorded.
 * @param $dates  - The date when the disclosures are sent to the thrid party.
 * @param $event  - The type of the disclosure.
 * param $comment - The recipient and the description of the disclosure are appended.
 * $logeventid    - The id of the record which is to be edited.
 */
function updateRecordedDisclosure($dates,$event,$recipient,$description,$disclosure_id)
{
         $adodb = $GLOBALS['adodb']['db'];
         $sql="update extended_log set
                event=" . $adodb->qstr($event) . ",
                date=" .  $adodb->qstr($dates) . ",
                recipient=" . $adodb->qstr($recipient) . ",
                description=" . $adodb->qstr($description) . "
                where id=" . $adodb->qstr($disclosure_id) . "";
          $ret = sqlInsertClean_audit($sql);
}
/**
 * Delete the disclosures that is recorded.
 * $deleteid - The id of the record which is to be deleted.
 */
function deleteDisclosure($deletelid)
{
        $sql="delete from extended_log where id='" . add_escape_custom($deletelid) . "'";
        $ret = sqlInsertClean_audit($sql);
}

//July 1, 2014: Ensoftek: Function to AES256 encrypt a given string
function aes256Encrypt($sValue){
	$sSecretKey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
    return rtrim(
        base64_encode(
            mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $sSecretKey, $sValue, 
                MCRYPT_MODE_ECB, 
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256, 
                        MCRYPT_MODE_ECB
                    ), 
                    MCRYPT_RAND)
                )
            ), "\0"
        );
}

//July 1, 2014: Ensoftek: Function to AES256 decrypt a given string
function aes256Decrypt($sValue){
	$sSecretKey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
    return rtrim(
        mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256, 
            $sSecretKey, 
            base64_decode($sValue), 
            MCRYPT_MODE_ECB,
            mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256,
                    MCRYPT_MODE_ECB
                ), 
                MCRYPT_RAND
            )
        ), "\0"
    );
}

//July 1, 2014: Ensoftek: Utility function to get data from table(log_comment_encrypt)
function logCommentEncryptData($log_id){
	$encryptRow = array();
	$logRes = sqlStatement("SELECT * FROM log_comment_encrypt WHERE log_id=?", array($log_id));
	while($logRow = sqlFetchArray($logRes)){
		$encryptRow['encrypt'] = $logRow['encrypt'];
		$encryptRow['checksum'] = $logRow['checksum'];
	}
	return $encryptRow;
}
?>
