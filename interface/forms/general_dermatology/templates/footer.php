<?php
/**
 * Footer template for dermatology PDF reports
 */

// Variables expected to be available:
// $website - The facility website
// $phone - The facility phone
// $fax - The facility fax
// $address - The facility address
?>
<table width='100%' style="border-top: 1px solid #cccccc; padding-top: 5px; font-size: 9pt;">
    <tr>
        <td width='33%' style="text-align: left;">{PAGENO}/{nbpg}</td>
        <td width='34%' style="text-align: center;"><?php echo $website; ?></td>
        <td width='33%' style="text-align: right;"><?php echo $phone; ?> <?php echo $fax; ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: center; font-size: 8pt;"><?php echo $address; ?></td>
    </tr>
</table>
