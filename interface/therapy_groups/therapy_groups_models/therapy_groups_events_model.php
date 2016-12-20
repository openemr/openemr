<?php
/**
 * interface/therapy_groups/therapy_groups_models/therapy_groups_events_model.php contains the model for therapy group events.
 *
 * This model fetches the events for the therapy group from the DB.
 *
 * Copyright (C) 2016 Shachar <shachar058@gmail.com>
 * Copyright (C) 2016 Amiel<amielboim@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Shachar Zilbershlag <shachar058@gmail.com>
 * @author  Amiel Elboim <amielboim@gmail.com>
 * @link    http://www.open-emr.org
 */


class Therapy_Groups_Events{

    const TABLE = 'openemr_postcalendar_events';

    /**
     * Get all events of specified group.
     * @param $gid
     * @return ADORecordSet_mysqli
     */
    public function getGroupEvents($gid){

        $appts_to_show = $GLOBALS['number_of_group_appts_to_show'];
        $current_date = date('Y-m-d');
        $events = fetchNextXAppts($current_date, null, $appts_to_show, $gid );
        return $events;
    }



}