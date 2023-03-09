<?php

include_once("../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\CoverageCheck;

/*Get Provider Id*/
$providerId = CoverageCheck::getProviderId($dt['provider_id'], attr($dt['ins_data_id'.$cnt]), $pid);

/*Get InsuranceData based on insurence copmany details*/
$returnData = CoverageCheck::getInsuranceDataById(attr($dt['pid']), attr($dt['ins_data_id'.$cnt]), $providerId);
?>
<div id="<?php echo 'verification_contaner_'.$cnt; ?>">
	<?php
	if($returnData && is_array($returnData) && count($returnData) > 0) {
		if(!empty($returnData[0]['policy_number'])) {
			
			/*Get Html content on page render*/
			echo CoverageCheck::getHtmlContent($pid, attr($dt['case_id']), $cnt, attr($dt['ins_data_id'.$cnt]), $providerId, $returnData[0]);
		}
	}
	?>
</div>