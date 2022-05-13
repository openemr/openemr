<?php

use OpenEMR\Services\ListService;

$fldvalue = $fldvalue ?? '';
$globalValue = $globalValue ?? '';
$i = $i ?? 0;

if ($userMode) {
    $globalTitle = $globalValue;
}

$listService = new ListService();

$listOptions = $listService->getOptionsByListName('ccda-sections');
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
            $selectedOptions = $listOptionsByOptionId;
        }
    }
}
?>
<script>
    (function(window, oeUI) {

        const WIDGET_NAME = "multiSortedListWidget";

        var widgets = [];

        function Widget(node) {
            this.node = node;

            this.init = function() {
                // using our node go through and setup all of our event listeners
            };
            this.destory = function() {
                // using our node go through and remove all of the event listeners
                // set our references to be null
            };
        }

        function addSelectedListOption(event) {
            let target = event.currentTarget;
            console.log("Selected option is ", target);
            // algorithm is first, check if we already have the element...
                // if we do remove it from its current position and append it to the end of the list

            // if we don't, clone our template and re-init all of our events
        }

        function init() {
            let select = document.querySelector('.gbl-field-multi-sorted-list-picker');
            if (!select) {
                console.error("Failed to find select node in DOM to initialize " + WIDGET_NAME);
                return;
            }
            select.addEventListener('change', addSelectedListOption);
        }
        function destroy() {

        }
        let multiSortedListWidget = {
            init: init
        };
        oeUI.multiSortedListWidget = multiSortedListWidget;
    })(window, window.oeUI || {})
    function initMultiSortedListSelector() {

    }
    window.document.addEventListener("DOMContentLoaded", oeUi.multiSortedListWidget.init);
</script>
<template>
    <li>
        <button class="btn-cancel"></button>
        <button class="btn-m-downarrow"></button>
        <button class="btn-m-uparrow"></button>
    </li>
</template>
<div class="form-control mb-2">
    <input class="gbl-field-multi-sorted-list-value" type="hidden" name="form_<?php echo attr($i); ?> value="<?php echo attr($fldvalue); ?>" />

    <p class="gbl-field-multi-sorted-list-empty <?php echo empty($selectedOptions) ? "" : "d-none" ?>""><?php echo xlt("No sorted sections selected"); ?></p>
    <ul class="gbl-field-multi-sorted-list-container <?php echo empty($selectedOptions) ? "d-none" : "" ?>">
        <?php foreach ($selectedOptions as $option) : ?>
        <li data-option-id="<?php echo attr($option['option_id']); ?>">
            <?php echo xlt($option['title']); ?>
            <button class="btn-cancel"></button>
            <button class="btn-m-downarrow"></button>
            <button class="btn-m-uparrow"></button>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<select class="form-control gbl-field-multi-sorted-list-picker">
    <option value=""><?php echo xlt("Select a section to add"); ?></option>
    <?php foreach ($listOptions as $item) : ?>
    <option value="<?php echo attr($item['option_id']); ?>"><?php echo xlt($item['title']); ?></option>
    <?php endforeach; ?>
</select>