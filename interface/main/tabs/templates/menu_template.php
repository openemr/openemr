<?php

/**
 * menu_template.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (2) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

<script type="text/html" id="menu-action">
    <div class='dropdown-item oemr-navitem' data-bind="text:label,click: menuActionClick,css: {menuDisabled: ! enabled()}"></div>
</script>

<script type="text/html" id="menu-subaction">
    <div class='dropdown-item oemr-subnavitem' data-bind="text:label,click: menuActionClick,css: {menuDisabled: ! enabled()}"></div>
</script>

<script type="text/html" id="menu-link">
    <div class='nav-link oemr-navitem' data-bind="text:label,click: menuActionClick,css: {menuDisabled: ! enabled()}"></div>
</script>

<script type="text/html" id="menu-subheader">
    <div class="dropdown dropright py-0">
        <div class='dropdown-item oemr-navitem oemr-droptoggle' data-bind="text:label" role="button"></div>
        <ul class="dropdown-menu submenu rounded-0 border-0 py-0 mt-0 menu-shadow-ovr" name="menuEntries" data-bind="foreach: children">
            <li data-bind="template: {name:header ? 'menu-subheader' : 'menu-subaction', data: $data }"></li>
        </ul>
    </div>
</script>

<script type="text/html" id="menu-header">
    <div class="nav-item dropdown py-0">
        <div class='nav-link oemr-navitem' data-bind="text:label" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></div>
        <ul class="dropdown-menu rounded-0 border-0 py-0 mt-0 menu-shadow-ovr" name="menuEntries" data-bind="foreach: children">
            <li data-bind="template: {name:header ? 'menu-subheader' : 'menu-action', data: $data }"></li>
        </ul>
    </div>
</script>

<script type="text/html" id="menu-template">
    <div class='navbar-nav mr-auto' data-bind="foreach: menu">
        <div data-bind="template: {name:header ? 'menu-header' : 'menu-link', data: $data }"></div>
    </div>
</script>
