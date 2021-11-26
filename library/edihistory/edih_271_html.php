<?php

/*
 * edih_271_html.php
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
// require_once("$srcdir/edihistory/codes/edih_271_code_class.php");
//

/**
 * Produce an html display of information in
 * the x12 edi 271 eligibility report for a particular patient
 *
 * @uses edih_271_codes()
 * @uses edih_format_money()
 * @uses edih_format_date()
 * @uses edih_format_percent()
 *
 * @param object   edih_x12_file type 271
 * @param string   bht03 or clm01 reference for transaction
 * @return string
 */
function edih_271_transaction_html($obj271, $bht03)
{
    //
    $trans = $obj271->edih_x12_transaction($bht03);
    if (empty($trans) || !count($trans)) {
        $str_html = "<p>Did not find transaction " . text($bht03) . " in " . text($obj271->edih_filename()) . "</p>";
        return $str_html;
    }

    $de = $obj271->edih_delimiters()['e'];
    $ds = $obj271->edih_delimiters()['s'];
    $dr = $obj271->edih_delimiters()['r'];
    $fn = $obj271->edih_filename();
    //
    $cd271 = new edih_271_codes($ds, $dr);
    //
    $str_html = "";
    //
    $hdr_html = "<table id=" . attr($bht03) . " class='h271' columns=4><caption>Eligibility Benefit Response</caption>" . PHP_EOL;
    $hdr_html .= "<thead>" . PHP_EOL;
    $hdr_html .= "<tr><th>Reference</th><th colspan=2>Information</th><th colspan=2>" . text($fn) . "</th></tr>" . PHP_EOL;
    $hdr_html .= "</thead>" . PHP_EOL . "<tbody>" . PHP_EOL;
    $src_html = "";
    $rcv_html = "";
    $sbr_nm1_html = "";
    $dep_nm1_html = "";
    $sbr_ref_html = "";
    $dep_ref_html = "";
    $sbr_eb_html = "";
    $dep_eb_html = "";
    //
    $ebct = 0;
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
            //
            if (strncmp('BHT' . $de, $seg, 4) === 0) {
                $loopid = 'Heading';
                $sar = explode($de, $seg);
                $bht01 = ( isset($sar[1]) && $sar[1] == '022') ? "Src, Rcv, Sbr, Dep" : "Order unspecified";
                $bht02 = ( isset($sar[2]) && $sar[2] == '11') ? "Response" : "Confirmation";
                $bht03 = ( isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                $bht04 = ( isset($sar[4]) && $sar[4]) ? edih_format_date($sar[4]) : "";
                //
                $hdr_html .= "<tr><td><em>Reference:</em> " . text($bht03) . "</td><td><em>Date:</em> " . text($bht04) . "</td><td><em>Type:</em> " . text($bht02) . "</td><td>" . text($bht01) . "</td></tr>" . PHP_EOL;
                continue;
            }

            //
            if (strncmp('HL' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                if ($sar[3] == '20') {                      // level code
                    $loopid = '2000A';                      // info source (payer)
                    $src_html .= "<tr><td colspan=4><b>Information Source</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == '21') {
                    $loopid = '2000B';                      // info receiver (clinic)
                    $rcv_html .= "<tr><td colspan=4><b>Information Receiver</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == '22') {
                    $loopid = '2000C';                      // subscriber
                    $has_eb = false;
                    $sbr_nm1_html .= "<tr><td colspan=4><b>Subscriber</b></td></tr>" . PHP_EOL;
                } elseif ($sar[3] == '23') {
                    $loopid = '2000D';                      // dependent
                    $has_eb = false;
                    $dep_nm1_html .= "<tr><td colspan=4><b>Dependent</b></td></tr>" . PHP_EOL;
                }

                //
                $ebct = 0;
                continue;
            }

            //
            if (strncmp('AAA' . $de, $seg, 4) === 0) {
                // rejection
                $sar = explode($de, $seg);
                $rej_reason = $cd271->get_271_code('AAA03', $sar[3]);
                $follow = $cd271->get_271_code('AAA04', $sar[4]);
                if ($loopid == '2000A') {
                    $src_html .= "<tr><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000B') {
                    $rcv_html .= "<tr><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000C') {
                    $sbr_nm1_html .= "<tr><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2000D') {
                    $dep_nm1_html .= "<tr><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow)  . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110C') {
                    $sbr_eb_html .= "<tr class=$cls><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110D') {
                    $dep_eb_html .= "<tr class=$cls><td><em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                } else {
                    $hdr_html .= "<tr><td>$loopid <em><b>Rejection:</b></em></td><td colspan=3 title='" . attr($follow) . "'>" . text($rej_reason) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            if (strncmp('NM1' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $descr = (isset($sar[1]) && $sar[1] ) ? $cd271->get_271_code('NM101', $sar[1]) : "";
                //
                $name = (isset($sar[3]) && $sar[3] ) ? $sar[3] : "";
                $name .= (isset($sar[7]) && $sar[7]) ? " {$sar[7]}" : "";
                $name .= (isset($sar[4]) && $sar[4]) ? ", {$sar[4]}" : "";
                $name .= (isset($sar[5]) && $sar[5]) ? " {$sar[5]}" : "";
                $name .= (isset($sar[7]) && $sar[7]) ? " {$sar[7]}" : "";
                //
                $idtype = (isset($sar[8]) && $sar[8] ) ? $cd271->get_271_code('NM108', $sar[8]) : "";
                $nm109 = (isset($sar[9]) &&  $sar[9]) ? $sar[9] : "";
                //
                if ($loopid == '2000A') {
                    $src_html .= "<tr><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100A';
                } elseif ($loopid == '2000B') {
                    $rcv_html .= "<tr><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100B';
                } elseif ($loopid == '2000C') {
                    $sbr_nm1_html .= "<tr><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100C';
                } elseif ($loopid == '2000D') {
                    $dep_nm1_html .= "<tr><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100D';
                } elseif ($loopid == '2120C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=2 title='" . attr($descr) . "'>" . text($descr . " " . $name) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td title='" . attr($idtype) . "'>" . text($nm109) . "</td><td colspan=2 title='" . attr($descr) . "'>" . text($descr . " " . $name) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('PER' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $per02 = (isset($sar[2]) &&  $sar[2]) ? $sar[2] : '';
                $idtype = (isset($sar[3]) &&  $sar[3]) ? $cd271->get_271_code('PER03', $sar[3]) : "";
                $per04 = (isset($sar[4]) &&  $sar[2]) ? $sar[4] : '';
                if ($loopid == '2100A') {
                    $src_html .= "<tr><td colspan=3>$per02</td><td title='" . attr($idtype) . "'>$per04</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td title='" . attr($idtype) . "'>" . text($per04) . "</td><td colspan=2>" . text($per02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td title='" . attr($idtype) . "'>" . text($per04) . "</td><td colspan=2>" . text($per02) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('N3' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                $addr = (isset($sar[1])) ? $sar[1] : "";
                $addr .= (isset($sar[2])) ? " {$sar[2]}" : "";
                if ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($addr) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('N4' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                if ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($sar[1] . " " . $sar[2] . " " . $sar[3]) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('PRV' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $idtype = ($sar[1]) ? $cd271->get_271_code('PRV', $sar[1]) : "";
                if ($loopid == '2100B') {
                    $src_html .= "<tr><td colspan=3>" . text($sar[2]) . "</td><td title='" . attr($idtype) . "'>" . text($sar[3]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td title='" . attr($idtype) . "'>" . text($sar[3]) . "</td><td colspan=3>" . text($sar[2]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td title='" . attr($idtype) . "'>" . text($sar[3]) . "</td><td colspan=3>" . text($sar[2]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td title='" . attr($idtype) . "'>" . text($sar[3]) . "</td><td colspan=2>" . text($sar[2]) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2120D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td title='" . attr($idtype) . "'>" . text($sar[3]) . "</td><td colspan=2>" . text($sar[2]) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('DMG' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $dmg02 = (isset($sar[2]) && $sar[2]) ? edih_format_date($sar[2]) : "";
                if (isset($sar[3]) && $sar[3]) {
                    if ($sar[3] == 'M') {
                        $dmg03 = "Male";
                    } elseif ($sar[3] == 'F') {
                        $dmg03 = "Female";
                    } else {
                        $dmg03 = "Unknown";
                    }
                }

                if ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td>&gt;</td><td>" . text($dmg03) . "</td><td colspan=2><em>Date of Birth</em> " . text($dmg02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td>&gt;</td><td>" . text($dmg03) . "</td><td colspan=2><em>Date of Birth</em> " . text($dmg02) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            if (strncmp('INS' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $ins01 = (isset($sar[1]) && $sar[1] == 'Y') ? "Subscriber" : "Dependent";
                $ins02 = (isset($sar[2]) && $sar[2]) ? $cd271->get_271_code('INS02', $sar[2]) : "";
                $ins03 = (isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                $ins04 = (isset($sar[4]) && $sar[4] == '25') ? "<b>Information changed</b>" : "";
                $ins17 = (isset($sar[17]) && $sar[17]) ? $sar[17] : "";
                //
                if ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td>&gt;</td><td>" . text($ins01 . " " . $ins17) . "</td><td>" . text($ins02) . "</td><td>" . text($ins03) . " " . $ins04 . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td>&gt;</td><td>" . text($ins01 . " " . $ins17) . "</td><td>" . text($ins02) . "</td><td>" . text($ins03) . " " . $ins04 . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            if (strncmp('DTP' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                $var = '';
                $dtp01 = (isset($sar[1])) ? $sar[1] : '';
                $dtp02 = (isset($sar[2])) ? $sar[2] : '';
                $dtp03 = (isset($sar[3])) ? $sar[3] : '';
                //
                $idtype = ($dtp01) ? $cd271->get_271_code('DTP', $dtp01) : "";
                if ($dtp02 == 'D8' && $dtp03) {
                    $var = edih_format_date($dtp03);
                } elseif ($dtp02 == 'RD8' && $dtp03) {
                    $var = edih_format_date(substr($dtp03, 0, 8));
                    $var .= ' - ' . edih_format_date(substr($dtp03, -8));
                }

                if ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td>&gt;</td><td>" . text($idtype) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td>&gt;</td><td>" . text($idtype) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td>" . text($idtype) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td>" . text($idtype) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('MPI' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $idtype = (isset($sar[1]) &&  $sar[1]) ? $cd271->get_271_code('MPI', $sar[1]) : "";
                $idtype .= (isset($sar[2]) &&  $sar[2]) ? $cd271->get_271_code('MPI', $sar[2]) : "";
                $idtype .= (isset($sar[3]) &&  $sar[3]) ? $cd271->get_271_code('MPI', 'SB' . $sar[3]) : "";
                if (isset($sar[7]) && isset($sar[8])) {
                    if ($sar[7] == 'D8') {
                        $var = edih_format_date($sar[8]);
                    } elseif ($sar[7] == 'RD8') {
                        $var = edih_format_date(substr($sar[8], 0, 8));
                        $var .= ' - ' . edih_format_date(substr($sar[8], -8));
                    }
                }

                if ($loopid == '2100C') {
                    $sbr_nm1_html .= "<tr><td colspan=3>" . text($idtype) . "</td><td>" . text($var) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_nm1_html .= "<tr><td colspan=3>" . text($idtype) . "</td><td>" . text($var) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp('EB' . $de, $seg, 3) === 0) {
                //
                $ebct++;
                $cls = ($ebct % 2) ? 'ebe' : 'ebo';
                $sar = explode($de, $seg);
                //
                $eb01 = $cd271->get_271_code('EB01', $sar[1]);                                      // eligibility or benefit
                $eb02 = (isset($sar[2]) && $sar[2]) ? $cd271->get_271_code('EB02', $sar[2]) : '';   // coverage level
                $eb03 = (isset($sar[3]) && $sar[3]) ? $cd271->get_271_code('EB03', $sar[3]) : '';   // service type
                $eb04 = (isset($sar[4]) && $sar[4]) ? $cd271->get_271_code('EB04', $sar[4]) : '';   // insurance type
                $eb05 = (isset($sar[5]) && $sar[5]) ? $sar[5] : '';                                 // descriptive (plan name)
                $eb06 = (isset($sar[6]) && $sar[6]) ? $cd271->get_271_code('EB06', $sar[6]) : '';   // time qualifier
                $eb07 = (isset($sar[7]) && strlen($sar[7])) ? edih_format_money($sar[7]) : '';      // monetary amount
                $eb08 = (isset($sar[8]) && $sar[8]) ? edih_format_percent($sar[8]) : '';                // percentage amount
                $eb09 = (isset($sar[9]) && $sar[9]) ? $cd271->get_271_code('EB09', $sar[9]) : '';   // Quantity qualifier
                $eb10 = (isset($sar[10]) && $sar[10]) ? $sar[10] : '';                              // quantity
                $eb11 = (isset($sar[11]) && $sar[11]) ? $cd271->get_271_code('EB11', $sar[11]) : '';    // authorization required?
                $eb12 = (isset($sar[12]) && $sar[12]) ? $cd271->get_271_code('EB12', $sar[12]) : '';    // in network?
                $eb13 = "";
                if (isset($sar[13]) && strpos($sar[13], $ds)) {                                     // composite procedure ID
                    $eb13ar = explode($ds, $sar[13]);
                    reset($eb13ar);
                    foreach ($eb13ar as $k => $v) {
                        if ($k == 0) {
                            $eb13 = text($cd271->get_271_code('EB13', $v));
                        } else {
                            $eb13 .= " " . text($v);
                        }
                    }
                } else {
                    $eb13 = ($sar[13]) ? "<em>Procedure</em> " . text($eb13) : "";
                }

                $eb14 = "";
                if (isset($sar[14])) {
                    if (strpos($sar[14], $ds)) {                                        // composite diagnosis pointer
                        $eb14 = str_replace($ds, " | ", $sar[14]) ;
                    } else {
                        $eb14 = $sar[14];
                    }

                    $eb14 = ($eb14) ? "<em>Pointers</em> " . text($eb14) : "";
                }

                // if LS - LE segments loop should be 2110C or 2110D
                if ($loopid == '2100C' || $loopid == '2110C') {
                    $loopid = '2110C';
                    if (strpos('|A|B', $sar[1]) !== false) {
                        $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb07 . " " . $eb08) . "</td><td colspan=2>" . text($eb02 . " " . $eb03 . " " . $eb04 . " " . $eb06) . "</td></tr>" . PHP_EOL;
                    } elseif (strpos('|C|G|J|Y', $sar[1]) !== false) {
                        $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb02) . "</td><td>" . text($eb06 . " " . $eb07) . "</td><td>" . text($eb03 . " " . $eb04) . "</td></tr>" . PHP_EOL;
                    } elseif (strpos('|E|F|', $sar[1]) !== false) {
                        $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb02) . "</td><td colspan=2>" . text($eb03 . " " . $eb04 . " " . $eb06) . "</td></tr>" . PHP_EOL;
                    } else {
                        $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb02) . "</td><td colspan=3>" . text($eb07 . " " . $eb08 . " " . $eb03 . " " . $eb04 . " " . $eb06) . "</td></tr>" . PHP_EOL;
                    }

                    $sbr_eb_html .= ($eb09 || $eb10 || $eb11 || $eb12) ? "<tr class=" . attr($cls) . "><td colspan=2>&gt;</td><td colspan=2>" . text($eb09 . " " . $eb10 . " " . $eb11 . " " . $eb12) . "</td></tr>" . PHP_EOL : "";
                    $sbr_eb_html .= ($eb13 || $eb14) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . $eb13 . " " . $eb14 . "</td></tr>" . PHP_EOL : "";
                    $sbr_eb_html .= ($eb05) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($eb05) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2100D' || $loopid == '2110D') {
                    $loopid = '2110D';
                    if (strpos('|A|B', $eb01) && !$eb02) {
                        $dep_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($ebo7 . " " . $eb08) . "</td><td colspan=2>" . text($eb03 . " " . $eb04 . " " . $eb06) . "</td></tr>" . PHP_EOL;
                    } elseif (strpos('|C|G|J|Y', $eb01)) {
                        $dep_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb02) . "</td><td>" . text($eb07 . " " . $eb08) . "</td><td>" . text($eb11 . " " . $eb12) . "</td></tr>" . PHP_EOL;
                    } elseif (strpos('|E|F|', $eb01)) {
                        $dep_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb02) . "</td><td colspan=2>" . text($eb03 . " " . $eb04 . " " . $eb06) . "</td></tr>" . PHP_EOL;
                    } else {
                        $dep_eb_html .= "<tr class=" . attr($cls) . "><td>" . text($eb01) . "</td><td>" . text($eb02) . "</td><td colspan=3>" . text($eb07 . " " . $eb08 . " " . $eb03 . " " . $eb04 . " " . $eb06) . "</td></tr>" . PHP_EOL;
                    }

                    $dep_eb_html .= ($eb09 || $eb10 || $eb11 || $eb12) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($eb09 . " " . $eb10 . " " . $eb11 . " " . $eb12) . "</td></tr>" . PHP_EOL : "";
                    $dep_eb_html .= ($eb13 || $eb14) ? "<tr class=$cls><td>&gt;</td><td colspan=3>" . $eb13 . " " . $eb14 . "</td></tr>" . PHP_EOL : "";
                    $dep_eb_html .= ($eb05) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($eb05) . "</td></tr>" . PHP_EOL : "";
                }

                continue;
            }

            //
            if (strncmp('HSD' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $hsd01 = (isset($sar[1]) && $sar[1]) ? $cd271->get_271_code('HSD01', $sar[1]) : ''; // quantity qualifier
                $hsd02 = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';                                    // numeric quantity
                $hsd03 = (isset($sar[3]) && $sar[3]) ? $cd271->get_271_code('HSD01', $sar[3]) : ''; // measurement unit
                $hsd04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : '';                                    // sample selection modulus
                $hsd05 = (isset($sar[5]) && $sar[5]) ? $cd271->get_271_code('EB06', $sar[5]) : '';  // time period qualifier
                $hsd06 = (isset($sar[6]) && $sar[6]) ? $sar[6] : '';                                    // number of periods
                $hsd07 = (isset($sar[7]) && $sar[7]) ? $cd271->get_271_code('HSD07', $sar[7]) : ''; // delivery
                $hsd08 = (isset($sar[8]) && $sar[8]) ? $cd271->get_271_code('HSD08', $sar[8]) : ''; // delivery
                //
                if ($loopid == '2110C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td>" . text($hsd01 . " : " . $hsd02) . "</td><td>" . text($hsd03 . " : " . $hsd04) . "</td><td>" . text($hsd05 . " : " . $hsd06) . "</td></tr>" . PHP_EOL;
                    $sbr_eb_html .= ($hsd07 || $hsd08) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($hsd07 . " " . $hsd08) . "</td></tr>" . PHP_EOL : '';
                } elseif ($loopid == '2110D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td>" . text($hsd01 . " : " . $hsd02) . "</td><td>" . text($hsd03 . " : " . $hsd04) . "</td><td>" . text($hsd05 . " : " . $hsd06) . "</td></tr>" . PHP_EOL;
                    $dep_eb_html .= ($hsd07 || $hsd08) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($hsd07 . " " . $hsd08) . "</td></tr>" . PHP_EOL : '';
                }

                continue;
            }

            if (strncmp('REF' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $ref01 = (isset($sar[1]) && $sar[1]) ? $cd271->get_271_code('REF', $sar[1]) : '';   // identification qualifier
                $ref02 = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';                                    // identification value
                $ref03 = (isset($sar[3]) && $sar[3]) ? $sar[3] : '';                                    // description
                //
                if ($loopid == '2100C') {
                    $sbr_ref_html .= "<tr><td>&gt;</td><td colspan=2>" . text($ref03) . "</td><td>" . text($ref01 . " " . $ref02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100D') {
                    $dep_ref_html .= "<tr><td>&gt;</td><td colspan=2>" . text($ref03) . "</td><td>" . text($ref01 . " " . $ref02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=2>" . text($ref03) . "</td><td>" . text($ref01 . " " . $ref02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=2>" . text($ref03) . "</td><td>" . text($ref01 . " " . $ref02) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            if (strncmp('MSG' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $msg01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';
                if ($msg01 && $loopid == '2110C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($msg01) . "</td></tr>" . PHP_EOL;
                } elseif ($msg01 && $loopid == '2110D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($msg01) . "</td></tr>" . PHP_EOL;
                }

                continue;
            }

            if (strncmp('III' . $de, $seg, 4) === 0 && ($loopid == '2110C' || $loopid == '2110D')) {
                $sar = explode($de, $seg);
                if (isset($sar[1]) && ($sar[1] == 'GR' || $sar[1] == 'NI')) {
                    $iii02 = (isset($sar[2]) && $sar[2]) ? $cd271->get_271_code('IIIGR', $sar[2]) : '';
                } else {
                    $iii02 = (isset($sar[2]) && $sar[2]) ? $cd271->get_271_code('POS', $sar[2]) : '';
                }

                $iii03 = (isset($sar[3]) && $sar[3]) ? $sar[3] : '';
                //
                if ($loopid == '2110C') {
                    $sbr_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($iii02) . "</td></tr>" . PHP_EOL;
                    $sbr_eb_html .= ($iii03) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($iii03) . "</td></tr>" . PHP_EOL : '';
                } elseif ($loopid == '2110D') {
                    $dep_eb_html .= "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($iii02) . "</td></tr>" . PHP_EOL;
                    $dep_eb_html .= ($iii03) ? "<tr class=" . attr($cls) . "><td>&gt;</td><td colspan=3>" . text($iii03) . "</td></tr>" . PHP_EOL : '';
                }

                continue;
            }

            //
            if (strncmp('LS' . $de, $seg, 3) === 0) {
                if ($loopid == '2110C') {
                    $loopid = '2120C';
                }

                if ($loopid == '2110D') {
                    $loopid = '2120D';
                }

                continue;
            }

            if (strncmp('LE' . $de, $seg, 3) === 0) {
                if ($loopid == '2120C') {
                    $loopid = '2100C';
                }

                if ($loopid == '2120D') {
                    $loopid = '2100D';
                }

                continue;
            }

            //
        }

        //
        $str_html .= $hdr_html;
        $str_html .= ($src_html) ? $src_html : "";
        $str_html .= ($rcv_html) ? $rcv_html : "";
        $str_html .= ($sbr_nm1_html) ? $sbr_nm1_html : "";
        $str_html .= $sbr_ref_html;
        $str_html .= $sbr_eb_html;
        $str_html .= ($dep_nm1_html) ? $dep_nm1_html : "";
        $str_html .= $dep_ref_html;
        $str_html .= $dep_eb_html;
        $str_html .= "<tr><td colspan=4>&nbsp;</td></tr>" . PHP_EOL;
        $str_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
    }

    //
    return $str_html;
}

/**
 * create a display for an individual claim status response
 *
 * @uses csv_check_x12_obj()
 * @uses edih_271_transaction_html()
 *
 * @param string  $filename the filename
 * @param string  $bht03 identifier from 837 CLM or BHT segment
 *
 * @return string  either an error message or an html table
 */
function edih_271_html($filename, $bht03 = '')
{
    // create a display for an individual 277 response
    $html_str = '';
    //
    if ($filename) {
        $obj271 = csv_check_x12_obj($filename, 'f271');
        if ('edih_x12_file' == get_class($obj271)) {
            if ($bht03) {
                // particular transaction
                $html_str .= edih_271_transaction_html($obj271, $bht03);
            } else {
                // file contents
                $env_ar = $obj271->edih_envelopes();
                if (!isset($env_ar['ST'])) {
                    $html_str .= "<p>edih_271_html: file parse error, envelope error</p>" . PHP_EOL;
                    $html_str .= text($obj271->edih_message());
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
                        $html_str .= "<h3>" . text($bht) . " Benefit Eligibility</h3>" . PHP_EOL;
                        $html_str .= "<div id='ac_" . attr($bht) . "'>" . PHP_EOL;
                        //
                        $html_str .= edih_271_transaction_html($obj271, $bht);
                        //
                        $html_str .= "</div>" . PHP_EOL;
                    }

                    $html_str .= "</div>" . PHP_EOL;
                }
            }
        } else {
            $html_str .= "<p>edih_271_html: file parse error</p>" . PHP_EOL;
        }
    } else {
        csv_edihist_log("edih_271_html: error in file arguments");
        $html_str .= "Error: invalid file name<br />" . PHP_EOL;
        return $html_str;
    }

    //
    return $html_str;
}
