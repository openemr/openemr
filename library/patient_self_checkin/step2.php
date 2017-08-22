<?php

use OpenEMR\Core\Header;

$ignoreAuth = true;
require_once "../../interface/globals.php";
if (!$GLOBALS['self_checkin_enable'] == 1) {
    die("This feature has not been enabled");
}

Header::setupHeader();;
?>

<link rel="stylesheet" type="text/css" href="/interface/themes/selfCheckIn.css">

 <!-- // Get month of birth -->


    <h2>Select your month of birth</h2>

    <div class="row">
        <div class="col-sm-3"><a href="step3.php?month=01" class="btn btn-lg btn-default">January</a></div>
        <div class="col-sm-3"><a href="step3.php?month=02" class="btn btn-lg btn-default">February</a></div>
        <div class="col-sm-3"><a href="step3.php?month=03" class="btn btn-lg btn-default">March</a></div>
        <div class="col-sm-3"><a href="step3.php?month=04" class="btn btn-lg btn-default">April</a></div>
    </div>

    <div class="row">
        <div class="col-sm-3"><a href="step3.php?month=05" class="btn btn-lg btn-default">May</a></div>
        <div class="col-sm-3"><a href="step3.php?month=06" class="btn btn-lg btn-default">June</a></div>
        <div class="col-sm-3"><a href="step3.php?month=07" class="btn btn-lg btn-default">July</a></div>
        <div class="col-sm-3"><a href="step3.php?month=08" class="btn btn-lg btn-default">August</a></div>
    </div>

    <div class="row">
        <div class="col-sm-3"><a href="step3.php?month=09" class="btn btn-lg btn-default">September</a></div>
        <div class="col-sm-3"><a href="step3.php?month=10" class="btn btn-lg btn-default">October</a></div>
        <div class="col-sm-3"><a href="step3.php?month=11" class="btn btn-lg btn-default">November</a></div>
        <div class="col-sm-3"><a href="step3.php?month=12" class="btn btn-lg btn-default">December</a></div>
    </div>
