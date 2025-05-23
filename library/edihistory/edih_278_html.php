<?php

/*
 * test_278_parse.php
 *
 * Copyright 2016 Kevin McCormick <kevin@kt61p>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

//
//require_once("$srcdir/edihistory/codes/edih_271_code_class.php");
//
/*
 * HTML display of x12 278 file
 *  SV3 and TOO segments (Dental) are ignored
 *
 * @uses edih_271_codes()
 *
 * @param object   edih_x12_file
 * $param string   BHT03 value to identify transaction
 * @return string
 */
function edih_278_transaction_html($obj278, $bht03)
{
    //
    $trans = $obj278->edih_x12_transaction($bht03);
    if (empty($trans)) {
        $str_html = $obj278->edih_message();
        return $str_html;
    }

    $de = $obj278->edih_delimiters()['e'];
    $ds = $obj278->edih_delimiters()['s'];
    $dr = $obj278->edih_delimiters()['r'];
    $fn = $obj278->edih_filename();
    //
    $cd27x = new edih_271_codes($ds, $dr);
    //
    $cls = '';
    $capstr = 'Services Review (Cert/Auth)';
    //
    $hdr_html = "<table class='h278' columns=4><caption>" . text($capstr) . "</caption>";
    $hdr_html .= "<thead><tr><th>Reference</th><th>Information</th><th colspan=2>" . text($fn) . "</th></tr></thead>" . PHP_EOL;
    $hdr_html .= "<tbody>" . PHP_EOL;
    $str_html = "";
    $src_html = "";
    $rcv_html = "";
    $sbr_nm1_html = "";
    $dep_nm1_html = "";
    $evt_html = "";
    $svc_html = "";
    //
    $trns_ct = count($trans);
    for ($i = 0; $i < $trns_ct; $i++) {
        foreach ($trans[$i] as $seg) {
            //
            $idtype = '';
            $name = '';
            $var = '';
            $rej_reason = '';
            $follow = '';
            $addr = '';
            // debug
            //
            if (strncmp('BHT' . $de, $seg, 4) === 0) {
                $loopid = 'Heading';
                $sar = explode($de, $seg);
                $elem01 = ($sar[1] == '0007') ? "Src, Rcvr, Sbr, Dep, Evt, Svc" : "Not determined";
                //
                $elem02 = ( isset($sar[2]) && $sar[2] !== false) ? $cd27x->get_271_code('BHT02', $sar[2]) : "";
                //
                $elem03 = (isset($sar[3]) ) ? $sar[3] : '';
                $elem04 = (isset($sar[4]) ) ? edih_format_date($sar[4]) : '';
                //
                $elem06 = (isset($sar[6]) && $sar[6]) ? $cd27x->get_271_code('BHT06', $sar[6]) : "";
                //
                $hdr_html .= "<tr><td colspan=2><em>Transaction ID:</em> " . text($elem03) . " </td><td colspan=2><em>Sequence:</em> " . text($elem01) . "</td></tr>" . PHP_EOL;
                if ($elem06) {
                    $hdr_html .= "<tr><td colspan=2><em>Transaction Date:</em> " . text($elem04) . " </td><td colspan=2>" . text($elem02) . " <em>Type:</em> " . text($elem06) . "</td></tr>" . PHP_EOL;
                } else {
                    $hdr_html .= "<tr><td colspan=2><em>Transaction Date:</em> " . text($elem04) . " </td><td colspan=2><em>Type:</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //'src''rcv'sbr''dep''evt''svc''
            if (strncmp($seg, 'HL' . $de, 3) === 0) {
                $sar = explode($de, $seg);
                $hl = $sar[1];
                $hlpc = $sar[2];                            // parent code
                $hllc = $sar[3];
                $hlcc = (isset($sar[4])) ? $sar[4] : '';    // child code
                if ($sar[3] == '20') {                      // level code
                    $loopid = '2000A';
                    $cls = 'src';                   // info source (payer)
                    $src_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Information Source</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == '21') {
                    $loopid = '2000B';
                    $cls = 'rcv';                           // info receiver (clinic)
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Information Receiver</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == '22') {
                    $loopid = '2000C';                      // subscriber
                    $cls = 'sbr';
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Subscriber</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == '23') {
                    $loopid = '2000D';                      // dependent
                    $cls = 'dep';
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Dependent</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == 'EV') {
                    $loopid = '2000E';                      // patient event
                    $cls = 'evt';
                    $evt_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Patient Event</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == 'SS') {
                    $loopid = '2000F';                      // service
                    $cls = 'svc';
                    $svc_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Service</b></td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            if (strncmp('NM1' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $nm101 = ( isset($sar[1]) ) ? $sar[1] : '';
                $descr = ($nm101) ? $cd27x->get_271_code('NM101', $nm101) : "";
                //
                $name = (isset($sar[3]) && $sar[3] ) ? $sar[3] : "";
                $name .= (isset($sar[7]) && $sar[7]) ? " {$sar[7]}" : "";
                $name .= (isset($sar[4]) && $sar[4]) ? ", {$sar[4]}" : "";
                $name .= (isset($sar[5]) &&  $sar[5]) ? " {$sar[5]}" : "";
                $nm109 = (isset($sar[9]) &&  $sar[9]) ? $sar[9] : "";
                //
                $idtype = (isset($sar[8]) && $sar[8] ) ? $cd27x->get_271_code('NM108', $sar[8]) : "";
                //
                if ($loopid == '2000A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2010A';
                } elseif ($loopid == '2000B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2010B';
                } elseif ($loopid == '2000C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2010C';
                } elseif ($loopid == '2000D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2010D';
                } elseif ($loopid == '2000E' || strpos($loopid, '010E')) {
                    $loopid = (strpos('|71|72|73|77|AAJ|DD|DK|DN|FA|G3|P3|QB|QV|SJ', $nm101) ) ? '2010EA' : $loopid;
                    $loopid = (strpos('|45|FS|ND|PW|R3', $nm101) ) ? '2010EB' : $loopid;
                    $loopid = ($nm101 == 'L5') ? '2010EC' : $loopid;
                    $evt_html .= "<tr class='" . attr($cls) . "'><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($descr . " " . $name) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000F' || strpos($loopid, '010F')) {
                    $loopid = (strpos('|71|72|73|77|AAJ|DD|DK|DN|FA|G3|P3|QB|QV|SJ', $nm101) ) ? '2010FA' : $loopid;
                    $loopid = ($nm101 == 'L5') ? '2010FB' : $loopid;
                    $svc_html .= "<tr class='" . attr($cls) . "'><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($descr . " " . $name) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('N3' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                $addr = (isset($sar[1])) ? $sar[1] : "";
                $addr .= (isset($sar[2])) ? " {$sar[2]}" : "";
                //
                if ($loopid == '2010B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010EA' || $loopid == '2010EC') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010FA') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('N4' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                if ($loopid == '2010B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010EA' || $loopid == '2010EC') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010FA' || $loopid == '2010FB') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //                              //
            if (strncmp('PER' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $elem01 = (isset($sar[1])) ? $sar[1] : '';
                $elem02 = (isset($sar[2])) ? $sar[2] : '';
                $elem03 = (isset($sar[3])) ? $cd27x->get_271_code('PER03', $sar[3]) : "";
                $elem04 = (isset($sar[4])) ? $sar[4] : '';
                $elem05 = (isset($sar[5])) ? $cd27x->get_271_code('PER03', $sar[5]) : "";
                $elem06 = (isset($sar[6])) ? $sar[6] : '';
                $elem07 = (isset($sar[7])) ? $cd27x->get_271_code('PER03', $sar[7]) : "";
                $elem08 = (isset($sar[8])) ? $sar[8] : '';
                $elem09 = (isset($sar[9])) ? $sar[9] : '';

                $idtype = ($sar[3]) ? $cd27x->get_271_code('PER03', $sar[3]) : "";
                if ($loopid == '2010A') {
                    $src_html .= "<tr class='" . text($cls) . "'><td colspan=2>" . text($elem02) . "</td><td colspan=2 title='" . attr($elem03 . " " . $elem05 . " " . $elem07) . "'>" . text($elem04 . " " . $elem06 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td colspan=2>" . text($elem02) . "</td><td colspan=2 title='" . attr($elem03 . " " . $elem05 . " " . $elem07) . "'>" . text($elem04 . " " . $elem06 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010EA') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td colspan=2>" . text($elem02) . "</td><td colspan=2 title='" . attr($elem03 . " " . $elem05 . " " . $elem07) . "'>" . text($elem04 . " " . $elem06 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010FA') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td colspan=2>" . text($elem02) . "</td><td colspan=2 title='" . attr($elem03 . " " . $elem05 . " " . $elem07) . "'>" . text($elem04 . " " . $elem06 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('AAA' . $de, $seg, 4) === 0) {
                // rejection
                $sar = explode($de, $seg);
                $rej_reason = $cd27x->get_271_code('AAA03', $sar[3]);
                $follow = $cd27x->get_271_code('AAA04', $sar[4]);
                if ($loopid == '2000A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010B') {
                    $sbr_eb_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010C') {
                    $sbr_eb_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010D') {
                    $dep_eb_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010EA' || $loopid == '2010EC') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010FA') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } else {
                    $hdr_html .= "<tr class='" . attr($cls) . "'><td>" . text($loopid) . " <em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('TRN' . $de, $seg, 4) === 0) {
                // trace identifier
                $sar = explode($de, $seg);
                $elem01 = ( isset($sar[1]) ) ? $sar[1] : '';
                $elem02 = ( isset($sar[2]) ) ? $sar[2] : '';
                $elem03 = ( isset($sar[3]) ) ? $sar[3] : '';
                $elem04 = ( isset($sar[4]) ) ? $sar[4] : '';
                //
                $trctp = ($elem01 == '2') ? 'Reference ' : 'Current ';
                if ($loopid == '2000E') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($trctp) . " tracking no:</em> " . text($elem02) . " (by " . text($elem03 . " " . $elem04) . ")</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000F') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($trctp) . " tracking no:</em> " . text($elem02) . " (by " . text($elem03 . " " . $elem04) . ")</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //

            if (strncmp('UM' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1])) ? $cd27x->get_271_code('UM01', $sar[1]) : '';
                $elem02 = (isset($sar[2])) ? $cd27x->get_271_code('UM02', $sar[2]) : '';
                $elem03 = (isset($sar[3])) ? $cd27x->get_271_code('EB03', $sar[3]) : '';
                $elem04 = (isset($sar[4])) ? $sar[4] : '';
                $elem04a = '';
                if (strpos($elem04, $ds)) {
                    $elem04_ar = explode($ds, $elem04);
                    if (isset($elem04_ar[1]) && $elem04_ar[1] == 'B') {
                        $elem04a .= (isset($elem04_ar[0])) ? $cd27x->get_271_code('POS', $elem04_ar[0]) : '';
                    } else {
                        $elem04a = $elem04;
                    }
                }

                /* UM05 -- not used
                 $elem05 = (isset($sar[5])) ? $sar[5] : '';
                 $elem05a = '';
                 if (strpos($elem05, $ds) {
                    $elem05_ar = explode($ds, $elem05);
                    $elem05a .= (isset($elem05_ar[0])) ? $cd27x->get_271_code('POS', $elem05_ar[0]) : '';
                    $elem05a .= (isset($elem05_ar[1])) ? $cd27x->get_271_code('POS', $elem05_ar[1]) : '';
                    $elem05a .= (isset($elem05_ar[2])) ? $cd27x->get_271_code('POS', $elem05_ar[2]) : '';
                    $elem05a .= (isset($elem05_ar[3])) ? 'State Code: '.$elem05_ar[3] : '';
                    $elem05a .= (isset($elem05_ar[3])) ? 'Country Code: '.$elem05_ar[4] : '';
                } else {
                    $elem05a .= $elem05;
                }
                //
                * */
                $elem06 = (isset($sar[6])) ? $cd27x->get_271_code('UM06', $sar[6]) : '';
                // UM07 UM08 not used
                //$elem07 = (isset($sar[7])) ? $cd27x->get_271_code('UM07', $sar[7]) : '';
                //$elem08 = (isset($sar[8])) ? $cd27x->get_271_code('UM08', $sar[8]) : '';
                //
                if ($loopid == '2000E') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem01) . " <em>Certification Type:</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                    $evt_html .= ($elem03 || $elem04a || $elem06) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04a) . " (" . text($elem06) . ")</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2000F') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem01) . " <em>Certification Type:</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                    $svc_html .= ($elem03 || $elem04a || $elem06) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04a) . " (" . text($elem06) . ")</td></tr>" . PHP_EOL : "";
                }

                continue;
            }

            //
            if (strncmp('HCR' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('HCR01', $sar[1]) : '';
                $elem02 = (isset($sar[2]) && $sar[2]) ? "<em>Reference: </em>" . $sar[2] : '';
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('HCR03', $sar[3]) : '';
                $elem04 = (isset($sar[4]) && $sar[4]) ? $cd27x->get_271_code('HCR04', $sar[4]) : ''; {
                $elem04 = ($elem04) ? "<em>2nd Surgical Opinion:</em> $elem04" : "";
                }
                //
                if ($loopid == '2000E') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem01 . " " . $elem02) . "</td></tr>" . PHP_EOL;
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03) . "</td></tr>" . PHP_EOL;
                    $evt_html .= ($elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem04) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2000F') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem01 . " " . $elem02) . "</td></tr>" . PHP_EOL;
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03) . "</td></tr>" . PHP_EOL;
                    $svc_html .= ($elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem04) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
            if (strncmp('REF' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1])) ? $cd27x->get_271_code('REF', $sar[1]) : '';
                $elem02 = (isset($sar[2])) ? $sar[2] : '';
                $elem03 = (isset($sar[3])) ? $sar[2] : '';
                //
                if ($loopid == '2010B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000E') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010E') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000F') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010FA') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('DMG' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $elem02 = (isset($sar[2]) && $sar[2]) ? edih_format_date($sar[2]) : "";
                if (isset($sar[3]) && $sar[3]) {
                    if ($sar[3] == 'M') {
                        $elem03 = "Male";
                    } elseif ($sar[3] == 'F') {
                        $elem03 = "Female";
                    } else {
                        $elem03 = "Unknown";
                    }
                }

                if ($loopid == '2010C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($elem03) . "</td><td colspan=2><em>Date of Birth</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($elem03) . "</td><td colspan=2><em>Date of Birth</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('INS' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $elem01 = (isset($sar[1]) && $sar[1] == 'Y') ? "Subscriber" : "Dependent";
                $elem02 = (isset($sar[2]) && $sar[2]) ? $cd27x->get_271_code('INS02', $sar[2]) : "";
                $elem03 = (isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                $elem04 = (isset($sar[4]) && $sar[4] == '25') ? "<b>Information changed</b>" : "";
                $elem17 = (isset($sar[17]) && $sar[17]) ? $sar[17] : "";
                //
                if ($loopid == '2010C') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($elem01 . " " . $elem17) . "</td><td>" . text($elem02) . "</td><td>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2010D') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($elem01 . " " . $elem17) . "</td><td>" . text($elem02) . "</td><td>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('DTP' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                $var = '';
                $elem01 = (isset($sar[1])) ? $sar[1] : '';
                $elem02 = (isset($sar[2])) ? $sar[2] : '';
                $elem03 = (isset($sar[3])) ? $sar[3] : '';
                //
                $idtype = ($elem01) ? $cd27x->get_271_code('DTP', $elem01) : "";
                if ($elem02 == 'D8' && $elem03) {
                    $var = edih_format_date($elem03);
                } elseif ($elem02 == 'RD8' && $elem03) {
                    $var = edih_format_date(substr($elem03, 0, 8));
                    $var .= ' - ' . edih_format_date(substr($elem03, -8));
                }

                if ($loopid == '2000E') {
                    $evt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($idtype) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000F') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($idtype) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('HI' . $de, $seg, 3) === 0) {
                // This is the Health Care Information Codes segment
                //  -- to give all information in the segment, we need to
                //     lookup all the code values ICD-9, ICD-10, LOINC, DRG, etc.
                //  -- At this time, give the code source and code, with dates if any
                //
                $sar = explode($de, $seg);
                //
                $hi_str = '';
                $tr_str = '';
                foreach ($sar as $hi) {
                    if (strpos($hi, $ds)) {
                        $a = explode($ds, $hi);
                        $hi_str .= (isset($a[0]) && $a[0]) ? $cd27x->get_271_code('HI01', $a[0]) : "";
                        $hi_str .= (isset($a[1]) && $a[1]) ? '[ ' . $a[1] . ' ]' : '[]';
                        if (isset($a[2]) && isset($a[3])) {
                            $hi_str .= ($a[2] == 'D8') ? ' ' . edih_format_date($a[3]) : '';
                            $hi_str .= ($a[2] == 'RD8') ? edih_format_date(substr($a[3], 0, 8)) : "";
                            $hi_str .= ($a[2] == 'RD8') ? edih_format_date(substr($a[3], -8)) : "";
                        }
                    } else {
                        $hi_str .= $hi . ' ';
                    }

                    $tr_str .= "<tr class='" . attr($cls) . "'><td><em>Codes</em></td><td colspan=3>" . text($hi_str) . "</td></tr>" . PHP_EOL;
                }

                //
                if ($loopid == '2000E') {
                    $evt_html .= $tr_str;
                } elseif ($loopid == '2000F') {
                    $svc_html .= $tr_str;
                }

                //
                continue;
            }

            //
            if (strncmp('HSD' . $de, $seg, 4) === 0) {
                // Health Care Services Delivery
                $sar = explode($de, $seg);
                //
                $id = "Services Delivery";
                $tr_str = '';
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('HSD01', $sar[1]) : '';    // quantity qualifier
                $elem02 = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';                                   // numeric quantity
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('HSD01', $sar[3]) : '';    // measurement unit
                $elem04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : '';                                   // sample selection modulus
                $elem05 = (isset($sar[5]) && $sar[5]) ? $cd27x->get_271_code('EB06', $sar[5]) : ''; // time period qualifier
                $elem06 = (isset($sar[6]) && $sar[6]) ? $sar[6] : '';                                   // number of periods
                $elem07 = (isset($sar[7]) && $sar[7]) ? $cd27x->get_271_code('HSD07', $sar[7]) : '';    // delivery
                $elem08 = (isset($sar[8]) && $sar[8]) ? $cd27x->get_271_code('HSD08', $sar[8]) : '';    // delivery
                //
                if (($elem01 || $elem02) && !$elem08) {
                    $tr_str .= ($elem03 && $elem04 && $elem06) ? "<tr class='" . attr($cls) . "'><td><em>" . text($id) . "</em></td><td colspan=3>" . text($elem02 . " " . $elem01) . " per every " . text($elem04 . " " . $elem03) . " for " . text($elem06 . " " . $elem05 . " " . $elem07) . "</td></tr>" . PHP_EOL : "";
                    $tr_str .= ($elem03 && $elem04 && !$elem06) ? "<tr class='" . attr($cls) . "'><td><em>" . text($id) . "</em></td><td colspan=3>" . text($elem02 . " " . $elem01) . " per every " . text($elem04 . " " . $elem03 . " " . $elem07) . "</td></tr>" . PHP_EOL : "";
                    $tr_str .= ($elem03 && !$elem04 && $elem06) ? "<tr class='" . attr($cls) . "'><td><em>" . text($id) . "</em></td><td colspan=3>" . text($elem02 . " " . $elem01) . " for " . text($elem06 . " " . $elem05 . " " . $elem07) . "</td></tr>" . PHP_EOL : "";
                    $tr_str .= ($elem03 && !$elem04 && !$elem06) ? "<tr class='" . attr($cls) . "'><td><em>" . text($id) . "</em></td><td colspan=3>" . text($elem02 . " " . $elem01 . " " . $elem07) . "</td></tr>" . PHP_EOL : "";
                } else {
                    $tr_str .= "<tr class='" . attr($cls) . "'><td><em>" . text($id) . "</em></td><td colspan=3>" . text($elem02 . " " . $elem01 . " " . $elem07 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                }

                //
                if ($loopid == '2000E') {
                    $evt_html .= $tr_str;
                } elseif ($loopid == '2000F') {
                    $svc_html .= $tr_str;
                }

                continue;
            }

            //
            if (strncmp('CRC' . $de, $seg, 4) === 0) {
                // Certification
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('CRC01', $sar[1]) : '';    // certification type code
                $elem02 = (isset($sar[2]) && $sar[2]) ? $cd27x->get_271_code('HCR04', $sar[2]) : '';    // condition indicater
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('CRC03', $sar[3]) : '';    // condition descripter
                $elem04 = (isset($sar[4]) && $sar[4]) ? $cd27x->get_271_code('CRC03', $sar[4]) : '';    // condition descripter
                $elem05 = (isset($sar[5]) && $sar[5]) ? $cd27x->get_271_code('CRC03', $sar[5]) : '';    // condition descripter
                $elem06 = (isset($sar[6]) && $sar[6]) ? $cd27x->get_271_code('CRC03', $sar[6]) : '';    // condition descripter
                $elem07 = (isset($sar[7]) && $sar[7]) ? $cd27x->get_271_code('CRC03', $sar[7]) : '';    // condition descripter
                //
                $evt_html .= ($elem01) ? "<tr class='" . attr($cls) . "'><td><em>" . text($elem01) . "</em></td><td colspan=3><em>Conditions Apply:</em> " . text($elem02) . "</td></tr>" . PHP_EOL : "";
                $evt_html .= ($elem03) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03) . "</td></tr>" . PHP_EOL : "";
                $evt_html .= ($elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem04) . "</td></tr>" . PHP_EOL : "";
                $evt_html .= ($elem05) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem05) . "</td></tr>" . PHP_EOL : "";
                $evt_html .= ($elem06) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem06) . "</td></tr>" . PHP_EOL : "";
                $evt_html .= ($elem07) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem07) . "</td></tr>" . PHP_EOL : "";
                //
                continue;
            }

            //
            if (strncmp('CL1' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                //
                $tr_str = '';
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('CL101', $sar[1]) : '';    // admission type code
                $elem02 = (isset($sar[2]) && $sar[2]) ? $cd27x->get_271_code('CL102', $sar[2]) : '';    // admission source code
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('CL103', $sar[3]) : '';    // patient status code
                //
                $tr_str .= "<tr class='" . attr($cls) . "'><td><em>Hospital</em> </td><td colspan=3>" . text($elem01  . " " . $elem02) . "</td></tr>" . PHP_EOL;
                $tr_str .= ($elem03) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03) . "</td></tr>" . PHP_EOL : "";
                //
                if ($loopid == '2000E') {
                    $evt_html .= $tr_str;
                } elseif ($loopid == '2000F') {
                    $svc_html .= $tr_str;
                }

                continue;
            }

            //
            if (strncmp('CR1' . $de, $seg, 4) === 0) {
                //  ambulance
                $sar = explode($de, $seg);
                //
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('CR103', $sar[3]) : '';    // ambulance transit code
                if (isset($sar[5])) {
                    if ($sar[5] == 'DH') {
                        $elem05 = "miles";
                    } elseif ($sar[5] == 'DK') {
                        $elem05 = "kilometers";
                    } else {
                        $elem05 = "";
                    }
                } else {
                    $elem05 = "";
                }

                $elem06 = (isset($sar[6])) ? $sar[6] : "";
                //
                $evt_html .= "<tr class='" . attr($cls) . "'><td><em>Ambulance:</em></td><td colspan=3>" . text($elem03 . " " . $elem06 . " " . $elem05) . "</td></tr>" . PHP_EOL;
                //
                continue;
            }

            //
            if (strncmp('CR2' . $de, $seg, 4) === 0) {
                //  spinal manipulation
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : "";
                $elem02 = (isset($sar[2]) && $sar[2]) ? $sar[2] : "";
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('CR203', $sar[3]) : "";    // spinal code
                $elem04 = (isset($sar[4]) && $sar[4]) ? $cd27x->get_271_code('CR203', $sar[4]) : "";    // spinal code
                $elem04 = ($elem04) ? "-- " . $elem04 : "";
                // elem05 -- elem12 not used
                //
                $evt_html .= "<tr class='" . attr($cls) . "'><td><em>Spinal:</em></td><td colspan=3> " . text($elem01 . " " . $elem02 . " " . $elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL;
                //
                continue;
            }

            //
            if (strncmp('CR5' . $de, $seg, 4) === 0) {
                //  oxygen
                $sar = explode($de, $seg);
                //
                $tr_str = '';
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('CR503', $sar[3]) : "";    // oxygen code
                $elem04 = (isset($sar[4]) && $sar[4]) ? $cd27x->get_271_code('CR503', $sar[4]) : "";    // oxygen code
                $elem06 = (isset($sar[6]) && $sar[6]) ? "Flow (lpm): " . $sar[6] : "";
                $elem07 = (isset($sar[7]) && $sar[7]) ? "Times/day: " . $sar[7] : "";
                $elem08 = (isset($sar[8]) && $sar[8]) ? "Hours: " . $sar[8] : "";
                $elem09 = (isset($sar[9]) && $sar[9]) ? $sar[9] : "";
                $elem16 = (isset($sar[16]) && $sar[16]) ? "Flow (lpm): " . $sar[16] : "";
                $elem17 = (isset($sar[17]) && $sar[17]) ? $cd27x->get_271_code('CR517', $sar[17]) : "";
                $elem18 = (isset($sar[18]) && $sar[18]) ? $cd27x->get_271_code('CR503', $sar[18]) : "";
                //
                $title = "Descriptors and Rates/times per edi guide. <b>Do not rely on these!</b>" . PHP_EOL;
                $t2 = "Descriptors per edi companion guide. <b>Do not rely on these!</b>" . PHP_EOL;
                //
                $tr_str .= "<tr class='" . attr($cls) . "'><td><em>Oxygen</em></td><td colspan=3><b>You must independently verify this information!</b></td></tr>" . PHP_EOL;
                $tr_str .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>$title</td></tr>" . PHP_EOL;
                $tr_str .= ($elem03 || $elem04 ) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='$t2'><em>Equipment Type: </em>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL : "";
                $tr_str .= ($elem06 || $elem07 || $elem08) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='$title'>" . text($elem06 . " " . $elem07 . " " . $elem08) . "</td></tr>" . PHP_EOL : "";
                $tr_str .= ($elem09) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Therapist: </em>" . text($elem09) . "</td></tr>" . PHP_EOL : "";
                $tr_str .= ($elem16) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='$title'>" . text($elem16) . "</td></tr>" . PHP_EOL : "";
                $tr_str .= ($elem17) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='$t2'><em>Delivery: </em>" . text($elem17) . "</td></tr>" . PHP_EOL : "";
                $tr_str .= ($elem18) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='$t2'><em>Equipment Type: </em>" . text($elem18) . "</td></tr>" . PHP_EOL : "";
                //
                $evt_html .= $tr_str;
                //
                continue;
            }

            //
            if (strncmp('CR6' . $de, $seg, 4) === 0) {
                //  oxygen
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('UM07', $sar[1]) : ""; // patient condition code
                $elem02 = (isset($sar[2]) && $sar[2]) ? edih_format_date($sar[2]) : "";             // HH start date
                $elem03 = (isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                $elem04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : "";
                if ($elem03 == 'D8' && $elem04) {
                    $elem04 = edih_format_date($elem04);
                } elseif ($elem03 == 'RD8' && $elem04) {
                    $var = edih_format_date(substr($dtp03, 0, 8));
                    $elem04 = $var . ' - ' . edih_format_date(substr($elem04, -8));
                }

                $elem07 = (isset($sar[7]) && $sar[7]) ? $cd27x->get_271_code('HCR04', $sar[8]) : "";
                $elem08 = (isset($sar[8]) && $sar[8]) ? $cd27x->get_271_code('CR608', $sar[8]) : "";    // certification type code
                //
                $evt_html .= "<tr class='" . attr($cls) . "'><td><em>Home Health</em></td><td><em>HH Began</em> " . text($elem02) . "</td><td colspan=2>" . text($elem01) . "</td></tr>" . PHP_EOL;
                $evt_html .= ($elem03 || $elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>Period</em> " . text($elem04) . "</td><td>(MCR) " . text($elem07) . "</td></tr>" . PHP_EOL : "";
                $evt_html .= ($elem08) ?  "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>Period</em> " . text($elem08) . "</td><td>(MCR) " . attr($elem05) . "</td></tr>" . PHP_EOL : "";
                //
                continue;
            }

            //
            if (strncmp('PWK' . $de, $seg, 4) === 0) {
                //  paperwork
                $sar = explode($de, $seg);
                //
                $tr_str = '';
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('PWK01', $sar[1]) : "";    // paperwork code
                $elem02 = (isset($sar[2]) && $sar[2]) ? $cd27x->get_271_code('PWK02', $sar[2]) : "";    // delivery code
                $elem05 = (isset($sar[5]) && $sar[5] == 'AC') ? "Attachment Control Number" : "";
                $elem06 = (isset($sar[6]) && $sar[6]) ? $sar[6] : "";
                $elem07 = (isset($sar[7]) && $sar[7]) ? $sar[7] : "";
                //
                $tr_str .= "<tr class='" . attr($cls) . "'><td><em>Paperwork</em></td><td colspan=3>" . text($elem01 . " " . $elem02) . "</td></tr>" . PHP_EOL;
                $tr_str .= ($elem05) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem05 . " " . $elem06) . "</td></tr>" . PHP_EOL : "";
                $tr_str .= ($elem07) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem07) . "</td></tr>" . PHP_EOL : "";
                //
                if ($loopid == '2000E') {
                    $evt_html .= $tr_str;
                } elseif ($loopid == '2000F') {
                    $svc_html .= $tr_str;
                }

                continue;
            }

            //
            if (strncmp('MSG' . $de, $seg, 4) === 0) {
                //  paperwork
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : "";
                //
                if ($loopid == '2000E') {
                    $evt_html .= ($elem01) ? "<tr class='" . attr($cls) . "'><td><em>Message</em></td><td colspan=3>" . text($elem01) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2000F') {
                    $svc_html .= ($elem01) ? "<tr class='" . attr($cls) . "'><td><em>Message</em></td><td colspan=3>" . text($elem01) . "</td></tr>" . PHP_EOL : "";
                }

                continue;
            }

            //
            if (strncmp('SV1' . $de, $seg, 4) === 0) {
                //  professional services
                $sar = explode($de, $seg);
                //

                $elem01 = '';
                if (isset($sar[1]) && strpos($sar[1], $ds)) {
                    $ar01 = explode($ds, $sar[1]);
                    $ct = count($ar01);
                    $elem01 = (isset($ar01[0]) && $ar01[0]) ? $cd27x->get_271_code('SV101', $ar01[0]) : "";
                    //
                    if ($ct == 8) {
                        $ct = 7;
                        $elem01 .= " " . $ar01[1] . " --  " . $ar01[8];
                    } else {
                        $elem01 .= " " . $ar01[1];
                    }

                    if ($elem01 && $ct > 2) {
                        for ($i = 2; $i < $ct; $i++) {
                            $elem01 .= ' ' . $ar01[$i];
                        }
                    }
                } elseif (isset($sar[1]) && $sar[1]) {
                    $elem01 =  $sar[1];
                }

                $elem02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('SV103', $sar[3]) : "";
                $elem04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : "";
                $elem11 = (isset($sar[11]) && $sar[11]) ? $cd27x->get_271_code('HCR04', $sar[11]) : "";
                $elem20 = (isset($sar[20]) && $sar[20]) ? $cd27x->get_271_code('SV120', $sar[20]) : "";
                //
                $elem11 = ($elem11) ? "<em>EPSDT</em> " . $elem11 : "";
                //
                $svc_html .= ($elem01) ? "<tr class='" . attr($cls) . "'><td><em>Professional Svc</em></td><td colspan=3>" . text($elem01 . " " . $elem02) . "</td></tr>" . PHP_EOL : "";
                $svc_html .= ($elem03 || $elem04 || $elem11) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04 . " " . $elem11) . "</td></tr>" . PHP_EOL : "";
                $svc_html .= ($elem20) ? "<tr class='$cls'><td>&gt;</td><td colspan=3>$elem20</td></tr>" . PHP_EOL : "";
                //
                continue;
            }

            //
            if (strncmp('SV2' . $de, $seg, 4) === 0) {
                //  paperwork
                $sar = explode($de, $seg);
                //

                $elem01 = '';
                if (isset($sar[1]) && strpos($sar[1], $ds)) {
                    $ar01 = explode($ds, $sar[1]);
                    $ct = count($ar01);
                    $elem01 = (isset($ar01[0]) && $ar01[0]) ? $cd27x->get_271_code('SV101', $ar01[0]) : "";
                    //
                    if ($ct == 8) {
                        $ct = 7;
                        $elem01 .= " " . $ar01[1] . " --  " . $ar01[8];
                    } else {
                        $elem01 .= " " . $ar01[1];
                    }

                    if ($elem01 && count($ar01) > 2) {
                        for ($i = 2; $i < $ct; $i++) {
                            $elem01 .= ' ' . $ar01[$i];
                        }
                    }
                } elseif (isset($sar[1]) && $sar[1]) {
                    $elem01 =  $sar[1];
                }

                $elem02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";
                $elem03 = (isset($sar[3]) && $sar[3]) ? $cd27x->get_271_code('SV103', $ar01[3]) : "";
                $elem04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : "";
                $elem05 = (isset($sar[5]) && $sar[5]) ? $sar[5] : "";
                $elem06 = (isset($sar[6]) && $sar[6]) ? edih_format_money($sar[6]) : "";
                $elem10 = (isset($sar[20]) && $sar[20]) ? $cd27x->get_271_code('SV120', $ar01[20]) : "";
                //
                $svc_html .= ($elem01) ? "<tr class='" . attr($cls) . "'><td><em>Inst Service</em></td><td colspan=3>" . text($elem01 . " " . $elem02) . "</td></tr>" . PHP_EOL : "";
                $svc_html .= ($elem03 || $elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04 . " " . $elem05 . " " . $elem06) . "</td></tr>" . PHP_EOL : "";
                $svc_html .= ($elem10) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem10) . "</td></tr>" . PHP_EOL : "";
                //
                continue;
            }
        }

        //
        $str_html .= $hdr_html;
        $str_html .= ($src_html) ? $src_html : "";
        $str_html .= ($rcv_html) ? $rcv_html : "";
        $str_html .= ($sbr_nm1_html) ? $sbr_nm1_html : "";
        $str_html .= ($dep_nm1_html) ? $dep_nm1_html : "";
        $str_html .= ($evt_html) ? $evt_html : "";
        $str_html .= ($svc_html) ? $svc_html : "";
        $str_html .= "<tr><td colspan=4>&nbsp;</td></tr>" . PHP_EOL;
        $str_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
    }

    //
    return $str_html;
}

/**
 * create a display for an individual authorization response
 *
 * @uses csv_check_x12_obj()
 * @uses edih_278_transaction_html
 *
 * @param string  $filename the filename
 * @param string  $bht03 identifier from 837 CLM or27 BHT segment
 *
 * @return string  either an error message or an html table
 */
function edih_278_html($filename, $bht03 = '')
{
    // create a display for an individual 277 response
    $html_str = '';
    //
    if (!$filename) {
        csv_edihist_log("edih_278_html: called with no file arguments");
        $html_str .= "Error, no file given<br />" . PHP_EOL;
        return $html_str;
    } else {
        $obj278 = csv_check_x12_obj($filename, 'f278');
        if ($obj278 && 'edih_x12_file' == get_class($obj278)) {
            if ($bht03) {
                // particular transaction
                $html_str .= edih_278_transaction_html($obj278, $bht03);
            } else {
                // file contents
                $env_ar = $obj278->edih_envelopes();
                if (!isset($env_ar['ST'])) {
                    $html_str .= "<p>edih_278_html: file parse error, envelope error</p>" . PHP_EOL;
                    $html_str .= text($obj278->edih_message());
                    return $html_str;
                } else {
                    $html_str .= "<div id='accordion'>" . PHP_EOL;
                }

                foreach ($env_ar['ST'] as $st) {
                    foreach ($env_ar['GS'] as $gs) {
                        if ($gs['icn'] != $st['icn']) {
                            continue;
                        }

                        if ($gs['gsn'] == $st['gsn']) {
                            $gs_date = edih_format_date($gs['date']);
                            $gs_sender = $gs['sender'];
                            break;
                        }
                    }

                    //
                    // get each transaction
                    foreach ($st['bht03'] as $bht) {
                        $html_str .= "<h3>" . text($bht) . " Services Review</h3>" . PHP_EOL;
                        $html_str .= "<div id='ac_" . attr($bht) . "'>" . PHP_EOL;
                        //
                        $html_str .= edih_278_transaction_html($obj278, $bht);
                        //
                        $html_str .= "</div>" . PHP_EOL;
                    }

                    $html_str .= "</div>" . PHP_EOL;
                }
            }
        } else {
            csv_edihist_log("edih_278_html: error in retreiving file object");
            $html_str .= "<p>x12 278 file parse error</p>" . PHP_EOL;
        }
    }

    //
    return $html_str;
}
