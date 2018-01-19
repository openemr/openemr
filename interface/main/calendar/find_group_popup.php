<?php
/**
 * interface/main/calendar/find_group_popup.php
 *
 * Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR

 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */



require_once('../../globals.php');
require_once("$srcdir/group.inc");
require_once("$srcdir/formdata.inc.php");
require_once("../../therapy_groups/therapy_groups_controllers/therapy_groups_controller.php");

$info_msg = "";
$group_types = TherapyGroupsController::prepareGroupTypesList();
// If we are searching, search.
//
if ($_REQUEST['searchby'] && $_REQUEST['searchparm']) {
    $searchby = $_REQUEST['searchby'];
    $searchparm = trim($_REQUEST['searchparm']);

    if ($searchby == "Name") {
        $result = getGroupData("$searchparm", "*", 'group_name');
    } elseif ($searchby == "ID") {
        $result = getGroupData("$searchparm", "*", 'group_id');
    }
}
?>

<html>
<head>
    <?php html_header_show(); ?>
    <title><?php echo htmlspecialchars(xl('Group Finder'), ENT_NOQUOTES); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
    <script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
    <style>
        form {
            padding: 0px;
            margin: 0px;
        }

        #searchCriteria {
            text-align: center;
            width: 100%;
            font-size: 0.8em;
            background-color: #ddddff;
            font-weight: bold;
            padding: 3px;
        }

        #searchResultsHeader {
            width: 100%;
            background-color: lightgrey;
        }

        #searchResultsHeader table {
            width: 96%; /* not 100% because the 'searchResults' table has a scrollbar */
            border-collapse: collapse;
        }

        #searchResultsHeader th {
            font-size: 0.7em;
        }

        #searchResults {
            width: 96%;
            height: 80%;
            overflow: auto;
        }

        #results_table{
            text-align: center;
        }

        /* search results column widths */
        .srName {
            width: 30%;
        }

        .srGID {
            width: 21%;
        }

        .srType {
            width: 17%;
        }

        .srStartDate {
            width: 17%;
        }

        #searchResults table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        #searchResults tr {
            cursor: hand;
            cursor: pointer;
        }

        #searchResults td {
            font-size: 0.7em;
            border-bottom: 1px solid #eee;
        }

        .oneResult {
        }

        .billing {
            color: red;
            font-weight: bold;
        }

        /* for search results or 'searching' notification */
        #searchstatus {
            font-size: 0.8em;
            font-weight: bold;
            padding: 1px 1px 10px 1px;
            font-style: italic;
            color: black;
            text-align: center;
        }

        .noResults {
            background-color: #ccc;
        }

        .tooManyResults {
            background-color: #fc0;
        }

        .howManyResults {
            background-color: #9f6;
        }

        #searchspinner {
            display: inline;
            visibility: hidden;
        }

        /* highlight for the mouse-over */
        .highlight {
            background-color: #336699;
            color: white;
        }
    </style>

    <script type="text/javascript"
            src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-2-2/index.js"></script>
    <!-- ViSolve: Verify the noresult parameter -->
    <?php
    if (isset($_GET["res"])) {
        echo '
<script language="Javascript">
			// Pass the variable to parent hidden type and submit
			opener.document.theform.resname.value = "noresult";
			opener.document.theform.submit();
			// Close the window
			window.self.close();
</script>';
    }
    ?>
    <!-- ViSolve: Verify the noresult parameter -->

    <script language="JavaScript">

        function selgid(gid, name, end_date) {
            if (opener.closed || !opener.setgroup)
                alert("<?php echo htmlspecialchars(xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
            else
                opener.setgroup(gid, name, end_date);
            dlgclose();
            return false;
        }

    </script>

</head>

<body class="body_top">

<div id="searchCriteria">
    <form method='post' name='theform' id="theform"
          action='find_group_popup.php?<?php if (isset($_GET['pflag'])) {
                echo "pflag=0";
} ?>'>
        <?php echo htmlspecialchars(xl('Search by'), ENT_NOQUOTES) . ':'; ?>
        <select name='searchby'>
            <option value="Name"><?php echo htmlspecialchars(xl('Name'), ENT_NOQUOTES); ?></option>
            <option
                value="ID"<?php if ($searchby == 'ID') {
                    echo ' selected';
} ?>><?php echo htmlspecialchars(xl('ID'), ENT_NOQUOTES); ?></option>
        </select>
        <?php echo htmlspecialchars(xl('for'), ENT_NOQUOTES) . ':'; ?>
        <input type='text' id='searchparm' name='searchparm' size='12'
               value='<?php echo htmlspecialchars($_REQUEST['searchparm'], ENT_QUOTES); ?>'
               title='<?php echo htmlspecialchars(xl(''), ENT_QUOTES); ?>'>
        &nbsp;
        <input type='submit' id="submitbtn" value='<?php echo htmlspecialchars(xl('Search'), ENT_QUOTES); ?>'>
        <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>
    </form>
</div>


<?php if (!isset($_REQUEST['searchparm'])) : ?>
    <div id="searchstatus"><?php echo htmlspecialchars(xl('Enter your search criteria above'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result) == 0) : ?>
<div id="searchstatus"
     class="noResults"><?php echo htmlspecialchars(xl('No records found. Please expand your search criteria.'), ENT_NOQUOTES); ?>
    <br>
</div>
<?php elseif (count($result) >= 100) : ?>
<div id="searchstatus" class="tooManyResults"><?php echo htmlspecialchars(xl('More than 100 records found. Please narrow your search criteria.'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result) < 100) : ?>
<div id="searchstatus" class="howManyResults"><?php echo htmlspecialchars(count($result), ENT_NOQUOTES); ?> <?php echo htmlspecialchars(xl('records found.'), ENT_NOQUOTES); ?></div>
<?php endif; ?>

<?php if (isset($result)) : ?>

<div id="searchResultsHeader">
<table>
 <tr>
  <th class="srName"><?php echo htmlspecialchars(xl('Name'), ENT_NOQUOTES); ?></th>
  <th class="srGID"><?php echo htmlspecialchars(xl('ID'), ENT_NOQUOTES); ?></th> <!-- (CHEMED) Search by phone number -->
    <th class="srType"><?php echo htmlspecialchars(xl('Type'), ENT_NOQUOTES); ?></th>
    <th class="srStartDate"><?php echo htmlspecialchars(xl('Start Date'), ENT_NOQUOTES); ?></th>
    </tr>
    </table>
</div>

<div id="searchResults">
    <table id="results_table">
        <?php
        foreach ($result as $iter) {
            $itergid = $iter['group_id'];
            $itername = $iter['group_name'];
            $itertype = $group_types[$iter['group_type']];
            $iter_start_date = $iter['group_start_date'];
            $iter_end_date = $iter['group_end_date'];

            $trClass = "oneresult";

            echo " <tr class='" . $trClass . "' id='" .
                htmlspecialchars($itergid . "~" . $itername . "~" . $itertype . "~" . $iter_start_date . "~" . $iter_end_date, ENT_QUOTES) . "'>";
            echo "  <td class='srName'>" . htmlspecialchars($itername, ENT_NOQUOTES);
            echo "  <td class='srGID'>" . htmlspecialchars($itergid, ENT_NOQUOTES) . "</td>\n";
            echo "  <td class='srType'>" . htmlspecialchars($itertype, ENT_NOQUOTES) . "</td>\n";
            echo "  <td class='srStartDate'>" . htmlspecialchars($iter_start_date, ENT_NOQUOTES) . "</td>\n";
            echo " </tr>";
        }
        ?>
        </table>
    </div>
<?php endif; ?>

<script language="javascript">

    // jQuery stuff to make the page a little easier to use

    $(document).ready(function(){
        $("#searchparm").focus();
        $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
        $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
        $(".oneresult").click(function() { SelectGroup(this); });
        //ViSolve
        $(".noresult").click(function () { SubmitForm(this);});

        //$(".event").dblclick(function() { EditEvent(this); });
        $("#theform").submit(function() { SubmitForm(this); });

    });

    // show the 'searching...' status and submit the form
    var SubmitForm = function(eObj) {
        $("#submitbtn").css("disabled", "true");
        $("#searchspinner").css("visibility", "visible");
        return true;
    }


    // parts[] ==>  0=GID, 1=Group Name 4=Group End Date
    var SelectGroup = function (eObj) {
        objID = eObj.id;
        var parts = objID.split("~");
        return selgid(parts[0], parts[1], parts[4]);
    }

</script>

</center>
</body>
</html>
