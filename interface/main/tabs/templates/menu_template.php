<?php

/**
 * menu_template.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

<script type="text/html" id="menu-action">
    <i data-bind="css: icon,text:helperText" class="fa closeButton"></i>
    <div class='menuLabel' data-bind="text:label,click: menuActionClick,css: {menuDisabled: ! enabled()}"></div>

</script>
<script type="text/html" id="menu-header">
    <i data-bind="css: icon" class="fa closeButton"></i>
    <div class="menuSection dropdown">
        <div class='menuLabel dropdown-toggle oe-dropdown-toggle' data-bind="text:label" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></div>
        <ul class="menuEntries dropdown-menu rounded-0 border-0" name="menuEntries" data-bind="foreach: children">
            <li data-bind="template: {name:header ? 'menu-header' : 'menu-action', data: $data }"></li>
        </ul>
    </div>
</script>
<script type="text/html" id="menu-template">
    <div class='appMenu navbar-nav mr-auto' data-bind="foreach: menu">
        <div data-bind="template: {name:header ? 'menu-header' : 'menu-action', data: $data }"></div>
    </div>
</script>
