<?php 

if(!isset($_REQUEST['f'])) { 
	include_once("./globals.php");
	$ajaxUrl = isset($pageUrl) ? $pageUrl : basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);;
?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>Loading...</title>
		<?php OpenEMR\Core\Header::setupHeader(['opener','jquery']);  ?>
		
		<script type="text/javascript">
			$(document).ready(function() {
				$("#bodyContainer").load('<?php echo $ajaxUrl . "?f=1"; ?>', <?php echo json_encode($_REQUEST); ?>);
			});

			<?php if(isset($fullModal) && $fullModal === true) { ?>
				var modalbody = window.parent.document.querySelector('.dialogModal .modal-body');
				modalbody.classList.remove("px-1");
				modalbody.classList.add("p-0");
			<?php } ?>
		</script>
		<style type="text/css">
			.loaderContainer {
				width: 100%;
			    height: 100%;
			    display: grid;
			    position: absolute;
			    justify-content: center;
			    align-items: center;
			}
		</style>
	</head>
	<body id="bodyContainer">
		<div class="loaderContainer">
			<div class="spinner-border"></div>
		</div>
	</body>
	</html>
<?php 
	exit();
}