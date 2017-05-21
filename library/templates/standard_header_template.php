<?php
/**
 *
 * This is to standardize the header to ease ui standardization for developers.
 *
 * Example code in script:
 *    $include_standard_style_js = array("datetimepicker"); (php command and optional)
 *    require "{$GLOBALS['srcdir']}/templates/standard_header_template.php"; (php command)
 *
 * The $include_standard_style_js supports:
 *                                         jquery-ui (brings in just the js script)
 *                                         jquery-ui-darkness (brings in the darkness style)
 *                                         datetimepicker
 *                                         report_helper.js
 *                                         include_opener.js
 *                                         topdialog.js
 *
 *
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */
?>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
<?php if ($_SESSION['language_direction'] == 'rtl') { ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css">
<?php } ?>
<?php if (!empty($include_standard_style_js) && in_array("jquery-ui-darkness",$include_standard_style_js)) { ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-12-1/themes/ui-darkness/jquery-ui.min.css">
<?php } ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">
<?php if (!empty($include_standard_style_js) && in_array("datetimepicker",$include_standard_style_js)) { ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
<?php } ?>

<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-3-1-1/index.js"></script>
<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
<?php if (!empty($include_standard_style_js) && in_array("jquery-ui",$include_standard_style_js)) { ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-12-1/jquery-ui.min.js"></script>
<?php } ?>
<?php if (!empty($include_standard_style_js) && in_array("datetimepicker",$include_standard_style_js)) { ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>
<?php } ?>
<?php if (!empty($include_standard_style_js) && in_array("report_helper.js",$include_standard_style_js)) { ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/report_helper.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<?php } ?>
<?php if (!empty($include_standard_style_js) && in_array("include_opener.js",$include_standard_style_js)) { ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/main/tabs/js/include_opener.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<?php } ?>
<?php if (!empty($include_standard_style_js) && in_array("topdialog.js",$include_standard_style_js)) { ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/topdialog.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>

<?php $include_standard_style_js = array(); //clear this to prevent issues if this is called again in an embedded script ?>
