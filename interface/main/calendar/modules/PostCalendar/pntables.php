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
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 */
function postcalendar_pntables()
{
    // Initialise table array
    $pntable = array();
	$prefix = pnConfigGetVar('prefix');
    //$prefix = 'Rogue';
	
	$pc_events = $prefix . '_postcalendar_events';
    $pntable['postcalendar_events'] = $pc_events;
    $pntable['postcalendar_events_column'] = array(
        'eid'           => 'pc_eid', 
        'catid'         => 'pc_catid',
        'lid'           => 'pc_lid',       
        'aid'           => 'pc_aid',       
        'title'         => 'pc_title',     
        'time'          => 'pc_time',      
        'hometext'      => 'pc_hometext',   
        'comments'      => 'pc_comments',   
        'counter'       => 'pc_counter',   
        'topic'         => 'pc_topic',     
        'informant'     => 'pc_informant', 
        'eventDate'     => 'pc_eventDate', 
        'duration'      => 'pc_duration',
        'endDate'       => 'pc_endDate',   
        'recurrtype'    => 'pc_recurrtype',
        'recurrspec'    => 'pc_recurrspec',
        'recurrfreq'    => 'pc_recurrfreq', 
        'startTime'     => 'pc_startTime',  
        'endTime'       => 'pc_endTime',
        'alldayevent'   => 'pc_alldayevent',
        'location'      => 'pc_location',
        'conttel'       => 'pc_conttel',  
        'contname'      => 'pc_contname',  
        'contemail'     => 'pc_contemail', 
        'website'       => 'pc_website',  
        'fee'           => 'pc_fee',
        'eventstatus'   => 'pc_eventstatus',
        'sharing'       => 'pc_sharing',
        'language'      => 'pc_language'
        );
    
    // @since version 3.1
    // new category table
    $pc_categories = $prefix . '_postcalendar_categories';   
    $pntable['postcalendar_categories'] = $pc_categories;
    $pntable['postcalendar_categories_column'] = array(
        'catid'         => 'pc_catid',
        'catname'       => 'pc_catname',
        'catcolor'      => 'pc_catcolor',
        'catdesc'       => 'pc_catdesc',
        'recurrtype'	=> 	'pc_recurrtype',
    	'recurrspec'	=>	'pc_recurrspec',
    	'recurrfreq'	=>	'pc_recurrfreq',
    	'duration'		=>	'pc_duration',
    	'limit'			=>	'pc_dailylimit'
        );
    $pc_limit = $prefix . '_postcalendar_limits';
    $pntable['postcalendar_limits'] = $pc_limit;
    $pntable['postcalendar_limits_column'] = array(
    	'limitid'		=>	'pc_limitid',
        'catid'         =>	'pc_catid',
        'starttime'     =>	'pc_starttime',
        'endtime'		=>	'pc_endtime',
        'limit'		    => 	'pc_limit'
        );
	return $pntable;
}
?>
