<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
	include_once("$srcdir/api.inc");
	require_once("$srcdir/clinical_rules.php");
	require_once("$srcdir/options.js.php");
?>
<html>
	<head>
		<TITLE><?php echo xl('Clinical Reminder'); ?></TITLE>
        <?php Header::setupHeader(['opener']); ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/interface/forms/assessment_form/templates.css" type="text/css">
	</head>
	<body>
		<h3 class="title"><?php xl('Clinical Reminder','e'); ?></h3>
		<div>
			<?php
				$clin_rem_check = resolve_rules_sql('','0',TRUE,'',$_SESSION['authUser']);
				if ( (!empty($clin_rem_check)) && ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) ) {
					clinical_summary_widget($pid,"reminders-all",'','default',$_SESSION['authUser']);
				}
			?>
		</div>
	</body>
</html>
