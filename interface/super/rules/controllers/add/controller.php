<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

class Controller_add extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    function _action_add()
    {
        $this->viewBean->_view = "add.php";
    }
}
