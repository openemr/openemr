<?php

/*
 * new_edih_835_html.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2016 Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


// lookup codes
//require_once("$srcdir/edihistory/codes/edih_835_code_class.php");
//require_once("$srcdir/edihistory/codes/edih_271_code_class.php");

/**
 * callback to round floats to 2 digit precision
 *
 * @param float
 * @param string
 * @return float
 */
function edih_round_cb(&$v, $k)
{
    $v = round((int)$v, 2);
}
/**
 * Create summary html string for an x12 835 claim payment
 *
 * @param array
 * @param object
 * @param object
 * @param array
 * @param string
 *
 * @return string
 */
function edih_835_clp_summary($trans_array, $codes27x, $codes835, $delimiters, $fname = '')
{
    // NM1 CPL
    $str_html = "";
    if (is_array($trans_array) && count($trans_array)) {
        if (csv_singlerecord_test($trans_array)) {
            $clp_ar = array();
            $clp_ar[] = $trans_array;
        } else {
            $clp_ar = $trans_array;
        }
    } else {
        csv_edihist_log("edih_835_transaction_html: Did not get transaction segments");
        $str_html .= "<p>Did not get transaction segments</p>" . PHP_EOL;
        return $str_html;
    }

    $de = (isset($delimiters['e'])) ? $delimiters['e'] : "";
    $ds = (isset($delimiters['s'])) ? $delimiters['s'] : "";
    $dr = (isset($delimiters['r'])) ? $delimiters['r'] : "";
    //
    if (!$de || !$ds) {
        csv_edihist_log("edih_835_transaction_html: Did not get delimiters");
        $str_html .= "<p>Did not get delimiters</p>" . PHP_EOL;
        return $str_html;
    }

    //
    $fn = ($fname) ? trim($fname) : "";
    //
    // get the code objects right
    $cd835 = $cd27x = '';
    if ('edih_835_codes' == get_class($codes835)) {
        $cd835 = $codes835;
    } elseif ('edih_835_codes' == get_class($codes27x)) {
        $cd835 = $codes27x;
    }

    if ('edih_271_codes' == get_class($codes27x)) {
        $cd27x = $codes27x;
    } elseif ('edih_271_codes' == get_class($codes835)) {
        $cd27x = $codes835;
    }

    if (!$cd835 || !$cd27x) {
        csv_edihist_log('edih_835_payment_html: invalid code class argument');
        $str_html .= "<p>invalid code class argument</p>" . PHP_EOL;
        return $str_html;
    }

    //
    $tblid = "";
    $capstr = "";
    $mia_str = "";
    //
    $hdr_html = "<tr><th>Reference</th><th colspan=2>Information</th><th colspan=2>" . text($fn) . "</th></tr>" . PHP_EOL;
    $hdr_html .= "</thead>" . PHP_EOL . "<tbody>" . PHP_EOL;
    $clp_html = "";
    $svc_html = "";
    $sbr_html = "";
    $chksegs = array('CLP', 'NM1', 'AMT', 'QTY');
    foreach ($trans_array as $trans) {
        $capstr = "Summary ";
        $loopid = 'NA';
        foreach ($trans as $seg) {
            //
            $test_str = substr($seg, 0, 3);
            if ($test_str == 'SVC') {
                break;
            }

            if (!in_array($test_str, $chksegs, true)) {
                continue;
            }

            //
            if (strncmp('CLP' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $loopid = '2100';
                $cls = 'clp';
                //
                $clp09ar = array('1' => 'Original', '7' => 'Replacement',  '8' => 'Void');
                //
                $clp01 = $clp02 = $clp03 = $clp04 = $clp05 = $clp06 = $clp07 = '';
                $clp08 = $clp09 = $clp11 = $clp12 = $clp13 = $capstr = $tblid = '';
                foreach ($sar as $k => $v) {
                    switch ((int)$k) {
                        case 0:
                            break;
                        case 1:
                            $clp01 = $v;
                            $capstr = $v;
                            $tblid = $v;
                            break;              // Pt ID CLM01
                        case 2:
                            $clp02 = $cd835->get_835_code('CLAIM_STATUS', $v);
                            break;
                        case 3:
                            $clp03 = ($v) ? "<em>Fee:</em> " . text(edih_format_money($v)) : "0";
                            break;
                        case 4:
                            $clp04 = ($v) ? "<em>Pmt:</em> " . text(edih_format_money($v)) : "0";
                            break;
                        case 5:
                            $clp05 = ($v) ? "<em>PtRsp:</em> " . text(edih_format_money($v)) : "0";
                            break;
                        case 7:
                            $clp07 = ($v) ? "<em>PR Ref:</em> " . text($v) : "";
                            break;
                        case 8:
                            $clp08 = ($v) ? "<em>Location</em> " . text($cd27x->get_271_code('POS', $v)) : '';
                            break;
                        case 9:
                            $clp09 = ($v && isset($clp09ar[$v])) ? "<em>Freq</em> " . text($clp09ar[$v]) : text($v);
                    }
                }

                //
                $clp_html .= "<tr class='" . attr($cls) . "'><td><em>PtID:</em> " . text($clp01) . "</td><td colspan=3><em>Status</em> " . text($clp02) . " <em>" . text($clp06) . "</em></td></tr>" . PHP_EOL;
                $clp_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>$clp03 $clp04 $clp05 $clp07 </td></tr>" . PHP_EOL;
                $clp_html .= ($clp08 || $clp09) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>$clp08 $clp09</td></tr>" . PHP_EOL : "";
                //
                continue;
                //
            }

            if ($loopid == '2100') {
                if (strncmp('AMT' . $de, $seg, 4) === 0) {
                    // Payment information
                    $sar = explode($de, $seg);
                    //
                    $amt01 = (isset($sar[1]) && $sar[1]) ? $cd835->get_835_code('AMT', $sar[1]) : "";
                    $amt02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";
                    //
                    $clp_html .= ($amt01) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($amt01 . " " . $amt02) . "</td></tr>" . PHP_EOL : "";
                    //
                    continue;
                }

                //
                if (strncmp('QTY' . $de, $seg, 4) === 0) {
                    // Payment information
                    $sar = explode($de, $seg);
                    //
                    $qty01 = (isset($sar[1]) && $sar[1]) ? $cd835->get_835_code('AMT', $sar[1]) : "";
                    $qty02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";
                    //
                    if ($loopid == '2100') {
                        $clp_html .= ($qty01) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qty01 . " " . $qty02) . "</td></tr>" . PHP_EOL : "";
                    }

                    //
                    continue;
                }

                if (strncmp('NM1' . $de, $seg, 4) === 0) {
                    $sar = explode($de, $seg);
                    //
                    $descr = (isset($sar[1]) && $sar[1]) ? $cd27x->get_271_code('NM101', $sar[1]) : "";
                    //
                    $name = (isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                    $name .= (isset($sar[7]) && $sar[7]) ? " {$sar[7]}" : "";
                    $name .= (isset($sar[4]) && $sar[4]) ? ", {$sar[4]}" : "";
                    $name .= (isset($sar[5]) && $sar[5]) ? " {$sar[5]}" : "";
                    $name .= (isset($sar[7]) && $sar[7]) ? " {$sar[7]}" : "";
                    //
                    $nm108 = (isset($sar[8]) && $sar[8]) ? $cd27x->get_271_code('NM108', $sar[8]) : "";
                    $nm109 = (isset($sar[9]) &&  $sar[9]) ? $sar[9] : "";
                    // complete table caption
                    if (isset($sar[1]) && $sar[1] == "QC") {
                        $capstr .= " $name";
                    }

                    //
                    if ($nm108) {
                        $sbr_html .= "<tr class='sbr'><td><em>" . text($descr) . "</em></td><td colspan=3>" . text($name) . " <em>" . text($nm108) . "</em>  " . text($nm109) . "</td></tr>" . PHP_EOL;
                    } else {
                        $sbr_html .= "<tr class='sbr'><td><em>" . text($descr) . "</em></td><td colspan=3>" . text($name) . " </td></tr>" . PHP_EOL;
                    }

                    //
                    $descr = $name = $nm108 = $nm109 = '';
                    continue;
                }

                if (strncmp('CAS' . $de, $seg, 4) === 0) {
                    $sar = explode($de, $seg);
                    $cas_str = '';
                    // claim adjustment group;  expect CAS segment for each adjustment group
                    foreach ($sar as $k => $v) {
                        switch ((int)$k) {
                            case 0:
                                break;
                            case 1:
                                $cas_str .= "$v " . $cd835->get_835_code('CAS_GROUP', $v);
                                break;
                            case 2:
                                $cas_str .= ($v) ? " $v" : "";
                                break;
                            case 3:
                                $cas_str .= ($v) ? " " . edih_format_money($v) : "";
                                break;
                            case 4:
                                $cas_str .= ($v) ? "x$v" : "";
                                break;
                            case 5:
                                $cas_str .= ($v) ? " $v" : "";
                                break;
                            case 6:
                                $cas_str .= ($v) ? " " . edih_format_money($v) : "";
                                break;
                            case 7:
                                $cas_str .= ($v) ? "x$v" : "";
                                break;
                            case 8:
                                $cas_str .= ($v) ? " $v" : "";
                                break;
                            case 9:
                                $cas_str .= ($v) ? " " . edih_format_money($v) : "";
                                break;
                            case 10:
                                $cas_str .= ($v) ? "x$v" : "";
                                break;
                            default:
                                $cas_str .= " *";
                        }
                    }

                    $clp_html .= ($cas_str) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($cas_str) . "</td></tr>" . PHP_EOL : "";
                    //
                    continue;
                }
            }
        }

        //
        $str_html .= "<table name='" . attr($tblid) . "' class='h835c' columns=4><caption>" . text($capstr) . "</caption>" . PHP_EOL . "<thead>" . PHP_EOL;
        $str_html .= $hdr_html;
        $str_html .= $sbr_html;
        $str_html .= $clp_html;
        $str_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
    }

    //
    return $str_html;
}



/**
 * Create html string for an x12 835 claim payment
 *
 * @param array
 * @param object
 * @param object
 * @param array
 * @param string
 *
 * @return string
 */
function edih_835_transaction_html($trans_array, $codes27x, $codes835, $delimiters, $fname = '')
{
    //
    $str_html = "";
    if (is_array($trans_array) && count($trans_array)) {
        if (csv_singlerecord_test($trans_array)) {
            $clp_ar = array();
            $clp_ar[] = $trans_array;
        } else {
            $clp_ar = $trans_array;
        }
    } else {
        csv_edihist_log("edih_835_transaction_html: Did not get transaction segments");
        $str_html .= "<p>Did not get transaction segments</p>" . PHP_EOL;
        return $str_html;
    }

    $de = (isset($delimiters['e'])) ? $delimiters['e'] : "";
    $ds = (isset($delimiters['s'])) ? $delimiters['s'] : "";
    $dr = (isset($delimiters['r'])) ? $delimiters['r'] : "";
    //
    if (!$de || !$ds) {
        csv_edihist_log("edih_835_transaction_html: Did not get delimiters");
        $str_html .= "<p>Did not get delimiters</p>" . PHP_EOL;
        return $str_html;
    }

    //
    $fn = ($fname) ? trim($fname) : "";
    //
    // get the code objects right
    $cd835 = $cd27x = '';
    if ('edih_835_codes' == get_class($codes835)) {
        $cd835 = $codes835;
    } elseif ('edih_835_codes' == get_class($codes27x)) {
        $cd835 = $codes27x;
    }

    if ('edih_271_codes' == get_class($codes27x)) {
        $cd27x = $codes27x;
    } elseif ('edih_271_codes' == get_class($codes835)) {
        $cd27x = $codes835;
    }

    if (!$cd835 || !$cd27x) {
        csv_edihist_log('edih_835_payment_html: invalid code class argument');
        $str_html .= "<p>invalid code class argument</p>" . PHP_EOL;
        return $str_html;
    }

    //
    $str_html = "";
    //
    $tblid = "";
    $capstr = "";
    $mia_str = "";
    //
    $hdr_html = "<tr><th>Reference</th><th colspan=3>Information &nbsp;" . text($fn) . "</th></tr>" . PHP_EOL;
    $hdr_html .= "</thead>" . PHP_EOL . "<tbody>" . PHP_EOL;
    $clp_html = "";
    $svc_html = "";
    $sbr_html = "";
    $moa_html = "";
    //
    foreach ($clp_ar as $trans) {
        $lq_ar = array();
        $cas_ar = array();
        $moa_ar = array();
        $rarc_str = "";
        $clp_html = "";
        $svc_html = "";
        $sbr_html = "";
        $moa_html = "";
        foreach ($trans as $seg) {
            //
            if (strncmp('REF' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                if (isset($sar[1]) && $sar[1]) {
                    if ($sar[1] == 'LU') {
                        $ref01 = 'Location';
                        $ref02 = (isset($sar[2])) ? $cd27x->get_271_code('POS', $sar[2])  : '';
                    } else {
                        // entity ID code
                        $ref01 = (isset($sar[1])) ? $cd27x->get_271_code('REF', $sar[1]) : '';
                        // entity ID
                        $ref02 = (isset($sar[2])) ? $sar[2] : '';
                    }

                    //
                    if ($loopid == '2100') {
                        $clp_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($ref01) . "</em> " . text($ref02) . "</td></tr>" . PHP_EOL;
                    } elseif ($loopid == '2110') {
                        $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($ref01) . "</em> " . text($ref02) . "</td></tr>" . PHP_EOL;
                    }
                }

                //
                continue;
            }

            //
            if (strncmp('DTM' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                // DTM in 835 use DTP codes from 271 codes
                $dtm01 = (isset($sar[1])) ? $cd27x->get_271_code('DTP', $sar[1]) : '';  // date qualifier
                $dtm02 = (isset($sar[2])) ? edih_format_date($sar[2]) : '';             // production date
                $dtm05 = (isset($sar[5])) ? $sar[5] : '';
                $dtm06 = (isset($sar[6])) ? edih_format_date($sar[2]) : '';
                //
                //if ( $elem02 == 'D8' && $elem03) {
                        //$dtmar = edih_format_date($elem03);
                    //} elseif ( $elem02 == 'RD8' && $elem03) {
                        //$dtmar = edih_format_date( substr($elem03, 0, 8) );
                        //$dtmar .= ' - '.edih_format_date( substr($elem03, -8) );
                    //}
                //}
                if ($loopid == '2100') {
                    $clp_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($dtm01) . "</em> " . text($dtm02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2110') {
                    $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($dtm01) . "</em> " . text($dtm02) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('PER' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $per01_ar = array('CX' => 'Claims Dept','BL' => 'Technical Dept','IC' => 'Website');
                $per01 = $per02 = $per03 = $per04 = $per05 = $per06 = $per07 = $per08 = '';
                foreach ($sar as $k => $v) {
                    switch ((int)$k) {
                        case 0:
                            break;
                        case 1:
                            $per01 = (isset($per01_ar[$v])) ? $per01_ar[$v] : $v;
                            break;
                        case 2:
                            $per02 = $v;
                            break;
                        case 3:
                            $per03 = $v;
                            break;
                        case 4:
                            $per04 = ($per03 == 'TE') ? edih_format_telephone($v) : $v;
                            break;
                        case 5:
                            $per05 = $v;
                            break;
                        case 6:
                            $per06 = ($per03 == 'TE') ? edih_format_telephone($v) :  $v;
                            break;
                        case 7:
                            $per07 = $v;
                            break;
                        case 8:
                            $per08 = ($per03 == 'TE') ? edih_format_telephone($v) :  $v;
                    }
                }

                //
                if ($loopid == '2100') {
                    $clp_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($per01 . " " . $per02 . " " . $per03 . " " . $per04) . " </td></tr>" . PHP_EOL;
                    $clp_html .= ($per05 || $per07) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($per05 . " " . $per06 . " " . $per07 . " " . $per08) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
            if (strncmp('CLP' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $loopid = '2100';
                $cls = 'clp';
                //
                $clp01 = $clp02 = $clp03 = $clp04 = $clp05 = $clp06 = $clp07 = $clp08 = $clp09 = $clp11 = $clp12 = $clp13 = $capstr = '';
                //
                $clp01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';                                        // Pt ID CLM01
                $clp02 = (isset($sar[2]) && $sar[2]) ? $cd835->get_835_code('CLAIM_STATUS', $sar[2]) : '';  // status code
                $clp03 = (isset($sar[3]) && $sar[3]) ? edih_format_money($sar[3]) : '0';                    // fee amont
                $clp04 = (isset($sar[4]) && $sar[4]) ? edih_format_money($sar[4]) : '0';                    // paid amount
                $clp05 = (isset($sar[5]) && $sar[5]) ? edih_format_money($sar[5]) : '0';                    // pt responsibility amont
                $clp06 = (isset($sar[6]) && $sar[6]) ? $cd835->get_835_code('CLP06', $sar[6]) : '';         // filing indicator code
                $clp07 = (isset($sar[7]) && $sar[7]) ? $sar[7] : '';                                        // Payer reference ID
                $clp08 = (isset($sar[8]) && $sar[8]) ? "<em>Location</em> " . text($cd27x->get_271_code('POS', $sar[8])) : ''; // Faciliy code place of service
                // frequency type code 1 original  7 replacement  8 void
                $clp09ar = array('1' => 'original', '7' => 'replacement',  '8' => 'void');
                if (isset($sar[9]) && array_key_exists($sar[9], $clp09ar)) {                                                           // claim frequency code
                    $clp09 = "<em>Freq</em> " . text($clp09ar[$sar[9]]);
                } else {
                    $clp09 = (isset($sar[9]) && $sar[9]) ? "<em>Freq</em> " . text($sar[9]) : "";
                }

                // DRG code not expected
                $clp11 = (isset($sar[11]) && $sar[11]) ? "<em>DRG Code</em> " . text($sar[11]) : '';
                // DRG weight
                $clp12 = (isset($sar[12]) && $sar[12]) ? "<em>DRG Weight</em> " . text($sar[12]) : '';
                // DRG percentage
                $clp13 = (isset($sar[13]) && $sar[13]) ? "<em>Dischg Frctn</em> " . text(edih_format_percent($sar[13])) : '';
                //
                // table caption PtID PtName
                $capstr .= $clp01;
                $tblid = $clp01;
                //
                $clp_html .= "<tr class='" . attr($cls) . "'><td><em>Pt ID</em> " . text($clp01) . "</td><td colspan=3><em>Status</em> " . text($clp02) . " <em>" . text($clp06) . "</em></td></tr>" . PHP_EOL;
                $clp_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Fee</em> " . text($clp03) . " <em>Pmt</em> " . text($clp04) . " <em>PtRsp</em> " . text($clp05) . " <em>PR Ref</em> " . text($clp07) . " </td></tr>" . PHP_EOL;
                $clp_html .= ($clp08 || $clp09) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>$clp08 $clp09</td></tr>" . PHP_EOL : "";
                $clp_html .= ($clp11 || $clp12 || $clp13) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>$clp11 $clp12 $clp13</td></tr>" . PHP_EOL : "";
                //
                continue;
                //
            }

            if (strncmp('CAS' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                // claim adjustments
                $cls = ($loopid == '2100') ? 'clp' : 'svc';
                // claim adjustment group;  expect CAS segment for each adjustment group
                if (isset($sar[1]) && $sar[1]) {
                    $cas_ar[$loopid][$sar[1]] = array_chunk(array_slice($sar, 2), 3);
                    // debug
                    //echo '== array_chunk'.PHP_EOL;
                    //var_dump( $cas_ar ).PHP_EOL;
                }

                //
                continue;
                //
            }

            //
            if (strncmp('NM1' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                $nm1_str = "";
                //
                if (isset($sar[1]) && $sar[1]) {
                    if (strpos('|IL|QC|72', $sar[1])) {
                        $cls = 'sbr';
                    } else {
                        $cls = 'clp';
                    }

                    $descr = $cd27x->get_271_code('NM101', $sar[1]);
                } else {
                    $cls = 'clp';
                    $descr = '';
                }

                //
                $name = (isset($sar[3]) && $sar[3]) ? $sar[3] : "";
                $name .= (isset($sar[7]) && $sar[7]) ? " {$sar[7]}" : "";
                $name .= (isset($sar[4]) && $sar[4]) ? ", {$sar[4]}" : "";
                $name .= (isset($sar[5]) && $sar[5]) ? " {$sar[5]}" : "";
                $name .= (isset($sar[6]) && $sar[6]) ? " {$sar[6]}" : "";
                //
                $nm108 = (isset($sar[8]) && $sar[8]) ? $cd27x->get_271_code('NM108', $sar[8]) : "";
                $nm109 = (isset($sar[9]) &&  $sar[9]) ? $sar[9] : "";
                // complete table caption
                if (isset($sar[1]) && $sar[1] == "QC") {
                    $capstr .= " $name";
                }

                //

                if ($nm108) {
                    $nm1_str .= "<tr class='" . attr($cls) . "'><td><em>" . text($descr) . "</em></td><td colspan=3>" . text($name) . " <em>" . text($nm108) . "</em>  " . text($nm109) . "</td></tr>" . PHP_EOL;
                } else {
                    $nm1_str .= "<tr class='" . attr($cls) . "'><td><em>" . text($descr) . "</em></td><td colspan=3>" . text($name) . " </td></tr>" . PHP_EOL;
                }

                if ($loopid == '2100') {
                    $clp_html .= $nm1_str;
                } elseif ($loopid == '2110') {
                    $svc_html .= $nm1_str;
                }

                //
                $descr = $name = $nm108 = $nm109 = '';
                continue;
            }

            //
            if (strncmp('MIA' . $de, $seg, 4) === 0) {
                // Inpatient Adjudication information
                $sar = explode($de, $seg);
                // <tr class='mia'><td>&gt;</td><td> </td></tr>".PHP_EOL;
                $tr1 = "<tr class='mia'><td>&gt;</td><td colspan=3>";
                $tr2 = "</td></tr>" . PHP_EOL;
                //
                $mia_str .= (isset($sar[1]) && $sar[1]) ? $tr1 . "Covered Days or Visits: " . text($sar[1]) . $tr2 : "";  // days or visits
                $mia_str .= (isset($sar[2]) && $sar[2]) ? $tr1 . "PPS Operating Outlier Amt: " . text(edih_format_money($sar[2])) . $tr2 : "";
                $mia_str .= (isset($sar[3]) && $sar[3]) ? $tr1 . "Lifetime Psychiatric Days: " . text($sar[3]) . $tr2 : "";
                $mia_str .= (isset($sar[4]) && $sar[4]) ? $tr1 . "Claim DRG Amt: " . text(edih_format_money($sar[4])) . $tr2 : "";
                $mia_str .= (isset($sar[5]) && $sar[5]) ? "<tr class='mia'><td>" . text($sar[5]) . "</td><td colspan=3>" . text($cd835->get_835_code('RARC', $sar[5])) . $tr2 : "";
                $mia_str .= (isset($sar[6]) && $sar[6]) ? $tr1 . "Claim DSH Amt: " . text(edih_format_money($sar[6])) . $tr2 : "";
                $mia_str .= (isset($sar[7]) && $sar[7]) ? $tr1 . "Claim MSP Pass Thru Amt: " . text(edih_format_money($sar[7])) . $tr2 : "";
                $mia_str .= (isset($sar[8]) && $sar[8]) ? $tr1 . "Claim PPS Capital Amt: " . text(edih_format_money($sar[8])) . $tr2 : "";
                $mia_str .= (isset($sar[9]) && $sar[9]) ? $tr1 . "PPS Capital FSP DRG Amt: " . text(edih_format_money($sar[9])) . $tr2 : "";
                $mia_str .= (isset($sar[10]) && $sar[10]) ? $tr1 . "PPS Capital HSP DRG Amt: " . text(edih_format_money($sar[10])) . $tr2 : "";
                $mia_str .= (isset($sar[11]) && $sar[11]) ? $tr1 . "PPS Capital DSH DRG Amt: " . text(edih_format_money($sar[11])) . $tr2 : "";
                $mia_str .= (isset($sar[12]) && $sar[12]) ? $tr1 . "Old Capital Amt: " . text(edih_format_money($sar[12])) . $tr2 : "";
                $mia_str .= (isset($sar[13]) && $sar[13]) ? $tr1 . "PPS Capital Ind Med Edu Amt: " . text(edih_format_money($sar[13])) . $tr2 : "";
                $mia_str .= (isset($sar[14]) && $sar[14]) ? $tr1 . "PPS Oper HSP Spec DRG Amt: " . text(edih_format_money($sar[14])) . $tr2 : "";
                $mia_str .= (isset($sar[15]) && $sar[15]) ? $tr1 . "Cost Report Day Count: " . text($sar[15]) . $tr2 : "";
                $mia_str .= (isset($sar[16]) && $sar[16]) ? $tr1 . "PPS Oper FSP Spec DRG Amt: " . text(edih_format_money($sar[16])) . $tr2 : "";
                $mia_str .= (isset($sar[17]) && $sar[17]) ? $tr1 . "Claim PPS Outlier Amt: " . text(edih_format_money($sar[17])) . $tr2 : "";
                $mia_str .= (isset($sar[18]) && $sar[18]) ? $tr1 . "Claim Indirect Teaching: " . text(edih_format_money($sar[18])) . $tr2 : "";
                $mia_str .= (isset($sar[19]) && $sar[19]) ? $tr1 . "Non Pay Prof Component Amt: " . text(edih_format_money($sar[19])) . $tr2 : "";
                $mia_str .= (isset($sar[20]) && $sar[20]) ? "<tr class='mia'><td>" . text($sar[20]) . "</td><td colspan=3>" . text($cd835->get_835_code('RARC', $sar[20])) . $tr2 : "";
                $mia_str .= (isset($sar[21]) && $sar[21]) ? "<tr class='mia'><td>" . text($sar[21]) . "</td><td colspan=3>" . text($cd835->get_835_code('RARC', $sar[21])) . $tr2 : "";
                $mia_str .= (isset($sar[22]) && $sar[22]) ? "<tr class='mia'><td>" . text($sar[22]) . "</td><td colspan=3>" . text($cd835->get_835_code('RARC', $sar[22])) . $tr2 : "";
                //
                continue;
            }

            //
            if (strncmp('MOA' . $de, $seg, 4) === 0) {
                // Inpatient Adjudication information
                $sar = explode($de, $seg);
                //
                $moa_str = 'Claim Level Remarks: ';
                foreach ($sar as $k => $v) {
                    switch ((int)$k) {
                        case 0:
                            break;
                        case 1:
                            $moa_str .= ($v) ? 'Reimbursement Rate: ' . edih_format_percent($v) : '';
                            break;
                        case 2:
                            $moa_str .= ($v) ? 'Allowed Amt: ' . edih_format_money($v) : '';
                            break;
                        case 8:
                            $moa_str .= ($v) ? 'ESRD Amt: ' . edih_format_money($v) : '';
                            break;
                        case 9:
                            $moa_str .= ($v) ? 'Non-Pay Prof Cmpnt: ' . edih_format_money($v) : '';
                            break;
                        default:
                        // case 3, 4, 5, 6, 7 are remark codes
                            $moa_str .= ($v) ? ' ' . $v : '';
                            $moa_ar[] = ($v) ? $v : '';
                    }
                }

                //
                $clp_html .= ($moa_str) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($moa_str) . "</td></tr>" . PHP_EOL : "";
                //
                continue;
            }

            //
            if (strncmp('AMT' . $de, $seg, 4) === 0) {
                // Payment information
                $sar = explode($de, $seg);
                //
                $amt01 = (isset($sar[1]) && $sar[1]) ? $cd835->get_835_code('AMT', $sar[1]) : "";
                $amt02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";
                //
                if ($loopid == '2100') {
                    $clp_html .= ($amt01) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($amt01 . " " . $amt02) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2110') {
                    $svc_html .= ($amt01) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($amt01 . " " . $amt02) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
            if (strncmp('QTY' . $de, $seg, 4) === 0) {
                // Payment information
                $sar = explode($de, $seg);
                //
                $qty01 = (isset($sar[1]) && $sar[1]) ? $cd835->get_835_code('AMT', $sar[1]) : "";
                $qty02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";
                //
                if ($loopid == '2100') {
                    $clp_html .= ($qty01) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qty01 . " " . $qty02) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '2110') {
                    $svc_html .= ($qty01) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($qty01 . " " . $qty02) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
            if (strncmp('SVC' . $de, $seg, 4) === 0) {
                //
                $sar = explode($de, $seg);
                $loopid = '2110';
                $cls = 'svc';
                $rarc_str = ''; // used in LQ segment stanza
                // composite procedure code source:code:modifier:modifier
                $svc01 = '';
                if (isset($sar[1]) && $sar[1]) {
                    // construct a code source code modifier string
                    if (strpos($sar[1], $ds)) {
                        $scda = explode($ds, $sar[1]);
                        reset($scda);
                        foreach ($scda as $key => $val) {
                            if ($key == 0 && $val) {
                                $svc01 = $cd27x->get_271_code('EB13', $val);
                            } else {
                                $svc01 .= ":" . $val;
                            }
                        }
                    } else {
                        $svc01 = $sar[1];
                    }
                }

                //
                $svc02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : "";  // billed amount
                $svc03 = (isset($sar[3]) && $sar[3]) ? edih_format_money($sar[3]) : "";  // paid amount
                $svc04 = (isset($sar[4]) && $sar[4]) ? "<em>NUBC</em> " . $sar[4] : "";   // NUBC revenue code
                $svc05 = (isset($sar[5]) && $sar[5]) ? "<em>Units</em> " . $sar[5] : "";  // quantity
                //
                $svc06 = '';
                if (isset($sar[6]) && $sar[6]) {
                    // construct a code source code modifier string
                    if (strpos($sar[6], $ds)) {
                        $scda = explode($ds, $sar[6]);
                        reset($scda);
                        foreach ($scda as $key => $val) {
                            if ($key == 0 && $val) {
                                $svc06 = $cd27x->get_271_code('EB13', $val) . " ";
                            } else {
                                $svc06 .= ":" . $val;
                            }
                        }
                    } else {
                        $svc06 = $sar[6];
                    }
                }

                $svc07 = (isset($sar[7]) && $sar[7]) ? $sar[7] : "";                    // original unis of service
                //
                $svc_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Service</em> " . text($svc01) . " <em>Fee</em> " . text($svc02) . " <em>Pmt</em> " . text($svc03 . " " . $svc05 . " " . $svc04) . "</td></tr>" . PHP_EOL;
                $svc_html .= ($svc06) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Submitted Svc</em> " . text($svc06) . " <em>Units</em> " . text($svc07) . "</td></tr>" . PHP_EOL : "";
                //
                continue;
            }

            //
            if (strncmp('LQ' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                // Health Care Remark Codes
                $lq01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : "";
                if (isset($sar[2])) {
                    $lq02 = ($lq01 == 'HE') ? $sar[2] : "";
                    //$lq02 = $cd835->get_835_code('RARC', $sar[2]);
                    $rarc_str .= ($rarc_str) ? ' ' . $sar[2] : '<em>Service Remarks</em> ' . text($sar[2]);
                    $lq_ar[] = $sar[2];
                } else {
                    $lq02 = "";
                }

                //$lq02 = (isset($sar[2]) && $sar[2] && $lq01 == 'HE') ? $cd835->get_835_code('RARC', $sar[2]) : "";
                //
                // $svc_html .= ($rarc_str) ? "<tr class='$cls'><td>&gt;</td><td colspan=3>$rarc_str</td></tr>".PHP_EOL : "";
                //
                continue;
            }

            //
        } // end foreach trans as seg
        // assemble the html table at end of the inside foreach loop
        //
        $str_html .= "<table name='" . attr($tblid) . "' class='h835c' columns=4><caption>" . text($capstr) . "</caption>" . PHP_EOL . "<thead>" . PHP_EOL;
        $str_html .= $hdr_html;
        $str_html .= $sbr_html;
        $str_html .= $clp_html;
        $str_html .= ($mia_str) ? $mia_str : '';
        $str_html .= $svc_html;
        $str_html .= ($rarc_str) ? "<tr class='svc'><td>&gt;</td><td colspan=3>$rarc_str</td></tr>" . PHP_EOL : "";
        if (count($cas_ar)) {
            foreach ($cas_ar as $key => $cas) {
                if (!is_array($cas) && !count($cas)) {
                    continue;
                }

                if ($key == '2100' && count($cas)) {
                    $cls = 'remc';
                    $str_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Claim Level Adjustments</em></td></tr>" . PHP_EOL;
                } else {
                    $cls = 'rems';
                    $str_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Service Level Adjustments</em></td></tr>" . PHP_EOL;
                }

                $cg = '';
                foreach ($cas as $ky => $trp) {
                    //echo '==== cas_ar unwind cas as ky trp '.$ky.PHP_EOL;
                    //var_dump ($trp).PHP_EOL;
                    //
                    if (!is_array($trp) && !count($trp)) {
                        continue;
                    }

                    $cg = $cd835->get_835_code('CAS_GROUP', $ky);
                    foreach ($trp as $tr) {
                        // debug
                        //echo '==== cas_ar unwind trp as tr '.PHP_EOL;
                        //var_dump ($tr).PHP_EOL;
                        //
                        $cd = $cr = $ca = $cq = '';
                        foreach ($tr as $k => $c) {
                            //echo '==== cas_ar unwind tr as k c '.$k.PHP_EOL;
                            //var_dump ($c).PHP_EOL;
                            //
                            switch ((int)$k) {
                                case 0:
                                    $cd = $c;
                                    $cr = $cd835->get_835_code('CARC', $c);
                                    break;
                                case 1:
                                    $ca = ($c) ? edih_format_money($c) : "";
                                    break;
                                case 2:
                                    $cq = ($c) ? $c : "";
                            }
                        }
                    }

                    //
                    $str_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($ky . " " . $cg . " " . $cd . " " . $ca . " " . $cq) . "</td></tr>" . PHP_EOL;
                    $str_html .= "<tr class='" . attr($cls) . "'><td style='text-align: center;'>" . text($ky . " " . $cd) . "</td><td colspan=3>" . text($cr) . "</td></tr>" . PHP_EOL;
                }
            }
        }

        if (count($moa_ar)) {
            $cls = 'remc';
            $str_html .= "<tr class='" . attr($cls) . "'><td colspan=4><em>Remarks</em></td></tr>" . PHP_EOL;
            foreach ($moa_ar as $moa) {
                $moar = $cd835->get_835_code('RARC', $moa);
                $str_html .= "<tr class='" . attr($cls) . "'><td style='text-align: center;'>" . text($moa) . "</td><td colspan=3>" . text($moar) . "</td></tr>" . PHP_EOL;
            }
        }

        if (count($lq_ar)) {
            $cls = 'mia';
            $str_html .= ($rarc_str) ? "<tr class='" . attr($cls) . "'><td colspan=4>$rarc_str</td></tr>" . PHP_EOL : "";
            foreach ($lq_ar as $lq) {
                $lqr = $cd835->get_835_code('RARC', $lq);
                $str_html .= "<tr class='" . attr($cls) . "'><td style='text-align: center;'>$lq</td><td colspan=3>" . text($lqr) . "</td></tr>" . PHP_EOL;
            }
        }

        // bottom border
        $str_html .= "<tr class='remc'><td colspan=4>&nbsp;</td></tr>" . PHP_EOL;
        // end tags for table
        $str_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
    }

    //
    return $str_html;
}



/**
 * Create an HTML rendition of the 835 check payment transaction.
 *
 *
 * @param array
 * @param object
 * @param object
 * @param array
 * @param string
 *
 * @return string     HTML table
 */
function edih_835_payment_html($segments, $codes27x, $codes835, $delimiters, $fname = '')
{
    //
    $str_html = '';
    $pid = $chk = '';
    if (is_array($segments) && count($segments)) {
        $trans_ar = $segments;
    } else {
        csv_edihist_log("edih_835_payment_html: invalid segments argument");
        $str_html .= "<p>invalid segments argument</p>" . PHP_EOL;
        return $str_html;
    }

    if (is_array($delimiters) && count($delimiters)) {
        $de = $delimiters['e'];
        $ds = $delimiters['s'];
        $dr = $delimiters['r'];
    } else {
        csv_edihist_log("edih_835_payment_html: invalid delimiters argument");
        $str_html .= "<p>invalid delimiters argument</p>" . PHP_EOL;
        return $str_html;
    }

    //
    $fn = ($fname) ? trim($fname) : "";
    //
    // get the code objects right
    $cd835 = $cd27x = '';
    if ('edih_835_codes' == get_class($codes835)) {
        $cd835 = $codes835;
    } elseif ('edih_835_codes' == get_class($codes27x)) {
        $cd835 = $codes27x;
    }

    if ('edih_271_codes' == get_class($codes27x)) {
        $cd27x = $codes27x;
    } elseif ('edih_271_codes' == get_class($codes835)) {
        $cd27x = $codes835;
    }

    if (!$cd835 || !$cd27x) {
        csv_edihist_log('edih_835_payment_html: invalid code class argument');
        $str_html .= "<p>invalid code class argument</p>" . PHP_EOL;
        return $str_html;
    }

    //
    // collect all strings into this variable
    $str_html = "";
    //
    $hdr_html = "<thead>" . PHP_EOL;
    $hdr_html .= "<tr><th>Reference</th><th colspan=2>Information</th><th colspan=2>" . text($fn) . "</th></tr>" . PHP_EOL;
    $hdr_html .= "</thead>" . PHP_EOL . "<tbody>" . PHP_EOL;
    $pmt_html = "";
    $src_html = "";
    $rcv_html = "";
    $lx_html = "";
    $clp_html = "";
    $trl_html = "";
    //
    $acctng = array('pmt' => 0,'fee' => 0,'clmpmt' => 0,'clmadj' => 0, 'ptrsp' => 0, 'svcptrsp' => 0, 'svcfee' => 0,'svcadj' => 0,'plbadj' => 0);
    //
    foreach ($trans_ar as $trans) {
        $clpsegs = array();
        $lx_ar = array();
        $clp_ct = 0;
        $lx_ct = 0;
        $loop = '';
        $lxkey = '';
        $capstr = "Remittance ";
        $tblid = "";
        //
        foreach ($trans as $seg) {
            //
            if (strncmp('ST' . $de, $seg, 3) === 0) {
                $loopid = 'header';
                continue;
            }

            //
            if (strncmp('BPR' . $de, $seg, 4) === 0) {
                $loopid = 'header';
                $cls = 'pmt';
                //
                $acctng = array('pmt' => 0, 'fee' => 0, 'clmpmt' => 0, 'clmadj' => 0, 'ptrsp' => 0,
                                'svcptrsp' => 0, 'svcfee' => 0, 'svcpmt' => 0, 'svcadj' => 0, 'plbadj' => 0);
                //
                $sar = explode($de, $seg);
                $bpr01 = (isset($sar[1]) && $sar[1]) ? $cd835->get_835_code('BPR01', $sar[1]) : ''; // handling code
                $bpr02 = (isset($sar[2]) && $sar[2]) ? edih_format_money($sar[2]) : '';             // full payment amount
                $bpr03 = (isset($sar[3]) && $sar[3] == 'D' ) ? 'Debit' : 'Credit';                  // credit or debit flag
                $bpr04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : '';                                // payment method ACH|CHK|NON
                $bpr05 = (isset($sar[5]) && $sar[5]) ? $sar[5] : '';                                // payment format code CCP|CTX
                $bpr06 = (isset($sar[6]) && $sar[6]) ? $sar[6] : '';                                // DFI ID qualifier
                $bpr07 = (isset($sar[7]) && $sar[7]) ? $sar[7] : '';                                // bank ID
                $bpr08 = (isset($sar[8]) && $sar[8]) ? $sar[8] : '';                                // account no. qualifier DA
                $bpr09 = (isset($sar[9]) && $sar[9]) ? $sar[9] : '';                                // sender account number
                $bpr10 = (isset($sar[10]) && $sar[10]) ? $sar[10] : '';                             // originating company ID
                $bpr11 = (isset($sar[11]) && $sar[11]) ? $sar[11] : '';                             // originating company supplemental ID
                $bpr12 = (isset($sar[12]) && $sar[12]) ? $sar[12] : '';                             // deposit acount ID
                $bpr13 = (isset($sar[13]) && $sar[13]) ? $sar[13] : '';                             // deposit bank ID
                $bpr14 = (isset($sar[14]) && $sar[14]) ? $sar[14] : '';                             // account type DA deposit SG savings
                $bpr15 = (isset($sar[15]) && $sar[15]) ? $sar[15] : '';                             // account number
                $bpr16 = (isset($sar[16]) && $sar[16]) ? edih_format_date($sar[16]) : '';           // check or payment date
                //
                if ($bpr04 == 'NON') {
                    $pmt_html .= "<tr class='" . attr($cls) . "'><td>" . text($bpr16) . "</td><td>" . text($bpr03 . " " . $bpr04) . "</td><td colspan=2>Non Payment</td></tr>" . PHP_EOL;
                } else {
                    $pmt_html .= "<tr class='" . attr($cls) . "'><td>" . text($bpr16) . "</td><td>" . text($bpr03 . " " . $bpr04) . "</td><td colspan=2>" . text($bpr02) . " to " . text($bpr13 . " " . $bpr12 . " " . $bpr14) . "</td></tr>" . PHP_EOL;
                }

                if (strpos('|ACH|BOP|FWT', $bpr04)) {
                    $pmt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($bpr05) . " from: " . text($bpr07 . " " . $bpr09 . " " . $bpr10) . "</td></tr>" . PHP_EOL;
                }

                $pmt_html .= ($bpr11) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Pmt No.</em> " . text($bpr11 . " " . $bpr01) . "</td></tr>" . PHP_EOL : "";
                $acctng['pmt'] = (isset($sar[2]) && $sar[2]) ? (float)$sar[2] : "";
                //
                continue;
            }

            //
            if (strncmp('TRN' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $trn01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';  // trace type code
                $trn02 = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';  // trace number (= BPR11)
                $trn03 = (isset($sar[3]) && $sar[3]) ? $sar[3] : '';  // originator ID
                $trn04 = (isset($sar[4]) && $sar[4]) ? $sar[4] : '';  // originator supplemental ID
                if ($trn03[0] == '1') {
                    $trn03 = substr($trn03, 1);
                } // originator ID is '1' prepended to EIN or TIN
                // the html ID for the table
                $tblid = ($trn02) ? $trn02 : "";
                $capstr .= ($trn02) ? "Check No: " . $trn02 : "Payment Listing";
                //
                $pmt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Trace</em> " . text($trn02) . " <em>by</em> " . text($trn03 . " " . $trn04) . "</td></tr>" . PHP_EOL;
                //
                continue;
            }

            //
            if (strncmp('CUR' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $cur01 = (isset($sar[1])) ? $sar[1] : '';  // entity ID code
                $cur02 = (isset($sar[2])) ? $sar[2] : '';  // currency code
                //
                $pmt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>Trace</em> " . text($cur02) . " by " . text($cur03 . " " . $cur04) . "</td></tr>" . PHP_EOL;
                //
                continue;
            }

            //
            if (strncmp('REF' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                //
                $ref01 = (isset($sar[1])) ? $cd27x->get_271_code('REF', $sar[1]) : '';  // entity ID code
                $ref02 = (isset($sar[2])) ? $sar[2] : '';  // entity ID
                //
                if ($loopid == 'header') {
                    // should not be present for payee receiver
                    $pmt_html .= "<tr class='" . text($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($ref01) . "</em> " . text($ref02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '1000A') {
                    // source
                    $src_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($ref01) . "</em> " . text($ref02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '1000B') {
                    // receiver
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($ref01) . "</em> " . text($ref02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100') {
                    //
                    $clpsegs[] = $seg;
                } elseif ($loopid == '2110') {
                    //
                    $clpsegs[] = $seg;
                }

                //
                continue;
            }

            if (strncmp('DTM' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                // DTM in 835 use DTP codes from 271 codes
                $dtm01 = (isset($sar[1])) ? $cd27x->get_271_code('DTP', $sar[1]) : '';  // date qualifier
                $dtm02 = (isset($sar[2])) ? edih_format_date($sar[2]) : '';             // production date
                $dtm05 = (isset($sar[5])) ? $sar[5] : '';
                $dtm06 = (isset($sar[6])) ? edih_format_date($sar[2]) : '';
                //
                //if ( $elem02 == 'D8' && $elem03) {
                        //$dtmar = edih_format_date($elem03);
                    //} elseif ( $elem02 == 'RD8' && $elem03) {
                        //$dtmar = edih_format_date( substr($elem03, 0, 8) );
                        //$dtmar .= ' - '.edih_format_date( substr($elem03, -8) );
                    //}
                //}
                //
                if ($loopid == 'header') {
                    // should not be present for payee or receiver
                    $pmt_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3><em>" . text($dtm01) . "</em> " . text($dtm02) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '2100') {
                    $clpsegs[] = $seg;
                } elseif ($loopid == '2110') {
                    $clpsegs[] = $seg;
                }

                //
                continue;
            }

            //
            if (strncmp('N1' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                //
                $n101 = (isset($sar[1])) ? $cd27x->get_271_code('NM101', $sar[1]) : '';  // entity ID code
                $n102 = (isset($sar[2])) ? $sar[2] : '';                                // name
                $n103 = (isset($sar[3])) ? $cd27x->get_271_code('NM108', $sar[3]) : '';  // entity ID type code
                $n104 = (isset($sar[4])) ? $sar[4] : '';
                //
                if ($loopid == 'header') {
                    $loopid = '1000A';
                    $cls = 'src';
                    $src_html .= "<tr class='" . attr($cls) . "'><td><em>" . text($n101) . "</em></td><td colspan=3>" . text($n102) . " <em>" . text($n103) . "</em> " . text($n104) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '1000A') {
                    $loopid = '1000B';
                    $cls = 'rcv';
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td><em>" . text($n101) . "</em></td><td colspan=3>" . text($n102) . " <em>" . text($n103) . "</em> " . text($n104) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('N3' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                //
                $n301 = (isset($sar[1])) ? $sar[1] : '';  // address
                $n302 = (isset($sar[2])) ? $sar[2] : '';  // address line 2
                //
                if ($loopid == '1000A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($n301 . " " . $n302) . "</td></tr>" . PHP_EOL;
                } elseif ($loopid == '1000B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($n301 . " " . $n302) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('N4' . $de, $seg, 3) === 0) {
                $sar = explode($de, $seg);
                //
                $n401 = (isset($sar[1])) ? $sar[1] : '';  // city
                $n402 = (isset($sar[2])) ? $sar[2] : '';  // state
                $n403 = (isset($sar[3])) ? $sar[3] : '';  // Postal
                $n404 = (isset($sar[4])) ? $sar[4] : '';  // Country
                $n407 = (isset($sar[7])) ? $sar[7] : '';  // Country subdivision
                //
                if ($loopid == '1000A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($n401 . " " . $n402 . " " . $n403) . "</td></tr>" . PHP_EOL;
                    $src_html .= ($n404 || $n407) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($n404 . " " . $n405) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '1000B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($n401 . " " . $n402 . " " . $n403) . "</td></tr>" . PHP_EOL;
                    $rcv_html .= ($n404 || $n407) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($n404 . " " .  $n405) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
            if (strncmp('PER' . $de, $seg, 4) === 0) {
                if ($loopid == '2100' || $loopid == '2100') {
                    // loop 2100 only
                    $clpsegs[] = $seg;
                    continue;
                }

                $sar = explode($de, $seg);
                $per01_ar = array('CX' => 'Claims Dept','BL' => 'Technical Dept','IC' => 'Website');
                $per01 = $per02 = $per03 = $per04 = $per05 = $per06 = $per07 = $per08 = '';
                foreach ($sar as $k => $v) {
                    switch ((int)$k) {
                        case 0:
                            break;
                        case 1:
                            $per01 = (isset($per01_ar[$v])) ? $per01_ar[$v] : $v;
                            break;
                        case 2:
                            $per02 = $v;
                            break;
                        case 3:
                            $per03 = $v;
                            break;
                        case 4:
                            $per04 = ($per03 == 'TE') ? edih_format_telephone($v) : $v;
                            break;
                        case 5:
                            $per05 = $v;
                            break;
                        case 6:
                            $per06 = ($per03 == 'TE') ? edih_format_telephone($v) : $v;
                            break;
                        case 7:
                            $per07 = $v;
                            break;
                        case 8:
                            $per08 = ($per03 == 'TE') ? edih_format_telephone($v) : $v;
                    }
                }

                //
                if ($loopid == '1000A') {
                    $src_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($per01 . " " . $per02 . " " . $per03 . " " . $per04) . " </td></tr>" . PHP_EOL;
                    $src_html .= ($per05 || $per07) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($per05 . " " . $per06 . " " . $per07 . " " . $per08) . "</td></tr>" . PHP_EOL : "";
                } elseif ($loopid == '1000B') {
                    $rcv_html .= "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($per01 . " " . $per02 . " " . $per03 . " " . $per04) . " </td></tr>" . PHP_EOL;
                    $rcv_html .= ($per05 || $per07) ? "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>" . text($per05 . " " . $per06 . " " . $per07 . " " . $per08) . "</td></tr>" . PHP_EOL : "";
                }

                //
                continue;
            }

            //
            if (strncmp('RDM' . $de, $seg, 4) === 0) {
                // remittance delivery method
                // loop 1000B -- add to pmt information
                $sar = explode($de, $seg);
                //
                $rdm01 = (isset($sar[1])) ? $sar[1] : '';
                if ($sar[1] == 'BM') {
                    $rdm01 = 'By mail';
                } elseif ($sar[1] == 'EM') {
                    $rdm01 = 'By e-mail';
                } elseif ($sar[1] == 'FT') {
                    $rdm01 = 'By file transfer';
                } elseif ($sar[1] == 'OL') {
                    $rdm01 = 'By online';
                }

                $rdm02 = (isset($sar[2])) ? $sar[2] : '';                               // name
                $rdm03 = (isset($sar[3])) ? $sar[3] : '';                               // number
                //
                $pmt_html .= "<tr class='" . attr($cls) . "'><td>" . text($rdm01) . "</td><td colspan=3>" . text($rdm02 . " " . $rdm03) . "</td></tr>" . PHP_EOL;
                //
                continue;
            }

            //
            if (strncmp('LX' . $de, $seg, 3) === 0) {
                // LX can end loop 1000B or a claim grouping
                if ($loopid == '1000B') {
                    // finish off pmt, src, and rcv
                    $rcv_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
                } elseif ($loopid == '2110') {
                    if ($lxkey && array_key_exists($lxkey, $lx_ar)) {
                        // LX claim grouping -- cannot predict detail
                        // LX can follow loop 2110
                        if (count($clpsegs)) {
                            $clp_html .= edih_835_transaction_html($clpsegs, $codes27x, $codes835, $delimiters);
                            $clpsegs = array();
                        }

                        $nlx_html = ($lx_html) ? "<table name='lx_" . attr($lxkey) . "' class='h835c' columns=4>" . PHP_EOL . "<tbody>" . PHP_EOL . $lx_html . PHP_EOL : "";
                        $lx_ar[$lxkey]['lx'] = $nlx_html;
                        $lx_ar[$lxkey]['clp'] = $clp_html;
                        $lx_html = "";
                        $clp_html = "";
                        $clpsegs = array();
                    }
                }

                $sar = explode($de, $seg);
                $lxkey = (isset($sar[1]) && $sar[1]) ? $sar[1] : ''; // identify a grouping for claim info
                $lx_ar[$lxkey] = array();
                //
                $loopid = '2000';
                $cls = 'lx';
                //$lx_ct = count($lx_ar);
                $lx_html .= ($lxkey) ? "<tr class='" . attr($cls) . "'><td colspan=4><em>Claim Group</em> " . text($lxkey) . "</td></tr>" . PHP_EOL : "";
                continue;
            }

            //
            if (strncmp('TS3' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                // this looks like a medicare part A or hospital remittance segment
                // segment TS2 gives DRG totals -- not read in this sequence. If you need it, code it
                $loopid = '2000';
                $ts301 = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';                                // Provider ID
                $ts302 = (isset($sar[2]) && $sar[2]) ? $cd27x->get_271_code('POS', $sar[2]) : '';  // Facility Code (place of service)
                $ts303 = (isset($sar[3]) && $sar[3]) ? edih_format_date($sar[3]) : '';              // date - last day of provider fiscal year
                $ts304 = (isset($sar[4]) && $sar[4]) ? $sar[4] : '';                                // quantity
                $ts305 = (isset($sar[5]) && $sar[5]) ? edih_format_money($sar[5]) : '';             // monetary amount
                //
                $lx_html .= "<tr class='" . attr($cls) . "'><td><em>Prv</em> " . text($ts301) . "</td><td colspan=3>" . text($ts302) . " <em>Count</em> " . text($ts304) . " <em>Amount</em> " . text($ts305) . "</td></tr>" . PHP_EOL;
                //
                // Medicare Part A
                $tr1 = "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>";
                $tr2 = "</td></tr>" . PHP_EOL;
                //
                $lx_html .= (isset($sar[13]) && $sar[13]) ? $tr1 . "Total MSP Payer Amt: " . text(edih_format_money($sar[13])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[15]) && $sar[15]) ? $tr1 . "Total Non-Lab Chrg Amt: " . text(edih_format_money($sar[15])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[17]) && $sar[17]) ? $tr1 . "Total HCPCS Rpt Chrg Amt: " . text(edih_format_money($sar[17])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[18]) && $sar[18]) ? $tr1 . "Total HCPCS Payable Amt: " . text(edih_format_money($sar[18])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[20]) && $sar[20]) ? $tr1 . "Total Prof Cmpnt Amt: " . text(edih_format_money($sar[20])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[21]) && $sar[21]) ? $tr1 . "Total MSP Pt Liab Met Amt: " . text(edih_format_money($sar[21])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[22]) && $sar[22]) ? $tr1 . "Total MSP Pt Reimb Amt: " . text(edih_format_money($sar[22])) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[23]) && $sar[23]) ? $tr1 . "Total PIP Claim Count: " . text($sar[23]) . $tr2 . PHP_EOL : "";
                $lx_html .= (isset($sar[24]) && $sar[24]) ? $tr1 . "Total PIP Claim Count: " . text(edih_format_money($sar[24])) . $tr2 . PHP_EOL : "";
                //
                continue;
            }

            //
            if (strncmp('TS2' . $de, $seg, 4) === 0) {
                csv_edihist_log("edih_835_transaction_html: segment TS2 present in $fn");
                // Medicare Part A
                $tr1 = "<tr class='" . attr($cls) . "'><td>&gt;</td><td colspan=3>";
                $tr2 = "</td></tr>" . PHP_EOL;
                //
                $lx_html .= (isset($sar[1]) && $sar[1]) ? $tr1 . "Total DRG Amt: " . text(edih_format_money($sar[1])) . $tr2 : "";
                $lx_html .= (isset($sar[2]) && $sar[2]) ? $tr1 . "Total Fed Specific Amt: " . text(edih_format_money($sar[2])) . $tr2 : "";
                $lx_html .= (isset($sar[3]) && $sar[3]) ? $tr1 . "Total Hosp Specific Amt: " . text(edih_format_money($sar[3])) . $tr2 : "";
                $lx_html .= (isset($sar[4]) && $sar[4]) ? $tr1 . "Total DSP Share Amt: " . text(edih_format_money($sar[4])) . $tr2 : "";
                $lx_html .= (isset($sar[5]) && $sar[5]) ? $tr1 . "Total Capital Amt: " . text(edih_format_money($sar[5])) . $tr2 : "";
                $lx_html .= (isset($sar[6]) && $sar[6]) ? $tr1 . "Total Ind Med Edu Amt: " . text(edih_format_money($sar[6])) . $tr2 : "";
                $lx_html .= (isset($sar[7]) && $sar[7]) ? $tr1 . "Total Outlier Day Amt: " . text(edih_format_money($sar[7])) . $tr2 : "";
                $lx_html .= (isset($sar[8]) && $sar[8]) ? $tr1 . "Total Day Outlier Day Amt: " . text(edih_format_money($sar[8])) . $tr2 : "";
                $lx_html .= (isset($sar[9]) && $sar[9]) ? $tr1 . "Total Cost Outlier Day Amt: " . text(edih_format_money($sar[9])) . $tr2 : "";
                $lx_html .= (isset($sar[10]) && $sar[10]) ? $tr1 . "Avg DRG Length of Stay: " . text($sar[10]) . $tr2 : "";
                $lx_html .= (isset($sar[11]) && $sar[11]) ? $tr1 . "Total Discharge Count: " . text($sar[11]) . $tr2 : "";
                $lx_html .= (isset($sar[12]) && $sar[12]) ? $tr1 . "Total Cost Rpt Day Count: " . text($sar[12]) . $tr2 : "";
                $lx_html .= (isset($sar[13]) && $sar[13]) ? $tr1 . "Total Covered Day Count: " . text($sar[13]) . $tr2 : "";
                $lx_html .= (isset($sar[14]) && $sar[14]) ? $tr1 . "Total Non Covered Day Count: " . text($sar[14]) . $tr2 : "";
                $lx_html .= (isset($sar[15]) && $sar[15]) ? $tr1 . "Total MSP Pass-Thru Amt: " . text(edih_format_money($sar[15])) . $tr2 : "";
                $lx_html .= (isset($sar[16]) && $sar[16]) ? $tr1 . "Avg DRG Weight: " . text($sar[16]) . $tr2 : "";
                $lx_html .= (isset($sar[17]) && $sar[17]) ? $tr1 . "Total PPS Capital FSP DRG Amt: " . text(edih_format_money($sar[17])) . $tr2 : "";
                $lx_html .= (isset($sar[18]) && $sar[18]) ? $tr1 . "Total PPS Capital FSP HSP Amt: " . text(edih_format_money($sar[18])) . $tr2 : "";
                $lx_html .= (isset($sar[19]) && $sar[19]) ? $tr1 . "Total PPS DSH DRG Amt: " . text(edih_format_money($sar[19])) . $tr2 : "";
                //
                continue;
            }

            if (strncmp('PLB' . $de, $seg, 4) === 0) {
                // can signal end of claim transaction
                $loopid = 'summary';
                $cls = 'pmt';
                //if (count($clpsegs)) {
                    //$clp_html .= edih_835_transaction_html($clpsegs, $codes27x, $codes835, $delimiters);
                    //$clpsegs = array();
                //}
                //
                $sar = explode($de, $seg);
                // provider ID and fiscal year end date
                $plb01 = (isset($sar[1]) && $sar[1]) ? $sar[1] : "";
                $plb02 = (isset($sar[2]) && $sar[2]) ? edih_format_date($sar[2]) : "";
                //
                $pmt_html .= "<tr class='" . attr($cls) . "'><td><em>Provider</em></td><td colspan=3>" . text($plb01 . " " . $plb02) . "</td></tr>" . PHP_EOL;
                //
                $plbar = array_slice($sar, 2);
                $plbar = array_chunk($plbar, 2);
                // reason code and amount
                foreach ($plbar as $plb) {
                    foreach ($plb as $k => $p) {
                        // PLB 3, 5, 7, 9, 11, 13
                        // composite element 'code:reference'
                        if ($k == 0) {
                            if ($p && strpos($p, $ds)) {
                                $plb_rc = substr($p, 0, strpos($p, $ds));   // code
                                $plb_tr = substr($p, strpos($p, $ds) + 1);    // reference (case #)?
                            } else {
                                $plb_rc = ($p) ? $p : "";
                                $plb_tr = "";
                            }

                            $plb_rt = ($plb_rc) ? $cd835->get_835_code('PLB', $plb_rc) : "";
                        } else {
                            // PLB 4, 6, 8, 10, 12, 14
                            // monetary amount
                            $plb_amt = ($p) ? edih_format_money($p) : "";
                            $acctng['plbadj'] +=  ($p) ? (float)$p : 0;
                        }
                    }

                    $pmt_html .= "<tr class='" . attr($cls) . "'><td>" . text($plb_tr) . "</td><td colspan=3>" . text($plb_rc . " " . $plb_rt . " " . $plb_amt) . "</td></tr>" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strncmp('SE' . $de, $seg, 3) === 0) {
                // end of payment transaction, so create the html page
                $loopid = 'trailer';
                $cls = 'pmt';
                // include our accounting totals
                if (is_array($acctng) && count($acctng)) {
                    array_walk($acctng, 'edih_round_cb');
                    $bal = ($acctng['fee'] == ($acctng['pmt'] + $acctng['clmadj'] + $acctng['svcadj'] + $acctng['svcptrsp'] + $acctng['plbadj']) ) ? "Balanced" : "Not Balanced";
                    $acct_str = text($bal) . ": <em>Fee</em> " . text($acctng['fee']) . " <em>Pmt</em> " . text($acctng['pmt']) . " ";
                    $acct_str .= "<em>ClpAdj</em> " . text($acctng['clmadj']) . " <em>SvcAdj</em> " . text($acctng['svcadj']) . " ";
                    $acct_str .= "<em>PtRsp</em> " . text($acctng['ptrsp']) . " (<em>svcPtRsp</em> " . text($acctng['svcptrsp']) . ") <em>PlbAdj</em> " . text($acctng['plbadj']) . " ";
                    //
                    $pmt_html .= "<tr class='" . attr($cls) . "'><td colspan=4>$acct_str</td></tr>" . PHP_EOL;
                }

                //
                // create the html page
                $str_html .= "<table id=" . attr($tblid) . " class='h835' columns=4><caption>" . text($capstr) . "</caption>" . PHP_EOL;
                $str_html .= $hdr_html;
                if ($pmt_html) {
                    $str_html .= $pmt_html;
                    $pmt_html = "";
                }

                if ($src_html) {
                    $str_html .= $src_html;
                    $src_html = "";
                }

                if ($rcv_html) {
                    $str_html .= $rcv_html;
                    $rcv_html = "";
                }

                //
                if (count($lx_ar)) {
                    // claim segments are in lx array
                    // make sure we have current collection
                    if ($lxkey && array_key_exists($lxkey, $lx_ar)) {
                        if (count($clpsegs)) {
                            $clp_html .= edih_835_transaction_html($clpsegs, $codes27x, $codes835, $delimiters);
                            $clpsegs = array();
                        }

                        // note: table ending in CLP if stanza
                        $nlx_html = "<table name='lx_" . attr($lxkey) . "' class='h835c' columns=4>" . PHP_EOL . "<tbody>" . PHP_EOL . $lx_html . PHP_EOL;
                        $lx_ar[$lxkey]['lx'] = $nlx_html;
                        $lx_ar[$lxkey]['clp'] = $clp_html;
                        $lx_html = "";
                        $clp_html = "";
                        $clpsegs = array();
                    }

                    // append segments to html
                    foreach ($lx_ar as $key => $val) {
                        $str_html .= $val['lx'];
                        $str_html .= $val['clp'];
                    }
                } elseif ($lx_html) {
                    $str_html .= $lx_html;
                    $lx_html = "";
                }

                //
                if (count($clpsegs)) {
                    // would be captured in LX and lx array
                    $clp_html .= edih_835_transaction_html($clpsegs, $codes27x, $codes835, $delimiters);
                    $clpsegs = array();
                }

                if ($clp_html) {
                    $str_html .= $clp_html;
                    $clp_html = "";
                }

                if ($trl_html) {
                    $str_html .= $trl_html;
                    $trl_html = "";
                }

                //$str_html .= "</tbody>".PHP_EOL."</table>".PHP_EOL;
                //
                continue;
            }

            if (strncmp('CLP' . $de, $seg, 4) === 0) {
                if ($loopid == '1000B') {
                    // end of 1000B (receiver) loop
                    $rcv_html .= ($clp_ct) ? "" : "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
                } elseif ($loopid == '2000') {
                    // end of LX header (LX TS3 TS2 claim grouping)
                    $lx_html .= ($clp_ct) ? "" : "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
                }

                $loopid = '2100';
                //array('pmt'=>0, 'clmpmt'=>0, 'clmadj'=0, 'prvadj'=>0, 'ptrsp'=>0,'lx'=>array());
                $sar = explode($de, $seg);
                $acctng['fee'] += (isset($sar[3]) && $sar[3]) ? (float)$sar[3] : 0;
                $acctng['clmpmt'] += (isset($sar[4]) && $sar[4]) ? (float)$sar[4] : 0;
                $acctng['ptrsp'] += (isset($sar[5]) && $sar[5]) ? (float)$sar[5] : 0;
                //
                if (count($clpsegs)) {
                    $clp_html .= edih_835_transaction_html($clpsegs, $codes27x, $codes835, $delimiters);
                }

                $clpsegs = array();
                $clpsegs[] = $seg;
                $clp_ct++;
                continue;
            }

            if (strncmp('SVC' . $de, $seg, 4) === 0) {
                $loopid = '2110';
                $sar = explode($de, $seg);
                $pmtm = $pmts = 1;
                foreach ($sar as $k => $v) {
                    if ($k == 2) {
                        $svcfee = ($v) ? (float)$v : 0;
                    } elseif ($k == 3) {
                        $svcpmt = ($v) ? (float)$v : 0;
                    } elseif ($k == 5) {
                        $pmtm = ($v) ? (int)$v : 1;
                    } elseif ($k == 7) {
                        $pmts = ($v) ? (int)$v : 1;
                    }
                }

                $acctng['svcfee'] += $svcfee * $pmts;
                $acctng['svcpmt'] += $svcpmt * $pmtm;
                //
                $clpsegs[] = $seg;
                continue;
            }

            if (strncmp('CAS' . $de, $seg, 4) === 0) {
                $sar = explode($de, $seg);
                // category
                $ctg = (isset($sar[1]) && $sar[1]) ? $sar[1] : 'CO';
                // slice sar array to get triplet elements
                // chunk into triplets
                $sar1 = array_slice($sar, 2);
                $sar1 = array_chunk($sar1, 3);
                //
                foreach ($sar1 as $cas) {
                    $cav = 0;
                    $cq = '';
                    foreach ($cas as $k => $v) {
                        if ($k == 1) {
                            // monetary amount elem 3, 6, 9, 12, 15, 18
                            $cav = ($v) ?  $v : 0;
                        } elseif ($k == 2) {
                            // quantity elem 4, 7, 10, 13, 16, 19
                            $cq =  ($v) ? $v : "";
                            if ($cq && strcmp($cq, '1') > 0) {
                                $cav = $cav * $cq;
                            }
                        }
                    }

                    if ($ctg == 'PR') {
                        $acctng['svcptrsp'] += ($cav) ? (float)$cav : 0;
                    } else {
                        $acctky = ($loopid == '2100') ? 'clmadj' : 'svcadj';
                        $acctng[$acctky] += ($cav) ? (float)$cav : 0;
                    }
                }

                $clpsegs[] = $seg;
                continue;
            }

            // uncaught segments should be routed by this
            if ($loopid == '2100' || $loopid == '2110') {
                $clpsegs[] = $seg;
                continue;
            }
        } // end foreach(trans as seg)
    } // end foreach(trans_ar as trans)
    //
    return $str_html;
}


/**
 * create a display for an 835 claim payment file or transaction
 *
 * @uses csv_check_x12_obj()
 *
 * @param string  $filename the filename
 * @param string  TRN02 identifier from 835 check ir EFT
 * @param string  CLM01 identifier from 837 CLM
 *
 * @return string  error message or a table with file information
 */
function edih_835_html($filename, $trace = '', $clm01 = '', $summary = false)
{
    //
    $html_str = '';
    //
    if (trim($filename)) {
        $obj835 = csv_check_x12_obj($filename, 'f835');
        if ($obj835 && 'edih_x12_file' == get_class($obj835)) {
            $fn = $obj835->edih_filename();
            $delims = $obj835->edih_delimiters();
            $env_ar = $obj835->edih_x12_envelopes();
            //
            $de = (isset($delims['e'])) ? $delims['e'] : '';
            $ds = (isset($delims['s'])) ? $delims['s'] : '';
            $dr = (isset($delims['r'])) ? $delims['r'] : '';
                // $dr is not used, but just in case
        } else {
            $html_str .= "<p>edih_835_html: invalid file name</p>" . PHP_EOL;
            return $html_str;
        }
    } else {
        $html_str .= "Error in file name or file parsing <br />" . PHP_EOL;
        csv_edihist_log("edih_835_html: error in parsing file $filename");
        return $html_str;
    }

    if ($de && $ds) {
        // note $dr, repetition separator, is not always available
        $cd27x = new edih_271_codes($ds, $dr);
        $cd835 = new edih_835_codes($ds, $dr);
    } else {
        csv_edihist_log("edih_835_html: Did not get delimiters");
        $html_str .= "<p>Did not get delimiters for " . text($fn) . "</p>" . PHP_EOL;
        return $html_str;
    }

    //
    // if given, one only of trace or clm01
    $pid = $chk = '';
    if ($clm01) {
        $pid = trim((string)$clm01);
    } elseif ($trace) {
        $chk = trim((string)$trace);
    }

    //
    if ($pid) {
        $clp_ar = $obj835->edih_x12_transaction($pid);
        // $clp_ar is array[i][j]
        if (count($clp_ar)) {
            if ($summary) {
                $html_str .= edih_835_clp_summary($clp_ar, $cd27x, $cd835, $delims, $fn);
            } else {
                // create a table for this transaction for jquery-ui dialog
                $html_str .= edih_835_transaction_html($clp_ar, $cd27x, $cd835, $delims, $fn);
            }
        } else {
            csv_edihist_log("edih_835_html: Did not find PtID $pid in $fn");
            $html_str .= "<p>Did not find PtID " . text($pid) . " in " . text($fn) . "</p>" . PHP_EOL;
            return $html_str;
        }
    } elseif ($chk) {
        // check detail
        if (isset($env_ar['ST']) && count($env_ar['ST'])) {
            $trans_ar = array();
            foreach ($env_ar['ST'] as $st) {
                if ($st['trace'] != $chk) {
                    continue;
                }

                $trans_ar[] = $obj835->edih_x12_slice(array('trace' => $chk));
            }
        } else {
            csv_edihist_log("edih_835_transaction_html: Did not get envelopes information for $fn");
            $html_str .= "<p>Did not get envelopes information for " . text($fn) . "</p>" . PHP_EOL;
            return $html_str;
        }

        if (is_array($trans_ar) && count($trans_ar)) {
            // $trans_ar is a slice, array[i]
            $html_str .= edih_835_payment_html($trans_ar, $cd27x, $cd835, $delims, $fn);
        } else {
            csv_edihist_log("edih_835_transaction_html: Did not find trace $chk in $fn");
            $html_str .= "<p>Did not find trace " . text($chk) . " in " . text($fn) . "</p>" . PHP_EOL;
            return $html_str;
        }
    } else {
        // entire file
        if (isset($env_ar['ST']) && count($env_ar['ST'])) {
            $trans_ar = array();
            foreach ($env_ar['ST'] as $st) {
                $trans_ar[] = $obj835->edih_x12_slice(array('trace' => $st['trace']));
            }
        } else {
            csv_edihist_log("edih_835_transaction_html: Did not envelopes information for $fn");
            $html_str .= "<p>Did not get envelopes information for " . text($fn) . "</p>" . PHP_EOL;
            return $html_str;
        }

        if (is_array($trans_ar) && count($trans_ar)) {
            // $trans_ar is a slice, array[i]
            $html_str .= edih_835_payment_html($trans_ar, $cd27x, $cd835, $delims, $fn);
        } else {
            csv_edihist_log("edih_835_transaction_html: Did not get ST envelopes for $fn");
            $html_str .= "<p>Did not get ST envelopes for " . text($fn) . "</p>" . PHP_EOL;
            return $html_str;
        }
    }

    //
    return $html_str;
}
