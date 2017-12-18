<?php
/**
 * Morphine Report
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once ("../globals.php");
require_once ($GLOBALS['srcdir'].'/patient.inc');
use OpenEMR\Core\Header;

$group = "t";
$reportDataTotalPatients = morphineReport($group);

$blurb1 = xlt('Number of Patients in this group are ');
$blurb2 = xlt("Percentage of patients in this group ");
$buttonLabel = xlt('View Patients'); 
?>

<html>
<head>
    <title>Morphine Report</title>

    <?php Header::setupHeader(); ?>

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css"> 
    <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
	
</head>
<body class="body_top">

<div class="container">
    <h1><?php echo xlt('Morphine Report') ?></h1>
    <h3><?php echo xlt('Report Period: Last 90 days') ?></h3>
    <div>
        <p><strong><?php echo xlt('Total # of active patient today is') ?> <?php echo $reportDataTotalPatients;  ?></strong></p>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <p>Group Over 361</p>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#first"><?php echo $buttonLabel ?></button>
            <div id="first" class="collapse">
                <?php
                $group = "a";
                $patientOver361 = morphineReport($group);
                $a = 1;
                while ($row = sqlFetchArray($patientOver361))
				{
                    $pid = $row['pid'];
                    $name = getPatientName($pid);
                    echo $name . " - " . $row['last'] . "<br>";
                    $a++;
                }
                echo "</div>"; //closing div for the collapse
                echo "<br><br>". $blurb1 . $a . "<br>";
                $percentOver361 = ($a/$reportDataTotalPatients)*100;
                echo $blurb2 . round($percentOver361, 2) ."%";
                ?>
            </div>
        <div class="col-sm-2">
            <p>Group 241-360</p>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#second"><?php echo $buttonLabel ?></button>
            <div id="second" class="collapse">
            <?php
            $group = "b";
            $patientOver241 = morphineReport($group);
            $b = 1;
            while ($row = sqlFetchArray($patientOver241)){
                
                $pid = $row['pid'];
                $name = getPatientName($pid);
                echo $name . " - " . $row['last'] . "<br>";
                $b++;
            }
            echo "</div>"; //closing div for the collapse
            echo "<br><br>". $blurb1 . $b . "<br>";
            $percentOver241 = ($b/$reportDataTotalPatients)*100;
            echo $blurb2 . round($percentOver241, 2) ."%";
            ?>
        </div>
        <div class="col-sm-2">
            <p>Group 181-240</p>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#third"><?php echo $buttonLabel ?></button>
            <div id="third" class="collapse">
            <?php
            $group = "c";
            $patientOver181 = morphineReport($group);
            $c = 1;
            while ($row = sqlFetchArray($patientOver181)){
                
                $pid = $row['pid'];
                $name = getPatientName($pid);
                echo $name . " - " . $row['last'] . "<br>";
                $c++;
            }
            echo "</div>"; //closing div for the collapse
            echo "<br><br>" . $blurb1 . $c . "<br>";
            $percentOver181 = ($c/$reportDataTotalPatients)*100;
            echo $blurb2 . round($percentOver181, 2) ."%";
            ?>
        </div>
        <div class="col-sm-2">
            <p>Group 121-180</p>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#forth"><?php echo $buttonLabel ?></button>
            <div id="forth" class="collapse">
            <?php
            $group = "d";
            $patientOver121 = morphineReport($group);
            $d = 1;
            while ($row = sqlFetchArray($patientOver121)){
                
                $pid = $row['pid'];
                $name = getPatientName($pid);
                echo $name . " - " . $row['last'] . "<br>";
                $d++;
            }
            echo "</div>"; //closing div for the collapse
            echo "<br><br>" . $blurb1  . $d . "<br>";
            $percentOver121 = ($d/$reportDataTotalPatients)*100;
            echo $blurb2 . round($percentOver121, 2) ."%";
            ?>
        </div>
        <div class="col-sm-2">
            <p>Group 1-120</p>
            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#fifth"><?php echo $buttonLabel ?></button>
            <div id="fifth" class="collapse">
            <?php
            $group = "e";
            $patientOver1 = morphineReport($group);
            $e = 1;
            while ($row = sqlFetchArray($patientOver1)){
                
                $pid = $row['pid'];
                $name = getPatientName($pid);
                echo $name . " - " . $row['last'] . "<br>";
                $e++;
            }
            echo "</div>"; //closing div for the collapse
            echo "<br><br>". $blurb1 . $e . "<br>";
            $percentOver1 = ($e/$reportDataTotalPatients)*100;
            echo $blurb2 . round($percentOver1, 2) ."%";
            ?>
        </div>
    </div>

</div>


</body>
</html>
