<?
 include_once("../../globals.php");
 include_once("$srcdir/lists.inc");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
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

<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<font class='title'>Patient Report</font><br>

<a class="link_submit" href="full_report.php">[View Comprehensive Patient Report]</a>

<form name='report_form' method='post' action='custom_report.php'>

<table>
 <tr>
  <td class='text' valign='top'>
   <input type='checkbox' name='include_demographics' value="demographics" checked>Demographics<br>
   <input type='checkbox' name='include_history' value="history">History<br>
   <input type='checkbox' name='include_employer' value="employer">Employer<br>
   <input type='checkbox' name='include_insurance' value="insurance">Insurance<br>
   <input type='checkbox' name='include_billing' value="billing" checked>Billing<br>
  </td>
  <td class='text' valign='top'>
   <!--
   <input type='checkbox' name='include_allergies' value="allergies">Allergies<br>
   <input type='checkbox' name='include_medications' value="medications">Medications<br>
   -->
   <input type='checkbox' name='include_immunizations' value="immunizations">Immunizations<br>
   <!--
   <input type='checkbox' name='include_medical_problems' value="medical_problems">Medical Problems<br>
   -->
   <input type='checkbox' name='include_notes' value="notes">Patient Notes<br>
   <input type='checkbox' name='include_transactions' value="transactions">Transactions<br>
  </td>
 </tr>
</table>

<br>
<a href='javascript:document.report_form.submit()' class='link_submit'>Generate Report</a>
<hr>

<table>
 <tr>

  <td valign='top' class='text'>
   <span class='bold'>Issues to Include in this Report: &nbsp; &nbsp;</span>
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
<?
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
<span class='bold'>Encounter Forms to Include in this Report:</span>
<br><br>
<?
 $isfirst = 1;

 $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
  "forms.formdir, forms.date AS fdate, form_encounter.date " .
  "FROM forms, form_encounter WHERE " .
  "forms.pid = '$pid' AND form_encounter.encounter = forms.encounter " .
  "ORDER BY form_encounter.date DESC, fdate ASC");

 while($result = sqlFetchArray($res)) {
  if ($result{"form_name"} == "New Patient Encounter") {
   if ($isfirst == 0) {
    print "</blockquote>\n\n";
   }
   $isfirst = 0;

   print "<input type=checkbox name='" . $result{"formdir"} . "_" .
    $result{"form_id"} . "' value='" . $result{"encounter"} . "'";
   print " >New Encounter" .
    " (" . date("Y-m-d",strtotime($result{"date"})) .
    ")<blockquote>\n";
  } else {
   print "<input type='checkbox' name='" . $result{"formdir"} . "_" .
    $result{"form_id"} . "' value='" . $result{"encounter"} . "'";
   print ">" . $result{"form_name"} . "<br>\n";
  }
  //call_user_func($result{"formdir"} . "_report", $pid, $result{"encounter"}, $cols, $result{"form_id"});
}
?>
</blockquote>

  </td>
 </tr>
</table>

<span class="bold">Documents</span>:<br>
<ul>
<?
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

<a href='javascript:document.report_form.submit()' class='link_submit'>Generate Report</a>

</body>
</html>
