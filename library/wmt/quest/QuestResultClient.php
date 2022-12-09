<?php
/** **************************************************************************
 *	QuestResultClient.PHP
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
require_once 'ResultService.php';
require_once 'QuestParserHL7v23.php';

if (!class_exists("QuestResultClient")) {
	/**
	 * class QuestResultClient submits lab order (HL7 messages) to the MedPlus Hub
	 * platform.  Encapsulates the sending of an HL7 order to a Quest Lab
	 * via the Hub’s SOAP Web service.
	 *	
	 */
	class QuestResultClient {
		/**
		 * Will pass the username and password to establish a service connection to
		 * the hub. Facilitates packaging the order in a proper HL7 format. Performs
		 * the transmission of the order to the Hub's SOAP Web Service. Provides
		 * method calls to the Results Web Service to retrieve lab results.
		 * 
		 */
		private $STATUS; // D=development/training  V=validation  P=production
		private $ENDPOINT;
//		https://cert.hub.care360.com/observation/result/service?wsdl
//		https://hubservices.medplus.com/observation/result/service?wsdl
		private $USERNAME;
		private $PASSWORD;
		private $SENDING_APPLICATION;
		private $SENDING_FACILITY;
		private $RECEIVING_APPLICATION;
		private $RECEIVING_FACILITY;
		private $WSDL;
		
		// data storage   	
		private $service = null;
    	private $request = null;
    	private $response = null;
    	private $messages = array();
    	private $documents = array();
    	
		private $DEBUG = false;
		
    	/**
		 * Constructor for the 'result client' class.
		 */
		public function __construct($lab_id) {
			// retrieve processor data
			$this->lab_id = $lab_id;
			$lab_data = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($lab_id));
			if (!$lab_data['ppid'])
				throw new Exception("Missing processor information data.");			

			$this->lab_data = $lab_data;
			$this->REPOSITORY = $GLOBALS['oer_config']['documents']['repository'];
				
			$this->STATUS = 'D'; // default training
			if ($lab_data['DorP'] == 'P') $this->STATUS = 'P'; // production
			$this->SENDING_APPLICATION = $lab_data['send_app_id'];
			$this->SENDING_FACILITY = $lab_data['send_fac_id'];
			$this->RECEIVING_APPLICATION = $lab_data['recv_app_id'];
			$this->RECEIVING_FACILITY = $lab_data['recv_fac_id'];
			$this->ENDPOINT = $lab_data['remote_host'];
			$this->USERNAME = $lab_data['login'];
			$this->PASSWORD = $lab_data['password'];
			$this->WSDL = $lab_data['remote_host']."/".$lab_data['results_path'];
				
			$category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?",array($lab_data['name']));
			$this->DOCUMENT_CATEGORY = $category['id'];

			// sanity check
			if ($lab_data['protocol'] != 'WS' ||
					$lab_data['type'] != 'quest' ||
					!$this->DOCUMENT_CATEGORY ||
					!$this->RECEIVING_APPLICATION ||
					!$this->RECEIVING_FACILITY ||
					!$this->SENDING_APPLICATION ||
					!$this->SENDING_FACILITY ||
					!$this->WSDL ||
					!$this->USERNAME ||
					!$this->PASSWORD ||
					!$this->ENDPOINT ||
					!$this->STATUS ||
					!$this->REPOSITORY )
				die ("Quest Interface Not Properly Configured!!\n\n<pre>".var_dump($this)."</pre>\n\n");

			// web service initialization
			$options = array();
			$options['wsdl_local_copy'] = 'wsdl_quest_results';
			$options['wsdl_path'] = $GLOBALS["OE_SITE_DIR"]."/labs/".$lab_id;
			$options['login'] = $this->USERNAME;
			$options['password'] = $this->PASSWORD;
			
			$this->service = new ObservationResultService($this->WSDL,$options);
			$this->request = new ObservationResultRequest();
			$this->response = new ObservationResultResponse();	

			
			return;
		}

		/**
		 * buildRequest() constructs a valid HL7 Order result message string.
		 */
		public function buildRequest($max_messages = 1, $start_date = false, $end_date = false) {

			$this->request->retrieveFinalsOnly = FALSE;
			$this->request->maxMessages = $max_messages;
			if ($start_date) $this->request->startDate = $start_date;
			if ($end_date) $this->request->endDate = $end_date;
				
			return;
		}
		
		/**
		 *
	 	 * Retrieve result 
	 	 * This routine dispatches to the correct retrieval routine based on
	 	 * the protocol type specified for the current processor (lab).
		 *
		 */
		public function getResults($DEBUG = false) {
			$response = null;
			$results = array();
			$response_id = null;
			$more_results = false;
			$this->messages = array();
			
			try {
				$response = $this->service->getResults($this->request);
				$response_id = $response->requestId;
				$more_results = $response->isMore;
				$results = $response->observationResults;
				
				echo "\n".count($results)." Results Returned";
				if ($more_results) echo " (MORE RESULTS)";
				if ($DEBUG) {
					if (count($results)) echo "\nHL7 Messages:";
				}
				
				if ($results) {
					foreach ($results as $result) {
						if ($DEBUG) {
							echo "\n" . $result->HL7Message;
							$options = array('debug'=>true);
						}

						$parser = new Parser_HL7v23($result->HL7Message,$options);
						$parser->parse();
						$message = $parser->getRequest();
					
						$message->message_id = $result->resultId;
						$message->response_id = $response_id;
						$message->documents = $result->documents;
						$message->hl7data = $result->HL7Message;

						// add the message to the results
						$this->messages[] = $message;
					}
				}
			} 
			catch (Exception $e) {
				die("FATAL ERROR: " . $e->getMessage());
			}
			
			return $this->messages;
		}
		
		/**
		 * buildResultAck() constructs a valid HL7 Order result message string.
	 	 *
	 	 * @access public
	 	 * @param int $max maximum number of result to retrieve
	 	 * @param string[] $data array of order data
	 	 * @return Order $order
	 	 * 
		 */
		public function buildResultAck($result_id, $reject = FALSE) {
			$ack = new AcknowledgedResult();
			
			$ack->resultId = $result_id;
			$ack->ackCode = "CA"; // assume okay
			$ack->rejectionReason = '';
			
			if ($reject) {
				$ack->ackCode = "CR"; // reject
				$ack->rejectionReason = $reject;
			}

			return $ack;
		}
		
		/**
		 *
	 	 * The sendResultAck() method will:
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create an ACK request object
		 * 3. Submit calling Acknowledgment()
		 *
		 */
		public function sendResultAck($id, $acks, $DEBUG = false) {
			$response = null;
			$this->request = new Acknowledgment();
			$this->request->requestId = $id;
			$this->request->acknowledgedResults = $acks;
			
			try {
				if ($DEBUG) {
					echo "\n".count($acks)." Result Acknowledgments Sent";
				}
				
				$response = $this->service->acknowledgeResults($this->request);

			} 
			catch (Exception $e) {
				echo ($e->getMessage());
			}
			
			return;
		}
		
		
		public function getProviderAccounts() {
			$results = array();
			try {
				$results = $this->service->getProviderAccounts();
				echo "\n".count($results)." Results Returned";
				
				echo "\nProviders:";
				var_dump($results);
			} 
			catch (Exception $e) {
				echo($e->getMessage());
			}
			
			return;
		}
		
	}
}
