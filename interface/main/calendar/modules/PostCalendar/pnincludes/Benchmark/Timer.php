<?php
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Sebastian Bergmann <sb@sebastian-bergmann.de>               |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * Benchmark::Timer
 * 
 * Purpose:
 * 
 *     Timing Script Execution, Generating Profiling Information
 * 
 * Example:
 * 
 *     $timer = new Benchmark_Timer;
 * 
 *     $timer->start();
 *     $timer->setMarker('Marker 1');
 *     $timer->stop();
 * 
 *     $profiling = $timer->getProfiling();
 * 
 * @author   Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @version  $Revision$
 * @access   public
 */

class Benchmark_Timer {
    /**
     * Contains the markers
     *
     * @var    array
     * @access public
     */
    var $markers = array();

    /**
     * Set "Start" marker.
     *
     * @see    setMarker(), stop()
     * @access public
     */
    function start() {
        $this->setMarker('Start');
    }

    /**
     * Set "Stop" marker.
     *
     * @see    setMarker(), start()
     * @access public
     */
    function stop() {
        $this->setMarker('Stop');
    }

    /**
     * Set marker.
     *
     * @param  string  name of the marker to be set
     * @see    start(), stop()
     * @access public
     */
    function setMarker($name) {
        $microtime = explode(' ', microtime());
        $this->markers[$name] = $microtime[1] . substr($microtime[0], 1);
    }

    /**
     * Returns the time elapsed betweens two markers.
     *
     * @param  string  $start        start marker, defaults to "Start"
     * @param  string  $end          end marker, defaults to "Stop"
     * @return double  $time_elapsed time elapsed between $start and $end
     * @access public
     */
    function timeElapsed($start = 'Start', $end = 'Stop') {
        if (extension_loaded('bcmath')) {
            return bcsub($this->markers[$end], $this->markers[$start], 6);
        } else {
            return $this->markers[$end] - $this->markers[$start];
        }
    }

    /**
     * Returns profiling information.
     *
     * $profiling[x]['name']  = name of marker x
     * $profiling[x]['time']  = time index of marker x
     * $profiling[x]['diff']  = execution time from marker x-1 to this marker x
     * $profiling[x]['total'] = total execution time up to marker x
     *
     * @return array $profiling
     * @access public
     */
    function getProfiling() {
        $i = 0;
        $total = 0;
        $result = array();
        
        foreach ($this->markers as $marker => $time) {
            if ($marker == 'Start') {
                $diff = '-';
            } else {
                if (extension_loaded('bcmath')) {
                    $diff  = bcsub($time,  $temp, 6);
                    $total = bcadd($total, $diff, 6);
                } else {
                    $diff  = $time - $temp;
                    $total = $total + $diff;
                }
            }
            
            $result[$i]['name']  = $marker;
            $result[$i]['time']  = $time;
            $result[$i]['diff']  = $diff;
            $result[$i]['total'] = $total;
            
            $temp = $time;
            $i++;
        }

        return $result;
    }
}
?>
