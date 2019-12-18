<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

class Controller_delete extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    function _action_delete()
    {
        //ok to delete a rule, we need to delete it from any Plans that use it
        //and delete it from list_options clinical_reminders
        //and what else?
        
        //then show the list again
        $this->viewBean->_redirect = "index.php?action=browse!list";
    }
}
