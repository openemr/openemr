<?php

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\DrugSalesService;

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/drugs: Dispense Drug", xl("Dispense Drug"));
}

$saleId = (int)($_REQUEST['sale_id'] ?? 0);
if ($saleId <= 0) {
    die(xlt('Missing sale_id'));
}

$labelData  = (new DrugSalesService())->renderBottleLabel($saleId);
$headerText = $labelData['header_text'];
$labelText  = $labelData['label_text'];

?>
<html>
    <script src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<head>
<style>
body {
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
}
.labtop {
    color: #000000;
    font-family: sans-serif;
    font-size: 7pt;
    font-weight: normal;
    text-align: center;
    padding-bottom: 1pt;
}
.labbot {
    color: #000000;
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
    text-align: center;
    padding-top: 2pt;
}
</style>
   <title><?php echo xlt('Prescription Label'); ?></title>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>
<table border='0' cellpadding='0' cellspacing='0' style='width: 200pt'>
 <tr><td class="labtop" nowrap>
        <?php echo nl2br(text($headerText)); ?>
 </td></tr>
 <tr><td style='background-color: #000000; height: 5pt;'></td></tr>
 <tr><td class="labbot" nowrap>
        <?php echo nl2br(text($labelText)); ?>
 </td></tr>
</table>
</center>
<script>
 var win = top.printLogPrint ? top : opener.top;
 win.printLogPrint(window);
</script>
</body>
</html>
