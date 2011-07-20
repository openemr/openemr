<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 ZMG LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+
/*
 * Proper usage for xmltoarray_parser_htmlfix class:
 * $xmltoarray = new xmltoarray_parser_htmlfix(); //create instance of class
 * $xmltoarray->xmlparser_setoption(XML_OPTION_SKIP_WHITE, 1); //set options same as xml_parser_set_option
 * $xmltoarray->xmlparser_setoption(XML_OPTION_CASE_FOLDING, 0);
 * $xmltoarray->xmlparser_fix_into_struct($xmlstring); //fixes html values for XML
 * $array = $xmltoarray->createArray(); //creates an array with fixed html values
 * foreach($array as $key => $value){ 
 *	$array[$key] = $xmltoarray->fix_html_entities($value); //returns proper html values
 * }
 */
class xmltoarray_parser_htmlfix{
	var $values; 
	var $index; 
	var $thearray; 
	var $parser;
	
	/**
	 * Default constructor for xmltoarray_parser_htmlfix.
	 */
	function xmltoarray_parser_htmlfix(){
		$this->values = array(); 
		$this->index  = array(); 
		$this->thearray  = array(); 
		$this->parser = xml_parser_create();
	}
	
	/**
	 * xmlparser_setoption sets XML options based on xml_parser_set_option options.
	 * @param $optionName - The name of the option from the xml_parser_set_option list.
	 * @param $value - The value to set for the option.
	 */
	function xmlparser_setoption($optionName, $value){
		xml_parser_set_option($this->parser, $optionName, $value);
	}
	
	/**
	 * xmlparser_fix_into_struct fixes the XML and passes the XML into the struct parser.
	 * @param $xml - A string XML value.
	 */
	function xmlparser_fix_into_struct($xml){
		$trans_table = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
		$keys = array();
		foreach($trans_table as $key=>$value) {
            if($key != "<" && $key != ">" && $key != "&" && $key != "\"" && $key != "'" && $key != " "){
				$keys[$key] = $value;
			}
		}
		foreach($keys as $key=>$value){
			$xml =  preg_replace("/".$key."/",$value,$xml);
		}
		$xml =  str_replace("&","%and%",$xml);
		
		xml_parse_into_struct($this->parser, $xml, $this->values, $this->index);
		xml_parser_free($this->parser);
	}
	
	/**
	 * createArray creates and returns the array.
	 * @return The associative XML array.
	 */
	function createArray(){
		$i = 0; 
		$name = isset($this->values[$i]['tag']) ? $this->values[$i]['tag']: ''; 
		$this->thearray[$name] = isset($this->values[$i]['attributes']) ? $this->values[$i]['attributes'] : ''; 
		$this->thearray[$name] = $this->_struct_to_array($this->values, $i); 
		return $this->thearray; 
	}//createArray
	
	/**
	 * _struct_to_array is a recursive function that takes the values and creates the array.
	 * @param $values - The values of the XML
	 * @param &$i - The index value
	 * @return The child
	 */
	function _struct_to_array($values, &$i){
		$child = array(); 
		if (isset($values[$i]['value'])) array_push($child, $values[$i]['value']); 
		
		while ($i++ < count($values)) { 
			if(isset($values[$i])){
				switch ($values[$i]['type']) { 
					case 'cdata': 
					array_push($child, $values[$i]['value']); 
					break; 
					
					case 'complete': 
						$name = $values[$i]['tag']; 
						if(!empty($name)){
						$child[$name]= (isset($values[$i]['value']))?($values[$i]['value']):''; 
						if(isset($values[$i]['attributes'])) {					
							$child[$name] = $values[$i]['attributes']; 
						} 
					}	
					break; 
					
					case 'open': 
						$name = $values[$i]['tag']; 
						$size = isset($child[$name]) ? sizeof($child[$name]) : 0;
						$child[$name][$size] = $this->_struct_to_array($values, $i); 
					break;
					
					case 'close': 
					return $child; 
					break; 
				}
			}
		}
		return $child; 
	}//_struct_to_array

	/**
	 * fix_html_entities replaces all instances of '%and%' with '&', since the xml_parser can't handle '&'.
	 * @param $string - A string value.
	 * @return A fixed string with & instead of %and%.
	 */
	function fix_html_entities($string){
		$string =  str_replace("%and%","&",$string);
		return $string;
	}

}

?>