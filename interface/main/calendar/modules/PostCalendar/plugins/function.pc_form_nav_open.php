<?php

/**
 *  $Id$
 *
 *  PostCalendar::PostNuke Events Calendar Module
 *  Copyright (C) 2002  The PostCalendar Team
 *  http://postcalendar.tv
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *  To read the license please read the docs/license.txt or visit
 *  http://www.gnu.org/copyleft/gpl.html
 *
 */
function smarty_function_pc_form_nav_open($args = array())
{
    extract($args);
    unset($args);
    $viewtype = strtolower(pnVarCleanFromInput('viewtype'));
    if (_SETTING_OPEN_NEW_WINDOW && $viewtype == 'details') {
        $target = 'target="csCalendar"';
    } else {
        $target = '';
    }

    $fstart = '<form action="' . pnModURL(__POSTCALENDAR__, 'user', 'view') . '"'
            . ' method="post"'
            . ' enctype="application/x-www-form-urlencoded" ' . $target . '>';

    echo $fstart;
}
