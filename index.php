<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("interface/globals.php");
?>                                                                                        
<html>
<head></head>

<?php
// include_once("library/sqlconf.php");

if ($config == 1) {
?>                                                                                        
<body ONLOAD="javascript:top.location.href='<?php echo "interface/login/login_frame.php"?>';">
OpenEMR requires Javascript and a GUI browser. We can't promise you                       
anything but try following <a href="interface/login/login.php"                            
>this link</a> to continue.                                                               
<?php                                                                                     
} else {
?>                                                                       
<body ONLOAD="javascript:top.location.href='<?php echo "setup.php"?>';">     
 OpenEMR requires Javascript and a GUI browser. We can't promise you     
anything but try following <a href="setup.php">this link</a> to continue.
<?php                                                                    
}
?>
</body>
</html>
