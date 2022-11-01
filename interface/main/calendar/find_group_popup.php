<?php

/**
 * interface/main/calendar/find_group_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2007 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once("$srcdir/group.inc.php");
require_once("../../therapy_groups/therapy_groups_controllers/therapy_groups_controller.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$info_msg = "";
$group_types = TherapyGroupsController::prepareGroupTypesList();
// If we are searching, search.
//
if ($_POST['searchby'] && $_POST['searchparm']) {
    $searchby = $_POST['searchby'];
    $searchparm = trim($_POST['searchparm']);

    if ($searchby == "Name") {
        $result = getGroupData("$searchparm", "*", 'group_name');
    } elseif ($searchby == "ID") {
        $result = getGroupData("$searchparm", "*", 'group_id');
    }
}
?>
<html>
<head>
    <title><?php echo xlt('Group Finder'); ?></title>
    <?php Header::setupHeader('opener'); ?>

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

    <script

        function selgid(gid, name, end_date) {
            if (opener.closed || !opener.setgroup)
                alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
            else
                opener.setgroup(gid, name, end_date);
            dlgclose();
            return false;
        }

    </script>

</head>

<body class="body_top">

<div id="searchCriteria">
    <form method='post' name='theform' id="theform" action='find_group_popup.php'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <?php echo xlt('Search by') . ':'; ?>
        <select name='searchby'>
            <option value="Name"><?php echo xlt('Name'); ?></option>
            <option value="ID"<?php echo ($searchby == 'ID') ? ' selected' : ''; ?>><?php echo xlt('ID'); ?></option>
        </select>
        <?php echo xlt('for') . ':'; ?>
        <input type='text' id='searchparm' name='searchparm' size='12' value='<?php echo attr($_POST['searchparm']); ?>'>        &nbsp;
        <input type='submit' id="submitbtn" value='<?php echo xla('Search'); ?>'>
        <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>
    </form>
</div>


<?php if (!isset($_POST['searchparm'])) : ?>
    <div id="searchstatus"><?php echo xlt('Enter your search criteria above'); ?></div>
<?php elseif (count($result) == 0) : ?>
<div id="searchstatus" class="noResults"><?php echo xlt('No records found. Please expand your search criteria.'); ?>
    <br />
</div>
<?php elseif (count($result) >= 100) : ?>
<div id="searchstatus" class="tooManyResults"><?php echo xlt('More than 100 records found. Please narrow your search criteria.'); ?></div>
<?php elseif (count($result) < 100) : ?>
<div id="searchstatus" class="howManyResults"><?php echo text(count($result)); ?> <?php echo xlt('records found.'); ?></div>
<?php endif; ?>

<?php if (isset($result)) : ?>
<div id="searchResultsHeader">
<table>
 <tr>
  <th class="srName"><?php echo xlt('Name'); ?></th>
  <th class="srGID"><?php echo xlt('ID'); ?></th> <!-- (CHEMED) Search by phone number -->
    <th class="srType"><?php echo xlt('Type'); ?></th>
    <th class="srStartDate"><?php echo xlt('Start Date'); ?></th>
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

            echo " <tr class='" . attr($trClass) . "' id='" .
                attr($itergid . "~" . $itername . "~" . $itertype . "~" . $iter_start_date . "~" . $iter_end_date) . "'>";
            echo "  <td class='srName'>" . text($itername) . "</td>\n";
            echo "  <td class='srGID'>" . text($itergid) . "</td>\n";
            echo "  <td class='srType'>" . text($itertype) . "</td>\n";
            echo "  <td class='srStartDate'>" . text($iter_start_date) . "</td>\n";
            echo " </tr>";
        }
        ?>
        </table>
    </div>
<?php endif; ?>

<script>

    // jQuery stuff to make the page a little easier to use

    $(function () {
        $("#searchparm").trigger("focus");
        $(".oneresult").on("mouseover", function() { $(this).toggleClass("highlight"); });
        $(".oneresult").on("mouseout", function() { $(this).toggleClass("highlight"); });
        $(".oneresult").on("click", function() { SelectGroup(this); });

        $("#theform").on("submit" , function() { SubmitForm(this); });

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
