<?php

/**
 * User: sharonco@matrix.co.il
 * Date: 6/15/16
 * This class contains all the interaction with the database that are used by the holidays/clinic closed events
 */
class Holidays_Storage{
    const TABLE_NAME = "calendar_external";
    const CALENDAR_CATEGORY_HOLIDAY = "6";
    const CALENDAR_CATEGORY_CLOSED = "7";

    /**
     * This function selects ALL the holidays from the table calendar_external and returns an array
     * @return array
     */
    public function get_holidays(){
        $holidays= array();

        $sql = sprintf('SELECT * FROM %s', self::TABLE_NAME);
        $res=sqlStatement($sql);
        while ($row = sqlFetchArray($res)) {
            $holidays[] = $row;
        }
        return $holidays;
    }

    /**
     * Selects  holidays/closed clinic events from the table events in a range of dates
     * @param $star_date
     * @param $end_date
     * @return array [0=>"2016/06/16"]
     */
    public static function get_holidays_by_dates($start_date,$end_date){
        $holidays= array();
        $sql = sprintf(
            'SELECT * FROM %s WHERE (pc_catid="%d" OR pc_catid="%d") AND pc_eventDate >= "%s" AND pc_eventDate <= "%s"',
            'openemr_postcalendar_events',self::CALENDAR_CATEGORY_HOLIDAY,self::CALENDAR_CATEGORY_CLOSED,$start_date,$end_date);
        $res=sqlStatement($sql);
        while ($row = sqlFetchArray($res)) {
            $holidays[] = $row['pc_eventDate'];
        }
        return $holidays;

    }

    /**
     * From an array of holidays creates a row that will be inserted as an event to be used in the calendar
     * The holidays array must contai the date=>DD/MM/YYY, description=>"string"
     * @param array $holidays
     */
    public function create_events(array $holidays){
        $deleted = false;
        foreach ($holidays as $holiday){
            if(!$deleted){
                $this->delete_holiday_events();
                $deleted = true;
            }
            $row=array(
                self::CALENDAR_CATEGORY_HOLIDAY,//catgory
                0,//authid
                0,//pid
                $holiday['description'],//title
                $holiday['date'],//date
                86400,//duration all day in seconds
                "a:6:{s:17:\"event_repeat_freq\";s:1:\"0\";s:22:\"event_repeat_freq_type\";s:1:\"0\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";s:6:\"exdate\";s:0:\"\";}",
                1,//allday
                1,//status
                $_SESSION['pc_facility'],//facility
                2 //SHARING_PUBLIC
            );

            $pc_eid = sqlInsert("INSERT INTO openemr_postcalendar_events ( " .
                "pc_catid,  pc_aid, pc_pid, pc_title, pc_time, " .
                "pc_eventDate,  pc_duration, " . "pc_recurrspec,  pc_alldayevent, " . " pc_eventstatus, pc_facility,pc_sharing" .
                ") VALUES (?,?,?,?,NOW(),?,?,?,?,?,?,?)",
                $row
            );
        }
        return true;

    }

    /**
     * This function opend the $file(csv) and parses it to insert the values in the calendar_external so later they can be imported as events
     * csv format -> date,description
     * Example:
     * 2016/12/24,Christmas
     * @param $file (string containing the file name)
     */
    public function import_holidays($file){
        $data = array();
        $handle = fopen($file,"r");
        $deleted = false;
        do {
            if ($data[0]) {
                if(!$deleted){
                    $this->delete_calendar_external();
                    $deleted = true;
                }
                sqlInsert("INSERT INTO ".self::TABLE_NAME ." VALUES
                (   
                    '',
                    '".htmlspecialchars($data[0])."',
                    '".htmlspecialchars($data[1])."',
                    'csv'
                )
            ");
            }
        } while ($data = fgetcsv($handle,1000,",","'"));
        return true;
    }

    private function delete_calendar_external(){
        $sql = sprintf(
            'DELETE FROM %s ',
            self::TABLE_NAME);
        $res=sqlStatement($sql);
    }

    private function delete_holiday_events(){
        $sql = sprintf(
            'DELETE FROM %s WHERE pc_catid="%d"',
            'openemr_postcalendar_events',self::CALENDAR_CATEGORY_HOLIDAY);
        $res=sqlStatement($sql);

    }

}