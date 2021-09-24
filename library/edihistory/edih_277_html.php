<?php

/*
 * edih_277_html.php.php
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
function edih_277_transaction_html($obj277, $bht03, $accordion = false)
{
    // get the transaction segments
    $trans = $obj277->edih_x12_transaction($bht03);

    // get other necessary items
    $de = $obj277->edih_delimiters()['e'];
    $ds = $obj277->edih_delimiters()['s'];
    $dr = $obj277->edih_delimiters()['r'];
    $fn = $obj277->edih_filename();
    //
    if (!is_array($trans) || !count($trans)) {
        $str_html = "<p>Did not find transaction " . text($bht03) . " in " . attr($fn) . "</p>" . PHP_EOL;
        return $str_html;
    }

    //
    $cd27x = new edih_271_codes($ds, $dr);
    //
    $h3_lbl = '';
    $str_html = "";
    $hdr_html = "";
    //
    $hdr_html = "<table id=" . attr($bht03) . " class='h277' columns=4>";
    $hdr_html .= "<caption>Claim Status</caption>" . PHP_EOL;
    $hdr_html .= "<thead>" . PHP_EOL;
    $hdr_html .= "<tr><th>Reference</th><th>Information</th><th colspan=2>" . text($fn) . "</th></tr>" . PHP_EOL; //
    $hdr_html .= "</thead>" . PHP_EOL . "<tbody>" . PHP_EOL;
    //
    $src_html = "";
    $rcv_html = "";
    $prv_html = "";
    $sbr_nm1_html = "";
    $dep_nm1_html = "";
    $sbr_stc_html = "";
    $dep_stc_html = "";
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
            // echo "$i loop: $loopid Segment: $seg".PHP_EOL;
            //
            if (strncmp('BHT' . $de, $seg, 4) === 0) {
                $loopid = 'Heading';
                $sar = explode($de, $seg);
                if (isset($sar[1])) {
                    if ($sar[1] == '0010') {
                        $elem01 = "Src, Rcv, Prv, Sbr, Dep";
                    } elseif ($sar[1] == '0085') {
                        $elem01 = "Src, Rcv, Prv, Pt";
                    } else {
                        $elem01 = "Not determined ({$sar[1]})";
                    }
                } else {
                    $elem01 = '';
                }

                //
                $elem02 = ( isset($sar[2]) && $sar[2] !== false) ? $cd27x->get_271_code('BHT02', $sar[2]) : "";
                $elem03 = ( isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                $elem04 = ( isset($sar[4]) && $sar[4]) ? edih_format_date($sar[4]) : "";
                $elem06 = ( isset($sar[6]) && $sar[6]) ? $cd27x->get_271_code('BHT06', $sar[6]) : "";
                //
                $hdr_html .= "<tr><td colspan=2><em>Reference:</em> " . text($elem03) . "</td><td colspan=2><em>Sequence:</em> " . text($elem01) . "</td></tr>" . PHP_EOL;
                $hdr_html .= "<tr><td colspan=2><em>Date:</em> " . text($elem04) . "</td><td colspan=2><em>Type:</em> " . text($elem02) . "</td>" . PHP_EOL;
                $hdr_html .= ($elem06) ? "<tr><td>&gt;</td><td colspan=3><em>Type:</em> " . text($elem06) . "</td></tr>" . PHP_EOL : "";
                //
                $bht = $elem03;
                continue;
            }

            //
            if (strncmp('HL' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                $elem03 = ( isset($sar[3]) ) ? $sar[3] : "";
                if ($elem03 == '20') {                     // level code
                    $loopid = '2000A';                      // info source (payer)
                    $cls = "src";
                    $src_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Information Source</b></td></tr>" . PHP_EOL;
                } elseif ($elem03 == '21') {
                    $loopid = '2000B';                      // info receiver (clinic)
                    $cls = "rcv";
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Information Receiver</b></td></tr>" . PHP_EOL;
                } elseif ($elem03 == '19') {
                    $loopid = '2000C';                      // provider
                    $cls = "prv";
                    $has_eb = false;
                    $prv_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Provider</b></td></tr>" . PHP_EOL;
                } elseif ($elem03 == '22') {
                    $loopid = '2000D';                      // subscriber
                    $cls = "sbr";
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Subscriber</b></td></tr>" . PHP_EOL;
                } elseif ($elem03 == 'PT') {
                    $loopid = '2000D';                      // patient in 277CA
                    $cls = "sbr";
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Patient</b></td></tr>" . PHP_EOL;
                } elseif ($elem03 == '23') {
                    $loopid = '2000E';                      // dependent
                    $cls = "dep";
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td colspan=4><b>Dependent</b></td></tr>" . PHP_EOL;
                } else {
                    csv_edihist_log("edih_277_transaction_html: HL segment error $fn");
                }

                //
                $qtystr = '';  // reset for QTY and AMT segments in 277CA
                continue;
            }

            //
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
                $nm108 = (isset($sar[8]) && $sar[8] ) ? $cd27x->get_271_code('NM108', $sar[8]) : "";
                //
                if ($loopid == '2000A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $src_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'><em>" . text($nm108) . "</em> " . text($nm109) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100A';
                } elseif ($loopid == '2000B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'><em>" . text($nm108) . "</em> " . text($nm109) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100B';
                } elseif ($loopid == '2000C') {
                    $prv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $prv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'><em>" . text($nm108) . "</em> " . text($nm109) . "</td></tr>" . PHP_EOL;
                    $loopid = '2100C';
                } elseif ($loopid == '2000D') {
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $sbr_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'><em>" . text($nm108) . "</em> " . text($nm109) . "</td></tr>" . PHP_EOL;
                    $h3_lbl = $name;
                    $loopid = '2100D';
                } elseif ($loopid == '2000E') {
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                    $dep_nm1_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'><em>" . text($nm108) . "</em> " . text($nm109) . "</td></tr>" . PHP_EOL;
                    $h3_lbl = $name;
                    $loopid = '2100E';
                }

                //
                continue;
            }

            //                              //
            if (strncmp('PER' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $elem01 = (isset($sar[1])) ? $sar[1] : '';
                $elem02 = (isset($sar[2])) ? $sar[2] : '';
                $elem03 = (isset($sar[3])) ? $cd27x->get_271_code('PER03', $sar[3]) : "";
                $elem04 = (isset($sar[4])) ? $sar[4] : '';
                $elem05 = (isset($sar[5])) ? $cd27x->get_271_code('PER03', $sar[5]) : "";
                $elem06 = (isset($sar[6])) ? $sar[6] : '';
                $elem07 = (isset($sar[7])) ? $cd27x->get_271_code('PER03', $sar[7]) : "";
                $elem08 = (isset($sar[8])) ? $sar[8] : '';
                $elem09 = (isset($sar[9])) ? $sar[9] : '';
                //
                if ($loopid == '2100A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td colspan=2>" . text($elem02) . "</td><td colspan=2 title='" . attr($elem03 . " " . $elem05 . " " . $elem07) . "'>" . text($elem04 . " " . $elem06 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                } else {
                    csv_edihist_log('edih_277_html: PER segment not in 2100A loop ' . $fn);
                }

                //
                continue;
            }

            //
            if (strncmp('TRN' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $elem01 = ( isset($sar[1]) && $sar[1] == "1" ) ? "Transaction Ref" : "Trace";
                $elem02 = ( isset($sar[2]) ) ? $sar[2] : '';
                $elem03 = ( isset($sar[3]) ) ? $sar[3] : '';
                $elem04 = ( isset($sar[4]) ) ? $sar[4] : '';
                //
                if ($loopid == '2100B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($elem01) . "</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                    $loopid = '2200B';
                } elseif ($loopid == '2100C') {
                    $prv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($elem01) . "</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                    $loopid = '2200C';
                } elseif ($loopid == '2100D') {
                    $sbr_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($elem01) . "</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                    $h3_lbl = ($h3_lbl) ? $h3_lbl . ' ' . $elem02 : $h3_lbl;
                    $loopid = '2200D';
                } elseif ($loopid == '2100E') {
                    $dep_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($elem01) . "</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                    $h3_lbl = ($h3_lbl) ? $h3_lbl . ' ' . $elem02 : $h3_lbl;
                    $loopid = '2200E';
                }

                //
                continue;
            }

            //
            if (strncmp('STC' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                if (isset($sar[1])) {
                    if (strpos($sar[1], $ds)) {       // claim status category : claim status : entity identifier
                        $scda = explode($ds, $sar[1]);
                        $sc101 = ( isset($scda[0]) && $scda[0]) ? $cd27x->get_271_code('HCCSCC', $scda[0]) : "";
                        $sc102 = ( isset($scda[1]) && $scda[1]) ? $cd27x->get_271_code('HCCSC', $scda[1]) : "";
                        $sc103 = ( isset($scda[2]) && $scda[2]) ? $cd27x->get_271_code('NM101', $scda[2]) : "";
                    }
                } else {
                    $stc01 = $sar[1];
                }

                $stc02 = (isset($sar[2]) && $sar[2]) ? edih_format_date($sar[2]) : "";  // status information date
                $stc03 = "";                                                                // action code
                if (isset($sar[3])) {
                    if ($sar[3] == 'WQ') {
                        $stc03 = "Accepted";
                    } elseif ($sar[3] == 'F') {
                        $stc03 = "Final";
                    } elseif ($sar[3] == '15') {
                        $stc03 = "Correct/Resubmit";
                    } elseif ($sar[3] == 'U') {
                        $stc03 = "Rejected";
                    } else {
                        $stc03 = $sar[3];
                    }
                }

                $stc04 = (isset($sar[4]) && $sar[4]) ? edih_format_money($sar[4]) : "";  // billed amount
                $stc05 = (isset($sar[5]) && $sar[5]) ? edih_format_money($sar[5]) : "";  // paid amount
                $stc06 = (isset($sar[6]) && $sar[6]) ? edih_format_date($sar[6]) : "";   // payment date
                //$stc07  not used
                $stc08 = (isset($sar[8]) && $sar[8]) ? edih_format_date($sar[8]) : "";   // check issue date
                $stc09 = (isset($sar[9]) && $sar[9]) ? $sar[9] : "";                        // check or eft number
                //
                $stc10 = "";
                if (isset($sar[10]) && $sar[10]) {     // claim status category : claim status : entity identifier
                    if (strpos($sar[10], $ds)) {
                        $scda = explode($ds, $sar[1]);
                        $sc201 = ( isset($scda[0]) && $scda[0]) ? $cd27x->get_271_code('HCCSCC', $scda[0]) : "";
                        $sc202 = ( isset($scda[1]) && $scda[1]) ? $cd27x->get_271_code('HCCSC', $scda[1]) : "";
                        $sc203 = ( isset($scda[2]) && $scda[2]) ? $cd27x->get_271_code('NM101', $scda[2]) : "";
                        $sc204 = ( isset($scda[3]) && $scda[3] = 'RA') ? "Rx Reject/Payment Codes" : "";
                    } else {
                        $stc10 = $sar[10];
                    }
                }

                //
                $stc11 = "";
                if (isset($sar[11]) && $sar[11]) {     // claim status category : claim status : entity identifier
                    if (strpos($sar[10], $ds)) {
                        $scda = explode($ds, $sar[1]);
                        $sc301 = ( isset($scda[0]) && $scda[0]) ? $cd27x->get_271_code('HCCSCC', $scda[0]) : "";
                        $sc302 = ( isset($scda[1]) && $scda[1]) ? $cd27x->get_271_code('HCCSC', $scda[1]) : "";
                        $sc303 = ( isset($scda[2]) && $scda[2]) ? $cd27x->get_271_code('NM101', $scda[2]) : "";
                        $sc304 = ( isset($scda[3]) && $scda[3] = 'RA') ? "Rx Reject/Payment Codes" : "";
                    } else {
                        $stc11 = $sar[10];
                    }
                }

                //
                $stc12 =  ( isset($sar[12]) && $sar[12]) ? $sar[12] : "";    // message
                //
                $stc_html = (isset($sc101)) ? "<tr class='" . attr($cls) . "'><td>" . text($stc03) . "</td><td colspan=2>" . text($sc101) . "</td><td>" . text($stc02 . " " . $stc04) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc102)) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sc102) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc103) && $sc103) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Entity</em> " . text($sc103) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= ($stc05 || $stc06 || $stc08 || $stc09) ? "<tr class='" . attr($cls) . "'><td><em>Payment</em></td><td colspan=3>" . text($stc05 . " " . $stc06 . " " . $stc08 . " " . $stc09) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc201)) ?  "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sc201 . " " . $sc204) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc202)) ?  "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sc202) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc203) && $sc203) ?    "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Entity</em> " . text($sc203) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc301)) ?  "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sc301 . " " . $sc304) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc302)) ?  "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($sc302) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= (isset($sc303) && $sc303) ?    "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Entity</em> " . text($sc303) . "</td></tr>" . PHP_EOL : "";
                $stc_html .= ($stc12) ? "<tr class='" . attr($cls) . "'><td><em>Message</em></td><td colspan=3>" . text($stc12) . "</td></tr>" . PHP_EOL : "";
                //
                if ($loopid == '2200B') {
                    $rcv_html .= $stc_html;
                } elseif ($loopid == '2200C') {
                    $prv_html .= $stc_html;
                } elseif ($loopid == '2200D') {
                    $sbr_stc_html .= $stc_html;
                } elseif ($loopid == '2200E') {
                    $dep_stc_html .= $stc_html;
                }

                //
                continue;
            }

            // in 277CA, expect QTY followed by AMT
            // do not expect QTY or AMT in regular 277
            if (strncmp('QTY' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                if (isset($sar[1])) {
                    if ($sar[1] == '90') {
                        $qtystr = "Acknowledged Quantity ";
                    } elseif ($sar[1] == 'AA') {
                        $qtystr = "Unacknowledged Quantity ";
                    } elseif ($sar[1] == 'QA') {
                        $qtystr = "Quantity Approved ";
                    } elseif ($sar[1] == 'QC') {
                        $qtystr = "Quantity Disapproved ";
                    } else {
                        $qtystr = "Quantity ";
                    }
                } else {
                    $qtystr = "";
                }

                $qtystr .= (isset($sar[2]) && $sar[2]) ? $sar[2] : "";
            }

            //
            if (strncmp('AMT' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                // 277CA
                $amtstr = (isset($sar[1])  && $sar[1] == 'YU') ? "Amt " : "Amt Rej ";
                $amtstr .= (isset($sar[2])  && $sar[2]) ? edih_format_money($sar[2]) : "";
                //
                if ($loopid == '2200B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qtystr . " " . $amtstr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200C') {
                    $prv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qtystr . " " . $amtstr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200D') {
                    $sbr_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qtystr . " " . $amtstr) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200E') {
                    $dep_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qtystr . " " . $amtstr) . "</td></tr>" . PHP_EOL;
                }

                $amtstr = '';
                $qtystr = '';
                //
                continue;
            }

            //
            if (strncmp('REF' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                //
                $elem01 = (isset($sar[1])) ? $cd27x->get_271_code('REF', $sar[1]) : '';
                $elem02 = (isset($sar[2])) ? $sar[2] : '';
                $elem03 = (isset($sar[3])) ? $sar[2] : '';
                //
                if ($loopid == '2200B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200C') {
                    $prv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200D' || $loopid == '2220D') {
                    $sbr_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200E' || $loopid == '2220E') {
                    $dep_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('DTP' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                $var = '';
                //
                $elem01 = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('DTP', $sar[1]) : "";
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

                //
                if ($loopid == '2200D' || $loopid == '2220D') {
                    $sbr_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($elem01) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2200E' || $loopid == '2220E') {
                    $dep_stc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td>" . text($elem01) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('SVC' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                //
                $elem01 = '';                           // composite procedure code source:code:modifier:modifier
                if (isset($sar[1]) && $sar[1]) {
                    // construct a code source code modifier string
                    if (strpos($sar[1], $ds)) {
                        $scda = explode($ds, $sar[1]);
                        reset($scda);
                        foreach ($scda as $key => $val) {
                            if ($key == 0 && $val) {
                                $elem01 = $cd27x->get_271_code('EB13', $val);
                            } else {
                                $elem01 .= " " . $val;
                            }
                        }
                    } else {
                        $elem01 = $sar[1];
                    }
                }

                //
                $elem02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";  // billed amount
                $elem03 = (isset($sar[3]) && $sar[3]) ? edih_format_money($sar[3]) : "";  // paid amount
                $elem04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : "";                   // revenue code
                $elem05 = (isset($sar[5]) && $sar[5]) ? $sar[5] : "";                   // quantity
                // $elem06 not used
                $elem07 = (isset($sar[7]) && $sar[7]) ? $sar[7] : "";                   // original unis of service
                //
                if ($loopid == '2200B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td><em>Service</em></td><td>" . text($elem01) . "</td><td>" . text($elem02) . "</td><td>" . text($elem04) . "</td></tr>" . PHP_EOL;
                    $rcv_html .= ($elem03 || $elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2200D' || $loopid == '2220D') {
                    $sbr_stc_html .= "<tr class='" . attr($cls) . "'><td><em>Service</em></td><td>" . text($elem01) . "</td><td colspan=2>" . text($elem02 . " " . $elem04) . "</td></tr>" . PHP_EOL;
                    $sbr_stc_html .= ($elem03 || $elem04) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2200E' || $loopid == '2220E') {
                    $dep_stc_html .= "<tr class='" . attr($cls) . "'><td><em>Service</em></td><td>" . text($elem01) . "</td><td colspan=2>" . text($elem02 . " " . $elem04) . "</td></tr>" . PHP_EOL;
                    $dep_stc_html .= ($elem03 || $elem04) ?  "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
        }

        //
        if ($accordion) {
            $str_html .= "<h3>" . text($bht . " " . $h3_lbl) . "</h3>" . PHP_EOL;
            $str_html .= "<div id='ac_" . attr($bht) . "'>" . PHP_EOL;
        }

        $str_html .= ($hdr_html) ? $hdr_html : "";
        $str_html .= ($src_html) ? $src_html : "";
        $str_html .= ($rcv_html) ? $rcv_html : "";
        $str_html .= ($prv_html) ? $prv_html : "";
        $str_html .= ($sbr_nm1_html) ? $sbr_nm1_html : "";
        $str_html .= ($sbr_stc_html) ? $sbr_stc_html : "";
        $str_html .= ($dep_nm1_html) ? $dep_nm1_html : "";
        $str_html .= ($dep_stc_html) ? $dep_stc_html : "";
        $str_html .= "<tr><td colspan=4>&nbsp;</td></tr>" . PHP_EOL;
        $str_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
        //
        if ($accordion) {
            $str_html .= "</div>" . PHP_EOL;
        }
    }

    return  $str_html;
}

/**
 * create a display for an individual claim status response
 *
 * @uses csv_check_x12_obj()
 * @uses edih_277_transaction_html()
 *
 * @param string  $filename the filename
 * @param string  $clm01 identifier from 837 CLM of BHT segment
 *
 * @return string  either an error message or a table with the information from the response
 */
function edih_277_html($filename, $bht03 = '')
{
    // create a display for an individual 277 response
    $html_str = '';
    //
    if ($filename) {
        $fn = $filename;
    } else {
        csv_edihist_log("edih_277_html: called with no file arguments");
        $html_str .= "Error, no file given<br />" . PHP_EOL;
        return $html_str;
    }

    if ($fn) {
        $obj277 = csv_check_x12_obj($fn, 'f277');
        if ($obj277 && 'edih_x12_file' == get_class($obj277)) {
            if ($bht03) {
                // particular transaction
                $html_str .= edih_277_transaction_html($obj277, $bht03);
            } else {
                // file contents
                $env_ar = $obj277->edih_envelopes();
                if (!isset($env_ar['ST'])) {
                    $html_str .= "<p>edih_277_html: file parse error, envelope error</p>" . PHP_EOL;
                    $html_str .= text($obj277->edih_message());
                    return $html_str;
                } else {
                    $html_str .= "<div id='accordion'>" . PHP_EOL;
                }

                foreach ($env_ar['ST'] as $st) {
                    foreach ($env_ar['GS'] as $gs) {
                        if ($gs['icn'] != $st['icn']) {
                            continue;
                        } else {
                            $gs_date = edih_format_date($gs['date']);
                            $gs_sender = $gs['sender'];
                            break;
                        }
                    }

                    //
                    // get each transaction
                    foreach ($st['bht03'] as $bht) {
                        //$html_str .= "<h3>$bht Claim Status <em>Date</em> $gs_date <em>Source</em> $gs_sender</h3>".PHP_EOL;
                        //$html_str .= "<div id='ac_$bht'>".PHP_EOL;
                        //
                        $html_str .= edih_277_transaction_html($obj277, $bht, true);
                        //
                        //$html_str .= "</div>".PHP_EOL;
                    }
                }

                $html_str .= "</div>" . PHP_EOL;
            }
        } else {
            $html_str .= "<p>" . text($filename) . " : file parse error</p>" . PHP_EOL;
        }
    } else {
        $html_str .= "Error with file name or file parsing <br />" . PHP_EOL;
        csv_edihist_log("edih_277_html: error in retreiving file object");
        return $html_str;
    }

    //
    return $html_str;
}
