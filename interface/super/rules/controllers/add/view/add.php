<?php
/**
 * interface/super/rules/controllers/add/view/add.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<table class="header">
  <tr >
        <td class="title"><?php echo xlt('Add Rule'); ?></td>
        <td>
            <a href="index.php?action=add!add" class="iframe_medium css_button" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a>
            <a href="index.php?action=browse!list" class="iframe_medium css_button" onclick="top.restoreSession()"><span><?php echo xlt('Cancel'); ?></span></a>
        </td>
  </tr>
</table>
