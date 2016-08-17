<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
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
?>
<?php if(isset($_REQUEST['review_id']))
{ ?>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        jQuery("body table:first").hide();
        jQuery(".encounter-summary-column").hide();
        jQuery(".css_button").hide();
        jQuery(".css_button_small").hide();
        jQuery(".encounter-summary-column:first").show();
        jQuery(".title:first").text("<?php echo xls("Review"); ?> " + jQuery(".title:first").text() + " ("+<?php echo addslashes($encounter); ?>+")");
    });
</script>

<?php } ?>
