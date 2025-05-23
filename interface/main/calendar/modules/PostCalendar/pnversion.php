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

$modversion['name']             = 'PostCalendar';
$modversion['id']               = '44';
$modversion['version']          = '4.0.0';
$modversion['description']      = 'PostNuke Calendar Module Modified for OpenEMR';
$modversion['official']         = 0;
$modversion['author']           = 'The PostCalendar Team & Pennington Firm';
$modversion['contact']          = 'http://www.pennfirm.com';
$modversion['admin']            = 1;
$modversion['securityschema']   = array('PostCalendar::Event'    => 'Event Title::Event ID',
                                        'PostCalendar::Category' => 'Category Name::Category ID',
                                        'PostCalendar::Topic'    => 'Topic Name::Topic ID',
                                        'PostCalendar::User'     => 'User Name::User ID',
                                        'PostCalendar::'         => '::');
