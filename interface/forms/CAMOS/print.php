<?php

/**
 * CAMOS print.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: CAMOS");
?>
<html>

<head>
    <?php Header::setupHeader(); ?>
</head>

<body class='ml-1'>
    <form method=post action="<?php echo $rootdir;?>/forms/CAMOS/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <h1><?php echo xlt('CAMOS'); ?></h1>
        <hr>
        <input type="submit" name="submit form" value="<?php echo xla('submit form'); ?>" />
        <?php echo "<a href='{$GLOBALS['form_exit_url']}' onclick='top.restoreSession()'>[" . xlt('do not save') . "]</a>"; ?>
        <table>
        </table>
        <h3><?php echo xlt('Computer Aided Medical Ordering System'); ?></h3>
        <table>
            <tr>
                <td><?php echo xlt('category'); ?></td>
                <td><input type="text" name="category" /></td>
            </tr>
            <tr>
                <td><?php echo xlt('subcategory'); ?></td>
                <td><input type="text" name="subcategory" /></td>
            </tr>
            <tr>
                <td><?php echo xlt('item'); ?></td>
                <td><input type="text" name="item" /></td>
            </tr>
            <tr>
                <td><?php echo xlt('content'); ?></td>
                <td><input type="text" name="content" /></td>
            </tr>
        </table><input type="submit" name="submit form" value="submit form" />
        <?php
        echo "<a href='{$GLOBALS['form_exit_url']}' onclick='top.restoreSession()'>[" .
        xlt('do not save') . "]</a>";
        ?>

    </form>
    <?php
    formFooter();
