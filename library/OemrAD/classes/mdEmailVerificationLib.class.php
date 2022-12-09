<?php

namespace OpenEMR\OemrAd;


class EmailVerificationLib {
	
	/*Constructor*/
	public function __construct() {	
	}

	/*
	Author: Hardik Khatri
	Description: Get verification results
	*/
	public static function getEmailVerificationResults($user_id, $field_name) {
		if($user_id) {
			$sql = sqlStatement("SELECT * FROM email_verifications WHERE user_id = $user_id AND field_name = '$field_name' ");
			$records = sqlFetchArray($sql);
			if($records) {
				return $records;
			}
		}
		return false;
	}

	/*
	Author: Hardik Khatri
	Description: Update or Save email verificatio
	*/
	public static function updateEmailVerification($user_id, $data) {
		$hvFieldName = "hidden_verification_status";

		foreach ($data as $dKey => $dItem) {
			if (substr( $dKey, -strlen($hvFieldName) ) === $hvFieldName) {
				$form_field = str_replace("_" . $hvFieldName,"",$dKey);
				$hidden_verification_status = isset($data[$dKey]) ? $data[$dKey] : 0;

				if(isset($data[$form_field])) {
					$field_name = str_replace("form_","",$form_field);
					$form_field_value = isset($data[$form_field]) ? $data[$form_field] : "";

					if($field_name && $form_field_value != "") {
						$isRecordExists = self::getEmailVerificationResults($user_id, $field_name);

						if($isRecordExists) {
							sqlQuery("UPDATE email_verifications SET field_value = ?, verification_status = ? WHERE user_id = ? AND field_name = ?", array($form_field_value, $hidden_verification_status, $user_id, $field_name));
						} else {
							$query = "INSERT INTO email_verifications ( user_id, field_name, field_value, verification_status) VALUES ( ?,?,?,? )";
					        sqlStatement($query, array($user_id, $field_name, $form_field_value, $hidden_verification_status));
						}
					}
				}
			}
		}

		// if($field_name && $form_email_direct != "") {
		// 	$isRecordExists = self::getEmailVerificationResults($user_id, $field_name);

		// 	if($isRecordExists) {
		// 		sqlQuery("UPDATE email_verifications SET field_value = ?, verification_status = ? WHERE user_id = ? AND field_name = ?", array($form_email_direct, $hidden_verification_status, $user_id, $field_name));
		// 	} else {
		// 		$query = "INSERT INTO email_verifications ( user_id, field_name, field_value, verification_status) VALUES ( ?,?,?,? )";
		//         sqlStatement($query, array($user_id, $field_name, $form_email_direct, $hidden_verification_status));
		// 	}
		// }
	}

	/*
	Author: Hardik Khatri
	Description: Get email verification content
	*/
	public static function emailVerificationData($pid, $field_name = '', $currentVal = '') {
		$vStatusFlag = 0;

		if($pid && !empty($pid) && !empty($field_name)) {
			$form_email_direct = attr($result[$field_name]);
			$records = self::getEmailVerificationResults($pid, $field_name);

			if($records != false && is_array($records)) {
				$vfield_value = attr($records['field_value']);
				$vStatus = attr($records['verification_status']);

				if($vfield_value == $currentVal && $vStatus == "1") {
					$vStatusFlag = 1;
				}
			}
		}

		return $vStatusFlag;
	}

	/*
	Author: Hardik Khatri
	Description: Javascript functions for "new_comprehensive.php" 
	*/
	public static function getScript() {
		return <<<EOF
			<script type="text/javascript">
			</script>
EOF;
	}
}
