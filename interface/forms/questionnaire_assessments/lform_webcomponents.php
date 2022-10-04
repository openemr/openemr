<?php

// change root to public path when ready
$root =  $GLOBALS['webroot'] . "/interface/forms/questionnaire_assessments";

$insert = <<< insert
<link href="$root/lforms/webcomponent/styles.css" media="screen" rel="stylesheet" />
<script src="$root/lforms/webcomponent/assets/lib/zone.min.js"></script>
<script src="$root/lforms/webcomponent/scripts.js"></script>
<script src="$root/lforms/webcomponent/runtime-es2015.js"></script>
<script src="$root/lforms/webcomponent/polyfills-es2015.js"></script>
<script src="$root/lforms/webcomponent/main-es2015.js"></script>
<script src="$root/lforms/fhir/R4/lformsFHIR.min.js"></script>
insert;

echo $insert;
