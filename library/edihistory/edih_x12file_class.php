<?php

/*
 * edih_x12file_class.php
 *
 * Copyright 2014 Kevin McCormick Longview, Texas
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
 * @link: https://www.open-emr.org
 * @author: Kevin McCormick
 * @package: OpenEMR
 * @subpackage: ediHistory
 */

/* ********* project notes =================
 * determine GET and POST array elements
 * process new files -- type and csv data values
 * display tables -- links with GET and POST
 * find files -- find transactions
 * format display
 *
 * ==========================================
 */

/*********** php code here ****************************************************************/

/**
 * Class to read EDI X12 files in healthcare setting
 *
 * It is assumed that EDI X12 files will have mime-type text/plain; charset=us-ascii
 *
 * initialize with file path or as empty object, e.g.
 *   $x12_file = new edih_x12_file(filepath);  segment array and envelope array, no file text
 *   $x12_file = new edih_x12_file(filepath, false);  no segment or envelope array, no file text
 *   $x12_file = new edih_x12_file(filepath, false, true);  no segment or envelope array, yes file text
 *     or
 *   $x12_file = new edih_x12_file();  empty object, ' _x12_ ' methods available if file text supplied as method argument
 *
 * The properties filename, type, version, valid, isx12, hasGS, hasST, and delimiters should be available
 * if the valid filepath is provided when creating the object.
 *
 * @param string    $filepath  default = ''
 * @param bool      $mk_segs  default = true
 * @param bool      $text  default = false
 * @return bool|string   true for empty object "ovgis" for validated x12
 */
class edih_x12_file
{
    // properties
    private $filepath = '';
    private $filename = '';
    private $type = '';
    private $version = '';
    private $text = '';
    private $length = 0;
    private $valid = false;
    private $isx12 = false;
    private $hasGS = false;
    private $hasST = false;
    private $message = array();
    private $delimiters = array();
    private $segments = array();
    private $envelopes = array();
    //
    private $constructing = false;
    //
    private $gstype_ar = array('HB' => '271', 'HS' => '270', 'HR' => '276', 'HN' => '277',
                            'HI' => '278', 'HP' => '835', 'FA' => '999', 'HC' => '837');
    //
    function __construct($file_path = '', $mk_segs = true, $text = false)
    {
        //
        if ($file_path === '') {
            return true;
        }

        //
        if (is_file($file_path) && is_readable($file_path)) {
            $this->filepath = trim($file_path);
            $this->filename = basename($this->filepath);
            $f_text = file_get_contents($this->filepath);
            //
            $testval = ($f_text) ? $this->edih_x12_scan($f_text) : '';
            $this->valid = ( strpos($testval, 'v') ) ? true : false;
            $this->isx12 = ( strpos($testval, 'i') ) ? true : false;
            $this->hasGS = ( strpos($testval, 'g') ) ? true : false;
            $this->hasST = ( strpos($testval, 's') ) ? true : false;
            //
            if ($this->valid) {
                $this->constructing = true;
                $this->text = ($text) ? $f_text : '';
                $this->length = ($f_text) ? strlen($f_text) : 0;
                if ($this->isx12) {
                    $this->delimiters = $this->edih_x12_delimiters(substr($f_text, 0, 126));
                    $this->version = substr($f_text, 84, 5);
                    if ($mk_segs) {
                        $this->segments = $this->edih_x12_segments($f_text);
                        if (is_array($this->segments) && count($this->segments)) {
                            $this->envelopes = $this->edih_x12_envelopes();
                            $this->type = $this->edih_x12_type();
                        } else {
                            $this->message[] = 'edih_x12_file: error in creating segment array ' . text($this->filename) . PHP_EOL;
                        }
                    } else {
                        // read file contents to try and determine x12 type
                        $this->type = $this->edih_x12_type($f_text);
                    }
                }
            }
        } else {
            // invalid file path
            $this->message[] = 'edih_x12_file: invalid file path ' . text($file_path);
        }

        $this->constructing = false;
        return $this->valid;
    }

    /*
     * function to support empty object and '_x12_' functions called with supplied file text
     *
     * @param string   $file_text
     * @param bool     return x12 type
     * @param bool     return delimiters
     * @param bool     return segments
     * @return array   array['filetext'] and maybe ['type'] ['$delimiters'] ['segments']
     */
    private function edih_file_text($file_text, $type = false, $delimiters = false, $segments = false)
    {
        //
        $ret_ar = array();
        if (!$file_text || is_string($file_text) == false) {
            $this->message[] = 'edih_file_text(): invalid argument';
            return $ret_ar;
        }

        // do verifications
        $v = $this->edih_x12_scan($file_text);
        if (!strpos($v, 's')) {
            $this->message[] = 'edih_file_text(): failed scan of file text (' . text($v) . ')';
            return $ret_ar;
        }

        //
        $this->constructing = true;
        //
        if ($type) {
            $ret_ar['type'] = $this->edih_x12_type($file_text);
        }

        if ($delimiters) {
            $ret_ar['delimiters'] = $this->edih_x12_delimiters(substr($file_text, 0, 126));
        }

        if ($segments) {
            $ret_ar['segments'] = $this->edih_x12_segments($file_text);
        }

        //
        $this->constructing = false;
        //
        return $ret_ar;
    }

    /*
     *  functions to return properties
     */
    public function classname()
    {
        return get_class($this);
    }
    public function edih_filepath()
    {
        return $this->filepath;
    }
    public function edih_filename()
    {
        return $this->filename;
    }
    public function edih_type()
    {
        return $this->type;
    }
    public function edih_version()
    {
        return $this->version;
    }
    public function edih_text()
    {
        return $this->text;
    }
    public function edih_length()
    {
        return $this->length;
    }
    public function edih_valid()
    {
        return $this->valid;
    }
    public function edih_isx12()
    {
        return $this->isx12;
    }
    public function edih_hasGS()
    {
        return $this->hasGS;
    }
    public function edih_hasST()
    {
        return $this->hasST;
    }
    public function edih_delimiters()
    {
        return $this->delimiters;
    }
    public function edih_segments()
    {
        return $this->segments;
    }
    public function edih_envelopes()
    {
        return $this->envelopes;
    }

    /**
     * message statements regarding object or from functions
     * formatted as html
     *
     * @return string
     */
    public function edih_message()
    {
        $str_html = '<p>' . PHP_EOL;
        if (count($this->message)) {
            foreach ($this->message as $msg) {
                $str_html .= text($msg) . '<br />' . PHP_EOL;
            }

            $str_html .= PHP_EOL . '</p>' . PHP_EOL;
        } else {
            $str_html = '';
        }

        return $str_html;
    }


    /**
     * Numeric type of x12 HC file associated with GS01 code
     *
     * @param string $gs01
     * @return string|bool
     */
    public function edih_gs_type($gs01)
    {
        $tpky = strtoupper($gs01);
        return ( isset($this->gstype_ar[$tpky]) ) ? $this->gstype_ar[$tpky] : false;
    }

    /**
     * Use PHP FileInfo to check mime type and then scan for unwanted characters
     * check for Non-basic ASCII character and <%, <asp:, <?, ${, #!, <scr (any other evil script indicators?)
     * basically allows A-Z a-z 0-9 !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~ and newline carriage_return
     * This function accepts the following mime-type:  text/plain; charset=us-ascii
     *
     * The return string can be 'ovigs' ov - valid, igs - ISA GS ST
     *
     * @param string $filetext   the file contents
     * @return string            zero length on failure
     */
    public function edih_x12_scan($filetext)
    {
        $hasval = '';
        $ftxt = ( $filetext && is_string($filetext) ) ? trim($filetext) : $filetext;
        // possibly $ftxt = trim($filetext, "\x00..\x1F") to remove ASCII control characters
        // remove newlines
        if (strpos($ftxt, PHP_EOL)) {
            $ftxt = str_replace(PHP_EOL, '', $ftxt);
        }

        $flen = ( $ftxt && is_string($ftxt) ) ? strlen($ftxt) : 0;
        if (!$flen) {
            $this->message[] = 'edih_x12_scan: zero length or invalid file text';
            return $hasval;
        }

        $de = '';
        $dt = '';
        // use finfo php class
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME);
            $mimeinfo = $finfo->buffer($ftxt);
            if (strncmp($mimeinfo, 'text/plain; charset=us-ascii', 28) !== 0) {
                $this->message[] = 'edih_x12_scan: ' . text($this->filename) . ' : invalid mime info: <br />' . text($mimeinfo);
                //
                return $hasval;
            }
        }

        //
        if (preg_match('/[^\x20-\x7E\x0A\x0D]|(<\?)|(<%)|(<asp)|(#!)|(\$\{)|(<scr)|(script:)/is', $ftxt, $matches, PREG_OFFSET_CAPTURE)) {
            //
            $this->message[] = 'edih_x12_scan: suspect characters in file ' . text($this->filename) . '<br />' .
            ' character: ' . text($matches[0][0]) . '  position: ' . text($matches[0][1]);
            //
            return $hasval;
        }

        $hasval = 'ov'; // valid
        // check for required segments ISA GS ST; assume segment terminator is last character
        if (substr($ftxt, 0, 3) === 'ISA') {
            $hasval = 'ovi';
            $de = substr($ftxt, 3, 1);
            $dt = substr($ftxt, -1);
            if (strpos($ftxt, $dt . 'GS' . $de, 0)) {
                $hasval = 'ovig';
            }

            if (strpos($ftxt, $dt . 'ST' . $de, 0)) {
                $hasval = 'ovigs';
            }
        }

        return $hasval;
    }

    /**
     * read the GS segments in file contents to determine x12 type, or, if the
     * object was created with a file path and envelopes, from the GS envelope array
     *
     * @param string   $file_text    optional contents of an x12 file
     * @return string                the x12 type, e.g. 837, 835, 277, 999, etc.
     */
    public function edih_x12_type($file_text = '')
    {
        $tpstr = '';
        $tp_tmp = array();
        $f_text = '';
        $delims = array();
        $delimarg = '';
        $dt = ( isset($this->delimiters['t']) ) ? $this->delimiters['t'] : '';
        $de = ( isset($this->delimiters['e']) ) ? $this->delimiters['e'] : '';
        //
        if ($file_text) {
            // For when '_x12_' function is called with file contents as argument
            if (!$this->constructing) {
                $vars = $this->edih_file_text($file_text, false, true, false);
                $f_text = $file_text;
                $dt = ( isset($vars['delimiters']['t']) ) ? $vars['delimiters']['t'] : '';
                $de = ( isset($vars['delimiters']['e']) ) ? $vars['delimiters']['e'] : '';
            } elseif ($this->text) {
                // called in initial construction, delimiters already created if x12 file
                $f_text =& $this->text;
                if (!$dt) {
                    $this->message[] = 'edih_x12_type: not x12 file';
                    return $tpstr;
                }
            } else {
                // called after file scan, but no segment array exists
                $f_text =& $file_text;
                if (!$dt) {
                    $delims = $this->edih_x12_delimiters(substr($f_text, 0, 126));
                    $dt = ( isset($delims['t']) ) ? $delims['t'] : '';
                    $de = ( isset($delims['e']) ) ? $delims['e'] : '';
                }
            }

            if (!$f_text) {
                $this->message[] = 'edih_x12_type: failed scan of file content';
                return $tpstr;
            }
        } elseif (isset($this->envelopes['GS'])) {
            // No argument, so if envelopes exist, take values from there
            foreach ($this->envelopes['GS'] as $gs) {
                $tp_tmp[] = $gs['type'];
            }
        } elseif (count($this->segments)) {
            // No argument and no envelopes, so scan segments
            if (!$de) {
                $de = substr(reset($this->segments), 3, 1);
            }

            foreach ($this->segments as $seg) {
                if (strncmp($seg, 'GS' . $de, 3) == 0) {
                    $gs_ar = explode($de, $seg);
                    if (array_key_exists($gs_ar[1], $this->gstype_ar)) {
                        //$tp_tmp[] = $this->gstype_ar[$gs_ar[1]];
                        $tp_tmp[] = $gs_ar[1];
                    } else {
                        $tp_tmp[] = $gs_ar[1];
                        $this->message[] = 'edih_x12_type: unknown x12 type ' . text($gs_ar[1]);
                    }
                }
            }
        } else {
            $this->message[] = 'edih_x12_type: no content to determine x12 type';
            return $tpstr;
        }

        // $f_text has content only if file contents supplied or in text property
        if ($f_text) {
            // use regular expression instead of strpos($f_text, $dt.'GS'.$de)
            $pcrepattern = '/GS\\' . $de . '(?:HB|HS|HR|HI|HN|HP|FA|HC)\\' . $de . '/';
            $pr = preg_match_all($pcrepattern, $f_text, $matches, PREG_OFFSET_CAPTURE);
            //
            if ($pr && count($matches)) {
                foreach ($matches as $m) {
                    //$gspos1 = $m[0][1];
                    $gs_ar1 = explode($de, $m[0][0]);
                    if (array_key_exists($gs_ar1[1], $this->gstype_ar)) {
                        //$tp_tmp[] = $this->gstype_ar[$gs_ar1[1]];
                        $tp_tmp[] = $gs_ar1[1];
                    } else {
                        $tp_tmp[] = $gs_ar1[1];
                        $this->message[] = 'edih_x12_type: unknown x12 type ' . text($gs_ar1[1]);
                    }
                }
            } else {
                $this->message[] = 'edih_x12_type: did not find GS segment ';
            }

            /* **** this replaced by preg_match_all() above ******
            }
            // scan GS segments
            $gs_str = $dt.'GS'.$de;
            $gs_pos = 1;
            $gse_pos = 2;
            while ($gs_pos) {
                $gs_pos = strpos($f_text, $gs_str, $gs_pos);
                if ($gs_pos) {
                    $gsterm = strpos($f_text, $dt, $gs_pos+1);
                    $gsseg = trim(substr($f_text, $gs_pos+1, $gsterm-$gs_pos-1));
                    //$gs_ar = explode($de, substr($f_text, $gs_pos+1, $gsterm-$gs_pos-1) );
                    $this->message[] = 'edih_x12_type: '.$gsseg.PHP_EOL;
                    $gs_ar = explode($de, $gsseg);
                    if ( array_key_exists($gs_ar[1], $this->gstype_ar) ) {
                        $tp_tmp[] = $this->gstype_ar[$gs_ar[1]];
                    } else {
                        $tp_tmp[] = $gs_ar[1];
                        $this->message[] = 'edih_x12_type: unknown x12 type '.$gs_ar[1];
                    }
                    $gs_pos = $gsterm + 1;
                }
            }
            ******************* */
        }

        // x12 type information collected
        if (count($tp_tmp)) {
            $tp3 = array_values(array_unique($tp_tmp));
            // mixed should not happen -- concatenated ISA envelopes of different types?
            $tpstr = ( count($tp3) > 1 ) ? 'mixed|' . implode("|", $tp3) : $tp3[0];
            //$this->message[] = 'edih_x12_type: ' . $tpstr;
        } else {
            $this->message[] = 'edih_x12_type: error in identifying type ';
            return false;
        }

        return $tpstr;
    }


    /**
     * Extract x12 delimiters from the ISA segment
     *
     * There are obviously easier/faster ways of doing this, but we go character by character.
     * The value returned is empty on error, otherwise:
     * <pre>
     * array('t'=>segment terminator, 'e'=>element delimiter,
     *       's'=>sub-element delimiter, 'r'=>repetition delimiter)
     * </pre>
     *
     * @param string $isa_str110       first n>=106 characters of x12 file
     * @return array                   array or empty on error
     */
    public function edih_x12_delimiters($isa_str110 = '')
    {
        //
        $delim_ar = array();
        if (!$isa_str110 && $this->text) {
            $isa_str = substr($this->text, 0, 106);
        } else {
            $isa_str = trim($isa_str110);
        }

        $isalen = strlen($isa_str);
        if ($isalen >= 106) {
            if (substr($isa_str, 0, 3) != 'ISA') {
                // not the starting characters
                $this->message[] = 'edih_x12_delimiters: text does not begin with ISA';
                return $delim_ar;
            }

            /* Extract delimiters using the prescribed positions.
             *  -- problem is possibly mangled files
             * $t_ar['e'] = substr($isa_str, 3, 1);
             * $t_ar['r'] = substr($isa_str, 82, 1);
             * $t_ar['s'] = substr($isa_str, 104, 1);
             * $t_ar['t'] = substr($isa_str, 105, 1);
             */
        } else {
            $this->message[] = 'edih_x12_delimiters: ISA string too short' . PHP_EOL;
            return $delim_ar;
        }

        $s = '';
        $delim_ct = 0;
        $de = substr($isa_str, 3, 1);  // ISA*
        for ($i = 0; $i < $isalen; $i++) {
            if ($isa_str[$i] == $de) {
                // element count incremented at end of loop
                // repetition separator in version 5010
                if ($delim_ct == 11) {
                    $dr = substr($s, 1, 1);
                }

                if ($delim_ct == 12) {
                    if (strpos($s, '501') === false) {
                        $dr = '';
                    }
                }

                //
                if ($delim_ct == 15) {
                    $ds = substr($isa_str, $i + 1, 1);
                    $dt = substr($isa_str, $i + 2, 1);
                }

                if ($delim_ct == 16) {
                    break;
                }

                $s = $isa_str[$i];   // $elem_delim;
                $delim_ct++;
            } else {
                $s .= $isa_str[$i];
            }
        }

        // there are 16 elements in ISA segment
        if ($delim_ct < 16) {
            // too few elements -- probably did not get delimiters
            $this->message[] = "edih_x12_delimiters: too few elements in ISA string";
            return $delim_ar;
        }

        //
        $delim_ar = array('t' => $dt, 'e' => $de, 's' => $ds, 'r' => $dr);
        //
        return $delim_ar;
    }

    /**
     * Create a multidimensional array of edi envelope info from object segments.
     * Useful for slicing and dicing.  The ['ST'][$stky]['trace'] value is used only for 835
     * or 999 type files and the ['ST'][$stky]['acct'][i] array will have multiple values
     * likely only for 835, 271, and 277 types, because response from a payer will have
     * multiple transactions in the ST-SE envelope while OpenEMR probably will place each
     * transaction in its own ST-SE envelope for 270 and 837 types.
     *
     * The ['start'] and ['count'] values are for use in php function array_slice()
     * The numeric keys of the segments array begin at 1 and the ['start'] value is one less
     * than the actual key because array_slice() offset is zero-based.
     *
     * <pre>
     * ['ISA'][$icn]=>['start']['count']['sender']['receiver']['icn']['gscount']['date']
     * ['GS'][$gs_ct]=>['start']['count']['gsn']['icn']['sender']['date']['stcount']['type']
     * ['ST'][$stky]=>['start']['count']['stn']['gsn']['icn']['type']['trace']['acct']
     *   ['ST'][$stky]['acct'][i]=>CLM01
     *   ['ST'][$stky]['bht03'][i]=>BHT03
     * </pre>
     *
     * @return array                array as shown above or empty on error
     */
    public function edih_x12_envelopes($file_text = '')
    {
        // produce an array of envelopes and positions
        $env_ar = array();
        $de = '';
        if ($file_text) {
            // presume need for file scan and delimiters
            $vars = $this->edih_file_text($file_text, false, true, true);
            $segment_ar = (isset($vars['segments']) ) ? $vars['segments'] : array();
            $de = (isset($vars['delimiters']) ) ? $vars['delimiters']['e'] : '';
            //$segment_ar = $this->edih_x12_segments($file_text);
            if (empty($segment_ar) || !$de) {
                $this->message[] = 'edih_x12_envelopes: invalid file text';
                return $env_ar;
            }
        } elseif (count($this->segments)) {
            $segment_ar = $this->segments;
            if (isset($this->delimiters['e'])) {
                $de = $this->delimiters['e'];
            } else {
                $de = (substr(reset($segment_ar), 0, 3) == 'ISA') ? substr(reset($segment_ar), 3, 1) : '';
            }
        } else {
            $this->message[] = 'edih_x12_envelopes: no text or segments';
            return $env_ar;
        }

        if (!$de) {
            $this->message[] = 'edih_x12_envelopes: invalid delimiters';
            return $env_ar;
        }

        //
        // get the segment array bounds
        $seg_first = (reset($segment_ar) !== false) ? key($segment_ar) : '1';
        $seg_last = (end($segment_ar) !== false) ? key($segment_ar) : count($segment_ar) + $seg_first;
        if (reset($segment_ar) === false) {
            $this->message[] = 'edi_x12_envelopes: reset() error in segment array';
            return $env_ar;
        } else {
            $seg_ct = $seg_last + 1;
        }

        // variables
        $seg_txt = '';
        $sn = '';
        $st_type = '';
        $st_ct = 0;
        $isa_ct = 0;
        $iea_ct = 0;
        $gs_st_ct = 0;
        $trnset_seg_ct = 0;
        $st_segs_ct = 0;
        $isa_segs_ct = 0;
        $chk_trn = false;
        $trncd = '2';
        //$id278 = false;
        $ta1_icn = '';
        $seg_ar = array();
        // the segment IDs we look for
        $chk_segs = array('ISA', 'GS' . $de, 'TA1', 'ST' . $de, 'BHT', 'HL' . $de, 'TRN', 'CLP', 'CLM', 'SE' . $de, 'GE' . $de, 'IEA');
        //
        for ($i = $seg_first; $i < $seg_ct; $i++) {
            // counters
            $isa_segs_ct++;
            $st_segs_ct++;
            //
            $seg_text = $segment_ar[$i];
            $sn = substr($seg_text, 0, 4);
            // skip over segments that are not envelope boundaries or identifiers
            if (!in_array(substr($sn, 0, 3), $chk_segs)) {
                continue;
            }

            // create the structure array
            if (strncmp($sn, 'ISA' . $de, 4) == 0) {
                $seg_ar = explode($de, $seg_text);
                $icn = trim($seg_ar[13]);
                //
                $env_ar['ISA'][$icn]['start'] = strval($i - 1);
                $env_ar['ISA'][$icn]['sender'] = trim($seg_ar[6]);
                $env_ar['ISA'][$icn]['receiver'] = trim($seg_ar[8]);
                $env_ar['ISA'][$icn]['icn'] = $icn;
                $env_ar['ISA'][$icn]['date'] = trim($seg_ar[9]);    // YYMMDD
                $env_ar['ISA'][$icn]['version'] = trim($seg_ar[12]);
                //
                $isa_segs_ct = 1;
                $isa_ct++;
                continue;
            }

            //
            if (strncmp($sn, 'GS' . $de, 3) == 0) {
                $seg_ar = explode($de, $seg_text);
                $gs_start = strval($i - 1);
                $gsn = $seg_ar[6];
                // GS06 could be used to id 997/999 response, if truly unique
                // cannot index on $gsn due to concatenated ISA envelopes and non-unique
                $gs_ct = isset($env_ar['GS']) ? count($env_ar['GS']) : 0;
                //
                $env_ar['GS'][$gs_ct]['start'] = $gs_start;
                $env_ar['GS'][$gs_ct]['gsn'] = $gsn;
                $env_ar['GS'][$gs_ct]['icn'] = $icn;
                $env_ar['GS'][$gs_ct]['sender'] = trim($seg_ar[2]);
                $env_ar['GS'][$gs_ct]['date'] = trim($seg_ar[4]);
                $env_ar['GS'][$gs_ct]['srcid'] = '';
                // to verify type of edi transaction
                if (array_key_exists($seg_ar[1], $this->gstype_ar)) {
                    $gs_fid = $this->gstype_ar[$seg_ar[1]];
                    $env_ar['GS'][$gs_ct]['type'] = $seg_ar[1];
                } else {
                    $gs_fid = 'NA';
                    $env_ar['GS'][$gs_ct]['type'] = 'NA';
                    $this->message[] = 'edih_x12_envelopes: Unknown GS type ' . text($seg_ar[1]);
                }

                continue;
            }

            // expect 999 TA1 before ST
            if (strncmp($sn, 'TA1' . $de, 4) == 0) {
                $seg_ar = explode($de, $seg_text);
                if (isset($seg_ar[1]) && $seg_ar[1]) {
                    $ta1_icn = $seg_ar[1];
                } else {
                    $this->message[] = 'edih_x12_envelopes: Error in TA1 segment response ICN';
                }

                //TA1*ISA13ICN*ISA09DATE*ISA10TIME*ACKCode*NoteCode~
                continue;
            }

            //
            if (strncmp($sn, 'ST' . $de, 3) == 0) {
                $seg_ar = explode($de, $seg_text);
                $stn = $seg_ar[2];
                $st_type = $seg_ar[1];
                $st_start = strval($i);
                $st_segs_ct = 1;
                $st_ct = isset($env_ar['ST']) ? count($env_ar['ST']) : 0;
                //
                $env_ar['ST'][$st_ct]['start'] = strval($i - 1);
                $env_ar['ST'][$st_ct]['count'] = '';
                $env_ar['ST'][$st_ct]['stn'] = $seg_ar[2];
                $env_ar['ST'][$st_ct]['gsn'] = $gsn;
                $env_ar['ST'][$st_ct]['icn'] = $icn;
                $env_ar['ST'][$st_ct]['type'] = $seg_ar[1];
                $env_ar['ST'][$st_ct]['trace'] = '0';
                $env_ar['ST'][$st_ct]['acct'] = array();
                $env_ar['ST'][$st_ct]['bht03'] = array();
                // GS file id FA can be 999 or 997
                if ($gs_fid != $st_type && strpos($st_type, '99') === false) {
                    $this->message[] = "edih_x12_envelopes: ISA " . text($icn) . ", GS " . text($gsn . " " . $gs_fid) . " ST " . text($stn . " " . $st_type) . " type mismatch" . PHP_EOL;
                }

                //
                continue;
            }

            //
            if (strpos('|270|271|276|277|278', $st_type)) {
                //
                if (strncmp($sn, 'BHT' . $de, 4) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (isset($seg_ar[2])) {
                        $trncd = ($seg_ar[2] == '13') ? '1' : '2';
                        // 13 = request, otherwise assume response
                    } else {
                        $this->message[] = 'edih_x12_envelopes: missing BHT02 type element';
                    }

                    if (isset($seg_ar[3]) && $seg_ar[3]) {
                        $env_ar['ST'][$st_ct]['bht03'][] = $seg_ar[3];
                    } else {
                        $this->message[] = 'edih_x12_envelopes: missing BHT03 identifier';
                    }
                }

                if (strncmp($sn, 'HL' . $de, 3) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (isset($seg_ar[3]) && $seg_ar[3]) {
                        $chk_trn = ( strpos('|22|23|PT', $seg_ar[3]) ) ? true : false;
                    } else {
                        $this->message[] = 'edih_x12_envelopes: missing HL03 level element';
                    }

                    continue;
                }

                if ($chk_trn && strncmp($sn, 'TRN' . $de, 4) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (isset($seg_ar[1]) && $seg_ar[1] == $trncd) {
                        $env_ar['ST'][$st_ct]['acct'][] = (isset($seg_ar[2])) ? $seg_ar[2] : '';
                        $chk_trn = false;
                    } else {
                        $this->message[] = 'edih_x12_envelopes: missing TRN02 type identifier element';
                    }

                    continue;
                }
            }

            //
            if ($st_type == '835') {
                if (strncmp($sn, 'TRN' . $de, 4) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (!isset($seg_ar[2]) || !isset($seg_ar[3])) {
                        $this->message[] = 'error in 835 TRN segment ' . text($seg_text);
                    }

                    $env_ar['ST'][$st_ct]['trace'] = (isset($seg_ar[2])) ? $seg_ar[2] : "";
                    // to match OpenEMR billing parse file name
                    if (isset($seg_ar[4])) {
                        $env_ar['GS'][$gs_ct]['srcid'] = $seg_ar[4];
                    } else {
                        $env_ar['GS'][$gs_ct]['srcid'] = (isset($seg_ar[3])) ? $seg_ar[3] : "";
                    }

                    //
                    continue;
                }

                if (strncmp($sn, 'CLP' . $de, 4) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (isset($seg_ar[1])) {
                        $env_ar['ST'][$st_ct]['acct'][] = $seg_ar[1];
                    } else {
                        $this->message[] = 'error in 835 CLP segment ' . text($seg_text);
                    }

                    continue;
                }
            }

            //
            if ($st_type == '837') {
                if (strncmp($sn, 'BHT' . $de, 4) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (isset($seg_ar[3]) && $seg_ar[3]) {
                        $env_ar['ST'][$st_ct]['bht'][] = $seg_ar[3];
                    } else {
                        $this->message[] = 'edih_x12_envelopes: missing BHT03 identifier';
                    }
                }

                //
                if (strncmp($sn, 'CLM' . $de, 4) == 0) {
                    $seg_ar = explode($de, $seg_text);
                    if (isset($seg_ar[1])) {
                        $env_ar['ST'][$st_ct]['acct'][] = $seg_ar[1];
                    } else {
                        $this->message[] = 'error in 837 CLM segment ' . text($seg_text);
                    }

                    continue;
                }
            }

            //
            if (strncmp($sn, 'SE' . $de, 3) == 0) {
                // make sure no lingering toggle
                $id278 = false;
                $chk_trn = false;
                //
                $seg_ar = explode($de, $seg_text);
                $se_num = $seg_ar[2];
                $env_ar['ST'][$st_ct]['count'] = strval($seg_ar[1]);
                // 999 case: expect TA1 before ST, so capture batch icn here
                if ($st_type == '999' || $st_type == '997') {
                    if (isset($ta1_icn) && strlen($ta1_icn)) {
                        $env_ar['ST'][$st_ct]['trace'] = $ta1_icn;
                        $ta1_icn = '';
                    }
                }

                // errors
                if ($se_num != $stn) {
                    $this->message[] = 'edih_x12_envelopes: ST-SE number mismatch ' . text($stn) . ' ' . text($se_num) . ' in ISA ' . text($icn) . PHP_EOL;
                }

                if (intval($seg_ar[1]) != $st_segs_ct) {
                    $this->message[] = 'edih_x12_envelopes: ST-SE segment count mismatch ' . text($st_segs_ct) . ' ' . text($seg_ar[1]) . ' in ISA ' . text($icn) . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp($sn, 'GE' . $de, 3) == 0) {
                $seg_ar = explode($de, $seg_text);
                $env_ar['GS'][$gs_ct]['count'] = $i - $gs_start - 1;
                $env_ar['GS'][$gs_ct]['stcount'] = trim($seg_ar[1]);  // ST count
                $gs_st_ct += $seg_ar[1];
                //
                if ($seg_ar[2] != $env_ar['GS'][$gs_ct]['gsn']) {
                    $this->message[] = 'edih_x12_envelopes: GS-GE identifier mismatch' . PHP_EOL;
                }

                if ($gs_ct === 0 && ($seg_ar[1] != count($env_ar['ST']))) {
                    $this->message[] = 'edih_x12_envelopes: GS count of ST  mismatch' . PHP_EOL;
                } elseif ($gs_st_ct != count($env_ar['ST'])) {
                    $this->message[] = 'edih_x12_envelopes: GS count of ST  mismatch' . PHP_EOL;
                }

                continue;
            }

            //
            if (strncmp($sn, 'IEA' . $de, 4) == 0) {
                $seg_ar = explode($de, $seg_text);
                $env_ar['ISA'][$icn]['count'] = $isa_segs_ct;
                $env_ar['ISA'][$icn]['gscount'] = $seg_ar[1];
                $iea_ct++;
                //
                if (count($env_ar['GS']) != $seg_ar[1]) {
                    $this->message[] = 'edih_x12_envelopes: GS count mismatch in ISA ' . text($icn) . PHP_EOL;
                    $gsct = count($env_ar['GS']);
                    $this->message[] = 'GS group count: ' . text($gsct) . ' IEA01: ' . text($seg_ar[1]) . ' segment: ' . text($seg_text);
                }

                if ($env_ar['ISA'][$icn]['icn'] !== $seg_ar[2]) {
                    $this->message[] = 'edih_x12_envelopes: ISA-IEA identifier mismatch ISA ' . text($icn) . ' IEA ' . text($seg_ar[2]);
                }

                if ($iea_ct == $isa_ct) {
                    $trnset_seg_ct += $isa_segs_ct;
                    //if ( $i+1 != $trnset_seg_ct ) {
                    if ($i != $trnset_seg_ct) {
                        $this->message[] = 'edih_x12_envelopes: IEA segment count error ' . text($i) . ' : ' . text($trnset_seg_ct);
                    }
                } else {
                    $this->message[] = 'edih_x12_envelopes: ISA-IEA count mismatch ISA ' . text($isa_ct) . ' IEA ' . text($iea_ct);
                }

                continue;
            }
        }

        //
        return $env_ar;
    }

    /**
     * Parse x12 file contents into array of segments.
     *
     * @uses edih_x12_delimiters()
     * @uses edih_x12_scan()
     *
     * @param string      $file_text
     * @return array      array['i'] = segment, or empty on error
     */
    public function edih_x12_segments($file_text = '')
    {
        $ar_seg = array();
        // do verifications
        if ($file_text) {
            if (!$this->constructing) {
                // need to validate file
                $vars = $this->edih_file_text($file_text, false, true, false);
                $f_str = $file_text;
                $dt = ( isset($vars['delimiters']['t']) ) ? $vars['delimiters']['t'] : '';
            } else {
                $f_str = $file_text;
                if (isset($this->delimiters['t'])) {
                    $dt = $this->delimiters['t'];
                } else {
                    $delims = $this->edih_x12_delimiters(substr($f_str, 0, 126));
                    $dt = ( isset($delims['t']) ) ? $delims['t'] : '';
                }
            }
        } elseif ($this->text) {
            $f_str = $this->text;
            if (isset($this->delimiters['t'])) {
                $dt = $this->delimiters['t'];
            } else {
                $delims = $this->edih_x12_delimiters(substr($f_str, 0, 126));
                $dt = ( isset($delims['t']) ) ? $delims['t'] : '';
            }
        } else {
            $this->message[] = 'edih_x12_segments: no file text';
            return $ar_seg;
        }

        // did we get the segment terminator?
        if (!$dt) {
            $this->message[] = 'edih_x12_segments: invalid delimiters';
            return $ar_seg;
        }

        // OK, now initialize variables
        $seg_pos = 0;                         // position where segment begins
        $seg_end = 0;
        $seg_ct = 0;
        $moresegs = true;
        // could test this against simple $segments = explode($dt, $f_str)
        while ($moresegs) {
            // extract each segment from the file text
            $seg_end = strpos($f_str, $dt, $seg_pos);
            $seg_text = substr($f_str, $seg_pos, $seg_end - $seg_pos);
            $seg_pos = $seg_end + 1;
            $moresegs = strpos($f_str, $dt, $seg_pos);
            $seg_ct++;
            // we trim in case there are line or carriage returns
            $ar_seg[$seg_ct] = trim($seg_text);
        }

        //
        return $ar_seg;
    }


    /**
     * extract the segments representing a transaction for CLM01 pt-encounter number
     * note: there may be more than one in a file, all matching are returned
     * 27x transactions will have unique BHT03 that could be used as the claimid argument
     *
     * return_array[i] => transaction segments array
     * return_array[i][j] => particular segment string
     *
     * @param string $clm01      837 CLM01 or BHT03 from 277
     * @param string $stn        ST number  -- optional, limit search to that ST-SE envelope
     * @param string $filetext   optional file contents
     * @return array        multidimensional array of segments or empty on failure
     */
    public function edih_x12_transaction($clm01, $stn = '', $filetext = '')
    {
        //
        $ret_ar = array();
        //
        if (!$clm01) {
            $this->message[] = 'edih_x12_transaction: invalid argument';
            return $ret_ar;
        }

        //
        $de = '';
        $tp = '';
        $seg_ar = array();
        $env_ar = array();
        // select the data to search
        if ($filetext && !$this->constructing) {
            $vars = $this->edih_file_text($filetext, true, true, true);
            $tp = ( isset($vars['type']) ) ? $vars['type'] : $tp;
            $de = ( isset($vars['delimiters']['e']) ) ? $vars['delimiters']['e'] : $de;
            $seg_ar = ( isset($vars['segments']) ) ? $vars['segments'] : $seg_ar;
            //$env_ar = $vars['envelopes'];  // probably faster without envelopes in this case
        } elseif (count($this->segments)) {
            // default created object
            $seg_ar = $this->segments;
            if (count($this->delimiters)) {
                $de = $this->delimiters['e'];
            } else {
                $de = (substr(reset($segment_ar), 0, 3) == 'ISA') ? substr(reset($segment_ar), 3, 1) : '';
            }

            $tp = ($this->type) ? $this->type : $this->edih_x12_type();
            $env_ar = ( isset($this->envelopes['ST']) ) ? $this->envelopes : $env_ar;
        } elseif ($this->text) {
            // object with file text, but no processing
            $tp = $this->edih_x12_type();
            $seg_ar = ( $tp ) ? $this->edih_x12_segments() : $seg_ar;
            if (count($seg_ar)) {
                $de = substr(reset($seg_ar), 3, 1);
            }
        } else {
            $this->message[] = 'edih_x12_transaction: invalid search data';
            return $ret_ar;
        }

        if (!count($seg_ar)) {
            $this->message[] = 'edih_x12_transaction: invalid segments';
            return $ret_ar;
        }

        if (!$de) {
            $this->message[] = 'edih_x12_transaction: invalid delimiters';
            return $ret_ar;
        }

        //array('HB'=>'271', 'HS'=>'270', 'HR'=>'276', 'HI'=>'278',
        //      'HN'=>'277', 'HP'=>'835', 'FA'=>'999', 'HC'=>'837');
        if (substr($tp, 0, 5) == 'mixed') {
            $tp = substr($tp, -2);
        }

        if (!strpos('|HB|271|HS|270|HR|276|HI|278|HN|277|HP|835|FA|999|HC|837', $tp)) {
            $this->message[] = 'edih_x12_transaction: wrong edi type for transaction search ' . text($tp);
            return $ret_ar;
        }

        $idx = 0;
        $is_found = false;
        $slice = array();
        $srch_ar = array();
        $sl_idx = 0;
        // there may be several in same ST envelope with the same $clm01, esp. 835
        // we will get each set of relevant transaction segments in foreach() below
        if (count($env_ar)) {
            foreach ($env_ar['ST'] as $st) {
                if (strlen($stn) && $st['stn'] != $stn) {
                    continue;
                }

                if (isset($st['acct']) && count($st['acct'])) {
                    $ky = array_search($clm01, $st['acct']);
                    if ($ky !== false) {
                        $srch_ar[$idx]['array'] = array_slice($seg_ar, $st['start'], $st['count'], true);
                        $srch_ar[$idx]['start'] = $st['start'];
                        $srch_ar[$idx]['type'] = $st['type'];
                        $idx++;
                    }
                }
            }
        }

        // if not identified in envelope search, use segments
        if (!count($srch_ar)) {
            $srch_ar[0]['array'] = $seg_ar;
            $srch_ar[0]['start'] = 0;   // with array_slice() the index is absolute zero base
            $srch_ar[0]['type'] = $tp;
        }

        // verify we have type
        if ($srch_ar[0]['type'] == 'NA' || !$srch_ar[0]['type']) {
            $this->edih_message('edih_x12_transaction(): invalid file type ' . text($srch_ar[0]['type']));
            return $ret_ar;
        }

        // segments we check
        $test_id = array('TRN','CLM','CLP','ST' . $de,'BHT','REF','LX' . $de,'PLB','SE' . $de);
        //
        foreach ($srch_ar as $srch) {
            $idx = $srch['start'] - 1;      // align index to segments array offset
            $type = (string)$srch['type'];
            $is_found = false;
            $idval = '';
            $idlen = 1;
            //
            foreach ($srch['array'] as $key => $seg) {
                $idx++;
                //
                $test_str = substr($seg, 0, 3);
                if (!in_array($test_str, $test_id, true)) {
                    continue;
                }

                //
                // the opening ST segment should be in each search array,
                // so type and search values can be determined here.
                if (strncmp($seg, 'ST' . $de, 3) == 0) {
                    $stseg = explode($de, $seg);
                    $type = ($type) ? $type : (string)$stseg[1];
                    //
                    $idval = ( strpos('|HN|277|HB|271', $type) ) ? 'TRN' . $de . '2' . $de . $clm01 : '';
                    $idval = ( strpos('|HR|276|HS|270', $type) ) ? 'TRN' . $de . '1' . $de . $clm01 : $idval;
                    $idval = ( strpos('|HI|278', $type) ) ? 'REF' . $de . 'EJ' . $de . $clm01 : $idval;
                    $idval = ( strpos('|HC|837', $type) ) ? 'CLM' . $de . $clm01 . $de : $idval;
                    $idval = ( strpos('|HP|835', $type) ) ? 'CLP' . $de . $clm01 . $de : $idval;
                    $idlen = strlen($idval);
                    //
                    continue;
                }

                //array('HB'=>'271', 'HS'=>'270', 'HR'=>'276', 'HI'=>'278',
                //      'HN'=>'277', 'HP'=>'835', 'FA'=>'999', 'HC'=>'837');
                // these types use the BHT segment to begin transactions
                if (strpos('|HI|278|HN|277|HR|276|HB|271|HS|270|HC|837', $type)) {
                    //
                    if (strncmp($seg, 'BHT' . $de, 4) === 0) {
                        $bht_seg = explode($de, $seg);
                        $bht_pos = $idx;
                        //$bht_pos = $key;
                        if ($is_found && isset($slice[$sl_idx]['start'])) {
                            $slice[$sl_idx]['count'] = $idx - $slice[$sl_idx]['start'];
                            //$slice[$sl_idx]['count'] = $key - $slice[$sl_idx]['start'];
                            $is_found = false;
                            $sl_idx++;
                        } elseif (strcmp($clm01, $bht_seg[3]) === 0) {
                            // matched by BHT03 identifier
                            $is_found = true;
                            $slice[$sl_idx]['start'] = $bht_pos;
                        }

                        continue;
                    }

                    //
                    if (strncmp($seg, $idval, $idlen) === 0) {
                        // matched by clm01 identifier (idval)
                        $is_found = true;
                        $slice[$sl_idx]['start'] = $bht_pos;
                        continue;
                    }
                }

                //
                if ($type == 'HP' || $type == '835') {
                    if (strncmp($seg, 'CLP' . $de, 4) === 0) {
                        if (strncmp($seg, $idval, $idlen) === 0) {
                            if ($is_found && isset($slice[$sl_idx]['start'])) {
                                $slice[$sl_idx]['count'] = $idx - $slice[$sl_idx]['start'];
                                //$slice[$sl_idx]['count'] = $key - $slice[$sl_idx]['start'];
                                $sl_idx++;
                            }

                            $is_found = true;
                            $slice[$sl_idx]['start'] = $idx;
                            //$slice[$sl_idx]['start'] = $key;
                        } else {
                            if ($is_found && isset($slice[$sl_idx]['start'])) {
                                $slice[$sl_idx]['count'] = $idx - $slice[$sl_idx]['start'];
                                //$slice[$sl_idx]['count'] = $key - $slice[$sl_idx]['start'];
                                $is_found = false;
                                $sl_idx++;
                            }
                        }

                        continue;
                    }

                    // LX segment is often used to group claim payment information
                    // we do not capture TS3 or TS2 segments in the transaction
                    if (strncmp($seg, 'LX' . $de, 3) === 0) {
                        if ($is_found && isset($slice[$sl_idx]['start'])) {
                            $slice[$sl_idx]['count'] = $idx - $slice[$sl_idx]['start'];
                            //$slice[$sl_idx]['count'] = $key - $slice[$sl_idx]['start'];
                            $is_found = false;
                            $sl_idx++;
                        }

                        continue;
                    }

                    // PLB segment is part of summary/trailer in 835
                    // not part of the preceeding transaction
                    if (strncmp($seg, 'PLB' . $de, 4) === 0) {
                        if ($is_found && isset($slice[$sl_idx]['start'])) {
                            $slice[$sl_idx]['count'] = $idx - $slice[$sl_idx]['start'];
                            //$slice[$sl_idx]['count'] = $key - $slice[$sl_idx]['start'];
                            $is_found = false;
                            $sl_idx++;
                        }

                        continue;
                    }
                }

                // SE will always mark end of transaction segments
                if (strncmp($seg, 'SE' . $de, 3) === 0) {
                    if ($is_found && isset($slice[$sl_idx]['start'])) {
                        $slice[$sl_idx]['count'] = $idx - $slice[$sl_idx]['start'];
                        //$slice[$sl_idx]['count'] = $key - $slice[$sl_idx]['start'];
                        $is_found = false;
                        $sl_idx++;
                    }
                }
            } // end foreach($srch['array'] as $seg)
        } // end foreach($srch_ar as $srch)
        //
        if (count($slice)) {
            foreach ($slice as $sl) {
                $ret_ar[] = array_slice($seg_ar, $sl['start'], $sl['count'], true);
            }
        }

        //
        return $ret_ar;
    }


    /**
     * get the segment(s) with a particular ID, such as CLP, NM1, etc.
     * return is array
     *   array[i] => matching segment string
     *
     * @param string    $segmentID such as NM1, CLP, STC, etc.
     * @param string    $srchStr  optional string contained in segment
     * @param array     $seg_array  optional supplied array of segments to search
     * @return array
     */
    public function edih_get_segment($segmentID, $srchStr = '', $seg_array = '')
    {
        //
        $ret_ar = array();
        $seg_ar = array();
        $segid = ( strlen($segmentID) ) ? trim($segmentID) : '';
        //
        $srch = ( strlen($srchStr) ) ? $srchStr : '';

        //
        if (!$segid) {
            $this->message[] = 'edih_get_segment(): missing segment ID';
            return $ret_ar;
        }

        //
        $de = ( isset($this->delimiters['e']) ) ? $this->delimiters['e'] : '';
        $dt = ( isset($this->delimiters['t']) ) ? $this->delimiters['t'] : '';
        //
        // segment array from edih_x12_transaction() is two dimension
        if (is_array($seg_array) && count($seg_array)) {
            if (isset($seg_array[0]) && is_array($seg_array[0])) {
                foreach ($seg_array as $ar) {
                    $seg_ar = array_merge($seg_ar, $ar);
                }
            } else {
                $seg_ar = $seg_array;
            }
        } elseif (count($this->segments)) {
            $seg_ar = $this->segments;
        } elseif ($this->text) {
            if (!$de) {
                $delims = $this->edih_x12_delimiters(substr($this->text, 0, 126));
                $dt = ( isset($delims['t']) ) ? $delims['t'] : '';
                $de = ( isset($delims['e']) ) ? $delims['e'] : '';
            }

            if (!$de || !$dt) {
                $this->message[] = 'edih_get_segment() : unable to get delimiters';
                return $ret_ar;
            }

            //
            $segsrch = ($segid == 'ISA') ? $segid . $de : $dt . $segid . $de;
            $seg_pos = 1;
            $see_pos = 2;
            while ($seg_pos) {
                $seg_pos = strpos($this->text, $segsrch, $seg_pos);
                $see_pos = strpos($this->text, $dt, $seg_pos + 1);
                if ($seg_pos) {
                    $segstr =  trim(substr($this->text, $seg_pos, $see_pos - $seg_pos), $dt);
                    if ($srch) {
                        if (strpos($segstr, $srch) !== false) {
                            $ret_ar[] = $segstr;
                        }
                    } else {
                        $ret_ar[] = $segstr;
                    }

                    $seg_pos = $see_pos + 1;
                }
            }
        }

        //
        if (count($seg_ar)) {
            $cmplen = strlen($segid . $de);
            foreach ($seg_ar as $key => $seg) {
                if (strncmp($seg, $segid . $de, $cmplen) === 0) {
                    if ($srch) {
                        if (strpos($seg, $srch) !== false) {
                            $ret_ar[$key] = $seg;
                        }
                    } else {
                        $ret_ar[$key] = $seg;
                    }
                }
            }
        } else {
            $this->message[] = 'edih_get_segment() : no segments or text content available';
        }

        //
        return $ret_ar;
    }


    /**
     * Get a slice of the segments array
     * Supply an array with one or more of the following keys and values:
     *
     *  ['trace'] => trace value from 835(TRN02) or 999(TA101) x12 type
     *  ['ISA13'] => ISA13
     *  ['GS06'] => GS06   (sconsider also 'ISA13')
     *  ['ST02'] => ST02   (condider also 'ISA13' and 'GS06')
     *  ['keys'] => true to preserve segment numbering from original file
     *
     * The return value will be an array of one or more segments.
     * The 'search' parameter results in one or more segments containing
     * the search string.  The
     * @param array    note: all element values except 'keys' are strings
     * @return array
     */
    function edih_x12_slice($arg_array, $file_text = '')
    {
        //
        $ret_ar = array();
        $f_str = '';
        // see what we have
        if (!is_array($arg_array) || !count($arg_array)) {
            // debug
            $this->message[] = 'edih_x12_slice() invalid array argument';
            return $ret_ar;
        }

        //
        if ($file_text) {
            // need to validate file edih_file_text($file_text, $type=false, $delimiters=false, $segments=false)
            $vars = $this->edih_file_text($file_text, true, true, false);
            if (is_array($vars) && count($vars)) {
                $f_str = $file_text;
                $dt = ( isset($vars['delimiters']['t']) ) ? $vars['delimiters']['t'] : '';
                $de = ( isset($vars['delimiters']['e']) ) ? $vars['delimiters']['e'] : '';
                $ft = ( isset($vars['type']) ) ?  $vars['type'] : '';
                //$seg_ar = ( isset($vars['segments']) ) ? $vars['segments'] : '';
                //$env_ar = $this->edih_x12_envelopes($f_str);
            } else {
                $this->message[] = 'edih_x12_slice() error processing file text';
                // debug
                //echo $this->edih_message().PHP_EOL;
                return $ret_ar;
            }
        } elseif (count($this->segments) && count($this->envelopes) && count($this->delimiters)) {
            $seg_ar = $this->segments;
            $env_ar = $this->envelopes;
            $dt = $this->delimiters['t'];
            $de = $this->delimiters['e'];
            $ft = $this->type;
        } else {
            $this->message[] = 'edih_x12_slice() object missing needed properties';
            // debug
            //echo $this->edih_message().PHP_EOL;
            return $ret_ar;
        }

        // initialize search variables
        $trace = '';
        $stn = '';
        $gsn = '';
        $icn = '';
        $prskeys = false;
        //
        foreach ($arg_array as $key => $val) {
            switch ((string)$key) {
                case 'trace':
                    $trace = (string)$val;
                    break;
                case 'ST02':
                    $stn = (string)$val;
                    break;
                case 'GS06':
                    $gsn = (string)$val;
                    break;
                case 'ISA13':
                    $icn = (string)$val;
                    break;
                case 'keys':
                    $prskeys = (bool)$val;
                    break;
            }
        }

        //
        if ($trace && strpos('|HP|FA', $ft) === false) {
            $this->message[] = 'edih_x12_slice() incorrect type [' . text($ft) . '] for trace';
            return $ret_ar;
        }

        //
        if ($f_str) {
            $srchstr = '';
            if ($icn) {
                $icnpos =  strpos($f_str, $de . $icn . $de);
                if ($icnpos === false) {
                    // $icn not found
                    $this->message[] = 'edih_x12_slice() did not find ISA13 ' . text($icn);
                    // debug
                    //echo $this->edih_message().PHP_EOL;
                    return $ret_ar;
                } elseif ($icnpos < 106) {
                    $isapos = 0;
                } else {
                    $isapos = strrpos($f_str, $dt . 'ISA' . $de, ($icnpos - strlen($f_str))) + 1;
                }

                $ieapos = strpos($f_str, $de . $icn . $dt, $isapos);
                $ieapos = strpos($f_str, $dt, $ieapos) + 1;
                $segidx = ($prskeys) ? substr_count($f_str, $dt, 0, $isapos + 2) + 1 : 0;
                //
                $srchstr = substr($f_str, $isapos, $ieapos - $isapos);
            }

            if ($gsn) {
                $srchstr = ($srchstr) ? $srchstr : $f_str;
                $gspos =  strpos($srchstr, $de . $gsn . $de);
                if ($gspos === false) {
                    // $gsn not found
                    $this->message[] = 'edih_x12_slice() did not find GS06 ' . text($gsn);
                    return $ret_ar;
                } else {
                    $gspos = strrpos(substr($srchstr, 0, $gspos), $dt) + 1;
                }

                $gepos = strpos($srchstr, $dt . 'GE' . $dt, $gspos);
                $gepos = strpos($srchstr, $dt, $gepos + 1) + 1;
                $segidx = ($prskeys) ? substr_count($f_str, $dt, 0, $gspos + 2) + 1 : 0;
                //
                $srchstr = substr($srchstr, $gspos, $gepos - $gspos);
            }

            if ($stn) {
                $srchstr = ($srchstr) ? $srchstr : $f_str;
                $sttp = $this->gstype_ar[$ft];
                $seg_st = $dt . 'ST' . $de . $sttp . $de . $stn   ;
                $seg_se = $dt . 'SE' . $de;
                // $segpos = 1;
                $stpos = strpos($srchstr, $seg_st);
                if ($stpos === false) {
                    // $stn not found
                    $this->message[] = 'edih_x12_slice() did not find ST02 ' . text($stn);
                    return $ret_ar;
                } else {
                    $stpos = $stpos + 1;
                }

                $sepos = strpos($srchstr, $seg_se, $stpos);
                $sepos = strpos($srchstr, $dt, $sepos + 1);
                $segidx = ($prskeys) ? substr_count($f_str, $dt, 0, $stpos + 2) + 1 : 0;
                //
                $srchstr = substr($srchstr, $stpos, $sepos - $stpos);
            }

            if ($trace) {
                //
                $trpos = strpos($f_str, $de . $trace);
                if ($trpos === false) {
                    // $icn not found
                    $this->message[] = 'edih_x12_slice() did not find trace ' . text($trace);
                    return $ret_ar;
                }

                $sttp = $this->gstype_ar[$ft];
                $seg_st = $dt . 'ST' . $de . $sttp . $de;
                $stpos = strrpos($f_str, $seg_st, ($trpos - strlen($f_str)));
                $sepos = strpos($f_str, $dt . 'SE' . $de, $stpos);
                $sepos = strpos($f_str, $dt, $sepos + 1);
                //
                $segidx =  ($prskeys) ? substr_count($f_str, $dt, 0, $st_pos + 2) + 1 : 0;
                $srchstr = substr($f_str, $stpos + 1, $sepos - $stpos);
            }

            // if we have a match, the $srchstr should have the desired segments
            if ($trace || $icn || $gsn || $stn) {
                if ($srchstr) {
                    $seg_ar = explode($dt, $srchstr);
                    // to keep segment numbers same as original file
                    foreach ($seg_ar as $seg) {
                        $ret_ar[$segidx] = $seg;
                        $segidx++;
                    }

                    return $ret_ar;
                } else {
                    $this->message[] = 'edih_x12_slice() error creating substring';
                    return $ret_ar;
                }
            }

        // file_text not supplied, check for object values
        } elseif (!($seg_ar && $env_ar && $dt && $de && $ft)) {
            // debug
            $this->message[] = 'edih_x12_slice() error is processing file';
            return $ret_ar;
        }

        // file_text not supplied, use object values
        if ($trace) {
            foreach ($env_ar['ST'] as $st) {
                if ($st['trace'] == $trace) {
                // have to add one to the count to capture the SE segment so html_str has data
                // when called from edih_835_payment_html function in edih_835_html.php 4-25-17 SMW
                    $ret_ar = array_slice($seg_ar, $st['start'], $st['count'] + 1, $prskeys);
                    break;
                }
            }
        } elseif ($icn && !($stn || $gsn)) {
            if (isset($env_ar['ISA'][$icn])) {
                $ret_ar = array_slice($seg_ar, $env_ar['ISA'][$icn]['start'], $env_ar['ISA'][$icn]['count'], $prskeys);
            }
        } elseif ($gsn && !$stn) {
            foreach ($env_ar['GS'] as $gs) {
                if ($icn) {
                    if (($gs['icn'] == $icn) && ($gs['gsn'] == $gsn)) {
                        $ret_ar = array_slice($seg_ar, $gs['start'], $gs['count'], $prskeys);
                        break;
                    }
                } else {
                    if ($gs['gsn'] == $gsn) {
                        $ret_ar = array_slice($seg_ar, $gs['start'], $gs['count'], $prskeys);
                        break;
                    }
                }
            }
        } elseif ($stn) {
            // ;
            foreach ($env_ar['ST'] as $st) {
                //
                if ($icn) {
                    if ($gsn) {
                        if ($st['icn'] == $icn && $st['gsn'] == $gsn && $st['stn'] == $stn) {
                            $ret_ar = array_slice($seg_ar, $st['start'], $st['count'], $prskeys);
                            break;
                        }
                    } else {
                        if ($st['icn'] == $icn && $st['stn'] == $stn) {
                            $ret_ar = array_slice($seg_ar, $st['start'], $st['count'], $prskeys);
                            break;
                        }
                    }
                } elseif ($gsn) {
                    if ($st['gsn'] == $gsn && $st['stn'] == $stn) {
                        $ret_ar = array_slice($seg_ar, $st['start'], $st['count'], $prskeys);
                        break;
                    }
                } elseif ($st['stn'] == $stn) {
                    //
                    $ret_ar = array_slice($seg_ar, $st['start'], $st['count'], $prskeys);
                    break;
                }
            }
        } else {
            $this->message[] = 'edih_x12_slice() no file text or invalid array argument keys or values';
        }

        //
        if (!count($ret_ar)) {
            $this->message[] = 'edih_x12_slice() no match';
        }

        return $ret_ar;
    }

// end class edih_x12_file
}
