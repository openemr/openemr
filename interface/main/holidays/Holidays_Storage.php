<?php
require_once("Holidays_Storage.php");

/**
 * Created by PhpStorm.
 * User: sharonco
 * Date: 6/16/16
 * Time: 12:28 PM
 */


class Holidays_Storage{
    const TABLE_NAME = "calendar_external";
    const CALENDAR_CATEGORY_HOLIDAY = "6";
    const CALENDAR_CATEGORY_CLOSED = "7";

    
    public function get_holidays(){
        $holidays= array();

        $sql = sprintf('SELECT * FROM %s', self::TABLE_NAME);
        $res=sqlStatement($sql);
        while ($row = sqlFetchArray($res)) {
            $holidays[] = $row;
        }
        return $holidays;

    }

    public function create_events(array $holidays){

        foreach ($holidays as $holiday){
            $row=array(
                /*catgory*/self::CALENDAR_CATEGORY_HOLIDAY,/*TODO change me*/
                /*authid*/$_SESSION['authUserID'],
                /*pid*/0,
                /*title*/$holiday['description'],
                /*date*/$holiday['date'],
                /*duration*/86400,
                /*recurrspec*/"a:6:{s:17:\"event_repeat_freq\";s:1:\"0\";s:22:\"event_repeat_freq_type\";s:1:\"0\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";s:6:\"exdate\";s:0:\"\";}",
                /*allday*/1,
                /*status*/1,
                /*facility*/$_SESSION['pc_facility']
            );
            $pc_eid = sqlInsert("INSERT INTO openemr_postcalendar_events ( " .
                "pc_catid,  pc_aid, pc_pid, pc_title, pc_time, " .
                "pc_eventDate,  pc_duration, " . "pc_recurrspec,  pc_alldayevent, " . " pc_eventstatus, pc_facility" .
                ") VALUES (?,?,?,?,NOW(),?,?,?,?,?,?)",
                $row
            );
        }

    }
    

}