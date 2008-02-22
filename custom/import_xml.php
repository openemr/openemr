<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 /////////////////////////////////////////////////////////////////////
 // This imports patient demographics from our custom XML format.
 /////////////////////////////////////////////////////////////////////

 include_once("../interface/globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 function setInsurance($pid, $ainsurance, $asubscriber, $seq) {
  $iwhich = $seq == '2' ? "secondary" : ($seq == '3' ? "tertiary" : "primary");
  newInsuranceData(
   $pid,
   $iwhich,
   $ainsurance["provider$seq"],
   $ainsurance["policy$seq"],
   $ainsurance["group$seq"],
   $ainsurance["name$seq"],
   $asubscriber["lname$seq"],
   $asubscriber["mname$seq"],
   $asubscriber["fname$seq"],
   $asubscriber["relationship$seq"],
   $asubscriber["ss$seq"],
   fixDate($asubscriber["dob$seq"]),
   $asubscriber["street$seq"],
   $asubscriber["zip$seq"],
   $asubscriber["city$seq"],
   $asubscriber["state$seq"],
   $asubscriber["country$seq"],
   $asubscriber["phone$seq"],
   $asubscriber["employer$seq"],
   $asubscriber["employer_street$seq"],
   $asubscriber["employer_city$seq"],
   $asubscriber["employer_zip$seq"],
   $asubscriber["employer_state$seq"],
   $asubscriber["employer_country$seq"],
   $ainsurance["copay$seq"],
   $asubscriber["sex$seq"]
  );
 }

 // Check authorization.
 $thisauth = acl_check('patients', 'demo');
 if ($thisauth != 'write')
  die("Updating demographics is not authorized.");

 if ($_POST['form_import']) {
  $apatient    = array();
  $apcp        = array();
  $aemployer   = array();
  $ainsurance  = array();
  $asubscriber = array();

  // $probearr is an array of tag names corresponding to the current
  // container in the tree structure.  $probeix is the current level.
  $probearr = array('');
  $probeix = 0;

  $inspriority = '0'; // 1 = primary, 2 = secondary, 3 = tertiary

  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  $xml = array();

  if (xml_parse_into_struct($parser, $_POST['form_import_data'], $xml)) {

   foreach ($xml as $taginfo) {
    $tag = strtolower($taginfo['tag']);
    $tagtype = $taginfo['type'];
    $tagval = addslashes($taginfo['value']);

    if ($tagtype == 'open') {
     ++$probeix;
     $probearr[$probeix] = $tag;
     continue;
    }
    if ($tagtype == 'close') {
     --$probeix;
     continue;
    }
    if ($tagtype != 'complete') {
     die("Invalid tag type '$tagtype'");
    }

    if ($probeix == 1 && $probearr[$probeix] == 'patient') {
     $apatient[$tag] = $tagval;
    }
    else if ($probeix == 2 && $probearr[$probeix] == 'pcp') {
     $apcp[$tag] = $tagval;
    }
    else if ($probeix == 2 && $probearr[$probeix] == 'employer') {
     $aemployer[$tag] = $tagval;
    }
    else if ($probeix == 2 && $probearr[$probeix] == 'insurance') {
     if ($tag == 'priority') {
      $inspriority = $tagval;
     } else {
      $ainsurance["$tag$inspriority"] = $tagval;
     }
    }
    else if ($probeix == 3 && $probearr[$probeix] == 'subscriber') {
     $asubscriber["$tag$inspriority"] = $tagval;
    }
    else {
     $alertmsg = "Invalid tag \"" . $probearr[$probeix] . "\" at level $probeix";
    }
   }
  } else {
   $alertmsg = "Invalid import data!";
  }
  xml_parser_free($parser);

  $olddata = getPatientData($pid);

  if ($olddata['squad'] && ! acl_check('squads', $olddata['squad']))
   die("You are not authorized to access this squad.");

  newPatientData(
   $olddata['id'],
   $apatient['title'],
   $apatient['fname'],
   $apatient['lname'],
   $apatient['mname'],
   $apatient['sex'],
   $apatient['dob'],
   $apatient['street'],
   $apatient['zip'],
   $apatient['city'],
   $apatient['state'],
   $apatient['country'],
   $apatient['ss'],
   $apatient['occupation'],
   $apatient['phone_home'],
   $apatient['phone_biz'],
   $apatient['phone_contact'],
   $apatient['status'],
   $apatient['contact_relationship'],
   $apatient['referrer'],
   $apatient['referrerID'],
   $apatient['email'],
   $apatient['language'],
   $apatient['ethnoracial'],
   $apatient['interpreter'],
   $apatient['migrantseasonal'],
   $apatient['family_size'],
   $apatient['monthly_income'],
   $apatient['homeless'],
   fixDate($apatient['financial_review']),
   $apatient['pubpid'],
   $pid,
   $olddata['providerID'],
   $apatient['genericname1'],
   $apatient['genericval1'],
   $apatient['genericname2'],
   $apatient['genericval2'],
   $apatient['phone_cell'],
   $apatient['hipaa_mail'],
   $apatient['hipaa_voice'],
   $olddata['squad']
  );

  newEmployerData(
   $pid,
   $aemployer['name'],
   $aemployer['street'],
   $aemployer['zip'],
   $aemployer['city'],
   $aemployer['state'],
   $aemployer['country']
  );

  setInsurance($pid, $ainsurance, $asubscriber, '1');
  setInsurance($pid, $ainsurance, $asubscriber, '2');
  setInsurance($pid, $ainsurance, $asubscriber, '3');

  echo "<html>\n<body>\n<script language='JavaScript'>\n";
  if ($alertmsg) echo " alert('$alertmsg');\n";
  echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
  echo " window.close();\n";
  echo "</script>\n</body>\n</html>\n";
  exit();
 }
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>Import Patient Demographics</title>
</head>
<body <? echo $top_bg_line ?> onload="javascript:document.forms[0].form_import_data.focus()">

<p>Paste the data to import into the text area below:</p>

<center>
<form method='post' action="import_xml.php">

<textarea name='form_import_data' rows='10' cols='50' style='width:95%'></textarea>

<p>
<input type='submit' name='form_import' value='Import Patient' /> &nbsp;
<input type='button' value='Cancel' onclick='window.close()' /></p>
</form>
</center>

</body>
</html>
