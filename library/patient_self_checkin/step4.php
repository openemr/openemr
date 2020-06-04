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
$date = htmlspecialchars($_GET['date']);
?>


<h2><?php echo xlt("Select the FIRST letter of your SURNAME"); ?></h2>

<div class="surname">
<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=A&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">A</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=B&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">B</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=C&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">C</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=D&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">D</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=E&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">E</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=F&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">F</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=G&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">G</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=H&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">H</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=I&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">I</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=J&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">J</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=K&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">K</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=L&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">L</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=M&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">M</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=N&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">N</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=O&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">O</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=P&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">P</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=Q&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">Q</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=R&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">R</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=S&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">S</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=T&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">T</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=U&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">U</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=V&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">V</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=W&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">W</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=X&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">X</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=Y&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">Y</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=Z&date=<?php echo $date; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">Z</a></div>
 
</div>
</div>