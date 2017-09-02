<?php
/**
 * knockoutjs template for rendering review of old fee sheets.
 *
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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
<script type="text/html" id="review-display">
    <link rel="stylesheet" href="<?php echo $web_root;?>/interface/forms/fee_sheet/review/views/review.css" type="text/css">
    <div data-bind="visible: $data.show">
    <div data-bind="visible: encounters().length==0"><?php echo xlt("No prior encounters."); ?></div>
    <select data-bind="options:encounters, optionsText: 'date', value: selectedEncounter, event: {change: choose_encounter}, visible: encounters().length>0"></select>
    <div data-bind="visible: procedures().length==0"><?php echo xlt("No procedures in this encounter."); ?></div>
    <table data-bind="visible: $data.procedures().length>0">
        <thead>
            <tr>
                <th colspan='2' class='first_column'><?php echo xlt("Procedures");?></th>
                <th class="price"><?php echo xlt("Price");?></th>
                <th class="modifiers"><?php echo xlt("Modifiers");?></th>
                <th class="units"><?php echo xlt("Units");?></th>
                <th class="justify"><?php echo xlt("Justify");?></th>
            </tr>
        </thead>
        <tbody data-bind="foreach: $data.procedures">
            <tr>
                <td><input type="checkbox" data-bind="checked: selected"/></td>
                <td data-bind="template: {name: 'procedure-select', data: $data}"></td>
                <td><input class="price" type="text" data-bind="value:fee"/></td>
                <td><input type="text" data-bind="value:modifiers,attr: {size: mod_size}"/></td>
                <td><input class="units" type="text" data-bind="value:units"/></td>
                <td data-bind="foreach: justify"><input type="checkbox" data-bind="checked: selected"/><span data-bind="text:code"></span></td>
            </tr>
            
        </tbody>
    </table>
    <div data-bind="visible: issues().length==0"><?php echo xlt("No issues in this encounter."); ?></div>
    <table data-bind="visible: $data.issues().length>0">
        <thead>
            <tr><th colspan='3' class='first_column'><?php echo xlt("Issues");?></th></tr>
        </thead>
        <tbody data-bind="foreach: $data.issues">
            <tr>
                <td><input type="checkbox" data-bind="checked: selected"/></td>
                <td data-bind="text:code"></td>
                <td data-bind="text: description"></td>
            </tr>
        </tbody>
    </table>
    <div>
        <input type="button" data-bind="click: add_review" value="<?php echo xla("Add");?>" />
        <input class="cancel_dialog" type="button" data-bind="click: cancel_review" value="<?php echo xlt("Cancel");?>" />
    </div>
    </div>
</script>
