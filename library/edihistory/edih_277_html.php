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

use OpenEMR\Billing\EdiHistory\Claim277Renderer;

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

    // get other necessary items: narrow the delimiters to non-empty strings at
    // the source so the segment renderers receive honestly-typed values
    $delimiters = $obj277->edih_delimiters();
    $de = is_array($delimiters) ? ($delimiters['e'] ?? '') : '';
    $ds = is_array($delimiters) ? ($delimiters['s'] ?? '') : '';
    $dr = is_array($delimiters) ? ($delimiters['r'] ?? '') : '';
    $fnRaw = $obj277->edih_filename();
    $fn = is_string($fnRaw) ? $fnRaw : '';

    if (!is_array($trans) || !count($trans)) {
        return "<p>Did not find transaction " . text($bht03) . " in " . attr($fn) . "</p>" . PHP_EOL;
    }
    if (!is_string($de) || $de === '' || !is_string($ds) || $ds === '') {
        csv_edihist_log("edih_277_transaction_html: invalid x12 delimiters $fn");
        return "<p>Did not find transaction " . text($bht03) . " in " . attr($fn) . "</p>" . PHP_EOL;
    }

    $cd27x = new edih_271_codes($ds, $dr);

    $h3_lbl = '';
    $str_html = "";

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

    // one accumulator per HL section, emitted in a fixed order at the end
    $html = [
        'src' => '',
        'rcv' => '',
        'prv' => '',
        'sbr_nm1' => '',
        'dep_nm1' => '',
        'sbr_stc' => '',
        'dep_stc' => '',
    ];

    // state carried across segments: the loop cursor, the accordion label,
    // and the QTY prefix consumed by the next AMT (277CA)
    $loopid = '';
    $bht = '';
    $qtystr = '';

    foreach ($trans as $transaction) {
        $segments = is_array($transaction) ? array_filter($transaction, is_string(...)) : [];
        foreach ($segments as $seg) {
            // dispatch on the segment id: the token before the first element delimiter
            $sar = explode($de, $seg);
            $segid = $sar[0];
            // the row class is fixed by the current loop, derived from its id
            $cls = Claim277Renderer::rowClass($loopid);

            switch ($segid) {
                case 'BHT':
                    $loopid = 'Heading';
                    $bhtRow = Claim277Renderer::bht($sar, $cd27x);
                    $hdr_html .= $bhtRow['html'];
                    $bht = $bhtRow['ref'];
                    break;

                case 'HL':
                    // HL level code => [loopid, section accumulator, section heading]
                    $hl = match ($sar[3] ?? '') {
                        '20' => ['2000A', 'src', 'Information Source'],
                        '21' => ['2000B', 'rcv', 'Information Receiver'],
                        '19' => ['2000C', 'prv', 'Provider'],
                        '22' => ['2000D', 'sbr_nm1', 'Subscriber'],
                        'PT' => ['2000D', 'sbr_nm1', 'Patient'],  // patient in 277CA
                        '23' => ['2000E', 'dep_nm1', 'Dependent'],
                        default => null,
                    };
                    if ($hl === null) {
                        csv_edihist_log("edih_277_transaction_html: HL segment error $fn");
                    } else {
                        [$loopid, $section, $heading] = $hl;
                        $cls = Claim277Renderer::rowClass($loopid);
                        $html[$section] .= "<tr class='$cls'><td colspan=4><b>$heading</b></td></tr>";
                    }

                    $qtystr = '';  // reset for QTY and AMT segments in 277CA
                    break;

                case 'NM1':
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
                        $nm1Row = Claim277Renderer::nm1($sar, $cls, $cd27x);
                        $html[$section] .= $nm1Row['html'];
                        if ($setLabel) {
                            $h3_lbl = $nm1Row['name'];
                        }
                    }
                    break;

                case 'PER':
                    if ($loopid == '2100A') {
                        $html['src'] .= Claim277Renderer::per($sar, $cls, $cd27x);
                    } else {
                        csv_edihist_log('edih_277_html: PER segment not in 2100A loop ' . $fn);
                    }
                    break;

                case 'TRN':
                    $trn = match ($loopid) {
                        '2100B' => ['rcv', '2200B', false],
                        '2100C' => ['prv', '2200C', false],
                        '2100D' => ['sbr_stc', '2200D', true],
                        '2100E' => ['dep_stc', '2200E', true],
                        default => null,
                    };
                    if ($trn !== null) {
                        [$section, $loopid, $appendLabel] = $trn;
                        $trnRow = Claim277Renderer::trn($sar, $cls);
                        $html[$section] .= $trnRow['html'];
                        if ($appendLabel) {
                            $h3_lbl = ($h3_lbl) ? $h3_lbl . ' ' . $trnRow['ref'] : $h3_lbl;
                        }
                    }
                    break;

                case 'STC':
                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200C' => 'prv',
                        '2200D' => 'sbr_stc',
                        '2200E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= Claim277Renderer::stc($sar, $ds, $cls, $cd27x);
                    }
                    break;

                // in 277CA, expect QTY followed by AMT
                // do not expect QTY or AMT in regular 277
                case 'QTY':
                    $qtystr = Claim277Renderer::qtyString($sar);
                    break;

                case 'AMT':
                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200C' => 'prv',
                        '2200D' => 'sbr_stc',
                        '2200E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= Claim277Renderer::amt($sar, $cls, $qtystr);
                    }
                    $qtystr = '';
                    break;

                case 'REF':
                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200C' => 'prv',
                        '2200D', '2220D' => 'sbr_stc',
                        '2200E', '2220E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= Claim277Renderer::ref($sar, $cls, $cd27x);
                    }
                    break;

                case 'DTP':
                    $section = match ($loopid) {
                        '2200D', '2220D' => 'sbr_stc',
                        '2200E', '2220E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= Claim277Renderer::dtp($sar, $cls, $cd27x);
                    }
                    break;

                case 'SVC':
                    $section = match ($loopid) {
                        '2200B' => 'rcv',
                        '2200D', '2220D' => 'sbr_stc',
                        '2200E', '2220E' => 'dep_stc',
                        default => null,
                    };
                    if ($section !== null) {
                        $html[$section] .= Claim277Renderer::svc($sar, $ds, $cls, $section === 'rcv', $cd27x);
                    }
                    break;
            }
        }

        if ($accordion) {
            $str_html .= "<h3>" . text($bht . " " . $h3_lbl) . "</h3>";
            $str_html .= "<div id='ac_" . attr($bht) . "'>";
        }

        $str_html .= $hdr_html ?: "";
        $str_html .= $html['src'] ?: "";
        $str_html .= $html['rcv'] ?: "";
        $str_html .= $html['prv'] ?: "";
        $str_html .= $html['sbr_nm1'] ?: "";
        $str_html .= $html['sbr_stc'] ?: "";
        $str_html .= $html['dep_nm1'] ?: "";
        $str_html .= $html['dep_stc'] ?: "";
        $str_html .= "<tr><td colspan=4>&nbsp;</td></tr>";
        $str_html .= "</tbody>" . "</table>";

        if ($accordion) {
            $str_html .= "</div>";
        }
    }

    return $str_html;
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
