<?php

/*
 * edih_csv_parse.php
 *
 * Copyright 2016 Kevin McCormick Carrollton, Texas
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

/* ========= notes
 * EDI files may contain multiple ISA envelopes.  Each ISA envelope is treated as a "file" here.
 * The same file name may have one or more ISA control numbers
 * The ISA control number is associated with each GS and ST envelope and each included transaction.
 * Each transaction will also link to the ISA control number as a means of retrieving the file.
 *
 * The CSV data column headers are the array keys for each EDI file type.
 */

/**
 * Provide a more user-friendly date in csv tables
 *
 * @param string        YYYYMMDD
 * @return string       YYYY-MM-DD
 */
function edih_parse_date($strdate)
{
    if (strlen($strdate) == 6) {
        $gtdt = getdate();
        $cy = (string)$gtdt['year'];
        $dt1 = substr($cy, 0, 2) . $strdate;
    } elseif (strpos($strdate, '-') >= 6) {
        $dt1 = substr($strdate, 0, strpos($strdate, '-') - 1);
    } elseif (strlen($strdate) == 8) {
        $dt1 = $strdate;
    } else {
        // bad argument
        csv_edihist_log('edih_parse_date: invalid argument ' . $strdate);
        return false;
    }

    //
    return substr($dt1, 0, 4) . '-' . substr($dt1, 4, 2) . '-' . substr($dt1, 6, 2);
}

/** remittance 835 files
 *
 * Each payment trace number (Control) gets a row in the csv file record.
 * Each claim payment transaction gets a row in the csv claim record
 * <pre>
 *
 * $array
 * [$icn]['type']
 * [$icn]['file'][$i] => array keys match csv_table_head() output
 * [$icn]['claim'][$i] => array keys match csv_table_head() output
 *
 * </pre>
 *
 * @uses csv_file_type()
 * @uses edih_parse_date()
 * @uses edih_x12_transaction()
 * @uses edih_get_segment()
 * @param object     edih_x12_obj
 * @return array
 */
function edih_835_csv_data($obj835)
{
    //
    $ret_ar = array();
    //
    $seg_ar = $obj835->edih_segments();
    $env_ar = $obj835->edih_envelopes();
    $de = $obj835->edih_delimiters()['e'];
    $fn = $obj835->edih_filename();
    //$tp = $obj835->edih_type();
    //
    // since 835 "files" table uses transaction trace as the "Control"
    // we must assemble the 'files' array in the ST loop
    // ['ISA'][$icn]
    foreach ($env_ar['ISA'] as $icn => $isa) {
        //
        $ret_ar[$icn]['claim'] = array();
        $ret_ar[$icn]['file'] = array();
        //
        $gsdate = '';
        $trace = '';
        //
        if (array_key_exists('GS', $env_ar)) {
            // for unlikely case of 'mixed' GS types in file
            foreach ($env_ar['GS'] as $gs) {
                if ($gs['icn'] == $icn) {
                    $ft = $gs['type'];
                    $gsdate = $gs['date'];
                    $payer_id = $gs['srcid'];  // from TRN03
                    break;
                }
            }
        }

        $ret_ar[$icn]['type'] = csv_file_type($ft);
        $gsdate = ($gsdate) ? $gsdate : edih_parse_date($isa['date']);
        //
        $fdx = count($ret_ar[$icn]['file']);
        //
        // ['ST'][$stky]=>['start']['count']['stn']['gsn']['icn']['type']['trace']['acct']
        // ['ST'][$stky]['acct'][i]=>pid-enc$$ret_ar[$icn]['claim'][$cdx]['SvcDate'] =
        foreach ($env_ar['ST'] as $st) {
            if ($st['icn'] != $icn) {
                continue;
            }

            // the "files" table has a row for each payment trace
            //
            if ($trace != $st['trace']) {
                $fdx = count($ret_ar[$icn]['file']);
                //
                $ret_ar[$icn]['file'][$fdx]['Date'] = $gsdate;
                $ret_ar[$icn]['file'][$fdx]['FileName'] = $fn;
                $ret_ar[$icn]['file'][$fdx]['Control'] = (string)$icn;
                $ret_ar[$icn]['file'][$fdx]['Trace'] = $st['trace'];
                $ret_ar[$icn]['file'][$fdx]['Claim_ct'] = (string)count($st['acct']);
                $ret_ar[$icn]['file'][$fdx]['Denied'] = 0;
                $ret_ar[$icn]['file'][$fdx]['Payer'] = $payer_id;
                //
                $trace = $st['trace'];
            }

            //
            $stsegs = array_slice($seg_ar, $st['start'], $st['count']);
            $stacct = array_values(array_unique($st['acct']));
            $clmct = count($stacct);
            $payer = '';
            $denied = 0;
            //
            $n1pr = $obj835->edih_get_segment('N1', 'N1' . $de . 'PR' . $de, $stsegs);
            $payer = '';
            if ($n1pr) {
                foreach ($n1pr as $n1) {
                    $sar = explode($de, $n1);
                    $payer = (isset($sar[2])) ? $sar[2]  : '';
                }

                if ($payer) {
                    $ret_ar[$icn]['file'][$fdx]['Payer'] = $payer;
                }
            } // end if ($n1pr)
            //
            //
            for ($i = 0; $i < $clmct; $i++) {
                $clpsegs = $obj835->edih_x12_transaction($stacct[$i], $st['stn']);
                if (!is_array($clpsegs) || !count($clpsegs)) {
                    //
                    csv_edihist_log('edih_835_csv_data: no segments for account ' . $st['acct'][$i]);
                    continue;
                }

                //
                foreach ($clpsegs as $trans) {
                    //
                    $cdx = count($ret_ar[$icn]['claim']);
                    foreach ($trans as $seg) {
                        //
                        if (strncmp($seg, 'CLP' . $de, 4) === 0) {
                            $sar = explode($de, $seg);
                            //
                            $ret_ar[$icn]['claim'][$cdx]['CLM01'] = (isset($sar[1])) ? $sar[1] : '';
                            $ret_ar[$icn]['claim'][$cdx]['Status'] = (isset($sar[2])) ? $sar[2] : '';
                            $ret_ar[$icn]['claim'][$cdx]['Pmt'] = (isset($sar[4])) ? sprintf("%01.02f", $sar[4]) : '';
                            $ret_ar[$icn]['claim'][$cdx]['PtResp'] = ( isset($sar[5]) ) ? sprintf("%01.02f", $sar[5]) : '';
                            $ret_ar[$icn]['claim'][$cdx]['ClaimID'] = ( isset($sar[7]) ) ? trim($sar[7]) : '';
                            $ret_ar[$icn]['claim'][$cdx]['FileName'] = $fn;
                            $ret_ar[$icn]['claim'][$cdx]['Trace'] = $st['trace'];
                            $ret_ar[$icn]['claim'][$cdx]['Payer'] = $payer;
                            $ret_ar[$icn]['claim'][$cdx]['PtName'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = '';
                            //
                            if ($sar[2] == '4' || $sar[2] == '22' || $sar[2] == '23') {
                                $denied++;
                            }

                            $loopid = "2100";
                            continue;
                        }

                        //
                        if (strncmp($seg, 'NM1' . $de . 'QC' . $de, 7) === 0) {
                            $sar = explode($de, $seg);
                            $midn = ( isset($sar[5]) && strlen($sar[5]) ) ? ', ' . $sar[5] : "";
                            $ret_ar[$icn]['claim'][$cdx]['PtName'] = $sar[3] . ', ' . $sar[4] . $midn;
                            continue;
                        }

                        if (strncmp($seg, 'DTM' . $de . '232' . $de, 8) === 0) {
                            $sar = explode($de, $seg);
                            $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = $sar[2];
                            continue;
                        }

                        if (strncmp($seg, 'DTM' . $de . '472' . $de, 8) === 0) {
                            $sar = explode($de, $seg);
                            $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = $sar[2];
                            continue;
                        }
                    } // end foreach($trans as $seg)
                } // end foreach($clpsegs as $trans) {
            } // end for($i=0; $i<$clmct; $i++)
            // get denied count
            $ret_ar[$icn]['file'][$fdx]['Denied'] = ($denied) ? $denied : '0';
            //
        } // end  foreach($env_ar['ST'] as $st)
    } // end  foreach($env_ar['ISA'] as $icn => $isa)
    //
    return $ret_ar;
}


/*
 * Parse csv data from 837 files
 * Note that this can return false provider values for extensive claims
 * since loops are not tracked and 837 claims can have numerous
 * providers, but probably not an issue for OpenEMR
 *
 * @param object  edih_x12_ file object
 * @return array  data to write csv file and csv clain table rows
 */
function edih_837_csv_data($obj837)
{
    //
    $ret_ar = array();
    //
    $seg_ar = $obj837->edih_segments();
    $env_ar = $obj837->edih_envelopes();
    $de = $obj837->edih_delimiters()['e'];
    $fn = $obj837->edih_filename();
    //$ft = csv_file_type( $obj837->edih_type() );
    //
    if (!isset($env_ar['ST'])) {
        csv_edihist_log('edih_837_csv_data: envelope error');
        return $ret_ar;
    }

    //['file'] = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12_partner');
    //['claim'] =
    //       array('PtName', 'SvcDate', 'CLM01', 'InsLevel', 'Control', 'File_837', 'Fee', 'PtPaid', 'Provider' );

    foreach ($env_ar['ISA'] as $icn => $isa) {
        //
        $ret_ar[$icn]['claim'] = array();
        $ret_ar[$icn]['file'] = array();
        //$ret_ar[$icn]['type'] = $ft;
        foreach ($env_ar['GS'] as $gs) {
            if ($gs['icn'] == $icn) {
                $ret_ar[$icn]['type'] = csv_file_type($gs['type']);
                $gsdate = $gs['date'];
                break;
            }
        }

        //
        $fdx = count($ret_ar[$icn]['file']);
        //
        $ret_ar[$icn]['file'][$fdx]['Date'] = (string)$gsdate;
        $ret_ar[$icn]['file'][$fdx]['FileName'] = $fn;
        // if GS06 were unique, it could be used as the 'Control'
        $ret_ar[$icn]['file'][$fdx]['Control'] = (string)$icn;
        $ret_ar[$icn]['file'][$fdx]['x12Partner'] = (string)$isa['receiver'];
        //
        $clm_ct = 0;
        //
        foreach ($env_ar['ST'] as $st) {
            // claims should be in the correct ISA envelope
            if ($st['icn'] != $icn) {
                continue;
            }

            //
            $stsegs = array_slice($seg_ar, $st['start'], $st['count']);
            $stacct = array_values(array_unique($st['acct']));
            $clmct = count($stacct);
            // $st['icn'] is the ISA control number for the ISA envelope containing the ST--SE
            $date = $env_ar['ISA'][$st['icn']]['date'];
            $stn = $st['stn'];
            for ($i = 0; $i < $clmct; $i++) {
                //echo '=== ACCT '.$stacct[$i].' '.$st['stn'].'<br />'.PHP_EOL;
                $asegs = $obj837->edih_x12_transaction($stacct[$i], $st['stn']);
                if (!is_array($asegs) || !count($asegs)) {
                    csv_edihist_log('edih_837_csv_data: no segments for account ' . $st['acct'][$i]);
                    continue;
                }

                foreach ($asegs as $trans) {
                    //
                    //array('PtName'=>0, 'SvcDate'=>1, 'CLM01'=>2, 'InsLevel'=>3, 'Control'=>4, 'FileName'=>5, 'Fee'=>6, 'PtPaid'=>7, 'Provider'=>8 );

                    //'f837': $hdr = array('PtName', 'SvcDate', 'CLM01', 'InsLevel', 'Control', 'FileName', 'Fee', 'PtPaid', 'Provider' )
                    foreach ($trans as $seg) {
                        if (strncmp($seg, 'BHT' . $de, 4) === 0) {
                            $cdx = count($ret_ar[$icn]['claim']);
                            $sar = explode($de, $seg);
                            $bht03 = $sar[3];
                            $hl = '';
                            $clm_ct++;
                            //
                            $ret_ar[$icn]['claim'][$cdx]['PtName'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['CLM01'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['InsLevel'] = '';
                            //
                            $ret_ar[$icn]['claim'][$cdx]['BHT03'] = (strlen($bht03) == 13) ? $bht03 : sprintf("%s%04d", $st['icn'], $st['stn']);
                            $ret_ar[$icn]['claim'][$cdx]['FileName'] = $fn;
                            //
                            $ret_ar[$icn]['claim'][$cdx]['Fee'] = '0';
                            $ret_ar[$icn]['claim'][$cdx]['PtPaid'] = '0';
                            $ret_ar[$icn]['claim'][$cdx]['Provider'] = '';
                        }

                        if (strncmp($seg, 'HL' . $de, 3) === 0) {
                            $sar = explode($de, $seg);
                            $hl = $sar[3];
                            continue;
                        }

                        if (intval($hl) == 20) {
                            if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                if ($sar[2] == '82' || $sar[2] == '85') {
                                    $ret_ar[$icn]['claim'][$cdx]['Provider'] = $sar[9];
                                }

                                continue;
                            }
                        } // end if $hl == '20'

                        if (intval($hl) >= 22) {
                            if (strncmp($seg, 'SBR' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['InsLevel'] = $sar[1];
                            }

                            if (strncmp($seg, 'CLM' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['CLM01'] = $sar[1];
                                $ret_ar[$icn]['claim'][$cdx]['Fee'] = $sar[2];
                                continue;
                            }

                            if (strncmp($seg, 'AMT' . $de . 'F5' . $de, 7) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['PtPaid'] = $sar[2];
                                continue;
                            }

                            if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                if (strpos('|IL|QC', $sar[1])) {
                                    $midn = ( isset($sar[5]) && strlen($sar[5]) ) ? ', ' . $sar[5] : '';
                                    $ret_ar[$icn]['claim'][$cdx]['PtName'] = $sar[3] . ', ' . $sar[4] . $midn;
                                    continue;
                                }

                                if ($sar[1] == '82') {
                                    $ret_ar[$icn]['claim'][$cdx]['Provider'] = $sar[9];
                                    continue;
                                }
                            }

                            if (strncmp($seg, 'DTP' . $de . '472' . $de, 8) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = $sar[3];
                                continue;
                            }
                        } // end if $hl >= '22'
                    } // end foreach($trans as $seg)
                } // end foreach($asegs as $trans)
            } // end for($i=0; $i<$clmct; $i++)
        } // end foreach($obj837->envelopes['ST'] as $st)
        // claim count
        $ret_ar[$icn]['file'][$fdx]['Claim_ct'] = (string)$clm_ct;
    } // end  foreach($env_ar['ISA'] as $icn => $isa)
    //
    return $ret_ar;
}

/*
 * extract csv table row data for x12 277 files
 *
 * @param object  edih_x12_file object of type 276/277
 * @return array
 */
function edih_277_csv_data($obj277)
{
    //
    $ret_ar = array();
    //
    $seg_ar = $obj277->edih_segments();
    $env_ar = $obj277->edih_envelopes();
    $de = $obj277->edih_delimiters()['e'];
    $fn = $obj277->edih_filename();
    $tp = $obj277->edih_type();
    // 'HN' 277 'HR' 276 )
    $env_ar = $obj277->edih_envelopes();
    //
    if (!isset($env_ar['ST'])) {
        csv_edihist_log('edih_277_csv_data: envelope error');
        return $ret_ar;
    }

    if (!isset($env_ar['GS'])) {
        csv_edihist_log('edih_277_csv_data: envelope error');
        return $ret_ar;
    }

    //
    foreach ($env_ar['ISA'] as $icn => $isa) {
        //
        $ret_ar[$icn]['claim'] = array();
        $ret_ar[$icn]['file'] = array();
        //
        $rspdate = $isa['date'];
        foreach ($env_ar['GS'] as $gs) {
            if ($gs['icn'] == $icn) {
                $ret_ar[$icn]['type'] = csv_file_type($gs['type']);
                $rspdate = $gs['date'];
                break;
            }
        }

        //$ret_ar[$icn]['type'] = csv_file_type($ft);
        //
        $fdx = count($ret_ar[$icn]['file']);
        //['f277']['file'] = array('Date', 'FileName', 'Control', 'Accept', 'AccAmt', 'Reject', 'RejAmt');
        //'f276': $hdr = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12Partner');
        $ret_ar[$icn]['file'][$fdx]['Date'] = $rspdate;
        $ret_ar[$icn]['file'][$fdx]['FileName'] = $fn;
        // could be GS06 for 276 if it were unique
        $ret_ar[$icn]['file'][$fdx]['Control'] = (string)$icn;
        if ($tp == 'HN') {
            $ret_ar[$icn]['file'][$fdx]['Accept'] = 0;
            $ret_ar[$icn]['file'][$fdx]['Reject'] = 0;
            $ret_ar[$icn]['file'][$fdx]['AccAmt'] = 0;
            $ret_ar[$icn]['file'][$fdx]['RejAmt'] = 0;
        } else {
            $ret_ar[$icn]['file'][$fdx]['Claim_ct'] = 0;
            $ret_ar[$icn]['file'][$fdx]['x12Partner'] = $isa['receiver'];
        }

        //
        $clmct = 0;
        //
        //['ST'][$stky]=>['start']['count']['stn']['gsn']['icn']['type']['trace']['acct']
        //  ['ST'][$stky]['acct'][i]=>pid-enc
        //'f277': $hdr = array('PtName', 'SvcDate', 'CLM01', 'Status', 'BHT03', 'FileName', 'Payer', 'Trace');
        foreach ($env_ar['ST'] as $st) {
            //
            if ($st['icn'] != $icn) {
                continue;
            }

            //
            $stsegs = array_slice($seg_ar, $st['start'], $st['count']);
            $stacct = array_values(array_unique($st['bht03']));
            $clmct += count($stacct);
            $st_icn = $st['icn'];  // same value as $obj277->envelopes['ISA']['icn']
            $stn =  $st['stn'];
            //
            if ($tp == 'HR') {
                $ret_ar[$icn]['file'][$fdx]['Claim_ct'] = $clmct;
            }

            //
            //for($i=0; $i<$clmct; $i++) {
            for ($i = 0; $i < count($stacct); $i++) {
                //
                $asegs = $obj277->edih_x12_transaction($stacct[$i]);
                //
                foreach ($asegs as $trans) {
                    //
                    foreach ($trans as $seg) {
                        //
                        if (strncmp($seg, 'BHT' . $de, 4) === 0) {
                            $cdx = count($ret_ar[$icn]['claim']);
                            //
                            $sar = explode($de, $seg);
                            if ($sar[2] != '08') {
                                csv_edihist_log('Claim Status BHT purpose code: ' . $sar[2] . ' in ST ' . $st['stn'] . ' in file ' . $fn);
                            }

                            $trns_tp = (isset($sar[6]) && $sar[6]) ? $sar[6] : '';
                            if ($trns_tp == 'TH') {
                                $bht_id = 'ack'; // 277CA
                            } elseif ($trns_tp == 'DG') {
                                $bht_id = 'rsp'; // 277
                            } else {
                                $bht_id = 'sub'; // 276
                            }

                            //
                            $ret_ar[$icn]['claim'][$cdx]['PtName'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['CLM01'] = '';
                            //
                            if ($tp == 'HN') {
                                // 277
                                $ret_ar[$icn]['claim'][$cdx]['Status'] = '';
                                $ret_ar[$icn]['claim'][$cdx]['BHT03'] = $sar[3];
                            } elseif ($tp == 'HR') {
                                //276
                                $ret_ar[$icn]['claim'][$cdx]['ClaimID'] = '';
                                $ret_ar[$icn]['claim'][$cdx]['BHT03'] = sprintf("%s%04d", $st_icn, $stn);
                            }

                            //

                            $ret_ar[$icn]['claim'][$cdx]['FileName'] = $fn;
                            //
                            $ret_ar[$icn]['claim'][$cdx]['Payer'] = '';
                            //$ret_ar[$icn]['claim'][$cdx]['Ins_ID'] = '';
                            $ret_ar[$icn]['claim'][$cdx]['Trace'] = '';
                            continue;
                        }

                        //
                        if (strncmp($seg, 'HL' . $de, 3) === 0) {
                            $sar = explode($de, $seg);
                            $hl = (string)$sar[3];
                        }

                        //
                        if ($hl == '20') {
                            // information source
                            if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['Payer'] = ($sar[1] == 'PR' || $sar[1] == 'AY') ? $sar[3] : '';
                                continue;
                            }
                        }

                        //
                        if ($hl == '21') {
                            // information source or receiver level rejection
                            // -- user should view the transaction
                            // -- don't include in csv data so Trace only refers to 276
                            //if ( strncmp($seg, 'TRN'.$de, 4) === 0) {
                                //$sar = explode($de, $seg);
                                //$ret_ar[$icn]['claim'][$cdx]['Trace'] = $sar[2];
                            //}
                            if (strncmp($seg, 'STC' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['Status'] = $sar[1];
                            }

                            if (strncmp($seg, 'QTY' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                if ($sar[1] == '90') {
                                    $ret_ar[$icn]['file'][$fdx]['Accept'] += $sar[2];
                                }

                                if ($sar[1] == 'AA') {
                                    $ret_ar[$icn]['file'][$fdx]['Reject'] += $sar[2];
                                }
                            }

                            if (strncmp($seg, 'AMT' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                if ($sar[1] == 'YU') {
                                    $ret_ar[$icn]['file'][$fdx]['AccAmt'] += $sar[2];
                                }

                                if ($sar[1] == 'YY') {
                                    $ret_ar[$icn]['file'][$fdx]['RejAmt'] += $sar[2];
                                }
                            }

                            continue;
                        }

                        //
                        if ($hl == '19') {
                            // provider level rejection
                            // -- user should view the transaction
                            // -- don't include in csv data so Trace only refers to 276
                            //if ( strncmp($seg, 'TRN'.$de, 4) === 0) {
                                //$sar = explode($de, $seg);
                                //$ret_ar[$icn]['claim'][$cdx]['Trace'] = $sar[2];
                            //}
                            if (strncmp($seg, 'STC' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['Status'] = $sar[1];
                            }

                            continue;
                        }

                        //
                        if ($hl == '22' || $hl == '23') {
                            // subscriber or dependent
                            if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                if ($sar[1] == 'IL' || $sar[1] == 'QC') {
                                    $midn = ( isset($sar[5]) && strlen($sar[5]) ) ? ', ' . $sar[5] : "";
                                    $ret_ar[$icn]['claim'][$cdx]['PtName'] = $sar[3] . ', ' . $sar[4] . $midn;
                                    //if (isset($sar[8]) && $sar[8] == 'MI') {
                                        //$ret_ar[$icn]['claim'][$cdx]['Ins_ID'] = (isset($sar[9])) ? $sar[9] : '';
                                    //}
                                }

                                continue;
                            }

                            // in response to 276, this is the reference given in TRN02
                            if (strncmp($seg, 'TRN' . $de . '2' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['Trace'] = $sar[2];
                                continue;
                            }

                            // REF*EJ* will give the payer assigned claim number
                            //  not used much in 277CA files
                            if (strncmp($seg, 'REF' . $de . 'EJ' . $de, 7) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['CLM01'] = $sar[2];
                                continue;
                            }

                            if (strncmp($seg, 'REF' . $de . '1K' . $de, 7) === 0) {
                                // hopefully OpenEMR will include the claim number in 276
                                if ($tp == 'HR') {
                                    $sar = explode($de, $seg);
                                    $ret_ar[$icn]['claim'][$cdx]['ClaimID'] = $sar[2];
                                }

                                continue;
                            }

                            // REF*D9*(Claim number)~

                            if (strncmp($seg, 'STC' . $de, 4) === 0) {
                                // STC is only present in 277
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['Status'] = $sar[1];
                                continue;
                            }

                            continue;
                        }

                        if ($hl == 'PT') {
                            //  277CA
                            if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                if ($sar[1] == 'IL' || $sar[1] == 'QC') {
                                    $midn = ( isset($sar[5]) && strlen($sar[5]) ) ? ', ' . $sar[5] : "";
                                    $ret_ar[$icn]['claim'][$cdx]['PtName'] = $sar[3] . ', ' . $sar[4] . $midn;
                                    //$ret_ar[$icn]['claim'][$cdx]['Ins_ID'] = (isset($sar[9])) ? $sar[9] : '';
                                }

                                continue;
                            }

                            if (strncmp($seg, 'TRN' . $de . '2' . $de, 6) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['CLM01'] = $sar[2];
                                continue;
                            }

                            if (strncmp($seg, 'STC' . $de, 4) === 0) {
                                $sar = explode($de, $seg);
                                $ret_ar[$icn]['claim'][$cdx]['Status'] = $sar[1];
                                continue;
                            }

                            //if ( strncmp($seg, 'REF'.$de.'1K'.$de, 7) === 0) {
                                //$sar = explode($de, $seg);
                                //$ret_ar[$icn]['claim'][$cdx]['ClaimID'] = $sar[2];
                                //continue;
                            //}
                            if (strncmp($seg, 'DTP' . $de . '472' . $de, 8) === 0) {
                                $sar = explode($de, $seg);
                                // D8-CCYYMMDD  RD8-CCYYMMDD-CCYYMMDD  only take initial date
                                $ret_ar[$icn]['claim'][$cdx]['SvcDate'] = substr($sar[3], 0, 8);
                                continue;
                            }
                        }
                    } // end foreach($trans as $seg)
                } // end foreach($asegs as $trans)
            } // end for($i=0; $i<$clmct; $i++)
        }// end foreach($obj277->envelopes['ST'] as $st)
    } // end foreach($env_ar['ISA'] as $icn => $isa)
    //
    return $ret_ar;
}

/**
 * parse an x12 278 file into data rows for csv tables
 *
 * @param object   x12_file_object
 * @return array
 */
function edih_278_csv_data($obj278)
{
    //
    // f278 file = array('Date', 'FileName', 'Control', 'TrnCount', 'Auth', 'Payer');
    // f278 claim = array('PtName', 'FileDate', 'Trace', 'Status' 'BHT03', 'FileName', 'Auth', 'Payer');
    //
    $ret_ar = array();
    //
    $de = $obj278->edih_delimiters()['e'];
    $ds = $obj278->edih_delimiters()['s'];
    $fn = $obj278->edih_filename();
    $seg_ar = $obj278->edih_segments();
    $env_ar = $obj278->edih_envelopes();
    $ft = csv_file_type($obj278->edih_type());
    //
    // $ft: 'HI'=>'278'
    if (!isset($env_ar['ST'])) {
        csv_edihist_log('edih_278_csv_data: envelope error ' . $fn);
        return false;
    }

    //
    if (!isset($env_ar['GS'])) {
        csv_edihist_log('edih_278_csv_data: envelope error ' . $fn);
        return $ret_ar;
    }

    //
    foreach ($env_ar['ISA'] as $icn => $isa) {
        // array('Date', 'FileName', 'Control', 'ta1ctrl', 'RejCt');
        $ret_ar[$icn]['type'] = $ft;
        $ret_ar[$icn]['claim'] = array();
        $ret_ar[$icn]['file'] = array();
        $rspdate = $isa['date'];
        //
        foreach ($env_ar['GS'] as $gs) {
            if ($gs['icn'] == $icn) {
                $rspdate = $gs['date'];
                break;
            }
        }

        foreach ($env_ar['ST'] as $st) {
            //
            if ($st['icn'] != $icn) {
                continue;
            }

            //
            $stsegs = array_slice($seg_ar, $st['start'], $st['count']);
            $loopid = '0';
            $hl = 0;
            //$isasender = $env_ar['ISA'][$st['icn']]['sender'];
            //$isadate = $env_ar['ISA'][$st['icn']]['date'];
            $isaicn = $st['icn'];
            $has_le = false;
            //
            // for "claim" array
            //
            foreach ($stsegs as $seg) {
                //
                if (strncmp($seg, 'BHT' . $de, 4) === 0) {
                    // new transaction
                    // bht01 0007  --> Src, Rcv, Sbr, Dep, Event, Services
                    $rqst = '';
                    $status = 'A';
                    $cdx = count($ret_ar[$icn]['claim']);
                    //
                    $sar = explode($de, $seg);
                    if (isset($sar[2])) {
                        if ($sar[2] == '01') {
                            $rqst = 'Cancel';
                        }

                        if ($sar[2] == '13') {
                            $rqst = 'Req';
                        }

                        if ($sar[2] == '11') {
                            $rqst = 'Rsp';
                        }

                        if ($sar[2] == '36') {
                            $rqst = 'Reply';
                        }
                    }

                    $loopid = 0;
                    //
                    $ret_ar[$icn]['claim'][$cdx]['PtName'] = '';
                    $ret_ar[$icn]['claim'][$cdx]['FileDate'] = $rspdate;
                    $ret_ar[$icn]['claim'][$cdx]['Trace'] = '';
                    $ret_ar[$icn]['claim'][$cdx]['Status'] = '';
                    $ret_ar[$icn]['claim'][$cdx]['BHT03'] = $sar[3];  //bht03 = $sar[3];
                    $ret_ar[$icn]['claim'][$cdx]['FileName'] = $fn;
                    $ret_ar[$icn]['claim'][$cdx]['Auth'] = $rqst;
                    $ret_ar[$icn]['claim'][$cdx]['Payer'] = '';
                    //
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
                    } elseif ($sar[3] == '21') {
                        $loopid = '2000B';                      // info receiver (clinic)
                    } elseif ($sar[3] == '22') {
                        $loopid = '2000C';                      // subscriber
                    } elseif ($sar[3] == '23') {
                        $loopid = '2000D';                      // dependent
                    } elseif ($sar[3] == 'EV') {
                        $loopid = '2000E';                      // patient event
                    } elseif ($sar[3] == 'SS') {
                        $loopid = '2000F';                      // service
                    } else {
                        //debug
                        csv_edihist_log('HL has no level ' . $seg . ' in ' . $fn);
                    }

                    continue;
                }

                //
                if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    $nm101 = $sar[1];
                    $nm103 = $sar[3];
                    $nm103 = ($sar[4]) ? $nm103 . ', ' . $sar[4] : $nm103;
                    $nm103 = ($sar[5]) ? $nm103 . ' ' . $sar[5] : $nm103;
                    if ($loopid == '2000A') {
                        $ret_ar[$icn]['claim'][$cdx]['Payer'] = $nm103;  //
                        $payer_name = $nm103;
                    } elseif ($loopid == '2000C') {
                        $ret_ar[$icn]['claim'][$cdx]['PtName'] = $nm103;  //$ptname = $nm1;
                        $loopid = '2010C';
                    } elseif ($loopid == '2000D') {
                        $ret_ar[$icn]['claim'][$cdx]['PtName'] = $nm103;  //$ptname = $nm1;
                        $loopid = '2010D';
                    } elseif (strpos('|2000E', $loopid)) {
                        $loopid = '2000E';
                        $loopid = (strpos('|71|72|73|77|AAJ|DD|DK|DN|FA|G3|P3|QB|QV|SJ', $nm101) ) ? '2010EA' : $loopid;
                        $loopid = (strpos('|45|FS|ND|PW|R3', $nm101) ) ? '2010EB' : $loopid;
                        $loopid = ($nm101 == 'L5') ? '2010EC' : $loopid;
                    } elseif ($loopid == '2000F') {
                        $loopid = '2000F';
                        $loopid = (strpos('|71|72|73|77|AAJ|DD|DK|DN|FA|G3|P3|QB|QV|SJ', $nm101) ) ? '2010FA' : $loopid;
                    }

                    continue;
                }

                // for 278 eligibility response (invalid data in 278 request)
                if (strncmp($seg, 'AAA' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    $status = 'R';
                    $rej_ct++;
                    $aaa_code = $sar[3];
                    $rsp_code = $sar[4];
                    if ($loopid == '2000A') {
                        $status .= ' Src ' . $aaa_code;
                    } elseif ($loopid == '2000B') {
                        $status .= ' Rcv ' . $aaa_code;
                    } elseif ($loopid == '2000C') {
                        $status .= ' Sbr ' . $aaa_code;
                    } elseif ($loopid == '2000D') {
                        $status .= ' Dep ' . $aaa_code;
                    } elseif ($loopid == '2000E') {
                        $status .= ' Pt ' . $aaa_code;
                    } elseif ($loopid == '2000F') {
                        $status .= ' Svc ' . $aaa_code;
                    } elseif ($loopid == '2010FA') {
                        $status .= ' Prv ' . $aaa_code;
                    } elseif ($loopid == '2010EA') {
                        $status .= ' Svc ' . $aaa_code;
                    } elseif ($loopid == '2010EC') {
                        $status .= ' Tpt ' . $aaa_code;
                    }

                    //
                    $ret_ar[$icn]['claim'][$cdx]['Status'] = $status;
                    //
                    continue;
                }

                //
                // for 278 tracking
                // this assumes OpenEMR will include a TRN segment in requests
                if (strncmp($seg, 'TRN' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    if ($rqst == 'Req') {
                        if (isset($sar[1]) && $sar[1] == '1') {
                            $ret_ar[$icn]['claim'][$cdx]['Trace'] = (isset($sar[2])) ? $sar[2] : '';
                        }
                    } else {
                        if (isset($sar[1]) && $sar[1] == '2') {
                            $ret_ar[$icn]['claim'][$cdx]['Trace'] = (isset($sar[2])) ? $sar[2] : '';
                        }
                    }
                }
            } // end foreach($stsegs as $seg)
            //
        } // endforeach($env_ar['ST'] as $st
        $fdx = count($ret_ar[$icn]['file']);
        //
        $ret_ar[$icn]['file'][$fdx]['Date'] = $rspdate;
        $ret_ar[$icn]['file'][$fdx]['FileName'] = $fn;
        // could be GS06 for 278 request type
        $ret_ar[$icn]['file'][$fdx]['Control'] = $icn;
        $ret_ar[$icn]['file'][$fdx]['TrnCount'] = $cdx;
        $ret_ar[$icn]['file'][$fdx]['Auth'] = $rqst;
        $ret_ar[$icn]['file'][$fdx]['Payer'] = $payer_name;
        //
    } // endforeach($env_ar['ISA'] as $icn=>$isa)
    //
    return $ret_ar;
}


/**
 * Obtain claim information from batch 837|270|276|278 file to match 997/999 response
 * Note, we assume each batch ST envelope contains a single transaction.
 * Manual inspection is required if more than one transaction in an ST block,
 *
 * The 997/999 parse script creates a BHT03 reference by concatenating the
 * response ISA13 (in TA1 segment) and the ST02 from the AK2 segment
 *  return array ['pt_name'] ['clm01'] ['svcdate'] ['batch_name'] ['stn']
 *
 * @uses csv_file_type()
 * @uses csv_search_record()
 *
 * @param string    concatenate ISA13 and ST02 for source file
 * @param string    type of source file
 * @return array
 */
function edih_rsp_st_match($rsp_trace, $file_type)
{
    //
    $info_ar = array();
    //
    if (strlen($rsp_trace) == 13) {
        $bticn = substr($rsp_trace, 0, 9);
        $stn = substr($rsp_trace, -4);
        $btsrch = $rsp_trace;
    } else {
        // debug
        csv_edihist_log('edih_rsp_st_match() invalid trace argument ' . $rsp_trace);
        return $info_ar;
    }

    //
    $ft = csv_file_type($file_type);
    //
    if (strpos('|f837|f276|f270|f278', $ft) === false) {
        // debug
        csv_edihist_log('edih_rsp_st_match: file type ' . $ft . ' not in |f837|f276|f270|278');
        return $info_ar;
    }

    //
    $batch_srch = csv_search_record($ft, 'claim', array('s_val' => $rsp_trace, 's_col' => 4, 'r_cols' => 'All'), '1');
    if (is_array($batch_srch) && count($batch_srch[0])) {
        $info_ar['pt_name'] = $batch_srch[0][0]; // $batch_srch['PtName'];
        $info_ar['clm01'] = ($rtp == 'f837') ? $batch_srch[0][2] : $batch_srch[0][4]; // $batch_srch['CLM01'] : $batch_srch['BHT03'];
        $info_ar['svcdate'] = $batch_srch[0][1]; // ($rtp == 'f270') ? $batch_srch['ReqDate'] : $batch_srch['SvcDate'];
        $info_ar['batch_name'] = $batch_srch[0][5]; // $batch_srch['FileName'];
    }

    //
    return $info_ar;
}


/** Extract csv file data rows from 997/999 files
 *
 * <pre> Creates array
 *   ['claim'][$i] =  array('PtName', 'SvcDate', 'CLM01', 'Status', 'ak_num', 'File_997', 'Control', 'err_seg');
 *   ['file'][$i] = array('Date', 'FileName', 'Control', 'TA1ctrl', 'RejCt');
 * </pre>
 *
 * @uses edih_x12_file()  edi HC file class
 * @uses edih_997_837_st_match()
 * @param object  edih_x12_file object of type 999/997
 * @return array
 */
function edih_997_csv_data($obj997)
{
    //
    $ret_ar = array();
    //
    $de = $obj997->edih_delimiters()['e'];
    $ds = $obj997->edih_delimiters()['s'];
    $fn = $obj997->edih_filename();
    $seg_ar = $obj997->edih_segments();
    $env_ar = $obj997->edih_envelopes();
    //
    if (!isset($env_ar['ST'])) {
        csv_edihist_log('edih_997_csv_data: envelope error');
        return $ret_ar;
    }

    if (!isset($env_ar['GS'])) {
        csv_edihist_log('edih_997_csv_data: envelope error');
        return $ret_ar;
    }

    foreach ($env_ar['ISA'] as $icn => $isa) {
        //
        $ret_ar[$icn]['claim'] = array();
        $ret_ar[$icn]['file'] = array();

        foreach ($env_ar['GS'] as $gs) {
            if ($gs['icn'] == $icn) {
                $ret_ar[$icn]['type'] = csv_file_type($gs['type']);
                $rspdate = $gs['date'];
                break;
            }
        }

        //
        $fdx = count($ret_ar[$icn]['file']);
        //
        $ret_ar[$icn]['file'][$fdx]['Date'] = ($rspdate) ? $rspdate : $isa['date'];
        $ret_ar[$icn]['file'][$fdx]['FileName'] = $fn;
        $ret_ar[$icn]['file'][$fdx]['Control'] = $icn;
        //
        $rej_ct = 0;
        // CTX segment identifiers
        $trans_id = array('837' => 'CLM01', '270' => 'TRN02', '276' => 'TRN02');
        //
        //['f997']['claim'] =  array('PtName', 'RspDate', 'CLM01', 'Status', 'ak_num', 'File_997', 'Control', 'err_seg');
        foreach ($env_ar['ST'] as $st) {
            //
            if ($st['icn'] != $icn) {
                continue;
            }

            //
            $stsegs = array_slice($seg_ar, $st['start'], $st['count']);
            $loopid = '0';
            //$isasender = $env_ar['ISA'][$st['icn']]['sender'];
            $isadate = $env_ar['ISA'][$st['icn']]['date'];
            $isaicn = $st['icn'];
            // match 997/999 response to sent file ISA control
            $ret_ar[$icn]['file'][$fdx]['Trace'] = ($st['trace']) ? $st['trace'] : '';
            $bticn = ($st['trace']) ? $st['trace'] : '';
            if (!$st['trace']) {
                //
                csv_edihist_log('edih_997_csv_data: no trace to submitted file! ' . $fn);
            }

            //RspType
            foreach ($stsegs as $seg) {
                //
                if (strncmp($seg, 'AK1' . $de, 4) === 0) {
                    $sar = explode($de, $seg);

                    $loopid = '2000';
                    $rsptype = csv_file_type($sar[1]);
                    // AK102 could be the 'trace' value if it were unique
                    $rspgsn = $sar[2];
                    //
                    continue;
                }

                if (strncmp($seg, 'AK2' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    $rspsttype = csv_file_type($sar[1]);
                    $rspstn = (string)$sar[2];
                    $bht03syn = sprintf("%s%04d", $isaicn, $rspstn);
                    $iserr = false;
                    $err_seg = '';
                    $have_pt = false;
                    $ptname = '';
                    $svcdate = '';
                    continue;
                }

                if (strncmp($seg, 'IK3' . $de, 4) === 0 || strncmp($seg, 'AK3' . $de, 4) === 0) {
                    // >> try err_seg = str_replace($de, '*', $seg)
                    $sar = explode($de, $seg);
                    $iserr = true;
                    $loopid = '2100';
                    $ctx_ct = 0;
                    //
                    $err_seg .= ($err_seg) ? '' : $bht03syn . '*IK3*';    // ISA13+ST02 * invalid segment ID
                    $err_seg .= (isset($sar[1])) ? '*' . $sar[1] : '*';
                    $err_seg .= (isset($sar[2])) ? '*' . $sar[2] : '*';   // segment position
                    //$err_seg .= (isset($sar[3])) ? '*'.$sar[3] : '*';   // loop, first 4 characters
                    //$err_seg .= (isset($sar[4])) ? '*'.$sar[4] : '*';   // error code
                    //$err_seg .= '*'.;
                    // example IK3*NM1*16*2010*8~  |IK3*SegID*SegNum*Loop*ErrCode*$bht03syn
                    // locate segment (#16 in ST-SE envelope)
                    // ['err_seg'] = '|IK3*segID*segpos*loop*errcode*bht03syn|CTX-IK3*transID*segID*segpos*elempos
                    //                |IK4*elempos*errcode*elem*CTX-IK4*segID*segpos*elempos'
                    //
                    // ///// retrieve original submitted claim
                    if (!$have_pt) {
                        //
                        $pt_info = edih_rsp_st_match($bht03syn, $rspsttype);
                        //return array ['pt_name']['svcdate']['clm01']['batch_name'];
                        if ($pt_info) {
                            $ptname = (isset($pt_info['pt_name']) && strlen($pt_info['pt_name'])) ? $pt_info['pt_name'] : 'Unknown';
                            $have_pt = true;
                        } else {
                            $ptname = 'Unknown';
                        }
                    }

                    // /////////////////////////
                    continue;
                }

                //
                //if ( strncmp($seg, 'IK4'.$de, 4) === 0 || strncmp($seg, 'AK4'.$de, 4) === 0 ) {
                    //// data element error
                    //$sar = explode($de, $seg);
                    //$loopid == '2110';
                    //$iserr = true;

                    //$ctx_ct = 0;
                    //$err_seg .= ($err_seg) ? '|IK4' : '';
                    //$err_seg .= (isset($sar[1])) ? '*'.$sar[1] : '*';
                    //$err_seg .= (isset($sar[2])) ? '*'.$sar[2] : '*';
                    ////$err_seg .= (isset($sar[3])) ? '*'.$sar[3] : '*';
                    ////$err_seg .= (isset($sar[4])) ? '*'.$sar[4] : '*';
                    //// |IK4|elempos|errcode|elem
                    //continue;
                //}
                //
                //if ( strncmp($seg, 'CTX'.$de, 4) === 0 ) {
                    //$sar = explode($de, $seg);
                    //$ctx_ct++;
                    //if ($loopid == '2100') {
                        //$err_seg .= '|CTX-IK3';
                    //}
                    //if ($loopid == '2110') {
                        //$err_seg .= '|CTX-IK4';
                    //}
                    //// CTX segment identifiers $trans_id['837'] ['270'] ['276']
                    //if ( isset($trans_id[$rsptype]) && strpos($seg, $trans_id[$rsptype]) ) {
                        //$err_seg .= (isset($sar[1]) && $sar[1]) ? '*'.$sar[1] : '';
                    //}

                    //if(strncmp($sar[1], 'SITUA', 5) === 0 ) {
                        //// SITUATIONAL TRIGGER
                        //$err_seg .= (isset($sar[2]) && $sar[2]) ? '*'.$sar[2] : '*';
                        //$err_seg .= (isset($sar[3]) && $sar[3]) ? '*'.$sar[3] : '*';
                        //$err_seg .= (isset($sar[5]) && $sar[5]) ? '*'.$sar[5] : '*';
                        //// |CTX-IK3*segID*segPos*loopLS*elemPos:compositePos:repPos
                    //} elseif ($ctx_ct > 1) {
                        //$err_seg .= '*'.$sar[2];
                        //if (!$have_pt) {
                            //$p1 = strpos($sar[1],$ds);
                            //$p2 = ($p1) ? strlen($sar[1])-$p1-1 : strlen($sar[1]);
                            //$ret_ar[$icn]['claim'][$cdx]['CLM01'] = substr($sar[1], -$p2);
                        //}
                    //}

                    //continue;
                //}
                //
                if (strncmp($seg, 'AK5' . $de, 4) === 0 || strncmp($seg, 'IK5' . $de, 4) === 0) {
                    // only store claims entries if there is an error
                    $sar = explode($de, $seg);
                    if ($sar[1] == 'A') {
                        continue;
                    } else {
                        $rej_ct++;
                        //
                        $cdx = count($ret_ar[$icn]['claim']);
                        //array('PtName', 'RspDate', 'Trace', 'Status', 'Control', 'FileName', 'RspType', 'err_seg');
                        $ret_ar[$icn]['claim'][$cdx]['PtName'] = $ptname;
                        $ret_ar[$icn]['claim'][$cdx]['RspDate'] = $svcdate;
                        //
                        $ret_ar[$icn]['claim'][$cdx]['Trace'] = $bht03syn;
                        $ret_ar[$icn]['claim'][$cdx]['Status'] = $sar[1];
                        $ret_ar[$icn]['claim'][$cdx]['Control'] = $icn;
                        $ret_ar[$icn]['claim'][$cdx]['FileName'] = $fn;
                        $ret_ar[$icn]['claim'][$cdx]['RspType'] = $rspsttype;
                        //
                        $ret_ar[$icn]['claim'][$cdx]['err_seg'] = $err_seg;
                        //
                        // AK502 = Code indicating implementation error found based on the syntax editing of a transaction set
                    }
                }

                //
            } // end foreach($env_ar['ST'] as $st)
            $ret_ar[$icn]['file'][$fdx]['RejCt'] = $rej_ct;
            $ret_ar[$icn]['file'][$fdx]['RspType'] = $rsptype;
        } // end foreach($env_ar['ISA'] as $icn => $isa)
    }

    //
    return $ret_ar;
}


/**
 * parse an x12 270/271 file into data rows for csv tables
 *
 * @param object   x12_file_object
 * @return array
 */
function edih_271_csv_data($obj270)
{
    //'f270 claim = array('PtName', 'ReqDate', 'PtAcct', 'InsLevel', 'BHT03', 'FileName', 'Payer');
    //'f270 file = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12_partner');
    //'f271 file = array('Date', 'FileName', 'Control', 'Claim_ct', 'Denied', 'Payer');
    //'f271 claim = array('PtName', 'RspDate', 'Trace', 'Status', 'BHT03', 'FileName', 'Payer');
    //
    $ret_ar = array();
    //
    $de = $obj270->edih_delimiters()['e'];
    $ds = $obj270->edih_delimiters()['s'];
    $fn = $obj270->edih_filename();
    $seg_ar = $obj270->edih_segments();
    $env_ar = $obj270->edih_envelopes();
    $ft = csv_file_type($obj270->edih_type());
    //
    // $rsptype = array('HS'=>'270', 'HB'=>'271', 'HC'=>'837', 'HR'=>'276', 'HI'=>'278');
    if (!isset($env_ar['ST'])) {
        csv_edihist_log('edih_271_csv_data: envelope error ' . $fn);
        return $ret_ar;
    }

    //
    if (!isset($env_ar['GS'])) {
        csv_edihist_log('edih_271_csv_data: envelope error');
        return $ret_ar;
    }

    //
    foreach ($env_ar['ISA'] as $icn => $isa) {
        // array('Date', 'FileName', 'Control', 'ta1ctrl', 'RejCt');
        $ret_ar[$icn]['type'] = $ft;
        $ret_ar[$icn]['claim'] = array();
        $ret_ar[$icn]['file'] = array();
        $rspdate = $isa['date'];
        $x12ptnr = $isa['receiver'];
        //
        foreach ($env_ar['GS'] as $gs) {
            if ($gs['icn'] == $icn) {
                $gsdate = $gs['date'];
                break;
            }
        }

        foreach ($env_ar['ST'] as $st) {
            //
            if ($st['icn'] != $icn) {
                continue;
            }

            //
            $stsegs = array_slice($seg_ar, $st['start'], $st['count']);
            $loopid = '0';
            $hl = 0;
            //
            $isaicn = $st['icn'];
            $stn = $st['stn'];
            $has_le = false;
            //
            // for "claim" array
            // 'f270' array('PtName', 'ReqDate', 'Trace', 'InsBnft', 'BHT03', 'FileName', 'Payer'); break;
            // 'f271'array('PtName', 'RspDate', 'Trace', 'Status', 'BHT03', 'FileName', 'Payer'); break;
            //
            foreach ($stsegs as $seg) {
                //
                if (strncmp($seg, 'BHT' . $de, 4) === 0) {
                    // new transaction
                    $cdx = (isset($ret_ar[$icn]['claim']) ) ? count($ret_ar[$icn]['claim']) : 0;
                    //
                    $sar = explode($de, $seg);
                    //
                    $isrqst = (isset($sar[2]) && $sar[2] == '13') ? true : false;
                    $isrsp = (isset($sar[2]) && $sar[2] == '11') ? true : false;
                    $loopid = 0;
                    $bnfteq = '';
                    $rej_ct = 0;
                    //
                    $dtkey =  ($ft == 'f271') ? 'RspDate' : 'ReqDate';
                    //
                    $ret_ar[$icn]['claim'][$cdx]['PtName'] = '';
                    $ret_ar[$icn]['claim'][$cdx][$dtkey] = $gsdate;
                    $ret_ar[$icn]['claim'][$cdx]['Trace'] = '';
                    if ($isrsp || $ft == 'f271') {
                        $ret_ar[$icn]['claim'][$cdx]['Status'] = 'A';    // 271
                        $ret_ar[$icn]['claim'][$cdx]['BHT03'] = (isset($sar[3])) ? $sar[3] : '';  //bht03 = $sar[3];
                    } else {
                        $ret_ar[$icn]['claim'][$cdx]['InsBnft'] = '';    // 270
                        $ret_ar[$icn]['claim'][$cdx]['BHT03'] = sprintf("%s%04d", $isaicn, $stn);
                    }

                    $ret_ar[$icn]['claim'][$cdx]['FileName'] = $fn;
                    $ret_ar[$icn]['claim'][$cdx]['Payer'] = '';
                    //
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
                    } elseif ($sar[3] == '21') {
                        $loopid = '2000B';                      // info receiver (clinic)
                    } elseif ($sar[3] == '22') {
                        $loopid = '2000C';                      // subscriber
                    } elseif ($sar[3] == '23') {
                        $loopid = '2000D';                      // dependent
                    } else {
                        //debug
                        csv_edihist_log('HL has no level ' . $seg . ' in ' . $fn);
                    }

                    continue;
                }

                //
                if (strncmp($seg, 'NM1' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    $nm1 = $sar[3];
                    $nm1 = ($sar[4]) ? $nm1 . ', ' . $sar[4] : $nm1;
                    $nm1 = ($sar[5]) ? $nm1 . ' ' . $sar[5] : $nm1;
                    if ($loopid == '2000A') {
                        $ret_ar[$icn]['claim'][$cdx]['Payer'] = $nm1;  //
                        $payer_name = $nm1;
                    } elseif ($loopid == '2000C' || $loopid == '2000D') {
                        $ret_ar[$icn]['claim'][$cdx]['PtName'] = $nm1;  //$ptname = $nm1;
                    }

                    continue;
                }

                // for 271 eligibility response (invalid data in 270 request)
                if (strncmp($seg, 'AAA' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    $status = 'R';
                    $rej_ct++;
                    $aaa_code = $sar[3];
                    $rsp_code = $sar[4];
                    if ($loopid == '2000A') {
                        $status = $status . ' Src ' . $aaa_code;
                    } elseif ($loopid == '2000B') {
                        $status = $status . ' Rcv ' . $aaa_code;
                    } elseif ($loopid == '2000C') {
                        $status = $status . ' Sbr ' . $aaa_code;
                    } elseif ($loopid == '2000D') {
                        $status = $status . ' Dep ' . $aaa_code;
                    }

                    //
                    $ret_ar[$icn]['claim'][$cdx]['Status'] = $status;
                    //
                    continue;
                }

                //
                // for 270 eligibility request
                if (strncmp($seg, 'EQ' . $de, 3) === 0) {
                    if (strlen((string)$sar[1])) {
                        $bnfteq .= ($bnfteq) ? '|' . $sar[1] : $sar[1];
                    } elseif (strlen((string)$sar[2])) {
                        $bnfteq .= ($bnfteq) ?  '|' . $sar[2] : $sar[2];
                    } else {
                        csv_edihist_log('Invalid EQ segment, missing benefit type in ' . $fn);
                        continue;
                    }

                    $ret_ar[$icn]['claim'][$cdx]['InsBnft'] = $bnfteq;
                    continue;
                }

                //  overridden by REF*EJ*  (not)
                if (strncmp($seg, 'TRN' . $de, 4) === 0) {
                    if ($loopid = '2000C' || $loopid == '2000D') {
                        $sar = explode($de, $seg);
                        $ret_ar[$icn]['claim'][$cdx]['Trace'] = $sar[2];    //$ptacct = $sar[2];
                    }

                    //
                    continue;
                }

                // for 271 eligibility response
                if (strncmp($seg, 'EB' . $de, 3) === 0) {
                    $status = ( isset($status) ) ? $status : '';
                    //
                    if (strpos($ret_ar[$icn]['claim'][$cdx]['Status'], 'tive')) {
                        continue;
                    }

                    //
                    $sar = explode($de, $seg);
                    //
                    if (isset($sar[2])) {
                        if ($sar[2] == '6' || $sar[2] == '7' || $sar[2] == '8') {
                            $status = 'Inactive';
                        } elseif ($sar[2] == 'I') {
                            $status = 'Non-Covered';
                            $rej_ct++;
                        } elseif ($sar[2] == 'E') {
                            $status = 'Exclusions';
                            $rej_ct++;
                        } elseif ($sar[2] == 'N') {
                            $status = 'Restricted';
                            $rej_ct++;
                        } elseif ($sar[2] == 'V') {
                            $status = 'Unknown';
                            $rej_ct++;
                        } elseif ($sar[2] == 'T') {
                            $status = 'Card Lost';
                            $rej_ct++;
                        } elseif ($sar[2] == 'U') {
                            $status = 'Contact';
                            $rej_ct++;
                        } elseif ($sar[2] == '1') {
                            $status = 'Active';
                        } elseif ($sar[2] == '2' || $sar[2] == '3' || $sar[2] == '4') {
                            $status = 'Active Cap';
                        }
                    }

                    //
                    $ret_ar[$icn]['claim'][$cdx]['Status'] = $status;
                    //
                    continue;
                }

                //
                if (strncmp($seg, 'DTP' . $de, 4) === 0) {
                    $sar = explode($de, $seg);
                    $dtp03 = (isset($sar[2]) && $sar[2] == 'D8') ? $sar[3] : substr($sar[3], 0, 8);
                    if ($isrsp) {
                        if ($loopid == '2100C' || $loopid == '2110C') {
                            $ret_ar[$icn]['claim'][$cdx]['RspDate'] = $dtp03;
                        } elseif ($loopid == '2100D' || $loopid == '2110D') {
                            $ret_ar[$icn]['claim'][$cdx]['RspDate'] = $dtp03;
                        }
                    } else {
                        if ($loopid == '2100C' || $loopid == '2110C') {
                            $ret_ar[$icn]['claim'][$cdx]['ReqDate'] = $dtp03;
                        } elseif ($loopid == '2100D' || $loopid == '2110D') {
                            $ret_ar[$icn]['claim'][$cdx]['ReqDate'] = $dtp03;
                        }
                    }

                    //
                    continue;
                }

                //
                /*
                if (strncmp($seg, 'REF'.$de.'EJ'.$de, 7) === 0 ) {
                    // patient account -- replaces or replaced by TRN02
                    $sar = explode($de, $seg);
                    $ret_ar[$icn]['claim'][$cdx]['PtAcct'] =  $sar[2];   //$ptacct =  $sar[2];
                    //
                    continue;
                }
                */
                //
            } // end foreach($stsegs as $seg)
            //
        } // endforeach($env_ar['ST'] as $st
                // file: 'f271': array('Date', 'FileName', 'Control', 'Claim_ct', 'Reject', 'Payer')
        $fdx = count($ret_ar[$icn]['file']);
        //'f270': $hdr = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12Partner');
        $ret_ar[$icn]['file'][$fdx]['Date'] = $gsdate;
        $ret_ar[$icn]['file'][$fdx]['FileName'] = $fn;
        // for 270 type, could use GSO6 if it were unique
        $ret_ar[$icn]['file'][$fdx]['Control'] = $icn;
        $ret_ar[$icn]['file'][$fdx]['Claim_ct'] = $cdx;
        if ($isrsp || $ft == 'f271') {
            $ret_ar[$icn]['file'][$fdx]['Reject'] = $rej_ct;
            $ret_ar[$icn]['file'][$fdx]['Payer'] = $payer_name;
        } else {
            $ret_ar[$icn]['file'][$fdx]['x12Partner'] = $x12ptnr;
        }

        //
    } // endforeach($env_ar['ISA'] as $icn=>$isa)
    //
    return $ret_ar;
}

/*
 * this function opens the x12 file in the object and routes
 * the object to the parsing function according to the x12 type
 * csvdata array design:
 *  [$icn]['claims'][i]  [$icn]['files'][i]  [$icn]['type']
 *
 * @uses edih_835_csv_data()
 * @uses edih_837_csv_data()
 * @uses edih_277_csv_data()
 * @uses edih_271_csv_data()
 * @uses edih_278_csv_data()
 * @uses edih_997_csv_data()
 *
 * @param string     path to file
 * @return array     data for csv table records
 */
function edih_parse_select($file_path)
{
    $csvdata = array();
    // csvdata array design:
    //  $csvdata[$icn]['claims'][i]  [$icn]['files'][i]  [$icn]['type']
    //
    $x12_obj = csv_check_x12_obj($file_path);
    //$x12_obj = new edih_x12_file($file_path);
    if ($x12_obj instanceof edih_x12_file) {
        $ft = $x12_obj->edih_type();
    } else {
        csv_edihist_log('edih_parse_select: error in file path');
        return $csvdata;
    }

    //'HB'=>'271', 'HS'=>'270', 'HR'=>'276', 'HI'=>'278',
    //'HN'=>'277', 'HP'=>'835', 'FA'=>'999', 'HC'=>'837'
    //
    if ($ft == 'HP') {
        $csvdata = edih_835_csv_data($x12_obj);
    } elseif ($ft == 'HC') {
        $csvdata = edih_837_csv_data($x12_obj);
    } elseif ($ft == 'HN' || $ft == 'HR') {
        $csvdata = edih_277_csv_data($x12_obj);
    } elseif ($ft == 'FA') {
        $csvdata = edih_997_csv_data($x12_obj);
    } elseif ($ft == 'HB' || $ft == 'HS') {
        $csvdata = edih_271_csv_data($x12_obj);
    } elseif ($ft == 'HI') {
        $csvdata = edih_278_csv_data($x12_obj);
    } else {
        // debug
        csv_edihist_log('edih_parse_select(): unsupported file type ' . $ft . ' name: ' . basename($file_path));
    }

    //
    return $csvdata;
}
