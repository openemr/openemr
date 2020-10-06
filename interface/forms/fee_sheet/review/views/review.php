<?php

/**
 * knockoutjs template for rendering review of old fee sheets.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @copyright Copyright (c) 2019 bradymiller <bradymiller@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<script type="text/html" id="review-display">
    <link rel="stylesheet" href="<?php echo $web_root;?>/interface/forms/fee_sheet/review/views/review.css">
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
                <td><input class="price form-control" type="text" data-bind="value:fee" /></td>
                <td><input type="text" data-bind="value:modifiers,attr: {size: mod_size}"/></td>
                <td><input class="units form-control" type="text" data-bind="value:units"/></td>
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
    <div class="btn-group">
        <input type="button" class="btn btn-primary btn-sm" data-bind="click: add_review" value="<?php echo xla("Add");?>" />
        <input type="button" class="cancel_dialog btn btn-secondary btn-sm" data-bind="click: cancel_review" value="<?php echo xla("Cancel");?>" />
    </div>
    </div>
</script>
