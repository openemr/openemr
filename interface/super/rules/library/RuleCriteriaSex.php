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

/**
 * Description of RuleCriteriaSex
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
        return getListOptionsArray('sex');
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

        $sex = _post("fld_sex");
        $this->value = $sex;
    }
}
