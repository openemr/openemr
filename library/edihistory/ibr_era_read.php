<?php

/**
 * ibr_era_read.php
 * 
 * Copyright 2012 Kevin McCormick
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
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
 
 ///**
 // *  a security measure to prevent direct web access to this file
 // */
 //// if (!defined('SITE_IN')) die('Direct access not allowed!');
 
/**
 *  ibr_era_code_text ( $code_type, $code_str )
 * 
 * retrieve Qualifier code text from the included file ibr_code_arrays.php
 * 
 * @uses code_arrays()
 * @param string $code_type   one of "|CLMADJ|CLMSTAT|AMT|PER|REF|RA|PLB|"
 * @param string $code_str    a space delimited string of codes e.g. MA15 MA107 MA15
 * @return array              $c_ar[i] = array("Code", "Code text", "", "", "")
 */
 function ibr_era_code_text ( $code_type, $code_str ) {
	 // $code_str, from ibr_era_claim_vals, is like "45 47 125 " or "    MA01 MA27 MA18 "
	 $cd = trim($code_str);
	 $cd_ar = explode(" ", $cd);
	 //
	 // since the codes are just appended onto the $code_str
	 // eliminate duplicates
	 $uniq_ar = array_unique($cd_ar);
	 //
	 $code_class = new code_arrays();
	 
	 foreach ($uniq_ar as $val ) {
		 //
		 switch ($code_type) {
			 case ("CLMADJ"):
			 $c_ar[] = $code_class->get_CODE_CLAIM_ADJUSTMENT($val);
			 continue;
			 
			 case ("RA"):
			 $c_ar[] = $code_class->get_CODE_RA_REMARK($val);
			 continue;
			 
			 case ("PLB"):
			 $c_ar[] = $code_class->get_CODE_PLB_REASON($val);
			 continue;
			 
			 case ("CLMSTAT"):
			 $c_ar[] = $code_class->get_CODE_CLAIM_STATUS($val);
			 continue;
			 
			 case ("PER"):
			 $c_ar[] = $code_class->get_CODE_PER($val);
			 continue;			 

			 case ("REF"):
			 $c_ar[] = $code_class->get_CODE_REF($val);
			 continue;

			 case ("AMT"):
			 $c_ar[] = $code_class->get_CODE_AMT($val);
			 continue;			 
			 
			 case ("CAS"):
			 $c_ar[] = $code_class->get_CODE_CAS_GROUP($val);
			 continue;
			 
			 case ("LOC"):
			 $c_ar[] = $code_class->get_CODE_LOCATION($val);
			 continue;	
			 		 			 			 			 
			 default:
			 $c_ar[] =  array($val, "Unknown code", "", "", "");
		 }
	 }
	 return $c_ar;
 }
 
/**
 * insert dashes in ten-digit telephone numbers
 * 
 * @param string $str_val   the telephone number
 * @return string           the telephone number with dashes
 */
 function ibr_era_format_telephone ($str_val) {
	  $tel = substr($str_val,0,3) . "-" . substr($str_val,3,3) . "-" . substr($str_val,6);
	  return $tel;
  }

/**
 * order MM DD YYYY values and insert slashes in eight-digit dates
 * 
 * US MM/DD/YYYY or general YYYY/MM/DD 
 *  
 * @param string $str_val   the eight-digit date
 * @param string $pref      if 'US' (default) anything else means YYYY/MM/DD
 * @return string           the date with slashes
 */
  function ibr_era_format_date ($str_val, $pref = "US") { 
	  if ($pref == "US") {
		  $dt = substr($str_val,4,2) . "/" . substr($str_val,6) . "/" . substr($str_val,0,4);
	  } else {
		  $dt = substr($str_val,0,4) . "/" . substr($str_val,4,2) . "/" . substr($str_val,6);
	  }
	  return $dt;
  } 
  
/**
 * format monetary amounts with two digits after the decimal place
 * 
 * @todo                    add other formats
 * @param string $str_val   the amount string
 * @param string $pref      'US' is default, no other formats available 
 * @return string           the telephone number with dashes
 */  
  function ibr_era_format_money ($str_val, $pref = "US") { 
	  if (is_numeric($str_val)) { 
		  $mny = sprintf("%01.2f", $str_val);
	  } else {
		  $mny = $str_val;
	  }
	  return $mny;
  }
   
  
/**
 * The transactional slice of the x12 835 segments array is parsed into a multi-dimensional array
 * 
 * Each transactional section, the ST...SE segments is parsed into an array with the following structure
 * This array key listing may not be totally current.
 * <pre>
 * $ar_clm['BPR']['key'] 
 *      ['total_pmt']['credit']['method']['payer_id']['date_pmt']['trace']['payer_name']
 *      ['payer_source']['prod_date']['payer_id_num']['payer_id_name']['payer_id_addr2']
 *      ['payer_id_adr3']['payer_contact']['payer_tech']['payer_tech_contact']
 *      ['rdm_trans_code']['rdm_name']['rdm_comm_num']
 * 
 * $ar_clm['LX'][$lx_ct]['TS3']['key']
 *      ['ref_id']['facility_code']['fiscal_per']['claim_ct']
 *      ['chg_tot']['chg_cvd']['chg_noncvd']['chg_denied']
 *      ['amt_prov']['amt_int']['amt_adj']['amt_grr']['amt_msp']
 *      ['chg_bld']['chg_nonlab']['chg_coins']
 *      ['chg_hcpcs_rpt']['chg_hcpcs_pbl']['amt_dedctbl']['amt_prof']
 *      ['amt_msp_pt']['amt_reimb_pt']['pip_ct']['pip_amt']
 * 
 * $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['key'] 
 *      ['pid']['enctr']['clm_status']['fee']['pmt']['ptresp']['clm_id']['moa']['pt_last']
 *      ['pt_first']['pt_ins_id']['provider_id']['crossover_name']['pr_priority']
 *      ['pr_priority_id']['sbr_last']['sbr_first']['sbr_ins_id']['ins_expired']
 *      ['clm_recieved']['svc_date_per_begin']['svc_date_per_end']['clm_oth_id_descr']['clm_oth_id_num']
 *      ['corr_last']['corr_first']['corr_mi']['corr_ins_id']
 *      ['ref_description']['ref_value']['clm_pr_ver']['clm_pr_ver_num']
 *      ['clm_adj_type']['clm_adj_code']['clm_adj_amt']['clm_adj_total']['clm_adj_qty']
 *      ['clm_amt_code']['clm_amt_amt']['clm_adj_html']
 * 
 * $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['key']
 *      ['svc_enctr']['svc_adj_id']['svc_fee']['svc_pmt'] --['svc_nubc']['svc_units'] 
 *      ['svc_code']['svc_qty']['svc_adj_type']['svc_adj_code']['svc_adj_amt']['svc_adj_total']
 *      ['svc_adj_qty']['svc_adj_html']['svc_pt_resp']['svc_id_descr']['svc_id_num']
 *      ['prov_id_descr']['prov_id_num']['ref_description']['ref_value']['svc_amt_code']
 *      ['svc_amt_amt']
 * 
 * $ar_clm['PLB'][$plb_ct]['key']
 *      ['provider']['per_date']["identifier_$i"]["amount_$i"]["text_$i"]
 * </pre>
 * 
 * @todo verify index increment logic for LX-TS3-CLP segments -- works for what I have sen
 * @param array $ar_st_slice    array of era file segments ST...SE or single claim CLP...CLP
 * @param string $elem_delim    element delimiter, usually *, for the segments
 * @param string $comp_delim    delimiter for composite elements, usually :
 * @return array  				a multidimentional array of values as described above
 */
 function ibr_era_claim_vals ( $ar_st_slice, $elem_delim, $comp_delim ) { 
	 // 
	 //
	 $ar_clm = array();
	 $svc_ct = -1;
	 $clp_ct = -1;
	 $lx_ct= -1;
	 $lq_ct = 0;
	 $plb_ct = 0;
	 //
	 $loop = "0";
	 //
	 $seg_ct = 0;
	 //

     // segments in the ST -- SE block BPR|TRN|N1|N3|PER| LX|TS3|CLP|NM1|MIA|MOA|DTM|SVC|CAS|REF|LQ|AMT
	 foreach ( $ar_st_slice as $segtext ) {
		 //
		 $seg = explode($elem_delim, $segtext);
		 //
		 // do loop and increment resets here
		 if ($seg[0] == "BPR" ) {
			 $loop = "0";
		 }
		 if ($seg[0] == "CLP" ) { 
			 // if there are no preceeding LX segments, pretend we are in the first one
			 if ($lx_ct == -1) { $lx_ct = 0; } 
			 // scv_ct is incremented up on each SVC at top of foreach
			 // assume all related services and adjustments are in following loop 2110
			 $svc_ct = -1; 
			 //$adj_tot = 0;
			 $loop = "2100";
			 $clp_ct++;
			 // set claim adjustment variables so that concatenations will work
			 // This is for CAS segment in loop 2100
			 $adj_type2100 = "";
			 $adj_code2100 = "";
			 $adj_amt2100 = "";
			 $adj_total2100 = "";
			 $adj_qty2100 = "";
			 $adj_html2100 = "";
			 //
			 $amt_c = "";
			 $amt_a = "";
			 //
			 $lq_type = "";
			 $lq_code = "";
			 //
			 $refstr = "";
			 //
		 }
		 if ($seg[0] == "SVC" ) { 
			 $svc_ct++; 
			 $loop = "2110";
			 //$adj_tot = 0;
			 // for the related CAS segment 
			 // service level  loop 2110
			 $adj_type2110 = "";
			 $adj_code2110 = "";
			 $adj_amt2110 = "";
			 $adj_total2110 = "";
			 $adj_qty2110 = "";	
			 $adj_html2110 = "";
			 $lq_type = "";
			 $lq_code = "";
			 //
			 $amt_c = "";
			 $amt_a = "";
			 //
			 $refstr = "";
		 }
		 if ($seg[0] == "LX" ) { 
			 // problem here is LX but no TS3 just clutters HTML table
			 // LX groups claims, but appears useful only when TS3 is next
			 // move $lx_ct++ increment to TS3 segment part;
			 // $lx_ct++; 
			 $loop = "2000";
		 }
		 if ($seg[0] == "N1" && $seg[1] == "PR") { $loop = "1000A"; }
		 if ($seg[0] == "N1" && $seg[1] == "PE") { $loop = "1000B"; }
		 //
		 // now evaluate segments and construct array
		 //
		 // BPR segment
		 if ($seg[0] == "BPR" ) {
			 $ar_clm['BPR']['total_pmt'] = sprintf("%01.2f", $seg[2]);
			 $ar_clm['BPR']['credit'] = $seg[3];
			 $ar_clm['BPR']['method'] = $seg[4];
			 $ar_clm['BPR']['payer_id'] = $seg[10];
			 $ar_clm['BPR']['date_pmt'] = ibr_era_format_date($seg[16]);
			 // ['total_pmt']['credit']['method'] ['payer_id']['date_pmt'] 
			 // ['BPR02']    ['BPR03'] ['BPR04']  ['BPR10']   ['BPR16']
			 continue;
		 }
		 // TRN segment
		 if($seg[0] == "TRN" ) {
			 $ar_clm['BPR']['trace'] = $seg[2];
			 //['trace']
			 //['TRN02']
			 continue;			 
		 }
		 // N1 segment
		 if($seg[0] == "N1" && $seg[1] == "PR") {
			 $ar_clm['BPR']['payer_name'] = $seg[2];
			 if ( array_key_exists(4, $seg) ) { $ar_clm['BPR']['payer_id'] = $seg[4];}
			 //
			 continue;				 
		 }
		 // N3 segment
		 if($seg[0] == "N3" && $loop == "1000A") {
			 //['payer_id_addr2']['payer_id_adr3']['payer_contact']
			 $ar_clm['BPR']['payer_id_addr2'] = $seg[1];
			 if ( array_key_exists(2, $seg) ) { $ar_clm['BPR']['payer_id_addr2'] .= " {$seg[2]}"; }
			 //
			 continue;			 
		 }
		 // N4 segment
		 if ($seg[0] == "N4" && $loop == "1000A") {
			 $ar_clm['BPR']['payer_id_addr3'] = $seg[1];
			 if ( array_key_exists(2, $seg) ) { $ar_clm['BPR']['payer_id_addr3'] .= " {$seg[2]}"; }
			 if ( array_key_exists(3, $seg) ) { $ar_clm['BPR']['payer_id_addr3'] .= " {$seg[3]}"; }	
			 //
			 continue;
		 }	 
		 // RDM segment  5010
		 if ( $seg[0] == "RDM" ) { 
			 $rdm_m = array("BM"=>"By Mail", "EM"=>"E-Mail", 
			                      "FT"=> "File Transfer", "OL"=>"On-Line");
			                      
			 $ar_clm['BPR']['rdm_trans_code'] = isset($rdm_m[$seg[1]]) ? $rdm_m[$seg[1]] : $seg[1];
			 $ar_clm['BPR']['rdm_name'] = $seg[2];
			 $ar_clm['BPR']['rdm_comm_num'] = $seg[3];
			 //
			 continue;
		 }
		 
		 // TS3 segment
		 if ( $seg[0] == "TS3" ) {
			 // TS3	Provider Summary Information
			 // varying length of this segment per payer
			 // indicated by preceeding LX segment when used
			 // increment $lx_ct so TS3 and related claims are in the same subarray
			 // see also ibr_era_claim_EOB_heading_html for constructing html table header
			 // 
			 if ($loop == "2000" ) { $lx_ct++; } // a preceeding LX
			 //
			 $ts3_ky = array('ts3', 'ref_id', 'facility_code', 'fiscal_per', 'claim_ct', 
			                 'chg_tot', 'chg_cvd', 'chg_noncvd', 'chg_denied', 
			                 'amt_prov', 'amt_int', 'amt_adj', 'amt_grr', 
			                 'amt_msp', 'chg_bld', 'chg_nonlab', 'chg_coins', 
			                 'chg_hcpcs_rpt','chg_hcpcs_pbl',  'amt_dedctbl', 
			                 'amt_prof', 'amt_msp_pt', 'amt_reimb_pt', 
			                 'pip_ct', 'pip_amt');
			 //
			 //debug
			 //var_dump($seg);
			 //			                 
			 $ct = count($seg);
			 for ($i = 1; $i < $ct; $i++) { 
				 //echo "$i {$seg[$i]} " . PHP_EOL;
				 $ar_clm['LX'][$lx_ct]['TS3']["{$ts3_ky[$i]}"] = $seg[$i];

			 }
			 
			 continue;
		 }

		 //
		 // CLP segment  ibr_code_arrays -> get_CLAIM_STATUS($code)
		 // 1,2,3,4,5,10, 13, 15, 16, 17, 19, 20, 21, 22, 23, 25, 27
		 if ($seg[0] == "CLP" ) { 
			 // make sure $lx_ct is at least 0
			 if ($lx_ct == -1) { $lx_ct++; }
			 //
			 $status_ar = array("1"=>"1 Primary", "2"=>"2 Secondary", 
			                    "3"=>"3 Tertiary", "4"=>"4 Denied",
			                    "19"=>"19 Primary/FWD", 
			                    "20"=>"20 Secondary/FWD",
			                    "21"=>"21 Tertiary/FWD",
			                    "22"=>"22 Reversal/Refund",
			                    "23"=>"23 Not our claim/FWD",
			                    "24"=>"24 Predetermination only"
			                    );

			 // since patient ID and encounter are in form id-enctr
			 //
			 $inv_split = csv_pid_enctr_parse($seg[1]);
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pid'] = $inv_split['pid'];         // substr ( $seg[1], 0, strpos($seg[1], "-") );
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['enctr'] = $inv_split['enctr'];       // substr ( $seg[1], strpos($seg[1], "-") + 1 );
			 //
			 if (array_key_exists($seg[2], $status_ar)){
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_status'] = $status_ar[$seg[2]];
			 } else {
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_status'] = $seg[2];
			 }
			 //
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['fee'] = sprintf("%01.2f", $seg[3]);
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pmt'] = sprintf("%01.2f", $seg[4]);
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['ptresp'] = sprintf("%01.2f", $seg[5]);
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_id'] = $seg[7];
			 //
			 $svc_enctr = $inv_split['enctr'];  //  use enctr to match service with CLP
			 //
			 continue;
		 }
		 
		 //MOA segment  --I don't think we get the MIA segment for Dr's office
		 if ($seg[0] == "MOA" ) { 
			 // Outpatient adjudication information
			 // can't tell how many fields will be given
			 // create a space delimited string for each
			 // and get the text in the html function
			 $moa_str = "";
			 $moa_pct = "";
			 for ($i=1; $i < count($seg); $i++ ) { 
				 if ($i == 1 && strlen($seg[$i])) $moa_pct = "Reimb Rate " . sprintf("% 1.0f%%", $seg[$i]);
				 if ($i == 2 && strlen($seg[$i])) $moa_pct .= " Claim HCPCS Payable Amtt " . sprintf("%01.2f", $seg[$i]);
				 if ( $i > 2 && $i < 8  && strlen($seg[$i]) ) { $moa_str .= $seg[$i] . " "; }
				 if ($i == 8 && strlen($seg[$i])) $moa_pct .= " Claim ESRD Pmtt Amt " . sprintf("%01.2f", $seg[$i]);
				 if ($i == 9 && strlen($seg[$i])) $moa_pct .= " Nonpayable Prof Comp Amt " . sprintf("%01.2f", $seg[$i]);
			 }
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['moa'] = $moa_str;
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['moa_amt'] = $moa_pct;
			 continue;
		 }
		 // NM1 segment
		 if ($seg[0] == "NM1" ) { 
			 // multiple NM1 segments, check qualifier
			 // loop 2100
			 //
			 if ($seg[1] == "QC" ) { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pt_last'] = $seg[3];
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pt_first'] = $seg[4];
				 if ( isset($seg[8]) && isset($seg[9]) ) {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pt_ins_id'] = $seg[8] . " " . $seg[9];
				 }
				 continue;
			 }
			 if ($seg[1] == "IL" ) { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['sbr_last'] = $seg[3];
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['sbr_first'] = $seg[4];
				 if ( isset($seg[8]) && isset($seg[9]) ) {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['sbr_ins_id'] = $seg[8] . " " . $seg[9];
				 }
				 continue;
			 }
			 if ($seg[1] == "74" && count($seg) > 3 ) { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['corr_last'] = $seg[3];
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['corr_first'] = $seg[4];
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['corr_mi'] = $seg[5];
				 if ( isset($seg[8]) && isset($seg[9]) ) {
					 // expect $seg[8] to be 'C' insured's corrected ID
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['corr_ins_id'] = ($seg[8] == 'C') ?  $seg[9] : $seg[8] ." ". $seg[9];
				 }
				 continue;
			 }
			 			  				 
			 if ($seg[1] == "82" ) { 
				 if ( isset($seg[9]) ) {
					$ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['provider_id'] = $seg[9];
				}
				 continue;
			 }
			 if ($seg[1] == "TT" ) { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['crossover_name'] = $seg[3];
				 continue;
			 }

			 if ($seg[1] == "PR" && $loop == "2100") { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pr_priority'] = $seg[1] . ": " . $seg[3];
				 if ( isset($seg[8]) && isset($seg[9]) ) {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pr_priority_id'] = $seg[8] . " " . $seg[9];
				 }
				 continue; 
			 }			 
			 // this continue should only apply if there was no match
			 continue;
		 }
		 // DTM segment
		 // assume qualifier in seg[1] and calendar date in seg[2]
		 if ($seg[0] == "DTM" ) { 
			 // dates -- format
			 $fmt_dt = ibr_era_format_date($seg[2]);
			 //
			 if ( $loop == "2100" ) { 
				 if ($seg[1] == "050" ) { 
					 //if ($ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_recieved']) {
					 if ( array_key_exists('clm_recieved', $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]) ) {
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['pmt_date'] = $fmt_dt; //probably no such thing
					 } else {
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_recieved'] = $fmt_dt;
					 }
				 }
				 if ($seg[1] == "036" ) { $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['ins_expired'] = $fmt_dt; }
				 if ($seg[1] == "232" ) { $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['svc_date_per_begin'] = $fmt_dt; }
				 if ($seg[1] == "233" ) { $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['svc_date_per_end'] = $fmt_dt; }
				 //
				 continue;				 
			 } 
			 if ( $loop == "2110" ) {
				 // SVC dates  150,151,472
				 if ($seg[1] == "150" ) { $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_date_begin'] = $fmt_dt; }
				 if ($seg[1] == "151" ) { $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_date_end'] = $fmt_dt; }
				 // service date 
				 if ($seg[1] == "472" ) { $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_date'] = $fmt_dt; }
				 //
				 continue;					 
			 }
			 // 835 production date 
			 if ( $loop == "0" && $seg[1] == "405") { 
				 $ar_clm['BPR']['prod_date'] = $fmt_dt;
				 //
				 continue; 
			 }
			 // in case of no match, just skip it
			 continue;
		 }
		 // SVC segment
		 if ($seg[0] == "SVC" ) { 
			 // account for one or more SVC items
			 // HC, N4, NU, ZZ, HP 
			 /*	
			  * The value in SVC03 should equal the value in SVC02 
			  * minus all monetary amounts in the
			  * subsequent CAS segments of this loop.
			  */
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_enctr'] = $svc_enctr;
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_id'] = $seg[1];
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_fee'] = sprintf("%01.2f", $seg[2]);
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_pmt'] = sprintf("%01.2f", $seg[3]);
			 //
			 if (array_key_exists(4, $seg)) { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_nubc'] = $seg[4];
			 }
			 if (array_key_exists(5, $seg)) {
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_units'] = $seg[5];
			 }
			 if (array_key_exists(6, $seg) && strlen($seg[6])) { 
				 $svc_rev_str = '';
				 if (strpos($seg[6], $comp_delim)) {
					 $svc_rev_codes = array('AD'=>'Am Dental Assoc', 'ER'=>'Jur Specific', 'HC'=>'HCPCS',
					                        'HP'=>'HIPPS', 'IV'=>'HIEC', 'N4'=>'NDC542', 'WK'=>'ABC Code');
					 $svc_rev = explode($comp_delim, $seg[6]);
					 if (isset($svc_rev[1])) { $svc_rev_str .= $svc_rev[1]; }
					 if (isset($svc_rev[2])) { $svc_rev_str .= ':'.$svc_rev[2]; }
					 if (isset($svc_rev[3])) { $svc_rev_str .= ':'.$svc_rev[3]; }
					 if (isset($svc_rev[4])) { $svc_rev_str .= ':'.$svc_rev[4]; }
					 if (isset($svc_rev[5])) { $svc_rev_str .= ':'.$svc_rev[5]; }
					 if (isset($svc_rev[6])) { $svc_rev_str .= ':'.$svc_rev[6]; }
					 //
					 if (isset($svc_rev[0]) && array_key_exists($svc_rev[0], $svc_rev_codes)) { 
						 $svc_rev_str .= $svc_rev_codes[$svc_rev[0]]; 
					 }
					 if (isset($svc_rev[6])) { $svc_rev_str .=  ' '.$svc_rev[6]; }
					 //
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_code'] = $svc_rev_str;
				 } else {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_code'] = $seg[6];
				 }
			 }
			  if (array_key_exists(7, $seg)) { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_qty'] = $seg[7];
			 }
			 //
			 continue;

		 }
		 // REF segment
		 // ibr_code_arrays -> get_REF_1000($code) get_REF_2100($code) get_REF_2200($code)
		 if ($seg[0] == "REF" ) { 
			 // issue of more than one REF per CLP|SVC // EA, 28, 6P, 
			 // REF segments are like herding cats
			 //
			 $ref_2100 = array('1S'=>'APG', '6R'=>'ProvCtln', 'E9'=>'AttchCd', 'G1'=>'PriorAuth',
			         'G3'=>'PredetBenID', 'LU'=>'Loc', 'RB'=>'RateCd', '1L'=>'Grp/Pol',
			         '1W'=>'MbrID', '9A'=>'ReprRef', '9C'=>'AdjReprRef', 'A6'=>'EIN',
			         'APC'=>'APC', 'BB'=>'Auth', 'CE'=>'Contrct', 'EA'=>'Med RecID',
			         'F8'=>'Orig Ref', 'IG'=>'InsPol', 'SY'=>'SSN', '1A'=>'BC Prv',
			         '1B'=>'BS Prv', '1C'=>'MCR Prv', '1D'=>'MCD Prv', '1G'=>'UPIN',
			         '1H'=>'CHAMPUS ID', 'D3'=>'NABP', 'G2'=>'Prv Com', '1J'=>'Fclty',
			         'HPI'=>'HCFA', 'TJ'=>'TIN', 'EV'=>'RecID' );

             if ($loop == "2100") {
				 // claim level REF 
				 if (array_key_exists($seg[1], $ref_2100)) {
					 $refstr .= ' ' .  $ref_2100[$seg[1]];
					 $refstr .= ': ' . $seg[2];
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_oth_id_num'] = $refstr;
				 } else {
					 $refstr .= ' ' . $seg[1] . ' ' .$seg[2];
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['ref_value'] = $refstr;
				 }
				 
				 /* **** old stuff
				 if ( strpos("|1W|9A|9C|A6|BB|CE|EA|F8|G1|G3|IG|SY|28|6P", $seg[1] )) {
					 if (isset($ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_oth_id_descr'])) {
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_oth_id_descr'] .= " " . $seg[1]; 
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_oth_id_num'] .= " " . $seg[2];
					 } else {
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_oth_id_descr'] = $seg[1]; 
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_oth_id_num'] = $seg[2];
					 }						 
					 continue;
				 } else { 
				  
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['ref_description'] = $seg[1];
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['ref_value'] = $seg[2];
					 continue;
				 }
				 * **** end old stuff *****/
				 
			 }
			 if ($loop == "2110") {
				 // service level REF
				 if ( $seg[1] == "6R" ) {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_pcn'] = $seg[1];
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_pcn_num'] = $seg[2];
				 } elseif ( $seg[1] == "LU" ) {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_loc'] = $seg[1];
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_loc_code'] = $seg[2];
				 } elseif (strpos("|1S|APC|BB|E9|G1|G3|RB|0K", $seg[1] )) {
					 if (isset($ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_id_descr'])) {
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_id_descr'] .= " " . $seg[1];
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_id_num'] .= " " . $seg[2];
					 } else {
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_id_descr'] = $seg[1];
						 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_id_num'] = $seg[2];
					 }						 
					 continue;
				 }
				 if ( strpos("|1A|1B|1C|1D|1G|1H|D3|G2|HPI|TJ", $seg[1] )) {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['prov_id_descr'] = $seg[1];
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['prov_id_num'] = $seg[2];
					 continue;
				 }
			 }
			 
			 if ($loop == "0") {
				 if ($seg[1] == "F2") {
					 //$ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_pr_ver'] = $seg[1]; 
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_pr_ver'] = 'Ins Adj Ver:';
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_pr_ver_num'] = $seg[2];
					 continue;
				 }
			 }
			 //if ($loop == "1000A") {
				 //
			//	 $ar_clm['BPR']['ref_description'] = $seg[1];
			//	 $ar_clm['BPR']['ref_value'] = $seg[2];
			// }
				 // debug
				 //echo "REF" . PHP_EOL;
				 //var_dump($seg);
				 //
			 continue;
		 }
		 
		 // PER segment
		 if ($seg[0] == "PER" ) { 
			 if ($loop == "1000A") {
				 if ($seg[1] == "CX") {
					 $ar_clm['BPR']['payer_source'] = $seg[2];
				 
					 if ($seg[3] == "TE") { 
						 //$ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['payer_contact'] = substr($seg[4],0,3) . "-" . substr($seg[4],3,3) . "-" . substr($seg[4],6);
						 $ar_clm['BPR']['payer_contact'] = ibr_era_format_telephone($seg[4]);
					 } else {
						 $ar_clm['BPR']['payer_contact'] = $seg[4];
					 }
				 }
				 if ($seg[1] == "BL") {
					 $pr_tech = '';
					 for ($i=3; $i<count($seg); $i=$i+2) {
						 if ($seg[$i] == "TE" || $seg[$i] == "FX") { 
							 $pr_tech .= $seg[$i].' '.ibr_era_format_telephone($seg[$i+1]).' ';
						 } else {
							 $pr_tech .= $seg[$i].' '.$seg[$i+1].' ';	
						 }
					 }
					 $ar_clm['BPR']['payer_tech'] = $seg[2];
					 $ar_clm['BPR']['payer_tech_contact'] = $pr_tech;				 
				 }
			 }
			 if ($loop == "2100") {
				 if ($seg[1] == "CX") {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['payer_source'] = $seg[2];
				 }
				 if ($seg[3] == "TE") { 
					 //$ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['payer_contact'] = substr($seg[4],0,3) . "-" . substr($seg[4],3,3) . "-" . substr($seg[4],6);
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['payer_contact'] = ibr_era_format_telephone($seg[4]);
				 } else {
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['payer_contact'] = "{$seg[3]} {$seg[4]}";
				 }
			 }				 
			 //
			 continue;
		}

		 // CAS segment
		 if ($seg[0] == "CAS" ) { 
			 // can be in loop 2100 and loop 2110
			 // CAS segment can have multiple adjustments
			 // there is one CAS per adjustment type, but repeated if there are over 6 adjustments of that type
			 // the variables are set to empty strings on loop change, but appended to on segment repetition,
			 //  e.g. 2 CAS in loop 2100 -> $adj_type2100 is "CO OA "
			 // for accounting we need CAS['type']['index'] =>['code'] ['amount'] ['quantity']
			 // set adjustment total to 0
			 $adj_tot = 0;

			 $era_cas = array("CO" => "CO Contractual Obligations",
			                  "CR" => "CR Corrections and Reversals",
			                  "OA" => "OA Other Adjustments",
			                  "PI" => "PI Payor Initiated Reductions",
			                  "PR" => "PR Patient Responsibility"
			                 );
			                 
			 if ($loop == "2100" ) {
				 // claim level  adj_type2100 adj_code2100 adj_amt2100 adj_total2100 adj_qty2100
				 $adj_type2100 .= $seg[1] . " ";
				 $adj_html2100 .= $seg[1] . " ";
				 // multiple adjustments can be listed in the same CAS
				 // collect the information in variables, then assign to array keys
				 $cas_ct = count($seg);
				 for ( $i=2; $i<$cas_ct; $i=$i+3 ) { 
					 $adj_code2100 .= $seg[$i] . " ";
					 $adj_amt2100 .= sprintf("%01.2f", $seg[$i+1]) . " ";
					 $adj_total2100 += $seg[$i+1];
					 if ( array_key_exists( $i+2, $seg ) ) { $adj_qty2100 .= $seg[$i+2] . " "; }
					 //
					 $adj_html2100 .= $seg[$i];
					 $adj_html2100 .= ": ". sprintf("%01.2f", $seg[$i+1]);
					 $adj_html2100 .= (array_key_exists($i+2, $seg) && strlen(trim($seg[$i+2])) ) ? " x{$seg[$i+2]} | " : " | ";  
				 }
				 //
				 // This is where the accounting can get tricky 
				 // for me, the example of this is collecting too high a copay/deductible and having the
				 // insurance company pay less than allowed, with balance paid to patient
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_adj_type'] = $adj_type2100;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_adj_code'] = $adj_code2100;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_adj_amt'] = $adj_amt2100;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_adj_total'] = sprintf("%01.2f", $adj_total2100);
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_adj_qty'] = $adj_qty2100;
				 // for html loop 2100
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_adj_html'] = $adj_html2100 ."(".sprintf("%01.2f", $adj_total2100).")"; 	
				 // $adj_qty2100 may need isset($adj_qty2100) ? $adj_qty2100 : "";
				 // and then unset($adj_qty2100);
			 }
			 if ($loop == "2110" ) {
				 // service level adj_type2110 adj_code2110 adj_amt2110 adj_total2110 adj_qty2110
				 // use $svc_ct here since this segment applies to a particular service
				 //
				 $adj_type2110 .= $seg[1] . " ";
				 $adj_html2110 .= $seg[1] . " ";
				 // claim adjustment codes are at CAS02, CAS05, CAS08, etc.
				 $cas_ct = count($seg);
				 for ( $i=2; $i<$cas_ct; $i=$i+3 ) {
					 $adj_code2110 .= $seg[$i] . " ";
					 $adj_amt2110 .= sprintf("%01.2f", $seg[$i+1]) . " ";
					 $adj_total2110 += $seg[$i+1];
					 // quantity may be present
					 if ( array_key_exists( $i+2, $seg ) ) { $adj_qty2110 .= $seg[$i+2] . " "; }
					 // formatted for html output
					 $adj_html2110 .= $seg[$i];
					 $adj_html2110 .= ": ". sprintf("%01.2f", $seg[$i+1]);
					 $adj_html2110 .= (array_key_exists($i+2, $seg)  && strlen(trim($seg[$i+2])) ) ? " x{$seg[$i+2]} | " : " | ";  
				 }
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_type'] = $adj_type2110;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_code'] = $adj_code2110;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_amt'] = $adj_amt2110;
				 //$adj_tot += $seg[$i+1];
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_total'] = sprintf("%01.2f", $adj_total2110);
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_qty'] = $adj_qty2110; 
				 // for html
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_adj_html'] = $adj_html2110 ."(".sprintf("%01.2f", $adj_total2110).")"; 	  
				 //
				 if ($seg[1] == "PR") { 
					 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_pt_resp'] = sprintf("%01.2f", $seg[3]);
				 }
			 }
			 //
			 continue;
		 }

		 // LQ segment
		 if ($seg[0] == "LQ" ) { 
			 // for concatenation, if LQ segment is repeated in loop 2110
			 $lq_type .= $seg[1] . " ";
			 $lq_code .= $seg[2] . " ";
			 //
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['rem_type'] = $lq_type;
			 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['rem_code'] = $lq_code;
			 continue;
		 }
		 		 
		 // AMT segment
		 if ($seg[0] == "AMT" ) { 
			 // ibr_code_arrays -> get_AMT_CODE($code, $loop)
			 $amt_qual = array("B6"=>"Allowed",
			                   "AU"=>"Coverage Amt",
			                   "D8"=>"Discount Amt",
			                   "F5"=>"Patient Paid",
			                   "I"=>"Interest",
			                   "NL"=>"Neg Ledger Bal",
			                   "T2"=>"Tot Bef Taxes",
			                   "DY"=>"Per Day Lim",
			                   "KH"=>"DeductedAmt/LateFiling",
			                   "NE"=>"Net Billed",
			                   "T"=>"Tax",
			                   "ZK"=>"MCR/MCD Cat1",
			                   "ZL"=>"MCR/MCD Cat2",
			                   "ZM"=>"MCR/MCD Cat3",
			                   "ZN"=>"MCR/MCD Cat4",
			                   "ZO"=>"MCR/MCD Cat5"
			                   );

			 // use $svc_ct here as well
			 // Qualifiers: Loop 2100  AU|F5|I|NL|ZK|ZL|ZM|ZN|ZO
			 //             Loop 2110 B6,KH
			 //
			 // since more than one AMT is allowed, accumulate and concatenate
			 // no -- text takes too much space
			 //if (array_key_exists($seg[1], $amt_qual )) {
			 //	 $amt_a .= $amt_qual[$seg[1]] . ': ';
			 //} else {
			 //	 $amt_a .= $seg[1] . ': ';
			 //}
			 $amt_a .= $seg[1] . ': ';
			 // verify numeric quantity for seg[2]
			 $amt_a .= is_numeric($seg[2]) ? ibr_era_format_money(trim($seg[2])) : trim($seg[2]); 
			 $amt_a .= ' ';
			 //
			 $amt_c .= $seg[1] .' ';
			 //
			 if ($loop == "2100") { 
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_amt_code'] = $amt_c;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['clm_amt_amt'] = $amt_a;
			 }
			 if ($loop == "2110") {
				 //$amt_c = $amt_qual[$seg[1]];
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_amt_code'] = $amt_c;
				 $ar_clm['LX'][$lx_ct]['CLM'][$clp_ct]['SVC'][$svc_ct]['svc_amt_amt'] = $amt_a;
			 }
			 // debug
			 // var_dump($seg);
			 continue;
		 } 
		 
		 // PLB Segment -- not a claim and perhaps not for biller's eyes
		 // I believe more than one PLB segment per 835 is allowed
		 if ($seg[0] == "PLB" ) { 
			 // payents and adjustments that are not claims
			 $ar_clm['PLB'][$plb_ct]['provider'] = $seg[1];
			 $ar_clm['PLB'][$plb_ct]['per_date'] = $seg[2];
			 // multiple identifiers and amounts are possible
			 // PLB03 = Identifier: PLB04 = Amount 
			 $seg_ct = count($seg);
			 $itm_ct = 0;
			 for ($i=3; $i<$seg_ct; $i=$i+2) { 
 				 // the x12 SubItem delimiter is actually needed here
				 $plb_id = explode($comp_delim, $seg[$i]);
				 $plb_txt_ar = ibr_era_code_text ( "PLB", $plb_id[0] );
				 $ar_clm['PLB'][$plb_ct]['itm'][$itm_ct]['text'] = $plb_txt_ar[0][0] . " " . $plb_txt_ar[0][1];
				 //
				 $ar_clm['PLB'][$plb_ct]['itm'][$itm_ct]['identifier'] = $seg[$i];
				 $ar_clm['PLB'][$plb_ct]['itm'][$itm_ct]['amount'] = $seg[$i+1];
		         //
		         $itm_ct++;
		         // ['provider']['per_date']["identifier_$i"]["amount_$i"]["text_$i"]
			 }
			 $plb_ct++;	
			 // debug
			 // var_dump($seg);
			 continue;
		 }
		 // $loop_seg = $seg[0]; // keep track of previous segment
		 //debug  only segments that are not caught by an if block increment the counter
		 $seg_ct++;
	 } // end foreach
	 // debug
	 // var_dump($ar_clm);
	 //
	 return $ar_clm;
	 
 } // end function ibr_era_claim_vals

/**
 * Display brief summary of single payment
 * 
 * @uses csv_verify_file()
 * @uses csv_x12_segments()
 * @uses ibr_era_claim_slice_pos()
 * @uses ibr_era_claim_vals()
 * @param string $filename
 * @param string $clm01
 * @return string
 */
function ibr_era_claim_summary($filename, $clm01) {
	// create a display for dialogue from csv dataTable
	if (!$clm01) {
		csv_edihist_log("ibr_era_claim_popup: invalid claim id");
		return "empty or invalid claim id <br />" . PHP_EOL;
	}
	$f_path = csv_verify_file( $filename, "era");
	//
	if (!$f_path) {
		csv_edihist_log("ibr_era_claim_popup: failed verification $filename");
		return "failed verification for $filename <br />" . PHP_EOL;
	} else {
		$fn = basename($f_path);
		$ar_era_segments = csv_x12_segments($f_path, "era", false);
		if (!is_array($ar_era_segments) || !count($ar_era_segments['segments']) >0 ) {
			return "failed to get segments for $fn<br />" . PHP_EOL;
		} else {
			$ar_segs = $ar_era_segments['segments'];
			$comp_d = $ar_era_segments['delimiters']['s'];
			$elem_d = $ar_era_segments['delimiters']['e'];
			$srch = 'encounter';
			$ar_clp = array();
			$ar_clp_slice = ibr_era_claim_slice_pos ($ar_segs, $clm01, $elem_d, $srch);
			$svc_adj_codes = '';
			$sp = '';
		}
	} 
	//
	if ( !empty($ar_clp_slice) && is_array($ar_clp_slice)) {
		foreach($ar_clp_slice as $cs) {
			// because an encounter can be reported more than once in an era file
			if (count($cs) == 2) { 
				$clp_segs = array_slice($ar_segs, $cs['start'], $cs['count']);
				// append segments to $ar_clp
				foreach($clp_segs as $clp) {
					$ar_clp[] = $clp;
				}
			}
		 }
	 } else {
		 // no segments found for claim
		 csv_edihist_log("ibr_era_html_page: Claim $clm01 not found in $fn");
		 $sp .= "Claim $pid_enctr not found in $fn";
		 return $sp;
	 }
	 // now use $ar_clp to create a values array
	 $ar_eob = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d);
	 if (!is_array($ar_eob) && count($ar_eob)) {
		 $sp .= "Error in processing";
		 return $sp;
	 }
	 // this is a four column format of the individual claim RA
	 $sp .= "<table class='summaryRA' cols=4><caption>ERA Summary $clm01</caption>".PHP_EOL;
	 $sp .= "<thead><th>Miscellaneous</th><th>Fee</th><th>Pmt</th><th>PtRsp</th></thead>".PHP_EOL;
	 $sp .= "<tbody>".PHP_EOL;
	 foreach ($ar_eob['LX'][0]['CLM'] as $clm) {
		 //
		 $corr_str = "";
		 if (array_key_exists("corr_last", $clm)) {
			if (strlen($clm['corr_last'])) { $corr_str .= "<em>L &nbsp;</em>{$clm['corr_last']}&nbsp;"; }
			if (strlen($clm['corr_first'])) { $corr_str .= "<em>F &nbsp;</em>{$clm['corr_first']}&nbsp;"; }
			if (strlen($clm['corr_mi'])) { $corr_str .= "<em>MI &nbsp;</em>{$clm['corr_mi']}&nbsp;"; }
			if (strlen($clm['corr_ins_id'])) { $corr_str .= "<em>ID &nbsp;</em>{$clm['corr_ins_id']}"; }
		 } 				   						     
		 $sp .= "<tr class='summary'>".PHP_EOL;
		 $sp .= isset($clm['pt_last']) ? "<td>{$clm['pt_last']}, {$clm['pt_first']}</td>" : "<td>&nbsp;</td>";
		 $sp .= isset($clm['enctr']) ? "<td>{$clm['pid']}-{$clm['enctr']}</td>" : "<td>&nbsp;</td>";
		 $sp .= isset($clm['clm_id']) ? "<td>{$clm['clm_id']}</td>" : "<td>&nbsp;</td>";
		 $sp .= ($corr_str) ? "<td>$corr_str</td>" : "<td>&nbsp;</td>";
		 $sp .= PHP_EOL."</tr>".PHP_EOL;
		 //
		 if (isset($clm['ins_expired'])) {
			 $sp .= "<tr class='summary'>".PHP_EOL;
			 $sp .= isset($clm['ins_expired']) ? "<td class='denied' colspan=2>Policy Expired: {$clm['ins_expired']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $sp .= isset($clm['pr_priority']) ? "<td class='denied' colspan=2>Payer: {$clm['pr_priority']} {$clm['pr_priority_id']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $sp .= PHP_EOL."</tr>".PHP_EOL;
		 }
		 if (isset($clm['sbr_last'])) {
			 $sp .= "<tr class='summary'>".PHP_EOL;
			 $sp .= isset($clm['sbr_last']) ? "<td colspan=2>{$clm['sbr_last']}, {$clm['sbr_first']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $sp .= isset($clm['sbr_first']) ? "<td colspan=2>{$clm['sbr_ins_id']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $sp .= PHP_EOL."</tr>".PHP_EOL;
		 }
		 //
		 $sp .= "<tr class='summary'>".PHP_EOL;
		 $sp .= isset($clm['clm_status']) ? "<td>{$clm['clm_status']}</td>" : "<td>&nbsp;</td>";
		 $sp .= isset($clm['fee']) ? "<td>{$clm['fee']}</td>" : "<td>&nbsp;</td>";
		 $sp .= isset($clm['pmt']) ? "<td>{$clm['pmt']}</td>" : "<td>&nbsp;</td>";
		 $sp .= isset($clm['ptresp']) ? "<td>{$clm['ptresp']}</td>" : "<td>&nbsp;</td>";
		 $sp .= PHP_EOL."</tr>".PHP_EOL;
		 //$ar_eob['LX']['CLM']['SVC']
		 foreach($clm['SVC'] as $svc) {
			 if (array_key_exists("svc_adj_code", $svc)) { 
				 $svc_adj_codes .= $svc['svc_adj_code'];
			 }
			 $svcid = isset($svc['svc_date']) ? $svc['svc_date'] : '';
			 $svcid .= isset($svc['svc_adj_id']) ? ' &nbsp;' . $svc['svc_adj_id'] : '';
			 $sp .= "<tr class='summary'>".PHP_EOL;
			 //$sp .= isset($svc['svc_adj_id']) ? "<td>{$svc['svc_adj_id']}</td>" : "<td>&nbsp;</td>";
			 $sp .= "<td>$svcid</td>";
			 $sp .= isset($svc['svc_fee']) ? "<td>{$svc['svc_fee']}</td>" : "<td>&nbsp;</td>";
			 $sp .= isset($svc['svc_pmt']) ? "<td>{$svc['svc_pmt']}</td>" : "<td>&nbsp;</td>";
			 $sp .= isset($svc['svc_pt_resp']) ? "<td>{$svc['svc_pt_resp']}</td>" : "<td>&nbsp;</td>";
			 $sp .= PHP_EOL."</tr>".PHP_EOL;
			 $sp .= "<tr class='summary'>".PHP_EOL;
			 $sp .= isset($svc['prov_id_descr']) ? "<td colspan=2>{$svc['prov_id_descr']} {$svc['prov_id_num']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $sp .= isset($svc['svc_adj_html']) ? "<td colspan=2>{$svc['svc_adj_html']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $sp .= PHP_EOL."</tr>".PHP_EOL;
		 }
		 $sp .= "</tbody>".PHP_EOL;
	 }
	 //
	 if ($svc_adj_codes) { 
		 $svc_code_text = ibr_era_code_text ("CLMADJ", $svc_adj_codes );
		 $sp .= "<table class='summaryRA' cols=4><caption>Service Adjustment Codes</caption>".PHP_EOL;
		 //$sp .= "<tr class='code'><td colspan=4>Service Adjustment Codes</td></tr>";
		 foreach($svc_code_text as $cd) { 
			 $sp .= "<tr class=\"svccode\">".PHP_EOL;
			 $sp .= "<td align=\"center\" cellpadding=\"4\">{$cd[0]}</td> <td colspan=3>{$cd[1]}</td> ".PHP_EOL;
			 $sp .= "</tr>".PHP_EOL;
		 }
		 $sp .= "</tbody>".PHP_EOL;
	 }
	 
	//
	return $sp;
}


 /**
  * Generate the table heading for the tables created in ibr_era_claim_html ()
  * 
  * @param  string $section default is CLM one of |CLM|BPR|PLB|TS3| section heading for html table
  * @param  array $fmt_ar default is NULL otherwise TS3 values array is expected
  * @return string html table heading <table><caption></caption><thead><tr><th></th></tr></thead>
  */
 function ibr_era_claim_EOB_heading_html ( $section = "CLM", $fmt_ar = NULL) {
	 // generate an html string for the claim EOB table heading
	 // table should begin with: <table cols=7> 
	 // row 2 status, received, insurance id, subscriber, other ins||service period||crossover
	 $clm_tbl_head = "<table class=\"era835\"><caption>Claims Detail</caption>
		<thead>
		  <tr align=left>
			<th>Status</th> <th>Received</th> <th>InsuranceID</th> 
			<th>Subscriber</th> <th colspan=2>Period|Other|COB</th> 		              
		  </tr>
		  <tr align=left>
			<th colspan=6>INS CO Information (if any)</th>
		  </tr>
		  <tr align=left>
			<th>Provider</th> <th>Claim Adjustment</th> <th>Amount Code</th>
			<th colspan=2>Corrections</th> <th>Remarks</th>
		  </tr>
		  <tr align=left>
			<th>Patient Name</th> <th>PatientID</th> <th>ClaimID</th> 	
			<th>FeeTotal</th> <th>Payment</th> <th>Pt Resp</th> 
		  </tr>
		  <tr align=left> 
			<th>SVC_Date</th> <th>Service</th> <th>Allowed</th> 
			<th>Fee</th> <th>Svc Pmt</th> <th>Pt Resp</th> 
		  </tr>
		  <tr align=left>
			<th>Provider</th>  <th colspan=5>Revisions</th>
		  </tr>
		  <tr align=left>
			<th colspan=2 align=left>Location</th> <th colspan=3 align=left>Service Codes</th>
			<th align=left>Remark Codes</th> 
		  </tr>
		</thead>".PHP_EOL;
	              
	  $bpr_tbl_head = "<table  class=\"era835\"><caption>Transaction Information</caption>
		<thead>
		  <tr align=center>
			<th>Date</th> <th>Payer</th> <th>Payer ID</th> 
			<th>Payer Source</th> <th>Payer Contact</th>
		  </tr>
		  <tr align=center>
			<th>Method</th> <th>CR/DB</th> <th>Total Pmt</th> <th>Trace</th> <th>&nbsp;</th>		              
		  </tr>
		</thead>".PHP_EOL;
	              
	  $plb_tbl_head = "<table class=\"era835\"><caption>Non-Claim Credit/Debit Information</caption>
		<thead>
		  <tr align=center>
			<th>Provider</th> <th>&nbsp;</th> <th>Date</th>
		   </tr>
		   <tr align=center>
			<th>Amount</th> <th>Identifier</th> <th>Text</th>  
		  </tr>
		</thead>".PHP_EOL;
         	              
	  if ($section == "HDR") {
		  return $hd_file_head;  
	  }          
	  if ($section == "CLM") { 
		  return $clm_tbl_head;
	  } 
	  if ($section == "BPR") {
		  return $bpr_tbl_head;
	  }
	  if ($section == "PLB") {
		  return $plb_tbl_head;
	  }	
	  if ($section == "TS3") { 
		  // this is a tough one - variable and used differently by payers
		  // indicated by LX, LX required whenever TS3 or sorted CLP
		  // the $fmt_ar is just the ts3 values array
		  $hdr_ar = array('ts3'=>'SegmentID', 'ref_id'=>'ReferenceID', 
		                  'facility_code'=>'Facility_Code', 'fiscal_per'=>'Fiscal Period', 
		                  'claim_ct'=>'Claim Count', 'chg_tot'=>'Charge Total', 
			              'chg_cvd'=>'Covered Charges', 'chg_noncvd'=>'Non-Covered Amt', 
			              'chg_denied'=>'Denied Amt', 'amt_prov'=>'Provider Amt', 
			              'amt_int'=>'Interest Amt', 'amt_adj'=>'Adjustment Amt', 
			              'amt_grr'=>'Gramm-Rudman', 'amt_msp'=>'MCR MSP Payer',
			              'chg_bld'=>'Blood Deductible','chg_nonlab'=>'Non-Lab Chrg', 
			              'chg_coins'=>'Coinsurance','chg_hcpcs_rpt'=>'HCPCS Reported',
			              'chg_hcpcs_pbl'=>'HCPCS Payable', 'amt_dedctbl'=>'Deductible',      
			              'amt_prof'=>'Professional Amt', 'amt_msp_pt'=>'MCR MSP Pt',    
			              'amt_reimb_pt'=>'Patient Reimbursment', 'pip_ct'=>'PIP Claims',
			              'pip_amt'=>'PIP Amount');
			              
              if (is_array($fmt_ar)) {
				$idx = 0; 
				$ts3_tbl_head = "<table class=\"era835\">
				  <caption>Claim Payment Summary</caption>
				  <thead>
                     <tr align=left>";
				foreach ($fmt_ar as $ky=>$val) {
					if (array_key_exists($ky, $hdr_ar ) ) {
						$ts3_tbl_head .= "<th>{$hdr_ar[$ky]}</th>";
						$idx++;
						if ($idx % 7 == 0) {$ts3_tbl_head .="</tr><tr align=left>";}
					}
				}
				$ts3_tbl_head .="</tr>
				   </thead>".PHP_EOL;
			} else {
				// no format array
				$ts3_tbl_head = "";
			}
			return $ts3_tbl_head;
		}	
  } 
 
/**
 * Create an html page to display selected information in an x12 835 claim remittance file
 * 
 * This function is called from function ibr_era_html_page() in this script. It calls 
 * function ibr_era_claim_EOB_heading_html() to generate table headings <thead></thead>
 * This function generates a complete web page content with html tags.  It relies on the 
 * ibr_era_claim_vals() function in this php file.  The parts of the x12 835 file are divided 
 * into seperate tables in the page (which could benefit by better design).  
 * The transaction summary (BPR key) is one table, the provider level payment (PLB key)
 * is another, and the claims detail has a claim group summary (TS3 key) and claim detail 
 * (CLP key).  The coded messages are collected and displayed in a table that is to be 
 * designated as the footer.
 * 
 * @param array $ar_clpvals   the array from ibr_era_claim_vals()
 * @param string $fname       optional - the filename of the x12 835 remittance advice
 * @param string $items       ALL, trace #, or claim id
 * @return string             body of html page 
 */
 function ibr_era_claim_html ($ar_clpvals, $fname = "835 Remittance Advice", $items = "ALL" ) {
	 // @param array $ar_clpvals -- from ibr_era_claim_vals
	 //
	 //$ar_clpvals['BPR']['key']
	 //$ar_clpvals['LX'][$lx_ct]['TS3']['key']
	 //$ar_clpvals['LX'][$lx_ct]['CLM'][$clp_ct]['key']
	 //$ar_clpvals['LX'][$lx_ct]['CLM'][$clp_ct]["SVC"][$svc_ct]['key']
	 //$ar_clpvals['PLB']['key'] 
	 // assign values in proper places
	 $bpr_html = '';
	 $plb_html = '';
	 //
	 $svc_adj_type = "";
	 $clm_adj_type = "";
	 $svc_adj_codes = "";  // store claim and service adjustment codes
	 $clm_adj_codes = "";
	 $clm_amt_codes = "";
	 $lq_rem_codes = "";	 	 
	 //
	 $dtl = "";
	 if ($items == "ALL") {
		 // we are rendering all the ST--SE in the file
		 $dtl = "ALL";
	 } else {
		 // a specific trace or claim
		 $dtl = (strlen($items)) ? $items : "Items not specified";
	 }
	 // The TBODY element defines a group of data rows in a table. 
	 // A TABLE must have one or more TBODY elements, which must follow the optional TFOOT. 
	 // The TBODY end tag is always optional.
	 // generate a heading with the file name -- move heading to ibr_history.php
	 //$clp_html = "<html>
	 //   <head>
	 //    <link rel=\"stylesheet\" href=\"jscript/style/csv_new.css\" type=\"text/css\" media=\"print, projection, screen\" />
	 //   </head>
	 $clp_html = "<h4>HTML Rendering of: $fname &nbsp;&nbsp; $dtl</h4>".PHP_EOL;
	             
	 // issue of proper quoting in html table text
	 // $clp_html is supposed to hold the entire html text of the claims table
	 // so that it can be displayed with simple echo $clp_html
	 //
	 //
	 //	
	 // Information on the transaction
	 if (array_key_exists("BPR", $ar_clpvals ) ) { 
		 $ar_bpr = $ar_clpvals['BPR'];
		 $clp_html .= ibr_era_claim_EOB_heading_html ("BPR");
		 //['total_pmt']['credit']['method']['payer_id']['date_pmt']['trace']['payer_name']['payer_source']['payer_contact'] 
         //['payer_tech']['payer_tech_contact']['rdm_trans_code']['rdm_name']['rdm_comm_num']
		 // to-do: colorize NON payments and Debits['payer_id_name']['payer_id_num']['payer_id_addr2']['payer_id_adr3']['payer_contact']
		 // 
		 // $clp_html .= isset($ar_bpr['date_pmt']) ? "<td>{$ar_bpr['date_pmt']}</td>" : "<td>&nbsp;</td>";
		 //   the isset() routine is needed to prevent php NOTICE warnings for missing keys
		 $clp_html .= "<tbody class=\"bpr\">".PHP_EOL;
		 $clp_html .= "<tr class=\"bpr\">".PHP_EOL;
		 $clp_html .= isset($ar_bpr['date_pmt']) ? "<td>{$ar_bpr['date_pmt']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_name']) ? "<td>{$ar_bpr['payer_name']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_id']) ? "<td>{$ar_bpr['payer_id']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_source']) ? "<td>{$ar_bpr['payer_source']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_contact']) ? "<td>{$ar_bpr['payer_contact']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= PHP_EOL."</tr>".PHP_EOL;
		 $clp_html .= PHP_EOL."<tr class=\"bpr\">".PHP_EOL;
		 $clp_html .= isset($ar_bpr['payer_id_num']) ? "<td>{$ar_bpr['payer_id_num']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_id_addr2']) ? "<td>{$ar_bpr['payer_id_addr2']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_id_addr3']) ? "<td>{$ar_bpr['payer_id_addr3']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_contact']) ? "<td>{$ar_bpr['payer_contact']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['payer_id_name']) ? "<td>{$ar_bpr['payer_id_name']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= PHP_EOL."</tr>".PHP_EOL;
		 if (isset($ar_bpr['payer_tech'])) {
			 $clp_html .= "<tr class=\"bpr\">".PHP_EOL;
			 $clp_html .= isset($ar_bpr['payer_tech']) ? "<td>{$ar_bpr['payer_tech']}</td>" : "<td>&nbsp;</td>";
			 $clp_html .= isset($ar_bpr['payer_tech_contact']) ? "<td colspan=4>{$ar_bpr['payer_tech_contact']}</td>" : "<td colspan=4>&nbsp;</td>";
		     $clp_html .= PHP_EOL."</tr>".PHP_EOL;
		 }
		 if (isset($ar_bpr['rdm_trans_code'])) {
			 $clp_html .= "<tr class=\"bpr\">".PHP_EOL;
			 $clp_html .= isset($ar_bpr['rdm_trans_code']) ? "<td>{$ar_bpr['rdm_trans_code']}</td>" : "<td>&nbsp;</td>";
			 $clp_html .= isset($ar_bpr['rdm_name']) ? "<td colspan=2>{$ar_bpr['rdm_name']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $clp_html .= isset($ar_bpr['rdm_comm_num']) ? "<td colspan=2>{$ar_bpr['rdm_comm_num']}</td>" : "<td colspan=2>&nbsp;</td>";
			 $clp_html .= PHP_EOL."</tr>".PHP_EOL;
		 }
		 $clp_html .= "<tr class=\"bpr\">".PHP_EOL;
		 $clp_html .= isset($ar_bpr['method']) ? "<td>{$ar_bpr['method']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['credit']) ? "<td>{$ar_bpr['credit']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['total_pmt']) ? "<td>{$ar_bpr['total_pmt']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= isset($ar_bpr['trace']) ? "<td colspan=2>{$ar_bpr['trace']}</td>" : "<td>&nbsp;</td>";
		 $clp_html .= PHP_EOL."</tr>".PHP_EOL;
		 $clp_html .= "</tbody>".PHP_EOL."</table>".PHP_EOL;
	 }
	 //
	 // check for PLB segments
	 //  PLB is a non-claim related financial transaction, such as Medicare bonus, withholdings, garnishment, etc.
	 if (array_key_exists("PLB", $ar_clpvals ) ) {
		 $clp_html .= ibr_era_claim_EOB_heading_html ("PLB");
		 $clp_html .= "<tbody id=\"plb\">";
		 foreach ($ar_clpvals["PLB"] as $plb) {
			  $clp_html .= "<tr align=left class=\"plb\">
			                  <td colspan=2>{$plb['provider']}</td>
			                  <td>{$plb['per_date']}</td>
			                </tr>";
	          foreach ( $plb['itm'] as $itm) {
				  $clp_html .= "<tr align=\"left\" class=\"plb\">   
						<tr>
						  <td>{$itm['amount']}</td>
						  <td>{$itm['identifier']}</td>
						  <td>{$itm['text']}</td>
						</tr>";
	              //['provider']['per_date']["identifier_$i"]["amount_$i"]["text_$i"]
			  }
			  $clp_html .= "
			     </tbody>
			     </table>".PHP_EOL;
		  }
	  }
	  // now the claims in a table
	  if (array_key_exists("LX", $ar_clpvals) ) { 

		  foreach($ar_clpvals["LX"] as $ar_lx) {
			 $lx_ts3 = '';
			 $lx_head = '';
			 $lx_body ='';
			 $lx_foot = '';			  
			 if (array_key_exists("TS3", $ar_lx) ) {
				 // there should be none or one TS3 per LX
				 $idx = 0;
				 //
				 $lx_ts3 = '';
				 $lx_ts3 .= ibr_era_claim_EOB_heading_html ("TS3", $ar_lx['TS3']);
				 $lx_ts3 .= "<tbody id='ts3'><tr class=\"ts3\">";
				 //
				 foreach ($ar_lx['TS3'] as $ts3) {
					 $lx_ts3 .= "<td>$ts3</td>";
					 $idx++;
					 //if ($idx % 7 == 0) { $clp_html .="</tr><tr bgcolor = #EECDA5>";}
					 if ($idx % 7 == 0) { $clp_html .="</tr><tr class=\"ts3\">";}
				 }
				 $lx_ts3 .= "</tr>
				  </tbody>
				  </table>".PHP_EOL; 
			  } // end if (array_key_exists("TS3"
			  
			  if (array_key_exists("CLM", $ar_lx) ) {
				  //
				  $lx_head .= ibr_era_claim_EOB_heading_html ("CLM");	 
				  // index to alternate background colors
				  $idx = 0;			  
				  // information on each claim, the $ar_clpvals['CLM']
				  foreach ($ar_lx['CLM'] as $val) {
					  //alternate background colors <tr bgcolor= {$bgc}>
					  $bgc = ($idx % 2 == 1 ) ? 'clp0' : 'clp1';
					  $idx++;
					  // //////////////////////////
					  // this is where the <tbody id='eraclp'> goes
					  //  so each claim detail will be in its own <tbody> tag 
					  $lx_body .= "<tbody class='eraclp'>".PHP_EOL;
					  //
					  if ( array_key_exists ("SVC", $val) && is_array($val["SVC"]) ) {
						  // one or more svc arrays
						  // create $svc_html now, then append to $lx_body
						  $svc_html = "";
						  //
						  foreach ($val["SVC"] as $sv) {
							  //
							  // row 6 in RA  date, code, allowed, fee, payment, pt resp
							  // get quantity difference, if any; service id is required element
							  $sv_idcode = isset($sv['svc_qty']) ? $sv['svc_adj_id'].' ('.$sv['svc_qty'].')' : $sv['svc_adj_id'];
							  //
							  $svc_html .= "<tr class=\"{$bgc}\">".PHP_EOL;
							  $svc_html .= isset($sv['svc_date']) ? "<td>{$sv['svc_date']}</td>" : "<td>&nbsp;</td>";
							  $svc_html .= isset($sv['svc_adj_id']) ? "<td>$sv_idcode</td>" : "<td>&nbsp;</td>";
							  $svc_html .= isset($sv['svc_amt_amt']) ? "<td>{$sv['svc_amt_amt']}</td>" : "<td>&nbsp;</td>";
							  $svc_html .= isset($sv['svc_fee']) ? "<td>{$sv['svc_fee']}</td>" : "<td>&nbsp;</td>";
							  $svc_html .= isset($sv['svc_pmt']) ? "<td>{$sv['svc_pmt']}</td>" : "<td>&nbsp;</td>";
							  $svc_html .= isset($sv['svc_pt_resp']) ? "<td>{$sv['svc_pt_resp']}</td>" : "<td>&nbsp;</td>";
							  $svc_html .= PHP_EOL."</tr>".PHP_EOL;
							  // 
							  // row 7 in RA to-do set only if different provider or service item was adjusted svc item, 
							  if (isset($sv['svc_code']) || isset($sv['prov_id_descr'])) { 
								  $svc_html .= "<tr class=\"{$bgc}\">".PHP_EOL;
								  // provider id different for this service
								  $svc_html .= isset($sv['prov_id_num']) ? "<td>{$sv['prov_id_descr']} {$sv['prov_id_num']}</td>" : "<td>&nbsp;</td>";
								  //submitted service replaced by adjudicated service
								  $svc_html .= isset($sv['svc_code']) ? "<td colspan=5>{$sv['svc_code']}</td>": "<td colspan=5>&nbsp;</td>";
								  $svc_html .= PHP_EOL."</tr>".PHP_EOL;
							  }
							  
							  // row 7 in RA location, adjustments, remarks
							  $svc_html .= "<tr class=\"{$bgc}\">";
							  if ( isset($sv['svc_loc']) ) {
								  $loc_val = ibr_era_code_text ("LOC", $sv['svc_loc_code'] );
								  $svc_html .= "<td colspan=2>{$sv['svc_loc']} {$sv['svc_loc_code']} {$loc_val[0][1]} </td>";
							  } else {
								  $svc_html .= "<td colspan=2>&nbsp;</td>";
							  }
							  // Service Adjustment Codes   added 'svc_adj_amt'
							  if (array_key_exists("svc_adj_code", $sv)) { 
								  $svc_adj_codes .= $sv['svc_adj_code'];
								  $svc_adj_type .= $sv['svc_adj_type'];
								  $svc_html .= "<td align=left colspan=3>&nbsp;<em>Svc Adj:</em>&nbsp; {$sv['svc_adj_html']}</td>";
							  } else {
								  $svc_html .= "<td colspan=3>&nbsp;</td>";
							  }
							  // service amount codes with claim amount codes svc_amt_code
							  if (array_key_exists('svc_amt_code', $sv)) {
								  $clm_amt_codes .= $sv['svc_amt_code'];
							  }
							  // LQ Health Care Remark codes          						  
							  if (array_key_exists("rem_code", $sv)) {
								  $lq_rem_codes .= $sv['rem_code'];
								  // $rem_val = ibr_era_code_text ("LOC", $sv['rem_code'] );
								  $svc_html .= "
									<td><em>HC Remark</em> {$sv['rem_code']}</td>";
							  } else {
								  $svc_html .= "
								    <td>&nbsp;</td>";
							  }							  
							  $svc_html .= "
								</tr>".PHP_EOL;
							
						  } // end foreach ($val["SVC"] as $sv)
						
					  } // end if ($ky == "SVC")
					  //
					  // /////////////////////
					  // reconfiguring the claim detail table
					  // change clm_html to lx_body in this section
			 
					  // the first line of the claim detail, mostly CLP
					  //       <th>Status</th> <th>Provider ID</th> <th>Subscriber</th>  == "4 Denied"
			          //       <th>Start</th> <th>End</th><th>COB Crossover</th> 	
			          // row 1 divider
			          $lx_body .= "<tr class=\"{$bgc}\">".PHP_EOL;
			          $lx_body .= "<td colspan=6>&nbsp; ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ &nbsp;</td>".PHP_EOL;
			          $lx_body .= "</tr>".PHP_EOL;  				 			  
					  // row 2 status, received, insurance id, subscriber, other ins||service period||crossover
					  $lx_body .= "<tr class=\"{$bgc}\">".PHP_EOL;
					  if ( isset($val['clm_status']) && strpos("|4 |22|", substr($val['clm_status'],0,2)) !== FALSE ) {
						  $lx_body .= isset($val['clm_status']) ? "<td class=\"denied\">{$val['clm_status']}</td>" : "<td>&nbsp;</td>";
					  } else {
						  $lx_body .= isset($val['clm_status']) ? "<td>{$val['clm_status']}</td>" : "<td>&nbsp;</td>";
					  }
					  $lx_body .= isset($val['clm_recieved']) ? "<td>{$val['clm_recieved']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['pt_ins_id']) ? "<td>{$val['pt_ins_id']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['sbr_last']) ? "<td>{$val['sbr_last']}, {$val['sbr_first']}</td>" : "<td>&nbsp;</td>";
					  //$lx_body .= isset($val['svc_date_per_begin']) ? "<td>{$val['svc_date_per_begin']}</td>" : "<td>&nbsp;</td>";
					  // deal with the case where ins co claims another is priority payer
					  if (isset($val['pr_priority']) ) {
						  $lx_body .= isset($val['pr_priority']) ? "<td class=\"denied\" colspan=2>{$val['pr_priority']} {$val['pr_priority_id']}</td>" : "<td colspan=2>&nbsp;</td>";
						  //$lx_body .= isset($val['pr_priority_id']) ? "<td class=\"denied\">{$val['pr_priority_id']}</td>" : "<td>&nbsp;</td>";
					  } elseif (isset($val['svc_date_per_begin']) )  {
						  $lx_body .= "<td colspan=2>{$val['svc_date_per_begin']} to {$val['svc_date_per_end']}</td>";
					  } else {
						  $lx_body .= isset($val['crossover_name']) ? "<td colspan=2>{$val['crossover_name']}</td>" : "<td colspan=2>&nbsp;</td>";
					  }
					  $lx_body .= PHP_EOL."</tr>".PHP_EOL;
					  // row 3  otherID, expired||reference, , payer source, payer contact
					  $lx_body .= "<tr class=\"{$bgc}\">".PHP_EOL;
					  //$lx_body .= isset($val['clm_oth_id_descr']) ? "<td>{$val['clm_oth_id_descr']}</td>" : "<td>&nbsp;</td>";
					  
					  if ( isset($val['ins_expired']) ) {
						  $lx_body .= "<td colspan=3 class=\"denied\">Expiration Date: {$val['ins_expired']}</td>";
					  } else {
						  //$lx_body .= isset($val['ref_description']) ? "<td>{$val['ref_description']}</td>" : "<td>&nbsp;</td>";
						  $lx_body .= isset($val['clm_oth_id_num']) ? "<td colspan=3>{$val['clm_oth_id_num']}</td>" : "<td colspan=3>&nbsp;</td>";
					  }
					  $lx_body .= isset($val['ref_value']) ? "<td>{$val['ref_value']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['payer_source']) ? "<td>{$val['payer_source']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['payer_contact']) ? "<td>{$val['payer_contact']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= PHP_EOL."</tr>".PHP_EOL;
					  // row 4  providerID, claimAdj, amount code, corrections, Remark Code
					  // here we want to add a row of possible code values
					  //  corrected name information 
					  $lx_body .= "<tr class=\"{$bgc}\">".PHP_EOL;
					  // 
					  $lx_body .= isset($val['provider_id']) ? "<td>{$val['provider_id']}</td>" : "<td>&nbsp;</td>";
					  //
					  // Claim Adjustment Codes  CAS segment at loop 2100 level 
					  if (array_key_exists("clm_adj_code", $val)) { 
					     // expect service adjustment code instead of remark code
					     // collect the codes so we can get the meaning later
					     // ['clm_adj_type']['clm_adj_code']['clm_adj_amt']['clm_adj_total']['clm_adj_qty']
					     // indicates some issue, highlight this with denied style
					     $svc_adj_codes .= $val['clm_adj_code'];
					     $clm_adj_type .= $val['clm_adj_type'];
					     $lx_body .= "<td align=right class=\"denied\"><em>Claim Adj</em> {$val['clm_adj_html']}</td>";
					  } else {						   					   
					     $lx_body .= "<td>&nbsp;</td>";
					  }
					  // add AMT segment information at claim level, if any
					  if (array_key_exists("clm_amt_code", $val)) { 
						  // collect values for table footer code text
						  $clm_amt_codes .= $val['clm_amt_code'];
						  $lx_body .= "<td>{$val['clm_amt_amt']}</td>"; 
					   } else {
						   $lx_body .= "<td>&nbsp;</td>";
					   }
					   // here we get the corrected names ['corr_last']['corr_first']['corr_mi']['corr_ins_id']
					   if (array_key_exists("corr_last", $val)) {
						   // corrected names are usually pointless, but perhaps there are times when they are useful
						   $corr_str = "";
						   if (strlen($val['corr_last'])) { $corr_str .= "<em>L &nbsp;</em>{$val['corr_last']}&nbsp;"; }
						   if (strlen($val['corr_first'])) { $corr_str .= "<em>F &nbsp;</em>{$val['corr_first']}&nbsp;"; }
						   if (strlen($val['corr_mi'])) { $corr_str .= "<em>MI &nbsp;</em>{$val['corr_mi']}&nbsp;"; }
						   if (strlen($val['corr_ins_id'])) { $corr_str .= "<em>ID &nbsp;</em>{$val['corr_ins_id']}"; }
						   $lx_body .= "
								<td colspan=2><em>Corrected: </em> $corr_str</td>";
					   } else {
						   $lx_body .= "
						      <td colspan=2>&nbsp;</td>";
					   }					   						     
					   // here we get the Claim Adjustment and Remittance Remark codes and text
					   if (array_key_exists("moa", $val)) {
						   // $ar_codes = ibr_era_code_text ("RA", $val['moa'] );
						   // collect the codes so we can get the meaning later
						   $ra_adj_codes .= $val['moa']; // save these for later 
						   $lx_body .= "
								<td align=center><em>Rem Code</em> {$val['moa']}</td>";
						} else {
						   $lx_body .= "<td>&nbsp;</td>";
					   } 
					   $lx_body .= PHP_EOL."</tr>".PHP_EOL; 
					  // row 5  provider, amountCode, claimAdjustment, corrections, Remark Codes 
					  $lx_body .= "<tr class=\"{$bgc}\">".PHP_EOL;
					  $lx_body .= isset($val['pt_last']) ? "<td>{$val['pt_last']}, {$val['pt_first']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['pid']) ? "<td>{$val['pid']}-{$val['enctr']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['clm_id']) ? "<td>{$val['clm_id']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['fee']) ? "<td>{$val['fee']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['pmt']) ? "<td>{$val['pmt']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= isset($val['ptresp']) ? "<td>{$val['ptresp']}</td>" : "<td>&nbsp;</td>";
					  $lx_body .= PHP_EOL."</tr>".PHP_EOL;
					  
					  
					   // row 6 MOA information, if any
					   if (array_key_exists("moa_amt", $val) && strlen($val['moa_amt']) > 0) {
						   // outpatient adjudication information, medicare claim adjustments, if applicable
						   // set these out in their own row
						   $lx_body .= "<tr>".PHP_EOL;
						   $lx_body .= "<td colspan=6>{$val['moa_amt']}</td>";
						   $lx_body .= PHP_EOL."</tr>".PHP_EOL;
					   }
					   //
					  // add the svc html created above
					  $lx_body .= $svc_html;	
					  // 
					  // done with the Claim detail table
					  // but we will append rows for codes and explanations
					  // //////////////////
					  // now we would close the <tbody>  $clp_html .= "</tbody>";
					  $lx_body .= "</tbody>".PHP_EOL; 
					  
				  } // end foreach ($ar_clpvals['CLM'] as $val)
				  //
				  // now rows of codes and explanations
				  //  /////////////////////
				  //  Note that we could end the claim detail table and begin this 'codes' table
				  //    This would be wrapped in the <tfoot> tag
				  //    It would have to be assigned to a different variable, like $foot_html
				  //    When the entire era array has been read, the output html is assembled
				  //    by concatenating <thead> <tfoot> and <tbody>, in that order
				  //  ///////////////
				  //$clp_html .= "</tbody>";
				  // the Amount Code values
				  $lx_foot = "<tfoot>".PHP_EOL;
				  if ($clm_amt_codes) {
					  $ar_amt_rem = ibr_era_code_text ("AMT", $clm_amt_codes );
					  $lx_foot .= "<tr class=\"code\"><td colspan=6>Amount Codes</td></tr>";
					  foreach ( $ar_amt_rem as $cd ) { 
						  $lx_foot .= "<tr class=\"code\"> 
							<td align=\"center\">{$cd[0]}</td>
							<td colspan=5>{$cd[1]}</td>
						  </tr>";
					  }
					  // reset for next group of claims
					  $clm_amt_codes = "";
					  //$lx_foot .= "</tbody>";
				  }				  
				  // print out the Remark and Adjustment code text
				  if ($lq_rem_codes) {
					  $ar_lq_rem = ibr_era_code_text ("RA", $lq_rem_codes );
					  $lx_foot .= "<tr class=\"code\"><td colspan=6>HC Remark Codes</td></tr>";
					  foreach ( $ar_lq_rem as $cd ) { 
						  $lx_foot .= "<tr class=\"code\"> 
							<td align=\"center\">{$cd[0]}</td>
							<td colspan=5>{$cd[1]}</td>
						 </tr>";
					  }
					  // reset
					  $lq_rem_codes = "";
					  //$lx_foot .= "</tbody>";
				  }
				  if ($clm_adj_type) { 
					  $ar_clm_type = ibr_era_code_text ("CAS", $clm_adj_type );
					  $lx_foot .= "<tr class=\"code\"><td colspan=6>Adjustment Type</td></tr>";
					  foreach ( $ar_clm_type as $tp ) { 
						  $lx_foot .= "<tr class=\"code\">
						     <td align=\"center\">{$tp[0]}</td>
						     <td colspan=5>{$tp[1]}</td> 
						   </tr>";
					  }
					  // reset 
					  $clm_adj_type = "";
					  //$lx_foot .= "</tbody>";	
				  }	
				  			   				  
				  if ($ra_adj_codes) {
					  $ar_clm_codes = ibr_era_code_text ("RA", $ra_adj_codes );
					  //
					  $lx_foot .= "<tr class=\"code\"><td colspan=6>Remark Codes</td></tr>";
					  foreach ( $ar_clm_codes as $cd ) { 
						  $lx_foot .= "<tr class=\"code\">
						    <td align=\"center\">{$cd[0]}</td>
						    <td colspan=5>{$cd[1]}</td> 
						  </tr>";
					   }
					   // reset 
					   $ra_adj_codes = "";
					   //$lx_foot .= "</tbody>";
				   }

				   if ($svc_adj_type) { 
					   $ar_svc_type = ibr_era_code_text ("CAS", $svc_adj_type );
					   $lx_foot .= "<tr class=\"code\"><td colspan=6>Adjustment Type</td></tr>";
					   foreach ( $ar_svc_type as $tp ) { 
						  $lx_foot .= "<tr class=\"code\">
						     <td align=\"center\">{$tp[0]}</td>
						     <td colspan=5>{$tp[1]}</td> 
						   </tr>";
					   }
					   // reset 
					   $svc_adj_type = "";
					   //$lx_foot .= "</tbody>";	
				   } 
				   
				   if ($svc_adj_codes) { 
					   $ar_svc_codes = ibr_era_code_text ("CLMADJ", $svc_adj_codes );
					   $lx_foot .= "<tr class=\"code\"><td colspan=6>Service Adjustment Codes</td></tr>";
					   foreach ( $ar_svc_codes as $cd ) { 
						  $lx_foot .= "<tr class=\"code\">
						    <td align=\"center\">{$cd[0]}</td>
						    <td colspan=5>{$cd[1]}</td> 
						   </tr>";
					   }
					   // reset 
					   $svc_adj_codes = "";
					   //$clp_html .= "</tbody>";
				   }
				   // supposedly ending tags are not required
				   $lx_foot .= "</tfoot>".PHP_EOL;
			   } // end if (array_key_exists("CLM", $ar_lx) )
			   // assemble claims detail table
			   $clp_html .= $lx_ts3;
			   $clp_html .= $lx_head;
			   $clp_html .= $lx_foot;
			   $clp_html .= $lx_body.PHP_EOL."</table>".PHP_EOL;
		   } // end foreach($ar_clpvals['LX'][lx_ct] as $ar_lx)
		   // finish table
		   //$clp_html .= "</body></html>"; 
		   //$clp_html .= "</tbody></table></body></html>";      
		} // end if (array_key_exists("LX", $ar_clpvals ) )
	  //
	  return $clp_html;
 }

/**
 * Identify the segments comprising an ST...SE transaction set and the trace number
 * 
 * @param array $ar_segments    array of segments from x12 835 file
 * @param string $e_delim       element delimiter
 * @return array                (start, count, trace) possibly more than one
 */         
 function ibr_era_st_slice_pos ( $ar_segments, $e_delim="*" ) {
	 // scan through the segments array
	 // identify the index on ST and the count to SE
	 // useful for array_slice function
	 $ar_st_pos = array();
	 $st_idx = 0;
	 $st_pos = -1;
	 //
	 foreach ( $ar_segments as $segtxt ) {
		 //
		 $st_pos++;	
		 if (strpos("|ST*|TRN|SE*", substr($segtxt,0, 3)) !== false) {
			 $seg = explode($e_delim, $segtxt);
		 } else {
			 continue;
		 }
		 
		 if ($seg[0] == "ST") { 
			 $ar_st_pos[$st_idx] = array( "$st_pos", 0, 0 ); 
		 }
		 
		 if ($seg[0] == "TRN") { 
			 $ar_st_pos[$st_idx][2] = $seg[2]; 
		 }
		 
		 if ($seg[0] == "SE") { 
			 $ar_st_pos[$st_idx][1] = $seg[1];      // SE01 = segment count
			 $st_idx++;								// increment array index
		 }
	 }
	 //
	 return $ar_st_pos;
 } 


/**
 * Identify the segments comprising a claim remittance advice
 * 
 * Either the pid or encounter are required
 * 
 * @param array $ar_segments   segments array for era file
 * @param string $pid_enc      pt control (pid-encounter) value we are searching for
 * @param string $e_delim      the element delimiter in the segments
 * @param string $searchtype   optional 'encounter' (default) or 'pid' if otherwise
 * @return array               [$i][start] [count]
 */
 function ibr_era_claim_slice_pos ( $ar_segments, $pid_enc, $e_delim='*', $searchtype='encounter' ) { 
	 //
	 // scan through segments array and get the positions of the claim
	 // for array_slice(ar, start, count)
	 //  note: there are instances of the same claim reported more than once
	 //        in an era file, e.g. reversal and restatement
	 //        also, one or more claims for same patient in a single file
	 // returns [$i](start, count)
	 //
	 if (!$pid_enc || !is_string($pid_enc)) {
		 csv_edihist_log("ibr_era_claim_slice_test: missing or invalid pid_encounter");
		 return FALSE;
	 }
	 $ar_clm_pos = array();
	 $clm_found = FALSE;
	 $end_str = "|SE$e_delim|LX$e_delim";
	 //
	 $clm_pos = -1;
	 $test_pos = 0;
	 $clm_idx = 0;
	 //
	 preg_match('/\D/', $pid_enc, $match2, PREG_OFFSET_CAPTURE);
	 if (count($match2)) {
		 $idar = csv_pid_enctr_parse($pid_enc);
		 if (is_array($idar) && count($idar)) {
			 $p = $idar['pid'];
			 $plen = strlen($p);
			 $e = $idar['enctr'];
			 $elen = strlen($e);
		 } else {	 
			 csv_edihist_log("ibr_era_claim_slice_test: error parsing pid_encounter $pid_enc");
			 return FALSE;
		 }
	 } else {
		 $p = trim($pid_enc);
		 $e = trim($pid_enc); 
		 $plen = strlen($p);
		 $elen = strlen($e);
	 }
	 //
	 $srchtype = ($searchtype == 'encounter') ? 'enc' : 'pid';	 
	 $ponly = ($srchtype == 'pid') ? true : false;
	 $eonly = ($srchtype == 'enc') ? true : false;
	 //
	 foreach ( $ar_segments as $segtxt ) {
		 //
		 //$seg = explode($e_delim, $segtxt);
		 $clm_pos++;

		if (substr($segtxt, 0, 3) == "CLP") {
		    $seg = explode($e_delim, $segtxt);
		    $idstr = $seg[1];
		    // we are at the next claim following the one we want
			if ($clm_found && $test_pos != $clm_pos) {
				$ar_clm_pos[$clm_idx]['count'] = $clm_pos - $ar_clm_pos[$clm_idx]['start'];
				$clm_idx++;
				$clm_found = FALSE;
			}
			// we are looking for a match
			if ($ponly && substr($idstr, 0, $plen) == $p ) {
				$idar = csv_pid_enctr_parse($idstr);
				if ($idar['pid'] == $p) {
					$ar_clm_pos[$clm_idx]['start'] = $clm_pos;
					$clm_found = TRUE;
					$test_pos = $clm_pos;
				}
			} 
			if ($eonly && substr($idstr, -$elen) == $e ) {
				$ar_clm_pos[$clm_idx]['start'] = $clm_pos;
				$idar = csv_pid_enctr_parse($idstr);
				if ($idar['enctr'] == $e) {
					$clm_found = TRUE;
					$test_pos = $clm_pos;
				}
			} 
		 }
		 // we are at an ending segment
		 if ($clm_found && strpos($end_str, substr($segtxt, 0, 3)) ) { 
			 // if claim is found
			 if ($test_pos != $clm_pos && isset($ar_clm_pos[$clm_idx]['start']) ) { 
				 $ar_clm_pos[$clm_idx]['count'] = $clm_pos - $ar_clm_pos[$clm_idx]['start'];
				 $clm_idx++;
				 $clm_found = FALSE;
			 }
		 }
	 }
	 //
	 return $ar_clm_pos;
 }
 
	 
/**
 * Generates an HTML table Remittance Advice of the contents of the 835 file
 * 
 * @uses csv_verify_file()
 * @uses csv_x12_segments()
 * @uses ibr_era_claim_slice_pos()
 * @uses ibr_era_st_slice_pos()
 * @uses ibr_era_claim_vals()
 * @uses ibr_era_claim_html()
 * 
 * @param string $file_path  the full path to the 835 .era file to be processed
 * @param int $trn_trace     optional trace number
 * @param string $pid_enctr  optional claim ID number CLM01
 * @param string $searchtype optional search ALL, Trace, Pid, Encounter
 * @param string $fname 	 actual file name, used with tmp/file names
 * @return string            html page
 */		
 function ibr_era_html_page ( $file_path, $trn_trace=0, $pid_enctr=0, $searchtype='ALL', $fname='835 Remittance Advice') {
	 // 
	 // divide the era x12 835 file into slices for transaction info and claims info
	 //
	 $ar_eob_str = "";
	 $is_found = FALSE;
	 $ar_era_segments = "";
	 $srchstr = "";
	 //
	 $f_path = csv_verify_file( $file_path, "era");
	 //
	 if (!$f_path) {
		 csv_edihist_log("ibr_era_html_page: failed verification $file_path");
		 return "ibr_era_html_page: failed verification for $file_path <br />" . PHP_EOL;
	 } else {
		 $fn = basename($f_path);
		 $ar_era_segments = csv_x12_segments($f_path, "era", FALSE);
		 if (!is_array($ar_era_segments) || !count($ar_era_segments['segments']) > 0 ) {
			return "ibr_era_html_page: failed to get segments for $f_path <br />" . PHP_EOL;
		}
	 }
	 //
	 if (strpos(strtolower($searchtype), 'enc') !== false) {
		 $srch = 'encounter';
	 } elseif (strpos(strtolower($searchtype), 'pid') !== false) {
		 $srch = 'pid';
	 } elseif (strpos(strtolower($searchtype), 'tra') !== false) {
		 $srch = 'trace';
	 } else {
		 $srch = 'all';
	 }
	 //
	 $ar_segs = $ar_era_segments['segments'];
	 $comp_d = $ar_era_segments['delimiters']['s'];
	 $elem_d = $ar_era_segments['delimiters']['e'];
	 
	 // select the kind of information we want claim, ST or file
	 if ( ($srch == 'pid' || $srch == 'encounter') && $pid_enctr) {
		 // we are looking for a specific claim
		 $srchstr = ($srch == 'pid') ? "PtID: $pid_enctr" : '';
		 $srchstr = ($srch == 'encounter') ? "Enctr: $pid_enctr" : $srchstr;
		 //
		 $ar_clp = array();
		 $ar_clp_slice = ibr_era_claim_slice_pos ($ar_segs, $pid_enctr, $elem_d, $srch);
		 //
		 if ( !empty($ar_clp_slice) ) {
			 foreach($ar_clp_slice as $cs) {
				// because an encounter can be reported more than once in an era file
				if (count($cs) == 2) { 
					// clp_segs is an array of the CLP segments block for the claim (loop 2100 and 2110)
					// we simply slice out the claim segments and append each segment to $ar_clp
					$clp_segs = array_slice($ar_segs, $cs['start'], $cs['count']);
					foreach($clp_segs as $clp) {
						$ar_clp[] = $clp;
					}
				}
			 }
		 } else {
			 // no segments found for claim
			 $ar_eob_str = "Claim $pid_enctr not found in $fn";
			 csv_edihist_log("ibr_era_html_page: Claim $pid_enctr not found in $fn");
		 }
		 // segments were found, so use the segments in $ar_clp
		 if (!empty($ar_clp) ) {
			 // ar_clp will just be an array of segments for the claim(s) ar_clp[0]=CLP* ... etc
			 $ar_eob_vals = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d);
			 
			 $ar_eob_str .= ibr_era_claim_html ($ar_eob_vals, $fname, $srchstr );
			 // 
		 } else {
			 $ar_eob_str = "Claim $srchstr not found in $fn";
			 csv_edihist_log("ibr_era_html_page: Claim $srchstr not found in $fn");
		 }
	 } else {
		 // either the entire era file or a specific transaction	  
		 // gives us the extents if the ST-SE envelope -- one per check
		 $ar_st_slices = ibr_era_st_slice_pos ($ar_segs, $elem_d);
		 //
		 if (!$trn_trace) { 
			 // if the entire file is requested, then trn_trace should be 0 , i.e. not given
			 foreach ($ar_st_slices as $st) {
				 //
			 	 $ar_clp = array_slice($ar_segs, $st[0], $st[1]);
				 $ar_eob_vals = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d );
				 $ar_eob_str .= ibr_era_claim_html ($ar_eob_vals, $fname, "ALL Items" );
				 //
				 csv_edihist_log("ibr_era_html_page: era HTML output for $file_path");
			 }
		 } else {
			 // a specific transaction trace is requested
			 foreach ($ar_st_slices as $st) {
				 if ($st[2] == $trn_trace) { 	 
					 //
					 $is_found = TRUE;
					 $ar_clp = array_slice($ar_segs, $st[0], $st[1]);
					 $ar_eob_vals = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d);
					 $ar_eob_str .= ibr_era_claim_html ($ar_eob_vals, $fname, "Trace: $trn_trace" );
					 //
					 csv_edihist_log("ibr_era_html_page: HTML output for $trn_trace in $fn");
					 //
					 break;
				 } else {
					 // there may be trace numbers in ar_st_slices that do not match the requested one
					 continue;
				 }
			 } // end foreach ($ar_st_slices as $st)
			 if (!$is_found) {
			 	  // error -- trace number was not found in era file
				  csv_edihist_log("ibr_era_html_page: trace $trn_trace not found in era file $fn TMP: $file_path");
				  $ar_eob_str = "<p>Trace $trn_trace not found in $fn TMP: $file_path.</p>";
			 }
		 }
	 }
	 if (!$ar_eob_str) {
		 $ar_eob_str = "<p>ibr_era_html_page: failed to return anything for Trace $trn_trace Claim $claim_id in $file_path</p>";
		 csv_edihist_log("ibr_era_html_page: failed to return anything for Trace $trn_trace Claim $claim_id in $file_path");
	 }
	 return $ar_eob_str;
 }		 	 

 /**
  * function ibr_era_data_array() returns the array created in function ibr_era_claim_vals()
  * 
  * @uses csv_x12_segments()
  * @uses ibr_era_claim_slice_pos() 
  * @uses ibr_era_st_slice_pos()
  * @uses ibr_era_claim_vals() 
  *
  * @param string $file_path    full path to x12 835 era file
  * @param string $trn_trace    trace number for deposit transaction
  * @param string $pid          the pid, patient account identifier
  * @param string $enctr        the encounter number
  * @return array 
  */
 function ibr_era_data_array ($file_path, $trn_trace=0, $pid=0, $enctr=0) {
 	// simply run the file through the function ibr_era_claim_vals ( $ar_st_slice ) 
 	// and return the multi-dimensional array 
 	//
 	// a duplicate of the ibr_era_html_page() html page function
 	// without the html output
	 // get segments, path, and delimiters
	 $ar_era_segments = csv_x12_segments($file_path, "era", FALSE);
	 if (!is_array($ar_era_segments) || count($ar_era_segments['segments']) == 0 )  {
		 csv_edihist_log("ibr_era_data_array: no segments for $file_path");
		 return FALSE;
	 }
	 $fname = basename($file_path);
	 //
	 $ar_segs = $ar_era_segments['segments'];
	 $comp_d = $ar_era_segments['delimiters']['s'];
	 $elem_d = $ar_era_segments['delimiters']['e'];	
	 // 
	 // select the kind of information we want claim, ST or file
	 if ($pid || $enctr) {
		 // we are looking for a specific claim
		 $ar_clp = array();
		 $ar_clp_slice = ibr_era_claim_slice_pos ($ar_segs, $pid, $enctr, $elem_d);
		 //
		 if ( !empty($ar_clp_slice) ) {
			 foreach($ar_clp_slice as $cs) {
				// because an encounter can be reported more than once in an era file
				//
				if (count($cs) == 2) { 
					// clp_segs is an array of the CLP segments block for the claim (loop 2100 and 2110)
					$clp_segs = array_slice($ar_segs, $cs['start'], $cs['count']);
					foreach($clp_segs as $clp) {
						$ar_clp[] = $clp;
					}
				}
			 }
		 } else {
			 csv_edihist_log("ibr_era_data_array: Claim $claim_id not found in $fname");
			 return FALSE;
		 }
		 // segments for the claim were found
		 if (!empty($ar_clp) ) {
			 // ar_clp will just be an array of segments for the claim(s) ar_clp[0]=CLP* ... etc
			 $ar_eob_vals = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d);
			 // 
		 } else {
			 csv_edihist_log("ibr_era_data_array: Claim $claim_id not found in $fname");
			 return FALSE;
		 }
	 } else {
		 // either the entire era file or a specific transaction	  
		 // gives us the extents if the ST-SE envelope -- one per check
		 $ar_st_slices = ibr_era_st_slice_pos ($ar_segs, $elem_d);
		 //
		 $ar_eob_vals = array();
		 //
		 if (!$trn_trace) { 
			 // if the entire file is requested, then trn_trace should be 0 , i.e. not given
			 
			 foreach ($ar_st_slices as $st) {
				 //
			 	 $ar_clp = array_slice($ar_segs, $st[0], $st[1]);
				 $ar_eob_vals[] = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d );
				 //
				 csv_edihist_log("ibr_era_data_array: array output for $fname");
			 }
		 } else {
			 // a specific transaction trace is requested
			 foreach ($ar_st_slices as $st) {
				 if ($st[2] == $trn_trace) { 	 
					 //
					 $is_found = TRUE;
					 $ar_clp = array_slice($ar_segs, $st[0], $st[1]);
					 $ar_eob_vals[] = ibr_era_claim_vals($ar_clp, $elem_d, $comp_d);
					 //
					 csv_edihist_log("ibr_era_data_array: array output for $trn_trace in $fname");
					 //
					 break;
				 } else {
					 // there may be trace numbers in ar_st_slices that do not match the requested one
					 continue;
				 }
			 } // end foreach ($ar_st_slices as $st)
			 if (!$is_found) {
			 	  // error -- trace number was not found in era file
				  csv_edihist_log("ibr_era_data_array: trace $trn_trace not found in era file $fname");
				  return FALSE;
			 }
		 }
	 }
	 if ( !isset($ar_eob_vals) || empty($ar_eob_vals) ) {
		 csv_edihist_log("ibr_era_data_array: failed to generate array for params: File: $fname Trace $trn_trace Claim $claim_id");
		 return FALSE;
	 }	 
	 return $ar_eob_vals;
 }		 

 	
 /* =================================
  * CSV records functions
  *  these retrieve a list of new files (not in the .csv file table)
  *  and create arrays of data for writing to the .csv files
  *  also, a function to find which file contains a transaction trace
  */
  
/**
 * Parse x12 835 segments for selected data for csv files
 * 
 * This is like a summary of the information. the returned array has two keys
 * <pre>
 *  $ar_csv[$st_ct]['file']
 *    'mtime' 'fname' 'trace' 'payer' claimcount rej_count
 *  $ar_csv[$st_ct]['claim'][$clp_ct]
 *  'name''svc_date''pid''enctr''status''fee''pmt''pt_resp''trace''erafile''claim_id''payer'
 * </pre>
 * @param array $era_segments  the segments array from ibr_era_process_new()
 * @return array               described above
 */
 function ibr_era_csv_file_data ( $era_segments ) { 
	 // 
	 $era_dir = dirname($era_segments['path']);
	 $era_fname = basename($era_segments['path']);
	 $ar_era_segments = $era_segments['segments'];
	 $elem_d = $era_segments['delimiters']['e'];
	 //
	 //
	 $era_mtime = date ("Ymd", filemtime($era_segments['path']));
	 //
     $denied = 0;
     $has_ts3 = FALSE;
     //
 	 $ar_csv = array();
	 $C = -1;  // use for CLP count index
	 $S = -1;  // use for ST count index
	 $st_seg_ct = 0;
	 //
	 foreach($ar_era_segments as $segtxt) {
		//
		$seg = explode($elem_d, $segtxt);
		//
		$st_seg_ct++;
		// check for loops and set counters
		if ($seg[0] == "ST") { 
			$loopid = "0";
			$st_seg_ct = 1;  
			$S++; 
			$C = -1;
			//$ar_csv[$S]['claim'] = array();
			//$ar_csv[$S]['file'] = array();
			$denied = 0;
		}
		//
		if ($seg[0] == "CLP") { 
			$loopid = "2100"; 
			$C++;
		}
		//
		if ($seg[0] == "SVC") { $loopid = "2110"; }
		if ($seg[0] == "PLB") { $loopid = "0"; $C++; }
		if ($seg[0] == "SE") { $loopid = "0"; }
		//
		// now look at segments for data
		if ($seg[0] == "GS") { 
			$gs_date = $seg[4]; 	// functional group creation date -- required element
			continue;
		}
		if ($seg[0] == "BPR") {
			$pmt_date = $seg[16]; 	// settlement date per payer -- required element
			continue;
		}
		//
		if ($seg[0] == "TRN") {
			$trn_trace = strval($seg[2]); 	// trace number/check number -- required element
			continue;
		}
		//
		if ($seg[0] == "N1" && $seg[1] == "PR") { 
			$payer_name = $seg[2]; 											  // payer name -- required element
			if ( isset($seg[3]) && $seg[3] == "XV" ) { $payer_id = $seg[4]; } // national plan id payer id -- situational
			continue;
		}	
		//
		if ($seg[0] == "N1" && $seg[1] == "PE") { 
			$prov_name = $seg[2]; 	// provider name -- required element
			continue;
		}
        if ($seg[0] == "TS3" && $trn_ct > -1 ) {
			 // number of claims is in TS304
			 $ar_csv[$S]['file']['claims'] = $seg[4];             // 'claims'
			 $has_ts3 = TRUE;
		}		
		//
		// OK, now we are getting into the claim remittance advice
		//
		if ($seg[0] == "CLP") {
			//	
			$ar_csv[$S]['claim'][$C]['fee'] = sprintf("%01.02f", $seg[3]);
            //
            //$inv_split = preg_split('/\D/', $seg[1], 2, PREG_SPLIT_NO_EMPTY);
            $inv_split = csv_pid_enctr_parse($seg[1]);
            //
            $ar_csv[$S]['claim'][$C]['pid'] = $inv_split['pid'];     	// 'pid'
            $ar_csv[$S]['claim'][$C]['enctr'] = $inv_split['enctr'];    // 'enctr'
            //
            $ar_csv[$S]['claim'][$C]['clm01'] = $seg[1];
            //
            $ar_csv[$S]['claim'][$C]['status'] = $seg[2];
            // increment the denied count if status is 4
            if($seg[2]=='4' || $seg[2]=='22' || $seg[2]=='23') { $denied++; }
            //
            $ar_csv[$S]['claim'][$C]['status'] = $seg[2];             // 'status'
            //
            $ar_csv[$S]['claim'][$C]['fee'] = sprintf("%01.02f", $seg[3]);        // 'fee'
            $ar_csv[$S]['claim'][$C]['pmt'] = sprintf("%01.02f", $seg[4]);        // 'pmt'
            if ( isset($seg[4]) ) { 
				$ar_csv[$S]['claim'][$C]['pt_resp'] = sprintf("%01.02f", $seg[5]);    // 'pt_resp'
            } else {
				$ar_csv[$S]['claim'][$C]['pt_resp'] = '';
            }
            //
            if ( isset($seg[7]) ) { 
				$ar_csv[$S]['claim'][$C]['claim_id'] = trim($seg[7]);        //'claim_id'
            } else {
				$ar_csv[$S]['claim'][$C]['claim_id'] = '';
            }             
            //['clm01']['status']['fee']['pmt']['pt_resp']['trace']['payer']['erafile']['svc_date']['name']
                        
            $ar_csv[$S]['claim'][$C]['trace'] = $trn_trace;
            $ar_csv[$S]['claim'][$C]['payer'] = $payer_name;
            $ar_csv[$S]['claim'][$C]['erafile'] = $era_fname;         //'erafile'    
			//
			$lastn = '';
			$firstn = '';
			$midn = '';
			continue;	
		 }
		 //
		if ($seg[0] == "DTM") { 
			// get the from-to dates, but replace with the service date
			// 232 service from date  233 service to date
			// 036 coverage expiration date -- claim denied due to coverage lapse  -- look to era rendering
			if ($loopid == "2100" && $seg[1] == "232") { $ar_csv[$S]['claim'][$C]['svc_date'] = $seg[2]; }
			if ($loopid == "2100" && $seg[1] == "233") { $ar_csv[$S]['claim'][$C]['svc_date'] = $seg[2]; }
			// 472 service date 
			if ($loopid == "2110" && $seg[1] == "472") { $ar_csv[$S]['claim'][$C]['svc_date'] = $seg[2]; }
			//
			continue;
		 }
		 //
		 if ($seg[0] == "NM1" && $loopid == "2100") {
			// QC patient, IL insured, 74 corrected, 82 rendering provider, TT crossover carrier, PR payer, GB other insured
			// Try to catch corrected names by inserting CC: before the corrected part 
			// (seems to be Medicare practice to only give the corrected part in the NM1*74 segment, however, they have it wrong more often than we do)
			// (also, other payers are not consistent or give the full name repeated)
			if ($seg[1] == "QC") { 
				$lastn = $seg[3];
				$firstn =  ', ' . $seg[4];
				$midn = (isset($seg[5]) && strlen($seg[5])) ? ', ' . $seg[5]: "";
				//
				$ar_csv[$S]['claim'][$C]['name'] = $lastn . $firstn . $midn; 
			}
			/* **** correction infoprmation is in RA and summary
			if ( $seg[1] == "74" ) {
				if (strlen($seg[3]) && $lastn != $seg[3]) { $lastn = 'CC:' . $seg[3]; }
				if (strlen($seg[4]) && $firstn != $seg[4]) { $firstn = ', CC:' . $seg[4]; }
				if (strlen($seg[5]) && $midn != $seg[5]) { $midn = ', CC:' . $seg[5]; }
				//
				$ar_csv[$S]['claim'][$C]['name'] = $lastn . $firstn . $midn; 
			}
			* ***/
			continue;
		 }
		 //
		 //claims_era.csv array('name', 'svc_date', 'pid', 'enctr', 'status', 'fee', 'pmt', 'pt_resp', 'trace', 'erafile', 'claim_id', 'payer');
		 //['era']['claim'] =   array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'File_835', 'claimID', 'Pmt', 'PtResp', 'Payer');
		 if ($seg[0] == "PLB") {
			 // payment adjustments to transaction -- just fit it into claim array
			 // break down PLB03 -- not used
			 if (strpos($seg[3], $comp_d)) {
				 $plb03 = explode($comp_d, $seg[3]);
				 $ptxt_ar = ibr_era_code_text('PLB', $plb03[0]);
				 $ptxt = $ptxt_ar[1];
				 $plbid = $plb03[1];
			 }
			 $ar_csv[$S]['claim'][$C]['name'] = "PLB";
			 $ar_csv[$S]['claim'][$C]['svc_date'] = $seg[2];                   // date
			 $ar_csv[$S]['claim'][$C]['clm01'] = $seg[1];                      // provider id
			 $ar_csv[$S]['claim'][$C]['status'] = ($seg[4] <= 0) ? "C" : "D";  // negative amount is a payment, positive is reduction
			 $ar_csv[$S]['claim'][$C]['fee'] = $seg[3];                        // adjustment identifier
			 $ar_csv[$S]['claim'][$C]['pmt'] = sprintf("%01.02f", $seg[4]);    // monetary amount
			 $ar_csv[$S]['claim'][$C]['pt_resp'] = isset($seg[5]) ? $seg[5] : "";  // adjustment identifier
			 $ar_csv[$S]['claim'][$C]['claim_id'] = isset($seg[6]) ? sprintf("%01.02f", $seg[6]) : "";  // monetary amount
             $ar_csv[$S]['claim'][$C]['trace'] = $trn_trace;
             $ar_csv[$S]['claim'][$C]['payer'] = $payer_name;
             $ar_csv[$S]['claim'][$C]['erafile'] = $era_fname; 
             //
             continue;	
		 }		 
		 // OK, the claim is done.  get the file information at the SE segment, end of transatcion set
		 // $ar_csv['file'][$trn_ct]
		 //   'mtime' 'fname' 'trace' 'payer' claims rej_count
		 if ($seg[0] == "SE") {
			if ($seg[1] != $st_seg_ct) {
				csv_edihist_log ("ibr_era_csv_file_data: segment count mismatch $era_fname SE: {$seg[2]} count: $st_seg_ct");
			}
			//
			$ar_csv[$S]['file']['mtime'] = isset($pmt_date) ? $pmt_date : $gs_date;
			$ar_csv[$S]['file']['fname'] = $era_fname;
			$ar_csv[$S]['file']['trace'] = $trn_trace;
			$ar_csv[$S]['file']['payer'] = $payer_name;
			$ar_csv[$S]['file']['payer_id'] = isset($payer_id) ? $payer_id : '';
			$ar_csv[$S]['file']['claims'] = sprintf('%u', $C+1);
			$ar_csv[$S]['file']['rej_count'] = sprintf('%u', $denied);
			continue;
		 }
		//

	 } // end foreach($segments array)
	 // 
	 return $ar_csv;
 } // end function ibr_era_file_csv 


/**
 * Write the csv data to the csv files
 * 
 * @uses csv_write_record()
 * @see ibr_era_csv_file_data()
 * @param array $file_csv_array -- array produced by ibr_era_csv_file_data()
 * @return array                  characters written to files.csv and claims.csv
 */
function ibr_era_csv_write_data ( $file_csv_array, &$str_err ) {
	// 
	//
	// here we order the data for the csv tables 
	// expect $file_csv_array[i]['file'] and $file_csv_array[i]['claim'][j]
	//
	$csvf = array();
	$csvc = array();
	$chrsf = 0;
	$chrsc = 0;
	//
	foreach($file_csv_array as $file_data) {
		//$csv_hd_ar['era']['file'] =   array('Date', 'FileName', 'Trace', 'claim_ct', 'Denied', 'Payer');
		if (array_key_exists('file', $file_data) && count($file_data['file']) > 0 ) {
			//
			$csvf[0] = $file_data['file']['mtime'];
			$csvf[1] = $file_data['file']['fname'];
			$csvf[2] = $file_data['file']['trace'];
			$csvf[3] = $file_data['file']['claims'];
			$csvf[4] = $file_data['file']['rej_count'];
			$csvf[5] = $file_data['file']['payer'];
			//
			$ar_eraf[] = $csvf;
			// if an error in writing, we will have the file names
			$str_err .= $arw['file_name'].PHP_EOL;
		}
		//['era']['claim'] =   array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'File_835', 'claimID', 'Pmt', 'PtResp', 'Payer');
		//
		if (array_key_exists('claim', $file_data) && count($file_data['claim']) > 0 ) {
			// 
			foreach($file_data['claim'] as $clm) {
				//
				$csvc[0] = $clm['name'];
				$csvc[1] = $clm['svc_date'];
				$csvc[2] = $clm['clm01'];
				$csvc[3] = $clm['status'];
				$csvc[4] = $clm['trace'];
				$csvc[5] = $clm['erafile'];
				$csvc[6] = $clm['claim_id'];
				$csvc[7] = $clm['pmt'];
				$csvc[8] = $clm['pt_resp'];
				$csvc[9] = $clm['payer'];
				//
				$ar_erac[] = $csvc;
				//
			}
		}
	}
	$chrsf += csv_write_record($ar_eraf, 'era', 'file');		
	$chrsc += csv_write_record($ar_erac, 'era', 'claim');
	//
	return array($chrsf, $chrsc);
	//
}

/** 
 * generates html for the uploaded new files output
 * 
 * @param array $trn_csv_array -- the array of csv data generated in ibr_era_csv_file_data()
 * @param bool $err_only -- whether to report claim information only in denied case
 */
function ibr_era_csv_files_html ($trn_csv_array, $err_only=TRUE  ) {
	//
	$dtl = ($err_only) ? "Errors only" : "All included claims";
	//
    $htm_tplcap = "<table class=\"eracsv\" cols=6><caption>ERA Files Summary $dtl</caption>";
    $htm_tpl = "<table class=\"eracsv\" cols=6>";
	$htm_hdrf = "<thead>
         <tr>
         <th>File Time</th><th>File Name</th><th>Trace</th>
         <th>Payer</th><th>Claims</th><th>Denied</th>
         </tr>
         </thead>
         <tbody>";
    //
    // claims detail -- expected to be only for denied claims
    $htm_hdrc = "<table class=\"eracsv\" cols=6>
		<thead>
	      <tr class=\"clperr\">
	      <th>Name</th><th>Svc Date</th><th>Account</th><th>Status</th>
	      <th>Fee|Pmt|PtResp</th><th>Claim ID</th>
	      </tr>
	     </thead>";
	 //
	 $out_html = $htm_tplcap.$htm_hdrf;	
	 //
	 $idxc = 0;  // if denied claims are output for $err_only
	 $haserr = 0;
	 // now create the table body
	 // $trn_csv_array structure is $trn_csv_array[f#][S#]['file']  $trn_csv_array[f#][S#]['claim'][C#]  
	 //   so one 'file' and multiple 'claim' per ST-SE block (the S#)
	 foreach($trn_csv_array as $fdata) {
         // one fdata for each file processed
         // one ardata for each transaction trace
         $idx = 0;
         $idxc = 0;
         foreach($fdata as $ardata) {
            $file_html = "";
            $clm_str = "";
            //
            if (array_key_exists('file', $ardata) && count($ardata['file']) > 0 ) {
                // $ar_csv_data['file'] 'mtime' 'fname' 'trace' 'payer' claims rej_count
                //
                // if a denied claim occurred in the preceding iteration, insert the table heading
                if ($haserr) { 
                    $out_html .= $htm_tpl.$htm_hdrf;
                    $haserr = 0;
                }
                $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even'; 
                $idx++;
                //
                $file_html .= "<tr class='{$bgc}' >
                <td>{$ardata['file']['mtime']}</td>
                <td><a target='_blank' href='edi_history_main.php?fvkey={$ardata['file']['fname']}'>{$ardata['file']['fname']}</a></td>
                <td><a target='_blank' href='edi_history_main.php?erafn={$ardata['file']['fname']}&trace={$ardata['file']['trace']}'>{$ardata['file']['trace']}</a></td>
                <td>{$ardata['file']['payer']}</td>
                <td>{$ardata['file']['claims']}</td>
                <td>{$ardata['file']['rej_count']}</td>
                </tr>";
            }
            //
            if (array_key_exists('claim', $ardata) && count($ardata['claim']) > 0 ) {
                //
                $haserr = 0;
                $clm_str = "";
                //			
                foreach ($ardata['claim'] as $clm) {		
                    // output err_only is when claim status is a rejected type or payment amount is 0
                    if ($err_only && (!in_array($clm['status'], array('4', '22', '23')) && intval($clm['pmt']) > 0)) {
                       continue; 
                    }
                    //
                    $haserr++;
                    $bgclm = ($haserr % 2 == 1 ) ? 'fodd' : 'feven'; 
                    //array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'claimID', 'File_835', 'Pmt', 'PtResp', 'Payer');
                    //['clm01']['status']['fee']['pmt']['pt_resp']['trace']['payer']['erafile']['svc_date']['name']
                    $clm_str .= "
                    <tr class='{$bgclm}'>
                        <td>{$clm['name']}</td>
                        <td>{$clm['svc_date']}</td>
                        <td>{$clm['clm01']}</td>
                        <td>{$clm['status']} &nbsp;<a class='clmstatus' target='_blank' href='edi_history_main.php?erafn={$clm['erafile']}&pidenc={$clm['clm01']}&summary=yes'>S</a>&nbsp; <a target='_blank' href='edi_history_main.php?erafn={$clm['erafile']}&pidenc={$clm['clm01']}'>RA</a></td>
                        <td>{$clm['fee']} | {$clm['pmt']} | {$clm['pt_resp']}</td>
                        <td>{$clm['claim_id']}</td>
                    </tr>";
                }
            }
            // we have run through the set 'file' and 'claim'
            //
            $out_html .= $file_html;
            // if we have denied claims, insert the string
            if ($haserr) {
                $out_html .= $htm_hdrc;
                $out_html .= $clm_str;
                $out_html .= "</tbody></table>";
            }
         }
     } // end foreach($trn_csv_array as $ardata)
	 //
	 //finish off the table
	 $out_html .= "</tbody></table>";
	 //
	 return $out_html;
}


/**
 * Search claims_era.csv table and find patient ID or encounter and associated filename
 * 
 * will search for only the pid value or only encounter value
 * 
 * @uses csv_pid_enctr_parse()
 * @uses csv_parameters()
 * @param string $encounter   the patient control (pid-encounter) number
 * @param string $srchtype    default is 'encounter'
 * @return array              [i](pid-enctr, trace, filename) possibly more than one
 */
 function ibr_era_find_file_with_pid_enctr ($pid_enctr, $srchtype='encounter' ) { 
	 // 
	 // return the pid_encounter, trace, and filename, but there may be more than one file, so return an array
	 //	
	 if (!$pid_enctr) {
		 return "invalid encounter data<br />" . PHP_EOL;
	 }
	 $enctr = trim($pid_enctr);
	 preg_match('/\D/', $enctr, $match2, PREG_OFFSET_CAPTURE);
	 if (count($match2)) {
		 $idar = csv_pid_enctr_parse($enctr);
		 if (is_array($idar) && count($idar)) {
			 $p = strval($idar['pid']);
			 $plen = strlen($p);
			 $e = strval($idar['enctr']);
			 $elen = strlen($e);
		 } else {	 
			 csv_edihist_log("ibr_era_find_file_with_pid_enctr: error parsing pid_encounter $pid_enctr");
			 return FALSE;
		 }
	 } else {
		 $p = strval($enctr);
		 $e = strval($enctr); 
		 $plen = strlen($p);
		 $elen = strlen($e);
	 }
	 $ret_ar = array();
	 //array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'claimID', 'File_835', 'Pmt', 'PtResp', 'Payer');
	 //
	 $params = csv_parameters('era');
	 $fp = $params['claims_csv'];
	 //
	 if (($fh1 = fopen($fp, "r")) !== FALSE) {
		 if ($srchtype == 'encounter') {
			 while (($data = fgetcsv($fh1, 1024, ",")) !== FALSE) {
				 // check for a match
				 if (substr($data[2], -$elen) == $e) { 
					 // since e=123 will match 1123 and 123
					 $peval = csv_pid_enctr_parse($data[2]);
					 if (is_array($peval) && count($peval)) {
						 if ($peval['enctr'] == $e) {
							 $ret_ar[] = array($data[2], $data[4], $data[5]);
						 }
					 }
				 }
			 }
		 } else {
			 while (($data = fgetcsv($fh1, 1024, ",")) !== FALSE) {
				 // check for a match
				 if (substr($data[2], 0, $plen) == $p) { 
					 // since p=123 will match 1123 and 123
					 $peval = csv_pid_enctr_parse($data[2]);
					 if (is_array($peval) && count($peval)) {
						 if ($peval['pid'] == $p) {
							 $ret_ar[] = array($data[2], $data[4], $data[5]);
						 }
					 }
				 }
			 }
		 }			 
		 fclose($fh1);
	 } else {
		 csv_edihist_log("ibr_era_find_file_with_pid: failed to open file claims_era.csv");
	 }
	 return $ret_ar;
 }	 
	 

/**
 * copy the substring of text containing claim payment information for a claim
 * 
 * The filename must be found in the files.csv table, i.e. previously processed
 * 
 * @uses csv_verify_file()
 * @uses csv_x12_delimiters()
 * @param string $clp_clm_num  the pid-encounter value
 * @param string $era_file     the filename
 * @return string 			   newline characters are added to each segment end
 */
 function ibr_era_get_clp_text ( $clp_clm_num, $era_file, $html_out=true ) {
	// @param string $clp_clm_num -- CLP01 value, pid-encounter
	// @param string $era_file path to 835 file containing the $clp_clm_num
	// segment block CLP to CLP, SE, LX
	// get the substring of the era file containing the ST number
	//
	$fp = csv_verify_file($era_file, "era");
	if (!$fp) {
		csv_edihist_log ("ibr_era_get_clp_text: failed to read $era_file");
		$str_clp .= "failed to read $era_file";
		return $str_clp;
	} else {
		//
		$bstr = file_get_contents($fp);	
		if (!$bstr) { 
			csv_edihist_log ("ibr_era_get_clp_text: failed to get contents of $era_file"); 
			$str_clp .= "failed to read $era_file";
			return $str_clp;
		}
		// get the delimiters
		$str_isa = substr($bstr, 0, 126);
		//
		$ar_delim = csv_x12_delimiters($str_isa, "GS");
		$seg_delim = $ar_delim['t'];
		//
		$seg_clp = "CLP*" . $clp_clm_num;  // CLP segment that begins remittance detail	
		//
		$clp_pos = strpos($bstr, $seg_clp, 0);
		// break it off if $st_pos is not found
		if ( $clp_pos == FALSE ) { 
			csv_edihist_log ("Error: $clp_clm_num not found in $era_file");
			$str_clp .= "Error: $clp_clm_num not found in $era_file";
			return $str_clp;
		}
		//
		$seg_se = "SE*";
		$seg_lx = "LX*";
		$seg_clpx = "CLP*";	
		// see if we can find a closing segment
		$pos_ar[] = strpos($bstr, $seg_se, $clp_pos); // $se_pos = 
		$pos_ar[] = strpos($bstr, $seg_lx, $clp_pos); // $lx_pos = 
		$pos_ar[] = strpos($bstr, $seg_clpx, $clp_pos + 10); //$clpx_pos =   
		//
		// choose the best closing position, closest to $clp_pos
		asort($pos_ar);
		foreach ( $pos_ar as $p ) {
			// echo "clp_pos $clp_pos  pos_ar $p". PHP_EOL;
			if ( $p > $clp_pos ) {
				$end_pos = $p;
				break;
			}
		}		
		//
		$str_clp = substr($bstr, $clp_pos, $end_pos-$clp_pos);
		//
		// add newlines so each segment is on its own line
		if ( strpos($str_clp, $seg_delim.PHP_EOL) ) {
			// if true, assume the file has newlines ending segments
		} else {
			// we could get fancy and make an html table or add line numbers
			$str_clp = str_replace($seg_delim, $seg_delim.PHP_EOL, $str_clp);
		}
	}
	//
	if ($html_out) { 
		$str_html = "<div class=\"filetext\">";
		$str_html .= "<p>$pe &nbsp;&nbsp;".basename($fp)." </p>".PHP_EOL."<pre><code>";
		$str_html .= $str_clp;
		$str_html .= PHP_EOL."</code></pre>".PHP_EOL."</div>".PHP_EOL; 
		return $str_html;
	} else {
		return $str_clp;
	}
}

 
/**
 * Process new x12 835 files so data is written to csv tables and html output is created
 * 
 * This is a multi-operational function, calling other functions in this script and in 
 * csv_record_include.php to list newly uploaded files, parse them for csv data, write 
 * to the csv data files, and generate html output.
 * 
 * @uses csv_newfile_list()
 * @uses csv_x12_segments()
 * @uses ibr_era_csv_file_data()
 * @uses ibr_era_csv_write_data()
 * @uses ibr_era_csv_files_html()
 * 
 * @param array $file_array     not required, array of new filenames, generated by csv_newfile_list()
 * @param boolean $html_out     whether to generate html files summary table
 * @param boolean $err_only     whether to generate html for denied claims
 * @return string               html output
 */ 
 function ibr_era_process_new ($file_array=NULL, $html_out=TRUE, $err_only=TRUE) { 
	 //
	 // get new files from directory and write data to the .csv files
	 //
	 // create html table if $html_out is true
	 // 'mtime', 'dirname', 'fname', 'trace' 'payer' 'claims'
	 $html_str = "";
	 $ar_csv_data = array();
	 //
	 $err_ref = "";
	 //
	 if ( is_array($file_array) && count($file_array) ) {
		 $ar_newfiles = $file_array;
	 } else {
		 $ar_newfiles = csv_newfile_list("era");
	 }
	 // cut it off if there are no new files
	 if ( count($ar_newfiles) == 0) {
		 // no new files
		 if ($html_out) {
			 // return a message
			 $html_str = "<p>ibr_era_process_new: No new ERA 835 files found</p>";
			 return $html_str;
		 } else {
			 return FALSE;
		 }
	 } else {
		 $fcount = count($ar_newfiles);
	 }
	 // we have new files	 
	 foreach ($ar_newfiles as $f_era) {
		 //
		 $era_segments = csv_x12_segments($f_era, "era", FALSE);
		 //
		 if (is_array($era_segments) && count($era_segments['segments']) > 0 ) {
			 $ar_csv_data[] = ibr_era_csv_file_data ( $era_segments );
			 //
		 } else {
			 csv_edihist_log("ibr_era_process_new: did not get segments for " . basename($full_path));
			 $htm_hdrc .= "<p>did not get segments for $f_era </p>" . PHP_EOL;
			 continue;
		 }
	 }
	 // now send the data to be written to csv table and html output
	 foreach($ar_csv_data as $eradata) {
		$chars = ibr_era_csv_write_data($eradata, $err_ref);
		//$html_out
		$html_str .= isset($htm_hdrc) ? $htm_hdrc : "";
		//
		 if (is_array($chars) ) {
			 if ($chars[0]) {
				 csv_edihist_log("ibr_era_process_new: {$chars[0]} characters written to files_era.csv");
			 } else {
				 csv_edihist_log("ibr_era_process_new: error writing csv data for files: " .PHP_EOL.$err_ref);
				 $html_str .= "<p>ibr_era_process_new: error writing csv data</p>";
			 }
			 if ($chars[1]) {
				 csv_edihist_log("ibr_era_process_new: {$chars[1]} characters written to claims_era.csv");
			 } else {
				 csv_edihist_log("ibr_era_process_new: error writing csv data for claims ");
				 $html_str .= "<p>ibr_era_process_new: error writing csv data</p>";
			 }	
		 }
	 }
	 //
	 if ($html_out) {
         $html_str .= ibr_era_csv_files_html($ar_csv_data, $err_only);
	 } elseif ($chars[0] && $chars[1]) {
		 $html_str .= "x12_835 ERA files: processed $fcount ERA files <br />".PHP_EOL;
	 } else {
		 $html_str .= "x12_835 ERA: error writing csv data <br />";
	 }
	 return $html_str;
 }
 



?>
