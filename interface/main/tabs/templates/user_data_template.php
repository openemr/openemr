<?php

/**
 * user_data_template.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

<script type="text/html" id="user-data-template">
    <!-- ko with: user -->
        <div id="username">
                <div class='dropdown' id="username" title="<?php echo xla('Current user') ?>" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <div class="nav-link oemr-navitem">
                    <span data-bind="text:fname"></span>
                    <span data-bind="text:lname"></span>
                  </div>
                <ul id="userdropdown" class="dropdown-menu dropdown-menu-right rounded-0 border-0 py-0 mt-0 menu-shadow-ovr">
                    <li class="dropdown-item oemr-navitem" data-bind="click: editSettings"><?php echo xlt("Settings");?></li>
                    <li class="dropdown-item oemr-navitem" data-bind="click: changePassword"><?php echo xlt("Change Password");?></li>
                    <li class="dropdown-item oemr-navitem" data-bind="click: changeMFA"><?php echo xlt("MFA Management");?></li>
                    <li class="dropdown-item oemr-navitem" data-bind="click: logout"><?php echo xlt("Logout");?></li>
                </ul>
              </div>
        </div>
    <!-- /ko -->
</script>
