<?php
/**
 * Copyright (C) 2014 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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
<script type="text/html" id="code-choice-options">
    &nbsp;
    <div data-bind="foreach:categories">
        <div class="category-display">
            <button data-bind="text:title,click: set_active_category"></button>
        </div>
    </div>
    <!-- ko if: active_category -->
    <div class='active-category' data-bind='visible: show_choices'>
        <div data-bind="template: {name: 'category-options', data: active_category}">
        </div>
    </div>
    <!-- /ko -->
</script>

<script type="text/html" id="category-options">
    <div>
        <div data-bind="text:title"></div>
        <div data-bind="foreach:codes">
            <div class='code-choice'>
                <input type="checkbox" data-bind="checked: selected"/>
                <span data-bind="text:description,click: toggle_code"></span>
            </div>
        </div>
        <div style="clear: both;">
            <button data-bind="click:codes_ok"><?php echo xlt("OK")?></button>
            <button data-bind="click:codes_cancel"><?php echo xlt("Cancel")?></button>
        </div>
    </div>
</script>
