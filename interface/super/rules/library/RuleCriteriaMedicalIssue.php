<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once(library_src('RuleCriteriaSimpleText.php'));
/**
 * Description of RuleCriteriaMedicalIssue
 *
 * @author aron
 */
class RuleCriteriaMedicalIssue extends RuleCriteriaSimpleText
{
    function __construct($title, $value = '')
    {
        parent::__construct($title, $value);
    }

    function getDbView()
    {
        $dbView = parent::getDbView();

        $dbView->method = "lists";
        $dbView->methodDetail = "medical_problem";
        $dbView->value = "CUSTOM::" . $this->value;
        return $dbView;
    }
}
