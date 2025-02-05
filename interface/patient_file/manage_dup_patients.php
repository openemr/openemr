<?php

/*
 * This tool helps with identifying and merging duplicate patients.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$first_time = true;

/**
 * @param $row
 * @param $pid
 * @return void
 */
function displayRow($row, $pid = ''): void
{
    global $first_time;

    if (empty($pid)) {
        $pid = $row['pid'];
    }

    if (isset($row['myscore'])) {
        $myscore = $row['myscore'];
        $options = "<option value=''></option>" .
            "<option value='MK'>" . xlt('Merge and Keep') . "</option>" .
            "<option value='MD'>" . xlt('Merge and Discard') . "</option>";
    } else {
        $myscore = $row['dupscore'];
        $options = "<option value=''></option>" .
            "<option value='U'>" . xlt('Mark as Unique') . "</option>" .
            "<option value='R'>" . xlt('Recompute Score') . "</option>";
        if (!$first_time) {
            echo " <tr><td class='detail' colspan='12'>&nbsp;</td></tr>\n";
        }
    }

    $first_time = false;
    $ptname = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
    $phones = array();
    if (trim($row['phone_home'])) {
        $phones[] = trim($row['phone_home']);
    }
    if (trim($row['phone_biz'])) {
        $phones[] = trim($row['phone_biz']);
    }
    if (trim($row['phone_cell'])) {
        $phones[] = trim($row['phone_cell']);
    }
    $phones = implode(', ', $phones);
    $fac_name = '';
    if ($row['home_facility']) {
        $facrow = getFacility($row['home_facility']);
        if (!empty($facrow['name'])) {
            $fac_name = $facrow['name'];
        }
    }
    $highlight_text = $row['dupscore'] > 17 ? xlt('Merge From') : '';
    $highlight_class = $row['dupscore'] > 17 ? 'highlight' : '';
    if (!empty($row['myscore']) && $row['myscore'] > 17) {
        $highlight_class = 'highlight-master';
        $highlight_text = xlt('Merge To');
    }
    echo "<tr class='$highlight_class'>";
    ?>
    <td>
        <select onchange='selectChange(this, <?php echo attr_js($pid); ?>, <?php echo attr_js($row['pid']); ?>)' style='width:100%'>
            <?php echo $options; // this is html and already escaped as required
            ?>
        </select>
    </td>
    <td>
        <?php echo text($myscore); ?>
    </td>
    <td class="text-warning" onclick="openNewTopWindow(<?php echo attr_js($row['pid']); ?>)"
        title="<?php echo xla('Click to open in a new window or tab'); ?>" style="cursor:pointer">
        <?php echo text($row['pid']); ?>
    </td>
    <td>
        <?php echo text($row['pubpid']); ?>
    </td>
    <td>
        <?php echo $highlight_text; ?>
    </td>
    <td>
        <?php echo text($ptname); ?>
    </td>
    <td>
        <?php echo text(oeFormatShortDate($row['DOB'])); ?>
    </td>
    <td>
        <?php echo text($row['sex']); ?>
    </td>
    <td>
        <?php echo text($row['email']); ?>
    </td>
    <td>
        <?php echo text($phones); ?>
    </td>
    <td>
        <?php echo text(oeFormatShortDate($row['regdate'])); ?>
    </td>
    <td>
        <?php echo text($row['street']); ?>
    </td>
    </tr>
    <?php
}

/**
 * @return int
 */
function calculateScores(): int
{
    sqlStatementNoLog("UPDATE patient_data SET dupscore = -9 WHERE dupscore != -1");

    $query_limit = 5000;
    $endtime = time() + 365 * 24 * 60 * 60; // a year from now
    $endtime = time() + 240 * 60;
    $count = 0;
    $finished = false;

    while (!$finished && time() < $endtime) {
        $scores = array();
        $query = "SELECT p1.pid, MAX(" . getDupScoreSQL() . ") AS dupscore" .
            " FROM patient_data AS p1, patient_data AS p2" .
            " WHERE p1.dupscore = -9 AND p2.pid < p1.pid" .
            " GROUP BY p1.pid ORDER BY p1.pid LIMIT " . escape_limit($query_limit);
        $results = sqlStatementNoLog($query);
        while ($row1 = sqlFetchArray($results)) {
            $scores[$row1['pid']] = $row1['dupscore'];
        }
        foreach ($scores as $pid => $score) {
            sqlStatementNoLog(
                "UPDATE patient_data SET dupscore = ? WHERE pid = ?",
                array($score, $pid)
            );
            ++$count;
        }
        if (count($scores) < $query_limit) {
            $finished = true;
        }
    }

    return $count;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Duplicate Patient Management")]);
    exit;
}

$calc_count = calculateScores();
$score_calculate = getDupScoreSQL();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Duplicate Patient Management') ?></title>
    <?php Header::setupHeader(['report-helper']); ?>

    <style>
      .table th, .table td {
        text-align: center;
        vertical-align: middle;
      }

      .table tr.highlight {
        background-color: #ffc10733 !important;
      }

      .table tr.highlight-master {
        background-color: #ff000733 !important;
      }
    </style>
    <script>
        $(function () {
            // Enable fixed headers when scrolling the report.
            if (window.oeFixedHeaderSetup) {
                oeFixedHeaderSetup(document.getElementById('mymaintable'));
            }
        });

        function openNewTopWindow(pid) {
            document.fnew.patientID.value = pid;
            top.restoreSession();
            document.fnew.submit();
        }

        function selectChange(select, toppid, rowpid) {
            let form = document.forms[0];
            if (select.value == '') {
                return;
            }
            top.restoreSession();
            if (select.value == 'MK') {
                window.location = 'merge_patients.php?pid1=' + encodeURIComponent(rowpid) + '&pid2=' + encodeURIComponent(toppid);
            } else if (select.value == 'MD') {
                window.location = 'merge_patients.php?pid1=' + encodeURIComponent(toppid) + '&pid2=' + encodeURIComponent(rowpid);
            } else {
                // Currently 'U' and 'R' actions are supported and rowpid is meaningless.
                form.form_action.value = select.value;
                form.form_toppid.value = toppid;
                form.form_rowpid.value = rowpid;
                form.submit();
            }
        }
    </script>
</head>
<body class="container-fluid bg-light text-dark">
    <div class="text-center mx-2">
        <div class="text-center mt-1 w-100">
            <h2><?php echo xlt('Duplicate Patient Management') ?></h2>
        </div>
        <form class="form" method='post' action='manage_dup_patients.php'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="btn-sm-group mb-1 text-center">
                <button class="btn btn-sm btn-primary btn-refresh" type='submit' name='form_refresh' value="Refresh"><?php echo xla('ReCalculate Scores') ?></button>
                <button class="btn btn-sm btn-primary btn-print" type='button' value='Print' onclick='window.print()'><?php echo xla('Print'); ?></button>
            </div>
            <table id='mymaintable' class='table table-sm table-bordered table-hover w-100 table-light'>
                <thead class="thead-dark text-center">
                <tr>
                    <th>
                        <?php echo xlt('Actions'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Score'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Pid'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Public'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Scope'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Name'); ?>
                    </th>
                    <th>
                        <?php echo xlt('DOB'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Gender'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Email'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Telephone'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Registered'); ?>
                    </th>
                    <th>
                        <?php echo xlt('Address'); ?>
                    </th>
                </tr>
                </thead>
                <tbody class="text-center">
                <?php
                $form_action = $_POST['form_action'] ?? '';
                if ($form_action == 'U') {
                    sqlStatement(
                        "UPDATE patient_data SET dupscore = -1 WHERE pid = ?",
                        array($_POST['form_toppid'])
                    );
                } elseif ($form_action == 'R') {
                    updateDupScore($_POST['form_toppid']);
                }

                $query = "SELECT * FROM patient_data WHERE dupscore > 12 " . "ORDER BY dupscore DESC, pid DESC LIMIT 100";
                $res1 = sqlStatement($query);
                while ($row1 = sqlFetchArray($res1)) {
                    displayRow($row1);
                    $query = "SELECT p2.*, ($score_calculate) AS myscore " .
                        "FROM patient_data AS p1, patient_data AS p2 WHERE " .
                        "p1.pid = ? AND p2.pid < p1.pid AND ($score_calculate) > 12 " .
                        "ORDER BY myscore DESC, p2.pid DESC";
                    $res2 = sqlStatement($query, array($row1['pid']));
                    while ($row2 = sqlFetchArray($res2)) {
                        displayRow($row2, $row1['pid']);
                    }
                }
                ?>
                </tbody>
            </table>
            <input type='hidden' name='form_action' value='' />
            <input type='hidden' name='form_toppid' value='0' />
            <input type='hidden' name='form_rowpid' value='0' />
        </form>
    </div>
    <!-- form used to open a new top level window when a patient row is clicked -->
    <form name='fnew' method='post' target='_blank'
        action='../main/main_screen.php?auth=login&site=<?php echo attr_url($_SESSION['site_id']); ?>'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <input type='hidden' name='patientID' value='0' />
    </form>
</body>
</html>
