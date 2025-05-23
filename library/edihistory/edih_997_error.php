<?php

/*
 * edih_997_error.php
 *
 * Copyright 2016 Kevin McCormick Longview, Texas
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 or later.  You should have
 * received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *  <http://opensource.org/licenses/gpl-license.php>
 *
 *
 * @author Kevin McCormick
 * @link: https://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */

use OpenEMR\Common\Csrf\CsrfUtils;

// codes used in 997/999 files;
//require_once './codes/edih_997_codes.php';

/**
 * Look up file name by control number
 *
 * @param string
 * @param string
 *
 * @return string
 */
function edih_997_sbmtfile($icn, $filetype)
{
    //
    if (strlen($icn) == 13) {
        $bticn = substr($icn, 0, 9);
        $stn = substr($icn, -4);
    } else {
        $bticn = $icn;
    }

    if (is_numeric($filetype)) {
        $ftp = 'f' . $filetype;
    } else {
        $ftp = $filetype;
    }

    //
    $btfn = csv_file_by_controlnum($ftp, $bticn);
    $bfullpath = ($btfn) ? csv_check_filepath($btfn, $ftp) : '';
    //
    return $bfullpath;
}

/**
 * Extract information on rejected files or transactions
 *
 * @param object
 * @return array
 */
function edih_997_errdata($obj997)
{
    //
    $segments = $obj997->edih_segments();
    $delims = $obj997->edih_delimiters();
    $de = $delims['e'];
    $ds = $delims['s'];
    $dr = $delims['r'];
    //
    $dt = '~';
    //
    $diag = array();
    $diag['err'] = array();
    $iserr = false;
    $batchfile = '';
    $idx = -1;
    //
    foreach ($segments as $seg) {
        $sar = array();
        if (strncmp($seg, 'TA1' . $de, 4) == 0) {
            $sar = explode($de, $seg);
            //
            $sub_icn = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';
            $subdate = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';
            $subtime = (isset($sar[3]) && $sar[3]) ? $sar[3] : '';
            $ackcode = (isset($sar[4]) && $sar[4]) ? $sar[4] : '';
            $acknote = (isset($sar[5]) && $sar[5]) ? $sar[5] : '';
            //

            continue;
        }

        if (strncmp($seg, 'AK1' . $de, 4) == 0) {
            $sar = explode($de, $seg);
            //
            $fg_type = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';
            $fg_id = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';
            //
            continue;
        }

        if (strncmp($seg, 'AK2' . $de, 4) == 0 || strncmp($seg, 'IK2' . $de, 4) == 0) {
            $sar = explode($de, $seg);
            //
            $iserr = false;
            //
            $subtype = (isset($sar[1]) && $sar[1]) ? $sar[1] : '';
            $substn = (isset($sar[2]) && $sar[2]) ? $sar[2] : '';
            // AK2*837*0001
            continue;
        }

        if (strncmp($seg, 'AK3' . $de, 4) == 0 || strncmp($seg, 'IK3' . $de, 4) == 0) {
            $sar = explode($de, $seg);
            //$idx = count($diag);
            $idx++;
            $iserr = true;
            //
            $diag['err'][$idx]['subtype'] = 'f' . $subtype;
            $diag['err'][$idx]['substn'] = $substn;
            //
            $diag['err'][$idx]['ik3segid'] = (isset($sar[1])) ?  $sar[1] : '';
            $diag['err'][$idx]['ik3segpos'] = (isset($sar[2])) ?  $sar[2] : '';
            $diag['err'][$idx]['ik3loop'] = (isset($sar[3])) ?  $sar[3] : '';
            $diag['err'][$idx]['ik3code'] = (isset($sar[4])) ?  $sar[4] : '';
            //
            continue;
        }

        if (strncmp($seg, 'CTX' . $de, 4) == 0) {
            $sar = explode($de, $seg);
            if (isset($sar[1]) && strpos($sar[1], 'TRIG')) {
                // CTX*SITUATIONAL TRIGGER*
                $diag['err'][$idx]['ctxid'] = (isset($sar[2])) ?  $sar[2] : '';
                $diag['err'][$idx]['ctxpos'] = (isset($sar[3])) ?  $sar[3] : '';
                $diag['err'][$idx]['ctxloop'] = (isset($sar[4])) ?  $sar[4] : '';
                $diag['err'][$idx]['ctxelem'] = (isset($sar[5])) ?  $sar[5] : '';
                // $sar[6] Reference in Segment
                // Data Element Reference Number : Data Element Reference Number Composite
            } else {
                // business unit identifier
                $diag['err'][$idx]['ctxacct'] =  (isset($sar[2])) ?  $sar[2] : '';
            }

            //
            continue;
        }

        if (strncmp($seg, 'AK4' . $de, 4) == 0 || strncmp($seg, 'IK4' . $de, 4) == 0) {
            $sar = explode($de, $seg);
            $diag['err'][$idx]['ik401'] = (isset($sar[1])) ?  $sar[1] : '';
            $diag['err'][$idx]['ik402'] = (isset($sar[2])) ?  $sar[2] : '';
            $diag['err'][$idx]['ik403'] = (isset($sar[3])) ?  $sar[3] : '';
            $diag['err'][$idx]['ik404'] = (isset($sar[4])) ?  $sar[4] : '';
            //
            continue;
        }

        if (strncmp($seg, 'AK5' . $de, 4) == 0 || strncmp($seg, 'IK5' . $de, 4) == 0) {
            if ($iserr) {
                $sar = explode($de, $seg);
                $diag['err'][$idx]['ik501'] = (isset($sar[1])) ?  $sar[1] : '';
                $diag['err'][$idx]['ik502'] = (isset($sar[2])) ?  $sar[2] : '';
                $diag['err'][$idx]['ik503'] = (isset($sar[3])) ?  $sar[3] : '';
                $diag['err'][$idx]['ik504'] = (isset($sar[4])) ?  $sar[4] : '';
                $diag['err'][$idx]['ik505'] = (isset($sar[5])) ?  $sar[5] : '';
                //
                $iserr = false;
            }

                //
            continue;
        }

        if (strncmp($seg, 'AK9' . $de, 4) == 0) {
            $diag['summary']['sub_icn'] = $sub_icn;
            $diag['summary']['subtype'] = $subtype;
            $diag['summary']['subdate'] = $subdate;
            $diag['summary']['subtime'] = $subtime;
            $diag['summary']['ackcode'] = $ackcode;
            $diag['summary']['acknote'] = $acknote;
            $diag['summary']['fg_type'] = $fg_type;
            $diag['summary']['fg_id'] = $fg_id;
            //
            $sar = explode($de, $seg);
            $diag['summary']['ak901'] = (isset($sar[1])) ?  $sar[1] : ''; // AK901 A=Accepted R=Rejected.
            $diag['summary']['ak902'] = (isset($sar[2])) ?  $sar[2] : ''; // AK902  number of transaction sets
            $diag['summary']['ak903'] = (isset($sar[3])) ?  $sar[3] : ''; // AK903  number of transaction sets received by the translator.
            $diag['summary']['ak904'] = (isset($sar[4])) ?  $sar[4] : ''; // AK904  number of transaction sets accepted by the translator.
            $diag['summary']['ak905'] = (isset($sar[5])) ?  $sar[5] : ''; // codes
            $diag['summary']['ak906'] = (isset($sar[6])) ?  $sar[6] : '';
            $diag['summary']['ak907'] = (isset($sar[7])) ?  $sar[7] : '';
            $diag['summary']['ak908'] = (isset($sar[8])) ?  $sar[8] : '';
            $diag['summary']['ak909'] = (isset($sar[9])) ?  $sar[9] : '';
            //
            continue;
        }
    }

    return $diag;
}


/**
 * Create an html report on rejected files or transactions
 *
 * @uses edih_997_ta1_code()
 * @uses edih_997_code_text()
 * @uses edih_rsp_st_match()
 *
 * @param object
 * @return array
 */
function edih_997_err_report($err_array)
{
    //
    if (!is_array($err_array) || !count($err_array)) {
        $str_html = "Error: invalid argument for error report";
        csv_edihist_log('edih_997_err_report: invalid function argument');
        return $str_html;
    }

    //
    $str_html = "";
    $batchfile = "";
    //
    if (isset($err_array['summary'])) {
        extract($err_array['summary'], EXTR_OVERWRITE);
        //
        $str_html .= "<p class='rpt997'>" . PHP_EOL;
        $str_html .= (isset($sub_icn)) ? "<em>Submitted ICN</em>" . text($sub_icn) : "Submitted file unknown";
        $str_html .= (isset($subdate)) ? " <em>Date</em> " . text(edih_format_date($subdate)) : "";
        $str_html .= (isset($subtime)) ? " <em>Time</em> " . text($subtime) . "<br />" : "<br />";
        $str_html .= (isset($ackcode)) ? " TA1 $ackcode : " . text(edih_997_ta1_code($ackcode)) . " <br />" : "";
        $str_html .= (isset($acknote)) ? " TA1 $acknote : " . text(edih_997_ta1_code($acknote)) . " <br />" . PHP_EOL : "<br />" . PHP_EOL;
        if (isset($fg_type)) {
            $fgtp = csv_file_type($fg_type);
            $str_html .= " <em>Functional Group Type</em> " . text($fg_type) . " (" . text($fgtp) . ")";
            $str_html .= (isset($fg_id)) ? " <em>GS06</em> " . text($fg_id) . " <br />" . PHP_EOL : "<br />" . PHP_EOL;
        }

        //
        //$str_html .= "</p>".PHP_EOL;
        //
        $str_html .= (isset($ak901)) ? "999/997 $ak901 " . text(edih_997_code_text('ak501', $ak901)) . "<br />" : "";
        $str_html .= (isset($ak902)) ? " Transactions: submitted $ak902" : " ";
        $str_html .= (isset($ak903)) ? " received $ak903" : "";
        $str_html .= (isset($ak904)) ? " accepted $ak904" : "";
        $str_html .= (isset($ak905) && $ak905) ? "<br />$ak905 " . text(edih_997_code_text('ak502', $ak905)) . "<br />" : "";
        $str_html .= (isset($ak906) && $ak906) ? $ak906 . " " . text(edih_997_code_text('ak502', $ak906)) . "<br />" : "";
        $str_html .= (isset($ak907) && $ak907) ? $ak907 . " " . text(edih_997_code_text('ak502', $ak907)) . "<br />" : "";
        $str_html .= (isset($ak908) && $ak908) ? $ak908 . " " . text(edih_997_code_text('ak502', $ak908)) . "<br />" : "";
        $str_html .= (isset($ak909) && $ak909) ? $ak909 . " " . text(edih_997_code_text('ak502', $ak909)) . "<br />" : "";
        //
        $str_html .= "</p>" . PHP_EOL;
    }

    //
    foreach ($err_array['err'] as $k => $v) {
        //
        $ct = $k + 1;
        $icn = (isset($sub_icn)) ? $sub_icn : '';
        $stn = (isset($v['substn'])) ? $v['substn'] : '';
        $rtp = (isset($v['subtype'])) ? $v['subtype'] : '';
        //
        $str_html .= "<p class='err997'>" . PHP_EOL;
        $str_html .= "Error " . text($ct) . " ";
        $str_html .= ($stn) ? "<em>ST</em> " . text($stn) . " <br />" : "<br />";
        //
        if ($icn && $stn && $rtp) {
            $trc = sprintf("%s%04d", $icn, $stn);
            $srch = array('s_val' => $trc, 's_col' => 4,'r_cols' => 'All');
            // array('s_val'=>'0024', 's_col'=>9, 'r_cols'=>array(1, 2, 7)),
            $trn_ar = csv_search_record($rtp, 'claim', $srch);
            if (is_array($trn_ar) && count($trn_ar)) {
                //'f837':array('PtName', 'SvcDate', 'CLM01', 'InsLevel', 'BHT03', 'FileName', 'Fee', 'PtPaid', 'Provider' );
                //'f276':array('PtName', 'SvcDate', 'CLM01', 'ClaimID', 'BHT03', 'FileName', 'Payer', 'Trace'); break;
                //'f270':array('PtName', 'ReqDate', 'Trace', 'InsBnft', 'BHT03', 'FileName', 'Payer'); break;
                $pt_name = $trn_ar[0][0]; // $trn_ar['PtName'];
                $clm01 = ($rtp == 'f837') ? $trn_ar[0][2] : $trn_ar[0][4]; // $trn_ar['CLM01'] : $trn_ar['BHT03'];
                $svcdate = $trn_ar[0][1]; // ($rtp == 'f270') ? $trn_ar['ReqDate'] : $trn_ar['SvcDate'];
                $btfn = $trn_ar[0][5]; // $trn_ar['FileName'];
                $str_html .= text($pt_name) . " " . text($svcdate) . " <em>Trace</em> <a class='rpt' href='edih_main.php?gtbl=claim&fname=" . attr_url($btfn) . "&ftype=" . attr_url($rtp) . "&pid=" . attr_url($clm01) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($clm01) . "</a> <br />" . PHP_EOL;
            } else {
                $str_html .= "Unable to locate transaction  <em>Trace</em> " . text($trc) . " <br />" . PHP_EOL;
            }
        } else {
            $str_html .= "Unable to trace, did not get all of icn, type, and st number <br />" . PHP_EOL;
        }

        //
        $str_html .= (isset($v['ctxacct'])) ? "<em>Transaction ID</em> " . text($v['ctxacct']) : "";
        $str_html .= (isset($v['ik3segid'])) ? " Segment <em>ID</em> " . text($v['ik3segid']) : "";
        $str_html .= (isset($v['ik3segpos'])) ? " <em>Position</em> " . text($v['ik3segpos']) : "";
        $str_html .= (isset($v['ik3loop'])) ? " <em>Loop</em> " . text($v['ik3loop']) : "";
        $str_html .= (isset($v['ik3code'])) ? "<br /> <em>Code</em> " . text($v['ik3code']) . " " . text(edih_997_code_text('ak304', $v['ik3code'])) . "<br />" : "<br />";
        //
        $str_html .= (isset($v['ctxid'])) ? "Situational " . PHP_EOL . "<em>Segment</em> " . text($v['ctxid']) : "";
        $str_html .= (isset($v['ctxpos'])) ? " <em>Position</em> " . text($v['ctxpos']) : "";
        $str_html .= (isset($v['ctxloop'])) ? " <em>Position</em> " . text($v['ctxloop']) : "";
        $str_html .= (isset($v['ctxelem'])) ? " <em>Element</em> " . text($v['ctxelem']) . "<br />" . PHP_EOL : PHP_EOL;
        //
        $str_html .= (isset($v['ik401'])) ?  "Data Element <em>element</em> " . text($v['ik401']) : "";
        $str_html .= (isset($v['ik402'])) ?  " <em>ref</em> " . text($v['ik402']) : "";
        $str_html .= (isset($v['ik404'])) ?  " <em>data</em> " . text($v['ik404']) : "";
        $str_html .= (isset($v['ik403'])) ? "<br /> <em>code</em> " . text($v['ik403']) . " " . text(edih_997_code_text('ak403', $v['ik403'])) . "<br />" : "<br />";
        //
        $str_html .= (isset($v['ik501']) && $v['ik501']) ?  "<em>Status</em> " . text($v['ik501']) . " " . text(edih_997_code_text('ak501', $v['ik501'])) . "<br />" : "";
        $str_html .= (isset($v['ik502']) && $v['ik502']) ?  " <em>code</em> " . text($v['ik502']) . " " . text(edih_997_code_text('ak502', $v['ik502'])) . "<br />" : "";
        $str_html .= (isset($v['ik503']) && $v['ik503']) ?  " <em>code</em> " . text($v['ik503']) . " " . text(edih_997_code_text('ak502', $v['ik503'])) . "<br />" : "";
        $str_html .= (isset($v['ik504']) && $v['ik504']) ?  " <em>code</em> " . text($v['ik504']) . " " . text(edih_997_code_text('ak502', $v['ik504'])) . "<br />" : "";
        $str_html .= (isset($v['ik505']) && $v['ik505']) ?  " <em>code</em> " . text($v['ik505']) . " " . text(edih_997_code_text('ak502', $v['ik505'])) . "<br />" : "";
        //
        $str_html .= "</p>" . PHP_EOL;
    }

    return $str_html;
}

/**
 * Main function in this script
 *
 * @uses csv_check_x12_obj()
 * @uses edih_997_errdata()
 * @uses edih_997_err_report()
 *
 * @param string
 * @return string
 */
function edih_997_error($filepath)
{
    //
    $html_str = '';
    //
    $obj997 = csv_check_x12_obj($filepath, 'f997');
    if ($obj997 && ('edih_x12_file' == get_class($obj997))) {
        $data = edih_997_errdata($obj997);
        $html_str .= edih_997_err_report($data);
    } else {
        $html_str .= "<p>Error: invalid file path</p>" . PHP_EOL;
        csv_edihist_log("edih_997_error: invalid file path $filepath");
    }

    return $html_str;
}
