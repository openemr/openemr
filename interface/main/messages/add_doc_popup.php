<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'] ."/wmt-v3/wmt.globals.php");
include_once($GLOBALS['srcdir'] . "/OemrAD/oemrad.globals.php");
include_once($GLOBALS['srcdir'] . "/wmt-v3/wmt.globals.php");
include_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['pagetype'])) $_REQUEST['pagetype'] = '';


?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Facility Finder'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'datetime-picker']); ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/DocumentTreeMenu.js"></script>
    <style type="text/css">
    	.treeMenuPopDefault a.selectedCat::after { 
		  content: " - (Selected)";
		}
    </style>
</head>
<body>
	<div class="mainContainer p-2">
		<?php
		$c_doc = new \C_Document();
		$tree = new \CategoryTree(1);
		//$categories_list = $tree->_get_categories_array($_REQUEST['pid']);

		$menu  = new HTML_TreeMenu();
	    $rnode = $c_doc->array_recurse($tree->tree, array());

	    $menu->addItem($rnode);
	    $treeMenu = new HTML_TreeMenu_DHTML($menu, array('images' => $GLOBALS['webroot'].'/public/images', 'defaultClass' => 'treeMenuPopDefault'));
	    $treeMenu_listbox  = new HTML_TreeMenu_Listbox($menu, array('linkTarget' => '_self'));

		echo '<div class="categoryContainer">';
	    echo $treeMenu->toHTML();
	    echo '</div>';
		?>
		
		<form id="doc_form" class="mt-3">
			<div>
				<input type="hidden" id="category_id" name="category_id" value="">
				<div class="form-group">
				    <label><?php echo xlt('Date'); ?></label>
				    <input type="text" class="datepicker form-control" size='10' name='form_docdate' id='form_docdate' value='<?php echo date('Y-m-d'); ?>' placeholder="<?php echo xlt('Date'); ?>">
				</div>

				<div class="form-group">
				    <label><?php echo xlt('Destination File Name'); ?></label>
				    <input type="text" class="form-control" name='destination_name' id='destination_name' value='' placeholder="<?php echo xlt('Destination File Name'); ?>">
				</div>
				<div class="form-group">
				    <button id="submit_doc" class="btn btn-primary" type="button">Submit</button>
				</div>	
			</div>
		</form>
	</div>

	<script type="text/javascript">
		$(document).ready(function(){
			$('.datepicker').datetimepicker({
	            <?php $datetimepicker_timepicker = false; ?>
	            <?php $datetimepicker_showseconds = false; ?>
	            <?php $datetimepicker_formatInput = false; ?>
	            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
	            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
	        });

			$('.treeMenuPopDefault a').click(function(e) {
			    e.preventDefault();

			    var link = $(this).attr('href');
			    var params = getUrlParameter(link, 'parent_id');
			    
			    if(params != false) {
			    	$(".treeMenuPopDefault a").removeClass("selectedCat");
			    	$(this).addClass("selectedCat");
			    	$('#category_id').val(params);
			    }
			});

			$('#submit_doc').click(function(){
				var formData = $('#doc_form').serializeObject();

				var errorMsg = '';
				if($.trim(formData.category_id) == "" || formData.category_id == undefined) {
					errorMsg += 'Please Select Category\n';
				}

				if($.trim(formData.destination_name) == "" || formData.destination_name == undefined) {
					errorMsg += 'Please Enter Destination File Name\n';
				}

				if($.trim(formData.form_docdate) == "" || formData.form_docdate == undefined) {
					errorMsg += 'Please Enter Doc Date\n';
				}
				
				if($.trim(errorMsg) != "") {
					alert(errorMsg);
					return false;
				}

				return callSelectedDoc(formData.category_id, formData.destination_name, formData.form_docdate);
			});
		});

		function callSelectedDoc(category_id, destination_name, form_docdate) {
			if (opener.closed || ! opener.setSelectedDoc)
			alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
			else
			opener.setSelectedDoc(category_id, destination_name, form_docdate);
			window.close();
			return false;
		}

		$.fn.serializeObject = function() {
	        var o = {};
	        var a = this.serializeArray();
	        $.each(a, function() {
	            if (o[this.name]) {
	                if (!o[this.name].push) {
	                    o[this.name] = [o[this.name]];
	                }
	                o[this.name].push(this.value || '');
	            } else {
	                o[this.name] = this.value || '';
	            }
	        });
	        return o;
	    };

		var getUrlParameter = function getUrlParameter(url,sParam) {
		    var sPageURL = url,
		        sURLVariables = sPageURL.split('&'),
		        sParameterName,
		        i;

		    for (i = 0; i < sURLVariables.length; i++) {
		        sParameterName = sURLVariables[i].split('=');

		        if (sParameterName[0] === sParam) {
		            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
		        }
		    }
		    return false;
		};
	</script>
</body>
</html>