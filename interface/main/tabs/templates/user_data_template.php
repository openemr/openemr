<?php
/**
 * user_data_template.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>

<script type="text/html" id="user-data-template">
    <!-- ko with: user -->
        <div id="username" class="appMenu">
            <div class="menuSection userSection">
                <div class='menuLabel' id="username" title="<?php echo xla('Current user') ?>">
                    <div><i class="fa fa-2x fa-user oe-show" aria-hidden="true" id="user_icon"></i></div>
                    <span data-bind="text:fname"></span>
                    <span data-bind="text:lname"></span>
                </div>
                <ul class="userfunctions menuEntries hideaway">
                    <li class="menuLabel" data-bind="click: editSettings"><?php echo xlt("Settings");?></li>
                    <li class="menuLabel" data-bind="click: changePassword"><?php echo xlt("Change Password");?></li>
                    <li class="menuLabel" data-bind="click: changeMFA"><?php echo xlt("MFA Management");?></li>
                    <li class="menuLabel" data-bind="click: logout"><?php echo xlt("Logout");?></li>
                </ul>
            </div>
        </div>
    <!-- /ko -->
</script>
