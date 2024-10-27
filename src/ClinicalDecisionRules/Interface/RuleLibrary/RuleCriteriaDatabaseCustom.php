<?php

/**
 * interface/super/rules/library/OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaDatabaseCustom.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary;

use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteria;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaDatabaseCustom
 *
 * @author aron
 */
class RuleCriteriaDatabaseCustom extends RuleCriteria
{
    var $table;
    var $column;
    var $valueComparator;
    var $value;
    var $frequencyComparator;
    var $frequency;

    function __construct(
        $table,
        $column,
        $valueComparator,
        $value,
        $frequencyComparator,
        $frequency
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->valueComparator = $valueComparator;
        $this->value = $value;
        $this->frequencyComparator = $frequencyComparator;
        $this->frequency = $frequency;
    }

    function getRequirements()
    {
        $requirements = "";
        if ($this->value) {
            $requirements .= xl("Value") . ": ";
            $requirements .= $this->decodeComparator($this->valueComparator) . " " . $this->value;
            $requirements .= " | ";
        }

        $requirements .= xl("Frequency") . ": ";
        $requirements .= $this->decodeComparator($this->frequencyComparator) . " " . $this->frequency;

        return $requirements;
    }

    function getTitle()
    {
        return xl($this->table) . "." . xl($this->column);
    }

    function getView()
    {
        return "custom.php";
    }

    function getTableNameOptions()
    {
        $options = array();
        $stmts = sqlStatement("SHOW TABLES");
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            foreach ($row as $key => $value) {
                array_push($options, array("id" => $value, "label" => xl($value)));
            }
        }

        return $options;
    }

    function getDbView()
    {
        $dbView = parent::getDbView();

        $dbView->method = "database";
        $dbView->methodDetail = "";
        $dbView->value =
            "::"
            . $this->table . "::" . $this->column . "::"
            . $this->valueComparator . "::" . $this->value . "::"
            . $this->frequencyComparator . "::" . $this->frequency;
        return $dbView;
    }

    function updateFromRequest()
    {
        parent::updateFromRequest();

        $this->table = Common::post("fld_table");
        $this->column = Common::post("fld_column");
        $this->value = Common::post("fld_value");
        $this->valueComparator = Common::post("fld_value_comparator");
        $this->frequency = Common::post("fld_frequency");
        $this->frequencyComparator = Common::post("fld_frequency_comparator");
    }
}
