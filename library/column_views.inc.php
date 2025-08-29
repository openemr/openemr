<?php
/**
 * column_views.inc.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$column_views = array(
    "name" => array(
        "function" => "column_view_name",
        "require" => "lname, fname, mname"
    ),
    "name_first_last" => array(
        "function" => "column_view_name_first_last",
        "require" => "lname, fname"
    ),
    "name_first_last_middle" => array(
        "function" => "column_view_name_first_last_middle",
        "require" => "lname, fname, mname"
    ),
    "city_state" => array(
        "function" => "column_view_city_state",
        "require" => "city, state"
    ),
    "address_full" => array(
        "function" => "column_view_address_full",
        "require" => "street, street_line_2, city, state, postal_code"
    ),
    "address_full_country" => array(
        "function" => "column_view_address_full_country",
        "require" => "street, street_line_2, city, state, postal_code, country_code"
    ),
    "next_appointment" => array(
        "function" => "column_view_next_appointment",
        "require" => ""
    ),
    "last_appointment" => array(
        "function" => "column_view_last_appointment",
        "require" => ""
    ),
    "insurance_primary_provider_name" => array(
        "function" => "column_view_insurance_primary_provider_name",
        "require" => ""
    ),
    "insurance_primary_policy" => array(
        "function" => "column_view_insurance_primary_policy_number",
        "require" => ""
    ),
    "age" => array(
        "function" => "column_view_age",
        "require" => ""
    ),


);

function is_column_view($name) {
    global $column_views;
    return array_key_exists($name, $column_views);
}

function column_views_list() {
    global $column_views;
    $column_view_list = array();
    foreach($column_views as $name => $column_view) {
        $column_view_list[] = attr($name);
    }
    return $column_view_list;
}

function all_column_views($row) {
    global $column_views;
    $column_view_array = array();
    foreach($column_views as $name => $column_view) {
        $column_view_array[$name] = attr($column_view["function"]($row));
    }
    return $column_view_array;
}

function column_view($name, $row) {
    global $column_views;
    return attr($column_views[$name]["function"]($row));
}

function column_view_select_list($name) {
    global $column_views;
    return $column_views[$name]["require"];
}

function column_view_name($row) {
    $name = $row['lname'];
    if ($name && ($row['fname'] || $row['mname'])) {
        $name .= ', ';
    }
    if ($row['fname']) {
        $name .= $row['fname'];
    }
    if ($row['mname'] && $row['fname']) {
        $name .= ' ';
    }
    $name .= $row['mname'];
    return attr($name);
}

function column_view_name_first_last($row) {
    $name_first_last = $row['fname'];
    if ($name_first_last && $row['lname']) {
        $name_first_last .= ' ';
    }
    $name_first_last .= $row['lname'];
    return attr($name_first_last);
}

function column_view_name_first_last_middle($row) {
    $name_first_last_middle = $row['fname'];
    if ($name_first_last_middle && $row['mname']) {
        $name_first_last_middle .= ' ';
    }
    $name_first_last_middle .= $row['mname'];
    if ($name_first_last_middle && $row['lname']) {
        $name_first_last_middle .= ' ';
    }
    $name_first_last_middle .= $row['lname'];
    return attr($name_first_last_middle);
}

function column_view_city_state($row) {
    $city_state = $row['city'];
    if ($city_state && $row['state']) {
        $city_state .= ', ';
    }
    $city_state .= $row['state'];
    return attr($city_state);    
}
//street, street_line_2, city, state, postal_code, country_code
function column_view_address_full($row) {
    $address_full =  $row['street'];
    if ($address_full && $row['street_line_2']) {
        $address_full .= ', ';
    }
    $address_full .= $row['street_line_2'];
    if ($address_full && $row['city']) {
        $address_full .= ', ';
    }
    $address_full .= $row['city']; 
    if ($address_full && $row['state']) {
        $address_full .= ', ';
    }
    $address_full .= $row['state'];
    if ($address_full && $row['postal_code'] && $row['state']) {
        $address_full .= '  ';
    } elseif ($address_full && $row['postal_code']) {
        $address_full .= ', ';        
    }
    $address_full .= $row['postal_code'];
    return attr($address_full);    
}


function column_view_address_full_country($row) {
    $address_full_country = column_view_address_full($row);
    if ($address_full_country && $row['country_code']) {
        $address_full_country .= ', ';
    }
    $address_full_country .= $row['country_code'];    
    return attr($address_full_country);     
}

function column_view_next_appointment($row) {
    $pid = $row['pid'];
    $query = "SELECT
                pc_eventDate, 
                pc_startTime
                FROM `openemr_postcalendar_events` AS e
                WHERE (ADDTIME(CONVERT(pc_eventDate, DATETIME), pc_startTime)) > now() AND pc_pid = ?
                ORDER BY pc_eventDate, pc_startTime ASC
                LIMIT 1";
    $result = sqlStatement($query, array($pid)); 
    $row = sqlFetchArray($result);
    $event_date = $row['pc_eventDate'];
    $event_time = $row['pc_startTime'];
    if ($event_date && $event_time) {
    $next_appointment_datetime  = new DateTime($event_date." ".$event_time);
    $next_appointment = $next_appointment_datetime->format("D, M j, Y, g:i A");
    } else {
    $next_appointment = "";
    } 
    return $next_appointment;
}

function column_view_last_appointment($row) {
    $pid = $row['pid'];
    $query = "SELECT
                pc_eventDate, 
                pc_startTime
                FROM `openemr_postcalendar_events` AS e
                WHERE (ADDTIME(CONVERT(pc_eventDate, DATETIME), pc_startTime)) < now() AND pc_pid = ?
                ORDER BY pc_eventDate, pc_startTime DESC
                LIMIT 1";
    $result = sqlStatement($query, array($pid)); 
    $row = sqlFetchArray($result);
    $event_date = $row['pc_eventDate'];
    $event_time = $row['pc_startTime'];
    $last_appointment_datetime  = new DateTime($event_date." ".$event_time);
    $last_appointment = "";
    $last_appointment = $last_appointment_datetime->format("D, M j, Y, g:i A");
    return $last_appointment;
}

function column_view_insurance_primary_provider_name($row) {

}

function column_view_insurance_primary_policy_number($row) {

    }

function column_view_age($row) {
    $dob = $row['DOB'];
    $age = date_diff(date_create($dob), date_create('now'))->y;
    return $age;
}