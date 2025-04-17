<?php

/*
 * edih_csv_data.php
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
 *
 * @link: https://www.open-emr.org
 * @package ediHistory
 */

use OpenEMR\Common\Csrf\CsrfUtils;

/**
 * Display csv table data in an HTML table for newly processed files
 *
 * This function uses the csv table header row to order the csv data
 * The data array should be file table rows and claim table rows,
 * and have this structure:
 *    array[icn]['type'] => x12 type
 *    array[icn]['file'][i][array( --- data row---) ]
 *    array[icn]['claim'][i][array( --- data row---) ]
 *
 * @uses csv_table_header()
 * @uses csv_thead_html()
 * @param array
 * @param bool
 * @return string
 */
function edih_csv_process_html($data_ar, $err_only = false)
{
    //
    $str_html = '';
    $clm_html = '';
    //
    foreach ($data_ar as $icn => $csvdata) {
        if (array_key_exists('type', $csvdata)) {
            $ft = $csvdata['type'];
        } else {
            $str_html .= 'edih_csv_process_html() for ' . text($icn) . ' did not get type value';
            csv_edihist_log('edih_csv_process_html: for ' . $icn . ' did not get type value');
            return $str_html;
        }

        //
        $cls = (strpos('|f837|f270|f276|f278', $ft)) ? 'sub' : 'rsp';
        //
        if (array_key_exists('file', $csvdata)) {
            //
            $fidx = 0;
            foreach ($csvdata['file'] as $key => $csvfile) {
                //
                $oe = ( $fidx % 2 == 1 ) ? 'fodd' : 'feven';
                $cls = (strpos('|f837|f270|f276|f278', $ft)) ? 'sub' : 'rsp';
                //
                $dt_str = "";
                //
                $dte = (isset($csvfile['Date'])) ? $csvfile['Date'] : '';
                $fn1 = (isset($csvfile['FileName'])) ? $csvfile['FileName'] : '';
                $ctl = (isset($csvfile['Control'])) ? $csvfile['Control'] : '';
                $clmct = (isset($csvfile['Claim_ct'])) ? $csvfile['Claim_ct'] : '';
                $trc = (isset($csvfile['Trace'])) ? $csvfile['Trace'] : '';
                $typ = (isset($csvfile['RspType'])) ? $csvfile['RspType'] : '';
                $rej = (isset($csvfile['RejCt'])) ? $csvfile['RejCt'] : '';
                //
                $dt_str .= ($fn1) ? "<a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($fn1) . "</a>&nbsp;" : "";
                $dt_str .= ($dte) ? " &nbsp;" . text(substr($dte, 0, 4)) . "-" . text(substr($dte, 4, 2)) . "-" . text(substr($dte, 6, 2)) : "";
                $dt_str .= ($clmct) ? " &nbsp;<em>Claims</em> " . text($clmct) : "";
                if ($ft == 'f997' || $ft == 'ta1') {
                    $dt_str .= ($typ) ? " &nbsp;" . text($typ) : "";
                    $dt_str .= ($rej) ? " &nbsp;" . text($rej) : "";
                    $dt_str .= ($trc) ? "&nbsp; <a class='" . attr($cls) . "' title='" . attr($trc) . "' href='edih_main.php?gtbl=file&trace=" . attr_url($trc) . "&ftype=" . attr_url($ft) . "&rsptype=" . attr_url($typ) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'><em>trace</em></a>&nbsp;" : "";
                } elseif ($ft == 'f277') {
                    $dt_str .= (isset($csvfile['Accept'])) ? " &nbsp;<em>Accpt</em> " . text($csvfile['Accept']) : "";
                    $dt_str .= (isset($csvfile['Reject'])) ? " &nbsp;<em>Rej</em> " . text($csvfile['Reject']) : "";
                } elseif ($ft == 'f835') {
                    $dt_str .= (isset($csvfile['Denied'])) ? " &nbsp;<em>Denied</em> " . text($csvfile['Denied']) : "";
                    $dt_str .= ($trc) ? " &nbsp;<em>Chk</em> <a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn1) . "&trace=" . attr_url($trc) . "&ftype=" . attr_url($ft) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($trc) . "</a>&nbsp;" : "";
                    $dt_str .= (isset($csvfile['Payer'])) ? " &nbsp;" . text($csvfile['Payer']) : "";
                }

                //
                $str_html .= "<dt class=" . attr($oe) . ">$dt_str</dt>" . PHP_EOL;
                $fidx++;
            }
        }

        // &nbsp;&nbsp;
        // handle the claim array
        if (array_key_exists('claim', $csvdata)) {
            //
            $cidx = 0;
            $errct = 0;
            //
            foreach ($csvdata['claim'] as $claim) {
                if ($err_only) {
                    // output err_only is when claim status is a rejected type
                    if ($ft == 'f835') {
                        if (in_array($claim['Status'], array('1', '2', '3', '19', '20', '21'))) {
                            continue;
                        }
                    } elseif ($ft == 'f277') {
                        if (substr_count($claim['Status'], 'A1') || substr_count($claim['Status'], 'A2') || substr_count($claim['Status'], 'A5')) {
                            continue;
                        }
                    } elseif (strpos('|f997|f999|f271', $ft) && $claim['Status'] == 'A') {
                        continue;
                    } elseif (strpos('|f837|f270|f276', $ft)) {
                        continue;
                    }
                }

                //
                $errct++;
                $dd_str = "";
                $oe = ( $errct % 2 ) ? 'codd' : 'ceven';
                //
                $ptn = (isset($claim['PtName'])) ? $claim['PtName'] : '';
                $fn1 = (isset($claim['FileName'])) ? $claim['FileName'] : '';
                $ctl = (isset($claim['Control'])) ? $claim['Control'] : '';
                $pid = (isset($claim['CLM01'])) ? $claim['CLM01'] : '';
                $sts = (isset($claim['Status'])) ? $claim['Status'] : '';
                $err = (isset($claim['err_seg'])) ? $claim['err_seg'] : '';
                $trc = (isset($claim['Trace'])) ? $claim['Trace'] : '';
                $bht03 = (isset($claim['BHT03'])) ? $claim['BHT03'] : '';
                $pay = (isset($claim['Payer'])) ? $claim['Payer'] : '';
                $typ = (isset($csvfile['RspType'])) ? $claim['RspType'] : '';
                $auth = (isset($csvfile['Auth'])) ? $claim['Auth'] : '';
                //
                $ins = (isset($csvfile['InsBnft'])) ? $claim['InsBnft']  : '';
                $ins = (isset($csvfile['InsLevel'])) ? $claim['InsLevel'] : $ins;
                //
                $clm = (isset($csvfile['ClaimID'])) ? $claim['ClaimID'] : $ins;
                //
                $dte = (isset($claim['SvcDate'])) ? $claim['SvcDate'] : '';
                $dte = (isset($claim['ReqDate'])) ? $claim['ReqDate'] : $dte;
                $dte = (isset($claim['RspDate'])) ? $claim['RspDate'] : $dte;
                $dte = (isset($claim['FileDate'])) ? $claim['FileDate'] : $dte;

                $dd_str .= ($ptn) ? text($ptn) . "&nbsp; " : "";
                $dd_str .= ($dte) ? " &nbsp;" . text(substr($dte, 0, 4)) . "-" . text(substr($dte, 4, 2)) . "-" . text(substr($dte, 6, 2)) : "";
                if (strpos('|f277|f276|f270|f271|f278', $ft)) {
                    $dd_str .= ($sts) ? " &nbsp;" . text($sts) : "";
                    $dd_str .= ($ins) ? " &nbsp;" . text($ins) : "";
                    $dd_str .= ($clm) ? " &nbsp;" . text($clm) : "";
                    $dd_str .= ($bht03) ? " &nbsp;<em>view</em> <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&bht03=" . attr_url($bht03) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp; <a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&bht03=" . attr_url($bht03) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a>&nbsp;" : "";
                    $dd_str .= ($pid) ? " &nbsp;<em>trace</em> <a class='sub' href='edih_main.php?gtbl=claim&ftype=" . attr_url($ft) . "&rsptype=f837&trace=" . attr_url($pid) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($pid) . "</a>" : "";
                    $dd_str .= ($auth && $auth == 'Rsp' || $auth == 'Reply') ?  "<a class='sub' href='edih_main.php?gtbl=claim&ftype=" . attr_url($ft) . "&rsptype=f278&trace=" . attr_url($trc) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'><em>trace</em></a>" : "";
                } elseif ($ft == 'f835') {
                    $dd_str .= ($clm) ? " &nbsp;<em>Claim ID</em> $ins" : "";
                    $dd_str .= ($ins) ? " &nbsp;<em>InsLevel</em> $ins" : "";
                    $dd_str .= ($pid) ? " &nbsp;$pid <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&pid=" . attr_url($pid) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a> <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&pid=" . attr_url($pid) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a>" : "";
                } elseif ($ft == 'f997') {
                    $dd_str .= ($trc) ? " &nbsp;<a class='" . attr($cls) . "' title='" . attr($trc) . "' href='edih_main.php?gtbl=claim&ftype=" . attr_url($ft) . "&trace=" . attr_url($trc) . "&rsptype=" . attr_url($typ) . "&errseg=" . attr_url($err) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'><em>trace</em></a>" : "";
                    $dd_str .= ($err) ? " &nbsp;" . text(substr($err, 0, 8)) : "";
                } else {
                    $dd_str .= ($pid) ? " &nbsp;<a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&pid=" . attr_url($pid) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($pid) . "</a>" : "";
                }

                $dd_str .= ($fn1) ? " &nbsp;<a class='" . attr($cls) . "' title='" . attr($fn1) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn1) . "&ftype=" . attr_url($ft) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'><em>file</em></a>" : "";
                //
                $clm_html .= "<dd class='" . attr($oe) . "'>$dd_str</dd>" . PHP_EOL;
                //
            }
        }

        //
        if ($err_only) {
            if ($errct) {
                $str_html .= $clm_html;
            }
        } else {
            $str_html .= $clm_html;
        }
    }  // end foreach ($data_ar as $icn=>$csvdata)
    //
    return $str_html;
}

/**
 * make a list of brief information and links to claims where a problem
 * has been identified.
 *
 * @uses csv_file_type()
 * @uses edih_997_error()
 * @uses csv_denied_by_file()
 *
 * @param string
 * @param string
 * @param string
 *
 * @return string
 */
function edih_list_denied_claims($filetype, $filename, $trace = '')
{
    //
    $str_html = '';
    $row_ar = array();
    //
    $ft = csv_file_type($filetype);
    if ($ft == 'f997') {
        $str_html = edih_997_error($filename);
        return $str_html;
    } elseif (strpos('|f271|f277|f835', $ft)) {
        $row_ar = csv_denied_by_file($ft, $filename, $trace);
    } else {
        $str_html .= "Invalid file type " . text($filetype) . " for denied claim search<br />";
        csv_edihist_log("edih_list_denied_claims: wrong filetype $filetype");
        return $str_html;
    }

    //
    if (count($row_ar)) {
        $rw_ct = count($row_ar);
        $rwct = 0;
        $str_html .= "<h4>Denied/Rejected Claims Listing</h4>" . PHP_EOL;
        $str_html .= "<dl class ='" . attr($ft) . "'>" . text($rw_ct) . " claims <em>File</em>" .  text($filename) . " ";
        $str_html .= ($trace) ? "<em>Trace</em> $trace</dl>" . PHP_EOL : "" . PHP_EOL;
        if ($ft == 'f835') {
            foreach ($row_ar as $row) {
                $oe = ( $rwct % 2 ) ? 'codd' : 'ceven';
                $rwct++;
                $str_html .= "<dt class='" . attr($oe) . "'>" . text($row[0]) . " <a class='rpt' href='edih_main.php?gtbl=claim&fname=" . attr_url($row[5]) . "&ftype=" . attr_url($ft) . "&pid=" . attr_url($row[2]) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($row[2]) . "</a></dt>" . PHP_EOL;
            }
        } elseif ($ft == 'f277') {
            foreach ($row_ar as $row) {
                $oe = ( $rwct % 2 ) ? 'codd' : 'ceven';
                $rwct++;
                $str_html .= "<dt class='" . attr($oe) . "'>" . text($row[0]) . " <a class='rpt' href='edih_main.php?gtbl=claim&fname=" . attr_url($row[5]) . "&ftype=" . attr_url($ft) . "&bht03=" . attr_url($row[4]) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($row[4]) . "</a></dt>" . PHP_EOL;
            }
        } elseif ($ft == 'f271') {
            foreach ($row_ar as $row) {
                $oe = ( $rwct % 2 ) ? 'codd' : 'ceven';
                $rwct++;
                $str_html .= "<dt class='" . attr($oe) . "'>" . text($row[0]) . " <a class='rpt' href='edih_main.php?gtbl=claim&fname=" . attr_url($row[5]) . "&ftype=" . attr_url($ft) . "&bht03=" . attr_url($row[4]) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($row[4]) . "</a></dt>" . PHP_EOL;
            }
        } elseif ($ft == 'f997') {
            foreach ($row_ar as $row) {
                $oe = ( $rwct % 2 ) ? 'codd' : 'ceven';
                $rwct++;
                $str_html .= "<dt class='" . attr($oe) . "'>" . text($row[0]) . " <a class='rpt' href='edih_main.php?gtbl=claim&ftype=" . attr_url($tp) . "&trace=" . attr_url($row[2]) . "&rsptype=" . attr_url($row[6]) . "&err=" . attr_url($row[7]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($row[2]) . "</a></dt>" . PHP_EOL;
            }
        }

        //
        $str_html .= "</dl>" . PHP_EOL;
    } else {
        $str_html .= "Search returned no data rows from file " . text($filename) . "<br />";
        csv_edihist_log("edih_list_denied_claims: no rows returned $filetype $filename $trace");
    }

    //
    return $str_html;
}


/**
 * check the csv claims tables and return rows for a particular encounter
 *
 * @uses csv_pid_enctr_parse()
 * @uses csv_file_with_pid_enctr()
 * @uses csv_table_select_list()
 * @uses csv_search_record()
 *
 * @param string       encounter number
 *
 * @return string
 */
function edih_claim_history($encounter)
{
    //
    if ($encounter) {
        $e = (string)$encounter;
    } else {
        return 'invalid encounter value ' . text($encounter) . '<br />' . PHP_EOL;
    }

    // use function csv_table_select_list() so that only
    // existing csv tables are queried
    $tbl2 = csv_table_select_list('array');
    //
    $rtypes = array();
    if (is_array($tbl2['claims']) && count($tbl2['claims'])) {
        $rtypes = array_keys($tbl2['claims']);
    } else {
        csv_edihist_log("csv_claim_history: failed to get csv table names");
        return "failed to get csv table names";
    }

    //
    $ch_html = "<table class='clmhist' columns=4><caption>Encounter Record for " . text($e) . "</caption>";
    $ch_html .= "<tbody>" . PHP_EOL;
    //
    if (in_array('f837', $rtypes)) {
        $tp = 'f837';
        $btar = csv_file_by_enctr($e, $tp);
        //
        if (is_array($btar) && count($btar)) {
            $ch_html .= "<tr class='ch837'><td colspan=4>837 Claim " . text(count($btar)) . "</td></tr>" . PHP_EOL;
            $ch_html .= "<tr class='chhead'>" . PHP_EOL;
            $ch_html .= "<td>Name</td><td>SvcDate</td><td>CLM01</td><td>File</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
            foreach ($btar as $ch) {
                $dt = substr($ch['SvcDate'], 0, 4) . '-' . substr($ch['SvcDate'], 4, 2) . '-' . substr($ch['SvcDate'], 6, 2);
                //array('PtName', 'SvcDate', 'CLM01', 'InsLevel', 'Control', 'FileName', 'Fee', 'PtPaid', 'Provider' );
                $ch_html .= "<tr class='ch837'>" . PHP_EOL;
                //
                $ch_html .= "<td>" . text($ch['PtName']) . "</td>" . PHP_EOL;
                $ch_html .= "<td>" . text($dt) . "</td>" . PHP_EOL;
                $ch_html .= "<td><a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&pid=" . attr_url($ch['CLM01']) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['CLM01']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td title='" . attr($ch['Control']) . "'><a  class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['FileName']) . "</a></td>" . PHP_EOL;
                //
                $ch_html .= "</tr>" . PHP_EOL;
            }
        } else {
            $ch_html .= "<tr class='ch837'>" . PHP_EOL;
            $ch_html .= "<td colspan=4>837 Claim -- Nothing found for " . text($e) . "</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
        }
    }

    //
    if (in_array('f997', $rtypes)) {
        $tp = 'f997';
        $f997ar = csv_file_by_enctr($e, $tp);
        //
        if (is_array($f997ar) && count($f997ar)) {
            $ch_html .= "<tr class='ch997'><td colspan=4>Ack 997/999 " . text(count($f997ar)) . "</td></tr>" . PHP_EOL;
            $ch_html .= "<tr class='chhead'>" . PHP_EOL;
            $ch_html .= "<td colspan=3>Acknowledgement File</td><td>Notes</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
            foreach ($f997ar as $ch) {
                //
                $msg = strlen($ch[7]) ? $ch[7] : 'ST Number';
                //array('PtName', 'RspDate', 'Trace', 'Status', 'Control', 'FileName', 'RspType', 'err_seg');;
                $ch_html .= "<tr class='ch997'>";
                $ch_html .= "<td>Rsp Ack</td>" . PHP_EOL;
                $ch_html .= "<td><a class='rsp' target='_blank' href='edih_main.php?gtbl=claim&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&trace=" . attr_url($ch['Trace']) . "&rsptype=" . attr_url($ch['RspType']) . "&errseg=" . attr_url($ch['err_seg']) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['Status']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['FileName']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td title='Response type/date'>" . text($ch['RspType']) . " " . text($ch['RspDate']) . "</td>" . PHP_EOL;
                $ch_html .= "</tr>" . PHP_EOL;
            }
        } else {
            $ch_html .= "<tr class='ch997'>";
            $ch_html .= "<td colspan=4>Ack 997/999 -- Nothing found for " . text($e) . "</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
        }
    }

    //
    if (in_array('f277', $rtypes)) {
        $tp = 'f277';
        $f277ar = csv_file_by_enctr($e, $tp);
        //
        if (is_array($f277ar) && count($f277ar)) {
            $ch_html .= "<tr class='ch277'><td colspan=4>Status 277 " . text(count($f277ar)) . "</td></tr>" . PHP_EOL;
            $ch_html .= "<tr class='chhead'>" . PHP_EOL;
            $ch_html .= "<td>Response</td><td>Status</td><td>File</td><td>ClaimID</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
            foreach ($f277ar as $ch) {
                //'f277':array('PtName', 'SvcDate', 'CLM01', 'Status', 'BHT03', 'FileName', 'Payer', 'Trace');
                $ch_html .= "<tr class='ch277'>";
                //
                $ch_html .= "<td>Claim Status</td>" . PHP_EOL;
                $ch_html .= "<td><a class='rsp' href='edih_main.php?gtbl=claim&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($ch['BHT03']) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['Status']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td title='" . attr($ch['FileName']) . "'><a href='edih_main.php?gtbl=file&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['FileName']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td title='Payer " . attr($ch['Payer']) . "'>" . text($ch['Trace']) . "</td>" . PHP_EOL;
                //
                $ch_html .= "</tr>" . PHP_EOL;
            }
        } else {
            $ch_html .= "<tr class='ch277'>";
            $ch_html .= "<td colspan=4>Status 277 -- Nothing found for " . text($e) . "</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
        }
    }

    //
    if (in_array('f835', $rtypes)) {
        $tp = 'f835';
        $f835ar = csv_file_by_enctr($e, $tp);
        //
        if (is_array($f835ar) && count($f835ar)) {
            $ch_html .= "<tr class='ch835'><td colspan=4>Payment 835 " . text(count($f835ar)) . "</td></tr>" . PHP_EOL;
            $ch_html .= "<tr class='chhead'>" . PHP_EOL;
            $ch_html .= "<td>Response</td><td>Status</td><td>Trace</td><td>Payer</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
            foreach ($f835ar as $ch) {
                //
                $msg = $ch['ClaimID'] . ' ' . $ch['Pmt'] . ' ' . $ch['PtResp'];
                // array('PtName', 'SvcDate', 'CLM01', 'Status', 'Trace', 'FileName', 'ClaimID', 'Pmt', 'PtResp', 'Payer');
                $ch_html .= "<tr class='ch835'>";
                //
                $ch_html .= "<td>" . text($ch['PtName']) . "</td>" . PHP_EOL;
                $ch_html .= "<td><a class='rsp' href='edih_main.php?gtbl=claim&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&pid=" . attr_url($ch['CLM01']) . "&summary=no&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['Status']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td><a href='edih_main.php?gtbl=file&fname=" . attr_url($ch['FileName']) . "&ftype=" . attr_url($tp) . "&trace=" . attr_url($ch['Trace']) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($ch['Trace']) . "</a></td>" . PHP_EOL;
                $ch_html .= "<td title='" . attr($msg) . "'>" . text($ch['Payer']) . "</td>" . PHP_EOL;
                //
                $ch_html .= "</tr>" . PHP_EOL;
            }
        } else {
            $ch_html .= "<tr class='ch835'>";
            $ch_html .= "<td colspan=4>Payment 835 -- Nothing found for " . text($e) . "</td>" . PHP_EOL;
            $ch_html .= "</tr>" . PHP_EOL;
        }

        //
    } // end if($tp ...
    // -- this is where a query on the payments datatable could be used to show if payment
    //    has been received, even if no era file shows the payment.
    //
    $ch_html .= "</tbody>" . PHP_EOL;
    $ch_html .= "</table>" . PHP_EOL;
    //
    return $ch_html;
}


/**
 * Render one of our csv record files as an html table
 *
 * This function determines the actual csv file from the file_type and the
 * csv_type.  Tthe date field of each row is checked against the optional
 * lookback period or date parameters.
 *
 * @uses csv_file_type()
 * @uses csv_parameters()
 * @uses csv_thead_html()
 *
 * @param string      $file_type -- see function csv_file_type()
 * @param string      $csv_type -- either "file" or "claim"
 * @param string      $period -- lookback 2 weeks, 1 month, ALL, etc
 * @param string      $datestart -- from date  CCYY-MM-DD
 * @param string      $dateend -- to date  CCYY-MM-DD
 *
 * @return string
 */
function edih_csv_to_html($file_type, $csv_type, $period = '', $datestart = '', $dateend = '')
{
    //
    $csv_html = "";
    $modstr = '';
    $csv_d = array();
    // debug
    csv_edihist_log("edih_csv_to_html: period $period datestart $datestart dateend $dateend");
    //
    $is_date = false;
    $is_period = false;
    //
    if ($file_type && $csv_type) {
        $csv_ar = csv_assoc_array($file_type, $csv_type);
        if (is_array($csv_ar) && count($csv_ar)) {
            $rwct = count($csv_ar);
        } else {
            csv_edihist_log("edih_csv_to_html: error opening csv file $file_type $csv_type");
            $csv_html .= "error opening csv file " . text($file_type) . " " . text($csv_type) . "<br />";
            return false;
        }
    } else {
        $csv_html .= "invalid arguments for opening csv table<br />";
        return false;
    }

    $tp = csv_file_type($file_type);
    if ($tp) {
        $params = csv_parameters($tp);
    } else {
        csv_edihist_log("csv_to_html error: incorrect file type $file_type");
        $csv_html .= "csv_to_html error: incorrect file type " . text($file_type) . " <br />" . PHP_EOL;
        return false;
    }

    // csv tables date is in col 0 for file, col 5 for claim
    // file name is under FileName for all tables
    $dtcol = (strpos($csv_type, "aim")) ? $params['claimdate'] : $params['filedate'];
    $tp_lbl = (strpos($csv_type, "aim")) ? 'Claims' : 'Files';
    //
    // given dates shold be in CCYY-MM-DD or CCYY/MM/DD format
    // edih_view.php is supposed to use CCYY-MM-DD
    $dts = $dt4 = $dte = '';
    if (preg_match('/(19|20)\d{2}\D[01][0-9]\D[0-3][0-9]/', $datestart)) {
        $dts = implode('', preg_split("/\D/", $datestart));
        if ($dateend && preg_match('/(19|20)\d{2}\D[01][0-9]\D[0-3][0-9]/', $dateend)) {
            $dte = implode('', preg_split("/\D/", $dateend));
        } else {
            $dt4 = date_create(date('Y-m-d'));
            $dte = date_format($dt4, 'Ymd');
        }

        // php DateTime is poorly documented
        $is_date = ($dts && $dte);
        // debug
        csv_edihist_log("edih_csv_to_html: dts $dts dte $dte isdate " . strval($is_date));
        //
        //
    } elseif ($period) {
        $dtstr1 = '';
        $is_period = preg_match('/\d{1,2}(?=w|m|y)/', $period, $matches);
        if (count($matches)) {
            $pd = $matches[0];
            $gtdt = getdate();
            $mon = (string)$gtdt['mon'];
            $day = (string)($gtdt['mday']); // - $gtdt['wday'] + 1); not sure why this was valuable
            $yr = (string)$gtdt['year'];
            if (strtoupper($period) == 'ALL') {
                $is_period = false;
                $modstr = '';
            } elseif (strpos($period, 'w')) {
                // take the first character of 'period'
                $modstr = '-' . $pd . ' week';
                $dtstr1 = $mon . '/' . $day . '/' . $yr;
            } elseif (strpos($period, 'm')) {
                $modstr = '-' . $pd . ' month';
                $dtstr1 = $mon . '/01/' . $yr;
            } elseif (strpos($period, 'y')) {
                $modstr = '-' . $pd . ' year';
                $dtstr1 = $mon . '/01/' . $yr;
            } else {
                csv_edihist_log("csv_to_html error: incorrect date period $period");
                return false;
            }
        }

        //
        // debug
        csv_edihist_log("edih_csv_to_html: period $period modstr $modstr dtstr1 $dtstr1");
        //
        if ($is_period && $modstr) {
            $dtpd1 = date_create($dtstr1);
            $dtm = date_modify($dtpd1, $modstr);
            $dtpd2 = $dtm->format('Ymd');
            $dtpdlbl = $dtm->format('Y-m-d');
        } else {
            $dtpd2 = '';
            $dtpdlbl = 'All Dates';
        }
    }

    //
    if ($is_date) {
        $isok = false;
        $idx = 0;
        foreach ($csv_ar as $data) {
            $isok = (strcmp($data[$dtcol], $dts) >= 0) ? true : false;
            $isok = (strcmp($data[$dtcol], $dte) > 0) ? false : $isok;
            //
            if ($isok) {
                $csv_d[] = $data;
            }
        }
    } elseif ($is_period) {
        foreach ($csv_ar as $data) {
            if (strcmp($data[$dtcol], $dtpd2) > 0) {
                $csv_d[] = $data;
            }
        }
    } else {
        $csv_d = &$csv_ar;
    }

    //
    $ln_ct = count($csv_d);
    //
    if ($is_date) {
        //$csv_html .= "<div id='dttl'>".PHP_EOL;
        $csv_html .= "<h4>Table: " . text($tp_lbl) . " &nbsp;" . text($tp) . " &nbsp; Start: " . text($datestart) . " &nbsp; End: " . text($dateend) . " &nbsp;Rows: " . text($rwct) . " &nbsp;Shown: " . text($ln_ct) . "</h4>" . PHP_EOL;
        //$csv_html .= "</div>".PHP_EOL;
    } elseif ($is_period) {
        //div id='dttl'></div>
        $csv_html .= "<h4>Table: " . text($tp_lbl) . " &nbsp;" . text($tp) . " &nbsp;From Date: " . text($dtpdlbl) . " &nbsp;Rows: " . text($rwct) . " &nbsp;Shown: " . text($ln_ct) . "</h4>" . PHP_EOL;
    } else {
        //<div id='dttl'></div>
        $csv_html .= "<h4>Table: " . text($tp_lbl) . " &nbsp;" . text($tp) . " &nbsp;All Dates Rows: " . text($rwct) . " &nbsp; Shown: " . text($ln_ct) . "</h4>" . PHP_EOL;
    }

    //
    //$csv_html .= "<table id='csvTable' class='csvDisplay'>".PHP_EOL;  class='display'max-width: fit-content
    $csv_html .= "<table id='csvTable' style='width: fit-content; float: left'>" . PHP_EOL;
    $csv_html .= csv_thead_html($tp, $csv_type);
    $csv_html .= "<tbody>" . PHP_EOL;
    //
    // now create the body of the table
    //
    $cls = (strpos('|f837|f270|f276|f278', $tp)) ? 'sub' : 'rsp';
    //
    $idx = 0;
    if ($csv_type == 'file') {
        //
        if ($tp == 'f835') {
            //array('Date', 'FileName', 'Control', 'Trace', 'Claim_ct', 'Denied', 'Payer')
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                foreach ($val as $k => $v) {
                    if ($k == 'Date') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $fn = $v;
                        $csv_html .= "<td><a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Control') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&icn=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Trace') {
                        $trc = $v;
                        $csv_html .= "<td><a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&trace=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&rsptype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Claim_ct') {
                        $csv_html .= "<td>" . text($v) . " <a class='sub' href='edih_main.php?tracecheck=" . attr_url($trc) . "&ckprocessed=yes&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'><em>P?</em></a></td>" . PHP_EOL;
                    } elseif ($k == 'Denied') {
                        if ((int)$v > 0) {
                            $csv_html .= "<td><a class='sub' href='edih_main.php?chkdenied=yes&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&trace=" . attr_url($trc) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                        } else {
                            $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                        }
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f997') {
            //array('Date', 'FileName', 'Control', 'Trace', 'RspType', 'RejCt')
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                //
                $rsp = $val['RspType'];
                foreach ($val as $k => $v) {
                    if ($k == 'Date') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $fn = $v;
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Trace') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&trace=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&rsptype=" . attr_url($rsp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'RejCt') {
                        if ((int)$v > 0) {
                            $csv_html .= "<td><a class='rpt' href='edih_main.php?chkdenied=yes&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                        } else {
                            $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                        }
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }

            //
        } else {
            // the generic case -- for 'file' type tables
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                foreach ($val as $k => $v) {
                    if ($k == 'Date') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Control') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&icn=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Reject') {
                        if ((int)$v > 0) {
                            $csv_html .= "<td><a class='sub' href='edih_main.php?&chkdenied=yes&fname=" . attr_url($val['FileName']) . "&ftype=" . attr_url($tp) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                        } else {
                            $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                        }
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        }
    } elseif ($csv_type == 'claim') {
        //
        if ($tp == 'f837') {
            // array('PtName', 'SvcDate', 'CLM01', 'InsLevel', 'Control', 'FileName', 'Fee', 'PtPaid', 'Provider' );
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                foreach ($val as $k => $v) {
                    if ($k == 'CLM01') {
                        $csv_html .= "<td><a class='sub' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&pid=" . attr_url($v) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'SvcDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f835') {
            // array('PtName', 'SvcDate', 'CLM01', 'Status', 'Trace', 'FileName', 'ClaimID', 'Pmt', 'PtResp', 'Payer')
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $pid = $val['CLM01'];
                foreach ($val as $k => $v) {
                    if ($k == 'SvcDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'CLM01') {
                        $csv_html .= "<td>" . text($v) . " <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&pid=" . attr_url($pid) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&pid=" . attr_url($pid) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } elseif ($k == 'Trace') {
                        $csv_html .= "<td><a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&trace=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td title='" . attr($v) . "'>File <a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f277') {
            // array('PtName', 'SvcDate', 'CLM01', 'Status', 'BHT03', 'FileName', 'Payer', 'Ins_ID', 'Trace');
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $bht03 = $val['BHT03'];
                $trc = $val['CLM01'];
                foreach ($val as $k => $v) {
                    if ($k == 'SvcDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'CLM01') {
                        $csv_html .= "<td><a class='sub' href='edih_main.php?gtbl=claim&trace=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&rsptype=f837&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'BHT03') {
                        $csv_html .= "<td>" . text($v) . " <a class='rsp' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td title='" . attr($v) . "'>File <a class='rsp' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f276') {
            // array('PtName', 'ReqDate', 'CLM01', 'InsBnft', 'BHT03', 'FileName', 'Payer', 'Trace');
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $bht03 = $val['BHT03'];
                $trc = $val['CLM01'];
                foreach ($val as $k => $v) {
                    if ($k == 'ReqDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'CLM01') {
                        $csv_html .= "<td><a class='sub' href='edih_main.php?gtbl=claim&trace=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&rsptype=f837&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'BHT03') {
                        $csv_html .= "<td>" . text($v) . " <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td><a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f270') {
            // array('PtName', 'ReqDate', 'Trace', 'InsBnft', 'BHT03', 'FileName', 'Payer');
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $bht03 = $val['BHT03'];
                foreach ($val as $k => $v) {
                    if ($k == 'ReqDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'BHT03') {
                        $csv_html .= "<td> <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td><a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f271') {
            // array('PtName', 'RspDate', 'Trace', 'Status', 'BHT03', 'FileName', 'Payer');
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $bht03 = $val['BHT03'];
                foreach ($val as $k => $v) {
                    if ($k == 'RspDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'BHT03') {
                        $csv_html .= "<td>" . text($v) . " <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;" . PHP_EOL;
                        $csv_html .= "&nbsp;<a class='seg' target='_blank' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td title='" . attr($v) . "'> File <a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f278') {
            // array('PtName', 'FileDate', 'Trace', 'Status', 'BHT03', 'FileName', 'Auth', 'Payer')
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $bht03 = $val['BHT03'];
                foreach ($val as $k => $v) {
                    if ($k == 'FileDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'BHT03') {
                        $csv_html .= "<td>" . text($v) . " <a class='" . attr($cls) . "' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;" . PHP_EOL;
                        $csv_html .= "&nbsp;<a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&bht03=" . attr_url($v) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td title='" . attr($v) . "'> File <a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=htm&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>H</a>&nbsp;";
                        $csv_html .= "&nbsp;<a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>T</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>$v</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } elseif ($tp == 'f997') {
            // array('PtName', 'RspDate', 'Trace', 'Status', 'Control', 'FileName', 'RspType', 'err_seg');
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='$bgc'>" . PHP_EOL;
                // needed values for links
                $fn = $val['FileName'];
                $rsp = $val['RspType'];
                $err = $val['err_seg'];
                foreach ($val as $k => $v) {
                    if ($k == 'RspDate') {
                        $csv_html .= "<td>" . text(substr($v, 0, 4)) . '-' . text(substr($v, 4, 2)) . '-' . text(substr($v, 6, 2)) . "</td>" . PHP_EOL;
                    } elseif ($k == 'FileName') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'Trace') {
                        $csv_html .= "<td><a class='seg' href='edih_main.php?gtbl=claim&fname=" . attr_url($fn) . "&ftype=" . attr_url($tp) . "&trace=" . attr_url($v) . "&rsptype=" . attr_url($rsp) . "&err=" . attr_url($err) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } elseif ($k == 'err_seg') {
                        $csv_html .= "<td title='" . attr($v) . "'>" . text(substr($v, 0, 8)) . "...</td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        } else {
            // all types in the tables are covered in an elseif, so this is unexpected
            foreach ($csv_d as $val) {
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
                $csv_html .= "<tr class='" . attr($bgc) . "'>" . PHP_EOL;
                foreach ($val as $k => $v) {
                    if ($k == 'FileName') {
                        $csv_html .= "<td><a class='" . attr($cls) . "' href='edih_main.php?gtbl=file&fname=" . attr_url($v) . "&ftype=" . attr_url($tp) . "&fmt=seg&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . text($v) . "</a></td>" . PHP_EOL;
                    } else {
                        $csv_html .= "<td>" . text($v) . "</td>" . PHP_EOL;
                    }
                }

                $csv_html .= "</tr>" . PHP_EOL;
                $idx++;
            }
        }
    } // end body of the table
    //$csv_html .= "</tbody>".PHP_EOL."</table>".PHP_EOL."</div>".PHP_EOL;
    $csv_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
    //
    return $csv_html;
}
