<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 /////////////////////////////////////////////////////////////////////
 // This program exports patient demographics on demand and sends
 // them to an Atlas LabWorks server to facilitate lab requisitions.
 /////////////////////////////////////////////////////////////////////

 include_once("../interface/globals.php");
 include_once("../library/patient.inc");

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
 function Add($field) {
  global $out;
  $out .= "^" . trim(str_replace(array("\r", "\n", "\t"), " ", $field));
 }

 // Remove all non-digits from a string.
 function Digits($field) {
  return preg_replace("/\D/", "", $field);
 }

 // Translate sex.
 function Sex($field) {
  $sex = strtoupper(substr(trim($field), 0, 1));
  if ($sex != "M" && $sex != "F") $sex = "U";
  return $sex;
 }

 // Translate a date.
 function LWDate($field) {
  $tmp = fixDate($field);
  return substr($tmp, 5, 2) . substr($tmp, 8, 2) . substr($tmp, 0, 4);
 }

 // Translate insurance type.
 function InsType($field) {
  if (! $field)    return "";
  if ($field == 2) return "Medicare";
  if ($field == 3) return "Medicaid";
  return "Other";
 }

 // Error abort function that does not leave the system locked.
 function mydie($msg) {
  global $EXPORT_PATH;
  rename("$EXPORT_PATH/locked", "$EXPORT_PATH/unlocked");
  die($msg);
 }

 $alertmsg = ""; // anything here pops up in an alert box

 // This mess gets all the info for the patient.
 //
 $query = "SELECT " .
  "p.pubpid, p.fname, p.mname, p.lname, p.DOB, p.providerID, " .
  "p.ss, p.street, p.city, p.state, p.postal_code, p.phone_home, p.sex, " .
  "i1.policy_number AS policy1, i1.group_number AS group1, i1.provider as provider1, " .
  "i1.subscriber_fname AS fname1, i1.subscriber_mname AS mname1, i1.subscriber_lname AS lname1, " .
  "i1.subscriber_street AS sstreet1, i1.subscriber_city AS scity1, i1.subscriber_state AS sstate1, " .
  "i1.subscriber_postal_code AS szip1, i1.subscriber_relationship AS relationship1, " .
  "c1.name AS name1, c1.freeb_type AS instype1, " .
  "a1.line1 AS street11, a1.line2 AS street21, a1.city AS city1, a1.state AS state1, " .
  "a1.zip AS zip1, a1.plus_four AS zip41, " .
  "i2.policy_number AS policy2, i2.group_number AS group2, i2.provider as provider2, " .
  "i2.subscriber_fname AS fname2, i2.subscriber_mname AS mname2, i2.subscriber_lname AS lname2, " .
  "i2.subscriber_relationship AS relationship2, " .
  "c2.name AS name2, c2.freeb_type AS instype2, " .
  "a2.line1 AS street12, a2.line2 AS street22, a2.city AS city2, a2.state AS state2, " .
  "a2.zip AS zip2, a2.plus_four AS zip42 " .
  "FROM patient_data AS p " .
  "LEFT OUTER JOIN insurance_data AS i1 ON i1.pid = p.pid AND i1.type = 'primary' " .
  "LEFT OUTER JOIN insurance_data AS i2 ON i2.pid = p.pid AND i2.type = 'secondary' " .
  "LEFT OUTER JOIN insurance_companies AS c1 ON c1.id = i1.provider " .
  "LEFT OUTER JOIN insurance_companies AS c2 ON c2.id = i2.provider " .
  "LEFT OUTER JOIN addresses AS a1 ON a1.foreign_id = c1.id " .
  "LEFT OUTER JOIN addresses AS a2 ON a2.foreign_id = c2.id " .
  "WHERE p.pid = '$pid' LIMIT 1";

 $row = sqlFetchArray(sqlStatement($query));

 // Get primary care doc info.  If none was selected in the patient
 // demographics then pick the #1 doctor in the clinic.
 //
 $query = "select id, fname, mname, lname from users where authorized = 1";
 if ($row['providerID']) {
  $query .= " AND id = " . $row['providerID'];
 } else {
  $query .= " ORDER BY id LIMIT 1";
 }
 $prow = sqlFetchArray(sqlStatement($query));

 // Patient Section.
 //
 $out .= $pid;                     // patient id
 Add($row['pubpid']);              // chart number
 Add($row['lname']);               // last name
 Add($row['fname']);               // first name
 Add(substr($row['mname'], 0, 1)); // middle initial
 Add("");                          // alias
 Add(Digits($row['ss']));          // ssn
 Add(LWDate($row['DOB']));         // dob
 Add(Sex($row['sex']));            // gender
 Add("");                          // notes
 Add($row['street']);              // address 1
 Add("");                          // address2
 Add($row['city']);                // city
 Add($row['state']);               // state
 Add($row['postal_code']);         // zip
 Add(Digits($row['phone_home']));  // home phone

 // Guarantor Section.  OpenEMR does not have guarantors so we use the primary
 // insurance subscriber if there is one, otherwise the patient.
 //
 if (trim($row['lname1'])) {
  Add($row['lname1']);
  Add($row['fname1']);
  Add(substr($row['mname1'], 0, 1));
  Add($row['sstreet1']);
  Add("");
  Add($row['scity1']);
  Add($row['sstate1']);
  Add($row['szip1']);
 } else {
  Add($row['lname']);
  Add($row['fname']);
  Add(substr($row['mname'], 0, 1));
  Add($row['street']);
  Add("");
  Add($row['city']);
  Add($row['state']);
  Add($row['postal_code']);
 }

 // Primary Insurance Section.
 //
 Add($row['provider1']);
 Add($row['name1']);
 Add($row['street11']);
 Add($row['street21']);
 Add($row['city1']);
 Add($row['state1']);
 Add($row['zip1']);
 Add("");
 Add(InsType($row['instype1']));
 Add($row['fname1'] . " " . $row['lname1']);
 Add(ucfirst($row['relationship1']));
 Add($row['group1']);
 Add($row['policy1']);

 // Secondary Insurance Section.
 //
 Add($row['provider2']);
 Add($row['name2']);
 Add($row['street12']);
 Add($row['street22']);
 Add($row['city2']);
 Add($row['state2']);
 Add($row['zip2']);
 Add("");
 Add(InsType($row['instype2']));
 Add($row['fname2'] . " " . $row['lname2']);
 Add(ucfirst($row['relationship2']));
 Add($row['group2']);
 Add($row['policy2']);

 // Primary Care Physician Section.
 //
 Add($prow['id']);
 Add($prow['lname']);
 Add($prow['fname']);
 Add(substr($prow['mname'], 0, 1));
 Add(""); // UPIN not available

 // All done.
 $out .= "\rEND";

 // In case this is the very first time.
 if (! file_exists($EXPORT_PATH)) {
  mkdir($EXPORT_PATH);
  @touch("$EXPORT_PATH/unlocked");
 }

 // Serialize the following code; collisions would be very bad.
 if (! rename("$EXPORT_PATH/unlocked", "$EXPORT_PATH/locked"))
  die("Export seems to be in use by someone else; please try again.");

 // Figure out what to use for the target filename.
 $dh = opendir($EXPORT_PATH);
 if (! $dh) mydie("Cannot read $EXPORT_PATH");
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
 if (! $fh) mydie("Unable to open $initialpath for writing");
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
  if ($FTP_DIR) ftp_chdir($ftpconn, $FTP_DIR) or die("FTP chdir failed");
  ftp_put($ftpconn, $initialname, $finalpath, FTP_BINARY) or die("FTP put failed");
  ftp_rename($ftpconn, $initialname, $finalname) or die("FTP rename failed");
  ftp_close($ftpconn);
 }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>Export Patient Demographics</title>
</head>
<body>
<center>
<p>&nbsp;</p>
<p>Demographics for <? echo $row['fname'] . " " . $row['lname'] ?>
 have been exported to LabWorks.</p>
<p>&nbsp;</p>
<form>
<p><input type='button' value='OK' onclick='window.close()' /></p>
</form>
</center>
</body>
</html>
