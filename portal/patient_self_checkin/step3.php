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

require_once "../../interface/globals.php";
require_once $GLOBALS['srcdir'].'/sql.inc';

if ($GLOBALS['self_checkin_enable']==1) {

    // Store variable from previous step
    $sex = htmlspecialchars($_GET['sex']);
    $month = htmlspecialchars($_GET['month']);

?>

<h2>Select your date of birth</h2>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=01&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">1</a></div>
  <div class="col-sm-2"><a href="step4.php?date=02&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">2</a></div>
  <div class="col-sm-2"><a href="step4.php?date=03&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">3</a></div>
  <div class="col-sm-2"><a href="step4.php?date=04&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">4</a></div>
  <div class="col-sm-2"><a href="step4.php?date=05&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">5</a></div>
  <div class="col-sm-2"><a href="step4.php?date=06&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">6</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=07&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">7</a></div>
  <div class="col-sm-2"><a href="step4.php?date=08&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">8</a></div>
  <div class="col-sm-2"><a href="step4.php?date=09&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">9</a></div>
  <div class="col-sm-2"><a href="step4.php?date=10&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">10</a></div>
  <div class="col-sm-2"><a href="step4.php?date=11&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">11</a></div>
  <div class="col-sm-2"><a href="step4.php?date=12&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">12</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=13&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">13</a></div>
  <div class="col-sm-2"><a href="step4.php?date=14&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">14</a></div>
  <div class="col-sm-2"><a href="step4.php?date=15&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">15</a></div>
  <div class="col-sm-2"><a href="step4.php?date=16&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">16</a></div>
  <div class="col-sm-2"><a href="step4.php?date=17&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">17</a></div>
  <div class="col-sm-2"><a href="step4.php?date=18&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">18</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=19&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">19</a></div>
  <div class="col-sm-2"><a href="step4.php?date=20&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">20</a></div>
  <div class="col-sm-2"><a href="step4.php?date=21&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">21</a></div>
  <div class="col-sm-2"><a href="step4.php?date=22&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">22</a></div>
  <div class="col-sm-2"><a href="step4.php?date=23&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">23</a></div>
  <div class="col-sm-2"><a href="step4.php?date=24&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">24</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=25&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">25</a></div>
  <div class="col-sm-2"><a href="step4.php?date=26&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">26</a></div>
  <div class="col-sm-2"><a href="step4.php?date=27&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">27</a></div>
  <div class="col-sm-2"><a href="step4.php?date=28&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">28</a></div>
  <div class="col-sm-2"><a href="step4.php?date=29&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">29</a></div>
  <div class="col-sm-2"><a href="step4.php?date=30&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">30</a></div>
</div>

<div class="row">
  <div class="col-sm-2"><a href="step4.php?date=31&sex=<?php echo $sex; ?>&month=<?php echo $month; ?>" class="btn btn-lg btn-default">31</a></div>
  
</div>

<?php

} //end if

?>
