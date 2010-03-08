<?php
include_once("../../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/options.inc.php");

if (isset($mode)) {
    if ($mode == "add" ) {
        $sql = "REPLACE INTO immunizations set 
                      id = '" . mysql_real_escape_string($id) . "',
                      administered_date = if('" . mysql_real_escape_string($administered_date) . "','" . mysql_real_escape_string($administered_date) . "',NULL),  
                      immunization_id = '" . mysql_real_escape_string($form_immunization_id) . "',
                      manufacturer = '" . mysql_real_escape_string($manufacturer) . "',
                      lot_number = '" . mysql_real_escape_string($lot_number) . "',
                      administered_by_id = if(" . mysql_real_escape_string($administered_by_id) . "," . mysql_real_escape_string($administered_by_id) . ",NULL),
                      administered_by = if('" . mysql_real_escape_string($administered_by) . "','" . mysql_real_escape_string($administered_by) . "',NULL),
                      education_date = if('" . mysql_real_escape_string($education_date) . "','" . mysql_real_escape_string($education_date) . "',NULL), 
                      vis_date = if('" . mysql_real_escape_string($vis_date) . "','" . mysql_real_escape_string($vis_date) . "',NULL), 
                      note   = '" . mysql_real_escape_string($note) . "',
                      patient_id   = '" . mysql_real_escape_string($pid) . "',
                      created_by = '" . mysql_real_escape_string($_SESSION['authId']) . "',
                      updated_by = '" . mysql_real_escape_string($_SESSION['authId']) . "',
                      create_date = now() ";
        sqlStatement($sql);
        $administered_date=$education_date=date('Y-m-d');
        $immunization_id=$manufacturer=$lot_number=$administered_by_id=$note=$id="";
        $administered_by=$vis_date="";
    }
    elseif ($mode == "delete" ) {
        // log the event
        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Immunization id ".$_POST['id']." deleted from pid ".$_POST['pid']);
        // delete the immunization
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
            $administered_by_id = ($row['administered_by_id'] ? $row['administered_by_id'] : 0);
            $administered_by = $row['administered_by'];
            $education_date = $row['education_date'];
            $vis_date = $row['vis_date'];
            $note = stripslashes($row['note']);
        }
    }
}

// set the default sort method for the list of past immunizations
if (!$sortby) { $sortby = 'vacc'; }

// set the default value of 'administered_by'
if (!$administered_by && !$administered_by_id) { 
    $stmt = "select concat(lname,', ',fname) as full_name ".
            " from users where ".
            " id='".$_SESSION['authId']."'";
    $row = sqlQuery($stmt);
    $administered_by = $row['full_name'];
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
<input type="hidden" name="pid" id="pid" value="<?php echo $pid?>"> 
<br>
      <table border=0 cellpadding=1 cellspacing=1>

        <tr>
          <td align="right">
            <span class=text>
              <?php xl('Immunization','e'); ?>
            </span>
          </td>
          <td>
              <?php
               	// Modified 7/2009 by BM to incorporate the immunization items into the list_options listings
		generate_form_field(array('data_type'=>1,'field_id'=>'immunization_id','list_id'=>'immunizations','empty_title'=>'SKIP'), $immunization_id);
              ?>
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
            <input type="text" name="administered_by" id="administered_by" size="25" value="<?php echo $administered_by; ?>">
            <?php xl('or choose','e'); ?>
<!-- NEEDS WORK -->
            <select name="administered_by_id" id='administered_by_id'>
            <option value=""></option>
              <?php
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
              (<a href="http://www.cdc.gov/vaccines/pubs/vis/default.htm" title="<?php xl('Help','e'); ?>" target="_blank">?</a>)
          </td>
          <td>
            <input type='text' size='10' name="vis_date" id="vis_date"
                    value='<?php echo $vis_date ? $vis_date : date('Y-m-d'); ?>'
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
	
	    <input type="button" name="save" id="save" value="<?php xl('Save Immunization','e'); ?>">
	
            <input type="button" name="print" id="print" value="<?php echo xl('Print Record') . xl('PDF','',' (',')'); ?>">
	
	    <input type="button" name="printHtml" id="printHtml" value="<?php echo xl('Print Record') . xl('HTML','',' (',')'); ?>">
            
            <input type="reset" name="clear" id="clear" value="<?php xl('Clear','e'); ?>">
          </td>
        </tr>
      </table>
    </form>

<div id="immunization_list">

    <table border=0 cellpadding=3 cellspacing=0>

    <!-- some columns are sortable -->
    <tr class='text bold'>
    <th>
        <a href="javascript:top.restoreSession();location.href='immunizations.php?sortby=vacc';" title=<?php xl('Sort by vaccine','e','\'','\''); ?>><?php xl('Vaccine','e'); ?></a>
        <span class='small' style='font-family:arial'><?php if ($sortby == 'vacc') { echo 'v'; } ?></span>
    </th>
    <th>
        <a href="javascript:top.restoreSession();location.href='immunizations.php?sortby=date';" title=<?php xl('Sort by date','e','\'','\''); ?>><?php xl('Date','e'); ?></a>
        <span class='small' style='font-family:arial'><?php if ($sortby == 'date') { echo 'v'; } ?></span>
    </th>
    <th><?php xl('Manufacturer','e'); ?></th>
    <th><?php xl('Lot Number','e'); ?></th>
    <th><?php xl('Administered By','e'); ?></th>
    <th><?php xl('Education Date','e'); ?></th>
    <th><?php xl('Note','e'); ?></th>
    <th>&nbsp;</th>
    </tr>
    
<?php
        $sql = "select i1.id ,i1.immunization_id ,i1.administered_date ".
                ",i1.manufacturer ,i1.lot_number ".
                ",ifnull(concat(u.lname,', ',u.fname),'Other') as administered_by ".
                ",i1.education_date ,i1.note ".
                " from immunizations i1 ".
                " left join users u on i1.administered_by_id = u.id ".
                " where patient_id = $pid ".
                " order by ";
        if ($sortby == "vacc") { $sql .= " i1.immunization_id, i1.administered_date DESC"; }
        else { $sql .= " administered_date desc"; }

        $result = sqlStatement($sql);
        while($row = sqlFetchArray($result)) {
            if ($row["id"] == $id) {
                echo "<tr class='immrow text selected' id='".$row["id"]."'>";
            }
            else {
                echo "<tr class='immrow text' id='".$row["id"]."'>";
            }
	    // Modified 7/2009 by BM to utilize immunization items from the pertinent list in list_options
            echo "<td>" . generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']) . "</td>";
            echo "<td>" . $row["administered_date"] . "</td>";
            echo "<td>" . $row["manufacturer"] . "</td>";
            echo "<td>" . $row["lot_number"] . "</td>";
            echo "<td>" . $row["administered_by"] . "</td>";
            echo "<td>" . $row["education_date"] . "</td>";
            echo "<td>" . $row["note"] . "</td>";
            echo "<td><input type='button' class='delete' id='".$row["id"]."' value='" . xl('Delete') . "'></td>";
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
    $("#print").click(function() { PrintForm("pdf"); });
    $("#printHtml").click(function() { PrintForm("html"); });
    $(".immrow").click(function() { EditImm(this); });
    $(".delete").click(function(event) { DeleteImm(this); event.stopPropagation(); });

    $(".immrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".immrow").mouseout(function() { $(this).toggleClass("highlight"); });

    $("#administered_by_id").change(function() { $("#administered_by").val($("#administered_by_id :selected").text()); });
});

var PrintForm = function(typ) {
    top.restoreSession();
    newURL='shot_record.php?output='+typ+'&sortby=<?php echo $sortby; ?>';
    if (typ=="pdf") {
        location.href=newURL;
    }
    else { // typ=html
        window.open(newURL, '_blank', "menubar=1,toolbar=1,scrollbars=1,resizable=1,width=600,height=450");
    }	
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
    if (confirm("<?php xl('This action cannot be undone.','e'); ?>" + "\n" +"<?php xl('Do you wish to PERMANENTLY delete this immunization record?','e'); ?>")) {
        top.restoreSession();
        location.href='immunizations.php?mode=delete&id='+imm.id;
    }
}

</script>

</html>
