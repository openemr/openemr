<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * This object maintains a collection of ReminderIntervalDetail for a given rule.
 * Reminder details are derived from the rule_reminder table.
 * @author aron
 *
 */
class ReminderIntervals {

    var $detailMap;

    function __construct() {
        $this->detailMap = array();
    }

    /**
     * Adds a ReminderIntervalDetail to the collection, which is a map
     * @param ReminderIntervalDetail $detail
     */
    function addDetail( $detail ) {
        $details = $this->detailMap[ $detail->intervalType->code ];
        if ( is_null( $details ) ) {
            $details = array();
        }
        array_push( $details, $detail );
        $this->detailMap[ $detail->intervalType->code ] = $details;
    }

    function getTypes() {
        $types = array();
        foreach ( array_keys( $this->detailMap ) as $code )  {
            array_push( $types, ReminderIntervalType::from( $code ) );
        }
        return $types;
    }

    /**
     *
     * @param ReminderIntervalType $type
     * @param ReminderIntervalRange $range
     * @return array
     */
    function getDetailFor( $type, $range = null ) {
        $details = $this->detailMap[ $type->code ];
        if (is_null($range) ) {
            return $details;
        }

        foreach( $details as $detail ) {
            if ( $detail->intervalRange == $range ) {
                return $detail;
            }
        }

        return null;
    }

    function displayDetails( $type ) {
        $details = $this->getDetailFor($type);
        $display = "";
        foreach( $details as $detail ) {
            if ( $display != "" ) {
                $display .= ", ";
            }
            $display .= $detail->display();
        }
        return $display;
    }

}
?>
