<?php

include_once(dirname(__FILE__) . "/../../includes/config.php");

/**
 * class OFX
 *
 */
class OFX {
	
	var $billing_array;
	var $config;
	/**
	 * Constructor sets all OFX attributes to their default value
	 */
	function OFX($ba = array())	{
		$this->billing_array = $ba;
		$this->config = $GLOBALS['oer_config']['ofx'];
	}
	
	function get_OFX() {
		$string = $this->_ofx_header() . "\n";
		$trns = array();
		$sum = 0.00;
		$date_start = date("YmdHis",strtotime($this->billing_array[0]['bill_date']));
		$date_end = date("YmdHis",strtotime($this->billing_array[0]['bill_date']));
		foreach ($this->billing_array as $bill) {
			$key = $bill['pid'] . "-" . $bill['encounter'];
			if ($bill['code_type'] != "ICD9" && $bill['code_type'] != "COPAY") {
				$trns[$key]['amount'] += $bill['fee'];
				$trns[$key]['date'] = $bill['bill_date'];
				$trns[$key]['memo'] .= $bill['code'] . " ";
				$trns[$key]['payeeid'] = $bill['pid'];
				$trns[$key]['name'] = $bill['name'];
				if ($date_start > date("YmdHis",strtotime($trns[$key]['date']))) {
					//echo "\nsd: $date_start < " . date("YmdHis",strtotime($trn[$key]['date'])) . "\n";
					$date_start = date("YmdHis",strtotime($trns[$key]['date']));
				}
				if ($date_end < date("YmdHis",strtotime($trns[$key]['date']))) {
					//echo "\ned: $date_end < " . date("YmdHis",strtotime($trn[$key]['date'])) . "\n";
					$date_end = date("YmdHis",strtotime($trns[$key]['date']));
				}
			}	
		}
		if (!empty($date_start) && !empty($date_end)) {
			$string .= "<DTSTART>" . $date_start . "\n";
			$string .= "<DTEND>" . $date_end . "\n";	
		}
		foreach ($trns as $key => $trn) {
			$string .= "<STMTTRN>\n";
			$string .= "<TRNTYPE>CREDIT\n";
			$string .= "<DTPOSTED>" . date("YmdHis",strtotime($trn['date'])) . "\n";
			$string .= "<TRNAMT>" . sprintf("%0.2f",$trn['amount']) . "\n";
			$string .= "<FITID>" . $key . "\n";
			$string .= "<NAME>" . $trn['name'] . "\n";
			$string .= "<CHECKNUM>" . $key . "\n";
			$string .= "<PAYEEID>" . $trn['payeeid'] . "\n";
			$string .= "<MEMO>" . trim($trn['memo']) . "\n";
			$string .= "</STMTTRN>\n";
			$sum += $trn['amount'];
		}
		$string .= $this->_ofx_footer($sum);
		return $string;	
	}
	
	function _ofx_header() {
		$string .= "OFXHEADER:100\n";
		$string .= "DATA:OFXSGML\n";
		$string .= "VERSION:102\n";
		$string .= "SECURITY:NONE\n";
		$string .= "ENCODING:USASCII\n";
		$string .= "CHARSET:1252\n";
		$string .= "COMPRESSION:NONE\n";
		$string .= "OLDFILEUID:NONE\n";
		$string .= "NEWFILEUID:NONE\n\n";
		$string .= "<OFX>\n";
		$string .= "<SIGNONMSGSRSV1>\n";
		$string .= "<SONRS>\n";
		$string .= "<STATUS>\n";
		$string .= "<CODE>0\n";
		$string .= "<SEVERITY>INFO\n";
		$string .= "</STATUS>\n";
		$string .= "<DTSERVER>" . date("YmdHis") . "\n";
		$string .= "<LANGUAGE>ENG\n";
		
		//OpenEMR doesn't have a good grasp of timezone so we will need to revisit this later if it causes problems for transaction timestamping
		$string .= "<DTACCTUP>" . date("YmdHis") . ".000[-8:PST]\n";
		
		//intuit programs requires the fields below or it won't accept our ofx transactions
		$string .= "<FI>\n";
		$string .= "<ORG>Bank of America\n";
		$string .= "<FID>5959\n";
		$string .= "</FI>\n";
		$string .= "<INTU.BID>6526\n";
		$string .= "<INTU.USERID>111111111\n";
		//end intuit specific fields
		
		$string .= "</SONRS>\n";
		$string .= "</SIGNONMSGSRSV1>\n";
		
		$string .= "<BANKMSGSRSV1>\n";
		$string .= "<STMTTRNRS>\n";
		$string .= "<TRNUID>0\n";
		$string .= "<STATUS>\n";
		$string .= "<CODE>0\n";
		$string .= "<SEVERITY>INFO\n";
		$string .= "</STATUS>\n";
		$string .= "<STMTRS>\n";
		$string .= "<CURDEF>USD\n";
		$string .= "<BANKACCTFROM>\n";
		$string .= "<BANKID>" . $this->config['bankid'] . "\n";
		$string .= "<ACCTID>" . $this->config['acctid'] . "\n";
		$string .= "<ACCTTYPE>CHECKING";
		$string .= "</BANKACCTFROM>\n";
		$string .= "<BANKTRANLIST>\n";
		return $string;
	}
	
	function _ofx_footer($sum) {
		$string = "</BANKTRANLIST>\n";
		$string .= "<LEDGERBAL>\n";
		$string .= "<BALAMT>" . sprintf("%0.2f",$sum) . "\n";
		$string .= "<DTASOF>" . date("YmdHis"). "\n";
		$string .= "</LEDGERBAL>\n";
		$string .= "</STMTRS>\n";
		$string .= "</STMTTRNRS>\n";
		$string .= "</BANKMSGSRSV1>\n";
		$string .= "</OFX>\n";
		return $string;
	}
} 
?>
