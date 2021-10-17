<?php

/**
 *
 * Copyright (C) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
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

$appsql = new ApplicationTable();
$pending = $appsql->getPortalAudit($pid, 'review');
$N = 7;
?>

<style>
    .insurance .table .bold {
        font-weight: normal;
    }

    .insurance .table .text {
        color: var(--danger);
    }

    .demographics .groupname.bold {
        font-size: 1rem;
        color: var(--primary);
    }

    .demographics table .bold {
        font-weight: normal;
        border-top: 0;
    }

    .demographics table .text {
        font-weight: normal;
        color: var(--danger);
    }

    .demographics .table td {
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
                echo '<button type="button" id="editDems" class="btn btn-danger btn-sm float-right text-white">' . xlt('Pending Review') . '</button>';
            } else {
                echo '<button type="button" id="editDems" class="btn btn-success btn-sm float-right text-white">' . xlt('Revise') . '</button>';
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

        $query = "SELECT im.*, cd.code_text, DATE(administered_date) AS administered_date,
            DATE_FORMAT(administered_date,'%m/%d/%Y') AS administered_formatted, lo.title as route_of_administration,
            u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1,
            f.name, f.phone, lo.notes as route_code
            FROM immunizations AS im
            LEFT JOIN codes AS cd ON cd.code = im.cvx_code
            JOIN code_types AS ctype ON ctype.ct_key = 'CVX' AND ctype.ct_id=cd.code_type
            LEFT JOIN list_options AS lo ON lo.list_id = 'drug_route' AND lo.option_id = im.route
            LEFT JOIN users AS u ON u.id = im.administered_by_id
            LEFT JOIN facility AS f ON f.id = u.facility_id
            WHERE im.patient_id=?";
        $result = $appsql->zQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }
        foreach ($records as $row) {
            echo text($row['administered_formatted']) . ' : ';
            echo text($row['code_text']) . ' : ';
            echo text($row['note']) . ' : ';
            echo text($row['completion_status']) . '<br />';
        }
        echo "</div></div>";
        ?>
    </div>

</body>
