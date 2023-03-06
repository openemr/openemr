<?php

namespace OpenEMR\OemrAd;

/**
 * OemrAd\FeeSheet Class
 */
class FeeSheet{
	
	function __construct(){
	}

	/*
	public static function feesheet_head() {
		return <<<EOF
		<script type="text/javascript">
			function open_justify_form(pid, encounter) {
				var url = '{$GLOBALS['webroot']}/library/OemrAD/interface/forms/fee_sheet/justify_form.php?pid='+pid+'&encounter='+encounter;
			  	dlgopen(url, 'justify_form', 500, 200, '', 'Justify');
			}

			function setJustifyVal(value) {
				if(value != "") {
					var eleJustify = document.getElementsByClassName("selJustify");
					if(eleJustify.length > 0) {
						for (i = 0; i < eleJustify.length; i++) {
							eleJustify[i].selectedIndex = "0";
							eleJustify[i].options["0"].value = value; 
							eleJustify[i].options["0"].text = value; 
						}
					}

				}
			}
		</script>
EOF;
	}*/

	/*public static function feesheet_btn() {
		global $fs;

		?>
		<input type='button' name='bn_justify' value='<?php echo xla('Justify All'); ?>' onClick="open_justify_form('<?php echo $fs->pid ?>', '<?php echo $fs->encounter ?>')" />
		<?php
	}*/

	/*
	public static function getCodeTypes() {
		$code_types = array();
		$ctres = sqlStatement("SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
		while ($ctrow = sqlFetchArray($ctres)) {
		    $code_types[$ctrow['ct_key']] = array(
		    'active' => $ctrow['ct_active'  ],
		    'id'   => $ctrow['ct_id'  ],
		    'fee'  => $ctrow['ct_fee' ],
		    'mod'  => $ctrow['ct_mod' ],
		    'just' => $ctrow['ct_just'],
		    'rel'  => $ctrow['ct_rel' ],
		    'nofs' => $ctrow['ct_nofs'],
		    'diag' => $ctrow['ct_diag'],
		    'mask' => $ctrow['ct_mask'],
		    'label'=> ( (empty($ctrow['ct_label'])) ? $ctrow['ct_key'] : $ctrow['ct_label'] ),
		    'external'=> $ctrow['ct_external'],
		    'claim' => $ctrow['ct_claim'],
		    'proc' => $ctrow['ct_proc'],
		    'term' => $ctrow['ct_term'],
		    'problem'=> $ctrow['ct_problem'],
		    'drug'=> $ctrow['ct_drug']
		    );
		    if (array_key_exists($GLOBALS['default_search_code_type'], $code_types)) {
		        $default_search_type = $GLOBALS['default_search_code_type'];
		    } else {
		        reset($code_types);
		        $default_search_type = key($code_types);
		    }
		}

		return $code_types;
	}*/

	/*
	public static function getBillingInfo($encounter) {
		$results = array();

		$result = sqlStatement("SELECT bl.* FROM `billing` AS bl WHERE bl.encounter = ? AND bl.activity = 1 ",array($encounter));
		while ($row = sqlFetchArray($result)) {
			$results[] = $row;
		}

		return $results;
	}*/

	/*
	public static function validateCPTCode($encounter, $pid) {
		//print_r($encounter);

		$billingData = self::getBillingInfo($encounter);
		$codeItems = array();
		$validationStatus = true;

		foreach ($billingData as $key => $item) {
			if(isset($item['code_type']) && !empty($item['code_type'])) {
				$codeItems[] = array( 'code_type' => $item['code_type'], 'code' => $item['code']);
			}
		}

		foreach ($billingData as $key => $bItem) {
			if(isset($bItem['code_type']) && (substr($bItem['code_type'], 0, 3 ) == "CPT" || substr($bItem['code_type'], 0, 5 ) == "HCPCS")) {
				if(isset($bItem['justify']) && empty($bItem['justify'])) {
					$validationStatus = false;
				}

				if(isset($bItem['justify']) && !empty($bItem['justify'])) {
					$codeValues = self::decodeCode($bItem['justify']);
					$icdStatus = false;

					foreach ($codeValues as $key => $jCode) {
						if(isset($jCode['code_type']) && substr($jCode['code_type'], 0, 3 ) == "ICD") {
							$codeValueStatus = self::isCodeExists($jCode['code_type'], $jCode['code'], $codeItems);
							if($codeValueStatus === true) {
								$icdStatus = true;
							}
						}
					}
					$validationStatus = $icdStatus;
				}
			}
		}

		return $validationStatus;
	}*/

	/*
	public static function isCodeExists($type, $code, $items = array()) {
		$status = false;
		foreach ($items as $key => $item) {
			if(isset($item['code_type']) && $item['code_type'] == $type && $item['code'] == $code) {
				$status = true;
			}
		}

		return $status;
	}*/

	/*
	public static function decodeCode($codeStr) {
		$codes = array();

		if(!empty($codeStr)) {
			$codeItems = explode(":", $codeStr);

			foreach ($codeItems as $key => $codeItem) {
				if(!empty($codeItem)) {
					$codeValues = explode("|", $codeItem);
					if(!empty($codeValues)) {
						$codes[] = array( 'code_type' => $codeValues[0], 'code' => $codeValues[1]);
					}
				}
			}
		}

		return $codes;
	}*/
}
