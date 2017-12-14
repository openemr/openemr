<?php
/**
 * interface/therapy_groups/therapy_groups_views/header.php contains header for all therapy group views.
 *
 * This is the header of all therapy group related views.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */
?>
<!doctype html>

<html lang="">
<head>
    <meta charset="utf-8">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/bootstrap-3-3-4/dist/css/bootstrap.min.css" type="text/css">
    <?php if ($_SESSION['language_direction'] == 'rtl') : ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css" type="text/css">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-ui-1-11-4/themes/base/theme.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/datatables.net-jqui-1-10-13/css/dataTables.jqueryui.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-min-1-9-1/index.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/moment-2-13-0/min/moment.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/datatables.net-1-10-13/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'];?>/jquery-ui-1-12-1/jquery-ui.min.js"></script>
    <script src="<?php echo $GLOBALS['web_root'];?>/library/topdialog.js"></script>
    <script src="<?php echo $GLOBALS['web_root'];?>/library/dialog.js"></script>
    <script>
        <?php require $GLOBALS['srcdir'] . "/formatting_DateToYYYYMMDD_js.js.php" ?>
    </script>
</head>

<body class="body_top therapy_group">


