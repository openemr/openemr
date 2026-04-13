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
    function __construct(
        public string $table,
        public string $column,
        public string $valueComparator,
        public string $value,
        public string $frequencyComparator,
        public string $frequency,
    ) {
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
        return $this->table . "." . $this->column;
    }

    function getView()
    {
        return "custom.php";
    }

    function getTableNameOptions()
    {
        $options = [];
        $stmts = sqlStatement("SHOW TABLES");
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            foreach ($row as $value) {
                $options[] = ["id" => $value, "label" => $value];
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

        $this->table = self::postField("fld_table");
        $this->column = self::postField("fld_column");
        $this->value = self::postField("fld_value");
        $this->valueComparator = self::postField("fld_value_comparator");
        $this->frequency = self::postField("fld_frequency");
        $this->frequencyComparator = self::postField("fld_frequency_comparator");
    }

    /**
     * Read a string field from POST and strip the `::` delimiter used by
     * {@see self::getDbView()} to serialize criteria values. Without this, an
     * input containing `::` would shift field indices when parsed by
     * {@see RuleCriteriaDatabaseBuilder}.
     */
    private static function postField(string $key): string
    {
        return str_replace("::", "", Common::postString($key));
    }
}
