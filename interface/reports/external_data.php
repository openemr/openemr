<?php

/**
 * external_data.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once "$srcdir/options.inc.php";

use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;

$records1 = array();
$records2 = array();
?>
<html>
    <head>
        <?php Header::setupHeader();?>
        <title><?php echo xlt('External Data'); ?></title>
        <script><?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?></script>
        <?php
        $arrOeUiSettings = array(
            'heading_title' => xl('External Data'),
            'include_patient_name' => true,
            'expandable' => true,
            'expandable_files' => array("external_data_patient_xpd", "stats_full_patient_xpd", "patient_ledger_patient_xpd"),//all file names need suffix _xpd
            'action' => "",//conceal, reveal, search, reset, link or back
            'action_title' => "",
            'action_href' => "",//only for actions - reset, link or back
            'show_help_icon' => false,
            'help_file_name' => "external_data_dashboard_help.php"
        );
        $oemr_ui = new OemrUI($arrOeUiSettings);
        ?>
    </head>
    <body>
        <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?> mt-3">
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    require_once("$include_root/patient_file/summary/dashboard_header.php")
                    ?>
                </div>
            </div>
            <?php
            $list_id = "external_data"; // to indicate nav item is active, count and give correct id
            // Collect the patient menu then build it
            $menuPatient = new PatientMenuRole();
            $menuPatient->displayHorizNavBarMenu();
            ?>
            <div class="row mt-3">
                <div class="col-sm-12">
                    <ul class="nav nav-pills" id="pill-list" role="tablist">
                        <li class="nav-item" role="presention">
                            <a href="#encounters" id="pills-encounters-tab" class="nav-link active" data-toggle="pill" type="button" role="tab" aria-controls="pills-encounters-tab" aria-selected="true"><?php echo xlt('Encounters'); ?></a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#procedures" id="pills-procedures-tab" class="nav-link" data-toggle="pill" type="button" role="tab" aria-controls="pills-procedures-tab" aria-selected="false"><?php echo xlt('Procedures'); ?></a>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-12 mt-3">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane active" id="encounters" role="tabpanel" aria-labelledby="encounters-tab">
                            <div class="table-responsive">
                                <?php
                                $query1 = "SELECT ee.*,CONCAT_WS(' ',u1.lname, u1.fname) AS provider,u2.organization AS facility
                                    FROM external_encounters AS ee
                                    LEFT JOIN users AS u1 ON u1.id = ee.ee_provider_id
                                    LEFT JOIN users AS u2 ON u2.id = ee.ee_facility_id
                                    WHERE ee.ee_pid = ?";
                                $res1 = sqlStatement($query1, array($pid));
                                while ($row1 = sqlFetchArray($res1)) {
                                    $records1[] = $row1;
                                }
                                ?>

                                <?php if (count($records1) > 0) : ?>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo xlt('Date'); ?></th>
                                                <th><?php echo xlt('Diagnosis'); ?></th>
                                                <th><?php echo xlt('Provider'); ?></th>
                                                <th><?php echo xlt('Facility'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($records1 as $value1) : ?>
                                                <tr>
                                                    <td><?php echo text(oeFormatShortDate($value1['ee_date'])); ?></td>
                                                    <td><?php echo text($value1['ee_encounter_diagnosis']); ?></td>
                                                    <td><?php echo text($value1['provider']); ?></td>
                                                    <td><?php echo text($value1['facility']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p class="text-center">
                                        <?php echo xlt('No External Encounters'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tab-pane" id="procedures" role="tabpanel" aria-labelledby="proccedures-tab">
                            <div class="table-responsive">
                                <?php
                                $query2 = "SELECT ep.*,u.organization AS facility FROM external_procedures AS ep LEFT JOIN users AS u ON u.id = ep.ep_facility_id WHERE ep.ep_pid = ?";
                                $res2 = sqlStatement($query2, array($pid));
                                while ($row2 = sqlFetchArray($res2)) {
                                    $records2[] = $row2;
                                }
                                ?>
                                <?php if (count($records2) > 0) : ?>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo xlt('Date'); ?></th>
                                                <th><?php echo xlt('Code'); ?></th>
                                                <th><?php echo xlt('Code Text'); ?></th>
                                                <th><?php echo xlt('Facility'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($records2 as $value2) : ?>
                                                <tr>
                                                    <td><?php echo text(oeFormatShortDate($value2['ep_date'])); ?></td>
                                                    <td><?php echo text($value2['ep_code_type'] . ':' . $value2['ep_code']); ?></td>
                                                    <td><?php echo text($value2['ep_code_text']); ?></td>
                                                    <td><?php echo text($value2['facility']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p class="text-center">
                                        <?php echo xlt('No External Procedures'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end of container div-->
        <?php $oemr_ui->oeBelowContainerDiv();?>
    </body>
</html>
