<?php
/**
 * Batchcom navigation bar.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<nav>
    <ul class="nav nav-tabs nav-justified">
        <?php
        function menu_item($label, $href, $class)
        {
            return "<li class=\"$class\" role=\"presentation\" title=\"$label\"><a href=\"$href\">$label</a></li>";
        }

        if (acl_check('admin', 'batchcom')) {
            echo menu_item(xlt('BatchCom'), "/interface/batchcom/batchcom.php");
        }
        if (acl_check('admin', 'notification')) {
            echo menu_item(xlt('SMS Notification'), "/interface/batchcom/smsnotification.php");
        }
        echo menu_item(xlt("Email Notification"), "/interface/batchcom/emailnotification.php");
        echo menu_item(xlt("SMS/Email Alert Settings"), "/interface/batchcom/settingsnotification.php");
        ?>
    </ul>
</nav>

