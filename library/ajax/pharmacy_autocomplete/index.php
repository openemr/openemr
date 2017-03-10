<?php 
require_once('../../../interface/globals.php');


?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Demo</title>
  <link rel="stylesheet" href="../../js/jquery-ui.min.css" type="text/css" /> 

</head>
<body> 

	<form action='' method='post'>
		<p><label>Pharmacy Names : </label><input type='text' name='names' id="pharm" value='' class='auto'></p>
		<p><label>City : </label><input type='text' name='city' id="city" value='' placeholder='Enter City First'></p>
	</form>

<script type="text/javascript" src="../../js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="../../js/jquery-ui.min.js"></script>	
<script type="text/javascript">

	
$(function() {
	
	$("#pharm").click(function() {
	var city = $("#city").val();
    
	var str = "search.php?city="+city;
		//autocomplete
		$(".auto").autocomplete({
			source: str,
			minLength: 1
		});	
    });
});
</script>
</body>
</html>