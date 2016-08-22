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

<script type="text/html" id="user-data-template">
    <!-- ko with: user -->
        <div id="username">
            <span data-bind="text:fname"></span>
            <span data-bind="text:lname"></span>
            <div class="userfunctions">
                <div data-bind="click: editSettings"><?php echo xlt("Settings");?></div>
                <div data-bind="click: changePassword"><?php echo xlt("Change Password");?></div>
                <div data-bind="click: logout"><?php echo xlt("Logout");?></div>                
            </div>
        </div>
    <!-- /ko -->
</script>
