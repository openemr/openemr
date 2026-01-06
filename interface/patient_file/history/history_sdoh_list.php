<?php

/**
 * SDOH (USCDI v3) SDOH list page (all assessments for a patient)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 *
 * Renders the SDOH assessment list.
 *
 *  This file has been enhanced with assistance from ChatGPT to ensure code quality and maintainability.
 *  All generated code has been reviewed and tested for compliance with project standards.
 */

$srcdir = dirname(__FILE__, 4) . "/library";
require_once("../../globals.php");
require_once($srcdir . "/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

$pid = (int)($_GET['pid'] ?? 0);
if (!$pid) {
    die(xlt("Missing patient id.")); }

if (!AclMain::aclCheckCore('patients', 'med')) {
    die(xlt("Not authorized"));
}

// Pull rows newest first
$res = sqlStatement(
    "SELECT id, assessment_date, screening_tool, assessor, updated_at
       FROM form_history_sdoh
      WHERE pid = ?
   ORDER BY updated_at DESC, id DESC",
    [$pid]
);
?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("SDOH Assessments"); ?></title>
</head>
<body class="body_top">
    <div class="container mt-3">
        <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
            <h4 class="m-0"><?php echo xlt("SDOH Assessments"); ?></h4>
            <a class="btn btn-primary btn-sm"
                href="<?php echo attr($GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh.php?pid=" . urlencode((string) $pid) . "&new=1"); ?>">
                <?php echo xlt("New Assessment"); ?>
            </a>
        </div>

        <table class="table table-striped table-sm">
            <thead>
            <tr>
                <th><?php echo xlt("Assessment Date"); ?></th>
                <th><?php echo xlt("Tool"); ?></th>
                <th><?php echo xlt("Assessor"); ?></th>
                <th><?php echo xlt("Updated"); ?></th>
                <th class="text-right"><?php echo xlt("Action"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php while ($r = sqlFetchArray($res)) : ?>
                <tr>
                    <td><?php echo text($r['assessment_date'] ?: '—'); ?></td>
                    <td><?php echo text($r['screening_tool'] ?: ''); ?></td>
                    <td><?php echo text($r['assessor'] ?: ''); ?></td>
                    <td><?php echo text($r['updated_at'] ?: ''); ?></td>
                    <td class="text-right">
                        <a class="btn btn-sm btn-secondary"
                            href="<?php echo attr($GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh.php?pid=" . urlencode((string) $pid) . "&id=" . (int)$r['id']); ?>">
                            <?php echo xlt("Edit"); ?>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-3">
            <a class="btn btn-link"
                href="<?php echo attr($GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_widget.php?pid=" . urlencode((string) $pid)); ?>">
                &larr; <?php echo xlt("Back to Summary"); ?>
            </a>
        </div>
    </div>
</body>
</html>
