<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\RuleTemplateExtension;

require_once(Common::src_dir() . "/clinical_rules.php");

class ControllerBrowse extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    function _action_list()
    {
        $this->set_view("list.php");
    }

    function _action_plans_config()
    {
        $this->set_view("plans_config.php");
    }

    /**
     * @deprecated does not appear to be used
     */
    function _action_getrows()
    {
        $rows = array();

        $rules = resolve_rules_sql('', '0', true);
        foreach ($rules as $rowRule) {
            $title = RuleTemplateExtension::getLabel($rowRule['id'], 'clinical_rules');
            $type = xl("Reminder");

            $row = array(
                "title" => $title,
                "type" => $type,
                "id" => $rowRule['id']
            );
            $rows[] = $row;
        }

        $this->emit_json($rows);
    }
}
