<?php
include_once("../../globals.php");
include_once("$srcdir/sql.inc");

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
                      vis_date = if('" . mysql_real_escape_string($vis_date) . "','" . mysql_real_escape_string($vis_date) . "',NULL), 
                      note   = '" . mysql_real_escape_string($note) . "',
                      patient_id   = '" . mysql_real_escape_string($pid) . "',
                      created_by = '" . mysql_real_escape_string($_SESSION['authId']) . "',
                      updated_by = '" . mysql_real_escape_string($_SESSION['authId']) . "',
                      create_date = now() ";
        sqlStatement($sql);
        $administered_date=$education_date=date('Y-m-d');
        $immunization_id=$manufacturer=$lot_number=$administered_by_id=$note=$id="";
    }
    elseif ($mode == "clear" ) {
        $administered_date=$education_date=date('Y-m-d');
        $immunization_id=$manufacturer=$lot_number=$administered_by_id=$note=$id="";
    }
    elseif ($mode == "delete" ) {
        $sql="DELETE FROM immunizations WHERE id =". mysql_real_escape_string($id)." LIMIT 1";
        sqlStatement($sql);
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
            $vis_date = $row['vis_date'];
            $note = stripslashes($row['note']);
        }
    }
}
?>
<html>
<head>
<?php html_header_show();?>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">
// required to validate date text boxes
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">

<?php if ($GLOBALS['concurrent_layout']) { ?>
    <span class="title"><?php xl('Immunizations','e'); ?></span>
<?php } else { ?>
    <a href="patient_summary.php" target="Main" onclick="top.restoreSession()">
    <span class="title"><?php xl('Immunizations','e'); ?></span>
    <span class=back><?php echo $tback;?></span></a>
<?php } ?>

<form action="immunizations.php" name="add_immunization" id="add_immunization">
<input type="hidden" name="mode" id="mode" value="add">
<input type="hidden" name="id" id="id" value="<?php echo $id?>"> 
<br>
      <table border=0 cellpadding=1 cellspacing=1>

        <tr>
          <td align="right">
            <span class=text>
              <?php xl('Immunization','e'); ?>
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
              <?php xl('Date Administered','e'); ?>
            </span>
          </td>
          <td>

   <input type='text' size='10' name="administered_date" id="administered_date"
    value='<?php echo $administered_date ? $administered_date : date('Y-m-d'); ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc);'
    />
   <img src='<?php echo $rootdir; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_administered_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
    title='<?php xl('Click here to choose a date','e'); ?>'>

          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              <?php xl('Immunization Manufacturer','e'); ?>
            </span>
          </td>
          <td>
            <input class='text' type='text' name="manufacturer" size="25" value="<?php echo mysql_real_escape_string($manufacturer) ?>">
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class=text>
              <?php xl('Immunization Lot Number','e'); ?>
            </span>
          </td>
          <td>
            <input class='text' type='text' name="lot_number" size="25" value="<?php echo mysql_real_escape_string($lot_number) ?>">
          </td>
        </tr>
        <tr>
          <td align="right">
            <span class='text'>
              <?php xl('Name and Title of Immunization Administrator','e'); ?>
            </span>
          </td>
          <td class='text'>
NEEDS WORK
            <input type="text" name="administered_by" size="25">
            or choose
            <select name="administered_by_id">
              <?php

//              $sql = "(select id
//                             ,concat(lname,', ',fname) as full_name
//                         from users
//                       order by concat(lname,', ',fname))
//                       union all
//                              (select xtra_id, xtra_text from xtra limit 1)
//                     ";

                // This replaces the above.  There is no table "xtra".  -- Rod
                $sql = "select id, concat(lname,', ',fname) as full_name " .
                       "from users where username != '' " .
                       "order by concat(lname,', ',fname)";

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
          <td align="right" class="text">
              <?php xl('Date Immunization Information Statements Given','e'); ?>
          </td>
          <td>
            <input type='text' size='10' name="education_date" id="education_date"
                    value='<?php echo $education_date? $education_date : date('Y-m-d'); ?>'
                    title='<?php xl('yyyy-mm-dd','e'); ?>'
                    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc);'
            />
            <img src='<?php echo $rootdir; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22'
                id='img_education_date' border='0' alt='[?]' style='cursor:pointer;'
                title='<?php xl('Click here to choose a date','e'); ?>'
            />
          </td>
        </tr>
        <tr>
          <td align="right" class="text">
              <?php xl('Date of VIS Statement','e'); ?>
              (<a href="http://www.cdc.gov/vaccines/pubs/vis/default.htm" title="Help" target="_blank">?</a>)
          </td>
          <td>
            <input type='text' size='10' name="vis_date" id="vis_date"
                    value='<?php echo $education_date? $education_date : date('Y-m-d'); ?>'
                    title='<?php xl('yyyy-mm-dd','e'); ?>'
                    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc);'
            />
            <img src='<?php echo $rootdir; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22'
                id='img_vis_date' border='0' alt='[?]' style='cursor:pointer;'
                title='<?php xl('Click here to choose a date','e'); ?>'
            />
          </td>
        </tr>
        <tr>
          <td align="right" class='text'>
              <?php xl('Notes','e'); ?>
          </td>
          <td>
            <textarea class='text' name="note" id="note" rows=5 cols=25><?php echo $note ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center">
            <input type="button" name="print" id="print" value="<?php xl('Print Shot Record','e'); ?>">
            
            <input type="button" name="save" id="save" value="<?php xl('Save Immunization','e'); ?>">
            
            <input type="reset" name="clear" id="clear" value="<?php xl('Clear','e'); ?>">
          </td>
        </tr>
      </table>
    </form>

<div id="immunization_list">

    <table border=0 cellpadding=3 cellspacing=0>

    <!-- some columns are sortable -->
    <tr class='text bold'>
    <th>&nbsp;</th>
    <th><a href="javascript:top.restoreSession();location.href='immunizations.php?sortby=date';"><?php xl('Date','e'); ?></a></th>
    <th><a href="javascript:top.restoreSession();location.href='immunizations.php?sortby=vacc';"><?php xl('Vaccine','e'); ?></a></th>
    <th><?php xl('Manufacturer','e'); ?></th>
    <th><?php xl('Lot Number','e'); ?></th>
    <th><?php xl('Administered By','e'); ?></th>
    <th><?php xl('Education Date','e'); ?></th>
    <th><?php xl('Note','e'); ?></th>
    <th>&nbsp;</th>
    </tr>
    
<?php
        $sql = "select i1.id ,i1.administered_date ,i2.name as immunization ".
                ",i1.manufacturer ,i1.lot_number ".
                ",ifnull(concat(u.lname,', ',u.fname),'Other') as administered_by ".
                ",i1.education_date ,i1.note ".
                " from immunizations i1 ".
                " left join immunization i2 on i1.immunization_id = i2.id ".
                " left join users u on i1.administered_by_id = u.id ".
                " where patient_id = $pid ".
                " order by ";
        if ($sortby == "vacc") { $sql .= " immunization, i1.administered_date DESC"; }
        else { $sql .= " administered_date desc"; }

        $result = sqlStatement($sql);
        while($row = sqlFetchArray($result)) {
            if ($row["id"] == $id) {
                echo "<tr class='immrow text selected'>";
                echo "<td><input type='button' class='edit' id='".$row["id"]."' value='Edit' disabled=true></td>";
            }
            else {
                echo "<tr class='immrow text'>";
                echo "<td><input type='button' class='edit' id='".$row["id"]."' value='Edit'></td>";
            }
            echo "<td>" . $row["administered_date"] . "</td>";
            echo "<td>" . $row["immunization"] . "</td>";
            echo "<td>" . $row["manufacturer"] . "</td>";
            echo "<td>" . $row["lot_number"] . "</td>";
            echo "<td>" . $row["administered_by"] . "</td>";
            echo "<td>" . $row["education_date"] . "</td>";
            echo "<td>" . $row["note"] . "</td>";
            echo "<td><input type='button' class='delete' id='".$row["id"]."' value='Delete'></td>";
            echo "</tr>";
        }
?>

    </table>
</div> <!-- end immunizations -->

  </body>

<script language="javascript">
/* required for popup calendar */
Calendar.setup({inputField:"administered_date", ifFormat:"%Y-%m-%d", button:"img_administered_date"});
Calendar.setup({inputField:"education_date", ifFormat:"%Y-%m-%d", button:"img_education_date"});
Calendar.setup({inputField:"vis_date", ifFormat:"%Y-%m-%d", button:"img_vis_date"});

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#save").click(function() { SaveForm(); });
    $("#print").click(function() { PrintForm(); });
    $(".edit").click(function() { EditImm(this); });
    $(".delete").click(function() { DeleteImm(this); });
    $(".immrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".immrow").mouseout(function() { $(this).toggleClass("highlight"); });
});

var PrintForm = function() {
    top.restoreSession();
    location.href='shot_record.php';
}

var SaveForm = function() {
    top.restoreSession();
    $("#add_immunization").submit();
}

var EditImm = function(imm) {
    top.restoreSession();
    location.href='immunizations.php?mode=edit&id='+imm.id;
}

var DeleteImm = function(imm) {
    if (confirm("This action cannot be undone.\nDo you wish to PERMANENTLY delete this immunization record?")) {
        alert('immunizations.php?mode=delete&id='+imm.id);
        //top.restoreSession();
        //location.href='immunizations.php?mode=delete&id='+imm.id;
    }
}

</script>

</html>
