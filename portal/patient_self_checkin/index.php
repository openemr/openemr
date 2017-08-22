<?php
/**
 * Patient Self Check in
 *
 * This program allows patients to check themselves in using a touchscreen or similar device in the doctors' practice.
 * Doing so, they are marked as arrived (code @) on the calendar module of the clinician's screen and are also marked
 * as present in the Patient Flow Board.
 * The purpose of this program is to free up time for front desk staff.
 *
 * @category  Portal
 * @package   OpenEMR
 * @author    Alfie Carlisle <asc@carlisles.co>
 * @copyright Copyright (c) 2017 Alfie Carlisle <asc@carlisles.co>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      http://www.open-emr.org
 */

$ignoreAuth = true;
require_once "../../interface/globals.php";
require_once $GLOBALS['srcdir'].'/sql.inc';
if (!$GLOBALS['self_checkin_enable'] == 1) {
    die("This feature has not been enabled");
}

?>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
      crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
      integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp"
      crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>

<div id="welcomeText">
<p>
    <img style="text-align: center" width="500px"
        src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/>
</p>

<h1>Welcome to Self Check In</h1>
<h1>Touch your sex to begin</h1>
<br>
<br>
</div>

<style type="text/css">

  #welcomeText {

    text-align: center;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    font-size: 24pt;
  }

  th, td {
    text-align: left;
    padding: 8px;
  }

  tr:nth-child(even){background-color: #f2f2f2}

  .col-sm-6 {

    text-align: center;
  }

  body {
    font-family: sans-serif;
    background-color: #638fd0;

    background: -webkit-radial-gradient(circle, white, #638fd0);
    background: -moz-radial-gradient(circle, white, #638fd0);
  }

</style>

<div class="row">
  <div class="col-sm-6"><a href="step2.php?sex=Male"
                           style="width: 400px;
                           height: 300px;"
                           class="btn btn-lg btn-default">Male
          <br><br>
          <img height="200px" src="male.png">
      </a>
  </div>
  <div class="col-sm-6"><a href="step2.php?sex=Female"
                           style="width: 400px;
                           height: 300px;"
                           class="btn btn-lg btn-default">Female<br><br>
          <img height="200px" src="female.png"></a>
  </div>
</div>

<!--

<table class="tg">
  <tr>
    <th class="tg-yw4l">First Name</th>
    <th class="tg-yw4l">Last Name</th>
    <th class="tg-yw4l">Tap to check in</th>
  </tr>

<br>
<br> -->