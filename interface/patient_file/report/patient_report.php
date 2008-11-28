<?php
 include_once("../../globals.php");
 include_once("$srcdir/lists.inc");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script language='JavaScript'>
 // When an issue is checked, auto-check all the related encounters.
 function issueClick(icb) {
  if (! icb.checked) return;
  var f = document.forms[0];
  var nel = f.elements.length;
  for (var i = 0; i < nel; ++i) {
   var ecb = f.elements[i];
   if (ecb.name == 'documents[]' ||
    ecb.name.indexOf('include_') == 0 ||
    ecb.name.indexOf('issue_'  ) == 0)
    continue;
   if (icb.value.indexOf('/' + ecb.value + '/') >= 0) {
    ecb.checked = true;
   }
  }
 }
</script>
</head>

<body class="body_top">

<font class='title'><?php xl('Patient Report','e'); ?></font><br>

<a class="link_submit" href="full_report.php" onclick="top.restoreSession()">
[<?php xl('View Comprehensive Patient Report','e'); ?>]</a>

<form name='report_form' method='post' action='custom_report.php'>

<table>
 <tr>
  <td class='text' valign='top'>
   <input type='checkbox' name='include_demographics' value="demographics" checked><?php xl('Demographics','e'); ?><br>
   <input type='checkbox' name='include_history' value="history"><?php xl(' History','e'); ?><br>
   <!--
   <input type='checkbox' name='include_employer' value="employer"><?php xl('Employer','e'); ?><br>
   -->
   <input type='checkbox' name='include_insurance' value="insurance"><?php xl('Insurance','e'); ?><br>
   <input type='checkbox' name='include_billing' value="billing"
    <?php if (!$GLOBALS['simplified_demographics']) echo 'checked'; ?>><?php xl('Billing','e'); ?><br>
  </td>
  <td class='text' valign='top'>
   <!--
   <input type='checkbox' name='include_allergies' value="allergies">Allergies<br>
   <input type='checkbox' name='include_medications' value="medications">Medications<br>
   -->
   <input type='checkbox' name='include_immunizations' value="immunizations"><?php xl('Immunizations','e'); ?><br>
   <!--
   <input type='checkbox' name='include_medical_problems' value="medical_problems">Medical Problems<br>
   -->
   <input type='checkbox' name='include_notes' value="notes"><?php xl('Patient Notes','e'); ?><br>
   <input type='checkbox' name='include_transactions' value="transactions"><?php xl('Transactions','e'); ?><br>
   <input type='checkbox' name='include_batchcom' value="batchcom"><?php xl('Communications','e'); ?><br>
  </td>
 </tr>
</table>

<br>
<a href='javascript:top.restoreSession();document.report_form.submit()' class='link_submit'><?php xl('Generate Report','e'); ?></a>
<hr>

<table>
 <tr>

  <td valign='top' class='text'>
   <span class='bold'><?php xl('Issues to Include in this Report','e'); ?>: &nbsp; &nbsp;</span>
   <br>&nbsp;
   <table cellpadding='1' cellspacing='2'>
    <!--
    <tr class='bold'>
     <td>Type</td>
     <td>Title</td>
     <td>Begin</td>
     <td>End &nbsp; &nbsp; &nbsp;</td>
    </tr>
    -->
<?php
// get issues
 $pres = sqlStatement("SELECT * FROM lists WHERE pid = $pid " .
  "ORDER BY type, begdate");
 $lasttype = "";
 while ($prow = sqlFetchArray($pres)) {
  if ($lasttype != $prow['type']) {
   $lasttype = $prow['type'];

   /****
   $disptype = $lasttype;
   switch ($lasttype) {
    case "allergy"        : $disptype = "Allergies"       ; break;
    case "problem"        :
    case "medical_problem": $disptype = "Medical Problems"; break;
    case "medication"     : $disptype = "Medications"     ; break;
    case "surgery"        : $disptype = "Surgeries"       ; break;
   }
   ****/
   $disptype = $ISSUE_TYPES[$lasttype][0];

   echo " <tr>\n";
   echo "  <td valign='top' colspan='4' class='bold'><b>$disptype</b></td>\n";
   echo " </tr>\n";
  }
  $rowid = $prow['id'];
  $disptitle = trim($prow['title']) ? $prow['title'] : "[Missing Title]";

  $ieres = sqlStatement("SELECT encounter FROM issue_encounter WHERE " .
   "pid = '$pid' AND list_id = '$rowid'");

  echo "    <tr class='text'>\n";
  echo "     <td valign='top'>&nbsp;</td>\n";
  echo "     <td valign='top'>";
  echo "<input type='checkbox' name='issue_$rowid' onclick='issueClick(this)' value='/";
  while ($ierow = sqlFetchArray($ieres)) {
   echo $ierow['encounter'] . "/";
  }
  echo "' />$disptitle</td>\n";
  echo "     <td valign='top'>" . $prow['begdate'];
  if ($prow['enddate']) {
   echo " - " . $prow['enddate'];
  } else {
   echo " Active";
  }
  echo " &nbsp; &nbsp; </td>\n";
  echo "    </tr>\n";
 }
?>
   </table>
   <br>
  </td>

  <td valign='top' class='text'>
<span class='bold'><?php xl('Encounter Forms to Include in this Report','e'); ?>:</span>
<br><br>
<?php
 $isfirst = 1;
 $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
  "forms.formdir, forms.date AS fdate, form_encounter.date " .
  "FROM forms, form_encounter WHERE " .
  "forms.pid = '$pid' AND form_encounter.pid = '$pid' AND " .
  "form_encounter.encounter = forms.encounter " .
  "ORDER BY form_encounter.date DESC, fdate ASC");
 $res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
$html_strings = array();
$registry_form_name = array();
 while($result2 = sqlFetchArray($res2)) {
   array_push($registry_form_name,trim($result2['name']));
 }
 while($result = sqlFetchArray($res)) {
  if ($result{"form_name"} == "New Patient Encounter") {
   if ($isfirst == 0) {
    foreach($registry_form_name as $var) {
      if ($toprint = $html_strings[$var]) { 
        {foreach($toprint as $var) {print $var;}}
      }
    }
    $html_strings = array();
    print "</blockquote>\n\n";
   }
   $isfirst = 0;
   print "<input type=checkbox name='" . $result{"formdir"} . "_" .
    $result{"form_id"} . "' value='" . $result{"encounter"} . "'";
   print " >New Encounter" .
    " (" . date("Y-m-d",strtotime($result{"date"})) .
    ")<blockquote>\n";
  } 
  else {
   $form_name = trim($result{"form_name"});
   //if form name is not in registry, look for the closest match by
  // finding a registry name which is  at the start of the form name.
  //this is to allow for forms to put additional helpful information
  //in the database in the same string as their form name after the name
   $form_name_found_flag = 0;
   foreach($registry_form_name as $var) {if ($var == $form_name) {$form_name_found_flag = 1;}}
   // if the form does not match precisely with any names in the registry, now see if any front partial matches
   // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
   if (!$form_name_found_flag) { foreach($registry_form_name as $var) {if (strpos($form_name,$var) == 0) {$form_name = $var;}}}
 
   if (!is_array($html_strings[$form_name])) {$html_strings[$form_name] = array();}
   array_push($html_strings[$form_name], "<input type='checkbox' name='" 
     . $result{"formdir"} . "_" 
     . $result{"form_id"} . "' value='" . $result{"encounter"} . "'"
     . ">" . $result{"form_name"} . "<br>\n");
  }
}
foreach($registry_form_name as $var) {
  if ($toprint = $html_strings[$var]) { 
    {foreach($toprint as $var) {print $var;}}
  }
}
?>
</blockquote>

  </td>
 </tr>
</table>

<span class="bold"><?php xl('Documents','e'); ?></span>:<br>
<ul>
<?php
//code lists available images
 $db = $GLOBALS['adodb']['db'];
 $sql = "SELECT d.id, d.url, c.name FROM documents AS d " .
  "LEFT JOIN categories_to_documents AS ctd ON d.id=ctd.document_id " .
  "LEFT JOIN categories AS c ON c.id = ctd.category_id WHERE " .
  "d.foreign_id = " . $db->qstr($pid);
 $result = $db->Execute($sql);
 echo $db->ErrorMsg();
 while ($result && !$result->EOF) {
  echo '<span class="bold"><input type="checkbox" name="documents[]" value="' .
   $result->fields['id'] . '">';
  echo '&nbsp;&nbsp;<i>' . $result->fields['name'] . "</i>";
  echo '&nbsp;&nbsp;Name: <i>' . basename($result->fields['url']) . "</i>";
  echo '</span><br>';
  $result->MoveNext();	
 }
?>
</ul>
</form>

<a href='javascript:top.restoreSession();document.report_form.submit()' class='link_submit'><?php xl('Generate Report','e'); ?></a>

</body>
</html>
