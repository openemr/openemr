<?php

/**
 * edih_segments.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2016 Kevin McCormick Longview, Texas
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


/**
 * increment loop values ($lpval is a reference)
 *
 * @param $lptest   the prospective loop value
 * @param &$lpval    the present loop value -- reassigned here
 * @return integer  value from strcmp()
 */
function edih_change_loop($lptest, &$lpval)
{
    // strcmp($str1,$str2) Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
    if (strcmp($lptest, $lpval) > 0) {
        //echo "$lptest greater than $lpval" .PHP_EOL;
        $lpval = $lptest;
    }

    return strcmp($lptest, $lpval);
}

/**
 * format segments for display of x12 edi files
 *
 * @param array  $segments
 * @param string  $delimiter
 * return string
 */
function edih_segments_text($segments, $delimiter)
{
    //
    $str_html = '';
    //
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        // debug
        csv_edihist_log('edih_generic_text: invalid argument');
        $str_html = "Invalid arguments for view of x12 file text<br />";
        return $str_html;
    }

    //
    $de = $delimiter;
    $loopid = " -- ";
    $idx = 0;
    //
    foreach ($segments as $key => $seg) {
        $idx++;
        //
        $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
    }

    //
    return $str_html;
}

/**
 * identify loops and format segments for display
 * of 837 (professional claim) type x12 edi files
 *
 * @param array  $segments
 * @param string  $delimiter
 * return string
 */
function edih_837_text($segments, $delimiter, $err_seg = '')
{
    //
    $str_html = '';
    $err_ar = array();
    //
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        // debug
        csv_edihist_log('edih_837_text: invalid argument');
        $str_html .= "Invalid arguments for view of x12 file text<br />";
        return $str_html;
    }

    // to highlight identified errors listed in 999/997 ack
    if ($err_seg) {
        $er = edih_errseg_parse($err_seg);
        $erstn = (isset($er['trace'])) ? substr($er['trace'], -4) : '';
        $erseg = (isset($er['err'])) ? $er['err'] : array();
    } else {
        $erstn = '';
        $erseg = array();
    }

    //
    $de = $delimiter;
    $loopid = "0";
    $idx = 0;
    $stkey = 0;
    $segnum = 0;
    $stsegct = 0;
    $bterr = 'btseg';
    //
    foreach ($segments as $key => $seg) {
        $idx++;
        $title = '';
        $stsegct++;
        $bterr = 'btseg';
        //
        if ($erstn && ($erstn == $stn)) {
            $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
        }

        //
        if (strncmp('ST' . $de, $seg, 3) === 0) {
            $loopid = 'Header';
            $stkey = (int)$key;
            $stsegct = 1;
            $stn = explode($de, $seg)[2];
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('BHT' . $de, $seg, 4) === 0) {
            $loopid = 'Begin';
            $stsegct = 2;
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('HL' . $de, $seg, 3) === 0) {
            $sar = explode($de, $seg);
            if ($sar[3] == '20') {                      // level code
                $loopid = '2000A';                      // billing provider (clinic)
            } elseif ($sar[3] == '22') {
                $loopid = '2000B';                      // subscriber
            } elseif ($sar[3] == '23' || $sar[3] == 'PT') {
                $loopid = '2000C';                      // dependent
                $has_eb = false;
            } else {
                //debug
                csv_edihist_log('edih_837_text: HL has no level ' . $seg);
            }

            //
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('CLM' . $de, $seg, 4) === 0) {
            $loopid = '2300';
            $title = 'Claim';
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $title = '';
            continue;
        }

        //
        if (strncmp('SBR' . $de, $seg, 4) === 0) {
            if ($loopid == '2000B') {
                $title = 'Subscriber';
                $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            } else {
                $title = 'Other Subscriber';
                $loopid = '2320';
                $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'> -- </td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            }

            $title = '';
            continue;
        }

        //
        if (strncmp('LX' . $de, $seg, 3) === 0) {
            $loopid = '2400';
            $title = 'Svc Line Number';
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $title = '';
            continue;
        }

        //
        if (strncmp('LIN' . $de, $seg, 4) === 0) {
            $loopid = '2410';
            $title = 'Drug ID';
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $title = '';
            continue;
        }

        //
        if (strncmp('SVD' . $de, $seg, 4) === 0) {
            $loopid = '2430';
            $title = 'Line Adjudication';
            $str_html .= "<tr></tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $title = '';
            continue;
        }

        //
        if (strncmp('NM1' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $nm101 = ( isset($sar[1]) ) ? $sar[1] : '';
            if ($loopid == 'Begin' || strcmp(substr($loopid, 0, 4), '2320') < 0) {
                if ($nm101 == '41') {
                    $loopid = '1000A';
                    $title = 'Submitter';
                } elseif ($nm101 == '40') {
                    $loopid = '1000B';
                    $title = 'Receiver';
                } elseif ($nm101 == '85') {
                    $loopid = '2010AA';
                    $title = 'Billing';
                } elseif ($nm101 == '87') {
                    $loopid = '2010AB';
                    $title = 'Pay to';
                } elseif ($nm101 == 'PE') {
                    $loopid = '2010AC';
                    $title = 'Pay to Plan';
                } elseif ($nm101 == 'IL') {
                    $loopid = '2010BA';
                    $title = 'Subscriber';
                } elseif ($nm101 == 'PR') {
                    $loopid = '2010BB';
                    $title = 'Payer';
                } elseif ($nm101 == 'QC') {
                    $loopid = '2010CA';
                    $title = 'Patient';
                } elseif ($nm101 == 'DN') {
                    $loopid = '2310A';
                    $title = 'Referring Provider';
                } elseif ($nm101 == 'P3') {
                    $loopid = '2310A';
                    $title = 'Primary Care Provider';
                } elseif ($nm101 == '82') {
                    $loopid = '2310B';
                    $title = 'Rendering Provider';
                } elseif ($nm101 == '77') {
                    $loopid = '2310C';
                    $title = 'Service Facility';
                } elseif ($nm101 == 'DQ') {
                    $loopid = '2310D';
                    $title = 'Supervising Provider';
                } elseif ($nm101 == 'PW') {
                    $loopid = '2310E';
                    $title = 'Ambulance pickup';
                } elseif ($nm101 == '45') {
                    $loopid = '2310F';
                    $title = 'Ambulance dropoff';
                }
            } elseif (strcmp(substr($loopid, 0, 4), '2400')  < 0) {
                if ($nm101 == 'IL') {
                    $loopid = '2330A';
                    $title = 'Other Subscriber';
                } elseif ($nm101 == 'PR') {
                    $loopid = '2330B';
                    $title = 'Other Payer';
                } elseif ($nm101 == 'PR') {
                    $loopid = '2330C';
                    $title = 'Other Referring Provider';
                } elseif ($nm101 == '82') {
                    $loopid = '2330D';
                    $title = 'Other Rendering Provider';
                } elseif ($nm101 == '77') {
                    $loopid = '2330E';
                    $title = 'Other Svc Facility';
                } elseif ($nm101 == 'DQ') {
                    $loopid = '2330F';
                    $title = 'Other Supervising Provider';
                } elseif ($nm101 == '85') {
                    $loopid = '2330G';
                    $title = 'Other Billing Provider';
                }
            } else {
                if ($nm101 == '82') {
                    $loopid = '2420A';
                    $title = 'Rendering Provider';
                } elseif ($nm101 == 'QB') {
                    $loopid = '2420B';
                    $title = 'Purchased Svc Provider';
                } elseif ($nm101 == '77') {
                    $loopid = '2420C';
                    $title = 'Service Facility';
                } elseif ($nm101 == 'DQ') {
                    $loopid = '2420D';
                    $title = 'Supervising Provider';
                } elseif ($nm101 == 'DK') {
                    $loopid = '2420E';
                    $title = 'Ordering Provider';
                } elseif ($nm101 == 'DN') {
                    $loopid = '2420F';
                    $title = 'Referring Provider';
                } elseif ($nm101 == 'P3') {
                    $loopid = '2420F';
                    $title = 'Primary Care Provider';
                } elseif ($nm101 == 'PW') {
                    $loopid = '2420G';
                    $title = 'Ambulance pickup';
                } elseif ($nm101 == '45') {
                    $loopid = '2420H';
                    $title = 'Ambulance dropoff';
                }
            }

            //
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $title = '';
            continue;
        }

        //
        if (strncmp('SE' . $de, $seg, 3) === 0) {
            $loopid = 'Trailer';
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $title = '';
            continue;
        }

        // for all the segments that do not begin loops
        $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'> -- </td><td class='btnum' title='" . attr($key) . "'>" . text($stsegct) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
        //
    }

    //
    return $str_html;
}

/**
 * identify loops and format segments for display
 * of 270/271 (eligibility) type x12 edi files
 *
 * @param array  $segments
 * @param string  $delimiter
 * return string
 */
function edih_271_text($segments, $delimiter, $err_seg = '')
{
    //
    $str_html = '';
    //
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        // debug
        csv_edihist_log('edih_271_text: invalid argument');
        $str_html .= "Invalid arguments for view of x12 file text<br />";
        return $str_html;
    }

    //
    $de = $delimiter;
    $prevseg = '';
    $loopid = "0";
    $lx_ct = 0;
    $hasst = false;
    $idx = 0;
    $stsegct = 0;
    //
    // to highlight identified errors listed in 999/997 ack (for 270)
    if ($err_seg) {
        $er = edih_errseg_parse($err_seg);
        $erstn = (isset($er['trace'])) ? substr($er['trace'], -4) : '';
        $erseg = (isset($er['err'])) ? $er['err'] : array();
    } else {
        $erstn = '';
        $erseg = array();
    }

    //
    if ($err_seg) {
        $er = edih_errseg_parse($err_seg);
        if (is_array($er) && count($er)) {
            $err_ar = $er;
        }
    }

    //
    foreach ($segments as $key => $seg) {
        $sar = array();
        $idx++;
        $stsegct++;
        $bterr = 'btseg';
        //
        if ($erstn && ($erstn == $stn)) {
            $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
        }

        //
        if (strncmp('ST' . $de, $seg, 3) === 0) {
            $sar = explode($de, $seg);
            $loopid = 'Header';
            $hasst = true;
            $stsegct = 1;
            $sttp = (isset($seg[1])) ? $seg[1] : '';
            $stn = (isset($seg[2])) ? $seg[2] : '';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('BHT' . $de, $seg, 4) === 0) {
            $loopid = 'Begin';
            // 2nd seg in transaction, ST may not be included if segments are transaction slice
            if ($stsegct < 2) {
                $stsegct = 2;
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('HL' . $de, $seg, 3) === 0) {
            $sar = explode($de, $seg);
            if ($sar[3] == '20') {                      // level code
                $loopid = '2000A';                      // info source (payer)
            } elseif ($sar[3] == '21') {
                $loopid = '2000B';                      // info receiver (clinic)
            } elseif ($sar[3] == '22') {
                $loopid = '2000C';                      // subscriber
                $has_eb = false;
            } elseif ($sar[3] == '23') {
                $loopid = '2000D';                      // dependent
                $has_eb = false;
            } else {
                //debug
                csv_edihist_log('edih_271_text: HL has no level ' . $seg);
            }

            //
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'HL' . $de;
            continue;
        }

        //
        if (strncmp('NM1' . $de, $seg, 4) === 0) {
            if (strncmp('NM1' . $de, $prevseg, 4) === 0) {
                $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
                continue;
            }

            switch ((string)$loopid) {
                case '2000A':
                    $loopid = '2100A';
                    break;     // edih_change_loop($lptest, &$lpval)
                case '2000B':
                    $loopid = '2100B';
                    break;
                case '2000C':
                    $loopid = '2100C';
                    break;
                case '2000D':
                    $loopid = '2100D';
                    break;
                default:
                    $loopid = $loopid;
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'NM1' . $de;
            continue;
        }

        //
        if (strncmp('EB' . $de, $seg, 3) === 0 || strncmp('EQ' . $de, $seg, 3) === 0) {
            // EB* segment is in 271 type, EQ* is corresponding segment in 270 type
            if (strncmp($seg, $prevseg, 3) === 0) {
                $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
                $prevseg = substr($seg, 0, 3);
                continue;
            }

            if ($loopid = '2100C' || $loopid = '2115C' || $loopid = '2120C') {
                $loopid = '2110C';
            } elseif ($loopid = '2100D' || $loopid = '2115D' || $loopid = '2120D') {
                $loopid = '2110D';
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = substr($seg, 0, 3);
            $has_eb = (strncmp('EB' . $de, $seg, 3) === 0);
            $has_iii = false;
            continue;
        }

        //
        if (strncmp('III' . $de, $seg, 4) === 0 && $has_eb) {
            // the III segment begins a loop in 271 type, but not in 270
            if ($loopid = '2110C') {
                $loopid = '2115C';
            }

            if ($loopid = '2100D') {
                $loopid = '2115D';
            }

            if ($has_iii) {
                $str_html .= "<tr><td class='btloop'></td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            } else {
                $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
                $has_iii = true;
            }

            $prevseg = substr($seg, 0, 4);
            continue;
        }

        //
        if (strncmp('LS' . $de, $seg, 3) === 0) {
            if ($loopid = '2110C' || $loopid = '2115C') {
                $loopid = '2120C';
            } elseif ($loopid = '2110D' || $loopid = '2115D') {
                $loopid = '2120D';
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('SE' . $de, $seg, 3) === 0) {
            $str_html .= "<tr><td class='btloop'>Trailer</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $loopid = '0';
            continue;
        }

        // for all the segments that do not begin loops
        $prevseg = substr($seg, 0, strpos($seg, $de) + 1);
        $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
        //
    }

    //
    return $str_html;
}


function edih_835_text($segments, $delimiter, $err_seg = '')
{
    //
    $str_html = '';
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        //debug
        csv_edihist_log('edih_835_text: invalid segments');
        $str_html .= "Invalid arguments for view of x12 file text<br />";
        return $str_html;
    }

    //
    $de = $delimiter;
    $prevseg = '';
    $loopid = "0";
    $lx_ct = 0;
    $idx = 0;
    //
    foreach ($segments as $key => $seg) {
        //$idx++;
        //
        if (strncmp('ST' . $de, $seg, 3) === 0) {
            $loopid = 'Header';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('N1' . $de, $seg, 3) === 0) {
            $sar = explode($de, $seg);
            if ($sar[1] == 'PR') {
                $loopid = '1000A';
            } elseif ($sar[1] == 'PE') {
                $loopid = '1000B';
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'N1' . $de;
            continue;
        }

        //
        if (strncmp('LX' . $de, $seg, 3) === 0) {
            $loopid = '2000';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'LX' . $de;
            continue;
        }

        //
        if (strncmp('TS3' . $de, $seg, 4) === 0) {
            if ($loopid == '2000') {
                $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            } else {
                $loopid = '2000';
                $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            }

            $prevseg = 'TS3' . $de;
            continue;
        }

        //
        if (strncmp('CLP' . $de, $seg, 4) === 0) {
            $loopid = '2100';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'CLP' . $de;
            continue;
        }

        //
        if (strncmp('SVC' . $de, $seg, 4) === 0) {
            $loopid = '2110';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'SVC' . $de;
            continue;
        }

        //
        if (strncmp('PLB' . $de, $seg, 4) === 0) {
            $loopid = 'Adjust';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'PLB' . $de;
            continue;
        }

        //
        if (strncmp('SE' . $de, $seg, 3) === 0) {
            $loopid = 'Trailer';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'SE' . $de;
            continue;
        }

        // for all the segments that do not begin loops
        $prevseg = substr($seg, 0, 3);
        $prevseg .= $de;
        $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
        //
    }

    //
    return $str_html;
}


/**
 * identify loops and format segments for display
 * of 277 (claim status) type x12 edi files
 *
 * @param array  $segments
 * @param string  $delimiter
 * return string
 */
function edih_277_text($segments, $delimiter, $stpos = '')
{
    //
    $str_html = '';
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        //debug
        csv_edihist_log('edih_277_text: invlid segments');
        $str_html .= "Invalid arguments for view of x12 file text<br />";
        return $str_html;
    }

    //
    // to highlight identified errors listed in 999/997 ack (for 276)
    //if ($err_seg) {
        //$er = edih_errseg_parse($err_seg);
        //$erstn = (isset($er['trace'])) ? substr($er['trace'], -4) : '';
        //$erseg = (isset($er['err'])) ? $er['err'] : array();
    //} else {
        //$erstn = '';
        //$erseg = array();
    //}
    //
    $de = $delimiter;
    $prevseg = '';
    $loopid = "0";
    $lx_ct = 0;
    $stsegct = 0;
    //$idx = 0;
    //
    foreach ($segments as $idx => $seg) {
        //$idx++;
        $stsegct++;
        $key = ($stpos) ? $idx - $stpos : $idx;
        // if 276 transactions are parsed, 999 errors may be present
        //if ($erstn && ($erstn == $stn)) {
            //$bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
        //}
        //
        if (strncmp('ST' . $de, $seg, 3) === 0) {
            $loopid = 'Header';
            $stsegct = 1;
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('BHT' . $de, $seg, 4) === 0) {
            $loopid = 'Begin';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('HL' . $de, $seg, 3) === 0) {
            $sar = explode($de, $seg);
            if ($sar[3] == '20') {                      // level code
                $loopid = '2000A';                      // info source (payer)
            } elseif ($sar[3] == '21') {
                $loopid = '2000B';                      // info receiver (clinic)
            } elseif ($sar[3] == '19') {
                $loopid = '2000C';                      // provider
            } elseif ($sar[3] == '22' || $sar[3] == 'PT') {
                $loopid = '2000D';                      // subscriber
            } elseif ($sar[3] == '23') {
                $loopid = '2000E';                      // dependent
            } else {
                //debug
                csv_edihist_log('edih_277_text: HL has no level ' . $seg);
            }

            //
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'HL' . $de;
            continue;
        }

        //
        //
        if (strncmp('NM1' . $de, $seg, 4) === 0) {
            if (strncmp('NM1' . $de, $prevseg, 4) === 0) {
                $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
                continue;
            }

            switch ((string)$loopid) {
                case '2000A':
                    $loopid = '2100A';
                    break;     // edih_change_loop($lptest, &$lpval)
                case '2000B':
                    $loopid = '2100B';
                    break;
                case '2000C':
                    $loopid = '2100C';
                    break;
                case '2000D':
                    $loopid = '2100D';
                    break;
                case '2000E':
                    $loopid = '2100E';
                    break;
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'NM1';
            continue;
        }

        //
        if (strncmp('TRN' . $de, $seg, 4) === 0) {
            if (strncmp('TRN' . $de, $prevseg, 4) === 0) {
                $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
                continue;
            }

            switch ((string)$loopid) {
                case '2100A':
                    $loopid = '2200A';
                    break;
                case '2100B':
                    $loopid = '2200B';
                    break;
                case '2100C':
                    $loopid = '2200C';
                    break;
                case '2100D':
                    $loopid = '2200D';
                    break;
                case '2100E':
                    $loopid = '2200E';
                    break;
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'TRN';
            continue;
        }

        //
        if (strncmp('SVC' . $de, $seg, 4) === 0) {
            if (strncmp('SVC' . $de, $prevseg, 4) === 0) {
                $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
                continue;
            }

            switch ((string)$loopid) {
                case '2200D':
                    $loopid = '2220D';
                    break;
                case '2200E':
                    $loopid = '2220E';
                    break;
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'SVC';
            continue;
        }

        //
        if (strncmp('SE' . $de, $seg, 3) === 0) {
            $loopid = 'Trailer';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'SE';
            continue;
        }

        // for all the segments that do not begin loops
        $prevseg = substr($seg, 0, 3);
        $prevseg .= $de;
        $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
    }

    //
    return $str_html;
}

/**
 * identify loops and format segments for display
 * of 278 (authorization) type x12 edi files
 *
 * @param array  $segments
 * @param string  $delimiter
 * return string
 */
function edih_278_text($segments, $delimiter, $err_seg = '')
{
    //
    $str_html = '';
    //
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        // debug
        csv_edihist_log('edih_278_text(): invalid argument');
        $str_html .= "Invalid arguments for view of x12 file text<br />";
        return $str_html;
    }

    //
    $de = $delimiter;
    $prevseg = '';
    $loopid = "0";
    $lx_ct = 0;
    $hasst = false;
    $err_ar = array();
    $idx = 0;
    $stsegct = 0;
    //
    // to highlight identified errors listed in 999/997 ack
    if ($err_seg) {
        $er = edih_errseg_parse($err_seg);
        $erstn = (isset($er['trace'])) ? substr($er['trace'], -4) : '';
        $erseg = (isset($er['err'])) ? $er['err'] : array();
    } else {
        $erstn = '';
        $erseg = array();
    }

    //
    foreach ($segments as $key => $seg) {
        $idx++;
        $stsegct++;
        $title = '';
        $bterr = 'btseg';
        //
        if ($erstn && ($erstn == $stn)) {
            $bterr = (in_array($stsegct, $erseg)) ? 'bterr' : 'btseg';
        }

        //
        if (strncmp('ST' . $de, $seg, 3) === 0) {
            $loopid = 'Header';
            $hasst = true;
            $stsegct = 1;
            $stn = explode($de, $seg)[2];
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('BHT' . $de, $seg, 4) === 0) {
            $loopid = 'Begin';
            // 2nd seg in transaction, ST may not be included if segments are transaction slice
            if ($stsegct < 2) {
                $stsegct = 2;
            }

            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp($seg, 'HL' . $de, 3) === 0) {
            $sar = explode($de, $seg);
            $hl = $sar[1];
            $hlpc = $sar[2];                            // parent code
            $hllc = $sar[3];
            $hlcc = (isset($sar[4])) ? $sar[4] : '';    // child code
            if ($sar[3] == '20') {                      // level code
                $loopid = '2000A';                      // info source (payer)
                $title = 'Info Source';
            } elseif ($sar[3] == '21') {
                $loopid = '2000B';                      // info receiver (clinic)
                $title = 'Info Receiver';
            } elseif ($sar[3] == '22') {
                $loopid = '2000C';                      // subscriber
                $title = 'Subscriber';
            } elseif ($sar[3] == '23') {
                $loopid = '2000D';                      // dependent
                $title = 'Dependent';
            } elseif ($sar[3] == 'EV') {
                $loopid = '2000E';                      // patient event
                $title = 'Patient Event';
            } elseif ($sar[3] == 'SS') {
                $loopid = '2000F';                      // service
                $title = 'Service';
            } else {
                //debug
                csv_edihist_log('edih_278_text: HL has no level ' . $seg);
            }

            //
            $str_html .= "<tr><td class='btloop' title='" . attr($title) . "'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'HL';
            continue;
        }

        //
        if (strncmp($seg, 'NM1' . $de, 4) === 0) {
            $sar = explode($de, $seg);
            $nm101 = $sar[1];
            if ($loopid == '2000A') {
                $loopid == '2010A';  // Source
            } elseif ($loopid == '2000B') {
                $loopid = '2010B';   // Receiver
            } elseif ($loopid == '2000C') {
                $loopid = '2010C';   // Subscriber
            } elseif ($loopid == '2000D') {
                $loopid = '2010D';   // Dependent
            } elseif ($loopid == '2000E' || strpos($loopid, '010E')) {    // Patient Event
                $loopid = (strpos('|71|72|73|77|AAJ|DD|DK|DN|FA|G3|P3|QB|QV|SJ', $nm101) ) ? '2010EA' : $loopid;
                $loopid = (strpos('|45|FS|ND|PW|R3', $nm101) ) ? '2010EB' : $loopid;
                $loopid = ($nm101 == 'L5') ? '2010EC' : $loopid;
            } elseif ($loopid == '2000F' || strpos($loopid, '010F')) {   // Service
                $loopid = (strpos('|71|72|73|77|AAJ|DD|DK|DN|FA|G3|P3|QB|QV|SJ', $nm101) ) ? '2010FA' : $loopid;
            }

            //
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $prevseg = 'NM1';
            continue;
        }

        //
        if (strncmp('SE' . $de, $seg, 3) === 0) {
            $str_html .= "<tr><td class='btloop'>Trailer</td><td class='btnum'>" . text($key) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
            $loopid = '0';
            $prevseg = 'SE';
            continue;
        }

        // for all the segments that do not begin loops
        $prevseg = substr($seg, 0, strpos($seg, $de));
        $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='" . attr($bterr) . "'>" . text($seg) . "</td></tr>" . PHP_EOL;
        //
    }

    //
    return $str_html;
}

/**
 * identify loops and format segments for display
 * of 997/999 (acknowledgement) type x12 edi files
 *
 * @param array  $segments
 * @param string  $delimiter
 * return string
 */
function edih_997_text($segments, $delimiter)
{
    //
    $str_html = '';
    if (!is_array($segments) || !count($segments) || strlen($delimiter) != 1) {
        //debug
        csv_edihist_log('edih_997_text(): invalid segments');
        return $str_html;
    }

    //
    $de = $delimiter;
    $loopid = "0";
    //
    //echo 'edih_997_text() foreach segment count: '.count($segments).PHP_EOL;
    //
    foreach ($segments as $key => $seg) {
        //
        //echo var_dump($seg).PHP_EOL;
        //
        if (strncmp('TA1' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $rspicn = $sar[1];
            $loopid = 'ACK';  // not official
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            unset($sar);
            $loopid = '';  // reset loop for subsequent segments
            continue;
        }

        //
        if (strncmp('ST' . $de, $seg, 3) === 0) {
            $loopid = 'Header';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('AK1' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $rsptp = csv_file_type($sar[1]);
            if ($rspicn && $rsptp) {
                $rspfile = csv_file_by_controlnum($rsptp, $rspicn);
            }

            $title = ($rspfile) ? 'response to ' . $rspfile : '';
            $loopid = '2000';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td title='" . attr($title) . "' class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            unset($sar);
            continue;
        }

        //
        if (strncmp('AK2' . $de, $seg, 4) === 0) {
            $loopid = 'AK2';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('IK3' . $de, $seg, 4) === 0 || strncmp('AK3' . $de, $seg, 4) === 0) {
            $loopid = '2100';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('IK4' . $de, $seg, 4) === 0 || strncmp('AK4' . $de, $seg, 4) === 0) {
            $loopid = '2110';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        //
        if (strncmp('SE' . $de, $seg, 3) === 0) {
            $loopid = 'Trailer';
            $str_html .= "<tr><td class='btloop'>" . text($loopid) . "</td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
            continue;
        }

        // for all the segments that do not begin loops ;
        $str_html .= "<tr><td class='btloop'> -- </td><td class='btnum'>" . text($key) . "</td><td class='btseg'>" . text($seg) . "</td></tr>" . PHP_EOL;
    }

    //
    return $str_html;
}

/**
 * Display of x12 edi transaction listing or all segments in the files.
 * When using $err_info, you must use the filepath of the submitted file
 *
 * @param string   $filepath path to desired file
 * @param string   $filetype used when filepath is just filename
 * @param string   optional $claimid CLM01, or BHT03 to identify a transaction or a trace value
 * @param bool     false: $claimid is pt transaction, true: $claimid is trace from 835 or 999
 * @param string   optional $err_info  the prepared error info from a 997/999 response
 * @return string  html for display of file segments
 */
function edih_display_text($filepath, $filetype = '', $claimid = '', $trace = false, $err_info = '')
{
    //
    $str_html = '';
    $de = '';
    $segments = '';
    $stsegkey = '';
    $ft = ($filetype) ? $filetype : '';
    $errs = ( strlen($err_info) ) ? $err_info : '';
    $bht03 = '';
    //
    // verify x12 file
    $x12obj = csv_check_x12_obj($filepath, $ft);
    //
    if ($x12obj && 'edih_x12_file' == get_class($x12obj)) {
        $ftype = $x12obj->edih_type();
        $ft = csv_file_type($ftype);
        $delims = $x12obj->edih_delimiters();
        $de = $delims['e'];
        $fn = $x12obj->edih_filename();
        $segs_ar = $x12obj->edih_segments();
        $env_ar = $x12obj->edih_envelopes();
        if (!$de || strlen($de) != 1) {
            // error in object
            // debug
            $str_html = 'edih_display_text(): error in delimiters<br />' . PHP_EOL;
            $str_html .= $x12obj->edih_message() . PHP_EOL;
            return $str_html;
        }

        if (!is_array($segs_ar) || !count($segs_ar)) {
            // unknown error
            $str_html = "<p>unknown error retrieving segments for " . text($fn) . "</p>" . PHP_EOL;
            $str_html .= $x12obj->edih_message() . PHP_EOL;
            return $str_html;
        }
    } else {
        //debug
        csv_edihist_log('edih_transaction_text(): invalid path ' . $filepath);
        $str_html = 'edih_display_text(): error accessing file<br />' . PHP_EOL;
        return $str_html;
    }

    //
    if ($claimid) {
        // claimid can be for transaction, payment, or error response
        if ($trace && array_key_exists($claimid, $env_ar['ISA'])) {
            $arg_ar = array('ISA13' => $claimid, 'keys' => true);
            $segments = $x12obj->edih_x12_slice($arg_ar);
        } else {
            // claimid alone can be clm01 or bht03, if trace=true, expect trn02 for claimid
            foreach ($env_ar['ST'] as $st) {
                if ($trace && $claimid == $st['trace']) {
                    $arg_ar = array('ISA13' => $st['icn'], 'GS06' => $st['gsn'], 'trace' => $claimid, 'keys' => true);
                    $segments = $x12obj->edih_x12_slice($arg_ar);
                    break;
                } elseif (in_array($claimid, $st['acct'])) {
                    if ($errs) {
                        $arg_ar = array('ST02' => $st['stn'], 'ISA13' => $st['icn'], 'GS06' => $st['gsn'], 'keys' => true);
                        $segments = $x12obj->edih_x12_slice($arg_ar);
                    } else {
                        // request for individual transaction segments
                        $segments = $x12obj->edih_x12_transaction($claimid);
                    }

                    break;
                } elseif (in_array($claimid, $st['bht03'])) {
                    // also possible that bht03 number is given for claimid
                    // this will likely be a 27x
                    if ($errs) {
                        $arg_ar = array('ST02' => $st['stn'], 'ISA13' => $st['icn'], 'GS06' => $st['gsn'], 'keys' => true);
                        $segments = $x12obj->edih_x12_slice($arg_ar);
                    } else {
                        $segments = $x12obj->edih_x12_transaction($claimid);
                    }

                    $bht03 = $claimid;
                    break;
                }
            }
        }
    } else {
        $segments = $segs_ar;
    }

    //
    // now check if we have segments
    if (empty($segments) || !count($segments)) {
        if ($claimid) {
            $str_html = "<p>error: transaction " . text($claimid) . " not found in " . text($fn) . "</p>" . PHP_EOL;
            $str_html .= $x12obj->edih_message() . PHP_EOL;
            return $str_html;
        } else {
            // unknown error
            $str_html = "<p>unknown error retrieving segments for " . text($fn) . "</p>" . PHP_EOL;
            $str_html .= $x12obj->edih_message() . PHP_EOL;
            return $str_html;
        }
    }

    // if the segments are from a slice or transaction
    // a multidimensional array  segs[i][j] must be flattened
    $ar_sngl = csv_singlerecord_test($segments);
    // false when segments are a transaction or trace only
    if (!$ar_sngl) {
        //
        // append segments to single array
        // keys should not duplicate since all segments
        // are from the same x12 file
        $trnsegs = array();
        for ($i = 0; $i < count($segments); $i++) {
            $trnsegs = array_merge($trnsegs, $segments[$i]);
        }

        $segments = $trnsegs;
        unset($trnsegs);
    }

    //
    $capstr = '';
    $tbl_id = ($claimid) ? $claimid : $fn;
    //
    //'HB'=>'271', 'HS'=>'270', 'HR'=>'276', 'HI'=>'278','HN'=>'277', 'HP'=>'835', 'FA'=>'999', 'HC'=>'837');
    switch ((string)$ftype) {
        case 'HC':
            $capstr = "Claim "; //$cls = "txt837";
            $trn_html = edih_837_text($segments, $de, $errs);
            break;
        case 'HP':
            $capstr = "Payment "; //$cls = "txt835";
            $trn_html = edih_835_text($segments, $de);
            break;
        case 'HR':
            $capstr = "Status Query ";  //$cls = "txt276";
            $trn_html = edih_277_text($segments, $de, $errs);
            break;
        case 'HN':
            $capstr = "Claim Status "; //$cls = "txt277";
            $trn_html = edih_277_text($segments, $de, $stsegkey);
            break;
        case 'HS':
            $capstr = "Eligibility Query "; //$cls = "txt270";
            $trn_html = edih_271_text($segments, $de, $errs);
            break;
        case 'HB':
            $capstr = "Eligibility Report "; //$cls = "txt271";
            $trn_html = edih_271_text($segments, $de);
            break;
        case 'HI':
            $capstr = "Authorization "; //$cls = "txt278";
            $trn_html = edih_278_text($segments, $de, $errs);
            break;
        case 'FA':
            $capstr = "Batch Acknowledgment "; //$cls = "txt997";
            $trn_html = edih_997_text($segments, $de);
            break;
        default:
            $capstr = "x12 $ftype "; //$cls = "txt_x12";
            $trn_html = edih_segments_text($segments, $de);
            break;
    }

    //
    $capstr .= ($claimid) ? " ID: " . text($claimid) : "";
    //
    $str_html .= "<table id=" . attr($tbl_id) . " cols=3 class='segtxt'><caption>" . text($capstr) . "</caption>" . PHP_EOL;
    $str_html .= "<thead>" . PHP_EOL;
    $str_html .= "<tr><th class='btloop'>Loop</th><th class='btloop'>Num</th>";
    $str_html .= "<th class='segtxt'>Segment (<em>File:</em> " . text($fn) . ")</th></tr>" . PHP_EOL;
    $str_html .= "</thead>" . PHP_EOL . "<tbody>" . PHP_EOL;
    //
    $str_html .= $trn_html;
    //
    $str_html .= "</tbody></table>" . PHP_EOL;
    //
    return $str_html;
}
