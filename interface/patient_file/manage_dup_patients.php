<?php

/*
 * This tool helps with identifying and merging duplicate patients.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * Ruth Moulton optionally output a csv file of the list of dubplicate patients
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$first_time = true;
$group = 1;

$session = SessionWrapperFactory::getInstance()->getWrapper();

/**
 * @param $row
 * @param $pid
 * @return void
 */
function displayRow($row, $pid = ''): void
{
    global $first_time, $group;

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
            $group = $group + 1;
            if (empty($_POST['form_csvexport'])) {     //rm - don't put the next line into the csv file
                echo " <tr><td class='detail' colspan='12'>&nbsp;</td></tr>\n";
            }
        }
    }

    $first_time = false;
    $ptname = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
    $phones = [];
    if (trim((string) $row['phone_home'])) {
        $phones[] = trim((string) $row['phone_home']);
    }
    if (trim((string) $row['phone_biz'])) {
        $phones[] = trim((string) $row['phone_biz']);
    }
    if (trim((string) $row['phone_cell'])) {
        $phones[] = trim((string) $row['phone_cell']);
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
  //  if (!empty($_POST['form_csvexport'])) {   // rm out put the line to csv file
   if ($_POST['form_csvexport'] == "CSV" ) {   // rm out put the line to csv file
            echo csvEscape(text($group)) . ",";
            echo csvEscape(text($myscore)) . ",";
            echo csvEscape($row['pid']) . ",";
            echo csvEscape($row['pubpid']) . ",";
            echo csvEscape(text($highlight_text)) . ",";
            echo csvEscape(text($ptname)) . ",";
            // rm - format dates by users preference
            echo csvEscape(oeFormatShortDate(substr($row['DOB'], 0, 10))) . ",";
             echo csvEscape($row['sex']) . ",";
            echo csvEscape($row['email']) . ",";
            echo csvEscape(text($phones)) . ",";
            echo csvEscape(oeFormatShortDate($row['regdate'])) . ",";
         //   echo csvEscape(text($fac_name)) . ',';
             echo csvEscape($row['street']) . "\n";
    } else {  // rm otherwise output the line to the html page
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
    } //rm - end display on page
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
        $scores = [];
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
                [$score, $pid]
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
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'default', $session->getSymfonySession())) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/super: Duplicate Patient Management", xl("Duplicate Patient Management"));
}

$calc_count = calculateScores();
$score_calculate = getDupScoreSQL();
// rm - In the case of CSV export only, a file download will be forced. set up parameters
 if ($_POST['form_csvexport'] == "CSV" ) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    $today = getdate()['year']  . getdate()['mon'] . getdate()['mday'] ;
    $today = text($today);
    $filename = "duplicate_patients" . "_" . $GLOBALS['openemr_name'] . "_" .  $today . ".csv" ;
    header("Content-Disposition: attachment; filename=" . $filename); //rm 'attachment' forces the download
    header("Content-Description: File Transfer");
} else {
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
                const params = new URLSearchParams({
                    pid1: rowpid,
                    pid2: toppid
                });
                window.location = 'merge_patients.php?' + params;
            } else if (select.value == 'MD') {
                const params = new URLSearchParams({
                    pid1: toppid,
                    pid2: rowpid
                });
                window.location = 'merge_patients.php?' + params;
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
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('default', $session->getSymfonySession())); ?>" />
            <div class="btn-sm-group mb-1 text-center">
                <button class="btn btn-sm btn-primary btn-refresh" type='submit' name='form_refresh' value="Refresh"><?php echo xla('ReCalculate Scores') ?></button>
                <button class="btn btn-sm btn-primary btn-print" type='button' value='Print' onclick='window.print()'><?php echo xla('Print'); ?></button>
                <button class="btn btn-sm btn-primary btn-file" type='submit'  name='form_csvexport' value='CSV' ><?php echo xla('Generate a spreadsheet'); ?></button>
            </div>
            <?php } //rm end of html, rather than csv,  setup

            // either put out headings to the csv file or to the page
     if ($_POST['form_csvexport'] == "CSV" ) {
        // CSV column headings
        echo csvEscape(xl('Group')) . ',';
        echo csvEscape(xl('Score')) . ',';
        echo csvEscape(xl('PID')) . ',';
        echo csvEscape(xl('Public')) . ',';
        echo csvEscape(xl('Scope')) . ',';
        echo csvEscape(xl('Name')) . ',';
        echo csvEscape(xl('DOB')) . ',';
    //    echo csvEscape(xl('SSN')) . ',';
          echo csvEscape(xl('Gender')) . ',';
        echo csvEscape(xl('Email')) . ',';
        echo csvEscape(xl('Telephone')) . ',';
        echo csvEscape(xl('Registered')) . ',';
   //     echo csvEscape(xl('Home Facility')) . ',';
        echo csvEscape(xl('Address')) . "\n";
} else {
    ?>
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
                <?php } // rm - end of html column headers
$form_action = $_POST['form_action'] ?? '';
if ($form_action == 'U') {
    sqlStatement(
                        "UPDATE patient_data SET dupscore = -1 WHERE pid = ?",
                        [$_POST['form_toppid']]
                    );
} elseif ($form_action == 'R') {
                    updateDupScore($_POST['form_toppid']);
}

                // Track displayed patients to avoid showing the same patient in multiple groups
                $displayed = [];
                $query = "SELECT * FROM patient_data WHERE dupscore > 12 " . "ORDER BY dupscore DESC, pid DESC LIMIT 100";
                $res1 = sqlStatement($query);
while ($row1 = sqlFetchArray($res1)) {
                    // Skip if this patient was already shown as part of another group
    if (isset($displayed[$row1['pid']])) {
                        continue;
    }
                    // Use symmetric comparison (p2.pid != p1.pid) to find all matches,
                    // not just lower PIDs. This allows detecting duplicates when a patient
                    // with a lower PID is edited to match one with a higher PID.
                    $query = "SELECT p2.*, ($score_calculate) AS myscore " .
                        "FROM patient_data AS p1, patient_data AS p2 WHERE " .
                        "p1.pid = ? AND p2.pid != p1.pid AND p2.dupscore != -1 AND ($score_calculate) > 12 " .
                        "ORDER BY myscore DESC, p2.pid DESC";
                    $res2 = sqlStatement($query, [$row1['pid']]);
                    $matches = [];
    while ($row2 = sqlFetchArray($res2)) {
                        // Skip matches already displayed in a previous group
        if (!isset($displayed[$row2['pid']])) {
                            $matches[] = $row2;
         }
    }
                    // Only display this group if there are actual matches (prevents orphans)
    if (count($matches) > 0) {
                        displayRow($row1);
                        $displayed[$row1['pid']] = true;
        foreach ($matches as $row2) {
                            displayRow($row2, $row1['pid']);
                            $displayed[$row2['pid']] = true;
        }
    }
}
if ($_POST['form_csvexport'] != "CSV") { //rm - only output html if not generating csv file
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
        action='../main/main_screen.php?auth=login&site=<?php echo attr_url($session->get('site_id')); ?>'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('default', $session->getSymfonySession())); ?>" />
        <input type='hidden' name='patientID' value='0' />
    </form>
</body>
</html>
    <?php
}  // rm end of not generating csv
?>
