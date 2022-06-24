<?php

/**
 * field_multi_sorted_list_selector.php contains all of the html for the GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR
 * data type.  The javascript that controls the adding / removing, and sorting of the list items is in the edit_globals.js
 * file.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ListService;
use OpenEMR\Services\Globals\GlobalSetting;

$fldvalue = $fldvalue ?? '';
$globalValue = $globalValue ?? '';
$fldoptions = $fldoptions ?? [];

// can't do anything if we have no list
if (empty($fldoptions[GlobalSetting::DATA_TYPE_OPTION_LIST_ID])) {
    echo "<p>" . xlt("Datatype is missing required key") . ": " . GlobalSetting::DATA_TYPE_OPTION_LIST_ID . "</p>";
    return;
}

$i = $i ?? 0;

if ($userMode) {
    $globalTitle = $globalValue;
}

$listService = new ListService();

$listOptions = $listService->getOptionsByListName($fldoptions[GlobalSetting::DATA_TYPE_OPTION_LIST_ID]);
$listOptionsByOptionId = [];
foreach ($listOptions as $option) {
    $listOptionsByOptionId[$option['option_id']] = $option;
}

// we need to grab all of our options
// selected options
$selectedOptions = [];
if (!empty($fldvalue)) {
    // we have to retain our sort order here
    $fldValueOptions = explode(";", trim($fldvalue));

    foreach ($fldValueOptions as $option) {
        if (isset($listOptionsByOptionId[$option])) {
            $selectedOptions[] = $listOptionsByOptionId[$option];
        }
    }
}
?>
<div class="gbl-field-multi-sorted-list-widget">
    <template class="gbl-field-multi-sorted-list-item-template">
        <li class="gbl-field-multi-sorted-list-item mb-2" data-option-id="">
            <span class="text-label"></span>
            <button class="btn btn-uparrow btn-sm btn-secondary"><i class="fa fa-arrow-up"></i></button>
            <button class="btn btn-downarrow btn-sm btn-secondary"><i class="fa fa-arrow-down"></i></button>
            <button class="btn-delete btn btn-danger btn-sm"></button>
        </li>
    </template>
    <div class="mb-2">
        <input class="gbl-field-multi-sorted-list-value" type="hidden" id='form_<?php echo attr($i); ?>' name="form_<?php echo attr($i); ?>" value="<?php echo attr($fldvalue); ?>" />

        <p class="gbl-field-multi-sorted-list-empty <?php echo empty($selectedOptions) ? "" : "d-none" ?>""><?php echo xlt("No sorted sections selected"); ?></p>
        <ul class="gbl-field-multi-sorted-list-container <?php echo empty($selectedOptions) ? "d-none" : "" ?>">
            <?php foreach ($selectedOptions as $option) : ?>
            <li class="gbl-field-multi-sorted-list-item mb-2" data-option-id="<?php echo attr($option['option_id']); ?>">
                <span class="text-label"><?php echo xlt($option['title']); ?></span>
                <button class="btn btn-uparrow btn-sm btn-secondary"><i class="fa fa-arrow-up"></i></button>
                <button class="btn btn-downarrow btn-sm btn-secondary"><i class="fa fa-arrow-down"></i></button>
                <button class="btn-delete btn btn-danger btn-sm"></button>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <select class="form-control gbl-field-multi-sorted-list-picker">
        <option value=""><?php echo xlt("Select an item to add"); ?></option>
        <?php foreach ($listOptions as $item) : ?>
        <option value="<?php echo attr($item['option_id']); ?>"><?php echo xlt($item['title']); ?></option>
        <?php endforeach; ?>
    </select>
</div>