<?php
include_once("library/sqlconf.php");

?>

<html>
<head>


</head>
<?php
include_once("library/sqlconf.php");

if ($config == 1) {
?>                                                                                        
<body ONLOAD="javascript:top.location.href='<?echo "interface/login/login_frame.php"?>';">
OpenEMR requires Javascript and a GUI browser. We can't promise you                       
anything but try following <a href="interface/login/login.php"                            
>this link</a> to continue.                                                               
<?php                                                                                     
}                                                                                         
else {
?>                                                                       
<body ONLOAD="javascript:top.location.href='<?echo "setup.php"?>';">     
 OpenEMR requires Javascript and a GUI browser. We can't promise you     
anything but try following <a href="setup.php">this link</a> to continue.
<?php                                                                    
}                                                                        
</body>
</html>
