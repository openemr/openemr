<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Set the site ID if required.  This must be done before any database
// access is attempted.

if (!empty($_GET['site']))
  $site_id = $_GET['site'];
else if (is_dir("sites/" . $_SERVER['HTTP_HOST']))
  $site_id = $_SERVER['HTTP_HOST'];
else
  $site_id = 'default';

require_once("sites/$site_id/sqlconf.php");
?>
<html>
<?php if ($config == 1) { ?>
<body ONLOAD="javascript:top.location.href='<?php echo "interface/login/login_frame.php?site=$site_id" ?>';">
<?php } else { ?>
<body ONLOAD="javascript:top.location.href='<?php echo "setup.php?site=$site_id" ?>';">     
<?php } ?>
Redirecting...
</body>
</html>
