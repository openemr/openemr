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

	<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-dt-1-10-13/css/jquery.dataTables.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-dt-1-3-2/css/colReorder.dataTables.min.css" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-1-10-13/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-1-3-2/js/dataTables.colReorder.min.js"></script>
    <link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
    <script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/tiny-mce-nwt/tinymce.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/DocumentTreeMenu.js"></script>
    <style type="text/css">
    	.categoryContainer {
    		display: block;
    	}
    	.mainContainer {
    		padding: 10px;
    	}

    	.treeMenuPopDefault a.selectedCat::after { 
		  content: " - (Selected)";
		}

		.form-text-input {
			max-width: 450px;
		}

		#doc_form {
			margin-bottom: 50px;
		}
    </style>
</head>
<body>
	<div class="mainContainer">
		<?php
		$c_doc = new \C_Document();
		$tree = new \CategoryTree(1);
		//$categories_list = $tree->_get_categories_array($_REQUEST['pid']);

		$menu  = new HTML_TreeMenu();
	    $rnode = $c_doc->_array_recurse($tree->tree, array());

	    $menu->addItem($rnode);
	    $treeMenu = new HTML_TreeMenu_DHTML($menu, array('images' => $GLOBALS['webroot'].'/images', 'defaultClass' => 'treeMenuPopDefault'));
	    $treeMenu_listbox  = new HTML_TreeMenu_Listbox($menu, array('linkTarget' => '_self'));

		echo '<div class="categoryContainer">';
	    echo $treeMenu->toHTML();
	    echo '</div>';
		?>
		
		<form id="doc_form">
			<div style="margin-top:40px">
				<input type="hidden" id="category_id" name="category_id" value="">
				<div>
					<b><?php echo xlt('Date'); ?>:&nbsp;</b>
				</div>
				<input type='text' class='datepicker form-control form-text-input' size='10' name='form_docdate' id='form_docdate' value='<?php echo date('Y-m-d'); ?>' />
        		<br/>
				<div>
					<b><?php echo xlt('Destination File Name'); ?>:&nbsp;</b>
				</div>
				<input type='text' class='form-control form-text-input' id="destination_name" name="destination_name" value='' />
						
			</div>
			<br/>
			<footer style="border:none; margin-top: 10px;">
				<button id="submit_doc" type="button">Submit</button>
			</footer>
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