<?php
	// $Id$
	// $Author$
	// HL7 Parser

class Parser_HL7v2 {

	var $field_separator;
	var $map;
	var $message;
	var $message_type;

	var $MSH;
	var $EVN;

	function Parser_HL7v2 ( $message, $_options = NULL ) {
		// Assume separator is a pipe
		$this->message = $message;
		$this->field_separator = '|';
		if (is_array($_options)) {
			$this->options = $_options;
		}
	}
	function parse () {
		$message = $this->message;
		// Split HL7v2 message into lines
		$segments = explode("\n", $message);
		// Fail if there are no or one segments
		if (count($segments) <= 1) {
			return false;
		}

		// Loop through messages
		$count = 0;
		foreach ($segments AS $__garbage => $segment) {
			$count++;

			// Determine segment ID
			$type = substr($segment, 0, 3);
			switch ($type) {
				case 'MSH':
				case 'EVN':
				$this->message_type = trim($type);
				call_user_func_array(
					array(&$this, '_'.$type),
					array(
						// All but type
						substr(
							$segment,
							-(strlen($segment)-3)
						)
					)
				);
				$this->map[$count]['type'] = $type;
				$this->map[$count]['position'] = 0;
				break;

				default:
				$this->message_type = trim($type);
				$this->__default_segment_parser($segment);
				$this->map[$count]['type'] = $type;
				$this->map[$count]['position'] = count($this->message[$type]);
				break;
			} // end switch type
		}
		
		// Depending on message type, handle differently
		switch ($this->message_type) {
			default:
			return ('Message type '.$this->message_type.' is '.
				'currently unhandled'."<br/>\n");
			break;
		} // end switch
	} // end constructor Parser_HL7v2

	function Handle() {
		// Set to handle current method
		$type = str_replace('^', '_', $this->MSH['message_type']);

		// Check for an appropriate handler
		$handler = CreateObject('_FreeMED.Handler_HL7v2_'.$type, $this);

		// Error out if the handler doesn't exist
		if (!is_object($handler)) {
			if ($this->options['debug']) {
				print "<b>Could not load class ".
					"_FreeMED.Handler_HL7v2_".$type.
					"</b><br/>\n";
			}
			return false;
		}

		// Run appropriate handler
		return $handler->Handle();
	} // end method Handle

	//----- All handlers go below here

	function _EVN ($segment) {
		$composites = $this->__parse_segment ($segment);
		if ($this->options['debug']) {
			print "<b>EVN segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".prepare($v)."<br/>\n";
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

	function _MSH ($segment) {
		// Get separator
		$this->field_separator = substr($segment, 0, 1);
		$composites = $this->__parse_segment ($segment);
		if ($this->options['debug']) {
			print "<b>MSH segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".prepare($v)."<br/>\n";
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
			$this->MSH['security'],
			$this->MSH['message_type'],
			$this->MSH['message_control_id'],
			$this->MSH['processing_id'],
			$this->MSH['version_id'],
			$this->MSH['sequence_number'],
			$this->MSH['confirmation_pointer'],
			$this->MSH['accept_ack_type'],
			$this->MSH['application_ack_type'],
			$this->MSH['country_code']
		) = $composites;

		// TODO: Extract $this->MSH['encoding_characters'] and use
		// it instead of assuming the defaults.
	} // end method _MSH

	//----- Truly internal functions

	function __default_segment_parser ($segment) {
		$composites = $this->__parse_segment($segment);

		// The first composite is always the message type
		$type = $composites[0];

		// Debug
		if ($this->options['debug']) {
			print "<b>".$type." segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".prepare($v)."<br/>\n";
			}
		}

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}

		// Find out where we are
		if (is_array($this->message[$type])) {
			$pos = count($this->message[$type]);
		} else {
			$pos = 0;
		}

		// Add parsed segment to message
		$this->message[$type][$pos] = $composites;
	} // end method __default_segment_parser

	function __parse_composite ($composite) {
		return explode('^', $composite);
	} // end method __parse_composite

	function __parse_segment ($segment) {
		return explode($this->field_separator, $segment);
	} // end method __parse_segment
	
	function composite_array() {
		$cmp = array();
		$cmp["MSH"] = $this->MSH;
		$cmp["EVN"] = $this->EVN;	
		return $cmp;
	}
} // end class Parser_HL7v2

?>
