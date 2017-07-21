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

/**
 *  popup view for events displayed in a new window
 */
$output =& new pnHTML();
$output->SetInputMode(_PNH_VERBATIMINPUT);

// let's load the User API so we can use it :)
if (!pnModAPILoad('postcalendar', 'user')) {
    die('Could not load PostCalendar user API');
}

$eid  = pnVarCleanFromInput('eid');
$date = pnVarCleanFromInput('date');
$output->Text(pnModAPIFunc('postcalendar', 'user', 'eventDetail', array('eid'=>$eid,'date'=>$date)));

$output->Text('</body></html>');
$output->PrintPage();
