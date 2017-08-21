<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script>
.col-sm-3 {

    text-align: center;
}
</script>

<?php

require_once("../../interface/globals.php");
require_once($GLOBALS['srcdir'].'/sql.inc');

if ($GLOBALS['self_checkin_enable']==1) {

// Store variable from previous step
$sex = htmlspecialchars($_GET['sex']);?>

<!-- // Get month of birth -->

<h2>Select your month of birth</h2>

<div class="row">
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=01" class="btn btn-lg btn-default">January</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=02" class="btn btn-lg btn-default">February</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=03" class="btn btn-lg btn-default">March</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=04" class="btn btn-lg btn-default">April</a></div>
</div>

<div class="row">
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=05" class="btn btn-lg btn-default">May</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=06" class="btn btn-lg btn-default">June</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=07" class="btn btn-lg btn-default">July</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=08" class="btn btn-lg btn-default">August</a></div>
</div>

<div class="row">
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=09" class="btn btn-lg btn-default">September</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=10" class="btn btn-lg btn-default">October</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=11" class="btn btn-lg btn-default">November</a></div>
  <div class="col-sm-3"><a href="step3.php?sex=<?php echo $sex; ?>&month=12" class="btn btn-lg btn-default">December</a></div>
</div>

<?php

} // end 'if enabled?' if statement

else {


    echo "This feature is not enabled.";

}