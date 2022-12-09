<?php
/** **************************************************************************
 *	QuestParserHL7v23.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
class ParseException extends Exception {
	// just adds exception class name
}

class Segment_HL7v23 {
	// contents are dynamic
	var $type;
}

class Parser_HL7v23 {

	var $field_separator;
	var $map;
	var $message;
	var $message_type;

	var $MSH;
	var $PID;
	var $IN1;
	var $GT1;
	var $DG1;
	var $ORC;
	var $OBR;
	var $OBX;
	var $NTE;
	var $ZPS;
	var $OTHER;

	function Parser_HL7v23( $message, $_options = NULL ) {
		$this->message = $message;
		$this->field_separator = '|'; // default
		if (is_array($_options)) {
			$this->options = $_options;
		}
		
		// TURN OFF DEBUGGING !!!
//		$this->options['debug'] = false;
	}
	
	function parse() {
		// reference to message
		$message = &$this->message;
		
		// Split HL7v23 message into lines
		$segments = explode("\r", $message);
		
		// Fail if there are no or one segments
		if (count($segments) <= 1) {
			throw new ParseException('\nNo segments found in HL7 message');
		}

		// Loop through messages
		$count = 0;
		foreach ($segments AS $segment) {
			$segment = trim($segment); // strip garbage
			$pos = 0;
			$count++;

			// Determine segment ID
			$type = substr($segment, 0, 3);
			switch ($type) {
				case 'MSH':
				case 'PID':
				case 'ORC':
					// non-repeating segments
					$this->message_type = trim($type);
					$pos = call_user_func_array(
						array(&$this, '_'.$type),
						array($segment)
					);
					$this->map[$count]['type'] = $type;
					$this->map[$count]['position'] = 0;
					break;

				case 'IN1':
				case 'OBR':
				case 'OBX':
				case 'NTE':
				case 'EVN':
					// repeating segments
					$this->message_type = trim($type);
					$pos = call_user_func_array(
						array(&$this, '_'.$type),
						array($segment)
					);
					$this->map[$count]['type'] = $type;
					$this->map[$count]['position'] = $pos;
					break;

				default:
					// unknown segment type
					$this->message_type = trim($type);
					$this->__default_segment_parser($segment);
					$this->map[$count]['type'] = $type;
					$this->map[$count]['position'] = count($this->OTHER[$type]);
					break;
					
			} // end switch type
		}
	}


	// ----- All handlers go below here -----

	
	function _EVN ($segment) {
		$composites = $this->__parse_segment ($segment);
		if ($this->options['debug']) {
			print "<b>EVN segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		list (
			$this->EVN['event_type_code'],
			$this->EVN['event_datetime'],
			$this->EVN['event_planned'],
			$this->EVN['event_reason'],
			$this->EVN['operator_id']
		) = $composites;
	} // end method _EVN

	function _MSH($segment) {
		// Get separator
		$this->field_separator = substr($segment, 3, 1);
		
		// decompose composite segments
		$composites = $this->__parse_segment($segment);
		if ($this->options['debug']) {
			print "<b>MSH segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
		
		// Assign values
		list (
			$__garbage, // Skip index [0], it's the separator
			$this->MSH['encoding_characters'],
			$this->MSH['sending_application'],
			$this->MSH['sending_facility'] ,
			$this->MSH['receiving_application'],
			$this->MSH['receiving_facility'],
			$this->MSH['message_datetime'],
			$__garbage, // unsupported
			$this->MSH['message_type'],
			$this->MSH['message_control_id'],
			$this->MSH['processing_id'],
			$this->MSH['version_id']
		) = $composites;

	} // end method _MSH

	function _PID($segment) {
		$composites = $this->__parse_segment($segment);

		// try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		if ($this->options['debug']) {
			print "<b>PID segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
		
		// Assign values
		list (
			$__garbage, // Skip index [0], it's the type
			$this->PID['set_id'],
			$this->PID['patient_id'],
			$this->PID['external_id'],
			$this->PID['alternate_id'],
			$this->PID['patient_name'],
			$this->PID['maiden_name'],
			$this->PID['birth_datetime'],
			$this->PID['sex'],
			$this->PID['patient_alias'],
			$this->PID['race'],
			$this->PID['patient_address'],
			$this->PID['country_code'],
			$this->PID['phone_number'],
			$this->PID['phone_business'],
			$this->PID['primary_language'],
			$this->PID['marital_status'],
			$this->PID['religion'],
			$this->PID['accounting'],
			$this->PID['ssn']
		) = $composites;

	} // end method _PID

	function _IN1($segment) {
		$composites = $this->__parse_segment($segment);

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		// Debug
		if ($this->options['debug']) {
			print "<b>IN1 segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		// Find out where we are
		$pos = 0;
		if (is_array($this->IN1)) {
			$pos = count($this->IN1);
		}
		
		list (
			$__garbage, // Skip index [0], it's the type
			$this->IN1[$pos]['set_id'],
			$__garbage, // unsupported,
			$this->IN1[$pos]['ins_company_id'],
			$this->IN1[$pos]['ins_company_name'],
			$this->IN1[$pos]['ins_company_address'],
			$__garbage, // unsupported
			$this->IN1[$pos]['ins_phone_number'],
			$this->IN1[$pos]['group_number'],
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->IN1[$pos]['group_emp_name'],
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->IN1[$pos]['insured_name'],
			$this->IN1[$pos]['insured_relation'],
			$__garbage, // unsupported
			$this->IN1[$pos]['insured_address'],
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->IN1[$pos]['company_plan_code'],
			$this->IN1[$pos]['policy_number']
		) = $composites;
		
		return $pos;

	} // end method _IN1

	function _ORC($segment) {
		$composites = $this->__parse_segment($segment);

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		// Debug
		if ($this->options['debug']) {
			print "<b>ORC segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		list (
			$__garbage, // Skip index [0], it's the type
			$this->ORC['order_control'],
			$this->ORC['placer_order_number'],
			$this->ORC['filler_order_number'],
			$__garbage, // unsupported ORC-4
			$this->ORC['order_status'],
			$__garbage, // unsupported ORC-6
			$__garbage, // unsupported ORC-7
			$__garbage, // unsupported ORC-8
			$__garbage, // unsupported ORC-9
			$__garbage, // unsupported ORC-10
			$this->ORC['verified_by'],
			$this->ORC['ordering_provider']
		) = $composites;

	} // end method _ORC

	function _OBR($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>OBR segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->OBR)) {
			$pos = count($this->OBR);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->OBR[$pos]['set_id'],
			$this->OBR[$pos]['placer_order_number'],
			$this->OBR[$pos]['filler_order_number'],
			$this->OBR[$pos]['universal_service_id'],
			$__garbage, // unsupported OBR-5
			$__garbage, // unsupported OBR-6
			$this->OBR[$pos]['collected_datetime'],
			$__garbage, // unsupported OBR-8
			$__garbage, // unsupported OBR-9
			$__garbage, // unsupported OBR-10
			$this->OBR[$pos]['action_type'],
			$__garbage, // unsupported OBR-12
			$this->OBR[$pos]['additional_data'],
			$this->OBR[$pos]['received_datetime'],
			$__garbage, // unsupported OBR-15
			$this->OBR[$pos]['ordering_provider'],
			$__garbage, // unsupported OBR-17
			$this->OBR[$pos]['passthru_field1'],
			$this->OBR[$pos]['passthru_field2'],
			$this->OBR[$pos]['component_id'],
			$this->OBR[$pos]['lab_data'],
			$this->OBR[$pos]['reported_datetime'],
			$__garbage, // unsupported OBR-23
			$this->OBR[$pos]['producer_id'],
			$this->OBR[$pos]['result_status'],
			$this->OBR[$pos]['parent_result'],
			$this->OBR[$pos]['quantity_timing'],
			$this->OBR[$pos]['quantity_timing_priority'],
			$this->OBR[$pos]['quantity_timing_condition']
		) = $composites;
		
		return $pos;
	
	} // end method _OBR
	
	function _OBX($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>OBX segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->OBX)) {
			$pos = count($this->OBX);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->OBX[$pos]['set_id'],
			$this->OBX[$pos]['value_type'],
			$this->OBX[$pos]['universal_service_id'],
			$__garbage, // unsupported OBX-4
			$this->OBX[$pos]['observation_value'],
			$this->OBX[$pos]['observation_units'],
			$this->OBX[$pos]['observation_range'],
			$this->OBX[$pos]['observation_abnormal'],
			$__garbage, // unsupported OBX-9
			$__garbage, // unsupported OBX-10
			$this->OBX[$pos]['observation_status'],
			$__garbage, // unsupported OBX-12
			$__garbage, // unsupported OBX-13
			$this->OBX[$pos]['observation_datetime'],
			$this->OBX[$pos]['producer_id']
		) = $composites;
		
		return $pos;
	
	} // end method _OBX
	
	function _NTE($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>NTE segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->NTE)) {
			$pos = count($this->NTE);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->NTE[$pos]['set_id'],
			$this->NTE[$pos]['source'],
			$this->NTE[$pos]['comment']
		) = $composites;
	
		return $pos;
	
	} // end method _NTE
	
	
	function getRequest() {
		$map = &$this->map;
		$message = new Segment_HL7v23();
	
		// gather request information
		$message->datetime = $this->MSH['message_datetime'];
		$message->application = $this->MSH['receiving_application'];
		$message->facility_id = $this->MSH['receiving_facility'];
		
		$message->pid = $this->PID['patient_id'];
		$message->dob = $this->PID['birth_datetime'];
		$message->age = $this->PID['age'][0];
		$message->name = $this->PID['patient_name'];
		$message->sex = $this->PID['sex'];
		$message->ss = $this->PID['ss'];
		$message->pubpid = $this->PID['alternate_id'];
		$message->extpid = $this->PID['external_id'];
		$message->phone = $this->PID['phone_number'];
		$message->address = $this->PID['patient_address'];
//		$message->account = $this->PID['accounting'][0]; // first element
//		$message->bill_type = $this->PID['accounting'][3];
//		$message->lab_status = $this->PID['accounting'][5];
		
		$message->order_control = $this->ORC['order_control'];
		$message->order_number = $this->ORC['placer_order_number'];
		$message->provider = $this->ORC['ordering_provider'];
		$message->lab_received = $this->ORC['datetime_transaction'];
		$message->lab_number = $this->ORC['filler_order_number'];
		$message->lab_status = '';
/*
 *  C - Record coming over is a correction and thus replaces a final result
 *  D - Deletes the OBX record
 *  F - Final results; Can only be changed with a corrected result.
 *  I - Specimen in lab; results pending
 *  P - Preliminary results
 *  R - Results entered -- not verified
 *  S - Partial results
 *  X - Results cannot be obtained for this observation
 *  U - Results status change to Final. 
 *  W - Post original as wrong, e.g., transmitted for wrong patient
 */
		for ($i = 0; $i < count($map); $i++) {
			$item = $map[$i];
			
			while ($item['type'] == 'NTE') {
				$nte_data = &$this->NTE[$item['position']];
				
				$note = new Segment_HL7v23();
				$note->set_id = $nte_data['set_id'];
				$note->source = $nte_data['source'];
				$note->comment = $nte_data['comment'];
				
				$message->notes[] = $note;
				$item = $map[++$i];		
			}
			
			while ($item['type'] == 'OBR') {
				$obr_data = &$this->OBR[$item['position']];
						
				$report = new Segment_HL7v23();
				$report->set_id = $obr_data['set_id'];
				$report->order_control = $this->ORC['order_control'];
				$report->order_number = $obr_data['placer_order_number'];
				if (!$message->order_number) $message->order_number = $obr_data['placer_order_number'];
				$report->lab_number = $obr_data['filler_order_number'];
				if (!$message->lab_number) $message->lab_number = $obr_data['filler_order_number'];
				$report->service_id = $obr_data['universal_service_id'];
				$report->parent_id = $obr_data['parent_id'];
				$report->component_id = null;
				$report->specimen_datetime = ($obr_data['collected_datetime']) ? $obr_data['collected_datetime'] : $obr_data['received_datetime'];
				if (!$message->specimen_datetime) $message->specimen_datetime = $report->specimen_datetime; 
				$report->received_datetime = $obr_data['received_datetime'];
				if (!$message->received_datetime) $message->received_datetime = $obr_data['received_datetime']; 
				$report->result_datetime = $obr_data['reported_datetime'];
				if (!$message->reported_datetime) $message->reported_datetime = $obr_data['reported_datetime']; 
				$report->provider = $obr_data['ordering_provider'];
				if (!$message->provider) $message->provider = $obr_data['ordering_provider']; 
				$report->service_section = $obr_data['producer_id'];
				$report->result_status = $obr_data['result_status'];
				$report->action_type = $obr_data['action_type'];
				if (!$message->additional_data) $message->additional_data = $obr_data['additional_data'];

				// determine overall result status
				if ($message->lab_status == '' && $obr_data['result_status'] == 'F') $message->lab_status = 'F'; // default final if we get any results
				if ($message->lab_status == '' && $obr_data['result_status'] != 'F') $message->lab_status = $obr_data['result_status'];
				
				$item = $map[++$i];		
				while ($item['type'] == 'NTE') {
					$nte_data = &$this->NTE[$item['position']];
				
					$note = new Segment_HL7v23();
					$note->set_id = $nte_data['set_id'];
					$note->source = $nte_data['source'];
					$note->comment = $nte_data['comment'];
				
					$report->notes[] = $note;
					$item = $map[++$i];		
				}
			
				while ($item['type'] == 'OBX') {
					$obx_data = &$this->OBX[$item['position']];
			
					$result = new Segment_HL7v23();
					$result->set_id = $obx_data['set_id'];
					$result->value_type = $obx_data['value_type'];
					$result->observation_id = $obx_data['universal_service_id'];
					$result->observation_value = $obx_data['observation_value'];
					$result->observation_units = $obx_data['observation_units'];
					$result->observation_range = $obx_data['observation_range'];
					$result->observation_abnormal = $obx_data['observation_abnormal'];
					if ($obx_data['observation_abnormal'] == 'N') $result->observation_abnormal = '';
					$result->observation_status = $obx_data['observation_status'];
					$result->observation_datetime = $obx_data['observation_datetime'];
					if (!$message->reported_datetime) $message->reported_datetime = $obx_data['observation_datetime']; 
					$result->producer_id = $obx_data['producer_id'];

					// get next HL7 item
					$item = $map[++$i];		
					while ($item['type'] == 'NTE') {
						$nte_data = &$this->NTE[$item['position']];
				
						$note = new Segment_HL7v23();
						$note->set_id = $nte_data['set_id'];
						$note->source = $nte_data['source'];
						$note->comment = $nte_data['comment'];
				
						$result->notes[] = $note;
						$item = $map[++$i];		
					}
			
					$report->results[] = $result;
				}
				
				$message->reports[] = $report;
			}
		}	

		// check for no results
		if (! $message->lab_status) $message->lab_status = 'I'; // incomplete
		
		return $message;
	}
	
	
	//----- Truly internal functions

	function __default_segment_parser ($segment) {
		$composites = $this->__parse_segment($segment);

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		// The first composite is always the message type
		$type = $composites[0];

		// Debug
		if ($this->options['debug']) {
			print "<b>".$type." segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		$pos = 0;

		// Find out where we are
		if (is_array($this->OTHER[$type])) {
			$pos = count($this->OTHER[$type]);
		}
		
		$this->OTHER[$type][$pos] = $composites;

	} // end method __default_segment_parser

	function __parse_composite ($composite) {
		return explode('^', $composite);
	} // end method __parse_composite

	function __parse_segment ($segment) {
		return explode($this->field_separator, $segment);
	} // end method __parse_segment
	
} // end class Parser_HL7v23

?>
