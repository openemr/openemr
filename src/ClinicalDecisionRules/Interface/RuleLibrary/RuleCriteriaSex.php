<?php

/**
 * interface/super/rules/controllers/edit/helper/common.php
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
use OpenEMR\Services\ListService;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaSex
 *
 * @author aron
 */
class RuleCriteriaSex extends RuleCriteria
{
    var $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    function getRequirements()
    {
        return xl_list_label($this->value);
    }

    function getTitle()
    {
        return xl("Sex");
    }

    function getView()
    {
        return "sex.php";
    }

    function getOptions()
    {
        $listService = new ListService();
        $optionsByListName  = $listService->getOptionsByListName('sex', ['active' => 1]);
        $options = [];
        foreach ($optionsByListName as $row) {
            $options[] = array( "id" => $row['option_id'], "label" => xl_list_label($row['title']) );
        }

        return $options;
    }

    function getDbView()
    {
        $dbView = parent::getDbView();

        $dbView->method = "sex";
        $dbView->methodDetail = "";
        $dbView->value = $this->value;
        return $dbView;
    }

    function updateFromRequest()
    {
        parent::updateFromRequest();

        $sex = Common::post("fld_sex");
        $this->value = $sex;
    }
}
