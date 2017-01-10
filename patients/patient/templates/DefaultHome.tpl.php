<?php
$this->assign( 'title', 'Patient Portal | Home' );
$this->assign( 'nav', 'home' );

$this->display( '_Header.tpl.php' );
?>
<div class="modal fade" id="formdialog" tabindex="-1" role="dialog"	aria-hidden="true">
	<div class="modal-dialog modal-lg" style="background:white">
		<div class="modal-content">
			<div class="modal-header">
				<!-- --><button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">About Your Portal</h4>
			</div>
		</div>
		<div class="modal-body">
			<div><span>Help content goes here</span></div>
		</div>
		<div class="modal-footer">
			<button id="okButton" data-dismiss="modal" class="btn btn-secondary">Close...</button>
		</div>
	</div>
</div>
<div class="container">
	<div class='well'>
	<div class="jumbotron">
		<h1>
			Onsite Portal<i class="fa fa-user-md pull-right" style="font-size:60px;color:red"></i>
		</h1>
		<a class="btn btn-primary btn-lg" data-toggle="modal"
			data-target="#formdialog" href="#">Tell me more »</a>
	</div>
</div>
<div class='well'>
	<div class="row">
		<div class="col-sm-3 col-md-3">
			<h2>
				<i class="icon-cogs"></i> Latest Health Alerts
			</h2>
		</div>
		<div class="col-sm-3 col-md-3">
			<h2>
				<i class="icon-th"></i> The Patients Rights
			</h2>

		</div>
		<div class="col-sm-6 col-md-6">
			<h2>
				<i class="icon-signin"></i>Access Your Medical Records
			</h2>
			<p></p>
			<p>
				<!-- <a class="btn btn-default" href="loginform">Sign In »</a> -->
				<a class="btn btn-default" href="../index.php">Sign In »</a>
			</p>
		</div>

	</div>
</div>
</div>
<!-- /container -->
<?php
$this->display( '_Footer.tpl.php' );
?>