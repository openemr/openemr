<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteriaLifestyle
 *
 * @author aron
 */
class RuleCriteriaLifestyle extends RuleCriteria
{
    var $type;
    var $matchValue;

    function __construct($type, $matchValue)
    {
        $this->type = $type;
        $this->matchValue = $matchValue;
    }

    function getRequirements()
    {
        $requirements = xl("Value") . ": ";
        if (is_null($this->matchValue)) {
            $requirements .= xl("Any");
        } else {
            $requirements .= "'" . $this->matchValue . "'";
        }

        return $requirements;
    }

    function getTitle()
    {
        $label = xl_layout_label($this->getLayoutLabel($this->type, "HIS"));
        return xl("Lifestyle") . " - " . $label;
    }

    function getView()
    {
        return "lifestyle.php";
    }

    function getOptions()
    {
        $stmt = sqlStatement(
            "SELECT lo.field_id, lo.title FROM layout_options AS lo, layout_group_properties AS lp "
            . "WHERE lo.form_id = 'HIS' AND lp.grp_form_id = lo.form_id AND lp.grp_group_id = lo.group_id "
            . "AND lp.grp_title LIKE '%Lifestyle%'"
        );

        $options = array();

        for ($iter = 0; $row = sqlFetchArray($stmt); $iter++) {
            $id = $row['field_id'];
            $label = xl_layout_label($row['title']);
            $option = array( "id" => $id, "label" => $label );
            array_push($options, $option);
        }

        return $options;
    }

    function getDbView()
    {
        $dbView = parent::getDbView();

        $dbView->method = "database";
        $dbView->methodDetail = "";
        $dbView->value = "LIFESTYLE::" . $this->type . "::" . ( is_null($this->matchValue) ? "" : $this->matchValue );
        return $dbView;
    }

    function updateFromRequest()
    {
        parent::updateFromRequest();

        $lifestyle = _post("fld_lifestyle");
        $value = _post("fld_value");
        $matchType = _post("fld_value_type");

        $this->type = $lifestyle;

        if ($matchType == "any") {
            $this->matchValue = null;
        } else {
            $this->matchValue = $value;
        }
    }
}
