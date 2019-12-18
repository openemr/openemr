<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

class Controller_detail extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    function _action_view()
    {
        $ruleId = _get('id');
        $start  = _get('start');
        if ( is_null($ruleId) && ($start) ) {
            $rule = $this->getRuleManager()->newRule();
        } else {
            $rule = $this->getRuleManager()->getRule($ruleId);
        }
        $summary = _post("show");
    
        if (is_null($rule)) {
            $this->redirect("index.php?action=browse!list");
        } elseif ($summary) {
            $this->viewBean->rule = $rule;
            $this->set_view("view_summary.php", "undecorated.php");
        } else {
            $this->viewBean->rule = $rule;
            $this->set_view("view.php");
        }
    }
}
