<?php

require_once("templates/contraception_products.php");

use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$web_root = \OpenEMR\Core\OEGlobalsBag::getInstance()->getWebRoot();
?>

<script type="text/javascript" src='<?php echo CacheUtils::addAssetCacheParamToPath("$web_root/interface/forms/fee_sheet/contraception_products/js/view_model.js"); ?>'></script>
<link rel="stylesheet" href="<?php echo $web_root;?>/interface/forms/fee_sheet/contraception_products/css/contraception_products.css?v=<?php echo attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes')); ?>" type="text/css">
