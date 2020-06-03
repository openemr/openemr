<?php

/**
 *
 * Copyright (C) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
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
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @author Tyler Wrenn <tyler@tylerwrenn.com>
 * @link http://www.open-emr.org
 *
 */

//namespace OnsitePortal;

require_once("verify_session.php");
require_once("./../library/report.inc");
require_once("./../library/options.inc.php");
require_once("./../library/lists.inc");
require_once("./../custom/code_types.inc.php");
require_once("./../library/forms.inc");
require_once("./../library/patient.inc");

require_once("./lib/appsql.class.php");
require_once("./lib/section_fetch.class.php");
// $fetchsec = new FetchSection ();
$appsql = new ApplicationTable();
$pending = $appsql->getPortalAudit($pid, 'review');
$N = 7;
?>

<style>
    .insurance .table .bold {
        font-weight: bold;
        font-size: 14px;
    }

    .insurance .table .text {
        color: var(--danger);
    }

    .demographics .groupname.bold {
        font-size: 18px;
        color: var(--primary);
    }

    .demographics table .bold {
        font-weight: normal;
        font-size: 16px;
        color: var(--success);
        padding: 1px;
        border-top: 0;
    }

    .demographics table .text {
        font-weight: normal;
        font-size: 15px;
        color: var(--danger);
    }

    .demographics .table td {
        padding: 1px;
        border-top: 0;
    }

</style>
<body>

<div class='demographics table-responsive' id='DEM'>

    <?php
                $result1 = getPatientData($pid);
                $result2 = getEmployerData($pid);
    ?>
    <div class="card">
            <header class="card-header border border-bottom-0"><?php echo xlt('Profile Demographics'); ?>
            <?php if ($pending) {
                echo '<button type="button" id="editDems" class="btn btn-danger btn-sm float-right text-white" style="font-size: 14px">' . xlt('Pending Review') . '</button>';
            } else {
                echo '<button type="button" id="editDems" class="btn btn-success btn-sm float-right text-white" style="font-size: 14px">' . xlt('Revise') . '</button>';
            }
            ?>
            </header>
            <div class="card-body border" id="dempanel">
                <table class='table table-responsive table-sm'>
    <?php
                display_layout_rows('DEM', $result1, $result2);
    ?>
                </table>
            </div>
        </div>
    </div>
    <div class='insurance table-sm table-responsive'>
        <div class="card">
            <header class="card-header border border-bottom-0"><?php echo xlt('Primary Insurance');?></header>
            <div class="card-body border">
<?php
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "primary"), $N);
?>
            </div>
        </div>
        <div class="card">
            <header class="card-header border border-bottom-0"><?php echo xlt('Secondary Insurance');?></header>
            <div class="card-body border">
<?php
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "secondary"), $N);
?></div>
        </div>
        <div class="card">
            <header class="card-header border border-bottom-0"><?php echo xlt('Tertiary Insurance');?></header>
            <div class="card-body border">
<?php
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "tertiary"), $N);
?></div>
        </div>
    </div>
    <div>
        <?php
        echo "<div class='card'>";
        echo "<header class='card-header border border-bottom-0 immunizations'>" . xlt('Patient Immunization') . '</header>';
        echo "<div class='card-body border'>";
        $result = FetchSection::getImmunizations($pid);
        foreach ($result as $row) {
            echo text($row['administered_formatted']) . ' : ';
            echo text($row['code_text']) . ' : ';
            echo text($row['note']) . ' : ';
            echo text($row['completion_status']) . '<br />';
        }
        echo "</div></div>";
        ?>
    </div>

</body>
