<?php

/**
 * dupecheck index.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");
require_once("./Utils.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    foreach ($_POST as $key => $value) {
        $parameters[$key] = $value;
    }
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    die(xlt("Not Authorized"));
}

/* Use this code to identify duplicate patients in OpenEMR
 *
 */

// establish some defaults
if (! isset($parameters['sortby'])) {
    $parameters['sortby'] = "name";
}

if (! isset($parameters['limit'])) {
    $parameters['limit'] = 100;
}

if (
    ! isset($parameters['match_name']) &&
    ! isset($parameters['match_dob']) &&
    ! isset($parameters['match_sex']) &&
    ! isset($parameters['match_ssn'])
) {
    $parameters['match_name'] = 'on';
    $parameters['match_dob'] = 'on';
}
?>
<html>
<head>
<?php Header::setupHeader(['no_bootstrap', 'no_fontawesome', 'no_main-theme', 'no_textformat', 'no_dialog']); ?>
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
<form name="search_form" id="search_form" method="post" action="index.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="go" value="Go">
Matching criteria:
<input type="checkbox" name="match_name" id="match_name" <?php echo ($parameters['match_name']) ? "CHECKED" : ""; ?>>
<label for="match_name">Name</label>
<input type="checkbox" name="match_dob" id="match_dob" <?php echo ($parameters['match_dob']) ? "CHECKED" : ""; ?>>
<label for="match_dob">DOB</label>
<input type="checkbox" name="match_sex" id="match_sex" <?php echo ($parameters['match_sex']) ? "CHECKED" : ""; ?>>
<label for="match_sex">Gender</label>
<input type="checkbox" name="match_ssn" id="match_ssn" <?php echo ($parameters['match_ssn']) ? "CHECKED" : ""; ?>>
<label for="match_ssn">SSN</label>
<br />
Order results by:
<input type='radio' name='sortby' value='name' id="name" <?php echo ($parameters['sortby'] == 'name') ? "CHECKED" : ""; ?>>
<label for="name">Name</label>
<input type='radio' name='sortby' value='dob' id="dob" <?php echo ($parameters['sortby'] == 'dob') ? "CHECKED" : ""; ?>>
<label for="dob">DOB</label>
<input type='radio' name='sortby' value='sex' id="sex" <?php echo ($parameters['sortby'] == 'sex') ? "CHECKED" : ""; ?>>
<label for="sex">Gender</label>
<input type='radio' name='sortby' value='ssn' id="ssn" <?php echo ($parameters['sortby'] == 'ssn') ? "CHECKED" : ""; ?>>
<label for="ssn">SSN</label>
<br />
Limit search to first <input type='textbox' size='5' name='limit' id="limit" value='<?php echo attr($parameters['limit']); ?>'> records
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
    $sqlstmt = "select id, pid, fname, lname, dob, sex, ss from patient_data";
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
        $sqlstmt .= " LIMIT 0," . escape_limit($parameters['limit']);
    }

    $qResults = sqlStatement($sqlstmt);
    while ($row = sqlFetchArray($qResults)) {
        if ($dupelist[$row['id']] == 1) {
            continue;
        }

        $sqlBindArray = array();
        $sqlstmt = "select id, pid, fname, lname, dob, sex, ss " .
                    " from patient_data where ";
        $sqland = "";
        if ($parameters['match_name']) {
            $sqlstmt .= $sqland . " fname=?";
            $sqland = " AND ";
            $sqlstmt .= $sqland . " lname=?";
            array_push($sqlBindArray, $row['fname'], $row['lname']);
        }

        if ($parameters['match_sex']) {
            $sqlstmt .= $sqland . " sex=?";
            $sqland = " AND ";
            array_push($sqlBindArray, $row['sex']);
        }

        if ($parameters['match_ssn']) {
            $sqlstmt .= $sqland . " ss=?";
            $sqland = " AND ";
            array_push($sqlBindArray, $row['ss']);
        }

        if ($parameters['match_dob']) {
            $sqlstmt .= $sqland . " dob=?";
            $sqland = " AND ";
            array_push($sqlBindArray, $row['dob']);
        }

        $mResults = sqlStatement($sqlstmt, $sqlBindArray);

        if (! $mResults) {
            continue;
        }

        if (sqlNumRows($mResults) <= 1) {
            continue;
        }


        echo "<div class='match_block' style='padding: 5px 0px 5px 0px;' id='dupediv" . attr($dupecount) . "'>";
        echo "<table>";

        echo "<tr class='onerow' id='" . attr($row['id']) . "' oemrid='" . attr($row['id']) . "' dupecount='" . attr($dupecount) . "' title='Merge duplicates into this record'>";
        echo "<td>" . text($row['lname']) . ", " . text($row['fname']) . "</td>";
        echo "<td>" . text($row['dob']) . "</td>";
        echo "<td>" . text($row['sex']) . "</td>";
        echo "<td>" . text($row['ss']) . "</td>";
        echo "<td><input type='button' value=' ? ' class='moreinfo' oemrid='" . attr($row['pid']) . "' title='More info'></td>";
        echo "</tr>";

        while ($mrow = sqlFetchArray($mResults)) {
            if ($row['id'] == $mrow['id']) {
                continue;
            }

            echo "<tr class='onerow' id='" . attr($mrow['id']) . "' oemrid='" . attr($mrow['id']) . "' dupecount='" . attr($dupecount) . "' title='Merge duplicates into this record'>";
            echo "<td>" . text($mrow['lname']) . ", " . text($mrow['fname']) . "</td>";
            echo "<td>" . text($mrow['dob']) . "</td>";
            echo "<td>" . text($mrow['sex']) . "</td>";
            echo "<td>" . text($mrow['ss']) . "</td>";
            echo "<td><input type='button' value=' ? ' class='moreinfo' oemrid='" . attr($mrow['pid']) . "' title='More info'></td>";
            echo "</tr>";
            // to keep the output clean let's not repeat IDs already tagged as dupes
            $dupelist[$row['id']] = 1;
            $dupelist[$mrow['id']] = 1;
        }

        $dupecount++;

        echo "</table>";
        echo "</div>\n";
    }
}

?>
</div> <!-- end the big list -->
<?php if ($dupecount > 0) : ?>
<div id="dupecounter" style='display:inline;'><?php echo text($dupecount); ?></div>
&nbsp;duplicates found
<?php endif; ?>
</form>

</body>

<script>

$(function () {

    // capture RETURN keypress
    $("#limit").on("keypress", function(evt) { if (evt.keyCode == 13) $("#do_search").click(); });

    // perform the database search for duplicates
    $("#do_search").on("click", function() {
        $("#thebiglist").html("<p style='margin:10px;'><img src='<?php echo $GLOBALS['webroot']; ?>/interface/pic/ajax-loader.gif'> Searching ...</p>");
        $("#search_form").trigger("submit");
        return true;
    });

    // pop up an OpenEMR window directly to the patient info
    var moreinfoWin = null;
    $(".moreinfo").on("click", function(evt) {
        if (moreinfoWin) { moreinfoWin.close(); }
        moreinfoWin = window.open("<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/patient_file.php?set_pid=" + encodeURIComponent($(this).attr("oemrid")), "moreinfo");
        evt.stopPropagation();
    });

    // highlight the block of matching records
    $(".match_block").on("mouseover", function() { $(this).toggleClass("highlight_block"); });
    $(".match_block").on("mouseout", function() { $(this).toggleClass("highlight_block"); });
    $(".onerow").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".onerow").on("mouseout", function() { $(this).toggleClass("highlight"); });

    // begin the merge of a block into a single record
    $(".onerow").on("click", function() {
        var dupecount = $(this).attr("dupecount");
        var masterid = $(this).attr("oemrid");
        var newurl = "mergerecords.php?dupecount=" + encodeURIComponent(dupecount) + "&masterid=" + encodeURIComponent(masterid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        $("[dupecount="+dupecount+"]").each(function (i) {
            if (this.id != masterid) { newurl += "&otherid[]=" + encodeURIComponent(this.id); }
        });
        // open a new window and show the merge results
        moreinfoWin = window.open(newurl, "mergewin");
    });
});

function removedupe(dupeid) {
    // remove the merged records from the list of duplicates
    $("#dupediv"+dupeid).remove();
    // reduce the duplicate counter
    var dcounter = parseInt($("#dupecounter").html());
    $("#dupecounter").html(dcounter-1);
}

</script>

</html>
