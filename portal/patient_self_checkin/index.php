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
use OpenEMR\Core\Header;

$ignoreAuth = true;
require_once "../../interface/globals.php";
if (!$GLOBALS['self_checkin_enable'] == 1) {
    die("This feature has not been enabled");
}

?>

<!--Header and Bootstrap inclusion-->
<html>

<head>

    <?php
    Header::setupHeader();;
?>

</head>

<body>
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

<link rel="stylesheet" type="text/css" href="/interface/themes/selfCheckIn.css">

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
</body>
</html>
