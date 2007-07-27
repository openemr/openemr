<?php
 include_once("../../globals.php");
 include_once("$srcdir/lists.inc");
 include_once("$srcdir/acl.inc");
?>
<html>

<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.link {
 font-family: sans-serif;
 text-decoration: none;
 /* color: #000000; */
 font-size: 8pt;
}
</style>
</head>

<body <?echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<?php
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(".xl('Issues not authorized').")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

<table cellpadding='0' cellspacing='0'>

<?php
 $numcols = $GLOBALS['athletic_team'] ? '7' : '1';
 $ix = 0;
 foreach ($ISSUE_TYPES as $key => $arr) {
  // $result = getListByType($pid, $key, "id,title,begdate,enddate,returndate,extrainfo", "all", "all", 0);

  $query = "SELECT * FROM lists WHERE pid = $pid AND type = '$key' AND ";
  if ($GLOBALS['athletic_team']) {
   $query .= "( enddate IS NULL OR returndate IS NULL ) ";
  } else {
   $query .= "enddate IS NULL ";
  }
  $query .= "ORDER BY begdate";
  $pres = sqlStatement($query);

  if (mysql_num_rows($pres) > 0 || $ix == 0) {
   echo " <tr>\n";
   echo "  <td colspan='$numcols' valign='top'>\n";
   echo "   <a href='stats_full.php?active=all' target='";
   echo $GLOBALS['concurrent_layout'] ? "_parent" : "Main";
   echo "' onclick='top.restoreSession()'><font class='title'>" .
        $arr[0] . "</font><font class='more'>$tmore</font></a>\n";
   echo "  </td>\n";
   echo " </tr>\n";

   // Show headers if this is a long line.
   if ($GLOBALS['athletic_team'] && $arr[3] == 0 && mysql_num_rows($pres) > 0) {
    echo " <tr>\n";
    echo "  <td class='link'>&nbsp;&nbsp;<b>" .xl('Title'). "</b></td>\n";
    echo "  <td class='link'>&nbsp;<b>" .xl('Diag'). "</b></td>\n";
    echo "  <td class='link'>&nbsp;<b>" .xl('Start'). "</b></td>\n";
    echo "  <td class='link'>&nbsp;<b>" .xl('Return'). "</b></td>\n";
    echo "  <td class='link'>&nbsp;<b>" .xl('Games'). "</b></td>\n";
    echo "  <td class='link' align='right'>&nbsp;<b>" .xl('Days'). "</b></td>\n";
    echo "  <td class='link' align='right'>&nbsp;<b>" .xl('Enc'). "</b></td>\n";
    echo " </tr>\n";
   }

   while ($row = sqlFetchArray($pres)) {
    $rowcolor = '#000000';
    if (!$row['enddate'] && !$row['returndate'])
     $rowcolor = '#ee0000';
    else if (!$row['enddate'] && $row['returndate'])
     $rowcolor = '#dd5500';
    else if ($row['enddate'] && !$row['returndate'])
     $rowcolor = '#0000ff';

    echo " <tr style='color:$rowcolor;'>\n";

    if ($GLOBALS['athletic_team'] && $arr[3] == 0) {
     $endsecs = $row['returndate'] ? strtotime($row['returndate']) : time();
     $daysmissed = round(($endsecs - strtotime($row['begdate'])) / (60 * 60 * 24));
     $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter " .
      "WHERE list_id = '" . $row['id'] . "'");
     // echo "  <td><a class='link' target='Main' href='stats_full.php?active=1'>" .
     //      $row['title'] . "</a></td>\n";
     echo "  <td class='link' style='color:$rowcolor;'>&nbsp;&nbsp;" . $row['title'] . "</td>\n";
     echo "  <td class='link' style='color:$rowcolor;'>&nbsp;" . $row['diagnosis'] . "</td>\n";
     echo "  <td class='link' style='color:$rowcolor;'>&nbsp;" . $row['begdate'] . "</td>\n";
     echo "  <td class='link' style='color:$rowcolor;'>&nbsp;" . $row['returndate'] . "</td>\n";
     echo "  <td class='link' style='color:$rowcolor;'>&nbsp;" . $row['extrainfo'] . "</td>\n";
     echo "  <td class='link' style='color:$rowcolor;' align='right'>&nbsp;$daysmissed</td>\n";
     echo "  <td class='link' style='color:$rowcolor;' align='right'>&nbsp;" . $ierow['count'] . "</td>\n";
    } else {
     echo "  <td colspan='$numcols' class='link'>&nbsp;&nbsp;" . $row['title'] . "</td>\n";
    }

    echo " </tr>\n";
   }
   // echo "  </td>\n";
   // echo " </tr>\n";
  }

  ++$ix;
 }

 // Show spreadsheet forms if any are present.
 //
 $need_head = true;
 foreach (array('treatment_protocols','injury_log') as $formname) {
  if (mysql_num_rows(sqlStatement("SHOW TABLES LIKE 'form_$formname'")) > 0) {
   $dres = sqlStatement("SELECT tp.id, tp.value FROM forms, " .
    "form_$formname AS tp WHERE forms.pid = $pid AND " .
    "forms.formdir = '$formname' AND tp.id = forms.form_id AND " .
    "tp.rownbr = -1 AND tp.colnbr = -1 AND tp.value LIKE '0%' " .
    "ORDER BY tp.value DESC");
   if (mysql_num_rows($dres) > 0 && $need_head) {
    $need_head = false;
    echo " <tr>\n";
    echo "  <td colspan='$numcols' valign='top'>\n";
    echo "   <font class='title'>Injury Log</font>\n";
    echo "  </td>\n";
    echo " </tr>\n";
   }
   while ($row = sqlFetchArray($dres)) {
    list($completed, $start_date, $template_name) = explode('|', $row['value'], 3);
    echo " <tr>\n";
    echo "  <td colspan='$numcols'>&nbsp;&nbsp;";
    echo "<a class='link' target='_blank' ";
    echo "href='../../forms/$formname/new.php?popup=1&id=";
    echo $row['id'] . "' onclick='top.restoreSession()'>$start_date $template_name</a></td>\n";
    echo " </tr>\n";
   }
  }
 }
?>

<?php if (!$GLOBALS['weight_loss_clinic']) { ?>
<tr>
<td colspan='<?php echo $numcols ?>' valign='top'>
<a href="immunizations.php"
 target="<?php echo $GLOBALS['concurrent_layout'] ? "_parent" : "Main"; ?>"
 onclick="top.restoreSession()">
<font class="title"><? xl('Immunizations','e'); ?></font>
<font class=more><?echo $tmore;?></font></a><br>

<?php
  $sql = "select if(i1.administered_date
    ,concat(i1.administered_date,' - ',i2.name)
    ,substring(i1.note,1,20)
    ) as immunization_data
    from immunizations i1
    left join immunization i2
    on i1.immunization_id = i2.id
    where i1.patient_id = $pid
    order by administered_date desc";

  $result = sqlStatement($sql);

  while ($row=sqlFetchArray($result)){
    echo "<a class='link' target='";
    echo $GLOBALS['concurrent_layout'] ? "_parent" : "Main";
    echo "' href='immunizations.php' onclick='top.restoreSession()'>" .
    $row{'immunization_data'} . "</a><br>\n";
  }
?>
</td>
</tr>
<?php } ?>

<tr>
<td colspan='<?php echo $numcols ?>' valign='top'>
<?php
$cwd= getcwd();
chdir("../../../");
require_once("library/classes/Controller.class.php");
$c = new Controller();
echo '<font class="title">'.xl('Prescriptions').'</font>';
echo $c->act(array("prescription" => "", "block" => "", "patient_id" => $pid));
?>
</td>
</tr>
</table>

</body>
</html>
