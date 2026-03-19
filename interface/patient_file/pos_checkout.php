<?php

use OpenEMR\Core\OEGlobalsBag;

require_once("../globals.php");

// This script exists because IPPF and Community versions of the
// checkout module are vastly different.

if (OEGlobalsBag::getInstance()->get('ippf_specific')) {
    require('pos_checkout_ippf.php');
} else {
    require('pos_checkout_normal.php');
}
