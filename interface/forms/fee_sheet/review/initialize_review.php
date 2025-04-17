<?php

/**
 * Basic PHP setup for the fee sheet review features
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;

if (!$isBilled) {
    require_once("code_check.php");
    ?>
<script>
    var webroot = <?php echo js_escape($web_root); ?>;
    var pid = <?php echo js_escape($pid); ?>;
    var enc = <?php echo js_escape($encounter); ?>;
    var review_tag = <?php echo xlj('Review'); ?>;
    var justify_click_title = <?php echo xlj('Click to choose diagnoses to justify.'); ?>;
    var fee_sheet_options = [];
    // This is a list of diagnosis code types to present for as options in the justify dialog,
    // for now, only "internal codes" included.
    var diag_code_types = <?php echo diag_code_types('json');?>;
    var ippf_specific = <?php echo $GLOBALS['ippf_specific'] ? 'true' : 'false'; ?>;
    var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
</script>
<script>
    function fee_sheet_option(code,code_type,description,fee)
    {
        this.code=code;
        this.code_type=code_type;
        this.description=description;
        this.fee=fee;
        return this;
    }
</script>
<script src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/initialize_review.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<script src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/js/fee_sheet_core.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<script src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/fee_sheet_review_view_model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<script src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/fee_sheet_justify_view_model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>

    <?php
    // knockoutjs template files
    include_once("views/review.php");
    include_once("views/procedure_select.php");
    include_once("views/justify_display.php");
}
?>
