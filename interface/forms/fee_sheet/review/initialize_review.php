<?php 
/**
 * Basic PHP setup for the fee sheet review features
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
if(!$isBilled)
{
require_once("code_check.php");
?>
<script>
    var webroot="<?php echo $web_root;?>";
    var pid=<?php echo $pid;?>;
    var enc=<?php echo $encounter;?>;
    var review_tag="<?php echo xls('Review');?>";
    var justify_click_title="<?php echo xls('Click to choose diagnoses to justify.')?>";
    var fee_sheet_options=[];
    var diag_code_types=<?php echo diag_code_types('json');?>;  // This is a list of diagnosis code types to present for as options in the justify dialog, for now, only "internal codes" included.
</script>
<script type="text/javascript" src="<?php echo $web_root;?>/library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="<?php echo $web_root;?>/library/js/knockout/knockout-2.2.1.js"></script>
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
<script type="text/javascript" src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/initialize_review.js"></script>
<!-- Increment "v=" in the next line if you change fee_sheet_core.js. This makes sure the browser won't use the old cached version. -->
<script type="text/javascript" src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/js/fee_sheet_core.js?v=1"></script>
<script type="text/javascript" src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/fee_sheet_review_view_model.js"></script>
<script type="text/javascript" src="<?php echo $web_root;?>/interface/forms/fee_sheet/review/fee_sheet_justify_view_model.js"></script>


<?php
    // knockoutjs template files
    include_once("views/review.php");
    include_once("views/procedure_select.php");
    include_once("views/justify_display.php");
}
?>
