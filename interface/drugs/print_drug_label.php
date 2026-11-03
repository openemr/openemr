<?php

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\DrugSalesService;

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/drugs: Dispense Drug", xl("Dispense Drug"));
}

$saleId = (int)(filter_input(INPUT_GET, 'sale_id', FILTER_VALIDATE_INT) ?? 0);
if ($saleId <= 0) {
    die(xlt('Missing sale_id'));
}

// The sale may have been reversed (prescription deleted) or its related
// records may be missing; log the detail and show the user a generic message.
$labelData = null;
try {
    $labelData = (new DrugSalesService())->renderBottleLabel($saleId);
} catch (\RuntimeException $e) {
    ServiceContainer::getLogger()->error('Could not render bottle label', ['sale_id' => $saleId, 'exception' => $e]);
}
if ($labelData === null) {
    die(xlt('No label could be generated for this sale'));
}
$headerText = $labelData['header_text'];
$labelText  = $labelData['label_text'];

?>
<html>
    <script src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js?v=<?php echo attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes')); ?>"></script>
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
