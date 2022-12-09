<?php

include_once("../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\CoverageView;

echo \OpenEMR\OemrAd\CoverageView::getEligibilityContent($pid, $dt, $cnt);