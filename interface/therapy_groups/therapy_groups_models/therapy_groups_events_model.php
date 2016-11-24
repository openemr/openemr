<?php
/**
 * Created by PhpStorm.
 * User: shaharzi
 * Date: 23/11/16
 * Time: 18:25
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