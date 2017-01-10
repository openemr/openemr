<?php
	$this->assign('title','Patient Portal | File Not Found');
	$this->assign('nav','home');

	$this->display('_Header.tpl.php');
?>

<div class="container">

	<h1>Oh Snap!</h1>

	<!-- this is used by app.js for scraping -->
	<!-- ERROR The page you requested was not found /ERROR -->

	<p>The page you requested was not found.  Please check that you typed the URL correctly.</p>

</div> <!-- /container -->

<?php
	$this->display('_Footer.tpl.php');
?>