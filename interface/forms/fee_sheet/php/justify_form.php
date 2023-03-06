<?php

require_once(__DIR__ . "/../../../globals.php");
require_once("$srcdir/FeeSheetHtml.class.php");
require_once("$webserver_root/interface/forms/fee_sheet/codes.php");

use OpenEMR\Core\Header;
use OpenEMR\Billing\BillingUtilities;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : '';

function getCodeTypes() {
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
}

$code_types = getCodeTypes();

$billresult = BillingUtilities::getBillingByEncounter($pid, $encounter, "*");

// Generate JavaScript to build the array of diagnoses.
function genDiagJS($code_type, $code)
{
    global $code_types;
    if ($code_types[$code_type]['diag']) {
        echo "diags.push('" . attr($code_type) . "|" . attr($code) . "');\n";
    }
}

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Logs'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery']); ?>
	<style type="text/css">
		.mainContainer {
			margin-top: 0px;
			margin-bottom: 10px;
			margin-left: 10px;
			margin-right: 10px;
		}

		.modal-body {
		    padding: 2px;
		    height: 61.7621vh;
		    max-height: 94vh;
		    overflow-y: auto;
		}

		.modal-footer {
			text-align: right;
		    padding: 10px;
		    border-top: 1px solid rgb(229, 229, 229);
		}
	</style>
	<script type="text/javascript">
		var diags = new Array();

		<?php
		if ($billresult) {
		    foreach ($billresult as $iter) {
		        genDiagJS($iter["code_type"], trim($iter["code"]));
		    }
		}
		?>
	</script>
</head>
<body>
<div class="mainContainer modal-body">
	<form>
		<label>Select Justify</label>
		<select name='justify_ele' class="form-control dropdown" onchange='setJustify(this)'>
	        <option value=''></option>
	    </select>
	</form>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-primary btn-messagesaveBtn" data-dismiss="modal" onClick="selectJustify()">Select</button>
</div>
<script type="text/javascript">
	var f = document.forms[0];

	setJustify(f['justify_ele'], true);

	function setJustify(seljust, init = false) {
	 var theopts = seljust.options;
	 var jdisplay = theopts[0].text;
	 // Compute revised justification string.  Note this does nothing if
	 // the first entry is still selected, which is handy at startup.
	 if (seljust.selectedIndex > 0) {
	  var newdiag = seljust.value;
	  if (newdiag.length == 0) {
	   jdisplay = '';
	  }
	  else {
	   if (jdisplay.length) jdisplay += ',';
	   jdisplay += newdiag;
	  }
	 }

	 if(init === true && jdisplay.length === 0) {
	 	var ijdisplay = '';
	 	for (var i = 0; i < diags.length; ++i) {
		 	if (ijdisplay.length) ijdisplay += ',';
		   		ijdisplay += diags[i];
	   	}

	   	jdisplay = ijdisplay;
	 }
	 
	 // Rebuild selection list.
	 var jhaystack = ',' + jdisplay + ',';
	 var j = 0;
	 theopts.length = 0;
	 theopts[j++] = new Option(jdisplay,jdisplay,true,true);
	 for (var i = 0; i < diags.length; ++i) {
	  if (jhaystack.indexOf(',' + diags[i] + ',') < 0) {
	   theopts[j++] = new Option(diags[i],diags[i],false,false);
	  }
	 }
	 theopts[j++] = new Option('Clear','',false,false);
	}

	function selectJustify() {
		var selectedVal = f['justify_ele'].value;

		if(selectedVal == "") {
			alert("Please select justify value");
			return false;
		}
		return selJustify(selectedVal);
	}

	function selJustify(value) {
		if (opener.closed || ! opener.setJustify)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setJustifyVal(value);
		window.close();
		return false;
	}
	</script>
</body>
</html>