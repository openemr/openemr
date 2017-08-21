<?php
$ignoreAuth = true;
require_once "../../interface/globals.php";
require_once $GLOBALS['srcdir'].'/sql.inc';

?>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<style>

.btn-lg {

  width: 150px;
  height: 100px;
  margin: 5;
  font-size: 30px;
}

.col-sm-3 {

    text-align: center;
}

h2 { 

text-align: center; }

 body {
    font-family: sans-serif;
    background-color: #638fd0;

    background: -webkit-radial-gradient(circle, white, #638fd0);
    background: -moz-radial-gradient(circle, white, #638fd0);
  }

</style>

<?php
if ($GLOBALS['self_checkin_enable']==1) {
    // Store variable from previous step
    $sex = htmlspecialchars($_GET['sex']);
    $month = htmlspecialchars($_GET['month']);
    $date = htmlspecialchars($_GET['date']);
?>

<h2>Select the FIRST letter of your SURNAME</h2>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=A&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">A</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=B&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">B</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=C&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">C</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=D&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">D</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=E&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">E</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=F&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">F</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=G&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">G</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=H&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">H</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=I&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">I</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=J&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">J</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=K&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">K</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=L&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">L</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=M&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">M</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=N&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">N</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=O&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">O</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=P&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">P</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=Q&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">Q</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=R&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">R</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=S&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">S</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=T&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">T</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=U&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">U</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=V&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">V</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=W&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">W</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=X&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">X</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step5.php?surname=Y&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">Y</a></div>
  <div class="col-sm-2"><a href="step5.php?surname=Z&date=<?php echo $date; ?>&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">Z</a></div>
 
</div>


<?php
} // end if

?>