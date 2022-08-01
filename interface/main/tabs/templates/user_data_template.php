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
        <div id="username" class="appMenu ml-3">
                <div class='menuLabel dropdown' id="username" title="<?php echo xla('Current user') ?>" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa-stack">
                        <i class="far text-muted fa-circle fa-2x fa-stack-2x"></i>
                        <i class="fa text-muted fa-user fa-lg fa-stack-1x" aria-hidden="true" id="user_icon"></i>
                    </span>
                    <ul id="userdropdown" class="userfunctions menuEntries dropdown-menu dropdown-menu-right menu-shadow-ovr rounded-0 border-0">
                        <li class="menuLabel">
                            <span class="font-weight-bold"><span data-bind="text:fname"></span> <span data-bind="text:lname"></span></span>
                        </li>
                        <li class="menuLabel" data-bind="click: editSettings"><i class="fa fa-fw pr-2 text-muted fa-cog"></i>&nbsp;<?php echo xlt("Settings");?></li>
                        <li class="menuLabel" data-bind="click: changePassword"><i class="fa fa-fw pr-2 text-muted fa-lock"></i>&nbsp;<?php echo xlt("Change Password");?></li>
                        <li class="menuLabel" data-bind="click: changeMFA"><i class="fa fa-fw pr-2 text-muted fa-key"></i>&nbsp;<?php echo xlt("MFA Management");?></li>
                        <div class="dropdown-divider"></div>
                        <li class="menuLabel" data-bind="click: function() {navigateTab('./../about_page.php', 'About', function() {activateTabByName('About',true);});}"><i class="fa fa-fw pr-2 text-muted fa-info"></i>&nbsp;<?php echo xlt("About");?> <?php echo text($GLOBALS['openemr_name']); ?></li>
                        <div class="dropdown-divider"></div>
                        <li class="menuLabel" data-bind="click: logout"><i class="fa fa-fw pr-2 text-muted fa-sign-out-alt"></i>&nbsp;<?php echo xlt("Logout");?></li>
                    </ul>
              </div>
        </div>
    <!-- /ko -->
</script>
