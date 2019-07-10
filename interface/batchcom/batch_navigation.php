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
        if (acl_check('admin', 'batchcom')) { ?>
            <li role="presentation" title="<?php echo xla('BatchCom'); ?>">
                <a href="<?php echo $GLOBALS['rootdir']; ?>/batchcom/batchcom.php">
                    <?php echo xlt('BatchCom'); ?>
                </a>
            </li>
            <?php
        }

        if (acl_check('admin', 'notification')) { ?>
            <li role="presentation" title="<?php echo xla('SMS Notification'); ?>">
                <a href="<?php echo $GLOBALS['rootdir']; ?>/batchcom/smsnotification.php">
                    <?php echo xlt('SMS Notification'); ?>
                </a>
            </li>
            <?php
        }
        ?>
        <li role="presentation" title="<?php echo xla('Email Notification'); ?>">
            <a href="<?php echo $GLOBALS['rootdir']; ?>/batchcom/emailnotification.php">
                <?php echo xlt('Email Notification'); ?>
            </a>
        </li>
        <li role="presentation" title="<?php echo xla('SMS/Email Alert Settings'); ?>">
            <a href="<?php echo $GLOBALS['rootdir']; ?>/batchcom/settingsnotification.php">
                <?php echo xlt('SMS/Email Alert Settings'); ?>
            </a>
        </li>
       
    </ul>
</nav>

