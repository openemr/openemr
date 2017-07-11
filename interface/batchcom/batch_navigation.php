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

