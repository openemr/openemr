<?php
include_once("../../globals.php");
include_once("$srcdir/lists.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");

// We used to present a more complex list for "medical problem" issues
// for athletic teams.  However we decided in May 2008 to stop that.
// The logic remains in case minds are again changed.  -- Rod
//
$fancy_stats = false; // $GLOBALS['athletic_team'];
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>
.link {
 font-family: sans-serif;
 text-decoration: none;
 /* color: #000000; */
 font-size: 8pt;
}
</style>
</head>

<body class="body_bottom">

<div id="patient_stats_summary">

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

<table id="patient_stats_issues">

<?php
$numcols = $fancy_stats ? '7' : '1';
$ix = 0;
foreach ($ISSUE_TYPES as $key => $arr) {
    // $result = getListByType($pid, $key, "id,title,begdate,enddate,returndate,extrainfo", "all", "all", 0);

    $query = "SELECT * FROM lists WHERE pid = $pid AND type = '$key' AND ";
    if ($fancy_stats) {
        $query .= "( enddate IS NULL OR returndate IS NULL ) ";
    } else {
        $query .= "enddate IS NULL ";
    }
    $query .= "ORDER BY begdate";
    $pres = sqlStatement($query);

    if (mysql_num_rows($pres) > 0 || $ix == 0) {

        // output a header for the $ISSUE_TYPE
        echo " <tr class='issuetitle'>\n";
        echo "  <td colspan='$numcols'>\n";
        echo "   <a href='stats_full.php?active=all' target='";
        echo $GLOBALS['concurrent_layout'] ? "_parent" : "Main";
        echo "' onclick='top.restoreSession()'><span class='title'>" .
                $arr[0] . "</span> <span class='more'>$tmore</span></a>\n";
        echo "  </td>\n";
        echo " </tr>\n";

        // Show headers if this is a long line.
        if ($fancy_stats && $arr[3] == 0 && mysql_num_rows($pres) > 0) {
            echo " <tr class='issueheaders'>\n";
            echo "  <td>&nbsp;&nbsp;<b>" .xl('Title'). "</b></td>\n";
            echo "  <td>&nbsp;<b>" .xl('Diag'). "</b></td>\n";
            echo "  <td>&nbsp;<b>" .xl('Start'). "</b></td>\n";
            echo "  <td>&nbsp;<b>" .xl('Return'). "</b></td>\n";
            echo "  <td>&nbsp;<b>" .xl('Games'). "</b></td>\n";
            echo "  <td class='right'>&nbsp;<b>" .xl('Days'). "</b></td>\n";
            echo "  <td class='right'>&nbsp;<b>" .xl('Enc'). "</b></td>\n";
            echo " </tr>\n";
        }

        while ($row = sqlFetchArray($pres)) {
            // output each issue for the $ISSUE_TYPE
            if (!$row['enddate'] && !$row['returndate'])
                $rowclass="noend_noreturn";
            else if (!$row['enddate'] && $row['returndate'])
                $rowclass="noend";
            else if ($row['enddate'] && !$row['returndate'])
                $rowclass = "noreturn";

            echo " <tr class='$rowclass;'>\n";

            if ($fancy_stats && $arr[3] == 0) {
                $endsecs = $row['returndate'] ? strtotime($row['returndate']) : time();
                $daysmissed = round(($endsecs - strtotime($row['begdate'])) / (60 * 60 * 24));
                $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter " .
                                "WHERE list_id = '" . $row['id'] . "'");
                // echo "  <td><a class='link' target='Main' href='stats_full.php?active=1'>" .
                //      $row['title'] . "</a></td>\n";
                echo "  <td>&nbsp;&nbsp;" . $row['title'] . "</td>\n";
                echo "  <td>&nbsp;" . $row['diagnosis'] . "</td>\n";
                echo "  <td>&nbsp;" . $row['begdate'] . "</td>\n";
                echo "  <td>&nbsp;" . $row['returndate'] . "</td>\n";
                echo "  <td>&nbsp;" . $row['extrainfo'] . "</td>\n";
                echo "  <td class='right'>&nbsp;$daysmissed</td>\n";
                echo "  <td class='right'>&nbsp;" . $ierow['count'] . "</td>\n";
            } else {
                echo "  <td colspan='$numcols'>&nbsp;&nbsp;" . $row['title'] . "</td>\n";
            }

            echo " </tr>\n";
        }
        // echo "  </td>\n";
        // echo " </tr>\n";
    }

    ++$ix;
}
?>
</table> <!-- end patient_stats_issues -->

<table id="patient_stats_spreadsheets">
<?php

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
            echo "   <span class='title'>Injury Log</span>\n";
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
</table> <!-- end patient_stats_spreadsheets -->

<?php if (!$GLOBALS['weight_loss_clinic']) { ?>
<table id="patient_stats_imm">
<tr class='issuetitle'>
<td colspan='<?php echo $numcols ?>' valign='top'>
<a href="immunizations.php"
 target="<?php echo $GLOBALS['concurrent_layout'] ? "_parent" : "Main"; ?>"
 onclick="top.restoreSession()">
<span class="title"><?php xl('Immunizations','e'); ?></span>
<span class=more><?php echo $tmore;?></span></a>
</td></tr>
<tr><td>

<?php
  $sql = "select i1.id as id, i1.immunization_id as immunization_id,".
         " if (i1.administered_date, concat(i1.administered_date,' - '), substring(i1.note,1,20)) as immunization_data ".
         " from immunizations i1 ".
         " where i1.patient_id = $pid ".
         " order by i1.immunization_id, i1.administered_date desc";

  $result = sqlStatement($sql);

  while ($row=sqlFetchArray($result)){
    echo "&nbsp;&nbsp;";
    echo "<a class='link' target='";
    echo $GLOBALS['concurrent_layout'] ? "_parent" : "Main";
    echo "' href='immunizations.php?mode=edit&id=".$row['id']."' onclick='top.restoreSession()'>" .
    $row{'immunization_data'} .
    generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']) .
    "</a><br>\n";
  }
?>
</td>
</tr>
</table> <!-- end patient_stats_imm-->
<?php } ?>


<table id="patient_stats_prescriptions">
<tr><td colspan='<?php echo $numcols ?>' class='issuetitle'>
<span class='title'><?php echo xl('Prescriptions'); ?></span>
</td></tr>
</tr><td>
<?php
$cwd= getcwd();
chdir("../../../");
require_once("library/classes/Controller.class.php");
$c = new Controller();
echo $c->act(array("prescription" => "", "block" => "", "patient_id" => $pid));
?>
</td></tr>
</table> <!-- end patient_stats_prescriptions -->

</div> <!-- end patient_stats_summary -->

</body>
</html>
