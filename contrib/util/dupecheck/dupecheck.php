<?php 
require_once("../../../library/sql.inc");
require_once("./Utils.php");

/* Use this code to identify duplicate patients in OpenEMR
 *
 */
$parameters = GetParameters();

// establish some defaults
if (! isset($parameters['sortby'])) { $parameters['sortby'] == "name"; }
if (! isset($parameters['limit'])) { $parameters['limit'] = 100; }

if (! isset($parameters['match_name']) &&
    ! isset($parameters['match_dob']) &&
    ! isset($parameters['match_sex']) &&
    ! isset($parameters['match_ssn']))
{
    $parameters['match_name'] = 'on';
    $parameters['match_dob'] = 'on';
}
    
$oemrdb = $GLOBALS['dbh'];
?>

<html>
<head>
<script type="text/javascript" src="../../../library/js/jquery.js"></script>
<style>
body {
    font-family: arial, helvetica, times new roman;
    font-size: 1em;
    background-color: #eee;
}
.match_block {
    border: 1px solid #eee;
    background-color: white;
    padding: 5px;
}

.match_block table {
    border-collapse: collapse;
}
.match_block table tr {
    cursor: pointer;
}
.match_block table td {
    padding: 5px;
}
    
.highlight {
    background-color: #99a;
    color: white;
}
.highlight_block {
    background-color: #ffa;
}
.bold {
    font-weight: bold;
}
    
</style>
</head>
<body>
<form name="search_form" id="search_form" method="post" action="dupecheck.php">
<input type="hidden" name="go" value="Go">
Matching criteria:
<input type="checkbox" name="match_name" id="match_name" <?php if ($parameters['match_name']) echo "CHECKED"; ?>> 
<label for="match_name">Name</label>
<input type="checkbox" name="match_dob" id="match_dob" <?php if ($parameters['match_dob']) echo "CHECKED"; ?>> 
<label for="match_dob">DOB</label>
<input type="checkbox" name="match_sex" id="match_sex" <?php if ($parameters['match_sex']) echo "CHECKED"; ?>> 
<label for="match_sex">Gender</label>
<input type="checkbox" name="match_ssn" id="match_ssn" <?php if ($parameters['match_ssn']) echo "CHECKED"; ?>> 
<label for="match_ssn">SSN</label>
<br>
Order results by:
<input type='radio' name='sortby' value='name' id="name" <?php if ($parameters['sortby']=='name') echo "CHECKED"; ?>>
<label for="name">Name</label>
<input type='radio' name='sortby' value='dob' id="dob" <?php if ($parameters['sortby']=='dob') echo "CHECKED"; ?>>
<label for="dob">DOB</label>
<input type='radio' name='sortby' value='sex' id="sex" <?php if ($parameters['sortby']=='sex') echo "CHECKED"; ?>>
<label for="sex">Gender</label>
<input type='radio' name='sortby' value='ssn' id="ssn" <?php if ($parameters['sortby']=='ssn') echo "CHECKED"; ?>>
<label for="ssn">SSN</label>
<br>
Limit search to first <input type='textbox' size='5' name='limit' id="limit" value='<?php echo $parameters['limit']; ?>'> records
<input type="button" name="do_search" id="do_search" value="Go">
</form>

<div id="thebiglist" style="height: 300px; overflow: auto; border: 1px solid blue;">
<form name="resolve" id="resolve" method="POST" action="dupcheck.php">

<?php
if ($parameters['go'] == "Go") {
    // go and do the search

    // counter that gathers duplicates into groups
    $dupecount = 0;

    // for EACH patient in OpenEMR find potential matches
    $sqlstmt = "select id, pid, fname, lname, dob, sex, ss, change_date from patient_data";
    switch ($parameters['sortby']) {
        case 'dob':
            $orderby = " ORDER BY dob";
            break;
        case 'sex':
            $orderby = " ORDER BY sex";
            break;
        case 'ssn':
            $orderby = " ORDER BY ss";
            break;
        case 'name':
        default:
            $orderby = " ORDER BY lname, fname";
            break;
    }
    $sqlstmt .= $orderby;
    if ($parameters['limit']) {
        $sqlstmt .= " LIMIT 0,".$parameters['limit'];
    }

    $qResults = mysql_query($sqlstmt, $oemrdb);
    while ($row = mysql_fetch_assoc($qResults)) {

        if ($dupelist[$row['id']] == 1) continue;

        $sqlstmt = "select id, pid, fname, lname, dob, sex, ss, change_date ".
                    " from patient_data where ";
        $sqland = "";
        if ($parameters['match_name']) {
            $sqlstmt .= $sqland . " fname='".$row['fname']."'";
            $sqland = " AND ";
            $sqlstmt .= $sqland . " lname='".$row['lname']."'";
        }
        if ($parameters['match_sex']) {
            $sqlstmt .= $sqland . " sex='".$row['sex']."'";
            $sqland = " AND ";
        }
        if ($parameters['match_ssn']) {
            $sqlstmt .= $sqland . " ss='".$row['ss']."'";
            $sqland = " AND ";
        }
        if ($parameters['match_dob']) {
            $sqlstmt .= $sqland . " dob='".$row['dob']."'";
            $sqland = " AND ";
        }
        $mResults = mysql_query($sqlstmt, $oemrdb);

        if (! $mResults) continue;
        if (mysql_num_rows($mResults) <= 1) continue;


        echo "<div class='match_block' style='padding: 5px 0px 5px 0px;'>";
        echo "<table>";

        echo "<tr class='onerow' id='".$row['id']."' oemrid='".$row['id']."' dupecount='".$dupecount."'>";
        echo "<td>".$row['lname'].", ".$row['fname']."</td>";
        echo "<td>".$row['dob']."</td>";
        echo "<td>".$row['sex']."</td>";
        echo "<td>".$row['ss']."</td>";
        echo "<td>".$row['change_date']."</td>";
        echo "<td><input type='button' value=' ? ' class='moreinfo' oemrid='".$row['pid']."' title='More info'></td>";
        echo "</tr>";

        while ($mrow = mysql_fetch_assoc($mResults)) {
            if ($row['id'] == $mrow['id']) continue;
            echo "<tr class='onerow' id='".$mrow['id']."' oemrid='".$mrow['id']."' dupecount='".$dupecount."'>";
            echo "<td>".$mrow['lname'].", ".$mrow['fname']."</td>";
            echo "<td>".$mrow['dob']."</td>";
            echo "<td>".$mrow['sex']."</td>";
            echo "<td>".$mrow['ss']."</td>";
            echo "<td>".$mrow['change_date']."</td>";
            echo "<td><input type='button' value=' ? ' class='moreinfo' oemrid='".$mrow['pid']."' title='More info'></td>";
            echo "</tr>";
            // to keep the output clean let's not repeat IDs already tagged as dupes
            $dupelist[$row['id']] = 1;
            $dupelist[$mrow['id']] = 1;
            $dupecount++;
        }

        echo "</table>";
        echo "</div>\n";
    }
}

?>
</div> <!-- end the big list -->
<?php if ($dupecount > 0) { echo $dupecount." duplicates found."; } ?><br>
<input type="button" id="do_resolve" value="Merge">
</form>

</body>

<script language="javascript">
$(document).ready(function(){

    // capture RETURN keypress
    $("#limit").keypress(function(evt) { if (evt.keyCode == 13) $("#do_search").click(); });

    // perform the database search for duplicates
    $("#do_search").click(function() { 
        $("#thebiglist").html("<p style='margin:10px;'><img src='../../../interface/pic/ajax-loader.gif'> Searching ...</p>");
        $("#search_form").submit();
        return true;
    });

    // pop up an OpenEMR window directly to the patient info
    var moreinfoWin = null; 
    $(".moreinfo").click(function(evt) { 
        if (moreinfoWin) { moreinfoWin.close(); }
        moreinfoWin = window.open("https://webserv.cfapress.org/openemr/interface/patient_file/patient_file.php?set_pid="+$(this).attr("oemrid"), "moreinfo");
        evt.stopPropagation();
    });

    // highlight the block of matching records
    $(".match_block").mouseover(function() { $(this).toggleClass("highlight_block"); });
    $(".match_block").mouseout(function() { $(this).toggleClass("highlight_block"); });

    // begin the merge of a block into a single record
    $(".onerow").click(function() {
        var dupecount = $(this).attr("dupecount");
        var masterid = $(this).attr("oemrid");
        var newurl = "mergerecords.php?masterid="+masterid;
        $("[dupecount="+dupecount+"]").each(function (i) {
            if (this.id != masterid) { newurl += "&otherid[]="+this.id; }
        });
        //alert(newurl);
        document.location.href = newurl;
    });
});
</script>

</html>
