<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteriaDatabaseBucketBuilder
 *
 * @author aron
 */
class RuleCriteriaDatabaseBucket extends RuleCriteria
{
    var $category;
    var $item;
    var $categoryLbl;
    var $itemLbl;
    var $completed;
    var $frequencyComparator;
    var $frequency;

    function __construct(
        $category,
        $item,
        $completed,
        $frequencyComparator,
        $frequency
    ) {
        $this->category = $category;
        $this->categoryLbl = $this->getLabel($this->category, 'rule_action_category');
        $this->item = $item;
        $this->itemLbl = $this->getLabel($this->item, 'rule_action');
        $this->completed = $completed;
        $this->frequencyComparator = $frequencyComparator;
        $this->frequency = $frequency;
    }

    function getRequirements()
    {
        $requirements = xl("Completed") . ": ";
        $requirements .= $this->completed ? xl("Yes") : xl("No");
        $requirements .= " | ";
        $requirements .= xl("Frequency") . ": ";
        $requirements .= $this->decodeComparator($this->frequencyComparator) . " "
                       . $this->frequency . " ";
        return $requirements;
    }

    function getTitle()
    {
        return $this->getCategoryLabel() . " - " . $this->getItemLabel();
    }

    function getCategoryLabel()
    {
        return $this->categoryLbl;
    }

    function getItemLabel()
    {
        return $this->itemLbl;
    }

    function getView()
    {
        return "bucket.php";
    }

    function getDbView()
    {
        $dbView = parent::getDbView();

        $dbView->method = "database";
        $dbView->methodDetail = "";
        $dbView->value =
                  "CUSTOM::"
                . $this->category . "::" . $this->item . "::"
                . ($this->completed ? "YES" : "NO") . "::"
                . $this->frequencyComparator . "::" . $this->frequency;

        return $dbView;
    }

    function updateFromRequest()
    {
        parent::updateFromRequest();

        $category = _post("fld_category");
        $categoryLbl = _post("fld_category_lbl");
        $item = _post("fld_item");
        $itemLbl = _post("fld_item_lbl");
        $completed = _post("fld_completed");
        $frequency = _post("fld_frequency");
        $frequencyComparator = _post("fld_frequency_comparator");

        $this->completed = $completed == 'yes';
        $this->frequency = $frequency;
        $this->frequencyComparator = $frequencyComparator;

        // update labels
        // xxx todo abstract this out to a manager (which may or may not defer to core options handling code)!
        // xxx this belongs more in the rule manager
        $dbLbl = getLabel($category, 'rule_action_category');
        if ($category && $dbLbl != $categoryLbl) {
            // update
            sqlStatement("UPDATE list_options SET title = ? WHERE list_id = 'rule_action_category' AND option_id = ?", array(
                $categoryLbl,
                $category ));
        }

        $dbLbl = getLabel($item, 'rule_action');
        if ($item && $dbLbl != $itemLbl) {
            // update
            sqlStatement("UPDATE list_options SET title = ? WHERE list_id = 'rule_action' AND option_id = ?", array(
                $itemLbl,
                $item ));
        }

        $this->category = $category;
        $this->item = $item;
        $this->itemLbl = $itemLbl;
        $this->categoryLbl = $categoryLbl;
    }
}
