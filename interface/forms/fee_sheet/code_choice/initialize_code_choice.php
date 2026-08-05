<?php

/**
 * initialize_code_choice.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2014 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("templates/code_choices.php");

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$web_root = \OpenEMR\Core\OEGlobalsBag::getInstance()->getWebRoot();
?>

<script src="<?php echo $web_root;?>/interface/forms/fee_sheet/code_choice/js/view_model.js"></script>
<link rel="stylesheet" href="<?php echo $web_root;?>/interface/forms/fee_sheet/code_choice/css/code_choices.css">
