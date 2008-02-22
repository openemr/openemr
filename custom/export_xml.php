<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 /////////////////////////////////////////////////////////////////////
 // This program exports patient demographics to a custom XML format.
 /////////////////////////////////////////////////////////////////////

 include_once("../interface/globals.php");
 include_once("../library/patient.inc");

 $out = "";
 $indent = 0;

 // Add a string to output with some basic sanitizing.
 function Add($tag, $text) {
  global $out, $indent;
  $text = trim(str_replace(array("\r", "\n", "\t"), " ", $text));
  if ($text) {
   for ($i = 0; $i < $indent; ++$i) $out .= "\t";
   $out .= "<$tag>$text</$tag>\n";
  }
 }

 function OpenTag($tag) {
  global $out, $indent;
  for ($i = 0; $i < $indent; ++$i) $out .= "\t";
  ++$indent;
  $out .= "<$tag>\n";
 }

 function CloseTag($tag) {
  global $out, $indent;
  --$indent;
  for ($i = 0; $i < $indent; ++$i) $out .= "\t";
  $out .= "</$tag>\n";
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
  return fixDate($field);
 }

 // Add an insurance section.
 function addInsurance($row, $seq) {
  if ($row["name$seq"]) {
   OpenTag("insurance");
   Add("priority"            , $seq);
   Add("group"               , $row["group$seq"]);
   Add("policy"              , $row["policy$seq"]);
   Add("provider"            , $row["provider$seq"]);
   Add("name"                , $row["name$seq"]);
   Add("street1"             , $row["street1$seq"]);
   Add("street2"             , $row["street2$seq"]);
   Add("city"                , $row["city$seq"]);
   Add("state"               , $row["state$seq"]);
   Add("zip"                 , $row["zip$seq"]);
   Add("country"             , $row["country$seq"]);
   Add("type"                , $row["instype$seq"]);
   Add("copay"               , $row["copay$seq"]);
   OpenTag("subscriber");
   Add("relationship"        , $row["relationship$seq"]);
   Add("lname"               , $row["lname$seq"]);
   Add("fname"               , $row["fname$seq"]);
   Add("mname"               , $row["mname$seq"]);
   Add("street"              , $row["sstreet$seq"]);
   Add("city"                , $row["scity$seq"]);
   Add("state"               , $row["sstate$seq"]);
   Add("zip"                 , $row["szip$seq"]);
   Add("country"             , $row["scountry$seq"]);
   Add("dob"                 , $row["sdob$seq"]);
   Add("ss"                  , $row["sss$seq"]);
   Add("phone"               , $row["sphone$seq"]);
   Add("employer"            , $row["semployer$seq"]);
   Add("sex"                 , $row["ssex$seq"]);
   Add("employer_street"     , $row["semployer_street$seq"]);
   Add("employer_city"       , $row["semployer_city$seq"]);
   Add("employer_state"      , $row["semployer_state$seq"]);
   Add("employer_zip"        , $row["semployer_zip$seq"]);
   Add("employer_country"    , $row["semployer_country$seq"]);
   CloseTag("subscriber");
   CloseTag("insurance");
  }
 }

 // This mess gets all the info for the patient.
 //
 $insrow = array();
 foreach (array('primary','secondary','tertiary') as $value) {
   $insrow[] = sqlQuery("SELECT id FROM insurance_data WHERE " .
     "pid = '$pid' AND type = '$value' ORDER BY date DESC LIMIT 1");
 }
 $query = "SELECT " .
  "p.pubpid, p.fname, p.mname, p.lname, p.DOB, p.providerID, " .
  "p.ss, p.street, p.city, p.state, p.postal_code, p.phone_home, p.sex, " .
  "p.title, p.country_code, p.occupation, p.phone_biz, p.phone_contact, p.phone_cell, " .
  "p.status, p.contact_relationship, p.referrer, p.referrerID, p.email, " .
  "p.language, p.ethnoracial, p.interpretter, p.migrantseasonal, p.family_size, " .
  "p.monthly_income, p.homeless, p.financial_review, p.hipaa_mail, p.hipaa_voice, " .
  "p.genericname1, p.genericval1, p.genericname2, p.genericval2, " .
  "i1.policy_number AS policy1, i1.group_number AS group1, i1.provider as provider1, " .
  "i1.subscriber_fname AS fname1, i1.subscriber_mname AS mname1, i1.subscriber_lname AS lname1, " .
  "i1.subscriber_street AS sstreet1, i1.subscriber_city AS scity1, i1.subscriber_state AS sstate1, " .
  "i1.subscriber_postal_code AS szip1, i1.subscriber_relationship AS relationship1, " .
  "i1.subscriber_DOB AS sdob1, i1.subscriber_ss AS sss1, i1.subscriber_phone AS sphone1, " .
  "i1.subscriber_sex AS ssex1, i1.subscriber_country AS scountry1, " .
  "i1.subscriber_employer AS semployer1, i1.subscriber_employer_street AS semployer_street1, " .
  "i1.subscriber_employer_city AS semployer_city1, i1.subscriber_employer_state AS semployer_state1, " .
  "i1.subscriber_employer_postal_code AS semployer_zip1, " .
  "i1.subscriber_employer_country AS semployer_country1, i1.copay AS copay1, " .
  "c1.name AS name1, c1.freeb_type AS instype1, " .
  "a1.line1 AS street11, a1.line2 AS street21, a1.city AS city1, a1.state AS state1, " .
  "a1.zip AS zip1, a1.plus_four AS zip41, a1.country AS country1, " .
  "i2.policy_number AS policy2, i2.group_number AS group2, i2.provider as provider2, " .
  "i2.subscriber_fname AS fname2, i2.subscriber_mname AS mname2, i2.subscriber_lname AS lname2, " .
  "i2.subscriber_postal_code AS szip2, i2.subscriber_relationship AS relationship2, " .
  "i2.subscriber_DOB AS sdob2, i2.subscriber_ss AS sss2, i2.subscriber_phone AS sphone2, " .
  "i2.subscriber_sex AS ssex2, i2.subscriber_country AS scountry2, " .
  "i2.subscriber_employer AS semployer2, i2.subscriber_employer_street AS semployer_street2, " .
  "i2.subscriber_employer_city AS semployer_city2, i2.subscriber_employer_state AS semployer_state2, " .
  "i2.subscriber_employer_postal_code AS semployer_zip2, " .
  "i2.subscriber_employer_country AS semployer_country2, i2.copay AS copay2, " .
  "c2.name AS name2, c2.freeb_type AS instype2, " .
  "a2.line1 AS street12, a2.line2 AS street22, a2.city AS city2, a2.state AS state2, " .
  "a2.zip AS zip2, a2.plus_four AS zip42, a2.country AS country2, " .
  "i3.policy_number AS policy3, i3.group_number AS group3, i3.provider as provider3, " .
  "i3.subscriber_fname AS fname3, i3.subscriber_mname AS mname3, i3.subscriber_lname AS lname3, " .
  "i3.subscriber_postal_code AS szip3, i3.subscriber_relationship AS relationship3, " .
  "i3.subscriber_DOB AS sdob3, i3.subscriber_ss AS sss3, i3.subscriber_phone AS sphone3, " .
  "i3.subscriber_sex AS ssex3, i3.subscriber_country AS scountry3, " .
  "i3.subscriber_employer AS semployer3, i3.subscriber_employer_street AS semployer_street3, " .
  "i3.subscriber_employer_city AS semployer_city3, i3.subscriber_employer_state AS semployer_state3, " .
  "i3.subscriber_employer_postal_code AS semployer_zip3, " .
  "i3.subscriber_employer_country AS semployer_country3, i3.copay AS copay3, " .
  "c3.name AS name3, c3.freeb_type AS instype3, " .
  "a3.line1 AS street13, a3.line2 AS street23, a3.city AS city3, a3.state AS state3, " .
  "a3.zip AS zip3, a3.plus_four AS zip43, a3.country AS country3 " .
  "FROM patient_data AS p " .
  // "LEFT OUTER JOIN insurance_data AS i1 ON i1.pid = p.pid AND i1.type = 'primary'   " .
  // "LEFT OUTER JOIN insurance_data AS i2 ON i2.pid = p.pid AND i2.type = 'secondary' " .
  // "LEFT OUTER JOIN insurance_data AS i3 ON i3.pid = p.pid AND i3.type = 'tertiary'  " .
  "LEFT OUTER JOIN insurance_data AS i1 ON i1.id = '" . $insrow[0]['id'] . "' " .
  "LEFT OUTER JOIN insurance_data AS i2 ON i2.id = '" . $insrow[1]['id'] . "' " .
  "LEFT OUTER JOIN insurance_data AS i3 ON i3.id = '" . $insrow[2]['id'] . "' " .
  //
  "LEFT OUTER JOIN insurance_companies AS c1 ON c1.id = i1.provider " .
  "LEFT OUTER JOIN insurance_companies AS c2 ON c2.id = i2.provider " .
  "LEFT OUTER JOIN insurance_companies AS c3 ON c3.id = i3.provider " .
  "LEFT OUTER JOIN addresses AS a1 ON a1.foreign_id = c1.id " .
  "LEFT OUTER JOIN addresses AS a2 ON a2.foreign_id = c2.id " .
  "LEFT OUTER JOIN addresses AS a3 ON a3.foreign_id = c3.id " .
  "WHERE p.pid = '$pid' LIMIT 1";

 $row = sqlFetchArray(sqlStatement($query));

 $rowed = getEmployerData($pid);

 OpenTag("patient");

 // Patient Section.
 //
 Add("pid"        , $pid);
 Add("pubpid"     , $row['pubpid']);
 Add("lname"      , $row['lname']);
 Add("fname"      , $row['fname']);
 Add("mname"      , $row['mname']);
 Add("title"      , $row['title']);
 Add("ss"         , Digits($row['ss']));
 Add("dob"        , LWDate($row['DOB']));
 Add("sex"        , Sex($row['sex']));
 Add("street"     , $row['street']);
 Add("city"       , $row['city']);
 Add("state"      , $row['state']);
 Add("zip"        , $row['postal_code']);
 Add("country"    , $row['country_code']);
 Add("phone_home"          , Digits($row['phone_home']));
 Add("phone_biz"           , Digits($row['phone_biz']));
 Add("phone_contact"       , Digits($row['phone_contact']));
 Add("phone_cell"          , Digits($row['phone_cell']));
 Add("occupation"          , $row['occupation']);
 Add("status"              , $row['status']);
 Add("contact_relationship", $row['contact_relationship']);
 Add("referrer"            , $row['referrer']);
 Add("referrerID"          , $row['referrerID']);
 Add("email"               , $row['email']);
 Add("language"            , $row['language']);
 Add("ethnoracial"         , $row['ethnoracial']);
 Add("interpreter"         , $row['interpretter']);
 Add("migrantseasonal"     , $row['migrantseasonal']);
 Add("family_size"         , $row['family_size']);
 Add("monthly_income"      , $row['monthly_income']);
 Add("homeless"            , $row['homeless']);
 Add("financial_review"    , LWDate(substr($row['financial_review'], 0, 10)));
 Add("genericname1"        , $row['genericname1']);
 Add("genericval1"         , $row['genericval1']);
 Add("genericname2"        , $row['genericname2']);
 Add("genericval2"         , $row['genericval2']);
 Add("hipaa_mail"          , $row['hipaa_mail']);
 Add("hipaa_voice"         , $row['hipaa_voice']);

 // Insurance Sections.
 //
 addInsurance($row, '1');
 addInsurance($row, '2');
 addInsurance($row, '3');

 // Primary Care Physician Section.
 //
 if ($row['providerID']) {
  $query = "select id, fname, mname, lname from users where authorized = 1";
  $query .= " AND id = " . $row['providerID'];
  $prow = sqlFetchArray(sqlStatement($query));
  OpenTag("pcp");
  Add("id", $prow['id']);
  Add("lname", $prow['lname']);
  Add("fname", $prow['fname']);
  Add("mname", $prow['mname']);
  CloseTag("pcp");
 }

 // Employer Section.
 //
 if ($rowed['id']) {
  OpenTag("employer");
  Add("name"   , $rowed['name']);
  Add("street" , $rowed['street']);
  Add("zip"    , $rowed['postal_code']);
  Add("city"   , $rowed['city']);
  Add("state"  , $rowed['state']);
  Add("country", $rowed['country']);
  CloseTag("employer");
 }

 // All done.
 CloseTag("patient");

 // header('Content-type: text/xml');
 // header('Content-Disposition: attachment; filename="pid' . $pid . '.xml"');
 // echo $out;
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>Export Patient Demographics</title>
</head>
<body>

<p>The exported data appears in the text area below.  You can copy and
paste this into an email or to any other desired destination.</p>

<center>
<form>

<textarea rows='10' cols='50' style='width:95%' readonly>
<? echo $out ?>
</textarea>

<p><input type='button' value='OK' onclick='window.close()' /></p>
</form>
</center>

</body>
</html>
