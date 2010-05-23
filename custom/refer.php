<?php
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../interface/globals.php");
?>
<html>
<head>
<? html_header_show();?>
<title>Referrals</title>
</head>
<body>
<center>
<h2>Referrals</h2>
</center>

<p>This is a placeholder for a script that will initiate a new referral
using a referral management system.  Currently we know of one such system,
<a href='http://www.refercare.org/'>ReferCare.org</a>&#153;, which is a
web-based subscription service.</p>

<p>If you subscribe to ReferCare, then simply replace the file
custom/refer.php in the OpenEMR installation directory with the file
custom/refercare.php, and you will then be able to initiate referrals
from within OpenEMR.</p>

<p>If you wish to use some other referral management system, then replace
custom/refer.php with a suitable interface to that system.</p>

<p>If your practice never initiates referrals, then you may wish to delete
the file custom/refer.php so that the Refer option will no longer appear.</p>

<center>
<form>
<p><input type='button' value='OK' onclick='window.close()' /></p>
</form>
</center>

</body>
</html>
