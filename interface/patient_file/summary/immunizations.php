<?
include_once("../../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/overlib_mini.js");
include_once("$srcdir/calendar.js");


  if (isset($mode)) {
    if ($mode == "add" ) {
      $sql = "REPLACE INTO immunizations set 
                      id = '" . mysql_real_escape_string($id) . "',
                      administered_date    = if('" . mysql_real_escape_string($administered_date) . "','" . mysql_real_escape_string($administered_date) . "',NULL),  
                      immunization_id  = '" . mysql_real_escape_string($immunization_id) . "',
                      manufacturer  = '" . mysql_real_escape_string($manufacturer) . "',
                      lot_number   = '" . mysql_real_escape_string($lot_number) . "',
                      administered_by_id     = if(" . mysql_real_escape_string($administered_by_id) . "," . mysql_real_escape_string($administered_by_id) . ",NULL),
                      education_date     = if('" . mysql_real_escape_string($education_date) . "','" . mysql_real_escape_string($education_date) . "',NULL), 
                      note   = '" . mysql_real_escape_string($note) . "',
                      patient_id   = '" . mysql_real_escape_string($pid) . "',
                      created_by = '" . mysql_real_escape_string($_SESSION['authId']) . "',
                      updated_by = '" . mysql_real_escape_string($_SESSION['authId']) . "',
                      create_date = now()";
      sqlStatement($sql);
      $administered_date=$education_date=date('Y-m-d');
      $immunization_id=$manufacturer=$lot_number=$administered_by_id=$note=$id="";
    }
    elseif ($mode == "clear" ) {
      $administered_date=$education_date=date('Y-m-d');
      $immunization_id=$manufacturer=$lot_number=$administered_by_id=$note=$id="";
    }
    elseif ($mode == "edit" ) {
      $sql = "select * from immunizations where id = " . mysql_real_escape_string($id);
      $results = sqlQ($sql);
      while ($row = mysql_fetch_assoc($results)) {
        $administered_date = $row['administered_date'];
        $immunization_id = $row['immunization_id'];
        $manufacturer = $row['manufacturer'];
        $lot_number = $row['lot_number'];
        $administered_by_id = $row['administered_by_id'] ? $row['administered_by_id'] : 0;
        $education_date = $row['education_date'];
        $note = stripslashes($row['note']);
      }
    }
  }
?>

<html>
  <head>
    <link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
  </head>

  <body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
    <a href="patient_summary.php" target="Main"><font class="title">Immunizations</font><font class=back><?echo $tback;?></font></a>
    <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

    <form action="immunizations.php" name=add_immunization>
      <input type=hidden name=mode value="add">
      <br>
      <table border=0 cellpadding=1 cellspacing=1>

        <tr>
          <td align="right">
            <span class=text>
              Immunization
            </span>
          </td>
          <td>
            <select name="immunization_id">
              <?php
                $sql = "select id,name from immunization order by name";
                $result = sqlStatement($sql);
                while($row = sqlFetchArray($result)){
                  echo '<OPTION VALUE=' . $row{'id'};
                  echo $immunization_id == $row{'id'} ? ' selected>' : '>';
                  echo $row{'name'} . '</OPTION>';
                }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              Date Administered
            </span>
          </td>
          <td>
            <input class=text type=entry name="administered_date" size=10 value="<?php echo $administered_date ? $administered_date : date('Y-m-d'); ?>">
            <a href="javascript:show_calendar('add_immunization.administered_date');" onMouseOver="window.status='Date Picker'; overlib('Click here to choose a date.'); return true;" onMouseOut="window.status=''; nd(); return true;"><img src=<?echo "$rootdir/pic/show_calendar.gif";?> width=24 height=22 border=0></a>
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              Immunization Manufacturer
            </span>
          </td>
          <td>
            <input class=text type=entry name="manufacturer" size=25" value="<?php echo mysql_real_escape_string($manufacturer) ?>">
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              Immunization Lot Number
            </span>
          </td>
          <td>
            <input class=text  type=entry name="lot_number" size=25 value="<?php echo mysql_real_escape_string($lot_number) ?>">
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              Name and Title of Immunization Administrator
            </span>
          </td>
          <td>
            <select name="administered_by_id">
              <?php
                $sql = "(select id
                               ,concat(lname,', ',fname) as full_name
                           from users
                         order by concat(lname,', ',fname))
                         union all
                         select 0, 'Other'
                       ";
                $result = sqlStatement($sql);
                while($row = sqlFetchArray($result)){
                  echo '<OPTION VALUE=' . $row{'id'};
                  echo (isset($administered_by_id) && $administered_by_id != "" ? $administered_by_id : $_SESSION['authId']) == $row{'id'} ? ' selected>' : '>';
                  echo $row{'full_name'} . '</OPTION>';
                }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              Date Immunization Information Statements Given
            </span>
          </td>
          <td>
            <input class=text type=entry name="education_date" size=10 value="<?php echo $education_date? $education_date : date('Y-m-d'); ?>">
            <a href="javascript:show_calendar('add_immunization.education_date');" onMouseOver="window.status='Date Picker'; overlib('Click here to choose a date.'); return true;" onMouseOut="window.status=''; nd(); return true;"><img src=<?echo "$rootdir/pic/show_calendar.gif";?> width=24 height=22 border=0></a>
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              Notes
            </span>
          </td>
          <td>
            <textarea class=text name="note" rows=5 cols=25><?php echo $note ?></textarea>
          </td>
        </tr>
        <tr>
          <td align="center">
            <br /><a href='shot_record.php' class=link>[Print Shot Record]</a>
          </td>
          <td align="center">
            <input type="hidden" name="id" value="<?=$id?>"> 
            <br /><a href='javascript:document.add_immunization.submit();' class=link>[Save Immunization]</a>
          </td>
          <td align="center">
            <br /><a href='immunizations.php?mode=clear' class=link>[Clear]</a>
          </td>
        </tr>
      </table>
    </form>

    <table border=0 cellpadding=0 cellspacing=0>
      <tr>
        <td valign=top>
          <table border=0 cellpadding=5 cellspacing=0>
            <th><td><span class=bold>Date</span></td><td><span class=bold>Vaccine</span></td><td><span class=bold>Manufacturer</span></td><td><span class=bold>Lot Number</span></td><td><span class=bold>Administered By</span></td><td><span class=bold>Education Date</span></td><td><span class=bold>Note</span></td></th>
              <?php
                $sql = "select i1.id
                              ,i1.administered_date
                              ,i2.name as immunization
                              ,i1.manufacturer
                              ,i1.lot_number
                              ,ifnull(concat(u.lname,', ',u.fname),'Other') as administered_by
                              ,i1.education_date
                              ,i1.note
                          from immunizations i1
                                 left join immunization i2
                                   on i1.immunization_id = i2.id
                                 left join users u 
                                   on i1.administered_by_id = u.id
                         where patient_id = $pid 
                        order by administered_date desc";
                $result = sqlStatement($sql);
                while($row = sqlFetchArray($result)){
                  print "<tr><td><a class=link href='immunizations.php?mode=edit&id=".$row{"id"}."'>[Edit]</a></td>";
                  print "<td><span class=text>" . $row{"administered_date"} . "</span></td>";
                  print "<td><span class=text>" . $row{"immunization"} . "</span></td>";
                  print "<td><span class=text>" . $row{"manufacturer"} . "</span></td>";
                  print "<td><span class=text>" . $row{"lot_number"} . "</span></td>";
                  print "<td><span class=text>" . $row{"administered_by"} . "</span></td>";
                  print "<td><span class=text>" . $row{"education_date"} . "</span></td>";
                  print "<td><span class=text>" . $row{"note"} . "</span></td></tr>";
                }
              ?>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>

