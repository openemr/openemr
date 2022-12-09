<?php

namespace OpenEMR\OemrAd;

class Hubspot {

	const ACCEPTED_CODES = '200, 201, 202';

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->hapikey = "769ee2d9-58ea-4c1c-b5a1-dd93245b58d9";
		$returnList->fieldMapping = array(
			'form_fname' => 'firstname',
			'form_mname' => 'middlename',
			'form_lname' => 'lastname',
			'form_organization' => 'company',
			'form_specialty' => 'jobtitle',
			'form_email' => 'email',
			'form_fax' => 'fax',
			'form_phonecell' => 'mobilephone',
			'form_street' => 'address',
			'form_city' => 'city',
			'form_state' => 'state',
			'form_zip' => 'zip',
		);

		return $returnList;
	}

	public static function getContactData($objectId) {
		$configList = self::getConfigVars();

		if(isset($objectId) && !empty($objectId)) {
			$contactResponce = self::callRequest(
				'', 
				array(
					"url" => "https://api.hubapi.com/crm/v3/objects/contacts/$objectId?properties=email&properties=firstname&properties=lastname&properties=company&properties=jobtitle&properties=fax&properties=phone&properties=mobilephone&properties=address&properties=city&properties=state&properties=zip&properties=country&archived=false&hapikey=$configList->hapikey",
					"method" => "GET",
				)
			);

			return $contactResponce;
		}
	}

	public static function callRequest($body, $config = array()) {
		if(isset($config["method"])) {
			if($config["method"] == "GET") {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $config["url"]);
    			
    			if(isset($config["header"])) {
    				curl_setopt($ch, CURLOPT_HTTPHEADER, $config["header"]);
    				curl_setopt($ch, CURLOPT_HEADER, 0);
    			}

    			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
    			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    			if(isset($body) && !empty($body)) {
    				curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
    			} 

    			// Send data
				$result = curl_exec($ch);
				$errCode = curl_errno($ch);
				$errText = curl_error($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				// Handle result
				return self::handle($result, $httpCode);
			}
		}

		return false;
	}

	/* Handle CURL response from servers. */
	protected static function handle($result, $httpCode) {
		// Check for non-OK statuses
		$codes = explode(",", static::ACCEPTED_CODES);
		if (!in_array($httpCode, $codes)) {
			if($httpCode == "400") {
				//$xml = simplexml_load_string($result);
				//$json = json_encode($xml);
				return json_decode($result,TRUE);
			} else {
				return json_decode($result, true);
			}
		} else {
			return json_decode($result, true);
		}
	}

	public static function getHubspotMappingData($param = array()) {
		if(!empty($param)) {
			// $whereStr = "u.abook_type = ?";
			// $binds = array('Attorney');

			$whereStr = "";
			$binds = array();

			if(isset($param['hubspot_id'])) {
				$whereStr .= " hdm.hubspot_id = ? ";
				$binds[] = $param['hubspot_id'];
			} else if(isset($param['address_id'])) {
				$whereStr .= " hdm.address_id = ? ";
				$binds[] = $param['address_id'];
			}

			$sql = "SELECT hdm.*, u.abook_type from `hubspot_data_mapping` hdm left join users u on u.id = hdm.address_id  where $whereStr ";
			$hdmData = sqlQuery($sql, $binds);

			return $hdmData;
		}

		return false;
	}

	public static function getValObj($name, $data = array())
	{
	    return isset($data[$name]) ? "'$data[$name]'" : "''";
	}

	public static function updateAddressBook($userid, $data = array()) {
		if ($userid) {
	        $query = "UPDATE users SET " .
	        "abook_type = "   . self::getValObj('form_abook_type', $data)   . ", " .
	        "fname = "        . self::getValObj('form_fname', $data)                  . ", " .
	        "lname = "        . self::getValObj('form_lname', $data)                  . ", " .
	        "mname = "        . self::getValObj('form_mname', $data)                  . ", " .
	        "specialty = "    . self::getValObj('form_specialty', $data)    . ", " .
	        "organization = " . self::getValObj('form_organization', $data) . ", " .
	        "email = "        . self::getValObj('form_email', $data)        . ", " .
	        "street = "       . self::getValObj('form_street', $data)       . ", " .
	        "streetb = "      . self::getValObj('form_streetb', $data)      . ", " .
	        "city = "         . self::getValObj('form_city', $data)         . ", " .
	        "state = "        . self::getValObj('form_state', $data)        . ", " .
	        "zip = "          . self::getValObj('form_zip', $data)          . ", " .
	        "phonecell = "    . self::getValObj('form_phonecell', $data)    . ", " .
	        "fax = "          . self::getValObj('form_fax', $data)          . " " .
	        "WHERE id = '" . add_escape_custom($userid) . "'";
	        return sqlStatement($query);
	    }

	    return false;
	}

	public static function createAddressBook($data = array()) {
		extract($data);

		$userid = sqlInsert("INSERT INTO users ( " .
        "username, password, authorized, info, source, " .
        "title, fname, lname, mname, suffix, " .
        "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, cpoe, " .
        "specialty, organization, valedictory, assistant, billname, email, email_direct, url, " .
        "street, streetb, city, state, zip, " .
        "street2, streetb2, city2, state2, zip2, " .
        "phone, phonew1, phonew2, phonecell, fax, notes, abook_type, ct_communication "            .
        ") VALUES ( "                        .
        "'', "                               . // username
        "'', "                               . // password
        "0, "                                . // authorized
        "'', "                               . // info
        "NULL, "                             . // source
        "'', " .
        self::getValObj('form_fname', $data)                   . ", " .
        self::getValObj('form_lname', $data)                   . ", " .
        self::getValObj('form_mname', $data)					. ", " .
        self::getValObj('form_suffix', $data)					. ", " .
        "'', " .
        "'', "                               . // federaldrugid
        "'', " .
        "'', "                               . // facility
        "0, "                                . // see_auth
        "1, "                                . // active
        "'', " .
        "'', " .
        "'', " .
        self::getValObj('form_specialty', $data)     			. ", " .
        self::getValObj('form_organization', $data)  			. ", " .
        "'', " .
        "'', " .
        "'', "                               . // billname
        self::getValObj('form_email', $data)         			. ", " .
        "'', " .
        "'', " .
        self::getValObj('form_street', $data)        			. ", " .
        "'', " .	
        self::getValObj('form_city', $data)          			. ", " .
        self::getValObj('form_state', $data)         			. ", " .
        self::getValObj('form_zip', $data)           			. ", " .
        "'', " .
        "'', " .
        "'', " .
        "'', " .
        "'', " .
        self::getValObj('form_phone', $data) 					. ", " .
        "'', " .
        "'', " .
        self::getValObj('form_phonecell', $data)				. ", " .
        self::getValObj('form_fax', $data) 					. ", " .
        "'', " .
        self::getValObj('form_abook_type', $data) 				. ", " .
        "'' " .
        ")");

        return $userid;
	}

	public static function deleteAddressBook($userid) {
		return sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($userid));
	}

	public function prepareDataForHubspot($data = array()) {
		$configList = self::getConfigVars();
		$pData = array();

		if(!empty($configList->fieldMapping)) {
			foreach ($configList->fieldMapping as $fmKey => $fmItem) {
				$pData[$fmItem] = isset($data[$fmKey]) ? $data[$fmKey] : "";
			}
		}

		return $pData;
	}

	public static function prepareDataForOpenEMR($data = array()) {
		$configList = self::getConfigVars();
		$pData = array();

		if(!empty($configList->fieldMapping)) {
			foreach ($configList->fieldMapping as $fmKey => $fmItem) {
				$pData[$fmKey] = isset($data[$fmItem]) ? $data[$fmItem] : "";
			}
		}

		return $pData;
	}

	public static function logMappingData($data = array()) {
		extract($data);

		if(!empty($data)) {
			$logId = sqlInsert("INSERT INTO hubspot_data_mapping ( address_id, hubspot_id ) VALUES ( '$address_id',  '$hubspot_id') ");
		}

		return $logId;
	}

	public static function deleteLogMappingData($address_id, $hubspot_id) {
		if(!empty($address_id) && !empty($hubspot_id)) {
			return sqlStatement("DELETE FROM hubspot_data_mapping WHERE address_id = ? AND hubspot_id = ? ", array($address_id, $hubspot_id));
		}

		return false;
	}


	public function handleAbookOperations($jItem = array()) {
		$objectId = isset($jItem['objectId']) ? $jItem['objectId'] : "";
		$subType = isset($jItem['subscriptionType']) ? $jItem['subscriptionType'] : "";

		if(!empty($objectId)) {

			$contactData = self::getContactData($jItem['objectId']);
			$contactId = !empty($contactData) && isset($contactData['id']) ? $contactData['id'] : "";
			$properties = !empty($contactData) && isset($contactData['properties']) ? $contactData['properties'] : array();

			if(isset($subType) && ($subType == "contact.propertyChange" || $subType == "contact.creation")) {
				//if(!empty($contactData) && isset($contactData['id'])) {
					//$contactId = isset($contactData['id']) ? $contactData['id'] : "";
					//$properties = isset($contactData['properties']) ? $contactData['properties'] : array();

					if(!empty($contactId)) {
						$hdmData = self::getHubspotMappingData(array("hubspot_id" => $contactId));

						if(!empty($hdmData) && $hdmData['abook_type'] == 'Attorney') {
							self::handleUpdateAbook($hdmData['address_id'], $properties);
							echo "Updated";
						} else if(empty($hdmData)) {
							self::handleCreateAbook($contactId, $properties);
							echo "Created";
						}
					}
				//}

			} else if(isset($subType) && ($subType == "contact.deletion")) {
				if(!empty($contactId)) {
					$hdmData = self::getHubspotMappingData(array("hubspot_id" => $contactId));

					if(!empty($hdmData) && $hdmData['abook_type'] == 'Attorney') {
						self::handleDeleteAbook($hdmData['address_id'], $contactId);
						echo "Delete";
					}
				}
			}
		}
	}

	public static function handleUpdateAbook($abook_id = "", $data = array()) {
		$paramData = self::prepareDataForOpenEMR($data);
		$paramData['form_abook_type'] = 'Attorney';

		$uabookId = self::updateAddressBook($abook_id, $paramData);

		return $uabookId;
	}

	public static function handleCreateAbook($contactId = "", $data = array()) {
		$paramData = self::prepareDataForOpenEMR($data);
		$paramData['form_abook_type'] = 'Attorney';

		$newAbookId = self::createAddressBook($paramData);
		
		if(!empty($newAbookId)) {
			self::logMappingData(array(
				"address_id" => $newAbookId,
				"hubspot_id" => $contactId
			));
		}

		return $newAbookId;
	}

	public static function handleDeleteAbook($abook_id = "", $contactId = "") {
		if(!empty($abook_id)) {
			$deleteResponce = self::deleteAddressBook($abook_id);

			if($deleteResponce) {
				self::deleteLogMappingData($abook_id, $contactId);
			}
		}

		return false;
	}
}