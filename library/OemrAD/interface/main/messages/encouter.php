<?php

include_once("../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\MessagesLib;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);
$list = strip_tags($_REQUEST['list']);
$mid = strip_tags($_REQUEST['mid']);

$enc = MessagesLib::getFormEncounters($pid);

?>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<?php Header::setupHeader(['common','esign','dygraphs', 'opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base']);  ?>

	<style type="text/css">
	.childContainer li:last-child {
    	border-bottom: 0px solid;
    }
    .counterListContainer {
    	padding: 10px;
    	margin-bottom: 10px;
    }
	.encounter_data input[type=checkbox] {
    	margin-right: 8px;
    }
    .encounter_data .encounter_forms {
    	padding-left: 20px;
    }
    .modal-container {
    	height: 100%;
	    display: grid;
	    grid-template-rows: 1fr auto;
    }
    .modal-body {
    	overflow: auto;
    }
</style>
</head>
<body>
	<div class="modal-container">
		<div class="modal-body counterListContainer">
		<?php
			$res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
			$html_strings = array();
			$registry_form_name = array();
			while ($result2 = sqlFetchArray($res2)) {
			    array_push($registry_form_name, trim($result2['name']));
			}

			$isfirst = 1;
			foreach ($enc as $key => $result) {
				if ($result{"form_name"} == "New Patient Encounter") {
					if ($isfirst == 0) {
			            foreach ($registry_form_name as $var) {
			                if ($toprint = $html_strings[$var]) {
			                    foreach ($toprint as $var) {
			                        print $var;
			                    }
			                }
			            }

			            $html_strings = array();
			             echo "</div>\n"; // end DIV encounter_forms
			             echo "</div>\n\n";  //end DIV encounter_data
			             echo "<br>";
			        }

			        $result['raw_text'] = $result{"reason"}.
			                " (" . date("Y-m-d", strtotime($result{"date"})) .
			                ") ". $result['drname'];

			        $isfirst = 0;
			        echo "<div class='encounter_data'>\n";
			        echo "<input type=checkbox ".
			        		" data-title='" . $result['raw_text'] . "'".
			                " name='" . $result{"formdir"} . "_" .  $result{"form_id"} . "'".
			                " id='" . $result{"formdir"} . "_" .  $result{"form_id"} . "'".
			                " value='" . $result{"encounter"} . "'" .
			                " class='encounter'".
			                " >";

			        echo $result['raw_text'] . "\n";
			        echo "<div class='encounter_forms'>\n";
				} else {
					$form_name = trim($result{"form_name"});
			        //if form name is not in registry, look for the closest match by
			        // finding a registry name which is  at the start of the form name.
			        //this is to allow for forms to put additional helpful information
			        //in the database in the same string as their form name after the name
			        $form_name_found_flag = 0;
			        foreach ($registry_form_name as $var) {
			            if ($var == $form_name) {
			                $form_name_found_flag = 1;
			            }
			        }

			        // if the form does not match precisely with any names in the registry, now see if any front partial matches
			        // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
			        if (!$form_name_found_flag) {
			            foreach ($registry_form_name as $var) {
			                if (strpos($form_name, $var) == 0) {
			                    $form_name = $var;
			                }
			            }
			        }

			        if (!is_array($html_strings[$form_name])) {
			            $html_strings[$form_name] = array();
			        }

			        array_push($html_strings[$form_name], "<input type='checkbox' ".
			        										" data-title='" . xl_form_title($result{"form_name"}) . "'".
			                                                " name='" . $result{"formdir"} . "_" . $result{"form_id"} . "'".
			                                                " id='" . $result{"formdir"} . "_" . $result{"form_id"} . "'".
			                                                " value='" . $result{"encounter"} . "'" .
			                                                " class='encounter_form' ".
			                                                ">" . xl_form_title($result{"form_name"}) . "<br>\n");
				}
			}

			if(!empty($enc)) {
				echo '</div></div>';
			}

			foreach ($registry_form_name as $var) {
			    if ($toprint = $html_strings[$var]) {
			        foreach ($toprint as $var) {
			            print $var;
			        }
			    }
			}

		?>
		</div>
		<div class="modal-footer">
			<button class="btn btn-encounterSelectBtn btn-sm" data-dismiss="modal">Select</button>
			<button class="btn btn-default btn-Close btn-sm" data-dismiss="modal">Close</button>
		</div>
	</div>
	<script type="text/javascript">
		$listType = '<?php echo $list; ?>';
		$mid = '<?php echo $mid; ?>';

		$finalVariableStr = $mid;

		if($listType != '') {
			$finalVariableStr = $listType;
		}

		jQuery(document).ready(function($){
			//Close modal
			$('.btn-Close').on('click', function(){
				window.close();
				return false;
			});

			$( ".counterListContainer input[type=checkbox]" ).each(function( index ) {
				var eleid = $(this).attr('id');
				if(opener[$finalVariableStr]['selectedEncounterList'].hasOwnProperty(eleid)) {
					$(this).prop('checked', true);
				} 
			});


			$('.encounter').on("click", function(e) {
				var isChecked = $(this).prop("checked");
				var childContainer = $(this).parent().find('.encounter_forms input[type=checkbox]');

				$(childContainer).each(function( index ) {
					$(this).prop('checked', isChecked);
				});
			});

			$('.btn-encounterSelectBtn').on("click", function (e) {
				var tempSelected = {};

				$( ".encounter_data" ).each(function( index ) {
				  var parentCheckbox = $(this).find('input[type=checkbox]');
				  var parentTitleAttr = $(parentCheckbox).data('title');
				  var childContainer = $(this).find('.encounter_forms');
				  var tempEle = {};

				  var isParentChecked = false;
				  var parentId = null;

				  if($(parentCheckbox).prop("checked") == true) {
				  	parentId = $(parentCheckbox).attr('id');
				  	parentVal = $(parentCheckbox).val();
				  	tempSelected[parentId] = { "title" : parentTitleAttr, "value" : parentVal, "pid" : '<?php echo $pid; ?>' };
				  }

				  var childCheckbox = $(childContainer).find('input[type=checkbox]');
				  $(childCheckbox).each(function( index ) {
				  	var childTitleAttr = $(this).data('title');
				  	var childId = $(this).attr('id');
				  	var childVal = $(this).val();

				  	if($(this).prop("checked") == true) {
				  		tempSelected[childId] = { "title" : childTitleAttr, "value" : childVal, "pid" : '<?php echo $pid; ?>' };

				  		if(parentId != null) {
				  			tempSelected[childId]['parentId'] = parentId;
				  		}
				  	}
				  });
				});

				opener[$finalVariableStr]['selectedEncounterList'] = tempSelected;
				afterSelect(tempSelected);
			});
		});

		function afterSelect(list) {
			if (opener.closed || ! opener[$mid].handleEncountersCallBack)
			alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
			else
			opener[$mid].handleEncountersCallBack(list);
			window.close();
			return false;
		}
	</script>
</body>
</html>
