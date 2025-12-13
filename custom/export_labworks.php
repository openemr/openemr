<?php

 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 /////////////////////////////////////////////////////////////////////
 // This program exports patient demographics on demand and sends
 // them to an Atlas LabWorks server to facilitate lab requisitions.
 // This product is now called Clinisys Atlas, need to verify if the code is still used as of July 2025
 /////////////////////////////////////////////////////////////////////

 require_once("../interface/globals.php");
 require_once("../library/patient.inc.php");

 use OpenEMR\Core\Header;

 // FTP parameters that you must customize.  If you are not sending
 // then set $FTP_SERVER to an empty string.
 //
 $FTP_SERVER = "192.168.0.30";
 $FTP_USER   = "openemr";
 $FTP_PASS   = "secret";
 $FTP_DIR    = "";

 // This is the destination directory on the local machine for the
 // exported data.  Required even if FTP is used.
 //
 $EXPORT_PATH = "/tmp/labworks";

 $out = "";

 // Add a string to output with some basic sanitizing.
function custom_labworks_Add($field)
{
    return "^" . trim(str_replace(["\r", "\n", "\t"], " ", $field));
}

 // Remove all non-digits from a string.
function Digits($field)
{
    return preg_replace("/\D/", "", (string) $field);
}

 // Translate sex.
function Sex($field)
{
    $sex = strtoupper(substr(trim((string) $field), 0, 1));
    if ($sex != "M" && $sex != "F") {
        $sex = "U";
    }

    return $sex;
}

 // Translate a date.
function LWDate($field)
{
    $tmp = fixDate($field);
    return substr((string) $tmp, 5, 2) . substr((string) $tmp, 8, 2) . substr((string) $tmp, 0, 4);
}

 // Translate insurance type.
function InsType($field)
{
    if (! $field) {
        return "";
    }

    if ($field == 2) {
        return "Medicare";
    }

    if ($field == 3) {
        return "Medicaid";
    }

    return "Other";
}

 // Error abort function that does not leave the system locked.
function mydie($msg): void
{
    global $EXPORT_PATH;
    rename("$EXPORT_PATH/locked", "$EXPORT_PATH/unlocked");
    die($msg);
}

 $alertmsg = ""; // anything here pops up in an alert box

 // This mess gets all the info for the patient.
 //
 $insrow = [];
 global $pid; // defined in globals.php
foreach (['primary','secondary'] as $value) {
    $insrow[] = sqlQuery("SELECT id FROM insurance_data WHERE " .
    "pid = ? AND type = ? ORDER BY date DESC LIMIT 1", [$pid, $value]);
}

 $query = "SELECT " .
  "p.pubpid, p.fname, p.mname, p.lname, p.DOB, p.providerID, " .
  "p.ss, p.street, p.city, p.state, p.postal_code, p.phone_home, p.sex, " .
  "i1.policy_number AS policy1, i1.group_number AS group1, i1.provider as provider1, " .
  "i1.subscriber_fname AS fname1, i1.subscriber_mname AS mname1, i1.subscriber_lname AS lname1, " .
  "i1.subscriber_street AS sstreet1, i1.subscriber_city AS scity1, i1.subscriber_state AS sstate1, " .
  "i1.subscriber_postal_code AS szip1, i1.subscriber_relationship AS relationship1, " .
  "c1.name AS name1, c1.ins_type_code AS instype1, " .
  "a1.line1 AS street11, a1.line2 AS street21, a1.city AS city1, a1.state AS state1, " .
  "a1.zip AS zip1, a1.plus_four AS zip41, " .
  "i2.policy_number AS policy2, i2.group_number AS group2, i2.provider as provider2, " .
  "i2.subscriber_fname AS fname2, i2.subscriber_mname AS mname2, i2.subscriber_lname AS lname2, " .
  "i2.subscriber_relationship AS relationship2, " .
  "c2.name AS name2, c2.ins_type_code AS instype2, " .
  "a2.line1 AS street12, a2.line2 AS street22, a2.city AS city2, a2.state AS state2, " .
  "a2.zip AS zip2, a2.plus_four AS zip42 " .
  "FROM patient_data AS p " .
  // "LEFT OUTER JOIN insurance_data AS i1 ON i1.pid = p.pid AND i1.type = 'primary' " .
  // "LEFT OUTER JOIN insurance_data AS i2 ON i2.pid = p.pid AND i2.type = 'secondary' " .
  "LEFT OUTER JOIN insurance_data AS i1 ON i1.id = ? " .
  "LEFT OUTER JOIN insurance_data AS i2 ON i2.id = ? " .
  //
  "LEFT OUTER JOIN insurance_companies AS c1 ON c1.id = i1.provider " .
  "LEFT OUTER JOIN insurance_companies AS c2 ON c2.id = i2.provider " .
  "LEFT OUTER JOIN addresses AS a1 ON a1.foreign_id = c1.id " .
  "LEFT OUTER JOIN addresses AS a2 ON a2.foreign_id = c2.id " .
  "WHERE p.pid = ? LIMIT 1";

 $row = sqlFetchArray(sqlStatement($query, [$insrow[0]['id'], $insrow[1]['id'], $pid]));

 // Get primary care doc info.  If none was selected in the patient
 // demographics then pick the #1 doctor in the clinic.
 //
 $query = "select id, fname, mname, lname from users where authorized = 1";
 $sqlBindArray = [];
if ($row['providerID']) {
    $query .= " AND id = ?";
    array_push($sqlBindArray, $row['providerID']);
} else {
    $query .= " ORDER BY id LIMIT 1";
}

 $prow = sqlFetchArray(sqlStatement($query, $sqlBindArray));

 // Patient Section.
 //
 $out .= $pid;                     // patient id
 $out .= custom_labworks_Add($row['pubpid']);              // chart number
 $out .= custom_labworks_Add($row['lname']);               // last name
 $out .= custom_labworks_Add($row['fname']);               // first name
 $out .= custom_labworks_Add(substr((string) $row['mname'], 0, 1)); // middle initial
 $out .= custom_labworks_Add("");                          // alias
 $out .= custom_labworks_Add(Digits($row['ss']));          // ssn
 $out .= custom_labworks_Add(LWDate($row['DOB']));         // dob
 $out .= custom_labworks_Add(Sex($row['sex']));            // gender
 $out .= custom_labworks_Add("");                          // notes
 $out .= custom_labworks_Add($row['street']);              // address 1
 $out .= custom_labworks_Add("");                          // address2
 $out .= custom_labworks_Add($row['city']);                // city
 $out .= custom_labworks_Add($row['state']);               // state
 $out .= custom_labworks_Add($row['postal_code']);         // zip
 $out .= custom_labworks_Add(Digits($row['phone_home']));  // home phone

 // Guarantor Section.  OpenEMR does not have guarantors so we use the primary
 // insurance subscriber if there is one, otherwise the patient.
 //
if (trim((string) $row['lname1'])) {
    $out .= custom_labworks_Add($row['lname1']);
    $out .= custom_labworks_Add($row['fname1']);
    $out .= custom_labworks_Add(substr((string) $row['mname1'], 0, 1));
    $out .= custom_labworks_Add($row['sstreet1']);
    $out .= custom_labworks_Add("");
    $out .= custom_labworks_Add($row['scity1']);
    $out .= custom_labworks_Add($row['sstate1']);
    $out .= custom_labworks_Add($row['szip1']);
} else {
    $out .= custom_labworks_Add($row['lname']);
    $out .= custom_labworks_Add($row['fname']);
    $out .= custom_labworks_Add(substr((string) $row['mname'], 0, 1));
    $out .= custom_labworks_Add($row['street']);
    $out .= custom_labworks_Add("");
    $out .= custom_labworks_Add($row['city']);
    $out .= custom_labworks_Add($row['state']);
    $out .= custom_labworks_Add($row['postal_code']);
}

 // Primary Insurance Section.
 //
 $out .= custom_labworks_Add($row['provider1']);
 $out .= custom_labworks_Add($row['name1']);
 $out .= custom_labworks_Add($row['street11']);
 $out .= custom_labworks_Add($row['street21']);
 $out .= custom_labworks_Add($row['city1']);
 $out .= custom_labworks_Add($row['state1']);
 $out .= custom_labworks_Add($row['zip1']);
 $out .= custom_labworks_Add("");
 $out .= custom_labworks_Add(InsType($row['instype1']));
 $out .= custom_labworks_Add($row['fname1'] . " " . $row['lname1']);
 $out .= custom_labworks_Add(ucfirst((string) $row['relationship1']));
 $out .= custom_labworks_Add($row['group1']);
 $out .= custom_labworks_Add($row['policy1']);

 // Secondary Insurance Section.
 //
 $out .= custom_labworks_Add($row['provider2']);
 $out .= custom_labworks_Add($row['name2']);
 $out .= custom_labworks_Add($row['street12']);
 $out .= custom_labworks_Add($row['street22']);
 $out .= custom_labworks_Add($row['city2']);
 $out .= custom_labworks_Add($row['state2']);
 $out .= custom_labworks_Add($row['zip2']);
 $out .= custom_labworks_Add("");
 $out .= custom_labworks_Add(InsType($row['instype2']));
 $out .= custom_labworks_Add($row['fname2'] . " " . $row['lname2']);
 $out .= custom_labworks_Add(ucfirst((string) $row['relationship2']));
 $out .= custom_labworks_Add($row['group2']);
 $out .= custom_labworks_Add($row['policy2']);

 // Primary Care Physician Section.
 //
 $out .= custom_labworks_Add($prow['id']);
 $out .= custom_labworks_Add($prow['lname']);
 $out .= custom_labworks_Add($prow['fname']);
 $out .= custom_labworks_Add(substr((string) $prow['mname'], 0, 1));
 $out .= custom_labworks_Add(""); // UPIN not available

 // All done.
 $out .= "\rEND";

 // In case this is the very first time.
if (! file_exists($EXPORT_PATH)) {
    mkdir($EXPORT_PATH);
    @touch("$EXPORT_PATH/unlocked");
}

 // Serialize the following code; collisions would be very bad.
if (! rename("$EXPORT_PATH/unlocked", "$EXPORT_PATH/locked")) {
    die("Export seems to be in use by someone else; please try again.");
}

 // Figure out what to use for the target filename.
 $dh = opendir($EXPORT_PATH);
if (! $dh) {
    mydie("Cannot read $EXPORT_PATH");
}

 $nextnumber = 1;
while (false !== ($filename = readdir($dh))) {
    if (preg_match("/PMI(\d{8})\.DEM/", $filename, $matches)) {
        $tmp = 1 + $matches[1];
        if ($tmp > $nextnumber) {
            $nextnumber = $tmp;
        }
    }
}

 closedir($dh);
 $fnprefix = sprintf("PMI%08.0f.", $nextnumber);
 $initialname = $fnprefix . "creating";
 $finalname   = $fnprefix . "DEM";
 $initialpath = "$EXPORT_PATH/$initialname";
 $finalpath   = "$EXPORT_PATH/$finalname";

 // Write the file locally with a temporary version of the name.
 @touch($initialpath); // work around possible php bug
 $fh = @fopen($initialpath, "w");
if (! $fh) {
    mydie("Unable to open " . text($initialpath) . " for writing");
}

 fwrite($fh, $out);
 fclose($fh);

 // Rename the local file.
 rename($initialpath, $finalpath);

 // Delete old stuff to avoid uncontrolled growth.
if ($nextnumber > 5) {
    @unlink("$EXPORT_PATH/PMI%08.0f.DEM", $nextnumber - 5);
}

 // End of serialized code.
 rename("$EXPORT_PATH/locked", "$EXPORT_PATH/unlocked");

 // If we have an ftp server, send it there and then rename it.
if ($FTP_SERVER) {
    $ftpconn = ftp_connect($FTP_SERVER) or die("FTP connection failed");
    ftp_login($ftpconn, $FTP_USER, $FTP_PASS) or die("FTP login failed");
    if ($FTP_DIR) {
        ftp_chdir($ftpconn, $FTP_DIR) or die("FTP chdir failed");
    }

    ftp_put($ftpconn, $initialname, $finalpath, FTP_BINARY) or die("FTP put failed");
    ftp_rename($ftpconn, $initialname, $finalname) or die("FTP rename failed");
    ftp_close($ftpconn);
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title>Export Patient Demographics</title>
</head>
<body>
<center>
<p>&nbsp;</p>
<p>Demographics for <?php echo text($row['fname']) . " " . text($row['lname']); ?>
 have been exported to LabWorks.</p>
<p>&nbsp;</p>
<form>
<p><input type='button' value='OK' onclick='window.close()' /></p>
</form>
</center>
</body>
</html>
