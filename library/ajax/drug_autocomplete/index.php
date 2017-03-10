<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Demo</title>
  <link rel="stylesheet" href="../../library/js/jquery-ui.min.css" type="text/css" /> 
</head>
<body> 

	<form action='' method='post'>
		<p><label>Names:</label><input type='text' name='names' value='' class='auto'></p>
	</form>

<script type="text/javascript" src="../../library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="../../library/js/jquery-ui.min.js"></script>	
<script type="text/javascript">
$(function() {
	
	//autocomplete
	$(".auto").autocomplete({
		source: "search.php",
		minLength: 1
	});				

});
</script>
</body>
</html>