<?php

/**
 * new_search_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2010-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$fstart = isset($_REQUEST['fstart']) ? $_REQUEST['fstart'] + 0 : 0;

$searchcolor = empty($GLOBALS['layout_search_color']) ? 'var(--yellow)' : $GLOBALS['layout_search_color'];
$simpleSearch = $_GET['simple_search'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader('opener'); ?>
<style>
  form {
    padding: 0;
    margin: 0;
  }

  #searchCriteria {
    text-align: center;
    width: 100%;
    font-size: 0.8rem;
    background-color: var(--gray300);
    font-weight: bold;
    padding: 3px;
  }

  #searchResultsHeader th {
    font-size: 0.7rem;
  }

  #searchResults {
    width: 100%;
    height: 80%;
    overflow: auto;
  }

  #searchResults table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--white);
  }

  #searchResults tr {
    cursor: pointer;
  }

  #searchResults td {
    font-size: 0.7rem;
    border-bottom: 1px solid var(--gray200);
  }

  .topResult {
    background-color: <?php echo attr($searchcolor); ?>;
  }

  .billing {
    color: var(--danger);
    font-weight: bold;
  }

  .highlight {
    background-color: var(--info);
    color: var(--white);
  }
</style>
<script>
    // This is called when forward or backward paging is done.
    function submitList(offset) {
        var f = document.forms[0];
        var i = parseInt(f.fstart.value) + offset;
        if (i < 0) {
            i = 0;
        }
        f.fstart.value = i;
        f.submit();
    }
</script>
</head>
<body class="body_top">
    <form method='post' action='new_search_popup.php' name='theform'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <input type='hidden' name='fstart' value='<?php echo attr($fstart); ?>' />
        <?php
        $MAXSHOW = 100; // maximum number of results to display at once

        // Construct query and save search parameters as form fields.
        // An interesting requirement is to sort on the number of matching fields.

        $message = "";
        $numfields = 0;
        $relevance = "0";
        // array to hold the sql parameters for binding
        //  Note in this special situation, there are two:
        //   1. For the main sql statement - $sqlBindArray
        //   2. For the _set_patient_inc_count function - $sqlBindArraySpecial
        //      (this only holds $where and not $relevance binded values)
        $sqlBindArray = array();
        $sqlBindArraySpecial = array();
        $where = "1 = 0";
        foreach ($_REQUEST as $key => $value) {
            if (substr($key, 0, 3) != 'mf_') {
                continue; // "match field"
            }
            $fldname = substr($key, 3);
            // pubpid requires special treatment.  Match on that is fatal.
            if ($fldname == 'pubpid') {
                $relevance .= " + 1000 * ( " . add_escape_custom($fldname) . " LIKE ? )";
                array_push($sqlBindArray, $value);
            } else {
                $relevance .= " + ( " . add_escape_custom($fldname) . " LIKE ? )";
                array_push($sqlBindArray, $value);
            }
            $where .= " OR " . add_escape_custom($fldname) . " LIKE ?";
            array_push($sqlBindArraySpecial, $value);
            echo "<input type='hidden' name='" . attr($key) . "' value='" . attr($value) . "' />\n";
            ++$numfields;
        }

        $sql = "SELECT *, ( $relevance ) AS relevance, " .
            "DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS " .
            "FROM patient_data WHERE $where " .
            "ORDER BY relevance DESC, lname, fname, mname " .
            "LIMIT " . escape_limit($fstart) . ", " . escape_limit($MAXSHOW) . "";

        $sqlBindArray = array_merge($sqlBindArray, $sqlBindArraySpecial);
        $rez = sqlStatement($sql, $sqlBindArray);
        $result = array();
        while ($row = sqlFetchArray($rez)) {
            $result[] = $row;
        }
        _set_patient_inc_count($MAXSHOW, count($result), $where, $sqlBindArraySpecial);
        ?>
    </form>
    <div class="table-responsive">
        <table class="table border-0" cellpadding='5' cellspacing='0'>
            <tr>
                <td class='text'>
                    &nbsp;
                </td>
                <td class='text text-center'>
                    <?php if ($message) {
                        echo "<span class='text-danger font-weight-bold'>" . text($message) . "</span>\n";
                    } ?>
                </td>
                <td class='text text-right'>
                    <?php
                    // Show start and end row number, and number of rows, with paging links.
                    $count = $GLOBALS['PATIENT_INC_COUNT'];
                    $fend = $fstart + $MAXSHOW;
                    if ($fend > $count) {
                        $fend = $count;
                    }
                    ?>
                    <?php if ($fstart) { ?>
                        <a href="javascript:submitList(-<?php echo attr($MAXSHOW); ?>)">
                            &lt;&lt;
                        </a>&nbsp;
                    <?php } ?>
                    <?php echo ($fstart + 1) . text(" - $fend of $count") ?>
                    <?php if ($count > $fend) { ?>
                        &nbsp;&nbsp;
                        <a href="javascript:submitList(<?php echo attr($MAXSHOW); ?>)">
                            &gt;&gt;
                        </a>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
    <div id="searchResultsHeader" class="table-responsive">
        <table class="table">
            <thead class="thead-light">
            <tr>
                <th class="srID" scope="col"><?php echo xlt('Hits'); ?></th>
                <th class="srName" scope="col"><?php echo xlt('Name'); ?></th>
                <?php
                // This gets address plus other fields that are mandatory, up to a limit of 5.
                $extracols = array();
                $tres = sqlStatement("SELECT field_id, title FROM layout_options " .
                    "WHERE form_id = 'DEM' AND field_id != '' AND " .
                    "( uor > 1 OR uor > 0 AND edit_options LIKE '%D%' ) AND " .
                    "field_id NOT LIKE 'title' AND " .
                    "field_id NOT LIKE '_name' " .
                    "ORDER BY group_id, seq, title LIMIT 9");
                while ($trow = sqlFetchArray($tres)) {
                    $extracols[$trow['field_id']] = $trow['title'];
                    echo "<th class='srMisc' scope='col'>" . text(xl_layout_label($trow['title'])) . "</th>\n";
                }
                ?>
            </tr>
            </thead>
            <tr id="searchResults">
                <?php
                $pubpid_matched = false;
                if ($result) {
                    foreach ($result as $iter) {
                        $relevance = $iter['relevance'];
                        if ($relevance > 999) {
                            $relevance -= 999;
                            $pubpid_matched = true;
                        }
                        echo "<tr id='" . attr($iter['pid']) . "' class='oneresult";
                        // Highlight entries where all fields matched.
                        echo $numfields <= $iter['relevance'] ? " topresult" : "";
                        echo "'>";
                        echo "<td class='srID'>" . text($relevance) . "</td>\n";
                        echo "<td class='srName'>" . text($iter['lname'] . ", " . $iter['fname']) . "</td>\n";
                        foreach ($extracols as $field_id => $title) {
                            echo "<td class='srMisc'>" . text($iter[$field_id]) . "</td>\n";
                        }
                    }
                }
                ?>
            </tr>
        </table>
        <div style="text-align: center;">
            <?php if ($pubpid_matched) { ?>
                <input class='btn btn-primary' type='button' value='<?php echo xla('Cancel'); ?>' onclick='dlgclose();' />
            <?php } else { ?>
                <button class='btn btn-primary my-1' type='button' value='true' onclick='dlgclose("srcConfirmSave", false);'><?php echo xla('Confirm Create New Patient'); ?></button>
                <button class='btn btn-primary my-1' type='button' value='Cancel' onclick='dlgclose();'><?php echo xla('Cancel'); ?></button>
            <?php } ?>
        </div>
<script>
    // jQuery stuff to make the page a little easier to use
    $(function () {
        $(".oneresult").mouseover(function () {
            $(this).addClass("highlight");
        });
        $(".oneresult").mouseout(function () {
            $(this).removeClass("highlight");
        });
        <?php if (empty($simpleSearch)) { ?>
        $(".oneresult").click(function () {
            SelectPatient(this);
        });
        <?php } ?>
    });

    var SelectPatient = function (eObj) {
        <?php
        // The layout loads just the demographics frame here, which in turn
        // will set the pid and load all the other frames.
        $newPage = "../patient_file/summary/demographics.php?set_pid=";
        $target = "document";
        ?>
        objID = eObj.id;
        var parts = objID.split("~");
        opener.<?php echo $target; ?>.location.href = '<?php echo $newPage; ?>' + parts[0];
        dlgclose();
        return true;
    }

    var f = opener.document.forms[0];
    <?php if ($pubpid_matched) { ?>
    alert(<?php echo xlj('A patient with this ID already exists.'); ?>);
    <?php } else { ?>
    // unclear if still needed.
    if (typeof f.create !== 'undefined') {
        f.create.value = <?php echo xlj('Confirm Create New Patient'); ?>;
    }
    <?php } ?>
    <?php if (count($result ?? []) === 0) { ?>
    $("<td><h5><?php echo xlt('No matches were found.'); ?></h5></td>").appendTo("#searchResults");
    <?php } ?>
</script>
</body>
</html>
