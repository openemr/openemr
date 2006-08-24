<?
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
 color: #000000;
 font-size: 8pt;
}
</style>
</head>

<body <?echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<?
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

<table >

<?
 foreach ($ISSUE_TYPES as $key => $arr) {
  echo " <tr>\n";
  echo "  <td width='20%' valign='top'>\n";
  echo "   <a href='stats_full.php?active=all' target='Main'><font class='title'>" .
       $arr[0] . "</font><font class='more'>$tmore</font></a><br>\n";
  if ($result = getListByType($pid, $key, "id,title,comments,enddate", 1, "all", 0)) {
   foreach ($result as $iter) {
    if (! $iter['enddate']) {
     echo "<a class='link' target='Main' href='stats_full.php?active=1'>" .
      $iter['title'] . "</a><br>\n";
    }
   }
  }
  echo "  </td>\n";
  echo " </tr>\n";
 }
?>

<?php if (!$GLOBALS['weight_loss_clinic']) { ?>
<tr>
<td width="20%" valign="top">
<a href="immunizations.php" target="Main"><font class="title"><? xl('Immunizations','e'); ?></font><font class=more><?echo $tmore;?></font></a><br>
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
    echo "<a class=link target=Main href='immunizations.php'>" . $row{'immunization_data'} . "</a><br>\n";
  }
?>
</td>
</tr>
<?php } ?>

<tr>
<td width="20%" valign="top">
<?
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
