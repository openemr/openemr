<?php

use OpenEMR\Core\Header;

$ignoreAuth = true;
require_once "../../interface/globals.php";
if (!$GLOBALS['self_checkin_enable'] == 1) {
    die("This feature has not been enabled");
}

Header::setupHeader();

?>

<link rel="stylesheet" type="text/css" href="/interface/themes/selfCheckIn.css">

<?php
// Store variable from previous step
$month = htmlspecialchars($_GET['month']);
?>

<h2><?php echo xlt("Select your date of birth"); ?></h2>

<div class="dates">
<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=01&month=<?php echo $month; ?>" class="btn btn-lg btn-default">1</a></div>
  <div class="col-sm-2"><a href="step4.php?date=02&month=<?php echo $month; ?>" class="btn btn-lg btn-default">2</a></div>
  <div class="col-sm-2"><a href="step4.php?date=03&month=<?php echo $month; ?>" class="btn btn-lg btn-default">3</a></div>
  <div class="col-sm-2"><a href="step4.php?date=04&month=<?php echo $month; ?>" class="btn btn-lg btn-default">4</a></div>
  <div class="col-sm-2"><a href="step4.php?date=05&month=<?php echo $month; ?>" class="btn btn-lg btn-default">5</a></div>
  <div class="col-sm-2"><a href="step4.php?date=06&month=<?php echo $month; ?>" class="btn btn-lg btn-default">6</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=07&month=<?php echo $month; ?>" class="btn btn-lg btn-default">7</a></div>
  <div class="col-sm-2"><a href="step4.php?date=08&month=<?php echo $month; ?>" class="btn btn-lg btn-default">8</a></div>
  <div class="col-sm-2"><a href="step4.php?date=09&month=<?php echo $month; ?>" class="btn btn-lg btn-default">9</a></div>
  <div class="col-sm-2"><a href="step4.php?date=10&month=<?php echo $month; ?>" class="btn btn-lg btn-default">10</a></div>
  <div class="col-sm-2"><a href="step4.php?date=11&month=<?php echo $month; ?>" class="btn btn-lg btn-default">11</a></div>
  <div class="col-sm-2"><a href="step4.php?date=12&month=<?php echo $month; ?>" class="btn btn-lg btn-default">12</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=13&month=<?php echo $month; ?>" class="btn btn-lg btn-default">13</a></div>
  <div class="col-sm-2"><a href="step4.php?date=14&month=<?php echo $month; ?>" class="btn btn-lg btn-default">14</a></div>
  <div class="col-sm-2"><a href="step4.php?date=15&month=<?php echo $month; ?>" class="btn btn-lg btn-default">15</a></div>
  <div class="col-sm-2"><a href="step4.php?date=16&month=<?php echo $month; ?>" class="btn btn-lg btn-default">16</a></div>
  <div class="col-sm-2"><a href="step4.php?date=17&month=<?php echo $month; ?>" class="btn btn-lg btn-default">17</a></div>
  <div class="col-sm-2"><a href="step4.php?date=18&month=<?php echo $month; ?>" class="btn btn-lg btn-default">18</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=19&month=<?php echo $month; ?>" class="btn btn-lg btn-default">19</a></div>
  <div class="col-sm-2"><a href="step4.php?date=20&month=<?php echo $month; ?>" class="btn btn-lg btn-default">20</a></div>
  <div class="col-sm-2"><a href="step4.php?date=21&month=<?php echo $month; ?>" class="btn btn-lg btn-default">21</a></div>
  <div class="col-sm-2"><a href="step4.php?date=22&month=<?php echo $month; ?>" class="btn btn-lg btn-default">22</a></div>
  <div class="col-sm-2"><a href="step4.php?date=23&month=<?php echo $month; ?>" class="btn btn-lg btn-default">23</a></div>
  <div class="col-sm-2"><a href="step4.php?date=24&month=<?php echo $month; ?>" class="btn btn-lg btn-default">24</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=25&month=<?php echo $month; ?>" class="btn btn-lg btn-default">25</a></div>
  <div class="col-sm-2"><a href="step4.php?date=26&month=<?php echo $month; ?>" class="btn btn-lg btn-default">26</a></div>
  <div class="col-sm-2"><a href="step4.php?date=27&month=<?php echo $month; ?>" class="btn btn-lg btn-default">27</a></div>
  <div class="col-sm-2"><a href="step4.php?date=28&month=<?php echo $month; ?>" class="btn btn-lg btn-default">28</a></div>
  <div class="col-sm-2"><a href="step4.php?date=29&month=<?php echo $month; ?>" class="btn btn-lg btn-default">29</a></div>
  <div class="col-sm-2"><a href="step4.php?date=30&month=<?php echo $month; ?>" class="btn btn-lg btn-default">30</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=31&month=<?php echo $month; ?>" class="btn btn-lg btn-default">31</a></div>
</div>
</div>