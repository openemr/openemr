<?php

/**
 * This script merges two patient charts into a single patient chart.
 * It is to correct the error of creating a duplicate patient.
 *
 * @category  Patient_Data
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

set_time_limit(0);

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Common\Logging\EventAuditLogger;

$form_pid1 = empty($_GET['pid1']) ? 0 : intval($_GET['pid1']);
$form_pid2 = empty($_GET['pid2']) ? 0 : intval($_GET['pid2']);

// Set this to true for production use.
// If false you will get a "dry run" with no updates.
$PRODUCTION = true;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Merge Patients")]);
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo xlt('Merge Patients'); ?></title>
    <?php Header::setupHeader(); ?>

    <script>

        var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

        var el_pt_name;
        var el_pt_id;

        // This is for callback by the find-patient popup.
        function setpatient(pid, lname, fname, dob) {
            el_pt_name.value = lname + ', ' + fname + ' (' + pid + ')';
            el_pt_id.value = pid;
        }

        // This invokes the find-patient popup.
        function sel_patient(ename, epid) {
            el_pt_name = ename;
            el_pt_id = epid;
            dlgopen('../main/calendar/find_patient_popup.php', '_blank', 600, 400);
        }

    </script>

</head>

<body class="body_top">
    <div class="container">
        <h2><?php echo xlt('Merge Patients') ?></h2>

        <?php

        /**
         * Deletes rows from the given table that are no longer needed due to the merge.
         *
         * @param [type] $tblname    the name of the table to operate on.
         * @param [type] $colname    the column used for the query.
         * @param [type] $source_pid the source patient id.
         * @param [type] $target_pid the target patient id.
         *
         * @return void
         */
        function deleteRows($tblname, $colname, $source_pid, $target_pid)
        {
            global $PRODUCTION;
            $crow = sqlQuery(
                "SELECT COUNT(*) AS count FROM " . escape_table_name($tblname)
                . " WHERE " . escape_sql_column_name($colname, array($tblname)) . " = ?",
                array($source_pid)
            );
            $count = $crow['count'];
            if ($count) {
                $sql = "DELETE FROM " . escape_table_name($tblname) . " WHERE "
                    . escape_sql_column_name($colname, array($tblname)) . " = ?";
                echo "<br />$sql ($count)";
                if ($PRODUCTION) {
                    sqlStatement($sql, array($source_pid));
                    logMergeEvent(
                        $target_pid,
                        "delete",
                        "Deleted rows with " . $colname . " = " . $source_pid . " in table "
                        . $tblname
                    );
                }
            }
        }

        /**
         * Updates rows in the given table, where the given column's value
         * is the source_pid to the given target_pid value.
         *
         * @param [type] $tblname    the name of the table to operate on.
         * @param [type] $colname    the column used for the query.
         * @param [type] $source_pid the source patient id.
         * @param [type] $target_pid the target patient id.
         *
         * @return voidd
         */
        function updateRows($tblname, $colname, $source_pid, $target_pid)
        {
            global $PRODUCTION;
            $crow = sqlQuery(
                "SELECT COUNT(*) AS count FROM " . escape_table_name($tblname)
                . " WHERE " . escape_sql_column_name($colname, array($tblname)) . " = ?",
                array($source_pid)
            );
            $count = $crow['count'];
            if ($count) {
                $sql = "UPDATE " . escape_table_name($tblname) . " SET " .
                    escape_sql_column_name($colname, array($tblname)) . " = ? WHERE " .
                    escape_sql_column_name($colname, array($tblname)) . " = ?";
                echo "<br />$sql ($count)";
                if ($PRODUCTION) {
                    sqlStatement($sql, array($target_pid, $source_pid));
                    logMergeEvent(
                        $target_pid,
                        "update",
                        "Updated rows with " . $colname . " = " . $source_pid . " to " .
                        $target_pid . " in table " . $tblname
                    );
                }
            }
        }

        /**
         * Merge rows by changing the given column of the given table
         * from source_pid to target_pid.
         *
         * @param [type] $tblname    the table to operate on.
         * @param [type] $colname    the column name of the data to change.
         * @param [type] $source_pid the data to be changed from.
         * @param [type] $target_pid the data to be changed to.
         *
         * @return void
         */
        function mergeRows($tblname, $colname, $source_pid, $target_pid)
        {
            global $PRODUCTION;
            $crow = sqlQuery(
                "SELECT COUNT(*) AS count FROM " . escape_table_name($tblname) .
                " WHERE " . escape_sql_column_name($colname, array($tblname)) . " = ?",
                array($source_pid)
            );
            $count = $crow['count'];
            if ($count) {
                echo "<br />lists_touch count is ($count)";
                $source_sel = "SELECT * FROM " . escape_table_name($tblname) .
                    " WHERE `pid` = ?";
                $source_res = sqlStatement($source_sel, array($source_pid));

                $target_sel = "SELECT * FROM " . escape_table_name($tblname) .
                    " WHERE `pid` = ?";
                $target_res = sqlStatement($target_sel, array($target_pid));

                while ($source_row = sqlFetchArray($source_res)) {
                    while ($target_row = sqlFetchArray($target_res)) {
                        if ($source_row['type'] == $target_row['type']) {
                            if (strcmp($source_row['date'], $target_row['date']) < 0) {
                                // we delete the entry from the target since the source has
                                // an older date, then update source to target.
                                $sql1 = "DELETE FROM " . escape_table_name($tblname) .
                                    " WHERE " . escape_sql_column_name($colname, array($tblname))
                                    . " = ? AND `type` = ?";
                                $sql2 = "UPDATE " . escape_table_name($tblname) . " SET " .
                                    escape_sql_column_name($colname, array($tblname)) .
                                    " = ? WHERE " . escape_sql_column_name(
                                        $colname,
                                        array($tblname)
                                    ) . " = ?  AND `type` = ?";
                                echo "<br />$sql1";
                                echo "<br />$sql2";
                                if ($PRODUCTION) {
                                    sqlStatement(
                                        $sql1,
                                        array($target_pid, $source_row['type'])
                                    );
                                    sqlStatement(
                                        $sql2,
                                        array($target_pid, $source_pid,
                                            $source_row['type'])
                                    );
                                    logMergeEvent(
                                        $target_pid,
                                        "delete",
                                        "Deleted rows with " . $colname . " = " . $target_pid
                                        . " and type = " . $source_row['type'] .
                                        " in table " . $tblname
                                    );
                                    logMergeEvent(
                                        $target_pid,
                                        "update",
                                        "Updated rows with " . $colname . " = " . $source_pid
                                        . " to " . $target_pid .
                                        " in table " . $tblname
                                    );
                                }
                            } else {
                                // we just delete the entry from the source.
                                $sql = "DELETE FROM " . escape_table_name($tblname) .
                                    " WHERE " . escape_sql_column_name($colname, array($tblname))
                                    . " = ? AND `type` = ?";
                                echo "<br />$sql";
                                if ($PRODUCTION) {
                                    sqlStatement(
                                        $sql,
                                        array($source_pid, $source_row['type'])
                                    );
                                    logMergeEvent(
                                        $target_pid,
                                        "delete",
                                        "Deleted rows with " . $colname . " = " . $source_pid
                                        . " and type = " . $source_row['type'] .
                                        " in table " . $tblname
                                    );
                                }
                            }
                        }
                    }
                }
                // if there was no target but a source then check count again
                // ^^ should read : Check count again for the case of no target_rows.
                $crow = sqlQuery(
                    "SELECT COUNT(*) AS count FROM " .
                    escape_table_name($tblname) . " WHERE " . escape_sql_column_name(
                        $colname,
                        array($tblname)
                    ) . " = ?",
                    array($source_pid)
                );
                $count = $crow['count'];
                if ($count) {
                    $sql = "UPDATE " . escape_table_name($tblname) . " SET " .
                        escape_sql_column_name(
                            $colname,
                            array($tblname)
                        ) . " = ? WHERE " . escape_sql_column_name(
                            $colname,
                            array($tblname)
                        ) . " = ?";
                    echo "<br />$sql ($count)";
                    if ($PRODUCTION) {
                        sqlStatement($sql, array($target_pid, $source_pid));
                        logMergeEvent(
                            $target_pid,
                            "update",
                            "Updated rows with " . $colname . " = " . $source_pid
                            . " and type = " . $source_row['type'] .
                            " to " . $target_pid . " in table " . $tblname
                        );
                    }
                }
            }
        }

        /**
         * Add a line into the audit log for a merge event.
         *
         * @param [type] $target_pid  the target patient id.
         * @param [type] $event_type  the type of db change (update,delete,etc.)
         * @param [type] $log_message the message to log
         *
         * @return void
         */
        function logMergeEvent($target_pid, $event_type, $log_message)
        {
            EventAuditLogger::instance()->newEvent(
                "patient-merge-" . $event_type,
                $_SESSION['authUser'],
                $_SESSION['authProvider'],
                1,
                $log_message,
                $target_pid
            );
        }

        function resolveTargets($target_pid, $source_pid): array
        {
            // look for an anonymous encounter in a pid target/source set
            // this is where we source components to merge with target encounter.
            $sql = "SELECT e1.date, e1.encounter, e1.reason, e1.encounter_type_code, e1.pid
                    FROM `form_encounter` e1 WHERE e1.pid IN(?,?) AND e1.reason IS NULL AND e1.encounter_type_code IS NULL LIMIT 1";
            $source = sqlQuery($sql, array($target_pid, $source_pid));
            // will we need to deduplicate?
            if (empty($source)) {
                return [];
            }

            $test = ((int)$source['pid'] === (int)$source_pid) ? $source_pid : null;
            $targetPid = $test ? $target_pid : $source_pid;

            $sql = "SELECT e1.date, e1.encounter, e1.reason, e1.encounter_type_code, e1.pid
                    FROM `form_encounter` e1 WHERE e1.pid = ? AND e1.date = ? LIMIT 1";
            $target = sqlQuery($sql, array($targetPid, $source['date']));
            if (empty($target)) {
                // there wasn't a target encounter date match to merge components
                // so grab an encounter that is within the date period of source encounter.
                $src_date = date("Ymd", strtotime($source['date']));
                $sql = "SELECT e1.date, e1.date_end, e1.encounter, e1.reason, e1.encounter_type_code, e1.pid
                    FROM `form_encounter` e1 WHERE e1.pid = ? AND ? BETWEEN e1.date and e1.date_end LIMIT 1";
                $target = sqlQuery($sql, array($targetPid, $src_date));
            }
            return [$target ?? null, $source ?? null];
        }

        /**
         * @param $source_pid
         * @param $target_pid
         * @return void
         */
        function resolveDuplicateEncounters($targets): void
        {
            global $PRODUCTION;

            $target_pid = $targets[0]['pid'];
            $target = $targets[0]['encounter'];
            $source = $targets[1];

            if (!empty($target)) {
                $sql = "SELECT DISTINCT TABLE_NAME as encounter_table, COLUMN_NAME as encounter_column " .
                    "FROM INFORMATION_SCHEMA.COLUMNS " .
                    "WHERE COLUMN_NAME IN('encounter', 'encounter_id') AND TABLE_SCHEMA = ?";
                $res = sqlStatement($sql, array($GLOBALS['adodb']['db']->database));
                while ($tbl = sqlFetchArray($res)) {
                    $tables[] = $tbl;
                }
                foreach ($tables as $table) {
                    if ($table['encounter_table'] === 'form_encounter' || $table['encounter_table'] === 'forms') {
                        continue;
                    }
                    $sql = "UPDATE " . escape_table_name($table['encounter_table']) . " SET " .
                        escape_sql_column_name($table['encounter_column'], array($table['encounter_table'])) . " = ? WHERE " .
                        escape_sql_column_name($table['encounter_column'], array($table['encounter_table'])) . " = ?";

                    if ($PRODUCTION) {
                        sqlStatement($sql, array($target, $source['encounter']));
                        if ($GLOBALS['adodb']['db']->_connectionID->affected_rows) {
                            echo "<br />$sql (" . text($target) . ")" . " : (" . text($source['encounter']) . ")";
                            logMergeEvent(
                                $target_pid,
                                "update",
                                "Updated for duplicate encounters with " .
                                $table['encounter_table'] . '.' . $table['encounter_column'] . " = " . $target
                            );
                        }
                    }
                }
                $sql = "UPDATE " . escape_table_name('forms') . " SET " .
                    escape_sql_column_name('encounter', array('forms')) . " = ? WHERE " .
                    escape_sql_column_name('encounter', array('forms')) . " = ? AND " .
                    escape_sql_column_name('formdir', array('forms')) . " != 'newpatient'";
                sqlStatement($sql, array($target, $source['encounter']));
                if ($PRODUCTION) {
                    $sql = "DELETE FROM `forms` WHERE `encounter` = ? AND `formdir` = 'newpatient'";
                    sqlStatement($sql, array($source['encounter']));
                    $sql = "DELETE FROM `form_encounter` WHERE `encounter` = ?";
                    sqlStatement($sql, array($source['encounter']));
                    if ($GLOBALS['adodb']['db']->_connectionID->affected_rows) {
                        echo "<br />$sql" . text($source['encounter']);
                        logMergeEvent(
                            $target_pid,
                            "delete",
                            "deleted duplicate form encounter " . " = " . $source['encounter'] . " after move."
                        );
                    }
                }
            }
        }

        if (!empty($_POST['form_submit'])) {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
                CsrfUtils::csrfNotVerified();
            }

            $targets = null;
            $target_pid = intval($_POST['form_target_pid']);
            $source_pid = intval($_POST['form_source_pid']);
            echo "<div class='jumbotron jumbotron-fluid m-0 p-0 px-2'>";
            if ($target_pid == $source_pid) {
                die(xlt('Target and source pid may not be the same!'));
            }

            // we want to adjust target and source pid so we can remove the anonymous encounter
            // created by import to house independent components.
            // test if merge or dedupe action is needed.
            if (!empty($_POST['form_submit'])) {
                $targets = resolveTargets($target_pid, $source_pid);
                $_POST['form_submit'] = !empty($targets) ? 'dedupe' : 'merge';
                if ($_POST['form_submit'] == "dedupe" && (empty($targets[0]['pid']) || empty($targets[1]['pid']))) {
                    throw new \RuntimeException("Failed to resolve deduplication target");
                }
                if ($_POST['form_submit'] == "dedupe") {
                    $target_pid = $targets[0]['pid'] ?? null;
                    $source_pid = $targets[1]['pid'] ?? null;
                }
            }

            $tprow = sqlQuery(
                "SELECT * FROM patient_data WHERE pid = ?",
                array($target_pid)
            );
            $sprow = sqlQuery(
                "SELECT * FROM patient_data WHERE pid = ?",
                array($source_pid)
            );

            // Do some checking to make sure source and target exist and are the same person.
            if (empty($tprow['pid'])) {
                die(xlt('Target patient not found'));
            }

            if (empty($sprow['pid'])) {
                die(xlt('Source patient not found'));
            }

            // SSN and DOB checking are skipped if we are coming from the dup manager.
            if (!$form_pid1 || !$form_pid2) {
                if ($tprow['ss'] != $sprow['ss']) {
                    die(xlt('Target and source SSN do not match'));
                }
                if (empty($tprow['DOB']) || $tprow['DOB'] == '0000-00-00') {
                    die(xlt('Target patient has no DOB'));
                }
                if (empty($sprow['DOB']) || $sprow['DOB'] == '0000-00-00') {
                    die(xlt('Source patient has no DOB'));
                }
                if ($tprow['DOB'] != $sprow['DOB']) {
                    die(xlt('Target and source DOB do not match'));
                }
            }

            $tdocdir = "$OE_SITE_DIR/documents/" . check_file_dir_name($target_pid);
            $sdocdir = "$OE_SITE_DIR/documents/" . check_file_dir_name($source_pid);
            $sencdir = "$sdocdir/encounters";
            $tencdir = "$tdocdir/encounters";

            // Change normal documents first as that could fail if CouchDB connection fails.
            $dres = sqlStatement(
                "SELECT * FROM `documents` WHERE `foreign_id` = ?",
                array($source_pid)
            );
            while ($drow = sqlFetchArray($dres)) {
                $d = new Document($drow['id']);
                echo "<br />" . xlt('Changing patient ID for document') . ' '
                    . text($d->get_url_file());
                if ($PRODUCTION) {
                    if (!$d->change_patient($target_pid)) {
                        die("<br />" . xlt('Change failed! CouchDB connect error?'));
                    }
                }
            }

            // Move scanned encounter documents and delete their container.
            if (is_dir($sencdir)) {
                if ($PRODUCTION && !file_exists($tdocdir)) {
                    mkdir($tdocdir);
                }

                if ($PRODUCTION && !file_exists($tencdir)) {
                    mkdir($tencdir);
                }

                $dh = opendir($sencdir);
                if (!$dh) {
                    die(xlt('Cannot read directory') . " '" . text($sencdir) . "'");
                }

                while (false !== ($sfname = readdir($dh))) {
                    if ($sfname == '.' || $sfname == '..') {
                        continue;
                    }

                    if ($sfname == 'index.html') {
                        echo "<br />" . xlt('Deleting') . " " . text($sencdir) . "/"
                            . text($sfname);
                        if ($PRODUCTION) {
                            if (!unlink("$sencdir/$sfname")) {
                                die("<br />" . xlt('Delete failed!'));
                            }
                        }

                        continue;
                    }

                    echo "<br />" . xlt('Moving') . " " . text($sencdir) . "/"
                        . text($sfname) . " " . xlt('to{{Destination}}') . " "
                        . text($tencdir) . "/" . text($sfname);
                    if ($PRODUCTION) {
                        if (!rename("$sencdir/$sfname", "$tencdir/$sfname")) {
                            die("<br />" . xlt('Move failed!'));
                        }
                    }
                }

                closedir($dh);
                echo "<br />" . xlt('Deleting') . " $sencdir";
                if ($PRODUCTION) {
                    if (!rmdir($sencdir)) {
                        echo "<br />" . xlt('Directory delete failed; continuing.');
                    }
                }
            }

            $tres = sqlStatement("SHOW TABLES");
            while ($trow = sqlFetchArray($tres)) {
                $tblname = array_shift($trow);
                if (
                    $tblname == 'patient_data'
                    || $tblname == 'history_data'
                    || $tblname == 'insurance_data'
                ) {
                    deleteRows($tblname, 'pid', $source_pid, $target_pid);
                } elseif ($tblname == 'chart_tracker') {
                    updateRows($tblname, 'ct_pid', $source_pid, $target_pid);
                } elseif ($tblname == 'documents') {
                    // Documents already handled.
                } elseif ($tblname == 'openemr_postcalendar_events') {
                    updateRows($tblname, 'pc_pid', $source_pid, $target_pid);
                } elseif ($tblname == 'lists_touch') {
                    mergeRows($tblname, 'pid', $source_pid, $target_pid);
                } elseif ($tblname == 'log') {
                    // Don't mess with log data.
                } else {
                    $crow = sqlQuery(
                        "SHOW COLUMNS FROM `" . escape_table_name($tblname) . "` WHERE " .
                        "`Field` LIKE 'pid' OR `Field` LIKE 'patient_id'"
                    );
                    if (!empty($crow['Field'])) {
                        $colname = $crow['Field'];
                        updateRows($tblname, $colname, $source_pid, $target_pid);
                        // Note employer_data is included here; its rows are never deleted and the
                        // most recent row for each patient is the one that is normally relevant.
                    }
                }
            }

            // Deduplicate encounters
            // at this point the pids of merge/dedupe action has been merged whereas now
            // we'll resolve the encounters components to the appropriate target encounter.
            if ($_POST['form_submit'] == 'dedupe') {
                if (empty($targets)) {
                    throw new \RuntimeException("Failed to resolve targets for deduplication. Go back.");
                }
                resolveDuplicateEncounters($targets);
            }

            // Recompute dupscore for target patient.
            updateDupScore($target_pid);

            echo "<br />" . xlt('Merge complete.') . "</div>";

            echo "<br />&nbsp;<br />\n";
            echo "<input type='button' class='btn btn-primary' value='" . xla('Go to Duplicate Manager') .
                "' onclick='window.location = \"manage_dup_patients.php\"' />\n";
            echo "</body></html>";

            exit(0);
        }

        $target_string = xl('Click to select');
        $source_string = xl('Click to select');
        $target_pid = '0';
        $source_pid = '0';

        if ($form_pid1) {
            $target_pid = $form_pid1;
            $row = sqlQuery(
                "SELECT lname, fname FROM patient_data WHERE pid = ?",
                array($target_pid)
            );
            $target_string = $row['lname'] . ', ' . $row['fname'] . " ($target_pid)";
        }
        if ($form_pid2) {
            $source_pid = $form_pid2;
            $row = sqlQuery(
                "SELECT lname, fname FROM patient_data WHERE pid = ?",
                array($source_pid)
            );
            $source_string = $row['lname'] . ', ' . $row['fname'] . " ($source_pid)";
        }
        ?>

        <p>
        </p>
        <form method='post' action='merge_patients.php?<?php echo "pid1=" . attr_url($form_pid1) . "&pid2=" . attr_url($form_pid2); ?>'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="table-responsive">
                <table class="table w-100">
                    <tr>
                        <td>
                            <?php echo xlt('Target Patient') ?>
                        </td>
                        <td>
                            <input type='text' class="form-control" size='30' name='form_target_patient'
                                value=' (<?php echo attr($target_string); ?>)'
                                onclick='sel_patient(this, this.form.form_target_pid)'
                                title='<?php echo xla('Click to select patient'); ?>' readonly />
                            <input type='hidden' name='form_target_pid' value='<?php echo attr($target_pid); ?>' />
                        </td>
                        <td>
                            <?php echo xlt('This is the main chart that is to receive the merged data.'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo xlt('Source Patient') ?>
                        </td>
                        <td>
                            <input type='text' class='form-control' size='30' name='form_source_patient'
                                value='<?php echo attr($source_string); ?>'
                                onclick='sel_patient(this, this.form.form_source_pid)'
                                title='<?php echo xla('Click to select patient'); ?>' readonly />
                            <input type='hidden' name='form_source_pid' value='<?php echo attr($source_pid); ?>' />
                        </td>
                        <td>
                            <?php echo xlt('This is the chart that is to be merged into the main chart and then deleted.'); ?>
                        </td>
                    </tr>
                </table>
                <span><button type='submit' class="btn btn-primary" name='form_submit'
                        value='merge'><?php echo xla('Merge'); ?></button></span>
                <span><button type='submit' class="btn btn-primary" name='form_submit'
                        value='dedupe'><?php echo xla('Merge with Encounter Deduplication'); ?></button></span>
            </div>
        </form>
        <div class="jumbotron p-2 m-0 mt-2">
            <p class="font-weight-bold"><?php echo xlt('Be careful with this feature. Back up your database and documents before using it!'); ?></p>
            <?php if (!$PRODUCTION) { ?>
                <p><?php echo xlt('This will be a "dry run" with no physical data updates.'); ?></p>
            <?php } ?>

            <p><?php echo xlt('This will merge two patient charts into one.  It is useful when a patient has been duplicated by mistake.  If that happens often, fix your office procedures - do not run this routinely!'); ?></p>

            <p><?php echo xlt('The first ("target") chart is the one that is considered the most complete and accurate. Demographics, history and insurance sections for this one will be retained.'); ?></p>

            <p><?php echo xlt('The second ("source") chart will have its demographics, history and insurance sections discarded.  Its other data will be merged into the target chart.'); ?></p>

            <?php if (!$form_pid1 || !$form_pid2) { ?>
                <p><?php echo xlt('The merge will not run unless SSN and DOB for the two charts are identical. DOBs cannot be empty.'); ?></p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
