<?php
echo "heyyo. you have been here for ".($_GET['msec']/1000/60)." min";
?>
<script language="JavaScript">

nMilliSeconds = 1000 * 60 * .1;
setTimeout('windowClose(nMilliSeconds);', nMilliSeconds);

function windowClose(nMilliSeconds) {
	window.opener.location.replace("./goo.php");
	window.close();
}


</script>
