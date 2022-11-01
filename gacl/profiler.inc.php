<?php
/********************************************************************************\
 * Copyright (C) Carl Taylor (cjtaylor@adepteo.com)                             *
 * Copyright (C) Torben Nehmer (torben@nehmer.net) for Code Cleanup             *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
\********************************************************************************/

/// Enable multiple timers to aid profiling of performance over sections of code
class Profiler {
    var $description;
    var $startTime;
    var $endTime;
    var $initTime;
    var $cur_timer;
    var $stack;
    var $trail;
    var $trace;
    var $count;
    var $running;

    /**
    * Initialise the timer. with the current micro time
    */
    function __construct( $output_enabled=false, $trace_enabled=false)
    {
        $this->description = array();
        $this->startTime = array();
        $this->endTime = array();
        $this->initTime = 0;
        $this->cur_timer = "";
        $this->stack = array();
        $this->trail = "";
        $this->trace = "";
        $this->count = array();
        $this->running = array();
        $this->initTime = $this->getMicroTime();
        $this->output_enabled = $output_enabled;
        $this->trace_enabled = $trace_enabled;
        $this->startTimer('unprofiled');
    }

    // Public Methods

    /**
    *   Start an individual timer
    *   This will pause the running timer and place it on a stack.
    *   @param string $name name of the timer
    *   @param string optional $desc description of the timer
    */
    function startTimer($name, $desc="" ){
        $this->trace.="start   $name\n";
        $n=array_push( $this->stack, $this->cur_timer );
        $this->__suspendTimer( $this->stack[$n-1] );
        $this->startTime[$name] = $this->getMicroTime();
        $this->cur_timer=$name;
        $this->description[$name] = $desc;
        if (!array_key_exists($name,$this->count))
            $this->count[$name] = 1;
        else
            $this->count[$name]++;
    }

    /**
    *   Stop an individual timer
    *   Restart the timer that was running before this one
    *   @param string $name name of the timer
    */
    function stopTimer($name){
        $this->trace.="stop    $name\n";
        $this->endTime[$name] = $this->getMicroTime();
        if (!array_key_exists($name, $this->running))
            $this->running[$name] = $this->elapsedTime($name);
        else
            $this->running[$name] += $this->elapsedTime($name);
        $this->cur_timer=array_pop($this->stack);
        $this->__resumeTimer($this->cur_timer);
    }

    /**
    *   measure the elapsed time of a timer without stoping the timer if
    *   it is still running
    */
    function elapsedTime($name){
        // This shouldn't happen, but it does once.
        if (!array_key_exists($name,$this->startTime))
            return 0;

        if(array_key_exists($name,$this->endTime)){
            return ($this->endTime[$name] - $this->startTime[$name]);
        } else {
            $now=$this->getMicroTime();
            return ($now - $this->startTime[$name]);
        }
    }//end start_time

    /**
    *   Measure the elapsed time since the profile class was initialised
    *
    */
    function elapsedOverall(){
        $oaTime = $this->getMicroTime() - $this->initTime;
        return($oaTime);
    }//end start_time

    /**
    *   print out a log of all the timers that were registered
    *
    */
    function printTimers($enabled=false)
    {
        if($this->output_enabled||$enabled){
            $TimedTotal = 0;
            $tot_perc = 0;
            ksort($this->description);
            print("<pre>\n");
            $oaTime = $this->getMicroTime() - $this->initTime;
            echo"============================================================================\n";
            echo "                              PROFILER OUTPUT\n";
            echo"============================================================================\n";
            print( "Calls                    Time  Routine\n");
            echo"-----------------------------------------------------------------------------\n";
            while (list ($key, $val) = each ($this->description)) {
                $t = $this->elapsedTime($key);
                $total = $this->running[$key];
                $count = $this->count[$key];
                $TimedTotal += $total;
                $perc = ($total/$oaTime)*100;
                $tot_perc+=$perc;
                // $perc=sprintf("%3.2f", $perc );
                printf( "%3d    %3.4f ms (%3.2f %%)  %s\n", $count, $total*1000, $perc, $key);
            }

            echo "\n";

            $missed=$oaTime-$TimedTotal;
            $perc = ($missed/$oaTime)*100;
            $tot_perc+=$perc;
            // $perc=sprintf("%3.2f", $perc );
            printf( "       %3.4f ms (%3.2f %%)  %s\n", $missed*1000,$perc, "Missed");

            echo"============================================================================\n";

            printf( "       %3.4f ms (%3.2f %%)  %s\n", $oaTime*1000,$tot_perc, "OVERALL TIME");

            echo"============================================================================\n";

            print("</pre>");
        }
    }

    function printTrace( $enabled=false )
    {
        if($this->trace_enabled||$enabled){
            print("<pre>");
            print("Trace\n$this->trace\n\n");
            print("</pre>");
        }
    }

    /// Internal Use Only Functions

    /**
    * Get the current time as accuratly as possible
    *
    */
    function getMicroTime(){
        $tmp=explode(" ",microtime());
        $rt=$tmp[0]+$tmp[1];
        return $rt;
    }

    /**
    * resume  an individual timer
    *
    */
    function __resumeTimer($name){
        $this->trace.="resume  $name\n";
        $this->startTime[$name] = $this->getMicroTime();
    }

    /**
    *   suspend  an individual timer
    *
    */
    function __suspendTimer($name){
        $this->trace.="suspend $name\n";
        $this->endTime[$name] = $this->getMicroTime();
        if (!array_key_exists($name, $this->running))
            $this->running[$name] = $this->elapsedTime($name);
        else
            $this->running[$name] += $this->elapsedTime($name);
    }
}

function profiler_start($name) {
    if (array_key_exists("midcom_profiler",$GLOBALS))
      $GLOBALS["midcom_profiler"]->startTimer ($name);
}

function profiler_stop($name) {
    if (array_key_exists("midcom_profiler",$GLOBALS))
      $GLOBALS["midcom_profiler"]->stopTimer ($name);
}

?>
