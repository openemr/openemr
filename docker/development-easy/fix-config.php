<?php
$p = "/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php";
copy($p, $p . ".before-reinstall");
$s = file_get_contents($p);
$s = preg_replace('/\$config\s*=\s*1\s*;/', '$config = 0;', $s, 1);
file_put_contents($p, $s);
echo preg_match('/\$config\s*=\s*0\s*;/', $s) ? "config is now 0\n" : "config was not changed\n";
?>
