<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */
?>

<script type="text/html" id="user-data-template">
    <!-- ko with: user -->
        <div id="username" class="appMenu">
            <div class="menuSection userSection">
                <div class='menuLabel' id="username" title="<?php echo xla('Current user') ?>">
                    <span data-bind="text:fname"></span>
                    <span data-bind="text:lname"></span>
                </div>
                <ul class="userfunctions menuEntries">
                    <li class="menuLabel" data-bind="click: editSettings"><?php echo xlt("Settings");?></li>
                    <li class="menuLabel" data-bind="click: changePassword"><?php echo xlt("Change Password");?></li>
                    <li class="menuLabel" data-bind="click: logout"><?php echo xlt("Logout");?></li>
                </ul>
            </div>
        </div>
    <!-- /ko -->
</script>
