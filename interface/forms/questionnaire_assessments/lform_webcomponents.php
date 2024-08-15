<?php

// change root to public path when ready
$root =  $GLOBALS['webroot'] . "/interface/forms/questionnaire_assessments";

$style = "<link href='$root/styles_openemr_lforms.css' media='screen' rel='stylesheet' />" . "\n";
$style_org = "<link href='$root/lforms/webcomponent/styles.css' media='screen' rel='stylesheet' />" . "\n";

$insert = <<< insert
<script src="$root/lforms/webcomponent/assets/lib/zone.min.js"></script>
<script src="$root/lforms/webcomponent/scripts.js"></script>
<script src="$root/lforms/webcomponent/runtime.js"></script>
<script src="$root/lforms/webcomponent/polyfills.js"></script>
<script src="$root/lforms/webcomponent/main.js"></script>
<script src="$root/lforms/fhir/R4/lformsFHIR.min.js"></script>
insert;

$insert = $style . $insert;
echo $insert;
