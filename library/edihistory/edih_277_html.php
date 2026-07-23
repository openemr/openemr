<?php

/**
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
 */

/**
 * Produce an html display of information in
 * the x12 edi 271 eligibility report for a particular patient
 *
 * @uses edih_271_codes()
 * @uses edih_format_money()
 * @uses edih_format_date()
 * @uses edih_format_percent()
 *
 * @param mixed $obj277 edih_x12_file type 271
 * @param string $bht03 bht03 or clm01 reference for transaction
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

    if (!is_array($trans) || !count($trans)) {
        $str_html = "<p>Did not find transaction " . text($bht03) . " in " . attr($fn) . "</p>" . PHP_EOL;
        return $str_html;
    }

    $cd27x = new edih_271_codes($ds, $dr);

    $h3_lbl = '';
    $str_html = "";
    $hdr_html = "";

    $bht03Attr = attr($bht03);
    $fnText = text($fn);
    $hdr_html = <<<HTML
        <table id={$bht03Attr} class='h277' columns=4>
        <caption>Claim Status</caption>
        <thead>
        <tr><th>Reference</th><th>Information</th><th colspan=2>{$fnText}</th></tr>
        </thead>
        <tbody>
        HTML;

    $html = [
        'src' => '',
        'rcv' => '',
        'prv' => '',
        'sbr_nm1' => '',
        'dep_nm1' => '',
        'sbr_stc' => '',
        'dep_stc' => '',
    ];
    $cls = '';
    $loopid = '';
    $bht = '';
    $qtystr = '';
    $sc204 = '';
    $sc304 = '';

    foreach ($trans as $transaction) {
        $segments = is_array($transaction) ? array_filter($transaction, is_string(...)) : [];
        foreach ($segments as $seg) {

            $idtype = '';
            $name = '';

            $var = '';
            $rej_reason = '';
            $follow = '';
            $addr = '';

            // dispatch on the segment id: the token before the first element delimiter
            $sar = explode($de, $seg);
            $segid = $sar[0];
            switch ($segid) {
                case 'BHT':
                    $loopid = 'Heading';
                    $elem01 = match ($sar[1] ?? null) {
                        '0010' => 'Src, Rcv, Prv, Sbr, Dep',
                        '0085' => 'Src, Rcv, Prv, Pt',
                        null => '',
                        default => "Not determined ({$sar[1]})",
                    };


                    $elem02 = ($sar[2] ?? false) !== false ? $cd27x->get_271_code('BHT02', $sar[2]) : "";
                    $elem03 = ($sar[3] ?? '') ?: "";
                    $elem04 = ($sar[4] ?? '') ? edih_format_date($sar[4]) : "";
                    $elem06 = ($sar[6] ?? '') ? $cd27x->get_271_code('BHT06', $sar[6]) : "";

                    $e01Text = text($elem01);
                    $e02Text = text($elem02);
                    $e03Text = text($elem03);
                    $e04Text = text($elem04);
                    $hdr_html .= <<<HTML
                        <tr><td colspan=2><em>Reference:</em> {$e03Text}</td><td colspan=2><em>Sequence:</em> {$e01Text}</td></tr>
                        <tr><td colspan=2><em>Date:</em> {$e04Text}</td><td colspan=2><em>Type:</em> {$e02Text}</td>
                        HTML;
                    $hdr_html .= ($elem06) ? "<tr><td>&gt;</td><td colspan=3><em>Type:</em> " . text($elem06) . "</td></tr>" : "";

                    $bht = $elem03;
                break;


                case 'HL':
                    $elem03 = $sar[3] ?? "";
                    // HL level code => [loopid, $cls, section accumulator, section heading]
                    $hl = match ($elem03) {
                        '20' => ['2000A', 'src', 'src', 'Information Source'],
                        '21' => ['2000B', 'rcv', 'rcv', 'Information Receiver'],
                        '19' => ['2000C', 'prv', 'prv', 'Provider'],
                        '22' => ['2000D', 'sbr', 'sbr_nm1', 'Subscriber'],
                        'PT' => ['2000D', 'sbr', 'sbr_nm1', 'Patient'],  // patient in 277CA
                        '23' => ['2000E', 'dep', 'dep_nm1', 'Dependent'],
                        default => null,
                    };
                    if ($hl === null) {
                        csv_edihist_log("edih_277_transaction_html: HL segment error $fn");
                    } else {
                        [$loopid, $cls, $section, $heading] = $hl;
                        $html[$section] .= "<tr class='$cls'><td colspan=4><b>$heading</b></td></tr>" . PHP_EOL;
                    }


                    $qtystr = '';  // reset for QTY and AMT segments in 277CA
                break;


                case 'NM1':

                    $nm101 = $sar[1] ?? '';
                    $descr = ($nm101) ? $cd27x->get_271_code('NM101', $nm101) : "";

                    $name = ($sar[3] ?? '') ?: "";
                    $name .= ($sar[7] ?? '') ? " {$sar[7]}" : "";
                    $name .= ($sar[4] ?? '') ? ", {$sar[4]}" : "";
                    $name .= ($sar[5] ?? '') ? " {$sar[5]}" : "";
                    $nm109 = ($sar[9] ?? '') ?: "";

                    $nm108 = ($sar[8] ?? '') ? $cd27x->get_271_code('NM108', $sar[8]) : "";

                    $nm1 = match ($loopid) {
                        '2000A' => ['src', '2100A', false],
                        '2000B' => ['rcv', '2100B', false],
                        '2000C' => ['prv', '2100C', false],
                        '2000D' => ['sbr_nm1', '2100D', true],
                        '2000E' => ['dep_nm1', '2100E', true],
                        default => null,
                    };
                    if ($nm1 !== null) {
                        [$section, $loopid, $setLabel] = $nm1;
                        $html[$section] .= "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'>" . text($name) . "</td></tr>" . PHP_EOL;
                        $html[$section] .= "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3 title='" . attr($descr) . "'><em>" . text($nm108) . "</em> " . text($nm109) . "</td></tr>" . PHP_EOL;
                        if ($setLabel) {
                            $h3_lbl = $name;
                        }
                    }


                break;


                case 'PER':

                    $elem01 = $sar[1] ?? '';
                    $elem02 = $sar[2] ?? '';
                    $elem03 = (isset($sar[3])) ? $cd27x->get_271_code('PER03', $sar[3]) : "";
                    $elem04 = $sar[4] ?? '';
                    $elem05 = (isset($sar[5])) ? $cd27x->get_271_code('PER03', $sar[5]) : "";
                    $elem06 = $sar[6] ?? '';
                    $elem07 = (isset($sar[7])) ? $cd27x->get_271_code('PER03', $sar[7]) : "";
                    $elem08 = $sar[8] ?? '';
                    $elem09 = $sar[9] ?? '';

                    if ($loopid == '2100A') {
                        $html['src'] .= "<tr class='" . $cls . "'><td colspan=2>" . text($elem02) . "</td><td colspan=2 title='" . attr($elem03 . " " . $elem05 . " " . $elem07) . "'>" . text($elem04 . " " . $elem06 . " " . $elem08) . "</td></tr>" . PHP_EOL;
                    } else {
                        csv_edihist_log('edih_277_html: PER segment not in 2100A loop ' . $fn);
                    }


                break;


                case 'TRN':

                    $elem01 = ($sar[1] ?? '') == "1" ? "Transaction Ref" : "Trace";
                    $elem02 = $sar[2] ?? '';
                    $elem03 = $sar[3] ?? '';
                    $elem04 = $sar[4] ?? '';

                    $trn = match ($loopid) {
                        '2100B' => ['rcv', '2200B', false],
                        '2100C' => ['prv', '2200C', false],
                        '2100D' => ['sbr_stc', '2200D', true],
                        '2100E' => ['dep_stc', '2200E', true],
                        default => null,
                    };
                    if ($trn !== null) {
                        [$section, $loopid, $appendLabel] = $trn;
                        $html[$section] .= "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3><em>" . text($elem01) . "</em> " . text($elem02) . "</td></tr>" . PHP_EOL;
                        if ($appendLabel) {
                            $h3_lbl = ($h3_lbl) ? $h3_lbl . ' ' . $elem02 : $h3_lbl;
                        }
                    }


                break;


                case 'STC':
                    // reset composite-derived status codes so a prior STC's codes do
                    // not leak into a segment that omits its own STC01/STC10/STC11
                    // composite elements (the rows below are gated on isset()/truthiness)
                    $sc101 = $sc102 = $sc103 = null;
                    $sc201 = $sc202 = $sc203 = null;
                    $sc301 = $sc302 = $sc303 = null;
                    $sc204 = $sc304 = "";
                    //
                    if (isset($sar[1])) {
                        if (strpos($sar[1], (string) $ds)) {       // claim status category : claim status : entity identifier
                            $scda = explode($ds, $sar[1]);
                            $sc101 = $scda[0] ? $cd27x->get_271_code('HCCSCC', $scda[0]) : "";
                            $sc102 = ($scda[1] ?? '') ? $cd27x->get_271_code('HCCSC', $scda[1]) : "";
                            $sc103 = ($scda[2] ?? '') ? $cd27x->get_271_code('NM101', $scda[2]) : "";
                        }
                    }

                    $stc02 = ($sar[2] ?? '') ? edih_format_date($sar[2]) : "";  // status information date
                    $stc03 = match ($sar[3] ?? null) {                                           // action code
                        'WQ' => 'Accepted',
                        'F' => 'Final',
                        '15' => 'Correct/Resubmit',
                        'U' => 'Rejected',
                        null => '',
                        default => $sar[3],
                    };

                    $stc04 = ($sar[4] ?? '') ? edih_format_money($sar[4]) : "";  // billed amount
                    $stc05 = ($sar[5] ?? '') ? edih_format_money($sar[5]) : "";  // paid amount
                    $stc06 = ($sar[6] ?? '') ? edih_format_date($sar[6]) : "";   // payment date
                //$stc07  not used
                    $stc08 = ($sar[8] ?? '') ? edih_format_date($sar[8]) : "";   // check issue date
                    $stc09 = ($sar[9] ?? '') ?: "";                        // check or eft number

                    $stc10 = "";
                    if ($sar[10] ?? false) {     // claim status category : claim status : entity identifier
                        if (strpos($sar[10], (string) $ds)) {
                            $scda = explode($ds, $sar[10]);
                            $sc201 = $scda[0] ? $cd27x->get_271_code('HCCSCC', $scda[0]) : "";
                            $sc202 = ($scda[1] ?? '') ? $cd27x->get_271_code('HCCSC', $scda[1]) : "";
                            $sc203 = ($scda[2] ?? '') ? $cd27x->get_271_code('NM101', $scda[2]) : "";
                            $sc204 = ($scda[3] ?? '') === 'RA' ? "Rx Reject/Payment Codes" : "";
                        } else {
                            $stc10 = $sar[10];
                        }
                    }


                    $stc11 = "";
                    if ($sar[11] ?? false) {     // claim status category : claim status : entity identifier
                        if (strpos($sar[11], (string) $ds)) {
                            $scda = explode($ds, $sar[11]);
                            $sc301 = $scda[0] ? $cd27x->get_271_code('HCCSCC', $scda[0]) : "";
                            $sc302 = ($scda[1] ?? '') ? $cd27x->get_271_code('HCCSC', $scda[1]) : "";
                            $sc303 = ($scda[2] ?? '') ? $cd27x->get_271_code('NM101', $scda[2]) : "";
                            $sc304 = ($scda[3] ?? '') === 'RA' ? "Rx Reject/Payment Codes" : "";
                        } else {
                            $stc11 = $sar[11];
                        }
                    }


                    $stc12 = ($sar[12] ?? '') ?: "";    // message

                    $stc_html = (isset($sc101)) ? "<tr class='" . $cls . "'><td>" . text($stc03) . "</td><td colspan=2>" . text($sc101) . "</td><td>" . text($stc02 . " " . $stc04) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= (isset($sc102)) ? "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($sc102) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= ($sc103 ?? '') ? "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3><em>Entity</em> " . text($sc103) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= ($stc05 || $stc06 || $stc08 || $stc09) ? "<tr class='" . $cls . "'><td><em>Payment</em></td><td colspan=3>" . text($stc05 . " " . $stc06 . " " . $stc08 . " " . $stc09) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= (isset($sc201)) ?  "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($sc201 . " " . $sc204) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= (isset($sc202)) ?  "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($sc202) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= ($sc203 ?? '') ?    "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3><em>Entity</em> " . text($sc203) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= (isset($sc301)) ?  "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($sc301 . " " . $sc304) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= (isset($sc302)) ?  "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($sc302) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= ($sc303 ?? '') ?    "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3><em>Entity</em> " . text($sc303) . "</td></tr>" . PHP_EOL : "";
                    $stc_html .= ($stc12) ? "<tr class='" . $cls . "'><td><em>Message</em></td><td colspan=3>" . text($stc12) . "</td></tr>" . PHP_EOL : "";

                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200C' => 'prv',
                        '2200D' => 'sbr_stc',
                        '2200E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= $stc_html;
                    }


                break;

            // in 277CA, expect QTY followed by AMT
            // do not expect QTY or AMT in regular 277
                case 'QTY':
                    $qtystr = match ($sar[1] ?? null) {
                        '90' => 'Acknowledged Quantity ',
                        'AA' => 'Unacknowledged Quantity ',
                        'QA' => 'Quantity Approved ',
                        'QC' => 'Quantity Disapproved ',
                        null => '',
                        default => 'Quantity ',
                    };

                    $qtystr .= ($sar[2] ?? '') ?: "";
                break;


                case 'AMT':
                    // 277CA
                    $amtstr = ($sar[1] ?? '') == 'YU' ? "Amt " : "Amt Rej ";
                    $amtstr .= ($sar[2] ?? '') ? edih_format_money($sar[2]) : "";

                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200C' => 'prv',
                        '2200D' => 'sbr_stc',
                        '2200E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($qtystr . " " . $amtstr) . "</td></tr>" . PHP_EOL;
                    }

                    $amtstr = '';
                    $qtystr = '';

                break;


                case 'REF':

                    $elem01 = (isset($sar[1])) ? $cd27x->get_271_code('REF', $sar[1]) : '';
                    $elem02 = $sar[2] ?? '';
                    $elem03 = $sar[3] ?? '';

                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200C' => 'prv',
                        '2200D', '2220D' => 'sbr_stc',
                        '2200E', '2220E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= "<tr class='" . $cls . "'><td>&gt;</td><td colspan=2><em>" . text($elem01) . "</em> " . text($elem02) . "</td><td>" . text($elem03) . "</td></tr>" . PHP_EOL;
                    }


                break;


                case 'DTP':

                    $var = '';

                    $elem01 = ($sar[1] ?? '') ? $cd27x->get_271_code('DTP', $sar[1]) : "";
                    $elem02 = $sar[2] ?? '';
                    $elem03 = $sar[3] ?? '';

                    $idtype = ($elem01) ? $cd27x->get_271_code('DTP', $elem01) : "";
                    if ($elem02 == 'D8' && $elem03) {
                        $var = edih_format_date($elem03);
                    } elseif ($elem02 == 'RD8' && $elem03) {
                        $var = edih_format_date(substr($elem03, 0, 8));
                        $var .= ' - ' . edih_format_date(substr($elem03, -8));
                    }


                    $section = match ($loopid) {
                        '2200D', '2220D' => 'sbr_stc',
                        '2200E', '2220E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= "<tr class='" . $cls . "'><td>&gt;</td><td>" . text($elem01) . "</td><td colspan=2>" . text($var) . "</td></tr>" . PHP_EOL;
                    }


                break;


                case 'SVC':

                    $elem01 = '';                           // composite procedure code source:code:modifier:modifier
                    if ($sar[1] ?? false) {
                        // construct a code source code modifier string
                        if (strpos($sar[1], (string) $ds)) {
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


                    $elem02 = ($sar[2] ?? '') ? edih_format_money($sar[2]) : "";  // billed amount
                    $elem03 = ($sar[3] ?? '') ? edih_format_money($sar[3]) : "";  // paid amount
                    $elem04 = ($sar[4] ?? '') ?: "";                   // revenue code
                    $elem05 = ($sar[5] ?? '') ?: "";                   // quantity
                    // $elem06 not used
                    $elem07 = ($sar[7] ?? '') ?: "";                   // original unis of service

                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200D', '2220D' => 'sbr_stc',
                        '2200E', '2220E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= ($section === 'rcv')
                            ? "<tr class='" . $cls . "'><td><em>Service</em></td><td>" . text($elem01) . "</td><td>" . text($elem02) . "</td><td>" . text($elem04) . "</td></tr>" . PHP_EOL
                            : "<tr class='" . $cls . "'><td><em>Service</em></td><td>" . text($elem01) . "</td><td colspan=2>" . text($elem02 . " " . $elem04) . "</td></tr>" . PHP_EOL;
                        $html[$section] .= ($elem03 || $elem04) ? "<tr class='" . $cls . "'><td>&gt;</td><td colspan=3>" . text($elem03 . " " . $elem04) . "</td></tr>" . PHP_EOL : "";
                    }


                break;
            }


        }


        if ($accordion) {
            $str_html .= "<h3>" . text($bht . " " . $h3_lbl) . "</h3>" . PHP_EOL;
            $str_html .= "<div id='ac_" . attr($bht) . "'>" . PHP_EOL;
        }

        $str_html .= $hdr_html ?: "";
        $str_html .= $html['src'] ?: "";
        $str_html .= $html['rcv'] ?: "";
        $str_html .= $html['prv'] ?: "";
        $str_html .= $html['sbr_nm1'] ?: "";
        $str_html .= $html['sbr_stc'] ?: "";
        $str_html .= $html['dep_nm1'] ?: "";
        $str_html .= $html['dep_stc'] ?: "";
        $str_html .= "<tr><td colspan=4>&nbsp;</td></tr>" . PHP_EOL;
        $str_html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;

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

    if ($filename) {
        $fn = $filename;
    } else {
        csv_edihist_log("edih_277_html: called with no file arguments");
        $html_str .= "Error, no file given<br />" . PHP_EOL;
        return $html_str;
    }

    if ($fn) {
        $obj277 = csv_check_x12_obj($fn, 'f277');
        if ($obj277 !== false) {
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


                    // get each transaction
                    foreach ($st['bht03'] as $bht) {
                        //$html_str .= "<h3>$bht Claim Status <em>Date</em> $gs_date <em>Source</em> $gs_sender</h3>".PHP_EOL;
                        //$html_str .= "<div id='ac_$bht'>".PHP_EOL;

                        $html_str .= edih_277_transaction_html($obj277, $bht, true);

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
        csv_edihist_log("edih_277_html: error in retrieving file object");
        return $html_str;
    }

    return $html_str;
}
