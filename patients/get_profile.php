<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
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
 * @link http://www.open-emr.org 
 *
 */
//namespace OnsitePortal;

require_once ( "verify_session.php" );
require_once ( "./../library/report.inc" );
require_once ( "./../library/options.inc.php" );
require_once ( "./../library/lists.inc" );
require_once ( "./../library/formatting.inc.php" );
require_once ( "./../custom/code_types.inc.php" );
require_once ( "./../library/forms.inc" );
require_once ( "./../library/patient.inc" );

require_once ( "./lib/appsql.class.php" );
require_once ( "./lib/section_fetch.class.php" );
// $fetchsec = new FetchSection ();
$appsql = new ApplicationTable();
$pending = $appsql->getPortalAudit( $pid, 'review' );
$N = 7;
?>

<style>
.insurance .table .bold {
    font-weight: bold;
    font-size: 14px;
}

.insurance .table .text {
    color: red;
}
.demographics .groupname.bold{
    font-size:18px;
    color: blue;
}
.demographics table .bold {
    font-weight:normal;
    font-size:16px;
    color:green;
    padding: 1px;
    border-top: 0;
}
.demographics table .text {
    font-weight: normal;
    font-size: 15px;
    color: red;
}

.demographics .table td {
    padding: 1px;
    border-top: 0;
}


.demographics .panel-heading {
    padding: 5px 8px;
    background: #337ab7;
    color: white;
}
</style>
<body>

<div class='demographics table-responsive' id='DEM'>
    <div class="col-sm-9">

        <?php
                    $result1 = getPatientData( $pid );
                    $result2 = getEmployerData( $pid );
                    ?>
        <div class="panel panel-primary" >
                <header class="panel-heading"><?php echo xlt('Profile Demographics'); ?>
                <?php if( $pending )
                    echo '<button type="button" id="editDems" class="btn btn-danger btn-xs pull-right" style="color:white;font-size:14px">' . xlt('Pending Review') . '</button>';
                else
                    echo '<button type="button" id="editDems" class="btn btn-success btn-xs pull-right" style="color:white;font-size:14px">' . xlt('Revise') . '</button>';
                        ?>
                </header>
                <div class="panel-body " id="dempanel">
                    <table class='table table-responsive table-condensed'>
        <?php
                    display_layout_rows( 'DEM', $result1, $result2 );
                    ?>
                    </table>
                </div>
                <div class="panel-footer"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class='insurance table-condensed table-responsive'>
                <div class="panel panel-primary">
                    <header class="panel-heading"><?php echo xlt('Primary Insurance');?></header>
                    <div class="panel-body">
        <?php
                    printRecDataOne( $insurance_data_array, getRecInsuranceData( $pid, "primary" ), $N );
                    ?>
                    </div>
                </div>
                <div class="panel panel-primary">
                    <header class="panel-heading"><?php echo xlt('Secondary Insurance');?></header>
                    <div class="panel-body">
        <?php
                    printRecDataOne( $insurance_data_array, getRecInsuranceData( $pid, "secondary" ), $N );
                    ?></div>
                </div>
                <div class="panel panel-primary">
                    <header class="panel-heading"><?php echo xlt('Tertiary Insurance');?></header>
                    <div class="panel-body">
        <?php
                    printRecDataOne( $insurance_data_array, getRecInsuranceData( $pid, "tertiary" ), $N );
                    ?></div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <?php
        echo "<div class='immunizations'><h4>" . xlt('Patient Immunization') . '</h4>';
        $result = FetchSection::getImmunizations( $pid );
        foreach( $result as $row ){
            echo text( $row{'administered_formatted'}) . ' : ';
            echo text($row['code_text']) . ' : ';
            echo text($row['note']) . ' : ';
            echo text($row['completion_status']) . '<br>';
        }
        ?>
        </div>

</body>