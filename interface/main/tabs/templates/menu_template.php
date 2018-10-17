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
    <div class="menuSection">
        <div class='menuLabel' data-bind="text:label"></div>
        <ul class="menuEntries" name="menuEntries" data-bind="foreach: children">
           <li data-bind="template: {name:header ? 'menu-header' : 'menu-action', data: $data }"></li>
        </ul>
    </div>
</script>
<script type="text/html" id="menu-template">
    <div>
        <div class='appMenu' data-bind="foreach: menu">
                <span data-bind="template: {name:header ? 'menu-header' : 'menu-action', data: $data }"></span>
        </div>
    </div>
</script>

